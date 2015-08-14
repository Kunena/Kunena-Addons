<?php
/**
 * Kunena Discuss Plugin
 * @package Kunena.plg_content_kunenadiscuss
 *
 * @copyright (C) 2008 - 2015 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined( '_JEXEC' ) or die ();
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
					<tr>
						<td><span class="kdiscuss-quick-post-label"><?php echo JText::_('PLG_KUNENADISCUSS_NAME') ?></span></td>
						<td><input type="text" name="name" required="required" value="<?php echo $this->name ?>" <?php if ($this->user->exists()) echo 'disabled="disabled" '; ?>/></td>
					</tr>
					<?php if(!$this->user->exists() && $this->config->askemail) : ?>
					<tr>
						<td><span class="kdiscuss-quick-post-label"><?php echo JText::_('PLG_KUNENADISCUSS_EMAIL') ?></span></td>
						<td><input type="text" name="email" required="required" value="<?php echo $this->email ?>" /></td>
					</tr>
					<?php endif; ?>
					<tr>
						<td><span class="kdiscuss-quick-post-label">
						<?php echo JText::_('PLG_KUNENADISCUSS_MESSAGE') ?></span>
					</tr>
					<tr>
						<td colspan="2"><textarea name="message" rows="5" cols="60" required="required" class="ktext"><?php echo $this->message ?></textarea></td>
					</tr>
					<?php if ($this->hasCaptcha()) : ?>
					<tr>
						<td><span class="kdiscuss-quick-post-label"><?php echo JText::_('PLG_KUNENADISCUSS_CAPTCHA') ?></span></td>
						<td><div id="dynamic_recaptcha_1"> </div></td>
					</tr>
					<?php endif; ?>
					<tr>
						<td><input type="submit" class="kbutton" value="<?php echo JText::_('PLG_KUNENADISCUSS_SUBMIT') ?>" /></td>
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

