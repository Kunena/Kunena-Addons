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

$config = $this->plugin->config;

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

$author = $message->getAuthor();
$date   = KunenaDate::getInstance($message->time);
// 96px for the circle of 48px, sharp on the screens with two pixels per point.
$avatar = $author->getAvatarImage('kdiscuss-avatar-img', 96, 96);
?>
<article class="kdiscuss-message" id="kdiscuss-message-<?php echo (int) $message->id; ?>">
    <?php // The picture only: the name next to it is the link to the profile (one link for the author, not two). ?>
    <div class="kdiscuss-avatar"><?php echo $avatar; ?></div>
    <div class="kdiscuss-content">
        <header class="kdiscuss-meta">
            <span class="kdiscuss-author"><?php echo $author->getLink(null, null, ''); ?></span>
            <span class="kdiscuss-replied"><?php echo Text::_('COM_KUNENA_MESSAGE_REPLIED'); ?></span>
            <time class="kdiscuss-time" datetime="<?php echo $date->toISO8601(); ?>"><?php echo $date->toKunena('config_postDateFormat'); ?></time>
            <a class="kdiscuss-number" href="<?php echo $topic->getUri($category, $message); ?>" rel="canonical">#<?php echo $numLink; ?></a>
        </header>
        <div class="kdiscuss-text">
            <?php echo $message->displayField('message'); ?>
        </div>
    </div>
</article>
