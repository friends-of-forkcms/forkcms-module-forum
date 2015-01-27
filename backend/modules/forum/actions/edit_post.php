<?php

/**
 * Display a form to edit an existing post.
 *
 * @package		backend
 * @subpackage	forum
 *
 * @author		Dieter Wyns <dieter.wyns@fork-cms.com>
 */
class BackendForumEditPost extends BackendBaseActionEdit
{
	/**
	 * Revision id
	 *
	 * @var	int
	 */
	private $revision;

	/**
	 * Execute the action.
	 *
	 * @return	void
	 */
	public function execute()
	{
		// get parameters
		$this->id = $this->getParameter('id', 'int');
		$this->revision = $this->getParameter('revision', 'int');

		// does the testimonial exist
		if($this->id !== null && BackendForumModel::existsPost($this->id))
		{
			// call parent, this will probably add some general CSS/JS or other required files
			parent::execute();

			// get data
			$this->getData();

			// get revisions
			$this->loadRevisions();

			// load form
			$this->loadForm();

			// validate form
			$this->validateForm();

			// parse
			$this->parse();

			// display
			$this->display();
		}

		// no testimonial found
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}


	/**
	 * Load the datagrid with revisions
	 */
	private function loadRevisions()
	{
		// create datagrid
		$this->dgRevisions = new BackendDataGridDB(BackendForumModel::QRY_DATAGRID_BROWSE_POST_REVISIONS, array('archived', $this->record->getId()));

		// hide columns
		$this->dgRevisions->setColumnsHidden(array('id', 'revision_id'));

		// disable paging
		$this->dgRevisions->setPaging(false);

		// html entities
		$this->dgRevisions->setColumnFunction('htmlentities', '[text]', 'text', true);

		// set headers
		$this->dgRevisions->setHeaderLabels(array('edited_on' => SpoonFilter::ucfirst(BL::lbl('LastEditedOn'))));

		// set column-functions
		$this->dgRevisions->setColumnFunction(array('BackendDataGridFunctions', 'getTimeAgo'), array('[edited_on]'), 'edited_on');

		// set column URLs
		$this->dgRevisions->setColumnURL('text', BackendModel::createURLForAction('edit_post') . '&amp;id=[id]&amp;revision=[revision_id]');

		// add use column
		$this->dgRevisions->addColumn('use_revision', null, BL::lbl('UseThisVersion'), BackendModel::createURLForAction('edit_post') . '&amp;id=[id]&amp;revision=[revision_id]', BL::lbl('UseThisVersion'));
	}


	/**
	 * Get the data.
	 *
	 * @return	void
	 */
	private function getData()
	{
		$this->record = new FrontendForumPost($this->id, $this->revision);
	}


	/**
	 * Load the form
	 *
	 * @return	void
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new BackendForm('edit');

		// set type values
		$rbtTypeValues[] = array('label' => BL::lbl('Published'), 'value' => 'visible');
		$rbtTypeValues[] = array('label' => BL::lbl('Hidden'), 'value' => 'hidden');
		$rbtTypeValues[] = array('label' => BL::lbl('Spam'), 'value' => 'spam');
		$rbtTypeValues[] = array('label' => BL::lbl('Deleted'), 'value' => 'deleted');

		// create elements
		$this->frm->addText('profile_id', $this->record->getProfileId());
		$this->frm->addTextarea('text', $this->record->getText(false));
		$this->frm->addRadiobutton('type', $rbtTypeValues, $this->record->getType());
	}


	/**
	 * Parse the form.
	 *
	 * @return	void
	 */
	protected function parse()
	{
		// call parent
		parent::parse();

		// assign fields
		$this->tpl->assign('item', $this->record->toArray());
		//$this->tpl->assign('detailUrl', SITE_URL . BackendModel::getURLForBlock('forum', 'detail') . '/' . $this->record->getTopic()->getUrl() . '#post-' . $this->record->getId());
		$this->tpl->assign('revisions', ($this->dgRevisions->getNumResults() != 0) ? $this->dgRevisions->getContent() : false);
	}


	/**
	 * Validate the form
	 *
	 * @return	void
	 */
	private function validateForm()
	{
		// is the form submitted?
		if($this->frm->isSubmitted())
		{
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// validate fields
			$this->frm->getField('profile_id')->isFilled(BL::err('FieldIsRequired'));
			$this->frm->getField('text')->isFilled(BL::err('FieldIsRequired'));

			// no errors?
			if($this->frm->isCorrect())
			{
				// build item
				$item['id'] = $this->id;
				$item['profile_id'] = $this->frm->getField('profile_id')->getValue();
				$item['text'] = $this->frm->getField('text')->getValue();
				$item['type'] = $this->frm->getField('type')->getValue();
				$item['edited_on'] = BackendModel::getUTCDate();
				
				if($this->revision) $item['revision_id'] = $this->revision;

				// update the post
				$item['revision_id'] = BackendForumModel::updatePost($item);

				// everything has been saved, so redirect to the overview
				$this->redirect(BackendModel::createURLForAction('index') . '&report=edited&highlight=row-' . $item['id']);
			}
		}
	}
}

?>