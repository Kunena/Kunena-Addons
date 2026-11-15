<?php

/**
 * Kunena Latest Module
 *
 * @package       Kunena.mod_kunenalatest
 *
 * @Copyright     @kunenacopyright@
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

\defined('_JEXEC') or die();

use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The service provider of the module: Joomla runs its dispatcher (src/Dispatcher/Dispatcher.php).
 */
return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     */
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new ModuleDispatcherFactory('\\Kunena\\Module\\KunenaLatest'));
        $container->registerServiceProvider(new Module());
    }
};
