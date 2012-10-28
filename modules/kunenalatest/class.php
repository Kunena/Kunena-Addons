<?php
/**
 * Kunena Latest Module
 * @package Kunena.mod_kunenalatest
 *
 * @copyright (C) 2008 - 2012 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

class modKunenaLatest {
	static protected $cssadded = false;

	protected $params = null;

	public function __construct($module, $params) {
		$this->params = $params;
	}

	function display() {
		KunenaForum::setup();
		KunenaFactory::loadLanguage('com_kunena.sys', 'admin');
		KunenaFactory::loadLanguage();
		KunenaFactory::loadLanguage('com_kunena.templates');

		// Load CSS only once
		$this->document = JFactory::getDocument ();
		if (self::$cssadded == false) {
			$this->document->addStyleSheet ( JURI::root (true) . '/modules/mod_kunenalatest/tmpl/css/kunenalatest.css' );
			self::$cssadded = true;
		}

		$me = KunenaFactory::getUser();
		$caching = $this->params->get ('owncache', 0);
		$cache = JFactory::getCache('com_kunena', 'output');

		// Use caching also for registered users.
		if ($caching) {
			$cache->setLifeTime($this->params->get ('cache_time', 180));
			$hash = md5(serialize($this->params));
			if ($cache->start("display.{$me->userid}.{$hash}", 'mod_kunenalatest')) {
				return;
			}
		}

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
		if ($caching) {
			$cache->end();
		}
	}

	static public function shortenLink($link, $len) {
		return preg_replace('/>([^<]{'.$len.'})[^<]*</u', '>\1...<', $link);
	}

	static public function setSubjectTitle($params, $topic) {
		$title = null;
		if ( $params->get('subjecttitle') == 'subject_only' ) {
			$title = $topic->subject;
		} elseif ( $params->get('subjecttitle') == 'body' ) {
			$title = $topic->first_post_message;
		}

		return $title;
	}
}
