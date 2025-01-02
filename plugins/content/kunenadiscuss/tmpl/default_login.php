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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Kunena\Forum\Libraries\Login\KunenaLogin;

// Get current URL for redirect
$uri = Uri::getInstance();
$return = base64_encode($uri->toString());

// Get login URL and append return parameter
$loginUrl = KunenaLogin::getInstance()->getLoginURL();
$loginUrl .= (strpos($loginUrl, '?') === false ? '?' : '&') . 'return=' . $return;
?>
<div class="kunenadiscuss kpublic pt-3 pb-3">
    <div class="kdiscuss-title login-discuss">
        <h3><?php echo Text::_('PLG_KUNENADISCUSS_DISCUSS_THIS_ARTICLE'); ?></h3>
    </div>
    <a class="klogin-to-discuss btn btn-outline-primary" rel="nofollow" href="<?php echo $loginUrl; ?>">
        <?php echo Text::_('PLG_KUNENADISCUSS_LOG_IN_TO_COMMENT'); ?>
    </a>
</div>
