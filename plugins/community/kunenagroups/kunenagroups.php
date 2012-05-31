<?php
/**
 * Kunena Groups Plugin for Jomsocial
 * @package Kunena.plg_community_kunenagroups
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

class plgCommunityKunenaGroups extends CApplications {
	var $name = "Kunena Groups";
	var $_name = 'community_kunenagroups';

	function plgCommunityKunenaGroups(& $subject, $config) {
		//Load Language file.
		JPlugin::loadLanguage ( 'plg_community_kunenagroups', JPATH_ADMINISTRATOR );

		parent::__construct ( $subject, $config );
	}

	protected static function kunenaOnline() {
		// Kunena detection and version check
		$minKunenaVersion = '2.0';
		if (! class_exists ( 'KunenaForum' ) || !KunenaForum::isCompatible($minKunenaVersion)) {
			JFactory::getApplication()->enqueueMessage(JText::sprintf('PLG_COMMUNITY_KUNENAGROUPS_KUNENA_NOT_INSTALLED', $minKunenaVersion),'notice');
			return false;
		}
		// Kunena installed check
		if (! KunenaForum::installed ()) {
			JFactory::getApplication()->enqueueMessage(JText::_('PLG_COMMUNITY_KUNENAGROUPS_KUNENA_OFFLINE'),'notice');
			return false;
		}
		KunenaForum::setup();

		return true;
	}

	function onGroupCreate( $group ) {
		if (! self::kunenaOnline ()) return;
		if (JRequest::getInt('kunenaforum', 0) < 0) return;

		$catid = self::getForumCategory($group->categoryid);
		if ($catid === false) return;

		$category = new KunenaForumCategory();
		$category->set('parent_id', $catid);
		$category->set('name', $group->name);
		$category->set('description', $group->description);
		$category->set('headerdesc', $group->description);
		$category->set('accesstype', 'jomsocial');
		$category->set('access', $group->id);
		$category->set('published', $group->published);
		$success = $category->save();
		if (!$success) {
			JFactory::getApplication ()->enqueueMessage ( JText::sprintf('PLG_COMMUNITY_KUNENAGROUPS_GROUP_CREATE_FAILED', 'notice'));
			return;
		}

		$category->setModerator($group->ownerid, 1);
	}

	function onGroupDisable( $group ) {
		if (! self::kunenaOnline ()) return;

		$categories = KunenaForumCategoryHelper::getCategoriesByAccess('jomsocial', $group->id);
		foreach ($categories as $category) {
			$category->set('published', 0);
			$success = $category->save();
			if (!$success) {
				JFactory::getApplication ()->enqueueMessage ( JText::sprintf('PLG_COMMUNITY_KUNENAGROUPS_GROUP_SAVE_FAILED', 'notice'));
			}
		}
	}

	function onAfterGroupDelete( $group ) {
		if (! self::kunenaOnline ()) return;

		$categories = KunenaForumCategoryHelper::getCategoriesByAccess('jomsocial', $group->id);
		foreach ($categories as $category) {
			$success = $category->delete();
			if (!$success) {
				JFactory::getApplication ()->enqueueMessage ( JText::sprintf('PLG_COMMUNITY_KUNENAGROUPS_GROUP_DELETE_FAILED', 'notice'));
			}
		}
	}

	function onGroupJoin( $group, $memberid ) {
		if (! self::kunenaOnline ()) return;

		$access = KunenaFactory::getAccessControl();
		$access->clearCache();
	}

	function onGroupJoinApproved( $group, $memberid ) {
		if (! self::kunenaOnline ()) return;

		$access = KunenaFactory::getAccessControl();
		$access->clearCache();
	}

	function onGroupLeave( $group, $memberid ) {
		if (! self::kunenaOnline ()) return;

		$access = KunenaFactory::getAccessControl();
		$access->clearCache();
	}

	function onAfterEventsUserBlocked() {
		if (! self::kunenaOnline ()) return;

		$access = KunenaFactory::getAccessControl();
		$access->clearCache();
	}

	function onFormDisplay( $formName ) {
		$fields = array();
		if (! self::kunenaOnline ()) return $fields;

		if( $formName == 'jsform-groups-forms' || $formName == 'jsform-groups-form' ) {
			$groupid = JRequest::getInt('groupid', 0);
			$forum = 0;
			if (!$groupid) {
				$forum = 0;
			} else {
				$group = JTable::getInstance('Group','CTable');
				$group->load( $groupid );

				$categories = KunenaForumCategoryHelper::getCategoriesByAccess( 'jomsocial', $group->id );
				foreach ($categories as $category) {
					$forum = $forum || $category->published;
				}
			}

			$element = new CFormElement();
			$element->label = JText::_('PLG_COMMUNITY_KUNENAGROUPS_FORUMS');
			$element->html = '<div><input type="radio" name="kunenaforum" id="kunenaforum-disabled" value="-1" '. (!$forum ? 'checked="checked"' : '') . ' />
				<label for="kunenaforum-disabled" class="label lblradio">'. JText::_('PLG_COMMUNITY_KUNENAGROUPS_FORUMS_DISABLE') .'</label></div>
				<div><input type="radio" name="kunenaforum" id="kunenaforum-members" value="0" '. ($forum ? 'checked="checked"' : '') . ' />
				<label for="kunenaforum-members" class="label lblradio">'. JText::_('PLG_COMMUNITY_KUNENAGROUPS_FORUMS_ALLOW').'</label></div>
				<div class="small">'. JText::_('PLG_COMMUNITY_KUNENAGROUPS_FORUMS_NOTE').'</div>';
// TODO: add link to category manager
/*			if ($forum) {
				$element->html .= '<div><a href="'.KunenaRoute::_('index.php?option=com_kunena&view=category&layout=manage').'">'.JText::_('PLG_COMMUNITY_KUNENAGROUPS_FORUMS_MANAGE').'</a></div>';
			}
*/
			$element->position = 'after';
			$fields[] = $element;
		}
		return $fields;
	}

	function onFormSave( $formName ) {
		if (! self::kunenaOnline ()) return true;

		if( $formName == 'jsform-groups-forms' ) {
			$groupid = JRequest::getInt('groupid', 0);
			if (!$groupid) return true;

			$group = JTable::getInstance('Group','CTable');
			$group->load( $groupid );

			$parent = self::getForumCategory($group->categoryid);
			$published = (JRequest::getInt('kunenaforum', 0) == 0 && $group->published);
			$categories = KunenaForumCategoryHelper::getCategoriesByAccess('jomsocial', $group->id);
			foreach ($categories as $category) {
				if ($category->parent == $parent) {
					$category->set('name', $group->name);
					$category->set('description', $group->description);
					$category->set('headerdesc', $group->description);
					$parent = -1;
				}
				$category->set('published', $published);
				$success = $category->save();
				if (!$success) {
					JFactory::getApplication ()->enqueueMessage ( JText::sprintf('PLG_COMMUNITY_KUNENAGROUPS_GROUP_SAVE_FAILED', 'notice'));
				}
			}
			if (empty($categories) && $published) {
				self::onGroupCreate($group);
			}
		}
		return true;
	}

	protected function getForumCategory($catid) {
		static $trans = array(' ', '');

		// Default Kunena category to put new topics into
		$default = intval ( $this->params->get ( 'default_category', 0 ) );
		// Category pairs will be always allowed
		$categoryPairs = explode ( ';', strtr($this->params->get ( 'category_mapping', '' ), $trans ) );
		$categoryMap = array ();
		foreach ( $categoryPairs as $pair ) {
			$pair = explode ( ',', $pair );
			$key = isset ( $pair [0] ) ? intval ( $pair [0] ) : 0;
			$value = isset ( $pair [1] ) ? intval ( $pair [1] ) : 0;
			if ($key > 0)
				$categoryMap [$key] = $value;
		}
		$allowCategories = explode ( ',', strtr( $this->params->get ( 'allow_categories', '' ), $trans ) );
		$denyCategories = explode ( ',', strtr( $this->params->get ( 'deny_categories', '' ), $trans ) );

		if (! is_numeric ( $catid ) || intval ( $catid ) == 0) {
			return false;
		}

		if (!empty ( $categoryMap ) && isset ( $categoryMap [$catid] )) {
			$forumcatid = $categoryMap [$catid];
			if (!$forumcatid) {
				return false;
			}
			return $forumcatid;
		}

		if (!$default) {
			return false;
		}

		if (in_array('0', $allowCategories ) || in_array($catid, $allowCategories )) {
			return $default;
		}
		if (in_array('0', $denyCategories ) || in_array($catid, $denyCategories )) {
			return false;
		}

		return $default;
	}
}
