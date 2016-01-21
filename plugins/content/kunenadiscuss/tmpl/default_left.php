<?php
/**
 * Kunena Discuss Plugin
 * @package Kunena.plg_content_kunenadiscuss
 *
 * @copyright (C) 2008 - 2016 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined( '_JEXEC' ) or die ( '' );

// Do not display first message
if ($this->message->id == $this->topic->first_post_id) return;
if ($this->message->hold) return;
?>
<div class="kdiscuss-item kdiscuss-item<?php echo $this->mmm & 1 ? 1 : 2 ?>">
	<a id="ktopic-<?php echo $this->displayMessageField('id') ?>" > </a>
	<div class="kdiscuss-reply-header">
		<span class="kdiscuss-date" title="<?php echo KunenaDate::getInstance($this->message->time)->toKunena('config_post_dateformat_hover') ?>">
			<?php echo JText::_ ( 'PLG_KUNENADISCUSS_POSTED' )?> <?php echo KunenaDate::getInstance($this->message->time)->toKunena('config_post_dateformat') ?>
		</span>
		<span class="kdiscuss-username">
			<?php echo JText::_ ( 'PLG_KUNENADISCUSS_BY' ) . ' ' . $this->message->getAuthor()->getLink($this->message->name) ?>
		</span>
		<span class="kdiscuss-id">
			<a href="<?php echo $this->message->getUrl() ?>">#<?php echo $this->displayMessageField('id') ?></a>
		</span>
	</div>
	<div class="kdiscuss-reply-body">
		<?php $avatar = $this->message->getAuthor()->getAvatarImage ('kavatar', 'welcome'); if ($avatar) : ?>
		<div class="kdiscuss-avatar">
			<?php echo $this->message->getAuthor()->getLink($avatar) ?>
		</div>
		<?php endif; ?>
		<div class="kdiscuss-text">
			<?php echo $this->displayMessageField('message') ?>
		</div>
	</div>
</div>
