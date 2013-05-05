<?php
/**
 * Kunena Search Module
 * @package Kunena.mod_kunenasearch
 *
 * @copyright (C) 2008 - 2013 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

/**
 * Class ModuleKunenaSearch
 */
class ModuleKunenaSearch {
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

	public function display() {
		// Load CSS only once
		if (self::$cssadded !== true) {
			self::$cssadded = true;
			$this->document->addStyleSheet(JURI::root (true) . '/modules/mod_kunenasearch/tmpl/css/kunenasearch.css');
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

		$this->ksearch_button			= $this->params->get('ksearch_button', '');
		$this->ksearch_button_pos		= $this->params->get('ksearch_button_pos', 'right');
		$this->ksearch_button_txt		= $this->params->get('ksearch_button_txt', JText::_('Search'));
		$this->ksearch_width			= intval($this->params->get('ksearch_width', 20));
		$this->ksearch_maxlength		= $this->ksearch_width > 20 ? $this->ksearch_width : 20;
		$this->ksearch_txt				= $this->params->get('ksearch_txt', JText::_('Search...'));
		$this->ksearch_moduleclass_sfx	= $this->params->get('moduleclass_sfx', '');
		$this->url						= KunenaRoute::_('index.php?option=com_kunena');

		require(JModuleHelper::getLayoutPath('mod_kunenasearch'));

		if (isset($cache)) {
			$cache->end();
		}
	}
}
