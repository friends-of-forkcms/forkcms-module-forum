<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the index-action (default), it will display the overview
 *
 * @author Dieter Wyns <dieter.wyns@fork-cms.com>
 */
class BackendForumIndex extends BackendBaseActionIndex
{
	/**
	 * The datagrids
	 *
	 * @var	SpoonDataGrid
	 */
	private $dgPublished, $dgSpam;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		$this->loadPublishedDatagrid();
		$this->loadSpamDatagrid();
		$this->parse();
		$this->display();
	}

	/**
	 * Loads the dataGrid
	 */
	private function loadPublishedDatagrid()
	{
		$this->dgPublished = new BackendDataGridDB(BackendForumModel::QRY_DATAGRID_BROWSE, array('spam', 'active', 'spam', 'active'));
		$this->dgPublished->setRowAttributes(array('id' => '[id]'));

		// add the multicheckbox column
		$this->dgPublished->setMassActionCheckboxes('checkbox', '[id]');

		// html entities
		$this->dgPublished->setColumnFunction('htmlentities', '[text]', 'text', true);

		// sorting
		$this->dgPublished->setSortingColumns(array('created_on', 'profile', 'type', 'text'), 'created_on');
		$this->dgPublished->setSortParameter('desc');

		// linkify the title column
		$this->dgPublished->setColumnURL('text', BackendModel::createURLForAction('edit_[type]') . '&amp;id=[id]');

		// linkify the profile column
		$this->dgPublished->setColumnURL('profile', BackendModel::createURLForAction('edit', 'profiles') . '&amp;id=[profile_id]');

		// hide columns
		$this->dgPublished->setColumnsHidden('profile_id');

		// add mass action dropdown
		$ddmMassAction = new SpoonFormDropdown('action', array('spam' => BL::lbl('MoveToSpam'), 'delete' => BL::lbl('Delete')), 'spam');
		$ddmMassAction->setAttribute('id', 'actionPublished');
		$ddmMassAction->setOptionAttributes('delete', array('data-message-id' => 'confirmDeletePublished'));
		$ddmMassAction->setOptionAttributes('spam', array('data-message-id' => 'confirmSpamPublished'));
		$this->dgPublished->setMassAction($ddmMassAction);

		// add extra column for button
		$this->dgPublished->addColumn('edit', null, BL::lbl('Edit'), BackendModel::createURLForAction('edit_[type]') . '&amp;id=[id]', BL::lbl('Edit'));
		$this->dgPublished->addColumn('mark_as_spam', null, BL::lbl('MarkAsSpam'), BackendModel::createURLForAction('mass_action') . '&amp;id=[id]&amp;from=published&amp;action=spam', BL::lbl('MarkAsSpam'));
	}

	/**
	 * Loads the dataGrid
	 */
	private function loadSpamDatagrid()
	{
		$this->dgSpam = new BackendDataGridDB(BackendForumModel::QRY_DATAGRID_BROWSE_SPAM, array('spam', 'active', 'spam', 'active'));
		$this->dgSpam->setRowAttributes(array('id' => '[id]'));

		// add the multicheckbox column
		$this->dgSpam->setMassActionCheckboxes('checkbox', '[id]');

		// html entities
		$this->dgSpam->setColumnFunction('htmlentities', '[text]', 'text', true);

		// sorting
		$this->dgSpam->setSortingColumns(array('created_on', 'profile', 'type', 'text'), 'created_on');
		$this->dgSpam->setSortParameter('desc');

		// linkify the profile column
		$this->dgSpam->setColumnURL('profile', BackendModel::createURLForAction('edit', 'profiles') . '&amp;id=[profile_id]');

		// hide columns
		$this->dgSpam->setColumnsHidden('profile_id');

		// add mass action dropdown
		$ddmMassAction = new SpoonFormDropdown('action', array('visible' => BL::lbl('MoveToPublished'), 'delete' => BL::lbl('Delete')), 'visible');
		$ddmMassAction->setAttribute('id', 'actionSpam');
		$ddmMassAction->setOptionAttributes('delete', array('data-message-id' => 'confirmDeleteSpam'));
		$this->dgSpam->setMassAction($ddmMassAction);

		// add extra column for button
		$this->dgSpam->addColumn('approve', null, BL::lbl('Approve'), BackendModel::createURLForAction('mass_action') . '&amp;id=[id]&amp;from=spam&amp;action=visible', BL::lbl('Approve'));
	}

	/**
	 * Parse the datagrid and the reports
	 */
	protected function parse()
	{
		parent::parse();

		// parse dataGrids
		$this->tpl->assign('dgPublished', ($this->dgPublished->getNumResults() != 0) ? $this->dgPublished->getContent() : false);
		$this->tpl->assign('numPublished', $this->dgPublished->getNumResults());
		$this->tpl->assign('dgSpam', ($this->dgSpam->getNumResults() != 0) ? $this->dgSpam->getContent() : false);
		$this->tpl->assign('numSpam', $this->dgSpam->getNumResults());
	}
}
