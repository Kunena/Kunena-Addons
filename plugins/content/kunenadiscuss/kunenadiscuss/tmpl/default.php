<?php
/**
 * Kunena Discuss Plugin
 * @package Kunena.plg_content_kunenadiscuss
 *
 * @copyright (C) 2008 - 2013 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined( '_JEXEC' ) or die ( '' );
?>
<div class="kdiscuss-title">
	<?php echo JHtml::_('kunenaforum.link', $this->topic->getUri ($this->category), JText::_('PLG_KUNENADISCUSS_POSTS') ) ?>
</div>

<?php $this->displayMessages() ?>

<div class="kdiscuss-more">
	<?php echo JHtml::_('kunenaforum.link', $this->topic->getUri ($this->category), JText::_('COM_KUNENA_READMORE') ) ?>
</div>
