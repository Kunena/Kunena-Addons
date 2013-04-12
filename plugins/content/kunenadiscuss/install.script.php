<?php
/**
 * Kunena Discuss Plugin
 * @package Kunena.plg_content_kunenadiscuss
 *
 * @copyright (C) 2008 - 2013 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined( '_JEXEC' ) or die();

class PlgContentKunenadiscussInstallerScript {
	function postflight($type, $parent) {
		// Delete useless manifest file
		$path = $parent->getParent()->getPath('extension_root');
		$name = preg_replace('/^plg_[^_]*_/', '', $parent->get('name'));
		if (JFile::exists("{$path}/{$name}.j25.xml")) {
			JFile::delete("{$path}/{$name}.j25.xml");
		}
	}
}