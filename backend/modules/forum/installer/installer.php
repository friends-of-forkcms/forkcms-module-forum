<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Installer for the location module
 *
 * @author Dieter Wyns <dieter.wyns@fork-cms.com>
 */
class ForumInstaller extends ModuleInstaller
{
	/**
	 * Install the module
	 */
	public function install()
	{
		// load install.sql
		$this->importSQL(dirname(__FILE__) . '/data/install.sql');

		// add 'location' as a module
		$this->addModule('forum');

		// import locale
		$this->importLocale(dirname(__FILE__) . '/data/locale.xml');

		// module rights
		$this->setModuleRights(1, 'forum');

		// action rights
		$this->setActionRights(1, 'forum', 'delete_spam');
		$this->setActionRights(1, 'forum', 'edit_post');
		$this->setActionRights(1, 'forum', 'edit_topic');
		$this->setActionRights(1, 'forum', 'index');
		$this->setActionRights(1, 'forum', 'mass_action');

		// set navigation
		$navigationModulesId = $this->setNavigation(null, 'Modules');
		$this->setNavigation($navigationModulesId, 'Forum', 'forum/index', array('forum/edit_post', 'forum/edit_topic'));

		// add extra's
		$this->insertExtra('forum', 'block', 'Forum', null, null, 'N');
		$this->insertExtra('forum', 'widget', 'LatestTopics', 'latest_topics', null, 'N');
	}
}
