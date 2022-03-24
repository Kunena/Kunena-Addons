<?php
/**
 * Kunena Discuss Plugin
 *
 * @package       Kunena.plg_content_kunenadiscuss
 *
 * @copyright (C) 2008 - 2019 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/
defined('_JEXEC') or die ('');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Kunena\Forum\Libraries\Config\KunenaConfig;
use Kunena\Forum\Libraries\Date\KunenaDate;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Joomla\Registry\Registry;

$config       = KunenaConfig::getInstance();
$plugin       = PluginHelper::getPlugin('content', 'kunenadiscuss');
$pluginParams = new Registry($plugin->params);
$bootstrap    = $pluginParams->get('bootstrap');
$avatarType   = KunenaFactory::getTemplate()->params->get('avatarType');

if ($config->orderingSystem == 'mesid')
{
	$this->numLink = $this->message->id;
}
else
{
	$this->numLink = $this->message->replynum;
}

// Do not display first message
if ($this->message->id == $this->topic->first_post_id)
{
	return;
}

if ($this->message->hold)
{
	return;
}
?>

<div class="container-fluid">
	<div class="row-fluid">
		<div class="<?php echo $bootstrap; ?>1">
			<?php $avatar = $this->message->getAuthor()->getAvatarImage($avatarType, 120);

			if ($avatar) : ?>
				<?php echo $this->message->getAuthor()->getLink($avatar, null, '') ?>
			<?php endif; ?>
		</div>
		<div class="<?php echo $bootstrap; ?>11">
			<div class="panel panel-default">
				<div class="panel-heading">
					<span><?php echo $this->message->getAuthor()->getLink(null, null, '') . ' ' . Text::_('COM_KUNENA_MESSAGE_REPLIED'); ?></span>
					<span class="pull-right" style="padding-left: 5px;"><a href="<?php echo $this->topic->getUri($this->category) . '#' . $this->message->id; ?>" rel="canonical">#<?php echo $this->numLink; ?></a></span>
					<span class="text-muted pull-right"><?php echo KunenaDate::getInstance($this->message->time)->toKunena('config_postDateFormat') ?></span>
				</div>
				<div class="panel-body">
					<?php echo $this->displayMessageField('message') ?>
				</div>
			</div>
		</div>
	</div>
</div>
