<?php
/**
 * Kunena Login Module
 * @package Kunena.mod_kunenalogin
 *
 * @copyright (C) 2008 - 2013 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined('_JEXEC') or die();
?>
<div class="klogin-horiz">
	<?php if($this->type == 'logout') : ?>
		<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" name="login">
			<input type="hidden" name="option" value="com_kunena" />
			<input type="hidden" name="view" value="user" />
			<input type="hidden" name="task" value="logout" />
			<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
			<?php echo JHTML::_( 'form.token' ); ?>

			<div class="klogin-avatar">
				<?php if ($this->params->get('showav')) echo  $this->kunenaAvatar( $this->me->userid ) ?>
			</div>
			<div class="klogin-middle">
				<ul>
					<?php if ($this->params->get('greeting') || $this->params->get('lastlog')) : ?>
					<li>
						<?php if ($this->params->get('greeting')) : ?>
						<span class="klogin-hiname">
							<?php echo JText::sprintf('MOD_KUNENALOGIN_HINAME','<strong>'
								.$this->me->getLink ( $this->me->getName()).'</strong>', $this->me->getName() ); ?>
						</span>
						<?php endif; ?>
						<?php if ($this->params->get('lastlog')) : ?>
							(
							<span class="klogin-lasttext"><?php echo JText::_('MOD_KUNENALOGIN_LASTVISIT'); ?></span>
							<?php echo $this->lastvisitDate->toSpan('date_today', 'ago', false, 'klogin-lastdate') ?>
							)
						<?php endif; ?>
					</li>
					<?php endif; ?>
					<li class="klogin-links">
						<ul class="klogin-loginlink">
							<?php if ($this->privateMessages) : ?>
								<li class="klogin-mypm"><?php echo $this->privateMessages; ?></li>
							<?php endif; ?>
							<?php if ($this->params->get('showprofile')) : ?>
								<li class="klogin-myprofile"><?php echo $this->me->getLink ( JText::_ ( 'MOD_KUNENALOGIN_MYPROFILE' ) ); ?></li>
							<?php endif; ?>
							<?php if ($this->params->get('showmyposts')) : ?>
								<li class="klogin-mypost"><?php echo $this->myPosts ?></li>
							<?php endif; ?>
							<?php if ($this->params->get('showrecent')) : ?>
								<li class="klogin-recent"><?php echo $this->recentPosts ?></li>
							<?php endif; ?>
						</ul>
					</li>
					<li class="klogin-logout-button">
						<input type="submit" name="Submit" class="kbutton" value="<?php echo JText::_('MOD_KUNENALOGIN_BUTTON_LOGOUT'); ?>" />
					</li>
				</ul>
			</div>
		</form>
	<?php else : ?>
		<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" name="login" class="klogin-form-login" >
			<input type="hidden" name="option" value="com_kunena" />
			<input type="hidden" name="view" value="user" />
			<input type="hidden" name="task" value="login" />
			<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
			<?php echo JHTML::_( 'form.token' ); ?>

			<?php echo $this->params->get('pretext'); ?>
			<ul class="klogin-logoutfield">
				<li class="klogout-uname">
					<dl>
						<dd class="klogin-form-login-username">
							<input class="klogin-username inputbox" type="text"
								name="username"
								alt="username" size="18"
								value="<?php echo JText::_('MOD_KUNENALOGIN_USERNAME'); ?>"
								onblur = "if(this.value=='') this.value='<?php echo JText::_('MOD_KUNENALOGIN_USERNAME'); ?>';"
								onfocus = "if(this.value=='<?php echo JText::_('MOD_KUNENALOGIN_USERNAME'); ?>') this.value='';" />
						</dd>
						<dd class="klogin-form-login-password">
							<input class="klogin-passwd kinputbox" type="password"
								name="password" size="18" alt="password"
								value="<?php echo JText::_('MOD_KUNENALOGIN_PASSWORD'); ?>"
								onblur = "if(this.value=='') this.value='<?php echo JText::_('MOD_KUNENALOGIN_PASSWORD'); ?>';"
								onfocus = "if(this.value=='<?php echo JText::_('MOD_KUNENALOGIN_PASSWORD'); ?>') this.value='';"/>
						</dd>
					</dl>
				</li>
				<li class="klogout-pwd">
					<dl>
						<?php if($this->remember) : ?>
						<dd class="klogin-form-login-remember">
							<label for="klogin-remember">
								<input id="klogin-remember" class="klogin-remember" type="checkbox" name="remember" value="yes"
									alt="<?php echo JText::_('MOD_KUNENALOGIN_REMEMBER_ME') ?>" />
								<?php echo JText::_('MOD_KUNENALOGIN_REMEMBER_ME') ?>
							</label>
						</dd>
						<?php endif; ?>
						<dd>
							<input type="submit" name="Submit" class="kbutton" value="<?php echo JText::_('MOD_KUNENALOGIN_BUTTON_LOGIN') ?>" />
						</dd>
					</dl>
				</li>
				<li>
					<dl>
						<dd class="klogin-forgotpass">
							<a href="<?php echo $this->lostPasswordUrl ?>" rel="nofollow"><?php echo JText::_('COM_KUNENA_PROFILEBOX_FORGOT_PASSWORD') ?></a>
						</dd>
						<dd class="klogin-forgotname">
							<a href="<?php echo $this->lostUsernameUrl ?>" rel="nofollow"><?php echo JText::_('COM_KUNENA_PROFILEBOX_FORGOT_USERNAME') ?></a>
						</dd>
						<?php if ($this->registerUrl) : ?>
						<dd class="klogin-register">
							<a href="<?php echo $this->registerUrl ?>" rel="nofollow"><?php echo JText::_('COM_KUNENA_PROFILEBOX_CREATE_ACCOUNT') ?></a>
						</dd>
						<?php endif; ?>
					</dl>
				</li>
			</ul>
			<?php echo $this->params->get('posttext'); ?>
		</form>
	<?php endif; ?>
</div>