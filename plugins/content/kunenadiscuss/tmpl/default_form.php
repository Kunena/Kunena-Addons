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
?>
<div id="kdiscuss-quick-post<?php echo $row->id ?>" class="card shadow col-lg-6 kdiscuss-form">
	<div class="accordion accordion-flush" id="accordionKdiscuss">
		<div class="accordion-item">
			<h3 class="accordion-header" id="headingOne">
				<button class="accordion-button bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseForm" aria-expanded="true" aria-controls="collapseForm">
					<?php echo Text::_('PLG_KUNENADISCUSS_DISCUSS') ?>
				</button>
			</h3>
			<div id="collapseForm" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionKdiscuss">
				<div class="accordion-body">
					<?php if (!$this->plugin->user->exists()) : ?>
						<div class="alert alert-info"><?php echo Text::_('PLG_KUNENADISCUSS_GEN_GUEST'); ?></div>
					<?php endif; ?>
					<?php foreach ($queuedMessages as $msg) : ?>
						<div class="alert alert-<?php echo $msg['type'] == 'error' ? 'danger' : $msg['type']; ?>"><?php echo $msg['message']; ?></div>
					<?php endforeach; ?>
					<form action="<?php echo Route::_('index.php'); ?>" method="post" class="form-validate">
						<?php foreach ($form->getFieldsets() as $fieldset) : ?>
							<?php $fields = $form->getFieldset($fieldset->name); ?>
							<?php if (count($fields)) : ?>
								<fieldset class="m-0">
									<?php if (isset($fieldset->label) && ($legend = trim(Text::_($fieldset->label))) !== '') : ?>
										<legend><?php echo $legend; ?></legend>
									<?php endif; ?>
									<?php foreach ($fields as $field) : ?>
										<?php echo $field->renderField(); ?>
									<?php endforeach; ?>
								</fieldset>
							<?php endif; ?>
						<?php endforeach; ?>
						<button class="btn btn-primary validate" type="submit"><?php echo Text::_('PLG_KUNENADISCUSS_SUBMIT'); ?></button>
						<input type="hidden" name="kdiscussContentId" value="<?php echo $row->id ?>" />
						<input type="hidden" name="return" value="<?php echo $returnPage; ?>">
						<?php echo HTMLHelper::_('form.token'); ?>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>