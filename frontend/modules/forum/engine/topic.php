<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use \Michelf\Markdown;

/**
 * In this file we store all generic functions that we will be using to get and set topic information.
 *
 * @author Dieter Wyns <dieter.wyns@fork-cms.com>
 */
class FrontendForumTopic
{
	/**
	 * The profile of topic creator.
	 *
	 * @var	string
	 */
	private $profile;

	/**
	 * The profile id of topic creator.
	 *
	 * @var	int
	 */
	private $profileId;

	/**
	 * The topic id.
	 *
	 * @var	int
	 */
	private $id;

	/**
	 * The revision id.
	 *
	 * @var	int
	 */
	private $revisionId;

	/**
	 * The datetime the topic was published
	 *
	 * @var	int
	 */
	private $createdOn;

	/**
	 * The datetime the topic was last edited
	 * NOT last replied
	 *
	 * @var	int
	 */
	private $editedOn;

	/**
	 * Type
	 * Possible values: Visible, hidden, spam, deleted
	 *
	 * @var	string
	 */
	private $type;

	/**
	 * The topic url.
	 *
	 * @var	string
	 */
	private $url;

	/**
	 * The title
	 *
	 * @var	string
	 */
	private $title;

	/**
	 * The body text
	 *
	 * @var	string
	 */
	private $text;

	/**
	 * Posts within this topic
	 *
	 * @var	array
	 */
	private $posts;

	/**
	 * Constructor.
	 *
	 * @param int[optional] $topicId The topic id to load data from.
	 */
	public function __construct($topicId = null)
	{
		if($topicId !== null) $this->loadTopic((int) $topicId);
	}

	/**
	 * Load the data of a topic
	 * Not the posts
	 * Not the detailed profile information
	 *
	 * @param int $id Profile id to load.
	 */
	private function loadTopic($id)
	{
		// get topic data
		$topicData = (array) FrontendModel::getContainer()->get('database')->getRecord(
			'SELECT t.`id`, t.`revision_id`, t.`profile_id`, t.`language`, t.`url`, t.`type`,
					t.`title`, t.`text`, t.`status`, UNIX_TIMESTAMP(t.`created_on`) AS created_on,
					UNIX_TIMESTAMP(t.`edited_on`) AS edited_on, t.`num_posts`
			 FROM `forum_topics` t
			 WHERE `id` = ?',
			(int) $id
		);

		// set properties
		$this->loadByData($topicData);
	}

	/**
	 * Array to object
	 *
	 * @param array $data
	 */
	public function loadByData($topicData)
	{
		$this->setId($topicData['id']);
		$this->setRevisionId($topicData['revision_id']);
		$this->setProfileId($topicData['profile_id']);
		$this->setLanguage($topicData['language']);
		$this->setUrl($topicData['url']);
		$this->setType($topicData['type']);
		$this->setTitle($topicData['title']);
		$this->setText($topicData['text']);
		$this->setStatus($topicData['status']);
		$this->setCreatedOn($topicData['created_on']);
		$this->setEditedOn($topicData['edited_on']);
		$this->setNumPosts($topicData['num_posts']);
	}

	/**
	 * Load the data of a topic by URL
	 * Not the posts
	 * Not the detailed profile information
	 *
	 * @param string $url
	 */
	public function loadTopicByUrl($url)
	{
		// get topic data
		$topicData = (array) FrontendModel::getContainer()->get('database')->getRecord(
			'SELECT t.`id`, t.`revision_id`, t.`profile_id`, t.`language`, t.`url`, t.`type`, 
					t.`title`, t.`text`, t.`status`, UNIX_TIMESTAMP(t.`created_on`) AS created_on, 
					UNIX_TIMESTAMP(t.`edited_on`) AS edited_on, t.`num_posts` 
			 FROM `forum_topics` t
			 WHERE t.`url` = ?',
			(string) $url
		);

		// set properties
		$this->loadByData($topicData);
	}

	/**
	 * Load all posts of the topic
	 * Topic needs to be loaded for this
	 */
	private function loadPosts()
	{
		if($this->getId() != null)
		{
			// clear
			$this->posts = array();

			// get posts
			$postsData = (array) FrontendModel::getContainer()->get('database')->getRecords(
				'SELECT p.`id`, p.`revision_id`, p.`profile_id`, p.`topic_id`, p.`type`,
						p.`text`, p.`status`, UNIX_TIMESTAMP(p.`created_on`) AS created_on,
						UNIX_TIMESTAMP(p.`edited_on`) AS edited_on
				 FROM `forum_posts` p
				 WHERE p.`topic_id` = ? AND p.`status` = ? AND p.`type` = ?
				 ORDER BY created_on ASC',
				array($this->getId(), 'active', 'visible')
			);

			// add all objects from array
			foreach($postsData as $postData)
			{
				$post = new FrontendForumPost();
				$post->loadPostData($postData);
				$this->posts[] = $post;
			}
		}
	}

