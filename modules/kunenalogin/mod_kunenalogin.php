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

// Kunena detection and version check
$minKunenaVersion = '2.0';
if (!class_exists('KunenaForum') || !KunenaForum::isCompatible($minKunenaVersion)) {
	echo JText::sprintf('MOD_KUNENALOGIN_KUNENA_NOT_INSTALLED', $minKunenaVersion);
	return;
}
// Kunena online check
if (!KunenaForum::enabled()) {
	echo JText::_('MOD_KUNENALOGIN_KUNENA_OFFLINE');
	return;
}

require_once (dirname ( __FILE__ ) . '/class.php');

$params = ( object ) $params;
$module = ( object ) $module;

$instance = new ModKunenaLogin ( $module, $params );
$instance->display();