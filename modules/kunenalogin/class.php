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
class ModuleKunenaLogin extends KunenaModule {
	static protected $css = '/modules/mod_kunenalogin/tmpl/css/kunenalogin.css';

	protected function _display() {
		// Load language files.
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
	}

	/**
	 * @return string
	 */
	protected function getReturnURL() {
		$item = null;
		$itemid = (int) $this->params->get($this->type);
		if ($itemid) {
			$app = JFactory::getApplication();
			$menu = $app->getMenu();

			$item = $menu->getItem($itemid);
			if ($item && $item->type == 'alias' && isset($item->query['Itemid'])) {
				$item = $menu->getItem($item->query['Itemid']);
			}
		}
		$url = '';
		if ($item && $item->type == 'component') {
			// Found existing menu item
			$url = $item->link . '&Itemid=' . $itemid;
		}

		return base64_encode($url);
	}

	/**
	 * @param $userid
	 *
	 * @return string|null
	 */
	protected function kunenaAvatar($userid) {
		$user = KunenaFactory::getUser((int)$userid);
		$avatarlink = $user->getAvatarImage('', $this->params->get('avatar_w'), $this->params->get('avatar_h'));
		return $user->getLink($avatarlink);
	}
}
