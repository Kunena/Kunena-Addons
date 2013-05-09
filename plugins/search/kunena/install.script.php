<?php
/**
 * Kunena Search Plugin
 * @package Kunena.plg_search_kunena
 *
 * @copyright (C) 2008 - 2013 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

class PlgSearchKunenaInstallerScript {
	function postflight($type, $parent) {
		// Delete useless manifest file
		$path = $parent->getParent()->getPath('extension_root');
		$name = preg_replace('/^plg_[^_]*_/', '', $parent->get('name'));
		if (JFile::exists("{$path}/{$name}.j25.xml")) {
			JFile::delete("{$path}/{$name}.j25.xml");
		}
		// Uninstall old version of the plug-in.
		$this->uninstallPlugin('search', 'kunenasearch');
	}

	function uninstallPlugin($folder, $name) {
		// Joomla 2.5+
		$query = "SELECT extension_id FROM #__extensions WHERE type='plugin' AND folder='{$folder}' AND element='{$name}'";
		$db = JFactory::getDbo();
		$db->setQuery ( $query );
		$pluginid = $db->loadResult ();
		if ($pluginid) {
			$installer = new JInstaller ( );
			$installer->uninstall ( 'plugin', $pluginid );
		}
	}
}
