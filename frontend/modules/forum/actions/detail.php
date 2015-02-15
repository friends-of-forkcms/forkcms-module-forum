<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the detail-action
 *
 * @author Dieter Wyns <dieter.wyns@fork-cms.com>
 */
class FrontendForumDetail extends FrontendBaseBlock
{
	/**
	 * Form instance for reply
	 *
	 * @var FrontendForm
	 */
	private $frm;

	/**
	 * The topic
	 *
	 * @var	array
	 */
	private $topic;

	/**
	 * Execute the extra
	 */
	public function execute()
	{
		parent::execute();

		// hide contentTitle, in the template the title is wrapped with an inverse-option
		$this->tpl->assign('hideContentTitle', true);

		$this->loadTemplate();
		$this->getData();
		// @TODO: $this->updateStatistics();
		if(FrontendProfilesAuthentication::isLoggedIn())
		{
			$this->loadForm();
			$this->validateForm();
		}
		$this->parse();
	}

	/**
	 * Load the data, don't forget to validate the incoming data
	 */
	private function getData()
	{
		// validate incoming parameters
		if($this->URL->getParameter(1) === null) $this->redirect(FrontendNavigation::getURL(404));

		// get by URL
		$this->topic = new FrontendForumTopic();
		$this->topic->loadTopicByUrl($this->URL->getParameter(1));

		// anything found?
		if($this->topic->getTitle() == null) $this->redirect(FrontendNavigation::getURL(404));

        // tagged as a spam topic? Hide it for google
        if($this->topic->getType() == 'spam') {
            $this->header->addMetaData(array('name' => 'robots', 'content' => 'noindex, follow'), true);
        }
	}

	/**
	 * Load the form
	 */
	private function loadForm()
	{
		$this->frm = new FrontendForm('addPostForm');
		$this->frm->addMarkdownEditor('text');
	}

	/**
	 * Parse the data into the template
	 */
	private function parse()
	{
		// add to breadcrumb
		$this->breadcrumb->addElement(SpoonFilter::ucfirst($this->topic->getTitle()));

		// set meta
		$this->header->setPageTitle($this->topic->getTitle());

		// assign topic
		$this->tpl->assign('item', $this->topic->toArray());

		// parse the form
		if(isset($this->frm))
		{
			$this->frm->parse($this->tpl);
		}

		// logged in?
		$this->tpl->assign('isLoggedIn', FrontendProfilesAuthentication::isLoggedIn());
	}

	/**
	 * Validate the form
	 */
	private function validateForm()
	{
		if($this->topic->getType() != 'visible') return false;

		if($this->frm->isSubmitted())
		{
			// cleanup the submitted fields, ignore fields that were added by hackers
			$this->frm->cleanupFields();

			// validate required fields
			$this->frm->getField('text')->isFilled(FL::err('MessageIsRequired'));

			if($this->frm->isCorrect())
			{
				// data
				$post['id'] = FrontendForumModel::getMaximumPostId() + 1;
				$post['profile_id'] = FrontendProfilesAuthentication::getProfile()->getId();
				$post['topic_id'] = $this->topic->getId();
				$post['created_on'] = $post['edited_on'] = FrontendModel::getUTCDate();
				$post['status'] = 'active';
				$post['type'] = 'visible';
				$post['text'] = $this->frm->getField('text')->getValue(true);

				// insert
				$post['revision_id'] = FrontendForumModel::insertPost($post);

				// notify topic starter
				$mailValues = array();
				$mailValues['introduction'] = vsprintf(FL::msg('ForumReplyMailIntroductionTopicStarter'), $this->topic->getTitle());
				$mailValues['author'] = FrontendProfilesAuthentication::getProfile()->getDisplayName();
				$mailValues['message'] = $post['text'];
				$mailValues['callToActionUrl'] = SITE_URL . FrontendNavigation::getURLForBlock('forum', 'detail') . '/' . $this->topic->getUrl() . '#post-' . $post['id'];
				$mailValues['callToAction'] = ucfirst(FL::lbl('GoToTopic'));
				$mailValues['changeEmailSettingsUrl'] = SITE_URL . FrontendNavigation::getURLForBlock('profiles', 'settings');
				$mailValues['changeEmailSettings'] = FL::lbl('ChangeEmailSettings');

				FrontendMailer::addEmail(
					vsprintf(FL::getMessage('ForumReplyMailSubjectTopicStarter'), $this->topic->getTitle()),
					FRONTEND_PATH . '/modules/forum/layout/templates/mails/reply.tpl',
					$mailValues,
					$this->topic->getProfile()->getEmail(),
					$this->topic->getProfile()->getDisplayName()
				);

				// notify all subscribers
				$subscribers = array();

				$mailValues['introduction'] = vsprintf(FL::msg('ForumReplyMailIntroductionRepliers'), $this->topic->getTitle());

				foreach($this->topic->getPosts() as $reply)
				{
					// one mail per subsriber, not per reply
					// and don't email the person that made the reply
					if(	!in_array($reply->getProfileId(), $subscribers)
						&& $this->topic->getProfileId() != $reply->getProfileId()
						&& FrontendProfilesAuthentication::getProfile()->getId() != $reply->getProfileId()
					  )
					{
						FrontendMailer::addEmail(
							vsprintf(FL::getMessage('ForumReplyMailSubjectRepliers'), $this->topic->getTitle()),
							FRONTEND_PATH . '/modules/forum/layout/templates/mails/reply.tpl',
							$mailValues,
							$reply->getProfile()->getEmail(),
							$reply->getProfile()->getDisplayName()
						);

						$subscribers[] = $reply->getProfileId();
					}
				}

				// trigger event
				FrontendModel::triggerEvent('forum', 'after_add_post', array('post' => $post));

				// redirect to same page but with success action
				$this->redirect(SITE_URL . FrontendNavigation::getURLForBlock('forum', 'detail') . '/' . $this->topic->getUrl() . '#post-' . $post['id']);
			}
		}
	}
}
