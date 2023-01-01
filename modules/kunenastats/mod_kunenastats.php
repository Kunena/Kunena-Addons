<?php

/**
 * Kunena Statistics Module
 *
 * @package       Kunena.mod_kunenastats
 *
 * @Copyright (C) 2008 - 2023 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

defined('_JEXEC') or die();

// Kunena detection and version check
$minKunenaVersion = '6.0';

if (!class_exists('KunenaForum') || !KunenaForum::isCompatible($minKunenaVersion)) {
    echo JText::sprintf('MOD_KUNENASTATS_KUNENA_NOT_INSTALLED', $minKunenaVersion);

    return;
}

// Kunena online check
if (!KunenaForum::enabled()) {
    echo JText::_('MOD_KUNENASTATS_KUNENA_OFFLINE');

    return;
}

require_once __DIR__ . '/class.php';

/** @var stdClass $module */
/** @var JRegistry $params */
$instance = new ModuleKunenaStats($module, $params);
$instance->display();
