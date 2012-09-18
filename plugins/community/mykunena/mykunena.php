<?php
/**
 * My Kunena Plugin for Jomsocial
 * @package Kunena.plg_community_mykunena
 *
 * @copyright (C) 2008 - 2012 Kunena Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.kunena.org
 **/
defined ( '_JEXEC' ) or die ();

$path = JPATH_ROOT . '/components/com_community/libraries/core.php';
if (! is_file ( $path ))
	return;
require_once $path;

class plgCommunityMyKunena extends CApplications {
	var $name = "My Forum Posts";
	var $_name = 'community_mykunena';

	function plgCommunityMyKunena(& $subject, $config) {
		//Load Language file.
		JPlugin::loadLanguage ( 'plg_community_mykunena', JPATH_ADMINISTRATOR );

		parent::__construct ( $subject, $config );
	}

	protected static function kunenaOnline() {
		// Kunena detection and version check
		$minKunenaVersion = '2.0';
		if (! class_exists ( 'KunenaForum' ) || !KunenaForum::isCompatible($minKunenaVersion)) {
			return false;
		}
		// Kunena online check
		if (! KunenaForum::enabled ()) {
			return false;
		}
		KunenaForum::setup();

		return true;
	}

	function onProfileDisplay() {
		if (! self::kunenaOnline ()) return;

		$document = JFactory::getDocument ();
		$document->addStyleSheet ( JURI::base () . 'plugins/community/mykunena/style.css' );

		$user = CFactory::getRequestUser ();
		$messages = array();
		if ($user->id) {
			$params = array('user'=>$user->id, 'starttime'=>-1);
			list($total, $messages) = KunenaForumMessageHelper::getLatestMessages(false, 0, $this->params->get ( 'count', 5 ), $params);
		}

		$caching = $this->params->get ( 'cache', 1 );
		if ($caching) {
			$app = JFactory::getApplication ();
			$caching = $app->getCfg ( 'caching' );
		}

		$cache = JFactory::getCache ( 'community' );
		$cache->setCaching ( $caching );
		$callback = array ($this, '_getMyKunenaHTML' );
		$content = $cache->call ( $callback, $user, $messages );

		return $content;
	}

	function _getMyKunenaHTML($user, $items) {
		ob_start ();
		$template = KunenaFactory::getTemplate ();
		if ( !$items ) : ?>

		<div class="icon-nopost"><img src="<?php echo JURI::base (); ?>plugins/community/mykunena/no-post.gif" alt="" /></div>
		<div class="content-nopost"><?php echo JText::sprintf ( 'PLG_COMMUNITY_MYKUNENA_NO_POSTS', $user->getDisplayName() ); ?></div>

		<?php else : ?>

		<div id="community-mykunena-wrap">
			<ul class="cList clrfix">
			<?php
			foreach ( $items as $item ) :
				$postDate = new JDate ( $item->time );
			?>
				<li>
					<div class="content">
						<a href="<?php echo KunenaRoute::_ ( "index.php?option=com_kunena&view=topic&catid={$item->catid}&id={$item->thread}&mesid={$item->id}" ); ?>" class="kjsubject"><?php echo $item->getTopic()->displayField('subject'); ?></a> <?php echo JText::_('PLG_COMMUNITY_MYKUNENA_POST_IN'); ?>
						<a href="<?php echo KunenaRoute::_ ( "index.php?option=com_kunena&view=category&catid={$item->catid}" ); ?>" class="kjcategory"><?php echo $item->getCategory()->displayField('name'); ?></a> <?php echo JText::_('PLG_COMMUNITY_MYKUNENA_POST_ON'); ?>
						<span class="kjdate"><?php echo version_compare(JVERSION, '1.7','>') ? $postDate->Format ( JText::_ ( 'DATE_FORMAT_LC2' ) ) : $postDate->toFormat ( JText::_('DATE_FORMAT_LC2')) ?></span>
					</div>
				</li>
			<?php endforeach; ?>
			</ul>
		</div>

		<?php endif;

		$contents = ob_get_contents ();
		ob_end_clean ();
		return $contents;
	}
}
