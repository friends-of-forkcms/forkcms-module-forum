<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * In this file we store all generic functions that we will be using in the faq module
 *
 * @author Dieter Wyns <dieter.wyns@fork-cms.com>
 */
class FrontendForumModel
{
	/**
	 * Get maximum id of a post
	 *
	 * @return int
	 */
	public static function getMaximumPostId()
	{
		return (int) FrontendModel::getContainer()->get('database')->getVar('SELECT MAX(id) FROM forum_posts LIMIT 1');
	}


	/**
	 * Get maximum id of a topic
	 *
	 * @return int
	 */
	public static function getMaximumTopicId()
	{
		return (int) FrontendModel::getContainer()->get('database')->getVar('SELECT MAX(id) FROM forum_topics LIMIT 1');
	}


	/**
	 * Get all topics (at least a chunk)
	 *
	 * @param int[optional] $limit The number of items to get.
	 * @param int[optional] $offset The offset.
	 * @return array
	 */
	public static function getTopics($limit = 20, $offset = 0)
	{
		// We need to get: Topic, profile of topic author, the latest post and the latest post author
		$results = (array) FrontendModel::getContainer()->get('database')->getRecords(
			'SELECT t.`id`, t.`revision_id`, t.`profile_id`, t.`language`, t.`url`, t.`type`,
			 	t.`title`, t.`text`, t.`status`, UNIX_TIMESTAMP(t.`created_on`) AS created_on,
			 	UNIX_TIMESTAMP(t.`created_on`) AS created_on, t.`num_posts` AS num_posts,
			 	IFNULL(t.`last_post_author`, t.`profile_id`) AS last_post_author_id,
			 	UNIX_TIMESTAMP(MAX(IFNULL(t.`last_post_date`, t.`created_on`))) AS last_post_date
			 FROM forum_topics AS t
			 WHERE t.type = ? AND t.status= ?
			 GROUP BY t.`id`
			 ORDER BY `last_post_date` DESC
			 LIMIT ?, ?',
			array('visible', 'active', (int) $offset, (int) $limit)
		);

		// Assign profiles
		foreach($results as &$topic)
		{
			$author = new FrontendProfilesProfile($topic['profile_id']);
			$topic['author'] = $author->toArray();
			$lastPostAuthor = new FrontendProfilesProfile($topic['last_post_author_id']);
			$topic['last_post_author'] = $lastPostAuthor->toArray();
		}

		return $results;
	}


	/**
	 * Insert new post
	 *
	 * @param array $item
	 */
	public static function insertPost($item)
	{
		// insert and return the new revision id
		$item['revision_id'] = FrontendModel::getContainer()->get('database')->insert('forum_posts', $item);

		if($item['type'] == 'visible' && $item['status'] == 'active')
		{
			// update num posts
			FrontendModel::getContainer()->get('database')->execute('UPDATE `forum_topics`
																	 SET
																		`num_posts`=`num_posts`+1,
																		`last_post_date`=?,
																		`last_post_author`=?,
																		`last_post_id`=?
																	 WHERE `id` = ?',
																	array($item['created_on'], $item['profile_id'], $item['id'], $item['topic_id']));
		}

		// return the new revision id
		return $item['revision_id'];
	}


	/**
	 * Insert new topic
	 *
	 * @param array $item
	 */
	public static function insertTopic($item)
	{
		// insert and return the new revision id
		return FrontendModel::getContainer()->get('database')->insert('forum_topics', $item);
	}
}
