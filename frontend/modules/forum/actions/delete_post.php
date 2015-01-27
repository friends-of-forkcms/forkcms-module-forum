<?php

/**
 * Delete a extensions.
 *
 * @package		frontend
 * @subpackage	forum
 *
 * @author		Dieter Wyns <dieter.wyns@fork-cms.com>
 * @since		3.5
 */
class FrontendForumDeletePost extends FrontendBaseBlock
{
	/**
	 * Execute the current action.
	 *
	 * @return	void
	 */
	public function execute()
	{
		// profile logged in
		if(FrontendProfilesAuthentication::isLoggedIn())
		{
			// get parameters
			$this->id = urldecode(SpoonFilter::getGetValue('id', null, 0));

			// does the item exist
			if($this->id !== null && BackendForumModel::existsPost($this->id))
			{
				// get the post
				$post = new FrontendForumPost($this->id);

				// authorized to delete this post?
				if($post->isAuthor())
				{
					// call parent, this will probably add some general CSS/JS or other required files
					parent::execute();

					// build item
					$item = array();
					$item['id'] = $post->getId();
					$item['edited_on'] = FrontendModel::getUTCDate();
					$item['text'] = $post->getText(false);
					$item['type'] = 'deleted';

					// update
					BackendForumModel::updatePost($item);

					// redirect back to the index
					$this->redirect(SITE_URL . FrontendNavigation::getURLForBlock('forum', 'detail') . '/' . $post->getTopic()->getUrl());
				}

				// log current user out
				else $this->redirect(FrontendNavigation::getURLForBlock('profiles', 'logout'));
			}

			// no item found
			else $this->redirect(FrontendNavigation::getURL(404));
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
}

?>