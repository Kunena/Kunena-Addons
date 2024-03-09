<?php

/**
 * Kunena Login Module
 *
 * @package       Kunena.mod_kunenalogin
 *
 * @Copyright (C) 2008 - 2024 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

namespace Kunena\Module\KunenaLogin\Site;

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Kunena\Forum\Libraries\Config\KunenaConfig;
use Kunena\Forum\Libraries\Date\KunenaDate;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Login\KunenaLogin;
use Kunena\Forum\Libraries\Module\KunenaModule;
use Joomla\CMS\Helper\ModuleHelper;


/**
 * Class ModuleKunenaLogin
 */
class ModuleKunenaLogin extends KunenaModule
{
    public $me;

    public $type;

    public $login;

    public $logout;

    public $lastvisitDate;

    public $recentPosts;

    public $myPosts;

    public $privateMessages;

    public $return;

    public $lostPasswordUrl;

    public $lostUsernameUrl;

    public $registerUrl;

    public $remember;

    protected function _display(): void
    {
        Factory::getDocument()->addStyleSheet(Uri::root(true) . '/modules/mod_kunenalogin/tmpl/css/kunenalogin.css');

        // Load language files.
        KunenaFactory::loadLanguage();
        KunenaFactory::loadLanguage('com_kunena.templates');

        $this->params->def('greeting', 1);

        $this->document = Factory::getDocument();
        $this->me       = KunenaFactory::getUser();
        $token          = Session::getFormToken();

        $login  = KunenaLogin::getInstance();
        $access = KunenaConfig::getInstance()->accessComponent;

        if (!$access) {
            Factory::getApplication()->enqueueMessage(Text::_('MOD_KUNENALOGIN_DIRECT'), 'error');
        } else {
            if (!$this->me->exists()) {
                $this->type  = 'login';
                $this->login = null;

                if ($login) {
                    $this->lostPasswordUrl = $login->getResetURL();
                    $this->lostUsernameUrl = $login->getRemindURL();
                    $this->registerUrl     = $login->getRegistrationURL();
                    $this->remember        = PluginHelper::isEnabled('system', 'remember');
                }
            } else {
                $this->type          = 'logout';
                $this->logout        = null;
                $this->lastvisitDate = KunenaDate::getInstance($this->me->lastvisitDate);

                if ($login) {
                    $this->logout      = $login->getLogoutURL();
                    $this->recentPosts = HTMLHelper::_('kunenaforum.link', 'index.php?option=com_kunena&view=topics', Text::_('MOD_KUNENALOGIN_RECENT'));
                    $this->myPosts     = HTMLHelper::_('kunenaforum.link', 'index.php?option=com_kunena&view=topics&layout=user&mode=default', Text::_('MOD_KUNENALOGIN_MYPOSTS'));
                }

                // Private messages
                $private               = KunenaFactory::getPrivateMessaging();
                $this->privateMessages = '';

                if ($this->params->get('showmessage') && $private) {
                    $count                 = $private->getUnreadCount($this->me->userid);
                    $this->privateMessages = $private->getInboxLink($count ? Text::sprintf('COM_KUNENA_PMS_INBOX_NEW', $count) : Text::_('COM_KUNENA_PMS_INBOX'));
                }
            }

            $this->return = $this->getReturnURL();

            require ModuleHelper::getLayoutPath('mod_kunenalogin');
        }
    }

    /**
     * @return string
     */
    protected function getReturnURL()
    {
        $item   = null;
        $itemid = (int) $this->params->get($this->type);

        if ($itemid) {
            $app  = Factory::getApplication();
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
    protected function kunenaAvatar($userid)
    {
        $user       = KunenaFactory::getUser((int) $userid);
        $avatarlink = $user->getAvatarImage('', $this->params->get('avatar_w'), $this->params->get('avatar_h'));

        return $user->getLink($avatarlink);
    }
}
