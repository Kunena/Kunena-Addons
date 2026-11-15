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

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;

$messageLayoutPath = PluginHelper::getLayoutPath('content', 'kunenadiscuss', $layout . '_message');
?>
<div class="kdiscuss-posts">
    <div class="kdiscuss-posts-header">
        <h3 class="kdiscuss-heading"><?php echo Text::_('PLG_KUNENADISCUSS_POSTS'); ?></h3>
        <?php echo HTMLHelper::_('kunenaforum.link', $topic->getUri($category), $topic->displayField('subject'), null, 'kdiscuss-topic') ?>
    </div>
    <div class="kdiscuss-messages">
        <?php foreach ($messages as $message) {
            ob_start();
            include $messageLayoutPath;
            echo ob_get_clean();
        } ?>
    </div>
    <div class="kdiscuss-posts-footer">
        <?php echo HTMLHelper::_('kunenaforum.link', $topic->getUri($category), Text::_('COM_KUNENA_READMORE'), null, 'kdiscuss-readmore btn btn-outline-primary') ?>
    </div>
</div>
