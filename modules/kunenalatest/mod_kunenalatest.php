<?php
/**
 * Kunena Latest Module
 *
 * @package       Kunena.mod_kunenalatest
 *
 * @copyright (C) 2008 - 2022 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/
use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Forum\KunenaForum;

defined('_JEXEC') or die();

// Kunena detection and version check
$minKunenaVersion = '6.0';

if (!class_exists('Kunena\Forum\Libraries\Forum\KunenaForum') || !KunenaForum::isCompatible($minKunenaVersion))
{
	echo Text::sprintf('MOD_KUNENALATEST_KUNENA_NOT_INSTALLED', $minKunenaVersion);

	return;
}

// Kunena online check
if (!KunenaForum::enabled())
{
	echo Text::_('MOD_KUNENALATEST_KUNENA_OFFLINE');

	return;
}

require_once __DIR__ . '/class.php';

/** @var stdClass $module */
/** @var Registry $params */
$instance = new ModuleKunenaLatest($module, $params);
$instance->display();
