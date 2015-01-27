<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the index-action
 *
 * @author Dieter Wyns <dieter.wyns@fork-cms.com>
 */
class FrontendForumIndex extends FrontendBaseBlock
{
	/**
	 * Topics
	 *
	 * @var	array
	 */
	private $topics;

	/**
	 * Execute the extra
	 */
	public function execute()
	{
		parent::execute();

		$this->getData();
		$this->loadTemplate();
		$this->parse();
	}

	/**
	 * Load the data
	 */
	private function getData()
	{
		// @TODO pagination
		$this->topics = FrontendForumModel::getTopics();
	}

	/**
	 * Parse the data into the template
	 */
	private function parse()
	{
		$this->tpl->assign('items', (array) $this->topics);
	}
}
