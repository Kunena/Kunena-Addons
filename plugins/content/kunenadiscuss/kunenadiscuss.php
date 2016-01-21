<?php
/**
 * Kunena Discuss Plugin
 *
 * @package       Kunena.plg_content_kunenadiscuss
 *
 * @copyright (C) 2008 - 2016 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          http://www.kunena.org
 **/
defined('_JEXEC') or die ('');

/**
 * Class plgContentKunenaDiscuss
 */
class plgContentKunenaDiscuss extends JPlugin
{
	/**
	 * Associative array to hold results of the plugin.
	 *
	 * @var array
	 */
	protected static $plgDisplay = array();
	/**
	 * @var bool
	 */
	protected static $includedCss = false;
	/**
	 * @var bool
	 */
	protected static $inevent = false;

	/**
	 * @var JApplication
	 */
	public $app = null;
	/**
	 * @var JDatabaseDriver
	 */
	public $db = null;
	/**
	 * @var JUser
	 */
	public $user = null;
	/**
	 * @var KunenaConfig
	 */
	public $config = null;

	/**
	 * @param object $subject
	 * @param array  $params
	 */
	public function __construct(&$subject, $params)
	{
		$this->app = JFactory::getApplication();

		// Do not register plug-in in administration.
		if ($this->app->isAdmin())
		{
			return;
		}

		// If scope isn't articles or Kunena, do not register plug-in.
		if ($this->app->scope != 'com_content' && $this->app->scope != 'com_kunena')
		{
			return;
		}

		// Kunena detection and version check
		$minKunenaVersion = '3.0';
		if (!class_exists('KunenaForum') || !KunenaForum::isCompatible($minKunenaVersion))
		{
			$this->loadLanguage();
			$this->app->enqueueMessage(JText::sprintf('PLG_KUNENADISCUSS_DEPENDENCY_FAIL', $minKunenaVersion));

			return;
		}
		// Kunena online check
		if (!KunenaForum::enabled())
		{
			return;
		}

		// Initialize variables
		$this->db     = JFactory::getDbo();
		$this->user   = KunenaFactory::getUser();
		$this->config = KunenaFactory::getConfig();

		// Initialize plugin
		parent::__construct($subject, $params);

		$this->debug("Constructor called in {$this->app->scope}");
	}

	/**
	 * Before display content method.
	 *
	 * Method is called by the view and the results are imploded and displayed in a placeholder.
	 *
	 * @param   string  $context    The context for the content passed to the plugin.
	 * @param   object  $article    The content object.  Note $article->text is also available
	 * @param   object  $params     The content params
	 * @param   integer $limitstart The 'page' number
	 *
	 * @return  string
	 */
	public function onContentBeforeDisplay($context, &$article, &$params, $limitstart = 0)
	{
		static $loaded = false;

		// Initialize plug-in during the first run.
		if (!$loaded)
		{
			$loaded = true;
			// Load language files and setup Kunena libraries.
			$this->loadLanguage();
			KunenaForum::setup();
			KunenaFactory::loadLanguage();

			// Create Kunena Discuss table if it doesn't exist.
			$this->createTable();
		}

		// Make sure that event gets only called once.
		if (self::$inevent)
		{
			return '';
		}

		self::$inevent = true;
		$this->prepare($context, $article, $params);
		self::$inevent = false;

		return '';
	}

	/**
	 * After display content method.
	 *
	 * Method is called by the view and the results are imploded and displayed in a placeholder.
	 *
	 * @param   string  $context    The context for the content passed to the plugin.
	 * @param   object  $article    The content object.  Note $article->text is also available
	 * @param   object  $params     The content params
	 * @param   integer $limitstart The 'page' number
	 *
	 * @return  string
	 */
	public function onContentAfterDisplay($context, &$article, &$params, $limitstart = 0)
	{
		// Make sure that event gets only called once and there's something to display.
		if (self::$inevent || !isset($article->id) || !isset(self::$plgDisplay[$article->id]))
		{
			return '';
		}

		$this->debug("onAfterDisplayContent: Returning content for article {$article->id}");

		$result = self::$plgDisplay[$article->id];
		$user   = JFactory::getUser();
		if ($user->guest)
		{
			$login_public = $this->params->get('login_public', 0);
			if ($login_public)
			{
				$guestHtml = "<div class='kunenadiscuss kpublic'>";
				$guestHtml = $guestHtml . "<div class='kdiscuss-title login-discuss'>" . JText::_('PLG_KUNENADISCUSS_DISCUSS_THIS_ARTICLE') . "</div>";
				$guestHtml = $guestHtml . "<a class='klogin-to-discuss' href='" . JRoute::_('index.php?option=com_users&view=login&Itemid=1988') . "' >" . JText::_('PLG_KUNENADISCUSS_LOG_IN_TO_COMMENT') . "</a>";
				$guestHtml = $guestHtml . "</div>";
				$result    = $guestHtml . $result;
			}
		}

		return $result;
	}

