<?php
/**
 * Content - Kunena Discuss Plugin
 * @package Kunena Discuss
 *
 * @copyright (C) 2008 - 2012 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined( '_JEXEC' ) or die();

class PlgContentKunenadiscussInstallerScript {
	function postflight($type, $parent) {
		// Rename manifest file
		$path = $parent->getParent()->getPath('extension_root');
		$name = preg_replace('/^plg_[^_]*_/', '', $parent->get('name'));
		if (JFile::exists("{$path}/{$name}.j25.xml")) {
			if ( JFile::exists("{$path}/{$name}.xml")) JFile::delete("{$path}/{$name}.xml");
			JFile::move("{$path}/{$name}.j25.xml", "{$path}/{$name}.xml");
		}
	}
}