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
class FrontendForumAddTopic extends FrontendBaseBlock
{
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
			parent::execute();
			$this->loadTemplate();
			$this->loadForm();
			$this->validateForm();
			$this->parse();
		}

		// profile not logged in
		else
		{
			$this->redirect(
				FrontendNavigation::getURLForBlock('profiles', 'login') . '?queryString=' . FrontendNavigation::getURLForBlock('forum', 'add_topic'),
				307
			);
		}
	}

	/**
	 * Load the form.
	 */
	private function loadForm()
	{
		// create form
		$this->frm = new FrontendForm('addTopicForm');

		// create elements
		$this->frm->addText('title')->setAttribute('id', 'title');
		$this->frm->addMarkdownEditor('text');
	}

	/**
	 * Parse the data into the template
	 */
	private function parse()
	{
		// add to breadcrumb
		$this->breadcrumb->addElement(SpoonFilter::ucfirst(FL::getLabel('NewTopic')));

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
			$this->frm->getField('title')->isFilled(FL::err('TitleIsRequired'));
			$this->frm->getField('text')->isFilled(FL::err('MessageIsRequired'));

			// no errors?
			if($this->frm->isCorrect())
			{
				// build item
				$item['id'] = FrontendForumModel::getMaximumTopicId() + 1;
				$item['profile_id'] = FrontendProfilesAuthentication::getProfile()->getId();
				$item['language'] = FRONTEND_LANGUAGE;
				$item['title'] = $this->frm->getField('title')->getValue();
				$item['text'] = $this->frm->getField('text')->getValue(true);
				$item['url'] = SpoonFilter::urlise($item['title']); // @TODO improve
				$item['type'] = 'visible';
				$item['status'] = 'active';
				$item['created_on'] = $item['edited_on'] = FrontendModel::getUTCDate();

				// insert the item
				$item['revision_id'] = (int) FrontendForumModel::insertTopic($item);

				// redirect
				$this->redirect(SITE_URL . FrontendNavigation::getURLForBlock('forum', 'detail') . '/' . $item['url']);
			}
		}
	}
}
