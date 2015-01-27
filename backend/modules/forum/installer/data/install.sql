CREATE TABLE IF NOT EXISTS `forum_posts` (
  `id` int(11) NOT NULL,
  `revision_id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(11) NOT NULL,
  `topic_id` int(11) unsigned NOT NULL,
  `type` enum('visible','hidden','spam','deleted') NOT NULL,
  `text` text NOT NULL,
  `status` enum('active','archived','draft') NOT NULL,
  `created_on` datetime NOT NULL,
  `edited_on` datetime NOT NULL,
  PRIMARY KEY (`revision_id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `forum_topics` (
  `id` int(11) unsigned NOT NULL,
  `revision_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(11) NOT NULL,
  `language` varchar(5) NOT NULL,
  `url` varchar(255) NOT NULL,
  `type` enum('visible','hidden','spam','deleted') NOT NULL,
  `title` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `status` enum('active','archived','draft') NOT NULL,
  `created_on` datetime NOT NULL,
  `edited_on` datetime NOT NULL,
  `num_posts` int(11) NOT NULL,
  `last_post_date` datetime DEFAULT NULL,
  `last_post_author` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_post_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`revision_id`)
) ENGINE=MyISAM;
