<?php
/**
 * Kunena Search Plugin
 * @package Kunena.plg_search_kunena
 *
 * @copyright (C) 2008 - 2012 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

class PlgSearchKunenaInstallerScript {
	function postflight($type, $parent) {
		// Rename manifest file
		$path = $parent->getParent()->getPath('extension_root');
		$name = preg_replace('/^plg_[^_]*_/', '', $parent->get('name'));
		if (JFile::exists("{$path}/{$name}.j25.xml")) {
			if ( JFile::exists("{$path}/{$name}.xml")) JFile::delete("{$path}/{$name}.xml");
			JFile::move("{$path}/{$name}.j25.xml", "{$path}/{$name}.xml");
		}
		$this->uninstallPlugin('search', 'kunenasearch');
	}

	function uninstallPlugin($folder, $name) {
		if (version_compare(JVERSION, '1.6','>')) {
			// Joomla 1.6+
			$query = "SELECT extension_id FROM #__extensions WHERE type='plugin' AND folder='{$folder}' AND element='{$name}'";
		} else {
			// Joomla 1.5
			$query = "SELECT id FROM #__plugins WHERE folder='{$folder}' AND element='{$name}'";
		}
		$db = JFactory::getDbo();
		$db->setQuery ( $query );
		$pluginid = $db->loadResult ();
		if ($pluginid) {
			$installer = new JInstaller ( );
			$installer->uninstall ( 'plugin', $pluginid );
		}
	}
}
