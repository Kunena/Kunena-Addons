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
use Kunena\Forum\Libraries\Date\KunenaDate;

$config       = $this->plugin->config; //KunenaConfig::getInstance();
$avatarType   = 'none'; //KunenaFactory::getTemplate()->params->get('avatarType');
if ($config->orderingSystem == 'mesid') {
    $numLink = $message->id;
} else {
    $numLink = $message->replynum;
}

// Do not display first message
if ($message->id == $topic->first_post_id) {
    return;
}

if ($message->hold) {
    return;
}

?>

<div class="container-fluid">
    <div class="card shadow mb-3 ">
        <div class="row g-0">
            <div class="card-header panel-heading">
                <div class="float-start"><?php echo $message->getAuthor()->getLink(null, null, '') . ' ' . Text::_('COM_KUNENA_MESSAGE_REPLIED'); ?></div>
                <div class="float-end" style="padding-left: 5px;"><a href="<?php echo $topic->getUri($category) . '#' . $message->id; ?>" rel="canonical">#<?php echo $numLink; ?></a></div>
                <div class="float-end text-muted"><?php echo KunenaDate::getInstance($message->time)->toKunena('config_postDateFormat') ?></div>
            </div>
            <div class="col-md-1">
                <div class="profilebox">
                    <?php $avatar = $message->getAuthor()->getAvatarImage($avatarType . ' avatar', 120, 120);

                    if ($avatar) : ?>
                        <?php echo $message->getAuthor()->getLink($avatar, null, '') ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-11">
                <div class="panel panel-default">
                    <div class="card-body panel-body">
                        <?php echo $message->displayField('message'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>