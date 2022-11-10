<?php

/**
 * Kunena Discuss Plugin
 *
 * @package       Kunena.plg_content_kunenadiscuss
 *
 * @copyright     @kunenacopyright@
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
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
        if (strtolower($type) == 'update') {
            // Load all maintenance variables
            $this->setPreFlightMaintenanceVariables();

            if (file_exists(JPATH_SITE . '/plugins/content/kunenadiscuss/src/Helper/KunenaDiscussInstallerHelper.php')) {
                $this->installedVersion = KunenaDiscussInstallerHelper::getInstalledVersion('plugin', 'kunenadiscuss');

                // Do preflight maintenance
                KunenaDiscussInstallerHelper::doMaintenance($this->preflightVariables, $this->installedVersion);
            } else {
                // We are on a version that doesn't have the Installer Helper installed, so pré 6.0.0
                // We need to cleanup one-time manually
                $remove_directories = [
                    JPATH_SITE . '/plugins/content/kunenadiscuss/css',
                    JPATH_SITE . '/plugins/content/kunenadiscuss/language',
                    JPATH_SITE . '/plugins/content/kunenadiscuss/tmpl',
                    JPATH_SITE . '/media/plg_content_kunenadiscuss',
                ];

                if (isset($this->preflightVariables['remove_directories'])) {
                    foreach ($remove_directories as $directory) {
                        $application = Factory::getApplication();

                        if (is_dir($directory)) {
                            if (Folder::delete($directory)) {
                                $application->enqueueMessage(
                                    'Obsolete (left-over from previous release) directory "' . $directory
                                        . '" successfully removed.',
                                    'Message'
                                );
                            } else {
                                $application->enqueueMessage(
                                    'Directory "' . $directory
                                        . '" (left-over from previous release) could not be removed, please remove manually.',
                                    'Warning'
                                );
                            }
                        }
                    }
                }
            }
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
