<?php
/**
 * Kunena Latest Module
 *
 * @package       Kunena.mod_kunenalatest
 *
 * @copyright (C) 2008 - 2022 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/
use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Date\KunenaDate;
use Kunena\Forum\Libraries\Html\KunenaParser;
use Kunena\Forum\Libraries\Icons\KunenaIcons;
use Kunena\Forum\Libraries\Template\KunenaTemplate;

defined('_JEXEC') or die ();
$topic = $this->topic;

?>
<li class="klatest-item">
	<ul class="klatest-itemdetails">
		<?php
		if ($this->params->get('sh_topiciconoravatar') == 1) : ?>
			<li class="klatest-avatar">
				<?php echo $this->topic->getLastPostAuthor()->getLink($this->topic->getLastPostAuthor()->getAvatarImage('', $this->params->get('avatarwidth'), $this->params->get('avatarheight'))) ?>
			</li>
		<?php elseif ($this->params->get('sh_topiciconoravatar') == 0) : ?>
			<li class="klatest-topicicon">
				<?php if ($this->topic->unread && $this->params->get('sh_unread') == 1) : ?>
					<?php echo $this->getTopicLink($topic, 'unread', $topic->getIcon($topic), '', null, $this->category, true, true); ?>
				<?php else : ?>
					<?php echo $this->getTopicLink($topic, $this->params->get('sh_url_link'), $topic->getIcon($topic), '', null, $this->category, true, false); ?>
				<?php endif; ?>
			</li>
		<?php endif; ?>

		<li class="klatest-subject">
			<?php
			if ($topic->unread)
			{
				if ($topic->unread && $this->params->get('sh_unread') == 1)
				{
					echo ModuleKunenaLatest::shortenLink($this->getTopicLink($topic, 'unread', $this->escape($topic->subject), null, KunenaTemplate::getInstance()->tooltips(), $this->category, true, true), $this->params->get('titlelength'));
				}
				else
				{
					echo ModuleKunenaLatest::shortenLink($this->getTopicLink($topic, $this->params->get('sh_url_link'), $this->escape($topic->subject), null, KunenaTemplate::getInstance()->tooltips(), $this->category, true, true), $this->params->get('titlelength'));
				}

				echo '<sup class="knewchar" dir="ltr">(' . (int) $topic->unread .
					' ' . Text::_('COM_KUNENA_A_GEN_NEWCHAR') . ')</sup>';
			}
			else
			{
				echo ModuleKunenaLatest::shortenLink($this->getTopicLink($topic, $this->params->get('sh_url_link'), null, null, KunenaTemplate::getInstance()->tooltips() . ' topictitle', $this->category, true, false), $this->params->get('titlelength'));
			}

			if ($this->params->get('sh_postcount'))
			{
				echo ' (' . $this->topic->getTotal() . ' ' . Text::_('MOD_KUNENALATEST_MSG') . ')';
			}

			if ($this->params->get('sh_locked') && $this->topic->locked)
			{
				echo '<span ' . KunenaTemplate::getInstance()->tooltips(true) . ' title="' . Text::_('COM_KUNENA_GEN_LOCKED_TOPIC') .'">' . KunenaIcons::lock() . '</span>';
			}

			if ($this->params->get('sh_favorite') && $this->topic->getUserTopic()->favorite)
			{
				echo '<span ' . KunenaTemplate::getInstance()->tooltips(true) . ' title="' . Text::_('COM_KUNENA_FAVORITE') .'">' . KunenaIcons::star() . '</span>';
			}
			?>
		</li>
		<?php if ($this->params->get('sh_firstcontentcharacter')) : ?>
			<li class="klatest-preview-content"><?php echo KunenaParser::stripBBCode($this->topic->last_post_message, $this->params->get('lengthcontentcharacters')); ?></li>
		<?php endif; ?>
		<?php if ($this->params->get('sh_category')) : ?>
			<li class="klatest-cat"><?php echo Text::_('MOD_KUNENALATEST_IN_CATEGORY') . ' ' . $this->categoryLink ?></li>
		<?php endif; ?>
		<?php if ($this->params->get('sh_author')) : ?>
			<li class="klatest-author"><?php echo Text::_('MOD_KUNENALATEST_LAST_POST_BY') . ' ' . $this->lastPostAuthor->getLink($this->lastUserName); ?></li>
		<?php endif; ?>
		<?php if ($this->params->get('sh_time')) : ?>
			<li class="klatest-posttime"><?php $override = $this->params->get('dateformat');
				echo KunenaDate::getInstance($this->topic->last_post_time)->toKunena($override ? $override : 'config_postDateFormat'); ?></li>
		<?php endif; ?>
	</ul>
</li>
