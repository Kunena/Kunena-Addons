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
use Kunena\Forum\Libraries\Login\KunenaLogin;
?>
<div class="kunenadiscuss kpublic pt-3 pb-3">
    <div class="kdiscuss-title login-discuss">
        <h3><?php echo Text::_('PLG_KUNENADISCUSS_DISCUSS_THIS_ARTICLE'); ?></h3>
    </div>
    <a class="klogin-to-discuss btn btn-outline-primary" rel="nofollow" href="<?php echo KunenaLogin::getInstance()->getLoginURL(); ?>">
        <?php echo Text::_('PLG_KUNENADISCUSS_LOG_IN_TO_COMMENT'); ?>
    </a>
</div>