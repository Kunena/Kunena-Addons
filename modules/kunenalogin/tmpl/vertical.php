<?php
/**
 * Kunena Login Module
 *
 * @package       Kunena.mod_kunenalogin
 *
 * @copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/
defined('_JEXEC') or die();
?>
<div class="klogin-vert">

	<?php if ($this->type == 'logout')
	:
	?>
		<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" name="login">
			<input type="hidden" name="option" value="com_kunena" />
			<input type="hidden" name="view" value="user" />
			<input type="hidden" name="task" value="logout" />
			<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
			<?php echo JHTML::_('form.token'); ?>

			<?php if ($this->params->get('greeting'))
			:
	?>
				<div class="klogin-hiname">
					<?php echo JText::sprintf('MOD_KUNENALOGIN_HINAME', '<strong>' . $this->me->getLink($this->me->getName()) . '</strong>', $this->me->getName()); ?>
				</div>
			<?php endif; ?>
			<div class="img-rounded">
				<?php if ($this->params->get('showav'))
				{
					echo $this->kunenaAvatar($this->me->userid);
} ?>
			</div>
			<div>
				<?php if ($this->params->get('lastlog'))
				:
	?>
					<div class="klogin-lastvisit">
						<ul>
							<li class="kms">
								<span class="klogin-lasttext"><?php echo JText::_('MOD_KUNENALOGIN_LASTVISIT'); ?></span>
								<?php echo $this->lastvisitDate->toSpan('date_today', 'ago', false, 'klogin-lastdate') ?>
							</li>
						</ul>
					</div>
				<?php endif; ?>
			</div>
			<div>
				<ul class="klogin-loginlink">
					<?php if ($this->privateMessages)
					:
	?>
						<li class="klogin-mypm"><?php echo $this->privateMessages; ?></li>
					<?php endif; ?>
					<?php
					if ($this->params->get('showprofile'))
					:
	?>
						<li class="klogin-myprofile"><?php echo $this->me->getLink(JText::_('MOD_KUNENALOGIN_MYPROFILE')); ?></li>
					<?php                                                                                                                                                                                                                                                                                                                                                                                                                                                         endif; ?>
					<?php
					if ($this->params->get('showmyposts'))
					:
	?>
						<li class="klogin-mypost"><?php echo $this->myPosts ?></li>
					<?php                                                                                                                                                                                                                                                                                                                                                                                                                                                         endif; ?>
					<?php
					if ($this->params->get('showrecent'))
					:
	?>
						<li class="klogin-recent"><?php echo $this->recentPosts ?></li>
					<?php                                                                                                                                                                                                                                                                                                                                                                                                                                                         endif; ?>
				</ul>
			</div>
			<div class="klogin-links">
				<input type="submit" name="Submit" class="btn btn-default btn-primary" value="<?php echo JText::_('MOD_KUNENALOGIN_BUTTON_LOGOUT'); ?>" />
			</div>
		</form>

	<?php else:
	?>

		<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" name="login" class="klogin-form-login">
			<input type="hidden" name="option" value="com_kunena" />
			<input type="hidden" name="view" value="user" />
			<input type="hidden" name="task" value="login" />
			<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
			<?php echo JHTML::_('form.token'); ?>

			<?php echo $this->params->get('pretext'); ?>
			<div class="userdata">
				<div id="form-login-username" class="control-group">
					<div class="controls">
						<div class="input-prepend">
							<span class="add-on">
								<span class="icon-user hasTooltip" title="<?php echo JText::_('MOD_KUNENALOGIN_USERNAME') ?>"></span>
								<label for="modlgn-username" class="element-invisible"><?php echo JText::_('MOD_KUNENALOGIN_USERNAME'); ?></label>
							</span>
							<input id="modlgn-username" type="text" name="username" class="input-small" tabindex="0" size="18" placeholder="<?php echo JText::_('MOD_KUNENALOGIN_USERNAME') ?>" />
						</div>
					</div>
				</div>
				<div id="form-login-password" class="control-group">
					<div class="controls">
						<div class="input-prepend">
							<span class="add-on">
								<span class="icon-lock hasTooltip" title="<?php echo JText::_('MOD_KUNENALOGIN_PASSWORD') ?>"></span>
								<label for="modlgn-passwd" class="element-invisible"><?php echo JText::_('MOD_KUNENALOGIN_PASSWORD'); ?></label>
							</span>
							<input id="modlgn-passwd" type="password" name="password" class="input-small" tabindex="0" size="18" placeholder="<?php echo JText::_('MOD_KUNENALOGIN_PASSWORD') ?>" />
						</div>
					</div>
				</div>
				<?php $login = KunenaLogin::getInstance(); ?>
				<?php
				if ($login->getTwoFactorMethods() > 1)
				:
	?>
					<div id="form-login-secretkey" class="control-group">
						<div class="controls">
							<div class="input-prepend input-append">
								<span class="add-on">
									<span class="icon-star hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>"></span>
									<label for="modlgn-secretkey" class="element-invisible"><?php echo JText::_('JGLOBAL_SECRETKEY'); ?></label>
								</span>
								<input id="modlgn-secretkey" autocomplete="off" type="text" name="secretkey" class="input-small" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY') ?>" />
								<span class="btn width-auto hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY_HELP'); ?>">
									<span class="icon-help"></span>
								</span>
							</div>
						</div>
					</div>
				<?php                                                                                                                                                                                                                                                                                                                                                 endif; ?>
				<?php
				if (JPluginHelper::isEnabled('system', 'remember'))
				:
	?>
					<div id="form-login-remember" class="control-group center">
						<div class="controls">
							<div class="input-prepend input-append">
								<input id="login-remember" type="checkbox" name="remember" class="inputbox" value="yes" />
								<label for="login-remember" class="control-label">
									<?php echo JText::_('MOD_KUNENALOGIN_REMEMBER_ME'); ?>
								</label>
							</div>
						</div>
					</div>
				<?php                                                                                                                                                                                                                                                                                                                                                 endif; ?>
				<div id="form-login-submit" class="control-group center">
					<p>
						<button type="submit" tabindex="3" name="submit" class="btn btn-primary btn">
							<?php echo JText::_('MOD_KUNENALOGIN_BUTTON_LOGIN'); ?>
						</button>
					</p>

					<p>
						<a href="<?php echo $this->lostPasswordUrl ?>" rel="nofollow"><?php echo JText::_('COM_KUNENA_PROFILEBOX_FORGOT_PASSWORD') ?></a>
						<br />

						<a href="<?php echo $this->lostUsernameUrl ?>" rel="nofollow"><?php echo JText::_('COM_KUNENA_PROFILEBOX_FORGOT_USERNAME') ?></a>
						<br />

						<?php if ($this->registerUrl)
						:
	?>
							<a href="<?php echo $this->registerUrl ?>" rel="nofollow"><?php echo JText::_('COM_KUNENA_PROFILEBOX_CREATE_ACCOUNT') ?></a>
						<?php endif; ?>
					</p>
				</div>

				<?php echo $this->params->get('posttext'); ?>
			</div>
		</form>
	<?php endif; ?>
</div>
