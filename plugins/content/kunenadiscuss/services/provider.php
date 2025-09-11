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

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Kunena\Plugin\Content\Kunenadiscuss\Extension\Kunenadiscuss;

return new class implements ServiceProviderInterface
{
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     * @since   2.0.0
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $config  = (array) PluginHelper::getPlugin('content', 'kunenadiscuss');
                $subject = $container->get(DispatcherInterface::class);
                $plugin  = new Kunenadiscuss($config);

                $plugin->traitSetDispatcher($subject);
                $plugin->setApplication(Factory::getApplication());
                $plugin->setDatabase($container->get('DatabaseDriver'));

                return $plugin;
            }
        );
    }
};
