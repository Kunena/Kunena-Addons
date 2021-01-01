<?php
/**
 * Kunena Discuss Plugin
 *
 * @package       Kunena.plg_content_kunenadiscuss
 *
 * @copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/
defined('_JEXEC') or die('');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>
<div class="panel-heading">
	<?php echo HTMLHelper::_('kunenaforum.link', $this->topic->getUri($this->category), Text::_('PLG_KUNENADISCUSS_POSTS') . ': ' . $this->topic->subject, null, 'kdiscuss-topic') ?>
</div>

<div>
	<?php echo $this->subRequest('Topic/Item/Actions')->set('id', $this->topic->id); ?>
	<br />
</div>

<?php $this->displayMessages() ?>

<div class="panel-heading">
	<?php echo HTMLHelper::_('kunenaforum.link', $this->topic->getUri($this->category), Text::_('COM_KUNENA_READMORE'), null, 'kdiscuss-readmore') ?>
</div>
