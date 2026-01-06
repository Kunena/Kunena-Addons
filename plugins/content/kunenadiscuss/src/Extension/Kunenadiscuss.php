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

namespace Kunena\Plugin\Content\Kunenadiscuss\Extension;

\defined('_JEXEC') or die();

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Event\Content\AfterDisplayEvent;
use Joomla\CMS\Event\Content\BeforeDisplayEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\SubscriberInterface;
use Kunena\Forum\Libraries\Config\KunenaConfig;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Forum\KunenaForum;
use Kunena\Forum\Libraries\Template\KunenaTemplate;
use Kunena\Forum\Libraries\User\KunenaUser;
use Kunena\Plugin\Content\Kunenadiscuss\Helper\KunenaDiscussHelper;

/**
 * Class plgContentKunenaDiscuss
 * @since Kunena
 */
class Kunenadiscuss extends CMSPlugin implements SubscriberInterface, DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Load language file for front-end translations
     *
     * @var boolean
     */
    protected $autoloadLanguage = \true;

    /**
     * Associative array to hold results of the plugin.
     *
     * @var   array
     * @since Kunena
     *
     */
    public static array $plgDisplay = [];

    /**
     * @var   boolean
     * @since Kunena
     */
    public static bool $includedCss = \false;

    /**
     * @var   boolean
     * @since Kunena
     *
     */
    public static bool $inevent = \false;

    /**
     * @var   ?KunenaDiscussHelper
     * @since Kunena
     *
     */
    public ?KunenaDiscussHelper $helper = \null;

    /**
     * @var   ?KunenaUser
     * @since Kunena
     *
     */
    public ?KunenaUser $user = \null;

    /**
     * @var   ?KunenaConfig
     * @since Kunena
     *
     */
    public ?KunenaConfig $config = \null;

    /**
     * @var   boolean
     * @since Kunena
     *
     */
    public bool $allowed = \false;

    /**
     * @var   ?KunenaTemplate
     * @since Kunena
     * 
     */
    public ?KunenaTemplate $ktemplate = \null;

    /**
     * @var    DatabaseDriver
     * @since  6.2.0
     */
    public DatabaseDriver $database;

    /**
     * @var    CMSApplication
     * @since  6.2.0
     */
    public CMSApplication $application;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  - The method name to call (priority defaults to 0)
     *  - An array composed of the method name to call and the priority
     *
     * @return  array
     * @since   Kunena 6.5
     */
    public static function getSubscribedEvents(): array
    {
        $app     = Factory::getApplication();
        $mapping = [];

        if ($app->isClient('site') || $app->isClient('administrator')) {
            if ($app->isClient('site')) {
                // Only allowed in the frontend
                $mapping['onContentBeforeDisplay'] = 'onContentBeforeDisplay';
                $mapping['onContentAfterDisplay']  = 'onContentAfterDisplay';
            } elseif ($app->isClient('administrator')) {
                // Only allowed in the backend
            }
        }

        return $mapping;
    }
    /**
     * Constructor Function
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @since  Kunena
     * @throws \Exception
     */
    public function __construct($config)
    {
        // Do not register plug-in in administration.
        if (Factory::getApplication()->isClient('administrator')) {
            return;
        }

        // We need to set these here as these are used in the KunenaDiscussHelper
        $this->database    = Factory::getContainer()->get(DatabaseInterface::class);
        $this->application = Factory::getApplication();

        // If scope isn't articles or Kunena, do not register plug-in.
        if ($this->application->scope != 'com_content' && $this->application->scope != 'com_kunena') {
            return;
        }

        // Kunena detection and version check
        $minKunenaVersion = '6.4';

        if (!\class_exists('Kunena\Forum\Libraries\Forum\KunenaForum') || !KunenaForum::isCompatible($minKunenaVersion)) {
            $this->loadLanguage();
            $this->application->enqueueMessage(Text::sprintf('PLG_KUNENADISCUSS_DEPENDENCY_FAIL', $minKunenaVersion));

            return;
        }

        // Kunena online check
        if (!KunenaForum::enabled()) {
            return;
        }

        // Boot Kunena component for use of Kunena registered function in non com_kunena page
        if (!$this->application->bootComponent('com_kunena')) {
            return;
        }

        // Initialize variables
        $this->user   = KunenaFactory::getUser();
        $this->config = KunenaFactory::getConfig();

        // Initialize plugin
        parent::__construct($config);

        $this->allowed = \true;

        $this->helper = new KunenaDiscussHelper($this);

        $this->helper->debug("Constructor called in {$this->application->scope}");
    }

    /**
     * Before display content method.
     *
     * Method is called by the view and the results are imploded and displayed in a placeholder.
     *
     * @param   BeforeDisplayEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   Kunena 6.5
     */
    public function onContentBeforeDisplay(BeforeDisplayEvent $event)
    {
        static $loaded = \false;

        if (!$this->allowed) {
            return;
        }

        // Initialize plug-in during the first run.
        if (!$loaded) {
            $loaded = \true;

            // Load language files and setup Kunena libraries.
            $this->loadLanguage();
            KunenaForum::setup();
            KunenaFactory::loadLanguage();

            // Create Kunena Discuss table if it doesn't exist.
            $this->helper->createTable();
        }

        // Make sure that event gets only called once.
        if (self::$inevent) {
            return;
        }

        $context = $event->getContext();
        $article = $event->getItem();
        $params  = $event->getParams();

        self::$inevent = \true;

        $article = $this->helper->prepareContent($context, $article, $params);
        $event->setArgument('item', $article);

        self::$inevent = \false;
    }

    /**
     * After display content method.
     *
     * Method is called by the view and the results are imploded and displayed in a placeholder.
     *
     * @param   AfterDisplayEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   Kunena 6.5
     */
    public function onContentAfterDisplay(AfterDisplayEvent $event)
    {
        $article = $event->getItem();

        // Make sure that event gets only called once and there's something to display.
        if (self::$inevent || !isset($article->id) || !isset(self::$plgDisplay[$article->id])) {
            return;
        }

        $this->ktemplate = KunenaFactory::getTemplate();
        $this->ktemplate->loadFontawesome();

        $this->helper->debug("onAfterDisplayContent: Returning content for article {$article->id}");

        $result = self::$plgDisplay[$article->id];
        $user   = $this->getApplication()->getIdentity();

        if ($user->guest) {
            $login_public = $this->params->get('login_public', 0);

            if ($login_public) {
                $layout          = $this->params->get('layout', 'default');
                $loginLayoutPath = PluginHelper::getLayoutPath('content', 'kunenadiscuss', $layout . '_login');

                \ob_start();
                include $loginLayoutPath;
                $guestHtml = \ob_get_clean();

                $result    = $guestHtml . $result;
            }
        }

        $event->addResult($result);
    }
}
