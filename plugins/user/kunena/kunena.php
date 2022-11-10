<?php

/**
 * Kunena Stopforumspam Plugin
 *
 * @package       Kunena.plg_stopforumspam_kunena
 *
 * @copyright (C) 2008 - 2022 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Http\Http;
use Joomla\CMS\Http\Transport\StreamTransport;
use Joomla\Registry\Registry;
use Joomla\Utilities\IpHelper;

\defined('_JEXEC') or die;

class PlgUserKunena extends CMSPlugin
{
    /**
     * @var    \Joomla\CMS\Application\CMSApplication
     *
     * @since  6.0.0
     */
    protected $app;

    /**
     * Method is called before user data is stored in the database
     *
     * @param   array    $user   Holds the old user data.
     * @param   boolean  $isNew  True if a new user is stored.
     * @param   array    $data   Holds the new user data.
     *
     * @return  boolean
     *
     * @since   6.0.0
     * @throws  InvalidArgumentException on missing required data.
     */
    public function onUserBeforeSave($user, $isNew, $data)
    {
        // Only check for front-end user registration
        if ($this->app->isClient('administrator')) {
            return true;
        }

        $userId = ArrayHelper::getValue($user, 'id', 0, 'int');

        // User already registered, no need to check it further
        if ($userId > 0) {
            return true;
        }

        // Check that the terms is checked if required ie only in registration from frontend.
        $username   = $this->app->input->post->getString('username');
        $email   = $this->app->input->post->getString('email');
        $ip = IpHelper::getIp();

        $data = '&username=' . $username;
        $data .= '&email=' . $email;
        if ($ip != '::1') {
            $data .= '&ip=' . $ip;
        }

        $options = new Registry();

        $transport = new StreamTransport($options);

        // Create a 'stream' transport.
        $http = new Http($options, $transport);

        $response = $http->post('https://api.stopforumspam.org/api', $data . '&json');

        if ($response->code == '200') {
            // The query has worked
            $result = json_decode($response->body);

            if ($result->success) {
                // The username is already present in stopforumspam database
                if ($result->username->appears) {
                    return false;
                }

                // The email is already present in stopforumspam database
                if ($result->email->appears) {
                    return false;
                }

                // The ip address is already present in stopforumspam database
                if (!empty($result->ip)) {
                    if ($result->ip->appears) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