	/**
	 * @param $context
	 * @param $article
	 * @param $params
	 */
	protected function prepare($context, &$article, &$params)
	{
		if (!isset($article->state) || $article->state != 1)
		{
			$this->debug("onPrepareContent: Article not published");

			return;
		}
		// Only proceed if this event is not originated by Kunena itself or we run the danger of an event recursion
		$ksource = '';
		if ($params instanceof JRegistry)
		{
			$ksource = $params->get('ksource', '');
		}

		if ($ksource != 'kunena')
		{

			$customTopics = $this->params->get('custom_topics', 1);

			$articleCategory = (isset ($article->catid) ? $article->catid : 0);
			$isStaticContent = !$articleCategory;
			if ($isStaticContent)
			{
				$kunenaCategory = false;
			}
			else
			{
				$kunenaCategory = $this->getForumCategory($articleCategory);
				if (!$kunenaCategory)
				{
					if (!$customTopics)
					{
						$this->debug("onPrepareContent: Allowing only Custom Topics");
					}
				}
			}
			$kunenaTopic = false;

			$regex = '/{kunena_discuss:(\d+?)}/s';

			if (JRequest::getVar('tmpl', '') == 'component' || JRequest::getBool('print')
				|| JRequest::getVar('format', 'html') != 'html' || (isset ($article->state) && !$article->state)
				|| empty ($article->id) || $this->app->scope == 'com_kunena'
			)
			{
				$this->debug("onPrepareContent: Not allowed - removing tags.");
				if (isset ($article->text))
				{
					$article->text = preg_replace($regex, '', $article->text);
				}
				if (isset ($article->introtext))
				{
					$article->introtext = preg_replace($regex, '', $article->introtext);
				}
				if (isset ($article->fulltext))
				{
					$article->fulltext = preg_replace($regex, '', $article->fulltext);
				}
			}

			$view        = JRequest::getVar('view');
			$layout      = JRequest::getVar('layout');
			$isBlogPage  = ($view == 'section' || $view == 'category') && $layout == 'blog';
			$isFrontPage = $view == 'frontpage' || $view == 'featured';
			if ($isBlogPage)
			{
				$this->debug("onPrepareContent: we are in blog page.");
				$show = $this->params->get('show_blog_page', 2);
			}
			else
			{
				if ($isFrontPage)
				{
					$this->debug("onPrepareContent: we are in front page.");
					$show = $this->params->get('show_front_page', 2);
				}
				else
				{
					$this->debug("onPrepareContent: we are in {$view}/{$layout} page.");
					$show = $this->params->get('show_other_pages', 2);
				}
			}
			if (!$show || isset (self::$plgDisplay [$article->id]))
			{
				$this->debug("onPrepareContent: Configured to show nothing");
				if (isset ($article->text))
				{
					$article->text = preg_replace($regex, '', $article->text);
				}
				if (isset ($article->introtext))
				{
					$article->introtext = preg_replace($regex, '', $article->introtext);
				}
				if (isset ($article->fulltext))
				{
					$article->fulltext = preg_replace($regex, '', $article->fulltext);
				}

				return;
			}

			$this->debug("onPrepareContent: Article {$article->id}");

			if (!$customTopics)
			{
				$this->debug("onPrepareContent: Custom Topics disabled");
			}
			else
			{
				// Get fulltext from frontpage articles (tag can be inside fulltext)
				if ($isFrontPage)
				{
					$db    = $this->db;
					$query = $db->getQuery(true)
						->select($db->quoteName('fulltext'))
						->from('#__content')
						->where("id={$db->quote($article->id)}");
					$this->db->setQuery($query);
					$fulltext = $this->db->loadResult();
					KunenaError::checkDatabaseError();
					$text = $article->introtext . ' ' . $fulltext;
				}
				else
				{
					if (isset ($article->text))
					{
						$text = $article->text;
					}
					else
					{
						$text = array();
						if (isset ($article->introtext))
						{
							$text [] = $article->introtext;
						}
						if (isset ($article->fulltext))
						{
							$text [] = $article->fulltext;
						}
						$text = implode("\n\n", $text);
					}
				}

				$matches = array();
				if (preg_match($regex, $text, $matches))
				{
					$kunenaTopic = intval($matches [1]);
					if (isset ($article->text))
					{
						$article->text = preg_replace("/{kunena_discuss:$kunenaTopic}/", '', $article->text, 1);
					}
					if (isset ($article->introtext))
					{
						$article->introtext = preg_replace("/{kunena_discuss:$kunenaTopic}/", '', $article->introtext, 1);
					}
					if (isset ($article->fulltext))
					{
						$article->fulltext = preg_replace("/{kunena_discuss:$kunenaTopic}/", '', $article->fulltext, 1);
					}
					if ($kunenaTopic == 0)
					{
						$this->debug("onPrepareContent: Searched for {kunena_discuss:#}: Discussion of this article has been disabled.");

						return;
					}
				}
				$this->debug("onPrepareContent: Searched for {kunena_discuss:#}: Custom Topic "
					. ($kunenaTopic ? "{$kunenaTopic} found." : "not found."));
			}

			if ($kunenaCategory || $kunenaTopic)
			{
				self::$plgDisplay [$article->id] = $this->showPlugin($kunenaCategory, $kunenaTopic, $article, $show == 1);
			}
		} // end of $ksource!='kunena' check
	}

