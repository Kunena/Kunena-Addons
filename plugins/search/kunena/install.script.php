<?php

/**
 * Kunena Search Plugin
 *
 * @package       Kunena.plg_search_kunena
 *
 * @Copyright (C) 2008 - 2024 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;

class PlgSearchKunenaInstallerScript
{
    function postflight($type, $parent)
    {
        // Uninstall old version of the plug-in.
        $this->uninstallPlugin('search', 'kunenasearch');
    }

    function uninstallPlugin($folder, $name)
    {
        // Joomla 2.5+
        $query = "SELECT extension_id FROM #__extensions WHERE type='plugin' AND folder='{$folder}' AND element='{$name}'";
        $db    = Factory::getDbo();
        $db->setQuery($query);
        $pluginid = $db->loadResult();

        if ($pluginid) {
            $installer = new Installer();
            $installer->uninstall('plugin', $pluginid);
        }
    }
}
