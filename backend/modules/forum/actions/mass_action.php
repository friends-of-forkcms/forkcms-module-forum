<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This action is used to update one or more items (status, delete, ...)
 * An item can be a forum topic or post
 * @TODO: submit spam / ham to akismet
 *
 * @author Dieter Wyns <dieter.wyns@fork-cms.com>
 */
class BackendForumMassAction extends BackendBaseAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		// current status
		$from = SpoonFilter::getGetValue('from', array('visible', 'spam'), 'visible');

		// action to execute
		$action = SpoonFilter::getGetValue('action', array('visible', 'spam', 'delete'), 'spam');

		// no id's provided
		if(!isset($_GET['id'])) $this->redirect(BackendModel::createURLForAction('index') . '&error=no-selection');

		// redefine id's
		$ids = (array) $_GET['id'];

		// delete item(s)
		if($action == 'delete') BackendForumModel::delete($ids);

		// change statuses
		else BackendForumModel::updatePostTypes($ids, $action);

		// define report
		$report = (count($ids) > 1) ? 'items-' : 'item-';

		// init var
		if($action == 'published') $report .= 'moved-published';
		if($action == 'spam') $report .= 'moved-spam';
		if($action == 'delete') $report .= 'deleted';

		// redirect
		$this->redirect(BackendModel::createURLForAction('index') . '&report=' . $report . '#tab' . SpoonFilter::ucfirst($from));
	}
}
