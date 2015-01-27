<?php

/**
 * This is the latest topic widget.
 *
 * @package		frontend
 * @subpackage	forum
 *
 * @author		Dieter Wyns <dieter.wyns@fork-cms.com>
 */
class FrontendForumWidgetLatestTopics extends FrontendBaseWidget
{
	/**
	 * The topics
	 *
	 * @var	array
	 */
	private $topics;


	/**
	 * Execute the extra
	 *
	 * @return	void
	 */
	public function execute()
	{
		// parent execute
		parent::execute();

		// load data
		$this->loadData();

		// load template
		$this->loadTemplate();

		// parse
		$this->parse();
	}


	/**
	 * Load the data
	 *
	 * @return	void
	 */
	private function loadData()
	{
		// fetch the item
		$this->topics = FrontendForumModel::getTopics();
	}


	/**
	 * Parse into template
	 *
	 * @return	void
	 */
	private function parse()
	{
		// assign data
		$this->tpl->assign('items', $this->topics);
	}
}

?>