	/**
	 * @param  int    $catid
	 * @param  int    $topic_id
	 * @param  object $row
	 * @param  bool   $linkOnly
	 *
	 * @return mixed|string
	 */
	protected function showPlugin($catid, $topic_id, &$row, $linkOnly)
	{
		// Show a simple form to allow posting to forum from the plugin
		$plgShowForm = $this->params->get('form', 1);
		// Default is to put QuickPost at the very bottom.
		$formLocation = $this->params->get('form_location', 0);

		// Don't repeat the CSS for each instance of this plugin in a page!
		if (!self::$includedCss)
		{
			$doc = JFactory::getDocument();
			$doc->addStyleSheet(JUri::root(true) . "/plugins/content/kunenadiscuss/css/discuss.css");
			self::$includedCss = true;
		}

		// Find cross reference and the real topic
		$db    = $this->db;
		$query = $db->getQuery(true)
			->select($db->quoteName('thread_id'))
			->from('#__kunenadiscuss')
			->where("content_id = {$db->quote($row->id)}");
		$this->db->setQuery($query);
		$result = $this->db->loadResult();
		KunenaError::checkDatabaseError();

		if ($topic_id)
		{
			// Custom topic found
			$this->debug("showPlugin: Loading Custom Topic {$topic_id}");
			$id = $topic_id;
		}
		elseif ($result)
		{
			// Reference found
			$this->debug("showPlugin: Loading Stored Topic {$result}");
			$id = $result;
		}
		else
		{
			// No topic exists
			$this->debug("showPlugin: No topic found");
			$id = 0;
		}
		$topic = KunenaForumTopicHelper::get($id);
		// If topic has been moved, find the real topic
		while ($topic->moved_id)
		{
			$this->debug("showPlugin: Topic {$topic->id} has been moved to {$topic->moved_id}");
			$topic = KunenaForumTopicHelper::get($topic->moved_id);
		}

		if ($result)
		{
			if (!$topic->exists())
			{
				$this->debug("showPlugin: Topic does not exist, removing reference to {$result}");
				$this->deleteReference($row);
			}
			elseif ($topic->id != $id)
			{
				$this->debug("showPlugin: Topic has been moved or changed, updating reference to {$topic->id}");
				$this->updateReference($row, $topic->id);
			}
		}
		elseif ($topic_id && $topic->exists())
		{
			$this->debug("showPlugin: First hit to Custom Topic, created reference to topic {$topic_id}");
			$this->createReference($row, $topic_id);
		}

		// Initialise some variables
		$subject = $row->title;
		if (isset($row->publish_up) && $row->publish_up != '0000-00-00 00:00:00')
		{
			$published = JFactory::getDate($row->publish_up)->toUnix(); // take start publishing date
		}
		else
		{
			$published = JFactory::getDate($row->created)->toUnix(); // or created date if publish_up is empty
		}
		$now = JFactory::getDate()->toUnix();

		if ($topic->exists())
		{
			// If current user doesn't have authorisation to read existing topic, we are done
			if ($id && !$topic->authorise('read'))
			{
				$this->debug("showPlugin: Topic said {$topic->getError()}");

				return '';
			}

			$category = $topic->getCategory();
		}
		else
		{
			$this->debug("showPlugin: Let's see what we can do..");

			// If current user doesn't have authorisation to read category, we are done
			$category = KunenaForumCategoryHelper::get($catid);
			if (!$category->authorise('read'))
			{
				$this->debug("showPlugin: Category {$catid} said {$category->getError()}");

				return '';
			}

			$create     = $this->params->get('create', 0);
			$createTime = $this->params->get('create_time', 0) * 604800; // Weeks in seconds
			if ($createTime && $published + $createTime < $now)
			{
				$this->debug("showPlugin: Topic creation time expired, cannot start new discussion anymore");

				return '';
			}
			if ($create)
			{
				$this->debug("showPlugin: First hit, created new topic {$topic_id} into forum");
				$topic = $this->createTopic($row, $category, $subject);
				if ($topic === false)
				{
					return '';
				}
			}
		}

		// Do we allow answers into the topic?
		$closeTime = $this->params->get('close_time', 0) * 604800; // Weeks in seconds or 0 (forever)
		if ($closeTime && $topic->exists())
		{
			$closeReason = $this->params->get('close_reason', 0);
			if ($closeReason)
			{
				$this->debug("showPlugin: Close time by last post");
				$closeTime += $topic->last_post_time;
			}
			else
			{
				$this->debug("showPlugin: Close time by topic creation");
				$closeTime += $topic->first_post_time;
			}
		}
		else
		{
			// Topic has not yet been created or will kept open forever
			$closeTime = $now + 1;
		}

		$linktopic = '';
		if ($topic->exists() && $linkOnly)
		{
			$this->debug("showPlugin: Displaying only link to the topic");
			$linktitle = JText::sprintf('PLG_KUNENADISCUSS_DISCUSS_ON_FORUMS', $topic->getReplies());

			return JHtml::_('kunenaforum.link', $topic->getUri($category), $linktitle, $linktitle);
		}
		elseif ($topic->exists() && !$plgShowForm)
		{
			$this->debug("showPlugin: Displaying link to the topic because the form is disabled");
			$linktitle = JText::sprintf('PLG_KUNENADISCUSS_DISCUSS_ON_FORUMS', $topic->getReplies());
			$linktopic = JHtml::_('kunenaforum.link', $topic->getUri($category), $linktitle, $linktitle);
		}
		elseif (!$topic->exists() && !$plgShowForm)
		{
			$linktopic = JText::_('PLG_KUNENADISCUSS_NEW_TOPIC_NOT_CREATED');
		}

		// ************************************************************************
		// Process the QuickPost form

		$quickPost = '';
		$canPost   = $this->canPost($category, $topic);

		if ($canPost && $plgShowForm && (!$closeTime || $closeTime >= $now))
		{

			if (JFactory::getUser()->get('guest'))
			{
				$this->debug("showPlugin: Guest can post: this feature doesn't work well if Joomla caching or Cache Plugin is enabled!");
			}
			if (JRequest::getInt('kdiscussContentId', -1, 'POST') == $row->id)
			{
				$this->debug("showPlugin: Reply topic!");
				$quickPost .= $this->replyTopic($row, $category, $topic, $subject);
			}
			else
			{
				$this->debug("showPlugin: Displaying form");
				$quickPost .= $this->showForm($row, $category, $topic, $subject);
			}
		}

		// This will be used all the way through to tell users how many posts are in the forum.
		$content = $this->showTopic($category, $topic, $linktopic);

		if (!$content && !$quickPost)
		{
			return $linktopic;
		}

		if ($formLocation)
		{
			$content = '<div class="kunenadiscuss">' . $content . '<br />' . $quickPost . '</div>';
		}
		else
		{
			$content = '<div class="kunenadiscuss">' . $quickPost . "<br />" . $content . '</div>';
		}

		return $content;
	}

