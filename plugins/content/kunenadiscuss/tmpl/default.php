<?php
/**
 * Kunena Discuss Plugin
 *
 * @package       Kunena.plg_content_kunenadiscuss
 *
 * @copyright (C) 2008 - 2016 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          http://www.kunena.org
 **/
defined('_JEXEC') or die('');
?>
<div class="panel-heading">
	<?php echo JHtml::_('kunenaforum.link', $this->topic->getUri($this->category), JText::_('PLG_KUNENADISCUSS_POSTS') . ':' . $this->topic->subject) ?>
</div>

<div>
	<?php echo $this->subRequest('Topic/Item/Actions')->set('id', $this->topic->id); ?>
	<br />
</div>

<?php $this->displayMessages() ?>

<div class="panel-heading">
	<?php echo JHtml::_('kunenaforum.link', $this->topic->getUri($this->category), JText::_('COM_KUNENA_READMORE')) ?>
</div>
