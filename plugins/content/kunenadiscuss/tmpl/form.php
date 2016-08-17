<?php
/**
 * Kunena Discuss Plugin
 *
 * @package       Kunena.plg_content_kunenadiscuss
 *
 * @copyright (C) 2008 - 2016 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          http://www.kunena.org
 **/
defined('_JEXEC') or die ();
$plugin       = JPluginHelper::getPlugin('content', 'kunenadiscuss');
$pluginParams = new JRegistry($plugin->params);
$bootstrap    = $pluginParams->get('bootstrap');
?>
<div id="kdiscuss-quick-post<?php echo $row->id ?>" class="kdiscuss-form">
	<div class="panel-heading"><h3><?php echo JText::_('PLG_KUNENADISCUSS_DISCUSS') ?></h3></div>
	<?php if (isset($this->msg)) : ?>
		<?php echo $this->msg; ?>
	<?php else: ?>
		<div class="container">
			<div class="row">
				<div class="<?php echo $bootstrap; ?>6">
					<div class="panel panel-default">
						<div class="panel-body">
							<form accept-charset="UTF-8" action="" method="POST" name="postform">
								<div class="form-group">
									<label for="name"><?php echo JText::_('PLG_KUNENADISCUSS_NAME') ?></label>
									<input class="form-control" type="text" name="name" value="<?php echo $this->name ?>" <?php if ($this->user->exists())
									{
										echo 'disabled="disabled" ';
									} ?>/>
								</div>
								<?php if (!$this->user->exists() && $this->config->askemail) : ?>
									<div class="form-group">
										<label for="email"><?php echo JText::_('PLG_KUNENADISCUSS_EMAIL') ?></label>
										<input class="form-control" type="text" name="email" value="<?php echo $this->email ?>" />
									</div>
								<?php endif; ?>
								<textarea class="form-control counted" name="message" placeholder="<?php echo JText::_('PLG_KUNENADISCUSS_MESSAGE') ?>" rows="5" style="margin-bottom:10px;width:100%"></textarea>
								<?php if ($this->hasCaptcha()) : ?>
									<?php echo plgContentKunenaDiscuss::displayCaptcha();?>
								<?php endif; ?>
								<button class="btn btn-info" type="submit"><?php echo JText::_('PLG_KUNENADISCUSS_SUBMIT') ?></button>
								<input type="hidden" name="kdiscussContentId" value="<?php echo $row->id ?>" />
								<?php echo JHTML::_('form.token'); ?>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>

