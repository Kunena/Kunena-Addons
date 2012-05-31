<?php
/**
 * Kunena Menu Plugin for Jomsocial
 * @package Kunena.plg_community_kunenamenu
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

class plgCommunityKunenaMenu extends CApplications {
	var $name = "My Forum Menu";
	var $_name = 'community_kunenamenu';

	function plgCommunityKunenaMenu(& $subject, $config) {
		//Load Language file.
		JPlugin::loadLanguage ( 'plg_community_kunenamenu', JPATH_ADMINISTRATOR );

		parent::__construct ( $subject, $config );
	}

	protected static function kunenaInstalled() {
		// Kunena detection and version check
		$minKunenaVersion = '2.0';
		if (! class_exists ( 'KunenaForum' ) || !KunenaForum::isCompatible($minKunenaVersion)) {
			return false;
		}
		return true;
	}

	function onSystemStart() {
		if (! self::kunenaInstalled ()) return;

		//initialize the toolbar object
		$toolbar = CFactory::getToolbar ();
		$user = JFactory::getUser();

		// Kunena online check
		if (! KunenaForum::enabled ()) {
			$toolbar->addGroup ( 'KUNENAMENU', JText::_ ( 'PLG_COMMUNITY_KUNENAMENU_KUNENA_OFFLINE' ), KunenaRoute::_ ( 'index.php?option=com_kunena' ) );
			return;
		}
		//adding new 'tab' 'Forum Settings' to JomSocial toolbar
		$toolbar->addGroup ( 'KUNENAMENU', JText::_ ( 'PLG_COMMUNITY_KUNENANENU_FORUM' ), 'index.php?option=com_kunena&view=user&layout=default&Itemid='.KunenaRoute::getItemid('index.php?option=com_kunena&view=user&layout=default') );
		if ( $this->params->get('sh_editprofile', 1) ) $toolbar->addItem ( 'KUNENAMENU', 'KUNENAMENU_EDITPROFILE', JText::_ ( 'PLG_COMMUNITY_KUNENAMENU_EDITPROFILE' ),'index.php?option=com_kunena&view=user&layout=edit&Itemid='.KunenaRoute::getItemid('index.php?option=com_kunena&view=user&layout=edit') );
		if ( $this->params->get('sh_myprofile', 1) ) $toolbar->addItem ( 'KUNENAMENU', 'KUNENAMENU_PROFILE', JText::_ ( 'PLG_COMMUNITY_KUNENAMENU_PROFILE' ), 'index.php?option=com_kunena&view=user&layout=default&Itemid='.KunenaRoute::getItemid('index.php?option=com_kunena&view=user&layout=default') );
		if ( $this->params->get('sh_myposts', 1) ) $toolbar->addItem ( 'KUNENAMENU', 'KUNENAMENU_POSTS', JText::_ ( 'PLG_COMMUNITY_KUNENAMENU_POSTS' ), 'index.php?option=com_kunena&view=topics&layout=posts&mode=recent&userid='.$user->id.'&sel=-1&Itemid='.KunenaRoute::getItemid('index.php?option=com_kunena&view=topics&layout=posts&mode=recent&userid='.$user->id.'&sel=-1') );
		if ( $this->params->get('sh_mysubscriptions', 1) ) $toolbar->addItem ( 'KUNENAMENU', 'KUNENAMENU_SUBSCRIBES', JText::_ ( 'PLG_COMMUNITY_KUNENAMENU_SUBSCRIBTIONS' ), 'index.php?option=com_kunena&view=topics&layout=user&mode=subscriptions&sel=-1&Itemid='.KunenaRoute::getItemid('index.php?option=com_kunena&view=topics&layout=user&mode=subscriptions&sel=-1') );
		if ( $this->params->get('sh_myfavorites', 1) ) $toolbar->addItem ( 'KUNENAMENU', 'KUNENAMENU_FAVORITES', JText::_ ( 'PLG_COMMUNITY_KUNENAMENU_FAVORITES' ), 'index.php?option=com_kunena&view=topics&layout=user&mode=favorites&sel=-1&Itemid='.KunenaRoute::getItemid('index.php?option=com_kunena&view=topics&layout=user&mode=favorite&sel=-1s') );

	}
}
