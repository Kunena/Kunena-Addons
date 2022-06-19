<?php

/**
 * Kunena Discuss Plugin
 *
 * @package       Kunena.plg_content_kunenadiscuss
 *
 * @copyright (C) 2008 - 2022 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/
defined('_JEXEC') or die('');

use Joomla\CMS\HTML\HTMLHelper;

$class .= ' btn btn-outline-primary mb-3';

echo HTMLHelper::_('kunenaforum.link', $url, $title, $title, $class);
