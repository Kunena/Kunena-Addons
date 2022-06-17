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

use Kunena\Plugin\Content\Kunenadiscuss\Helper\KunenaDiscussInstallerHelper;

defined('_JEXEC') or die();
/**
 * @package     Kunena
 *
 * @since       Kunena
 */
class PlgContentKunenadiscussInstallerScript
{
	/**
	 * @var string
	 */
	protected $installedVersion;

	/**
	 * @var array
	 */
	protected $maintenanceVariables;

	/**
	 * @var array
	 */
	protected $preflightVariables;

	/**
	 * Method to run before the install routine.
	 *
	 * @param   string                      $type    The action being performed
	 * @param   JInstallerAdapterComponent  $parent  The class calling this method
	 *
	 * @return  void|boolean
	 */
	public function preflight($type, $parent)
	{
		// Check for minimum required Joomla! version
		if (!KunenaDiscussInstallerHelper::checkMinimumJoomlaVersion($type, $parent)) {
			// We are not on minimum Joomla! version: get out of here...
			return false;
		}

		$type = strtolower($type);

		if ($type == 'update') {
			$this->installedVersion = KunenaDiscussInstallerHelper::getInstalledVersion('plugin', 'kunenadiscuss');

			// Load all maintenance variables
			$this->setPreFlightMaintenanceVariables();

			// Do preflight maintenance
			KunenaDiscussInstallerHelper::doMaintenance($this->preflightVariables, $this->installedVersion);
		}
	}

	/**
	 * Code to execute on plugin update, used for cleaning left-overs from previous versions
	 *
	 * @param   object  $adapter  Adapter instance
	 *
	 * @return  void
	 */
	public function update($adapter)
	{
		$newVersion = $adapter->manifest->version;

		// Load all maintenance variables
		$this->setMaintenanceVariables();

		// Rename all configured files
		KunenaDiscussInstallerHelper::doMaintenance($this->maintenanceVariables, $this->installedVersion);
	}

	/**
	 * Set the maintenance variables
	 *
	 * @return void
	 */
	public function setMaintenanceVariables()
	{
		$this->maintenanceVariables['rename_files'] = [];

		$this->maintenanceVariables['delete_files'] = [];

		$this->maintenanceVariables['remove_directories'] = [];

		$this->maintenanceVariables['installation_messages'] = [];

		$this->maintenanceVariables['component_warnings'] = [];

		$this->maintenanceVariables['update_sites'] = [];
	}

	/**
	 * Set the maintenance variables
	 *
	 * @return void
	 */
	public function setPreFlightMaintenanceVariables()
	{
		$this->preflightVariables['remove_directories'] = [
			[
				'folder' => JPATH_SITE . '/plugins/content/kunenadiscuss/css',
				'version' => '6.0.1',
				'compare' => '<'
			],
			[
				'folder' => JPATH_SITE . '/plugins/content/kunenadiscuss/language',
				'version' => '6.0.1',
				'compare' => '<'
			],
			[
				'folder' => JPATH_SITE . '/plugins/content/kunenadiscuss/tmpl',
				'version' => '6.0.1',
				'compare' => '<'
			],
			[
				'folder' => JPATH_SITE . '/media/plg_content_kunenadiscuss',
				'version' => '6.0.1',
				'compare' => '<'
			],
		];
	}
}
