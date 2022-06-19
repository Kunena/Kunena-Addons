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
<div class="panel-heading">
	<?php echo HTMLHelper::_('kunenaforum.link', $topic->getUri($category), Text::_('PLG_KUNENADISCUSS_POSTS') . ': ' . $topic->subject, null, 'kdiscuss-topic btn btn-outline-primary mb-3') ?>
</div>
<?php foreach ($messages as $message) {
	ob_start();
	include $messageLayoutPath;
	echo ob_get_clean();
} ?>
<div class="panel-heading">
	<?php echo HTMLHelper::_('kunenaforum.link', $topic->getUri($category), Text::_('COM_KUNENA_READMORE'), null, 'kdiscuss-readmore btn btn-outline-primary') ?>
</div>