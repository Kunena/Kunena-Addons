<?php

/**
 * Kunena Discuss Plugin
 *
 * @package       Kunena.plg_content_kunenadiscuss
 *
 * @copyright (C) 2008 - 2022 Kunena Team. All rights reserved.
 * @license       http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

namespace Kunena\Plugin\Content\Kunenadiscuss\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Registry\Registry;
use Kunena\Forum\Libraries\Error\KunenaError;
use Kunena\Forum\Libraries\Forum\Category\KunenaCategory;
use Kunena\Forum\Libraries\Forum\Category\KunenaCategoryHelper;
use Kunena\Forum\Libraries\Forum\Message\KunenaMessageHelper;
use Kunena\Forum\Libraries\Forum\KunenaForum;
use Kunena\Forum\Libraries\Forum\Topic\KunenaTopic;
use Kunena\Forum\Libraries\Forum\Topic\KunenaTopicHelper;
use Kunena\Forum\Libraries\Login\KunenaLogin;
use Kunena\Forum\Libraries\User\KunenaUserHelper;

defined('_JEXEC') or die('');

/**
 * Helper class for Kunena Discuss
 *
 * @since   6.0.0
 */
class KunenaDiscussHelper
{
	/**
	 * @var CMSPlugin
	 * @since Kunena
	 *
	 */
	private $plugin = null;

	/**
	 * Constructor Function
	 *
	 * @param   CMSPlugin  $plugin  The plugin to use the helper with
	 */
	public function __construct(CMSPlugin $plugin)
	{
		$this->plugin = $plugin;
	}