	/**
	 * Get profile object
	 */
	public function getProfile()
	{
		if($this->profile == null) $this->profile = new FrontendProfilesProfile($this->getProfileId());

		return $this->profile;
	}

	/**
	 * Get posts
	 */
	public function getPosts()
	{
		if($this->posts == null) $this->loadPosts();

		return $this->posts;
	}

	/**
	 * Is the current logged in profile the author of the topic?
	 *
	 * @return bool
	 */
	public function isAuthor()
	{
		if(FrontendProfilesAuthentication::isLoggedIn() && $this->getProfile()->getId() == FrontendProfilesAuthentication::getProfile()->getId())
		{
			return true;
		}

		return false;
	}

	/**
	 * Convert the object into an array for usage in the template
	 *
	 * @return array
	 */
	public function toArray()
	{
		$return['title'] = $this->getTitle();
		$return['text'] = $this->getText();
		$return['id'] = $this->getId();
		$return['revision_id'] = $this->getRevisionId();
		$return['profile_id'] = $this->getProfileId();
		$return['created_on'] = $this->getCreatedOn();
		$return['edited_on'] = $this->getEditedOn();
		$return['type'] = $this->getType();
		$return['status'] = $this->getStatus();
		$return['language'] = $this->getLanguage();
		$return['url'] = $this->getUrl();
		$return['num_posts'] = $this->getNumPosts();
		$return['is_author'] = $this->isAuthor();

		$return['profile']['display_name'] = $this->getProfile()->getDisplayName();
		$return['profile']['email'] = $this->getProfile()->getEmail();
		$return['profile']['url'] = $this->getProfile()->getUrl();
		$return['profile']['gravatar_id'] = $this->getProfile()->getGravatarId();
		$return['profile']['avatar'] = $this->getProfile()->getSetting('avatar');

		foreach($this->getPosts() as $post)
		{
			$return['posts'][] = $post->toArray();
		}

		return $return;
	}

	/**
	 * Get html to display
	 *
	 * @return string
	 */
	public function getText($parseMarkdown=true)
	{
		if($parseMarkdown)
		{
			// Transform markdown to html
			$content = Markdown::defaultTransform($this->text);

			// Purify the html to avoid XSS
			$config = HTMLPurifier_Config::createDefault();
			$config->set('Core.Encoding', 'UTF-8');
			$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
			$config->set('Cache.DefinitionImpl', null);
			$config->set('Core.EscapeInvalidTags', true);

			// Set allowed html tags (based on stackoverflow whitelist)
			$config->set(
				'HTML.Allowed',
				'a[href|title],b,strong,blockquote[cite],code,del,dd,dl,dt,em,h1,h2,h3,h4,h5,h6,i,li,ol,p,pre,s,sup,sub,strong,strike,ul,br,hr,
				img[src|alt|title]'
			);

			$purifier = new HTMLPurifier($config);

			return $purifier->purify($content);
		}

		return $this->text;
	}

	/**
	 * Getters and setters without extra logic
	 */
	public function getTitle() { return $this->title; }
	
	public function getProfileId() { return $this->profileId; }
	public function getId() { return $this->id; }
	public function getRevisionId() { return $this->revisionId; }
	public function getCreatedOn() { return $this->createdOn; }
	public function getEditedOn() { return $this->editedOn; }
	public function getType() { return $this->type; }
	public function getStatus() { return $this->status; }
	public function getLanguage() { return $this->language; }
	public function getUrl() { return $this->url; }
	public function getNumPosts() { return $this->numPosts; }

	public function setTitle($value) { $this->title = $value; }
	public function setText($value) { $this->text = $value; }
	public function setProfileId($value) { $this->profileId = $value; }
	public function setId($value) { $this->id = $value; }
	public function setRevisionId($value) { $this->revisionId = $value; }
	public function setCreatedOn($value) { $this->createdOn = $value; }
	public function setEditedOn($value) { $this->editedOn = $value; }
	public function setType($value) { $this->type = $value; }
	public function setStatus($value) { $this->status = $value; }
	public function setLanguage($value) { $this->language = $value; }
	public function setUrl($value) { $this->url = $value; }
	public function setNumPosts($value) { $this->numPosts = $value; }
}