	/******************************************************************************
	 * Output
	 *****************************************************************************/

	/**
	 * @param KunenaForumCategory $category
	 * @param KunenaForumTopic    $topic
	 * @param string              $link_topic
	 *
	 * @return string
	 */
	protected function showTopic(KunenaForumCategory $category, KunenaForumTopic $topic, $link_topic)
	{
		if (!$topic->exists())
		{
			$this->debug("showTopic: No messages to render");

			return '';
		}

		$this->debug("showTopic: Rendering discussion");

		$ordering = $this->params->get('ordering', 1); // 0=ASC, 1=DESC
		$params   = array(
			'catid'            => $category->id,
			'id'               => $topic->id,
			'limitstart'       => (int) !$ordering,
			'limit'            => $this->params->get('limit', 25),
			'filter_order_Dir' => $ordering ? 'desc' : 'asc',
			'templatepath'     => __DIR__ . '/tmpl'
		);
		ob_start();
		KunenaForum::display('topic', 'default', null, $params);
		$str = ob_get_contents();
		ob_end_clean();

		return $link_topic . $str;
	}

	/**
	 * @param                     $row
	 * @param KunenaForumCategory $category
	 * @param KunenaForumTopic    $topic
	 * @param                     $subject
	 *
	 * @return string
	 */
	protected function showForm($row, KunenaForumCategory $category, KunenaForumTopic $topic, $subject)
	{

		$canPost = $this->canPost($category, $topic);
		if (!$canPost)
		{
			if (!$this->user->exists())
			{
				$this->debug("showForm: Public posting is not permitted, show login instead");
				$login        = KunenaLogin::getInstance();
				$loginlink    = $login->getLoginURL();
				$registerlink = $login->getRegistrationURL();
				$this->msg    = JText::sprintf('PLG_KUNENADISCUSS_LOGIN_OR_REGISTER', '"' . $loginlink . '"', '"' . $registerlink . '"');
			}
			else
			{
				$this->debug("showForm: Unfortunately you cannot discuss this item");
				$this->msg = JText::_('PLG_KUNENADISCUSS_NO_PERMISSION_TO_POST');
			}
		}
		$this->open    = $this->params->get('quickpost_open', false);
		$this->name    = JRequest::getString('name', $this->user->getName(), 'POST');
		$this->email   = JRequest::getString('email', null, 'POST');
		$this->message = JRequest::getString('message', null, 'POST');

		if ($this->hasCaptcha())
		{
			$this->displayCaptcha();
		}

		ob_start();
		$this->debug("showForm: Rendering form");
		include(__DIR__ . "/tmpl/form.php");
		$str = ob_get_contents();
		ob_end_clean();

		return $str;
	}

