<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use dflydev\markdown\MarkdownParser;

/**
 * In this file we store all generic functions that we will be using to get and set post information.
 *
 * @author Dieter Wyns <dieter.wyns@fork-cms.com>
 */
class FrontendForumPost
{
	/**
	 * The post id.
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
	 * The topic id.
	 *
	 * @var	int
	 */
	private $topicId;

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
	 * Status
	 *
	 * @var string
	 */
	private $status;

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
	 * The body text
	 *
	 * @var	string
	 */
	private $text;

	/**
	 * Constructor.
	 *
	 * @param int[optional] $postId The post id to load data from.
	 */
	public function __construct($postId = null, $revisionId = null)
	{
		if($postId !== null) $this->loadPost((int) $postId, $revisionId);
	}

	/**
	 * Get the data of a post
	 *
	 * @param int $id Profile id to load.
	 */
	private function loadPost($id, $revisionId = null)
	{
		// get topic data
		if($revisionId == null)
		{
			$postData = (array) FrontendModel::getContainer()->get('database')->getRecord(
				'SELECT p.*, UNIX_TIMESTAMP(p.`created_on`) AS created_on, UNIX_TIMESTAMP(p.`edited_on`) AS edited_on
				 FROM `forum_posts` p
				 WHERE `id` = ? AND `status`=?
				 LIMIT 1',
				array((int) $id, 'active')
			);
		}
		else
		{
			$postData = (array) BackendModel::getContainer()->get('database')->getRecord(
				'SELECT p.*, UNIX_TIMESTAMP(p.`created_on`) AS created_on, UNIX_TIMESTAMP(p.`edited_on`) AS edited_on
				 FROM `forum_posts` p
				 WHERE `id` = ? AND `revision_id` = ?
				 LIMIT 1',
				array((int) $id, (int) $revisionId)
			);
		}

		// set properties
		if(!empty($postData)) $this->loadPostData($postData);
	}

	/**
	 * Load the data of a post
	 *
	 * @param array $data Profile data
	 */
	public function loadPostData($postData)
	{
		$this->setId($postData['id']);
		$this->setRevisionId($postData['revision_id']);
		$this->setTopicId($postData['topic_id']);
		$this->setProfileId($postData['profile_id']);
		$this->setType($postData['type']);
		$this->setText($postData['text']);
		$this->setStatus($postData['status']);
		$this->setCreatedOn($postData['created_on']);
		$this->setEditedOn($postData['edited_on']);
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
	 * Get html to display
	 *
	 * @return string
	 */
	public function getText($parseMarkdown=true)
	{
		if($parseMarkdown)
		{
			$parser = new MarkdownParser();
			return $parser->transformMarkdown(htmlentities($this->text));
		}

		return $this->text;
	}

	/**
	 * Get topic
	 *
	 * @return string
	 */
	public function getTopic()
	{
		return new FrontendForumTopic($this->topicId);
	}

	/**
	 * Is the current logged in profile the author of the post?
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
		$return['text'] = $this->getText();
		$return['id'] = $this->getId();
		$return['revision_id'] = $this->getRevisionId();
		$return['profile_id'] = $this->getProfileId();
		$return['topic_id'] = $this->getTopicId();
		$return['created_on'] = $this->getCreatedOn();
		$return['edited_on'] = $this->getEditedOn();
		$return['type'] = $this->getType();
		$return['status'] = $this->getStatus();
		$return['is_author'] = $this->isAuthor();

		$return['profile']['display_name'] = $this->getProfile()->getDisplayName();
		$return['profile']['email'] = $this->getProfile()->getEmail();
		$return['profile']['url'] = $this->getProfile()->getUrl();
		$return['profile']['gravatar_id'] = $this->getProfile()->getGravatarId();
		$return['profile']['avatar'] = $this->getProfile()->getSetting('avatar');

		return $return;
	}


	/**
	 * Getters and setters without extra logic
	 */
	public function getProfileId() { return $this->profileId; }
	public function getTopicId() { return $this->topicId; }
	public function getId() { return $this->id; }
	public function getRevisionId() { return $this->revisionId; }
	public function getCreatedOn() { return $this->createdOn; }
	public function getEditedOn() { return $this->editedOn; }
	public function getType() { return $this->type; }
	public function getStatus() { return $this->status; }

	public function setText($value) { $this->text = $value; }
	public function setProfileId($value) { $this->profileId = $value; }
	public function setTopicId($value) { $this->topicId = $value; }
	public function setId($value) { $this->id = $value; }
	public function setRevisionId($value) { $this->revisionId = $value; }
	public function setCreatedOn($value) { $this->createdOn = $value; }
	public function setEditedOn($value) { $this->editedOn = $value; }
	public function setType($value) { $this->type = $value; }
	public function setStatus($value) { $this->status = $value; }
}
