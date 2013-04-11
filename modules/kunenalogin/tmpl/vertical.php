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
<div class="klogin-vert">

<?php if($this->type == 'logout') : ?>
<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" name="login">
	<input type="hidden" name="option" value="com_kunena" />
	<input type="hidden" name="view" value="user" />
	<input type="hidden" name="task" value="logout" />
	<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>

	<?php if ($this->params->get('greeting')) : ?>
	<div class="klogin-hiname">
		<?php echo JText::sprintf('MOD_KUNENALOGIN_HINAME','<strong>'.$this->me->getLink ( $this->me->getName()).'</strong>', $this->me->getName() ); ?>
	</div>
	<?php endif; ?>
	<div class="klogin-avatar">
		<?php if ($this->params->get('showav')) echo $this->kunenaAvatar( $this->me->userid ) ?>
	</div>
	<div>
	<?php if ($this->params->get('lastlog')) : ?>
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
	<div class="klogin-links">
		<input type="submit" name="Submit" class="kbutton" value="<?php echo JText::_('MOD_KUNENALOGIN_BUTTON_LOGOUT'); ?>" />
	</div>
	<div>
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
	<fieldset class="input">
	<p class="klogin-form-login-username">
		<label for="klogin-username"><?php echo JText::_('MOD_KUNENALOGIN_USERNAME') ?></label>
		<input id="klogin-username" class="klogin-username" type="text" name="username" class="kinputbox" alt="username" size="18" />
	</p>
	<p class="klogin-form-login-password">
		<label for="klogin-passwd"><?php echo JText::_('MOD_KUNENALOGIN_PASSWORD') ?></label>
		<input id="klogin-passwd" class="klogin-passwd" type="password" name="password" class="kinputbox" size="18" alt="password" />
	</p>
	<?php if($this->remember) : ?>
	<p class="klogin-form-login-remember"><label for="klogin-remember">
	<input id="klogin-remember" class="klogin-remember" type="checkbox" name="remember" value="yes" alt="<?php echo JText::_('MOD_KUNENALOGIN_REMEMBER_ME') ?>" />
		<?php echo JText::_('MOD_KUNENALOGIN_REMEMBER_ME') ?></label>
	</p>
	<?php endif; ?>
	<input type="submit" name="Submit" class="kbutton" value="<?php echo JText::_('MOD_KUNENALOGIN_BUTTON_LOGIN') ?>" />
	</fieldset>
	<ul class="klogin-logoutlink">
		<li class="klogin-forgotpass">
			<a href="<?php echo $this->lostPasswordUrl ?>" rel="nofollow"><?php echo JText::_('COM_KUNENA_PROFILEBOX_FORGOT_PASSWORD') ?></a>
		</li>
		<li class="klogin-forgotname">
			<a href="<?php echo $this->lostUsernameUrl ?>" rel="nofollow"><?php echo JText::_('COM_KUNENA_PROFILEBOX_FORGOT_USERNAME') ?></a>
		</li>
		<?php if ($this->registerUrl) : ?>
		<li class="klogin-register">
			<a href="<?php echo $this->registerUrl ?>" rel="nofollow"><?php echo JText::_('COM_KUNENA_PROFILEBOX_CREATE_ACCOUNT') ?></a>
		</li>
		<?php endif; ?>
	</ul>
	<?php echo $this->params->get('posttext'); ?>
</form>
	<?php endif; ?>

</div>