	/******************************************************************************
	 * Create and reply to topic
	 *****************************************************************************/

	protected function createReference($row, $topic_id)
	{
		$db    = $this->db;
		$query = $db->getQuery(true)
			->insert('#__kunenadiscuss')
			->columns('content_id, thread_id')
			->values("{$db->quote($row->id)}, {$db->quote($topic_id)}");
		$this->db->setQuery($query);
		$this->db->query();
		KunenaError::checkDatabaseError();
	}

	/**
	 * @param  object $row
	 * @param  int    $topic_id
	 */
	protected function updateReference($row, $topic_id)
	{
		$db    = $this->db;
		$query = $db->getQuery(true)
			->update('#__kunenadiscuss')
			->set("thread_id={$db->quote($topic_id)}")
			->where("content_id={$db->quote($row->id)}");
		$this->db->setQuery($query);
		$this->db->query();
		KunenaError::checkDatabaseError();
	}

	/**
	 * @param  object $row
	 */
	protected function deleteReference($row)
	{
		$db    = $this->db;
		$query = $db->getQuery(true)
			->delete('#__kunenadiscuss')
			->where("content_id={$db->quote($row->id)}");
		$this->db->setQuery($query);
		$this->db->query();
		KunenaError::checkDatabaseError();
	}

	/**
	 * @param                     $row
	 * @param KunenaForumCategory $category
	 * @param                     $subject
	 *
	 * @return bool|KunenaForumTopic
	 */
	protected function createTopic($row, KunenaForumCategory $category, $subject)
	{
		if (!$category->exists())
		{
			$this->debug("showPlugin: Topic creation failed: forum category doesn't exist!");

			return false;
		}

		$this->debug("showPlugin: Create topic!");

		$add_snippet = $this->params->get('add_article_snippet');
		$textwords   = implode(' ', array_slice(explode(' ', $row->fulltext), 0, 10));

		if (empty($textwords))
		{
			$textwords = implode(' ', array_slice(explode(' ', $row->introtext), 0, 10));
		}

		$snippet = strip_tags($textwords) . "..." . "\n\n";

		$type = $this->params->get('bbcode');
		switch ($type)
		{
			case 'full':
			case 'intro':
			case 'link':
			{
				if ($add_snippet)
				{
					$contents = $snippet . "[article={$type}]{$row->id}[/article]";
				}
				else
				{
					$contents = "[article={$type}]{$row->id}[/article]";
				}
			}
				break;
			default:
			{
				if ($add_snippet)
				{
					$contents = $snippet . "[article]{$row->id}[/article]";
				}
				else
				{
					$contents = "[article]{$row->id}[/article]";
				}
			}
		}

		$topic_owner = $this->params->get('topic_owner', $row->created_by); // save the ID for later use
		$user        = KunenaUserHelper::get($topic_owner);
		$email       = $user->email; // get real email
		$params      = array(
			'email'   => $email, // we need to pass email of the topic starter (robot) when 'Require E-mail' option is enabled
			'subject' => $subject,
			'message' => $contents,
		);
		$safefields  = array(
			'category_id' => intval($category->id)
		);
		list($topic, $message) = $category->newTopic($params, $topic_owner, $safefields);

		// Set time of message published by the plugin in the Unix timestamp format
		if (isset($row->publish_up) && $row->publish_up != '0000-00-00 00:00:00')
		{
			$message->time = JFactory::getDate($row->publish_up)->toUnix(); // start puglishing date of the article
		}
		else
		{
			if (isset($row->created) && $row->created != '0000-00-00 00:00:00')
			{
				$message->time = JFactory::getDate($row->created)->toUnix(); // created date of the article
			}
			else
			{
				$message->time = JFactory::getDate()->toUnix(); // current date and time
			}
		}

		/** @var KunenaForumTopic $topic */
		/** @var KunenaForumMessage $message */
		$success = $message->save();
		if (!$success)
		{
			$this->app->enqueueMessage($message->getError(), 'error');

			return false;
		}

		// Create a reference
		$this->createReference($row, $topic->id);

		return $topic;
	}

