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
defined('_JEXEC') or die('');

use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Config\KunenaConfig;
use Kunena\Forum\Libraries\Date\KunenaDate;
use Kunena\Forum\Libraries\Factory\KunenaFactory;

$config       = KunenaConfig::getInstance();
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
	<div class="card shadow mb-3 ">
		<div class="row g-0">
			<div class="card-header panel-heading">
				<div class="float-start"><?php echo $this->message->getAuthor()->getLink(null, null, '') . ' ' . Text::_('COM_KUNENA_MESSAGE_REPLIED'); ?></div>
				<div class="float-end" style="padding-left: 5px;"><a href="<?php echo $this->topic->getUri($this->category) . '#' . $this->message->id; ?>" rel="canonical">#<?php echo $this->numLink; ?></a></div>
				<div class="float-end text-muted"><?php echo KunenaDate::getInstance($this->message->time)->toKunena('config_postDateFormat') ?></div>
			</div>
			<div class="col-md-1">
				<div class="profilebox">
					<?php $avatar = $this->message->getAuthor()->getAvatarImage($avatarType . ' avatar', 120, 120);

					if ($avatar) : ?>
						<?php echo $this->message->getAuthor()->getLink($avatar, null, '') ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="col-md-11">
				<div class="panel panel-default">
					<div class="card-body panel-body">
						<?php echo $this->displayMessageField('message') ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>