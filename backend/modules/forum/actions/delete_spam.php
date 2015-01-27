<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This action will delete a blogpost
 *
 * @author Dieter Wyns <dieter.wyns@fork-cms.com>
 */
class BackendForumDeleteSpam extends BackendBaseActionDelete
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		BackendForumModel::deleteSpam();

		// item was deleted, so redirect
		$this->redirect(BackendModel::createURLForAction('index') . '&report=deleted-spam#tabSpam');
	}
}
