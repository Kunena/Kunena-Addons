<?php

/**
 * Kunena Discuss Plugin
 *
 * @package       Kunena.plg_content_kunenadiscuss
 *
 * @copyright     @kunenacopyright@
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

defined('_JEXEC') or die('');

use Joomla\CMS\HTML\HTMLHelper;

$class .= ' btn btn-outline-primary';
?>
<div class="kunenadiscuss kdiscuss-link">
    <?php echo HTMLHelper::_('kunenaforum.link', $url, $title, $title, $class); ?>
</div>
