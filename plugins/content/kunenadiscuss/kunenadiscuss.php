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
defined('_JEXEC') or die('');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Forum\KunenaForum;
use Kunena\Plugin\Content\Kunenadiscuss\Helper\KunenaDiscussHelper;

/**
 * Class plgContentKunenaDiscuss
 * @since Kunena
 */
class PlgContentKunenaDiscuss extends CMSPlugin
{
	/**
	 * Associative array to hold results of the plugin.
	 *
	 * @var array
	 * @since Kunena
	 *
	 */
	public static $plgDisplay = [];

	/**
	 * @var boolean
	 * @since Kunena
	 */
	public static $includedCss = false;

	/**
	 * @var boolean
	 * @since Kunena
	 *
	 */
	public static $inevent = false;

	/**
	 * @var SiteApplication
	 * @since Kunena
	 *
	 */
	public $app = null;

	/**
	 * @var KunenaDiscussHelper
	 * @since Kunena
	 *
	 */
	public $helper = null;

	/**
	 * @var DatabaseDriver
	 * @since Kunena
	 *
	 */
	public $db = null;

	/**
	 * @var KunenaUser
	 * @since Kunena
	 *
	 */
	public $user = null;

	/**
	 * @var KunenaConfig
	 * @since Kunena
	 *
	 */
	public $config = null;

	/**
	 * @var boolean
	 * @since Kunena
	 *
	 */
	public $allowed = false;

	/**
	 * Constructor Function
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $params   An optional associative array of configuration settings.
	 *
	 * @since Kunena
	 * @throws Exception
	 */
	public function __construct(&$subject, $params)
	{
		$this->app = Factory::getApplication();

		// Do not register plug-in in administration.
		if ($this->app->isClient('administrator'))
		{
			return;
		}

		// If scope isn't articles or Kunena, do not register plug-in.
		if ($this->app->scope != 'com_content' && $this->app->scope != 'com_kunena')
		{
			return;
		}

		// Kunena detection and version check
		$minKunenaVersion = '6.0';

		if (!class_exists('Kunena\Forum\Libraries\Forum\KunenaForum') || !KunenaForum::isCompatible($minKunenaVersion))
		{
			$this->loadLanguage();
			$this->app->enqueueMessage(Text::sprintf('PLG_KUNENADISCUSS_DEPENDENCY_FAIL', $minKunenaVersion));

			return;
		}

		// Kunena online check
		if (!KunenaForum::enabled())
		{
			return;
		}

		// Boot Kunena component for use of Kunena registered function in non com_kunena page
		if (!$this->app->bootComponent('com_kunena'))
		{
			return;
		}

		// Initialize variables
		$this->user   = KunenaFactory::getUser();
		$this->config = KunenaFactory::getConfig();

		// Initialize plugin
		parent::__construct($subject, $params);

		$this->allowed = true;

		$this->helper = new KunenaDiscussHelper($this);

		$this->helper->debug("Constructor called in {$this->app->scope}");
	}


	/**
	 * Before display content method.
	 *
	 * Method is called by the view and the results are imploded and displayed in a placeholder.
	 *
	 * @param   string   $context     The context for the content passed to the plugin.
	 * @param   object   $article     The content object.  Note $article->text is also available
	 * @param   object   $params      The content params
	 * @param   integer  $limitstart  The 'page' number
	 *
	 * @return  string
	 * @since Kunena
	 * @throws Exception
	 */
	public function onContentBeforeDisplay($context, &$article, &$params, $limitstart = 0)
	{
		static $loaded = false;

		if (!$this->allowed)
		{
			return;
		}

		// Initialize plug-in during the first run.
		if (!$loaded)
		{
			$loaded = true;

			// Load language files and setup Kunena libraries.
			$this->loadLanguage();
			KunenaForum::setup();
			KunenaFactory::loadLanguage();

			// Create Kunena Discuss table if it doesn't exist.
			$this->helper->createTable();
		}

		// Make sure that event gets only called once.
		if (self::$inevent)
		{
			return '';
		}

		self::$inevent = true;
		$this->helper->prepareContent($context, $article, $params);
		self::$inevent = false;

		return '';
	}

	/**
	 * After display content method.
	 *
	 * Method is called by the view and the results are imploded and displayed in a placeholder.
	 *
	 * @param   string   $context     The context for the content passed to the plugin.
	 * @param   object   $article     The content object.  Note $article->text is also available
	 * @param   object   $params      The content params
	 * @param   integer  $limitstart  The 'page' number
	 *
	 * @return  string
	 * @since Kunena
	 * @throws Exception
	 */
	public function onContentAfterDisplay($context, &$article, &$params, $limitstart = 0)
	{
		// Make sure that event gets only called once and there's something to display.
		if (self::$inevent || !isset($article->id) || !isset(self::$plgDisplay[$article->id]))
		{
			return '';
		}

		$this->ktemplate = KunenaFactory::getTemplate();
		$this->ktemplate->loadFontawesome();

		$this->helper->debug("onAfterDisplayContent: Returning content for article {$article->id}");

		$result = self::$plgDisplay[$article->id];
		$user   = Factory::getUser();

		if ($user->guest)
		{
			$login_public = $this->params->get('login_public', 0);

			if ($login_public)
			{
				$layout          = $this->params->get('layout', 'default');
				$loginLayoutPath = PluginHelper::getLayoutPath('content', 'kunenadiscuss', $layout . '_login');

				ob_start();
				include $loginLayoutPath;
				$guestHtml = ob_get_clean();

				$result    = $guestHtml . $result;
			}
		}

		return $result;
	}
}
