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

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

$returnPage = base64_encode(Uri::getInstance()->toString() . '#kdiscuss');
$formId     = 'kdiscuss-quick-post' . (int) $row->id;
?>
<section id="<?php echo $formId ?>" class="kdiscuss-form" aria-labelledby="<?php echo $formId ?>-title">
    <h3 class="kdiscuss-heading" id="<?php echo $formId ?>-title"><?php echo Text::_('PLG_KUNENADISCUSS_DISCUSS') ?></h3>
    <?php if (!$this->plugin->user->exists()) : ?>
        <div class="alert alert-info"><?php echo Text::_('PLG_KUNENADISCUSS_GEN_GUEST'); ?></div>
    <?php endif; ?>
    <?php foreach ($queuedMessages as $msg) : ?>
        <div class="alert alert-<?php echo $msg['type'] == 'error' ? 'danger' : $msg['type']; ?>" role="<?php echo $msg['type'] == 'error' ? 'alert' : 'status'; ?>"><?php echo $msg['message']; ?></div>
    <?php endforeach; ?>
    <form id="kdiscuss-postform<?php echo (int) $row->id ?>" action="<?php echo Route::_('index.php'); ?>" method="post" class="form-validate">
        <?php foreach ($form->getFieldsets() as $fieldset) : ?>
            <?php $fields = $form->getFieldset($fieldset->name); ?>
            <?php if (count($fields)) : ?>
                <fieldset>
                    <?php if (isset($fieldset->label) && ($legend = trim(Text::_($fieldset->label))) !== '') : ?>
                        <legend><?php echo $legend; ?></legend>
                    <?php endif; ?>
                    <?php foreach ($fields as $field) : ?>
                        <?php echo $field->renderField(); ?>
                    <?php endforeach; ?>
                </fieldset>
            <?php endif; ?>
        <?php endforeach; ?>
        <div class="kdiscuss-actions">
            <button class="btn btn-primary validate" type="submit"><?php echo Text::_('PLG_KUNENADISCUSS_SUBMIT'); ?></button>
        </div>
        <input type="hidden" name="kdiscussContentId" value="<?php echo $row->id ?>" />
        <input type="hidden" name="return" value="<?php echo $returnPage; ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</section>
