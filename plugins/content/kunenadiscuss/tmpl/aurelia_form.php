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
	<div class="card-header">
		<h3><?php echo Text::_('PLG_KUNENADISCUSS_DISCUSS') ?></h3>
	</div>
	<?php if (isset($this->msg)) : ?>
		<?php echo $this->msg; ?>
	<?php else : ?>
		<div class="card-body">
			<?php if (!$this->plugin->user->exists()) : ?>
				<div class="alert alert-info"><?php echo Text::_('PLG_KUNENADISCUSS_GEN_GUEST'); ?></div>
			<?php endif; ?>
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
	<?php endif; ?>
</div>