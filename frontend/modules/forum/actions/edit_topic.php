<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Change the settings for the current logged in profile.
 *
 * @author Dieter Wyns <dieter.wyns@fork-cms.com>
 */
class FrontendForumEditTopic extends FrontendBaseBlock
{

	/**
	 * Topic
	 *
	 * @var FrontendForumTopic
	 */
	private $item;

	/**
	 * FrontendForm instance.
	 *
	 * @var	FrontendForm
	 */
	private $frm;

	/**
	 * Execute the extra.
	 */
	public function execute()
	{
		// profile logged in
		if(FrontendProfilesAuthentication::isLoggedIn())
		{
			$id = urldecode(SpoonFilter::getGetValue('id', null, 0));

			if($id == 0 || !BackendForumModel::existsTopic($id))
			{
				$this->redirect(FrontendNavigation::getURL(404));
			}

			parent::execute();
			$this->getData($id);
			$this->loadTemplate();
			$this->loadForm();
			$this->validateForm();
			$this->parse();
		}

		// profile not logged in
		else
		{
			$this->redirect(
				FrontendNavigation::getURLForBlock('profiles', 'login') . '?queryString=' . FrontendNavigation::getURLForBlock('forum'),
				307
			);
		}
	}

	/**
	 * Get data
	 */
	private function getData($id)
	{
		// get data
		$this->item = new FrontendForumTopic($id);
		
		// check if post is from this profile
		if($this->item->getProfile()->getId() != FrontendProfilesAuthentication::getProfile()->getId())
		{
			$this->redirect(FrontendNavigation::getURL(404));
		}
	}

	/**
	 * Load the form.
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new FrontendForm('edit');

		// create element
		$this->frm->addText('title', $this->item->getTitle());
		$this->frm->addMarkdownEditor('text', $this->item->getText(false));
	}

	/**
	 * Parse the data into the template
	 */
	private function parse()
	{
		// delete url
		$this->tpl->assign('deleteUrl', SITE_URL . FrontendNavigation::getURLForBlock('forum', 'delete_topic') . '?id=' . $this->item->getId());

		// parse the form
		$this->frm->parse($this->tpl);
	}

	/**
	 * Validate the form.
	 */
	private function validateForm()
	{
		// is the form submitted?
		if($this->frm->isSubmitted())
		{
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// validate fields
			$this->frm->getField('title')->isFilled(FL::err('FieldIsRequired'));
			$this->frm->getField('text')->isFilled(FL::err('FieldIsRequired'));

			// no errors?
			if($this->frm->isCorrect())
			{
				// build item
				$item['id'] = $this->item->getId();
				$item['revision_id'] = $this->item->getRevisionId();
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['text'] = $this->frm->getField('text')->getValue(true);
				$item['edited_on'] = FrontendModel::getUTCDate();

				// update the item
				$item['revision_id'] = BackendForumModel::updateTopic($item);

				// redirect
				$this->redirect(SITE_URL . FrontendNavigation::getURLForBlock('forum', 'detail') . '/' . $this->item->getUrl());
			}
		}
	}
}