	/**
	 * @param                     $row
	 * @param KunenaForumCategory $category
	 * @param KunenaForumTopic    $topic
	 * @param                     $subject
	 *
	 * @return bool|string
	 */
	protected function replyTopic($row, KunenaForumCategory $category, KunenaForumTopic $topic, $subject)
	{
		$uri = JFactory::getURI();

		if (JSession::checkToken() == false)
		{
			$this->app->enqueueMessage(JText::_('COM_KUNENA_ERROR_TOKEN'), 'error');

			return false;
		}
		/*if ($this->hasCaptcha() && !$this->verifyCaptcha()) {
			return $this->showForm ( $row, $category, $topic, $subject );
		}*/
		// Create topic if it doesn't exist
		if (!$topic->exists())
		{
			$topic = $this->createTopic($row, $category, $subject);
		}
		$params = array(
			'name'    => JRequest::getString('name', $this->user->getName(), 'POST'),
			'email'   => JRequest::getString('email', null, 'POST'),
			'subject' => $subject,
			'message' => JRequest::getString('message', null, 'POST'),
		);

		if ($this->hasCaptcha() && !$this->verifyCaptcha())
		{
			$this->app->redirect($uri->toString(), $result);
		}

		$message = $topic->newReply($params);
		$success = $message->save();
		if (!$success)
		{
			$this->app->enqueueMessage($message->getError(), 'error');

			return false;
		}
		$message->sendNotification();

		if ($message->hold)
		{
			$result = JText::_('PLG_KUNENADISCUSS_PENDING_MODERATOR_APPROVAL');
		}
		else
		{
			$result = JText::_('PLG_KUNENADISCUSS_MESSAGE_POSTED');
		}

		// Redirect
		$app = JFactory::getApplication('site');
		$app->redirect($uri->toString(), $result);

		return '';
	}

