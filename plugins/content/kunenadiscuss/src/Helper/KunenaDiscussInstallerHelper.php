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

namespace Kunena\Plugin\Content\Kunenadiscuss\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

/**
 * Installer Script Helper class
 * Called from installation script
 *
 * @since  0.0.0
 */
class KunenaDiscussInstallerHelper
{
    /**
     * Get the version of the current installed plugin /module / component
     *
     * @param   string  $type     Type of the element
     * @param   string  $element  Element name
     * @param   string  $folder   Plugin folder
     *
     * @return string Installed version number
     */
    public static function getInstalledVersion($type, $element, $folder = null)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('manifest_cache'));
        $query->from($db->quoteName('#__extensions'));
        $query->where($db->quoteName('type') . ' = ' . $db->quote($type));
        $query->where($db->quoteName('element') . ' = ' . $db->quote($element));

        if (!is_null($folder)) {
            $query->where(
                $db->quoteName('folder') . ' = ' . $db->quote($folder)
            );
        }

        $db->setQuery($query);

        $return = json_decode($db->loadResult());

        return $return->version;
    }

    /**
     * Check if the current Joomla version is equal or greater then the required Joomla version
     *
     * @param   string                      $type    Type of the element
     * @param   JInstallerAdapterComponent  $parent  The class calling this method
     *
     * @return boolean
     */
    public static function checkMinimumJoomlaVersion($type, $parent)
    {
        $joomlaVersion = new Version;
        $installer = method_exists($parent, 'getParent') ? $parent->getParent() : $parent->parent;

        // Extensions manifest file minimum Joomla version
        $minimumJoomlaRelease = $parent->manifest->attributes()->version;

        // Abort if the current Joomla release is older
        if (version_compare($joomlaVersion->getShortVersion(), $minimumJoomlaRelease, 'lt')) {
            if (version_compare($joomlaVersion->getShortVersion(), '3.8', 'lt')) {
                Factory::getApplication()->enqueueMessage(
                    'Cannot install ' . $parent->get('element')
                        . '. Minimum required Joomla version is ' . $minimumJoomlaRelease
                        . '. Your version is ' . $joomlaVersion->getShortVersion(),
                    'error'
                );
            } else {
                Factory::getApplication()->enqueueMessage(
                    'Cannot install ' . $parent->get('element')
                        . '. Minimum required Joomla version is ' . $minimumJoomlaRelease,
                    'error'
                );
            }

            // Remove any messages / description to avoid confusion for user
            $installer->set('message', '');

            return false;
        }

        return true;
    }

    /**
     * Method to remove files
     *
     * @param   array    $varMaintenanceVariables  Array with maintenance variables
     * @param   string   $installedVersion         The current version
     * @param   boolean  $stopOnError              Should this method stop the installation on an error or display the error and continue?
     *
     * @return void
     */
    public static function doMaintenance($varMaintenanceVariables, $installedVersion, $stopOnError = true)
    {
        if (array_key_exists('remove_files', $varMaintenanceVariables)) {
            self::removeFiles($varMaintenanceVariables['remove_files'], $installedVersion);
        }

        if (array_key_exists('remove_directories', $varMaintenanceVariables)) {
            self::removeDirectories($varMaintenanceVariables['remove_directories'], $installedVersion);
        }

        if (array_key_exists('installation_messages', $varMaintenanceVariables)) {
            self::installationMessages($varMaintenanceVariables['installation_messages'], $installedVersion);
        }

        if (array_key_exists('component_warnings', $varMaintenanceVariables)) {
            self::componentWarnings($varMaintenanceVariables['component_warnings'], $installedVersion);
        }

        if (array_key_exists('rename_files', $varMaintenanceVariables)) {
            self::renameFiles($varMaintenanceVariables['rename_files'], $installedVersion);
        }

        if (array_key_exists('database_updates', $varMaintenanceVariables)) {
            self::updateDatabase($varMaintenanceVariables['database_updates'], $installedVersion, $stopOnError);
        }

        if (array_key_exists('update_sites', $varMaintenanceVariables)) {
            self::removeUpdateSite($varMaintenanceVariables['update_sites'], $installedVersion);
        }
    }

    /**
     * Method to remove files
     *
     * @param   array   $varRemoveFiles    Files to remove
     * @param   string  $installedVersion  Version number of installed plugin, module, component
     *
     * @return void
     */
    private static function removeFiles($varRemoveFiles, $installedVersion)
    {
        if (!empty($varRemoveFiles)) {
            $application = Factory::getApplication();

            foreach ($varRemoveFiles as $removeFile) {
                if (version_compare($installedVersion, $removeFile['version'], $removeFile['compare'])) {
                    if (file_exists($removeFile['file'])) {
                        if (File::delete($removeFile['file'])) {
                            $application->enqueueMessage(
                                'Obsolete (left-over from previous release) file "' . $removeFile['file']
                                    . '" successfully removed.',
                                'Message'
                            );
                        } else {
                            $application->enqueueMessage(
                                'File "' . $removeFile['file']
                                    . '" (left-over from previous release) could not be removed, please remove manually.',
                                'Warning'
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Method to display installation messages
     *
     * @param   array   $varInstallationMessages    Installation Messages
     * @param   string  $installedVersion  	        Version number of installed plugin, module, component
     *
     * @return void
     */
    private static function installationMessages($varInstallationMessages, $installedVersion)
    {
        if (!empty($varInstallationMessages)) {
            $application = Factory::getApplication();

            foreach ($varInstallationMessages as $installationMessage) {
                if (version_compare($installedVersion, $installationMessage['version'], $installationMessage['compare'])) {
                    $application->enqueueMessage($installationMessage['message'], $installationMessage['type']);
                }
            }
        }
    }

    /**
     * Method to display component warnings
     *
     * @param   array   $varComponentWarnings    Warnings
     * @param   string  $installedVersion  	     Version number of installed plugin, module, component
     *
     * @return void
     */
    private static function componentWarnings($varComponentWarnings, $installedVersion)
    {
        if (!empty($varComponentWarnings)) {
            $application = Factory::getApplication();

            foreach ($varComponentWarnings as $componentWarning) {
                if (version_compare($installedVersion, $componentWarning['version'], $componentWarning['compare'])) {
                    if (file_exists($componentWarning['component'])) {
                        $application->enqueueMessage($componentWarning['message'], $componentWarning['type']);
                    }
                }
            }
        }
    }

    /**
     * Method to remove directories
     *
     * @param   array   $varRemoveDirectories    Directories to remove
     * @param   string  $installedVersion  	     Version number of installed plugin, module, component
     *
     * @return void
     */
    private static function removeDirectories($varRemoveDirectories, $installedVersion)
    {
        if (!empty($varRemoveDirectories)) {
            $application = Factory::getApplication();

            foreach ($varRemoveDirectories as $removeDirectory) {
                if (version_compare($installedVersion, $removeDirectory['version'], $removeDirectory['compare'])) {
                    if (is_dir($removeDirectory['folder'])) {
                        if (Folder::delete($removeDirectory['folder'])) {
                            $application->enqueueMessage(
                                'Obsolete (left-over from previous release) directory "' . $removeDirectory['folder']
                                    . '" successfully removed.',
                                'Message'
                            );
                        } else {
                            $application->enqueueMessage(
                                'Directory "' . $removeDirectory['folder']
                                    . '" (left-over from previous release) could not be removed, please remove manually.',
                                'Warning'
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Method to update database(s)
     *
     * @param   array    $varUpdateDatabase    SQL queries
     * @param   string   $installedVersion     Version number of installed plugin, module, component
     * @param   boolean  $stopOnError          Should this method stop the installation on an error or display the error and continue?
     *
     * @return void|boolean
     */
    private static function updateDatabase($varUpdateDatabase, $installedVersion, $stopOnError = true)
    {
        if (!empty($varUpdateDatabase)) {
            $application = Factory::getApplication();
            $db          = Factory::getDbo();
            $error       = false;

            foreach ($varUpdateDatabase as $UpdateDatabase) {
                if (version_compare($installedVersion, $UpdateDatabase['version'], $UpdateDatabase['compare'])) {
                    try {
                        $query = $UpdateDatabase['query'];
                        $db->setQuery($query);
                        $db->execute();
                    } catch (\Exception $e) {
                        $application->enqueueMessage($e->getMessage(), 'error');
                        $error = true;

                        if ($stopOnError) {
                            return false;
                        }
                    }

                    if (!$error) {
                        $application->enqueueMessage($UpdateDatabase['message'], $UpdateDatabase['type']);
                    }
                }
            }
        }
    }

    /**
     * Method to display postflight messages after installation or update
     *
     * @param   array   					$varPostflightMessages  Message
     * @param   string                      $type                   The action being performed
     * @param   JInstallerAdapterComponent  $parent                 The class calling this method
     * @param   bool						$append                 append or replace message
     *
     * @return void
     */
    public static function postflightMessages($varPostflightMessages, $type, $parent, $append = true)
    {
        if (!empty($varPostflightMessages)) {
            $application = Factory::getApplication();
            $installer = method_exists($parent, 'getParent') ? $parent->getParent() : $parent->parent;
            $append ? $message = $installer->get('message') : $message = '';

            foreach ($varPostflightMessages as $postflightMessage) {
                $message .= $postflightMessage['message'];
            }

            $installer->set('message', $message);
        }
    }

    /**
     * Method to rename files
     *
     * @param   array   $varRenameFiles    Files to remove
     * @param   string  $installedVersion  Version number of installed plugin, module, component
     *
     * @return void
     */
    private static function renameFiles($varRenameFiles, $installedVersion)
    {
        if (!empty($varRenameFiles)) {
            $application = Factory::getApplication();

            foreach ($varRenameFiles as $renameFile) {
                if (version_compare($installedVersion, $renameFile['version'], $renameFile['compare'])) {
                    if (file_exists($renameFile['oldname'])) {
                        if (File::move($renameFile['oldname'], $renameFile['newname'])) {
                            $application->enqueueMessage(
                                'File "' . $renameFile['oldname']
                                    . '" successfully renamed to file "' . $renameFile['newname'] . '"',
                                'Message'
                            );
                        } else {
                            $application->enqueueMessage(
                                'File "' . $renameFile['file']
                                    . '" (left-over from previous release) could not be renamed, please rename manually to file "'
                                    . $renameFile['newname'] . '"',
                                'Warning'
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Method to remove the URL for the Update Site table
     *
     * @param   array   $varRemoveLocations  Files to remove
     * @param   string  $installedVersion    Version number of installed plugin, module, component
     *
     * @return void
     */
    private static function removeUpdateSite($varRemoveLocations, $installedVersion)
    {
        if (!empty($varRemoveLocations)) {
            $application = Factory::getApplication();
            $db = Factory::getDbo();

            foreach ($varRemoveLocations as $removeLocation) {
                if (version_compare($installedVersion, $removeLocation['version'], $removeLocation['compare'])) {
                    // Remove obsolete update site location from database (if found)
                    $query = $db->getQuery(true);
                    $query
                        ->select($db->quoteName(array('update_site_id', 'location')))
                        ->from($db->quoteName('#__update_sites'))
                        ->where($db->quoteName('location') . ' = ' . $db->quote($removeLocation['updatesite']));

                    $db->setQuery($query);
                    $row = $db->loadRow();

                    if (!empty($row)) {
                        // Remove record from #__update_sites
                        $query = $db->getQuery(true);
                        $query
                            ->delete($db->quoteName('#__update_sites'))
                            ->where($db->quoteName('update_site_id') . ' = ' . $db->quote($row[0]));

                        $db->setQuery($query);
                        $result_us = $db->execute();

                        // Remove record from #__update_sites_extensions
                        $query = $db->getQuery(true);
                        $query
                            ->delete($db->quoteName('#__update_sites_extensions'))
                            ->where($db->quoteName('update_site_id') . ' = ' . $db->quote($row[0]));

                        $db->setQuery($query);
                        $result_use = $db->execute();

                        if ($result_us && $result_use) {
                            $application->enqueueMessage(
                                'Obsolete (left-over from previous release) Update Site "' . $removeLocation['updatesite']
                                    . '" successfully removed',
                                'Message'
                            );
                        }
                    }
                }
            }
        }
    }
}