	/**
	 * Function to display debug messages
	 *
	 * @param   string   $msg    The message to display
	 * @param   integer  $fatal  Fatal message
	 *
	 * @return void
	 * @since Kunena 6.0.0
	 */
	public function debug($msg, $fatal = 0)
	{
		// Print out debug info!
		$pluginParams = $this->plugin->params;
		$debug        = $pluginParams->get('show_debug', false);

		// Joomla Id's of Users who can see debug info
		$debugUsers = $pluginParams->get('show_debug_userids', '');

		if (!$debug || ($debugUsers && !in_array($this->plugin->user->userid, explode(',', $debugUsers))))
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

	/**
	 * Function to create the Kunena Discuss table (if not exists)
	 *
	 * @return boolean
	 *
	 * @since version
	 * @throws Exception
	 */
	public function createTable()
	{
		$this->debug('createTable: Check if plugin table exists.');

		// Create plugin table if doesn't exist
		$db    = $this->plugin->db;
		$query = "SHOW TABLES LIKE '{$db->getPrefix()}kunenadiscuss'";
		$db->setQuery($query);

		if (!$db->loadResult())
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__kunenadiscuss`
					(`content_id` int(11) NOT NULL default '0',
					 `thread_id` int(11) NOT NULL default '0',
					 PRIMARY KEY  (`content_id`)
					 )";
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (ExecutionFailureException $e)
			{
				KunenaError::displayDatabaseError($e);

				return false;
			}

			$this->debug("Created #__kunenadiscuss cross reference table.");

			// Migrate data from old FireBoard discussbot if it exists
			$query = "SHOW TABLES LIKE '{$db->getPrefix()}fb_discussbot'";
			$db->setQuery($query);

			if ($db->loadResult())
			{
				$query = "REPLACE INTO `#__kunenadiscuss`
					SELECT `content_id` , `thread_id`
					FROM `#__fb_discussbot`";
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (ExecutionFailureException $e)
				{
					KunenaError::displayDatabaseError($e);

					return false;
				}

				$this->debug("Migrated old data.");
			}
		}
	}

	/**
	 * Function to prepare the Content
	 *
	 * @param   string    $context  The context we are in
	 * @param   object    $article  The article to prepare
	 * @param   Registry  $params   The parameters to use
	 *
	 * @since Kunena
	 * @throws Exception
	 */
	public function prepareContent($context, &$article, &$params)
	{
		if (!isset($article->state) || $article->state != 1)
		{
			$this->debug("onPrepareContent: Article not published");

			return;
		}

		// Only proceed if this event is not originated by Kunena itself or we run the danger of an event recursion
		$ksource = '';

		if ($params instanceof Registry)
		{
			$ksource = $params->get('ksource', '');
		}

		if ($ksource != 'kunena')
		{
			$app          = $this->plugin->app;
			$pluginParams = $this->plugin->params;
			$customTopics = $pluginParams->get('custom_topics', 1);

			$articleCategory = (isset($article->catid) ? $article->catid : 0);
			$isStaticContent = !$articleCategory;
			$articleCategory = (int) $articleCategory;

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
			$regex       = '/{kunena_discuss:(\d+?)}/s';

			if (
				$app->input->getCmd('tmpl', '') == 'component'
				|| $app->input->getBool('print')
				|| $app->input->getCmd('format', 'html') != 'html'
				|| (isset($article->state) && !$article->state)
				|| empty($article->id)
				|| $app->scope == 'com_kunena'
			)
			{
				$this->debug("onPrepareContent: Not allowed - removing tags.");

				if (isset($article->text))
				{
					$article->text = preg_replace($regex, '', $article->text);
				}

				if (isset($article->introtext))
				{
					$article->introtext = preg_replace($regex, '', $article->introtext);
				}

				if (isset($article->fulltext))
				{
					$article->fulltext = preg_replace($regex, '', $article->fulltext);
				}
			}

			$view        = $app->input->getCmd('view');
			$layout      = $app->input->getCmd('layout');
			$isBlogPage  = ($view == 'section' || $view == 'category') && $layout == 'blog';
			$isFrontPage = $view == 'frontpage' || $view == 'featured';
			$isArticle   = $view == 'article';

			if ($isBlogPage)
			{
				$this->debug("onPrepareContent: we are in blog page.");
				$show = $pluginParams->get('show_blog_page', 2);
			}
			elseif ($isFrontPage)
			{
				$this->debug("onPrepareContent: we are in front page.");
				$show = $pluginParams->get('show_front_page', 2);
			}
			elseif ($isArticle)
			{
				$this->debug("onPrepareContent: we are viewing an article.");
				$show = $pluginParams->get('show_article_pages', 2);
			}
			else
			{
				$this->debug("onPrepareContent: we are in {$view}/{$layout} page.");
				$show = $pluginParams->get('show_other_pages', 2);
			}

			if (!$show || isset($this->plugin::$plgDisplay[$article->id]))
			{
				$this->debug("onPrepareContent: Configured to show nothing");

				if (isset($article->text))
				{
					$article->text = preg_replace($regex, '', $article->text);
				}

				if (isset($article->introtext))
				{
					$article->introtext = preg_replace($regex, '', $article->introtext);
				}

				if (isset($article->fulltext))
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
					$db    = $this->plugin->db;
					$query = $db->getQuery(true);
					$query->select($db->quoteName('fulltext'))
						->from('#__content')
						->where("id={$db->quote($article->id)}");
					$db->setQuery($query);

					try
					{
						$fulltext = $db->loadResult();
					}
					catch (\DatabaseExceptionExecuting $e)
					{
						KunenaError::displayDatabaseError($e);

						return false;
					}

					$text = $article->introtext . ' ' . $fulltext;
				}
				else
				{
					if (isset($article->text))
					{
						$text = $article->text;
					}
					else
					{
						$text = array();

						if (isset($article->introtext))
						{
							$text[] = $article->introtext;
						}

						if (isset($article->fulltext))
						{
							$text[] = $article->fulltext;
						}

						$text = implode("\n\n", $text);
					}
				}

				$matches = array();

				if (preg_match($regex, $text, $matches))
				{
					$kunenaTopic = intval($matches[1]);

					if (isset($article->text))
					{
						$article->text = preg_replace("/{kunena_discuss:$kunenaTopic}/", '', $article->text, 1);
					}

					if (isset($article->introtext))
					{
						$article->introtext = preg_replace("/{kunena_discuss:$kunenaTopic}/", '', $article->introtext, 1);
					}

					if (isset($article->fulltext))
					{
						$article->fulltext = preg_replace("/{kunena_discuss:$kunenaTopic}/", '', $article->fulltext, 1);
					}

					if ($kunenaTopic == 0)
					{
						$this->debug("onPrepareContent: Searched for {kunena_discuss:#}: Discussion of this article has been disabled.");

						return;
					}
				}

				$this->debug(
					"onPrepareContent: Searched for {kunena_discuss:#}: Custom Topic "
						. ($kunenaTopic ? "{$kunenaTopic} found." : "not found.")
				);
			}

			if ($kunenaCategory || $kunenaTopic)
			{
				$this->plugin::$plgDisplay[$article->id] = $this->showPlugin($kunenaCategory, $kunenaTopic, $article, $show == 1);
			}
		}
	}

	/**
	 * Function to get the Forum Category
	 *
	 * @param   integer  $catid  The Category to get
	 *
	 * @return boolena|integer
	 * @since Kunena
	 *
	 */
	protected function getForumCategory($catid)
	{
		$pluginParams = $this->plugin->params;

		// Default Kunena category to put new topics into
		$default = intval($pluginParams->get('default_category', 0));

		// Category pairs will be always allowed
		$categoryPairs = explode(';', $pluginParams->get('category_mapping', ''));
		$categoryMap   = [];

		foreach ($categoryPairs as $pair)
		{
			$pair  = explode(',', $pair);
			$key   = isset($pair[0]) ? intval($pair[0]) : 0;
			$value = isset($pair[1]) ? intval($pair[1]) : 0;

			if ($key > 0)
			{
				$categoryMap[$key] = $value;
			}
		}

		// Limit plugin to the following content categories
		$allowCategories = explode(',', $pluginParams->get('allow_categories', ''));

		// Exclude the plugin from the following categories
		$denyCategories = explode(',', $pluginParams->get('deny_categories', ''));

		if (!is_numeric($catid) || intval($catid) == 0)
		{
			$this->debug("onPrepareContent.Deny: Category {$catid} is not valid");

			return false;
		}

		$db    = $this->plugin->db;
		$query = $db->getQuery(true);
		$query->select($db->quoteName('parent_id'))
			->from('#__categories')
			->where("id = {$db->quote($catid)}");
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (\Exception $e)
		{
			$this->debug("onPrepareContent.Parent IDs: Error executing query - " . $e);
		}

		// Parent ID of the article's category
		$parent_catid = $db->loadResult();
		$this->debug("onPrepareContent.Parent category ID is: " . $parent_catid);

		// Let's check the mapping
		if (!empty($categoryMap))
		{
			if (isset($categoryMap[$catid]))
			{
				$forumcatid = intval($categoryMap[$catid]);

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
				{
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
	 * Function to show the plugin content / form
	 *
	 * @param   integer  $catid     The category ID
	 * @param   integer  $topic_id  The Topic ID
	 * @param   object   $row       The Content
	 * @param   booleam  $linkOnly  Show link only toggle
	 *
	 * @return mixed|string
	 * @since Kunena
	 * @throws Exception
	 */
	protected function showPlugin($catid, $topic_id, &$row, $linkOnly)
	{
		$pluginParams = $this->plugin->params;

		// Show a simple form to allow posting to forum from the plugin
		$plgShowForm = $pluginParams->get('form', 1);

		// Default is to put QuickPost at the very bottom.
		$formLocation = $pluginParams->get('form_location', 0);

		// Don't repeat the CSS for each instance of this plugin in a page!
		if (!$this->plugin::$includedCss)
		{
			$layout = $pluginParams->get('layout', 'default');

			/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
			$wa = $this->plugin->app->getDocument()->getWebAssetManager();
			$wa->registerAndUseStyle('plg_content_kunenadiscuss', 'plg_content_kunenadiscuss/' . $layout . '.css');

			$this->plugin::$includedCss = true;
		}

		$result = false;

		// Find cross reference and the real topic
		$db    = $this->plugin->db;
		$query = $db->getQuery(true);
		$query->select($db->quoteName('thread_id'))
			->from('#__kunenadiscuss')
			->where("content_id = {$db->quote($row->id)}");
		$db->setQuery($query);

		try
		{
			$result = $db->loadResult();
		}
		catch (ExecutionFailureException $e)
		{
			KunenaError::displayDatabaseError($e);

			return false;
		}

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

		$topic = KunenaTopicHelper::get($id);

		// If topic has been moved, find the real topic
		while ($topic->moved_id)
		{
			$this->debug("showPlugin: Topic {$topic->id} has been moved to {$topic->moved_id}");
			$topic = KunenaTopicHelper::get($topic->moved_id);
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

		if (isset($row->publish_up) && $row->publish_up != '1000-01-01 00:00:00')
		{
			// Take start publishing date
			$published = Factory::getDate($row->publish_up)->toUnix();
		}
		else
		{
			// Or created date if publish_up is empty
			$published = Factory::getDate($row->created)->toUnix();
		}

		$now = Factory::getDate()->toUnix();

		if ($topic->exists())
		{
			// If current user doesn't have authorisation to read existing topic, we are done
			if ($id && !$topic->isAuthorised('read'))
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
			$category = KunenaCategoryHelper::get($catid);

			if (!$category->isAuthorised('read'))
			{
				$this->debug("showPlugin: Category {$catid} said {$category->getError()}");

				return '';
			}

			$create = $pluginParams->get('create', 0);

			// Weeks in seconds
			$createTime = $pluginParams->get('create_time', 0) * 604800;

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

		// Do we allow answers into the topic? Weeks in seconds or 0 (forever)
		$closeTime = $pluginParams->get('close_time', 0) * 604800;

		if ($closeTime && $topic->exists())
		{
			$closeReason = $pluginParams->get('close_reason', 0);

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
			$linktitle = Text::sprintf('PLG_KUNENADISCUSS_DISCUSS_ON_FORUMS', $topic->getReplies());
			$linktopic = $this->getKunenaForumLink($topic->getUri($category), $linktitle);

			return $linktopic;
		}
		elseif ($topic->exists() && !$plgShowForm)
		{
			$this->debug("showPlugin: Displaying link to the topic because the form is disabled");
			$linktitle = Text::sprintf('PLG_KUNENADISCUSS_DISCUSS_ON_FORUMS', $topic->getReplies());
			$linktopic = $this->getKunenaForumLink($topic->getUri($category), $linktitle);
		}
		elseif (!$topic->exists() && !$plgShowForm)
		{
			$linktopic = Text::_('PLG_KUNENADISCUSS_NEW_TOPIC_NOT_CREATED');
		}

		// Process the QuickPost form
		$quickPost = '';
		$canPost   = $this->canPost($category, $topic);

		if ($canPost && $plgShowForm && (!$closeTime || $closeTime >= $now))
		{
			if (Factory::getUser()->get('guest'))
			{
				$this->debug("showPlugin: Guest can post: this feature doesn't work well if Joomla caching or Cache Plugin is enabled!");
			}

			if (Factory::getApplication()->input->getInt('kdiscussContentId', -1, 'POST') == $row->id)
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
			$content = '<div id="kdiscuss" class="kunenadiscuss">' . $content . '<br />' . $quickPost . '</div>';
		}
		else
		{
			$content = '<div id="kdiscuss" class="kunenadiscuss">' . $quickPost . "<br />" . $content . '</div>';
		}

		return $content;
	}

	/**
	 * @param   object  $row
	 *
	 * @return bool
	 * @since Kunena
	 * @throws Exception
	 */
	protected function deleteReference($row)
	{
		$this->debug("deleteReference: Delete");

		$db    = $this->plugin->db;
		$query = $db->getQuery(true);
		$query->delete('#__kunenadiscuss')
			->where("content_id={$db->quote($row->id)}");
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (ExecutionFailureException $e)
		{
			$this->debug("deleteReference: Delete error " . $e);

			KunenaError::displayDatabaseError($e);

			return false;
		}

		return true;
	}

	/**
	 * @param   object  $row
	 * @param   int     $topic_id
	 *
	 * @return bool
	 * @since Kunena
	 * @throws Exception
	 */
	protected function updateReference($row, $topic_id)
	{
		$this->debug("updateReference: Update");

		$db    = $this->plugin->db;
		$query = $db->getQuery(true);
		$query->update('#__kunenadiscuss')
			->set("thread_id={$db->quote($topic_id)}")
			->where("content_id={$db->quote($row->id)}");
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (ExecutionFailureException $e)
		{
			$this->debug("updateReference: Update error " . $e);

			KunenaError::displayDatabaseError($e);

			return false;
		}

		return true;
	}

	/**
	 *
	 * @param $row
	 * @param $topic_id
	 *
	 * @return bool
	 * @since Kunena
	 *
	 * @throws Exception
	 */
	protected function createReference($row, $topic_id)
	{
		$this->debug("createReference: create");

		$db    = $this->plugin->db;
		$query = $db->getQuery(true);
		$query->insert('#__kunenadiscuss')
			->columns('content_id, thread_id')
			->values("{$db->quote($row->id)}, {$db->quote($topic_id)}");
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (ExecutionFailureException $e)
		{
			$this->debug("createReference: Error:" . $e);
			$this->deleteReference($row);
		}

		return true;
	}

	/**
	 * @param                        $row
	 * @param   KunenaCategory  $category
	 * @param                        $subject
	 *
	 * @return boolean|KunenaTopic
	 * @since Kunena
	 * @throws Exception
	 */
	protected function createTopic($row, KunenaCategory $category, $subject)
	{
		if (!$category->exists())
		{
			$this->debug("showPlugin: Topic creation failed: forum category doesn't exist!");

			return false;
		}

		$this->debug("showPlugin: Create topic!");

		$pluginParams = $this->plugin->params;

		$add_snippet = $pluginParams->get('add_article_snippet');
		$textwords   = implode(' ', array_slice(explode(' ', $row->fulltext), 0, 10));

		if (empty($textwords))
		{
			$textwords = implode(' ', array_slice(explode(' ', $row->introtext), 0, 10));
		}

		$snippet = strip_tags($textwords) . "..." . "\n\n";

		$type = $pluginParams->get('bbcode');

		switch ($type)
		{
			case 'full':
			case 'intro':
			case 'link':
				if ($add_snippet)
				{
					$contents = $snippet . "[article={$type}]{$row->id}[/article]";
				}
				else
				{
					$contents = "[article={$type}]{$row->id}[/article]";
				}
				break;

			default:
				if ($add_snippet)
				{
					$contents = $snippet . "[article]{$row->id}[/article]";
				}
				else
				{
					$contents = "[article]{$row->id}[/article]";
				}
		}

		// Save the ID for later use
		$topic_owner = $pluginParams->get('topic_owner', $row->created_by);
		$user        = KunenaUserHelper::get($topic_owner);

		// Get real email, we need to pass email of the topic starter (robot) when 'Require E-mail' option is enabled
		$email      = $user->email;
		$params     = [
			'email'   => $email,
			'subject' => $subject,
			'message' => $contents,
		];
		$safefields = [
			'category_id' => intval($category->id)
		];
		list($topic, $message) = $category->newTopic($params, $topic_owner, $safefields);

		// Set time of message published by the plugin in the Unix timestamp format, start publishing date of the article
		if (isset($row->publish_up) && $row->publish_up != '1000-01-01 00:00:00')
		{
			$message->time = Factory::getDate($row->publish_up)->toUnix();
		}
		else
		{
			if (isset($row->created) && $row->created != '1000-01-01 00:00:00')
			{
				// Created date of the article
				$message->time = Factory::getDate($row->created)->toUnix();
			}
			else
			{
				// Current date and time
				$message->time = Factory::getDate()->toUnix();
			}
		}

		$success = $message->save();

		if (!$success)
		{
			$this->debug("showPlugin: Error:" . $message->getError());

			$this->plugin->app->enqueueMessage($message->getError(), 'error');

			return false;
		}

		// Create a reference
		$this->createReference($row, $topic->id);

		return $topic;
	}

	/**
	 * @param   KunenaCategory  $category
	 * @param   KunenaTopic     $topic
	 *
	 * @return boolean
	 * @since Kunena
	 * @throws Exception
	 */
	protected function canPost(KunenaCategory $category, KunenaTopic $topic)
	{
		if ($topic->exists())
		{
			return $topic->isAuthorised('reply');
		}
		else
		{
			return $category->isAuthorised('topic.reply');
		}
	}

	/**
	 * @param                        $row
	 * @param   KunenaCategory  $category
	 * @param   KunenaTopic     $topic
	 * @param                        $subject
	 *
	 * @return boolean|string
	 * @since Kunena
	 * @throws Exception
	 */
	public function replyTopic($row, KunenaCategory $category, KunenaTopic $topic, $subject)
	{
		$app = $this->plugin->app;

		// Get return URI from form
		$return = base64_decode($app->input->get('return', '', 'string'));

		if (empty($return))
		{
			// When no return URI set inform redirect to current URL
			$return = Uri::getInstance()->toString();
		}

		// Check for request forgeries.
		if (!Session::checkToken())
		{
			$app->enqueueMessage(Text::_('COM_KUNENA_ERROR_TOKEN'), 'error');
			$app->redirect($return);

			return false;
		}

		// Get the data from POST
		$data = $app->input->post->get('jform', [], 'array');

		$form = (new FormFactory)->createForm('kunenadiscuss', ['control' => 'jform', 'load_data' => false]);
		$form->loadFile(JPATH_SITE . '/plugins/content/kunenadiscuss/forms/kunenadiscuss.xml');

		$captcha = $app->get('captcha', '0');

		// Set or remove captcha depending on settings
		if ($captcha && $this->hasCaptcha())
		{
			$form->setFieldAttribute('captcha', 'plugin', $captcha);
		}
		else
		{
			$form->removeField('captcha');
		}
		if (!$form->process($data))
		{
			foreach ($form->getErrors() as $error)
			{
				$this->debug("replyTopic: form data validation failed!: " . $error->getMessage());
				$app->enqueueMessage($error->getMessage(), 'warning');
			}

			$app->setUserState('plg_content_kunenadiscuss.form.data', $data);
			$app->redirect($return);

			return false;
		}

		// Create topic if it doesn't exist
		if (!$topic->exists())
		{
			$this->debug("replyTopic: Create topic");

			$topic = $this->createTopic($row, $category, $subject);
		}

		$params = [
			'name'    => isset($data['name']) ? $data['name'] : null,
			'email'   => isset($data['email']) ? $data['email'] : null,
			'subject' => $subject,
			'message' => isset($data['message']) ? $data['message'] : null,
		];

		$message = $topic->newReply($params);
		$success = $message->save();

		if (!$success)
		{
			$this->debug("replyTopic: can't save message: " . $message->getError());

			$app->enqueueMessage($message->getError(), 'error');
			$app->redirect($return);

			return false;
		}

		$message->sendNotification();

		$app->setUserState('plg_content_kunenadiscuss.form.data', null);

		// Redirect
		$app->redirect($return);
	}

	/**
	 * Check if the user will have captcha or not
	 *
	 * @return boolean
	 * @since Kunena
	 * @throws Exception
	 */
	protected function hasCaptcha()
	{
		return $this->plugin->user->canDoCaptcha();
	}

	/**
	 * @param                        $row
	 * @param   KunenaCategory  $category
	 * @param   KunenaTopic     $topic
	 * @param                        $subject
	 *
	 * @return string
	 * @since Kunena
	 * @throws Exception
	 */
	protected function showForm($row, KunenaCategory $category, KunenaTopic $topic, $subject)
	{
		$canPost = $this->canPost($category, $topic);

		if (!$canPost)
		{
			if (!$this->plugin->user->exists())
			{
				$this->debug("showForm: Public posting is not permitted, show login instead");
				$login        = KunenaLogin::getInstance();
				$loginlink    = $login->getLoginURL();
				$registerlink = $login->getRegistrationURL();
				$this->msg    = Text::sprintf('PLG_KUNENADISCUSS_LOGIN_OR_REGISTER', '"' . $loginlink . '"', '"' . $registerlink . '"');
			}
			else
			{
				$this->debug("showForm: Unfortunately you cannot discuss this item");
				$this->msg = Text::_('PLG_KUNENADISCUSS_NO_PERMISSION_TO_POST');
			}
		}

		$app  = $this->plugin->app;
		$form = (new FormFactory)->createForm('kunenadiscuss', ['control' => 'jform', 'load_data' => false]);
		$form->loadFile(JPATH_SITE . '/plugins/content/kunenadiscuss/forms/kunenadiscuss.xml');

		// Load form data from previous attempt
		$formData = $app->getUserState('plg_content_kunenadiscuss.form.data', []);

		// Store previous data in form
		$form->bind($formData);

		// When user is logged in disable name field and fill in user name
		if ($this->plugin->user->exists())
		{
			$form->setFieldAttribute('name', 'disabled', 'true');
			$form->setFieldAttribute('name', 'default', $this->plugin->user->getName());
		}

		// Remove Email field when not needed / required
		if (!$this->plugin->config->askEmail || $this->plugin->user->exists())
		{
			$form->removeField('email');
		}

		$captcha = $app->get('captcha', '0');

		// Set or remove captcha depending on settings
		if ($captcha && $this->hasCaptcha())
		{
			$form->setFieldAttribute('captcha', 'plugin', $captcha);
		}
		else
		{
			$form->removeField('captcha');
		}

		$layout         = $this->plugin->params->get('layout', 'default');
		$formLayoutPath = PluginHelper::getLayoutPath('content', 'kunenadiscuss', $layout . '_form');

		ob_start();
		include $formLayoutPath;
		$str = ob_get_clean();

		return $str;
	}

	/**
	 * @param   KunenaCategory  $category
	 * @param   KunenaTopic     $topic
	 * @param   string               $link_topic
	 *
	 * @return string
	 * @since Kunena
	 * @throws Exception
	 */
	protected function showTopic(KunenaCategory $category, KunenaTopic $topic, $link_topic)
	{
		if (!$topic->exists())
		{
			$this->debug("showTopic: No messages to render");

			return '';
		}

		$this->debug("showTopic: Rendering discussion");

		$app          = $this->plugin->app;
		$pluginParams = $this->plugin->params;
		$article_id   = $app->input->get('id');
		$layout       = $pluginParams->get('layout', 'default');
		$layoutPath   = PluginHelper::getLayoutPath('content', 'kunenadiscuss', $layout);

		$ordering = $pluginParams->get('ordering', 1);        // 0=ASC, 1=DESC
		$params   = [
			'catid'            => $category->id,
			'id'               => $topic->id,
			'limitstart'       => (int) !$ordering,
			'limit'            => $pluginParams->get('limit', 25),
			'filter_order_Dir' => $ordering ? 'desc' : 'asc',
			'templatepath'     => dirname($layoutPath)
		];

		$messages          = KunenaMessageHelper::getMessagesByTopic($topic->id, (int) !$ordering, $pluginParams->get('limit', 25), $ordering ? 'desc' : 'asc');
		$messageLayoutPath = PluginHelper::getLayoutPath('content', 'kunenadiscuss', $layout);

		ob_start();
		include $messageLayoutPath;
		$messagesHtml = ob_get_clean();

		// Set the correct article id back on the content page
		$app->input->set('id', $article_id);

		return $link_topic . $messagesHtml;
	}

	/**
	 * Function to get a tmpl overrideable link
	 *
	 * @param   string  $url    The url to use on the link
	 * @param   string  $title  The Title to use on the link
	 * @param   string  $class  The class to set on the link
	 *
	 * @return string  html link
	 */
	private function getKunenaForumLink($url, $title, $class = '')
	{
		$layout         = $this->plugin->params->get('layout', 'default');
		$linkLayoutPath = PluginHelper::getLayoutPath('content', 'kunenadiscuss', $layout . '_link');

		ob_start();
		include $linkLayoutPath;
		return ob_get_clean();
	}
}
