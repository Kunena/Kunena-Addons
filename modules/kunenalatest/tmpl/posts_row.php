<?php
/**
 * Kunena Latest Module
 * @package Kunena.mod_kunenalatest
 *
 * @copyright (C) 2008 - 2013 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();
?>
<li class="klatest-item">
<ul class="klatest-itemdetails">
<?php
if ( $this->params->get ( 'sh_topiciconoravatar' ) == 1 ) : ?>
<li class="klatest-avatar">
	<?php echo $this->message->getAuthor()->getLink($this->message->getAuthor()->getAvatarImage('', $this->params->get ( 'avatarwidth' ), $this->params->get ( 'avatarheight' ))) ?>
</li>
<?php elseif ( $this->params->get ( 'sh_topiciconoravatar' ) == 0 ) : ?>
<li class="klatest-topicicon"><?php echo $this->getTopicLink ( $this->topic, 'unread', '[K=TOPIC_ICON]' ) ?></li>
<?php endif; ?>

<li class="klatest-subject">
	<?php
	echo ModuleKunenaLatest::shortenLink( $this->getTopicLink($this->topic, $this->message, null , ModuleKunenaLatest::setSubjectTitle($this, $this->message->message)), $this->params->get ( 'titlelength' ) );
	if ( $this->params->get ( 'sh_postcount' ) ) echo ' ('.$this->topic->getTotal().' '.JText::_('MOD_KUNENALATEST_MSG').')';

	if ($this->topic->unread) {
		echo ' <sup class="knewchar">(' . JText::_($this->params->get ( 'unreadindicator' )) . ')</sup>';
	}
	if ($this->params->get ( 'sh_sticky' ) && $this->topic->ordering) {
		echo $this->getIcon ( 'ktopicsticky', JText::_('MOD_KUNENALATEST_STICKY_TOPIC') );
	}
	if ($this->params->get ( 'sh_locked' ) && $this->topic->locked) {
		echo $this->getIcon ( 'ktopiclocked', JText::_('COM_KUNENA_GEN_LOCKED_TOPIC') );
	}
	if ($this->params->get ( 'sh_favorite' ) && $this->topic->getUserTopic()->favorite) {
		echo $this->getIcon ( 'kfavoritestar', JText::_('COM_KUNENA_FAVORITE') );
	}
	?>
</li>
<?php if ($this->params->get ( 'sh_firstcontentcharacter' )) : ?>
<li class="klatest-preview-content"><?php echo KunenaHtmlParser::stripBBCode($this->message->message, $this->params->get ( 'lengthcontentcharacters' )); ?></li>
<?php endif; ?>
<?php if ($this->params->get ( 'sh_category' )) : ?>
<li class="klatest-cat"><?php echo JText::_ ( 'MOD_KUNENALATEST_IN_CATEGORY' ).' '. $this->categoryLink ?></li>
<?php endif; ?>
<?php if ($this->params->get ( 'sh_author' )) : ?>
<li class="klatest-author"><?php echo JText::_ ( 'MOD_KUNENALATEST_POSTED_BY' ) .' '. $this->message->getAuthor()->getLink(); ?></li>
<?php endif; ?>
<?php if ($this->params->get ( 'sh_time' )) : ?>
<li class="klatest-posttime"><?php $override = $this->params->get ( 'dateformat' ); echo KunenaDate::getInstance($this->message->time)->toKunena($override ? $override : 'config_post_dateformat'); ?></li>
<?php endif; ?>
</ul>
</li>
