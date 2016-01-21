<?php
/**
 * Kunena Discuss Plugin
 * @package Kunena.plg_content_kunenadiscuss
 *
 * @copyright (C) 2008 - 2016 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined( '_JEXEC' ) or die ();

if (version_compare(JVERSION, '3.0', '>'))
{
	JHTML::_('behavior.formvalidator');
}
else
{
	JHtml::_('behavior.formvalidation');
}
?>
<div id="kdiscuss-quick-post<?php echo $row->id ?>" class="kdiscuss-form">
	<div class="kdiscuss-title"><?php echo JText::_('PLG_KUNENADISCUSS_DISCUSS') ?></div>
	<?php if (isset($this->msg)) : ?>
		<?php echo $this->msg; ?>
	<?php else: ?>
	<form method="post" name="postform">
		<table>
			<tr>
				<td valign="top">
					<table>
					<?php if(!$this->user->exists()) : ?>
					<tr>
						<td></td>
					</tr>
					<tr>
						<td>
							<div class="alert alert-info"><?php echo JText::_('COM_KUNENA_GEN_GUEST'); ?></div>
						</td>
					</tr>
					<?php endif; ?>
					<tr>
						<td><span class="kdiscuss-quick-post-label"><?php echo JText::_('PLG_KUNENADISCUSS_NAME') ?></span></td>
					</tr>
					<tr>
						<td><input type="text" name="name" value="<?php echo $this->name ?>" <?php if ($this->user->exists()) echo 'disabled="disabled" '; ?> required/></td>
					</tr>
					<?php if(!$this->user->exists() && $this->config->askemail) : ?>
						<tr>
							<td><span class="kdiscuss-quick-post-label"><?php echo JText::_('PLG_KUNENADISCUSS_EMAIL') ?></span></td>
						</tr>
					<tr>
						<td><input type="text" name="email" value="<?php echo $this->email ?>" /></td>
					</tr>
					<?php endif; ?>
					<tr>
						<td><span class="kdiscuss-quick-post-label">
						<?php echo JText::_('PLG_KUNENADISCUSS_MESSAGE') ?></span>
					</tr>
					<tr>
						<td colspan="2"><textarea name="message" rows="5" cols="60" class="ktext" required><?php echo $this->message ?></textarea></td>
					</tr>
					<?php if ($this->hasCaptcha()) : ?>
					<tr>
						<td><div id="dynamic_recaptcha_1"> </div></td>
					</tr>
					<?php endif; ?>
					<tr>
						<td><input type="submit" class="kbutton btn" value="<?php echo JText::_('PLG_KUNENADISCUSS_SUBMIT') ?>" /></td>
					</tr>
					</table>
				</td>
			</tr>
		</table>
		<input type="hidden" name="kdiscussContentId" value="<?php echo $row->id ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
	<?php endif; ?>
</div>

