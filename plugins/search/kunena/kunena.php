<?php
/**
 * Kunena Search Plugin
 * @package Kunena.plg_search_kunena
 *
 * @copyright (C) 2008 - 2013 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

// Kunena detection and version check
$minKunenaVersion = '3.0';
if (!class_exists('KunenaForum') || !KunenaForum::isCompatible($minKunenaVersion)) {
	return;
}
// Kunena online check
if (!KunenaForum::enabled()) {
	return;
}

// Setup Kunena framework
KunenaForum::setup();
KunenaFactory::loadLanguage('com_kunena.sys', 'admin');

// Initialize plugin
$app = JFactory::getApplication();
$app->registerEvent('onContentSearch', 'plgSearchKunena');
$app->registerEvent('onContentSearchAreas', 'plgSearchKunenaAreas');

/**
 * Function to return an array of search areas.
 *
 * @return mixed
 */
function &plgSearchKunenaAreas() {
	static $areas = array();
	if (empty($areas)) {
		$areas['kunena'] = JText::_('COM_KUNENA');
	}
	return $areas;
}

/**
 * @param        $text
 * @param string $phrase
 * @param string $ordering
 * @param null   $areas
 *
 * @return array
 */
function plgSearchKunena($text, $phrase = '', $ordering = '', $areas = null) {
	//If the array is not correct, return it:
	if (is_array($areas) && !array_intersect($areas, array_keys(plgSearchKunenaAreas()))) {
		return array();
	}

	$plugin = JPluginHelper::getPlugin('search', 'kunena');
	$pluginParams = new JRegistry();
	$pluginParams->loadString($plugin->params);

	//And define the parameters. For example like this..
	$limit = $pluginParams->get('search_limit', 50);
	$contentLimit = $pluginParams->get('content_limit', 40);
	$bbcode = $pluginParams->get('show_bbcode', 1);
	$openInNewPage = $pluginParams->get('open_new_page', 1);

	//Use the function trim to delete spaces in front of or at the back of the searching terms
	$text = trim($text);

	//Return Array when nothing was filled in
	if ($text == '') {
		return array ();
	}

	$db = JFactory::getDbo();

	//After this, you have to add the database part. This will be the most difficult part, because this changes per situation.
	//In the coding examples later on you will find some of the examples used by Joomla! 1.5 core Search Plugins.
	//It will look something like this.
	switch ($phrase) {

		//search exact
		case 'exact' :
			$text = $db->quote('%' . $db->escape($text) . '%', false);
			$where = "(m.subject LIKE {$text} OR t.message LIKE {$text})";
			break;

		//search all or any
		case 'all' :
		case 'any' :
		default :
			$where = array ();
			$words = explode ( ' ', $text );
			foreach ( $words as $word ) {
				$word = $db->quote('%' . $db->escape(trim($word)) . '%', false);
				$where [] = "m.subject LIKE {$word} OR t.message LIKE {$word}";
			}
			$where = '(' . implode ( ($phrase == 'all' ? ') AND (' : ') OR ('), $where ) . ')';
			break;
	}

	//ordering of the results
	switch ($ordering) {

		//oldest first
		case 'oldest' :
			$orderby = 'm.time ASC';
			break;

		//popular first
		case 'popular' :
			// FIXME: should be topic hits
			$orderby = 'm.hits DESC, m.time DESC';
			break;

		//newest first
		case 'newest' :
			$orderby = 'm.time DESC';
			break;

		//alphabetic, ascending
		case 'alpha' :
		//default setting: alphabetic, ascending
		default :
			$orderby = 'm.subject ASC, m.time DESC';
	}

	$params = array('orderby'=>$orderby, 'where'=>$where, 'starttime'=>-1);
	list($total, $messages) = KunenaForumMessageHelper::getLatestMessages(false, 0, $limit, $params);
	$rows = array();
	foreach ($messages as $message) {
		/** @var KunenaForumMessage $message */
		// Function must return: href, title, section, created, text, browsernav
		$row = new StdClass();
		$row->id = $message->id;
		$row->href = $message->getUrl();
		$row->title = JString::substr($message->subject, '0', $contentLimit);
		$row->section = $message->getCategory()->name;
		$row->created = $message->time;
		if ($bbcode) {
			$row->text = KunenaHtmlParser::parseBBCode($message->message, $contentLimit);
		} else {
			$row->text = KunenaHtmlParser::stripBBCode($message->message, $contentLimit);
		}
		$row->browsernav = $openInNewPage ? 1 : 0;
		$rows[] = $row;
	}

	//Return the search results in an array
	return $rows;
}