	/******************************************************************************
	 * Debugging and error handling
	 *****************************************************************************/

	protected function debug($msg, $fatal = 0)
	{
		$debug      = $this->params->get('show_debug', false); // Print out debug info!
		$debugUsers = $this->params->get('show_debug_userids', ''); // Joomla Id's of Users who can see debug info

		if (!$debug || ($debugUsers && !in_array($this->user->userid, explode(',', $debugUsers))))
		{
			return;
		}

		if ($fatal)
		{
			echo "<br /><span class=\"kdb-fatal\">[KunenaDiscuss FATAL: $msg ]</span>";
		}
		else
		{
			echo "<br />[KunenaDiscuss debug: $msg ]";
		}
	}

	/******************************************************************************
	 * Permission checks
	 *****************************************************************************/

	protected function getForumCategory($catid)
	{
		// Default Kunena category to put new topics into
		$default = intval($this->params->get('default_category', 0));
		// Category pairs will be always allowed
		$categoryPairs = explode(';', $this->params->get('category_mapping', ''));
		$categoryMap   = array();
		foreach ($categoryPairs as $pair)
		{
			$pair  = explode(',', $pair);
			$key   = isset ($pair [0]) ? intval($pair [0]) : 0;
			$value = isset ($pair [1]) ? intval($pair [1]) : 0;
			if ($key > 0)
			{
				$categoryMap [$key] = $value;
			}
		}
		// Limit plugin to the following content catgeories
		$allowCategories = explode(',', $this->params->get('allow_categories', ''));
		// Exclude the plugin from the following categories
		$denyCategories = explode(',', $this->params->get('deny_categories', ''));

		if (!is_numeric($catid) || intval($catid) == 0)
		{
			$this->debug("onPrepareContent.Deny: Category {$catid} is not valid");

			return false;
		}

		$db    = $this->db;
		$query = $db->getQuery(true);
		$query->select($db->quoteName('parent_id'));
		$query->from('#__categories');
		$query->where("id = {$db->quote( $catid )}");
		$this->db->setQuery($query);
		try
		{
			$db->execute();
		} catch (Exception $e)
		{
			$this->debug("onPrepareContent.Parent IDs: Error executing query - " . $e);
		}
		$parent_catid = $db->loadResult(); // parend ID of the article's category
		$this->debug("onPrepareContent.Parent category ID is: " . $parent_catid);

		if (!empty($categoryMap))
		{ // let's check the mapping

			if (isset($categoryMap[$catid]))
			{ // 1st check - by article's category
				$forumcatid = intval($categoryMap [$catid]);
				if (!$forumcatid)
				{
					$this->debug("onPrepareContent.Deny: Category {$catid} was disabled in the category map.");

					return false;
				}
				$this->debug("onPrepareContent.Allow: Category {$catid} is in the category map using Kunena category {$forumcatid}");

				return $forumcatid;
			}
			else
			{
				if (!empty($parent_catid) && isset($categoryMap[$parent_catid]))
				{ // 2nd check - by parent category
					$forumcatid = intval($categoryMap[$parent_catid]);
					$msg        = "onPrepareContent.Allow: "
						. "Parent category {$parent_catid} of the article category {$catid} is in the category map using Kunena category {$forumcatid}";
					$this->debug($msg);

					return $forumcatid;
				}
			}
		}

		if (!$default)
		{
			$this->debug("onPrepareContent.Deny: There is no default Kunena category");

			return false;
		}

		if (in_array('0', $allowCategories) || in_array($catid, $allowCategories))
		{
			$this->debug("onPrepareContent.Allow: Category {$catid} was listed in allow list and is using default Kunena category {$default}");

			return $default;
		}
		if (in_array('0', $denyCategories) || in_array($catid, $denyCategories))
		{
			$this->debug("onPrepareContent.Deny: Category {$catid} was listed in deny list");

			return false;
		}

		$this->debug("onPrepareContent.Allow: Category {$catid} is using default Kunena category {$default}");

		return $default;
	}

