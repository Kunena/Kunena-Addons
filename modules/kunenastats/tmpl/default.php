<?php
/**
 * Kunena Statistics Module
 * @package Kunena.mod_kunenastats
 *
 * @copyright (C) 2008 - 2013 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined('_JEXEC') or die();
$i=0;
?>
<div class="kstats-module kstats-module<?php echo $this->params->get( 'moduleclass_sfx' ) ?>">
	<?php if ( $this->type == 'general' ) : ?>
	<ul class="kstats-items">
		<li><?php echo JText::_('MOD_KUNENASTATS_TOTALUSERS'); ?> <?php echo $this->userlist; ?></li>
		<li><?php echo JText::_('MOD_KUNENASTATS_LATESTMEMBER'); ?> <?php echo $this->shortenLink($this->latestMemberLink, $this->params->get ( 'titlelength' )); ?></li>
		<li><?php echo JText::_('MOD_KUNENASTATS_TOTALPOSTS'); ?> <?php echo $this->formatLargeNumber($this->stats->messageCount, 3); ?></li>
		<li><?php echo JText::_('MOD_KUNENASTATS_TOTALTOPICS'); ?> <?php echo $this->formatLargeNumber($this->stats->topicCount, 3); ?></li>
		<li><?php echo JText::_('MOD_KUNENASTATS_TOTALSECTIONS'); ?> <?php echo $this->formatLargeNumber($this->stats->sectionCount, 3); ?></li>
		<li><?php echo JText::_('MOD_KUNENASTATS_TOTALCATEGORIES'); ?> <?php echo $this->formatLargeNumber($this->stats->categoryCount, 3); ?></li>
		<li><?php echo JText::_('MOD_KUNENASTATS_TODAYOPEN'); ?> <?php echo $this->formatLargeNumber($this->stats->todayTopicCount, 3); ?></li>
		<li><?php echo JText::_('MOD_KUNENASTATS_YESTERDAYOPEN'); ?> <?php echo $this->formatLargeNumber($this->stats->yesterdayTopicCount, 3); ?></li>
		<li><?php echo JText::_('MOD_KUNENASTATS_TODAYTOTANSW'); ?> <?php echo $this->formatLargeNumber($this->stats->todayReplyCount, 3); ?></li>
		<li><?php echo JText::_('MOD_KUNENASTATS_YESTERDAYTOTANSW'); ?> <?php echo $this->formatLargeNumber($this->stats->yesterdayReplyCount, 3); ?></li>
	</ul>
	<?php else : ?>
	<table class="kstats-table">
		<tr>
			<th><?php echo $this->titleHeader ?></th>
			<th><?php echo $this->valueHeader ?></th>
		</tr>
	<?php if (empty($this->stats)) : ?>
		<tr class="krow<?php echo ($i^=1)+1;?>"><td colspan="2"><?php echo JText::_('MOD_KUNENASTATS_NO_ITEMS'); ?></td></tr>
	<?php else : ?>
		<?php foreach ( $this->stats as $item) : ?>
		<tr class="krow<?php echo ($i^=1)+1;?>">
			<td class="kcol-first"><?php echo $this->shortenLink($item->link, $this->params->get ( 'titlelength' )) ?></td>
			<td class="kcol-last"><span class="kstats-hits-bg"><span class="kstats-hits" style="width:<?php echo $item->percent ?>%;"><?php echo $this->formatLargeNumber($item->count, 3);?></span></span></td>
		</tr>
		<?php endforeach; ?>
	<?php endif; ?>
	</table>
	<?php endif; ?>
	<?php if ($this->params->get( 'sh_statslink' )) : ?>
	<div class="kstats-all"><?php echo $this->stats_link ?></div>
	<?php endif; ?>
</div>