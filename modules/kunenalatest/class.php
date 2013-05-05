<?php
/**
 * Kunena Latest Module
 * @package Kunena.mod_kunenalatest
 *
 * @copyright (C) 2008 - 2013 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

/**
 * Class ModuleKunenaLatest
 */
class ModuleKunenaLatest {
	static protected $cssadded = false;

	/**
	 * @var stdClass
	 */
	protected $module = null;
	/**
	 * @var JRegistry
	 */
	protected $params = null;

	/**
	 * @param stdClass $module
	 * @param JRegistry $params
	 */
	public function __construct($module, $params) {
		$this->module = $module;
		$this->params = $params;
		$this->document = JFactory::getDocument();
	}

	function display() {
		// Load CSS only once
		if (self::$cssadded !== true) {
			self::$cssadded = true;
			$this->document->addStyleSheet(JURI::root(true) . '/modules/mod_kunenalatest/tmpl/css/kunenalatest.css');
		}

		// Use caching also for registered users if enabled.
		if ($this->params->get('owncache', 0)) {
			/** @var $cache JCacheControllerOutput */
			$cache = JFactory::getCache('com_kunena', 'output');

			$me = KunenaFactory::getUser();
			$cache->setLifeTime($this->params->get('cache_time', 180));
			$hash = md5(serialize($this->params));
			if ($cache->start("display.{$me->userid}.{$hash}", 'mod_kunenalatest')) {
				return;
			}
		}

		// Initialize Kunena and load language files.
		KunenaForum::setup();
		KunenaFactory::loadLanguage('com_kunena.sys', 'admin');
		KunenaFactory::loadLanguage();
		KunenaFactory::loadLanguage('com_kunena.templates');

		// Convert module parameters into topics view parameters
		$categories = $this->params->get ( 'category_id', 0 );
		$categories = is_array($categories) ? implode(',', $categories) : $categories;
		$this->params->set('limitstart', 0);
		$this->params->set('limit', $this->params->get ( 'nbpost',5 ));
		$this->params->set('topics_categories', $categories);
		$this->params->set('topics_catselection', $this->params->get ( 'sh_category_id_in', 1 ));
		$this->params->set('topics_time', $this->params->get ( 'show_list_time', 168 ));
		$userid = 0;
		switch ( $this->params->get( 'choosemodel' ) ) {
			case 'latestposts' :
				$layout = 'posts';
				$mode = 'recent';
				break;
			case 'noreplies' :
				$layout = 'default';
				$mode = 'noreplies';
				break;
			case 'catsubscriptions' :
				// TODO
				break;
			case 'subscriptions' :
				$userid = -1;
				$layout = 'user';
				$mode = 'subscriptions';
				break;
			case 'favorites' :
				$userid = -1;
				$layout = 'user';
				$mode = 'favorites';
				break;
			case 'owntopics' :
				$layout = 'user';
				$mode = 'posted';
				break;
			case 'deleted' :
				$layout = 'posts';
				$mode = 'deleted';
				break;
			case 'saidthankyouposts' :
				$userid = -1;
				$layout = 'posts';
				$mode = 'mythanks';
				break;
			case 'gotthankyouposts' :
				$userid = -1;
				$layout = 'posts';
				$mode = 'thankyou';
				break;
			case 'userposts' :
				$userid = -1;
				$layout = 'posts';
				$mode = 'recent';
				break;
			case 'latesttopics' :
			default :
				$layout = 'default';
				$mode = 'recent';
		}
		$this->params->set('layout', $layout);
		$this->params->set('mode', $mode);
		$this->params->set('userid', $userid);
		$this->params->set('moreuri', "index.php?option=com_kunena&view=topics&layout={$layout}&mode={$mode}".($userid ? "&userid={$userid}" : ''));

		// Set template path to module
		$this->params->set('templatepath', dirname (JModuleHelper::getLayoutPath ( 'mod_kunenalatest' )));

		// Display topics view
		KunenaForum::display('topics', $layout, null, $this->params);

		if (isset($cache)) {
			$cache->end();
		}
	}

	/**
	 * @param string $link
	 * @param int $len
	 *
	 * @return string
	 */
	static public function shortenLink($link, $len) {
		return preg_replace('/>([^<]{'.$len.'})[^<]*</u', '>\1...<', $link);
	}

	/**
	 * @param KunenaViewTopics $view
	 * @param string $message
	 *
	 * @return string
	 */
	static public function setSubjectTitle($view, $message) {
		$title = '';
		if ( $view->params->get('subjecttitle') == 'subject_only' ) {
			$title = $view->escape($view->topic->subject);
		} elseif ( $view->params->get('subjecttitle') == 'body' ) {
			$title = KunenaHtmlParser::stripBBCode($message, $view->params->get ( 'titlelength' ));
		}

		return $title;
	}
}

/**
 * Class modKunenaLatest is for backwards compatibility.
 */
class modKunenaLatest extends ModuleKunenaLatest {};