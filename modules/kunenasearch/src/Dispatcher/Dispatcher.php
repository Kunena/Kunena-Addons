<?php

/**
 * Kunena Search Module
 *
 * @package       Kunena.mod_kunenasearch
 *
 * @Copyright     @kunenacopyright@
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

namespace Kunena\Module\KunenaSearch\Site\Dispatcher;

\defined('_JEXEC') or die();

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Forum\KunenaForum;
use Kunena\Module\KunenaSearch\Site\ModuleKunenaSearch;

/**
 * Displays the module: the checks of Kunena (installed, version, online), then the module.
 */
class Dispatcher extends AbstractModuleDispatcher
{
    /**
     * The Kunena version the module needs.
     */
    private const MIN_KUNENA_VERSION = '8.0';

    /**
     * Dispatch the module.
     *
     * @return  void
     */
    public function dispatch()
    {
        $this->loadLanguage();

        // Kunena detection and version check
        if (!class_exists(KunenaForum::class) || !KunenaForum::isCompatible(self::MIN_KUNENA_VERSION)) {
            echo Text::sprintf('MOD_KUNENASEARCH_KUNENA_NOT_INSTALLED', self::MIN_KUNENA_VERSION);

            return;
        }

        // Kunena online check
        if (!KunenaForum::enabled()) {
            echo Text::_('MOD_KUNENASEARCH_KUNENA_OFFLINE');

            return;
        }

        // Kunena is loaded, so that the module also works on the pages of other components.
        $this->app->bootComponent('com_kunena');

        $data = $this->getLayoutData();

        (new ModuleKunenaSearch($data['module'], $data['params']))->display();
    }
}
