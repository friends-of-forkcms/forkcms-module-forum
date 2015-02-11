<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * In this file we store all generic functions that we will be using in the forum module
 *
 * @author Dieter Wyns <dieter.wyns@fork-cms.com>
 */
class BackendForumModel
{
	const QRY_DATAGRID_BROWSE = 
		'(	SELECT \'topic\' AS `type`, t.`id`, `title` AS `text`,
					t.`created_on`, t.`profile_id`, p.`display_name` AS `profile`
			FROM `forum_topics` t
			LEFT JOIN `profiles` p ON (p.`id` = t.`profile_id`)
			WHERE t.`type`<>? AND t.`status` = ?
		 )
		 UNION
		 (	SELECT \'post\' AS `type`, fp.`id`, LEFT(TRIM(fp.`text`), 70) AS `text`,
		 			fp.`created_on`, fp.`profile_id`, p.`display_name` AS `profile`
		 	FROM `forum_posts` fp
			LEFT JOIN `profiles` p ON (p.`id` = fp.`profile_id`)
			LEFT JOIN `forum_topics` t ON (t.`id` = fp.`topic_id`)
			WHERE fp.`type`<>? AND fp.`status` = ?
		 )';

	const QRY_DATAGRID_BROWSE_SPAM = 
		'(	SELECT \'topic\' AS `type`, t.`id`, LEFT(TRIM(t.`text`), 80) AS `text`,
					t.`created_on`, t.`profile_id`, p.`display_name` AS `profile`
			FROM `forum_topics` t
			LEFT JOIN `profiles` p ON (p.`id` = t.`profile_id`)
			WHERE t.`type`=? AND t.`status` = ?
		 )
		 UNION
		 (	SELECT \'post\' AS `type`, fp.`id`, LEFT(TRIM(fp.`text`), 150) AS `text`,
		 			fp.`created_on`, fp.`profile_id`, p.`display_name` AS `profile`
		 	FROM `forum_posts` fp
			LEFT JOIN `profiles` p ON (p.`id` = fp.`profile_id`)
			LEFT JOIN `forum_topics` t ON (t.`id` = fp.`topic_id`)
			WHERE fp.`type`=? AND fp.`status` = ?
		 )';

	const QRY_DATAGRID_BROWSE_POST_REVISIONS = 
		'SELECT i.id, i.revision_id, LEFT(TRIM(i.text), 150) AS `text`, UNIX_TIMESTAMP(i.edited_on) AS edited_on
		 FROM forum_posts AS i
		 WHERE i.status = ? AND i.id = ?
		 ORDER BY i.edited_on DESC';
	
	const QRY_DATAGRID_BROWSE_TOPIC_REVISIONS = 
		'SELECT i.id, i.revision_id, LEFT(TRIM(i.text), 150) AS `text`, UNIX_TIMESTAMP(i.edited_on) AS edited_on
		 FROM forum_topics AS i
		 WHERE i.status = ? AND i.id = ?
		 ORDER BY i.edited_on DESC';

	/**
	 * Delete all posts and topics marked as spam
	 */
	public static function deleteSpam()
	{
		$db = BackendModel::getContainer()->get('database');

		// delete records
		$db->delete('forum_posts', 'type = ?', array('spam'));
		$db->delete('forum_topics', 'type = ?', array('spam'));
	}


	/**
	 * Does the post exists?
	 */
	public static function existsPost($id)
	{
		return (bool) BackendModel::getContainer()->get('database')->getVar(
			'SELECT i.id
			 FROM forum_posts AS i
			 WHERE i.id = ?',
			array((int) $id)
		);
	}


	/**
	 * Does the topic exists?
	 */
	public static function existsTopic($id)
	{
		return (bool) BackendModel::getContainer()->get('database')->getVar(
			'SELECT i.id
			 FROM forum_topics AS i
			 WHERE i.id = ?',
			array((int) $id)
		);
	}


	/**
	 * Update an existing post
	 *
	 * @param array $item The new data.
	 * @return int
	 */
	public static function updatePost(array $item)
	{
		// get the record of the exact item we're editing
		$revision = new FrontendForumPost($item['id'], array_key_exists('revision_id', $item) ? $item['revision_id'] : null);

		// assign values
		$item['created_on'] = BackendModel::getUTCDate('Y-m-d H:i:s', $revision->getCreatedOn());
		$item['topic_id'] = $revision->getTopicId();
		$item['status'] = 'active';
		if(!array_key_exists('profile_id', $item)) $item['profile_id'] = $revision->getProfileId();
		if(!array_key_exists('type', $item)) $item['type'] = $revision->getType();

		// don't want revision id
		if(array_key_exists('revision_id', $item)) unset($item['revision_id']);

		// archive all older active versions
		BackendModel::getContainer()->get('database')->update('forum_posts', array('status' => 'archived'), 'id = ? AND status = ?', array($item['id'], 'active'));

		// how many revisions should we keep
		$rowsToKeep = (int) BackendModel::getModuleSetting('forum', 'max_num_revisions', 20);

		// set type of archive
		$archiveType = ($item['status'] == 'active' ? 'archived' : $item['status']);

		// get revision-ids for items to keep
		$revisionIdsToKeep = (array) BackendModel::getContainer()->get('database')->getColumn(
			'SELECT i.revision_id
			 FROM forum_posts AS i
			 WHERE i.id = ? AND i.status = ?
			 ORDER BY i.edited_on DESC
			 LIMIT ?',
			array($item['id'], $archiveType, 20)
		);

		// delete other revisions
		if(!empty($revisionIdsToKeep)) BackendModel::getContainer()->get('database')->delete('forum_posts', 'id = ? AND status = ? AND revision_id NOT IN (' . implode(', ', $revisionIdsToKeep) . ')', array($item['id'], $archiveType));

		// insert new version
		$item['revision_id'] = BackendModel::getContainer()->get('database')->insert('forum_posts', $item);

		// recalculate posts count
		BackendForumModel::recalculateNumPosts(array($item['topic_id']));
		BackendForumModel::refillLastPostInfo($item['topic_id']);

		// return the new revision id
		return $item['revision_id'];
	}


	/**
	 * Updates one or more post's type
	 *
	 * @param array $ids The id(s) of the post(s) to change the status for.
	 * @param string $status The new type.
	 */
	public static function updatePostTypes($ids, $type)
	{
		// make sure $ids is an array
		$ids = (array) $ids;

		// loop and cast to integers
		foreach($ids as &$id) $id = (int) $id;

		// database
		$db = BackendModel::getContainer()->get('database');

		// execute
		$db->update('forum_posts', array('type' => $type), 'id IN (' . implode(',', $ids) . ')');

		// recalculate num posts, need topic ids first
		$topicIds = (array) $db->getColumn('SELECT DISTINCT p.topic_id FROM forum_posts AS p WHERE p.id IN (' . implode(',', $ids) . ')');
		BackendForumModel::recalculateNumPosts($topicIds);
	}


	/**
	 * Update an existing topic
	 *
	 * @param array $item The new data.
	 * @return int
	 */
	public static function updateTopic(array $item)
	{
		// get the record of the exact item we're editing
		$revision = new FrontendForumTopic($item['id'], array_key_exists('revision_id', $item) ? $item['revision_id'] : null);

		// assign values
		$item['created_on'] = BackendModel::getUTCDate('Y-m-d H:i:s', $revision->getCreatedOn());
		$item['language'] = $revision->getLanguage();
		$item['status'] = 'active';
		if(!array_key_exists('type', $item)) $item['type'] = $revision->getType();
		if(!array_key_exists('url', $item)) $item['url'] = $revision->getUrl();
		if(!array_key_exists('profile_id', $item)) $item['profile_id'] = $revision->getProfileId();

		// don't want revision id
		if(array_key_exists('revision_id', $item)) unset($item['revision_id']);

		// archive all older active versions
		BackendModel::getContainer()->get('database')->update('forum_topics', array('status' => 'archived'), 'id = ? AND status = ?', array($item['id'], 'active'));

		// how many revisions should we keep
		$rowsToKeep = (int) BackendModel::getModuleSetting('forum', 'max_num_revisions', 20);

		// get revision-ids for items to keep
		$revisionIdsToKeep = (array) BackendModel::getContainer()->get('database')->getColumn(
			'SELECT i.revision_id
			 FROM forum_topics AS i
			 WHERE i.id = ? AND i.status = ?
			 ORDER BY i.edited_on DESC
			 LIMIT ?',
			array($item['id'], 'archived', 20)
		);

		// delete other revisions
		if(!empty($revisionIdsToKeep)) BackendModel::getContainer()->get('database')->delete('forum_topics', 'id = ? AND status = ? AND revision_id NOT IN (' . implode(', ', $revisionIdsToKeep) . ')', array($item['id'], 'archived'));

		// insert new version
		$item['revision_id'] = BackendModel::getContainer()->get('database')->insert('forum_topics', $item);

		// return the new revision id
		return $item['revision_id'];
	}


	/**
	 * Updates one or more topic's type
	 *
	 * @param array $ids The id(s) of the topic(s) to change the status for.
	 * @param string $status The new status.
	 */
	public static function updateTopicTypes($ids, $type)
	{
		// make sure $ids is an array
		$ids = (array) $ids;

		// loop and cast to integers
		foreach($ids as &$id) $id = (int) $id;

		// execute
		BackendModel::getContainer()->get('database')->update('forum_topics', array('type' => $type), 'id IN (' . implode(',', $ids) . ')');
	}

	/**
	 * Overwrite last post data of a topic with current data so it's back up-to-date
	 * Used when posts got deleted or marked as spam
	 *
	 * @param int	$id		Topic id
	 */
	public static function refillLastPostInfo($id)
	{
		return BackendModel::getContainer()->get('database')->execute('UPDATE `forum_topics` t
																	INNER JOIN `forum_posts` p ON (t.`id`=p.`topic_id`)
																	SET
																		t.`last_post_date`=p.`created_on`,
																		t.`last_post_author`=p.`profile_id`,
																		t.`last_post_id`=p.`id`
																	WHERE
																		p.`id`=(
																			SELECT id
																			FROM `forum_posts`
																			WHERE
																				`topic_id`=? AND
																				`type`=? AND
																				`status`=?
																			ORDER BY `created_on` DESC
																			LIMIT 1
																		)
																	', array((int) $id, 'visible', 'active'));
	}

	/**
	 * Recalculate the amount of answers (posts) on a topic
	 *
	 * @param array $ids	The id(s) of the topic(s) to recalculate
	 */
	public static function recalculateNumPosts($ids)
	{
		$ids = array_unique((array) $ids);

		// get db
		$db = BackendModel::getContainer()->get('database');

		// get counts
		$commentCounts = (array) $db->getPairs(
			'SELECT p.topic_id, COUNT(p.id) AS comment_count
			 FROM forum_posts AS p
			 WHERE p.type = ? AND p.status = ? AND p.topic_id IN (' . implode(',', $ids) . ')
			 GROUP BY p.topic_id',
			array('visible', 'active')
		);

		foreach($ids as $id)
		{
			// get count
			$count = (isset($commentCounts[$id])) ? (int) $commentCounts[$id] : 0;

			// update
			$db->update('forum_topics', array('num_posts' => $count), 'id = ?', $id);
		}

		return true;
	}
}
