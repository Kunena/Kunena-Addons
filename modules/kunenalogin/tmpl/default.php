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

$template = $this->params->get ( 'template', '0' );
switch ($template) {
	case "horizontal" :
		require_once (dirname(__FILE__) . '/horizontal.php');
		break;
	case "horizontal2" :
		require_once (dirname(__FILE__) . '/horizontal2.php');
		break;
	default :
		require_once (dirname ( __FILE__ ) . '/vertical.php');
}