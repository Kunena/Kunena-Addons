<?php
/**
 * Kunena Login Module
 * @package Kunena.mod_kunenalogin
 *
 * @copyright (C) 2008 - 2013 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

/**
 * Class ModuleKunenaLogin
 */
class ModuleKunenaLogin {
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
			$this->document->addStyleSheet(JURI::root(true) . '/modules/mod_kunenalogin/tmpl/css/kunenalogin.css');
		}

		// Use caching also for registered users if enabled.
		if ($this->params->get('owncache', 0)) {
			/** @var $cache JCacheControllerOutput */
			$cache = JFactory::getCache('com_kunena', 'output');

			$me = KunenaFactory::getUser();
			$cache->setLifeTime($this->params->get('cache_time', 180));
			$hash = md5(serialize($this->params));
			if ($cache->start("display.{$me->userid}.{$hash}", 'mod_kunenalogin')) {
				return;
			}
		}

		// Initialize Kunena and load language files.
		KunenaForum::setup();
		KunenaFactory::loadLanguage();
		KunenaFactory::loadLanguage('com_kunena.templates');

		$this->params->def ( 'greeting', 1 );

		$this->document = JFactory::getDocument ();
		$this->me = KunenaFactory::getUser ();
		$token = JSession::getFormToken();

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
			$this->lastvisitDate = KunenaDate::getInstance($this->me->lastvisitDate);
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

		if (isset($cache)) {
			$cache->end();
		}
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
