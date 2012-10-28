<?php
/**
 * Kunena Login Module
 * @package Kunena.mod_kunenalogin
 *
 * @copyright (C) 2008 - 2012 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

class ModKunenaLogin {
	static protected $cssadded = false;

	protected $params = null;

	function __construct($module, $params) {
		$this->params = $params;
	}

	function display() {
		KunenaForum::setup();
		KunenaFactory::loadLanguage();
		KunenaFactory::loadLanguage('com_kunena.templates');

		$this->params->def ( 'greeting', 1 );

		$this->document = JFactory::getDocument ();
		$this->me = KunenaFactory::getUser ();
		$token = JUtility::getToken();

		// Load CSS only once
		if (self::$cssadded == false) {
			$this->document->addStyleSheet ( JURI::root (true) . '/modules/mod_kunenalogin/tmpl/css/kunenalogin.css' );
			self::$cssadded = true;
		}

		$cache = JFactory::getCache('com_kunena', 'output');
		if ($cache->start("{$this->me->userid}.$token", 'mod_kunenalogin')) return;

		$login = KunenaLogin::getInstance();
		if (!$this->me->exists()) {
			$this->type = 'login';
			$this->login = null;
			if ($login) {
				$this->lostPasswordUrl = $login->getResetURL();
				$this->lostUsernameUrl = $login->getRemindURL();
				$this->registerUrl = $login->getRegistrationURL();
				$this->remember = JPluginHelper::isEnabled('system', 'remember');
			}
		} else {
			$this->type = 'logout';
			$this->logout = null;
			$this->lastvisitDate = new KunenaDate($this->me->lastvisitDate);
			if ($login) {
				$this->logout = $login->getLogoutURL();
				$this->recentPosts = JHtml::_('kunenaforum.link', 'index.php?option=com_kunena&view=topics', JText::_ ( 'MOD_KUNENALOGIN_RECENT' ));
				$this->myPosts = JHtml::_('kunenaforum.link', 'index.php?option=com_kunena&view=topics&layout=user&mode=default', JText::_ ( 'MOD_KUNENALOGIN_MYPOSTS' ));
			}

			// Private messages
			$private = KunenaFactory::getPrivateMessaging();
			$this->privateMessages = '';
			if ($this->params->get('showmessage') && $private) {
				$count = $private->getUnreadCount($this->me->userid);
				$this->privateMessages = $private->getInboxLink($count ? JText::sprintf('COM_KUNENA_PMS_INBOX_NEW', $count) : JText::_('COM_KUNENA_PMS_INBOX'));
			}
		}
		$this->return = $this->getReturnURL ();

		require JModuleHelper::getLayoutPath ( 'mod_kunenalogin' );
		$cache->end();
	}

	function getReturnURL() {
		$itemid = (int) $this->params->get ( $this->type );
		if ($itemid) {
			$app = JFactory::getApplication();
			$menu = $app->getMenu();
			$item = $menu->getItem ( $itemid );
		}
		if ($item) {
			// Found existing menu item
			$url = JRoute::_($item->link . '&Itemid=' . $itemid, false);
		} else {
			// stay on the same page
			$uri = JFactory::getURI ();
			$url = $uri->toString ( array ('path', 'query', 'fragment' ) );
		}

		return base64_encode ( $url );
	}

	function kunenaAvatar($userid) {
		$user = KunenaFactory::getUser ( ( int ) $userid );
		$avatarlink = $user->getAvatarImage ( '', $this->params->get ( 'avatar_w' ), $this->params->get ( 'avatar_h' ) );
		return $user->getLink ( $avatarlink );
	}
}