	/**
	 * @param KunenaForumCategory $category
	 * @param KunenaForumTopic    $topic
	 *
	 * @return bool
	 */
	protected function canPost(KunenaForumCategory $category, KunenaForumTopic $topic)
	{
		if ($topic->exists())
		{
			return $topic->authorise('reply');
		}
		else
		{
			return $category->authorise('topic.reply');
		}
	}

	/**
	 * Check if the user will have captcha or not
	 *
	 * @return bool
	 */
	public function hasCaptcha()
	{
		return $this->user->canDoCaptcha();
	}

	/**
	 * Display the captcha into the post form
	 *
	 * @return string
	 */
	protected function displayCaptcha()
	{
		if (JPluginHelper::isEnabled('captcha'))
		{
			$plugin         = JPluginHelper::getPlugin('captcha');
			$params         = new JRegistry($plugin[0]->params);
			$captcha_pubkey = $params->get('public_key');
			$catcha_privkey = $params->get('private_key');

			if (!empty($captcha_pubkey) && !empty($catcha_privkey))
			{
				JPluginHelper::importPlugin('captcha');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('onInit', 'dynamic_recaptcha_1');
			}
		}
	}

	/**
	 * Check if hte captcha given is correct
	 *
	 * @return bool
	 */
	protected function verifyCaptcha()
	{
		if (JPluginHelper::isEnabled('captcha') && $this->config->captcha)
		{
			$plugin = JPluginHelper::getPlugin('captcha');
			$params = new JRegistry($plugin[0]->params);

			$captcha_pubkey = $params->get('public_key');
			$catcha_privkey = $params->get('private_key');

			if (!empty($captcha_pubkey) && !empty($catcha_privkey))
			{
				JPluginHelper::importPlugin('captcha');
				$dispatcher = JDispatcher::getInstance();

				$captcha_response = $this->app->input->getString('g-recaptcha-response');

				if (!empty($captcha_response))
				{
					// For ReCaptcha API 2.0
					$res = $dispatcher->trigger('onCheckAnswer', $this->app->input->getString('g-recaptcha-response'));
				}
				else
				{
					// For ReCaptcha API 1.0
					$res = $dispatcher->trigger('onCheckAnswer', $this->app->input->getString('recaptcha_response_field'));
				}

				return $res[0];
			}
		}

		return false;
	}

	protected function createTable()
	{
		$this->debug('createTable: Check if plugin table exists.');

		// Create plugin table if doesn't exist
		$query = "SHOW TABLES LIKE '{$this->db->getPrefix()}kunenadiscuss'";
		$this->db->setQuery($query);
		if (!$this->db->loadResult())
		{
			KunenaError::checkDatabaseError();
			$query = "CREATE TABLE IF NOT EXISTS `#__kunenadiscuss`
					(`content_id` int(11) NOT NULL default '0',
					 `thread_id` int(11) NOT NULL default '0',
					 PRIMARY KEY  (`content_id`)
					 )";
			$this->db->setQuery($query);
			$this->db->query();
			KunenaError::checkDatabaseError();
			$this->debug("Created #__kunenadiscuss cross reference table.");

			// Migrate data from old FireBoard discussbot if it exists
			$query = "SHOW TABLES LIKE '{$this->db->getPrefix()}fb_discussbot'";
			$this->db->setQuery($query);
			if ($this->db->loadResult())
			{
				$query = "REPLACE INTO `#__kunenadiscuss`
					SELECT `content_id` , `thread_id`
					FROM `#__fb_discussbot`";
				$this->db->setQuery($query);
				$this->db->query();
				KunenaError::checkDatabaseError();
				$this->debug("Migrated old data.");
			}
		}
	}
}
