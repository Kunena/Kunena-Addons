<?php

/**
 * Kunena Search Module
 *
 * @package       Kunena.mod_kunenasearch
 *
 * @Copyright (C) 2008 - 2024 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Forum\KunenaForum;
use Kunena\Module\KunenaSearch\Site\ModuleKunenaSearch;

// Kunena detection and version check
$minKunenaVersion = '6.2';

if (!class_exists('Kunena\Forum\Libraries\Forum\KunenaForum') || !KunenaForum::isCompatible($minKunenaVersion)) {
    echo Text::sprintf('MOD_KUNENASEARCH_KUNENA_NOT_INSTALLED', $minKunenaVersion);

    return;
}

// Kunena online check
if (!KunenaForum::enabled()) {
    echo Text::_('MOD_KUNENASEARCH_KUNENA_OFFLINE');

    return;
}

/** @var stdClass $module */
/** @var JRegistry $params */
$instance = new ModuleKunenaSearch($module, $params);
$instance->display();
