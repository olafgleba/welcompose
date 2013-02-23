-- =============================================================================
-- Diagram Name: wcom
-- Created on: 12.02.2007 12:06:08
-- Last Modified: 10.10.2011 14:46
-- Diagram Version: 206
-- =============================================================================
DROP DATABASE IF EXISTS `wcom`;

CREATE DATABASE IF NOT EXISTS `wcom`;

USE `wcom`;

SET FOREIGN_KEY_CHECKS=0;

-- Drop table application_ping_services
DROP TABLE IF EXISTS `application_ping_services`;

CREATE TABLE `application_ping_services` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `host` varchar(255),
  `port` int(11) UNSIGNED,
  `path` varchar(255),
  PRIMARY KEY(`id`),
  INDEX `project`(`project`)
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table user_users
DROP TABLE IF EXISTS `user_users`;

CREATE TABLE `user_users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255),
  `email` varchar(255),
  `homepage` varchar(255),
  `secret` varchar(255),
  `editable` enum('0','1') NOT NULL DEFAULT '1',
  `date_modified` timestamp,
  `date_added` datetime,
  `_sync` varchar(255),
  PRIMARY KEY(`id`),
  INDEX `_sync`(`_sync`)
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table application_info
DROP TABLE IF EXISTS `application_info`;

CREATE TABLE `application_info` (
  `application_version` varchar(255),
  `schema_version` varchar(255)
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

INSERT INTO `application_info` (`schema_version`) VALUES ('@@schema_version@@');


-- Drop table application_projects
DROP TABLE IF EXISTS `application_projects`;

CREATE TABLE `application_projects` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `name_url` varchar(255),
  `default` enum('0','1') DEFAULT '0',
  `editable` enum('0','1') DEFAULT '0',
  `date_modified` timestamp,
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `owner`(`owner`),
  INDEX `name_url`(`name_url`),
  INDEX `default`(`default`),
  CONSTRAINT `application_projects.owner2user_users.id` FOREIGN KEY (`owner`)
    REFERENCES `user_users`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table templating_global_files
DROP TABLE IF EXISTS `templating_global_files`;

CREATE TABLE `templating_global_files` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `description` text,
  `name_on_disk` varchar(255),
  `mime_type` varchar(255),
  `size` int(11) UNSIGNED,
  `date_modified` timestamp,
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  INDEX `name`(`name`),
  CONSTRAINT `templating_global_files.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table templating_global_templates
DROP TABLE IF EXISTS `templating_global_templates`;

CREATE TABLE `templating_global_templates` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `description` text,
  `content` longtext,
  `mime_type` varchar(50),
  `change_delimiter` enum('0','1') DEFAULT '0',
  `date_modified` timestamp,
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  INDEX `name`(`name`),
  CONSTRAINT `templating_global_templates.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table media_tags
DROP TABLE IF EXISTS `media_tags`;

CREATE TABLE `media_tags` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `first_char` char(1),
  `word` varchar(255),
  `word_url` varchar(255),
  `occurrences` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `media_tags.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table community_blog_trackback_statuses
DROP TABLE IF EXISTS `community_blog_trackback_statuses`;

CREATE TABLE `community_blog_trackback_statuses` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `community_blog_trackback_statuses.project2application_project.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table community_anti_spam_plugins
DROP TABLE IF EXISTS `community_anti_spam_plugins`;

CREATE TABLE `community_anti_spam_plugins` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `type` enum('comment','trackback') DEFAULT 'comment',
  `name` varchar(255),
  `internal_name` varchar(255),
  `priority` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `active` enum('0','1') DEFAULT '1',
  PRIMARY KEY(`id`),
  INDEX `type`(`type`),
  CONSTRAINT `community_anti_spam_plugins.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_structural_templates
DROP TABLE IF EXISTS `content_structural_templates`;

CREATE TABLE `content_structural_templates` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `description` text,
  `content` text,
  `date_added` datetime,
  `date_modified` timestamp,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `content_structural_templates.id2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_blog_podcast_categories
DROP TABLE IF EXISTS `content_blog_podcast_categories`;

CREATE TABLE `content_blog_podcast_categories` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `category` varchar(255),
  `subcategory` varchar(255),
  `date_added` datetime,
  `date_modified` timestamp,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `content_blog_podcast_categories.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table user_groups
DROP TABLE IF EXISTS `user_groups`;

CREATE TABLE `user_groups` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `description` text,
  `editable` enum('0','1') DEFAULT '0',
  `date_modified` timestamp,
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `user_groups.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table templating_template_sets
DROP TABLE IF EXISTS `templating_template_sets`;

CREATE TABLE `templating_template_sets` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `description` text,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `templating_template_sets2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_page_types
DROP TABLE IF EXISTS `content_page_types`;

CREATE TABLE `content_page_types` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `internal_name` varchar(255),
  `editable` enum('0','1') DEFAULT '1',
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `content_page_types.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_navigations
DROP TABLE IF EXISTS `content_navigations`;

CREATE TABLE `content_navigations` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `content_navigations.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table media_objects
DROP TABLE IF EXISTS `media_objects`;

CREATE TABLE `media_objects` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `description` text,
  `tags` text,
  `file_name` varchar(255),
  `file_name_on_disk` varchar(255),
  `file_mime_type` varchar(255),
  `file_width` int(11) UNSIGNED,
  `file_height` int(11) UNSIGNED,
  `file_size` int(11) UNSIGNED,
  `preview_name_on_disk` varchar(255),
  `preview_mime_type` varchar(255),
  `preview_height` int(11) UNSIGNED,
  `preview_width` int(11) UNSIGNED,
  `preview_size` int(11) UNSIGNED,
  `date_modified` timestamp,
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  INDEX `mime_type`(`file_mime_type`),
  CONSTRAINT `media_images.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table templating_template_types
DROP TABLE IF EXISTS `templating_template_types`;

CREATE TABLE `templating_template_types` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `description` text,
  `editable` enum('0','1') DEFAULT '0',
  PRIMARY KEY(`id`),
  CONSTRAINT `templating_template_types.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table media_objects2media_tags
DROP TABLE IF EXISTS `media_objects2media_tags`;

CREATE TABLE `media_objects2media_tags` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `object` int(11) UNSIGNED NOT NULL,
  `tag` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  INDEX `object`(`object`),
  INDEX `tag`(`tag`),
  CONSTRAINT `media_objects.id2media_tags.id` FOREIGN KEY (`object`)
    REFERENCES `media_objects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `media_tags.id2media_objects.id` FOREIGN KEY (`tag`)
    REFERENCES `media_tags`(`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table user_users2user_groups
DROP TABLE IF EXISTS `user_users2user_groups`;

CREATE TABLE `user_users2user_groups` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  INDEX `group`(`group`),
  INDEX `user`(`user`),
  CONSTRAINT `user_groups.id2user_users.id` FOREIGN KEY (`user`)
    REFERENCES `user_users`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `user_users.id2user_groups.id` FOREIGN KEY (`group`)
    REFERENCES `user_groups`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table user_users2application_projects
DROP TABLE IF EXISTS `user_users2application_projects`;

CREATE TABLE `user_users2application_projects` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `active` enum('0','1') DEFAULT '1',
  `author` enum('0','1') DEFAULT '0',
  PRIMARY KEY(`id`),
  CONSTRAINT `user_users.id2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `application_projects.id2user_users.id` FOREIGN KEY (`user`)
    REFERENCES `user_users`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table user_rights
DROP TABLE IF EXISTS `user_rights`;

CREATE TABLE `user_rights` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `description` text,
  `editable` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `user_rights.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table community_blog_comment_statuses
DROP TABLE IF EXISTS `community_blog_comment_statuses`;

CREATE TABLE `community_blog_comment_statuses` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `community_blog_comment_statuses.project2application_project.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table application_text_macros
DROP TABLE IF EXISTS `application_text_macros`;

CREATE TABLE `application_text_macros` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `internal_name` varchar(255),
  `type` enum('pre','post','startup','shutdown') DEFAULT 'pre',
  `editable` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `application_textmacros.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_nodes
DROP TABLE IF EXISTS `content_nodes`;

CREATE TABLE `content_nodes` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `navigation` int(11) UNSIGNED NOT NULL,
  `root_node` int(11) UNSIGNED,
  `parent` int(11) UNSIGNED,
  `lft` int(11) UNSIGNED NOT NULL,
  `rgt` int(11) UNSIGNED NOT NULL,
  `level` int(11) UNSIGNED NOT NULL,
  `sorting` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  CONSTRAINT `content_nodes.navigation2content_navigation.id` FOREIGN KEY (`navigation`)
    REFERENCES `content_navigations`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table application_text_converters
DROP TABLE IF EXISTS `application_text_converters`;

CREATE TABLE `application_text_converters` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `internal_name` varchar(255),
  `name` varchar(255),
  `default` enum('0','1') DEFAULT '0',
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  CONSTRAINT `application_text_converters.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table templating_templates
DROP TABLE IF EXISTS `templating_templates`;

CREATE TABLE `templating_templates` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `description` text,
  `content` text,
  `date_modified` timestamp,
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `type`(`type`),
  CONSTRAINT `templating_templates.type2templating_template_types.id` FOREIGN KEY (`type`)
    REFERENCES `templating_template_types`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_abbriviations
DROP TABLE IF EXISTS `content_abbreviations`;

CREATE TABLE `content_abbreviations` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `first_char` char(1),
  `long_form` varchar(255),
  `content` text,
  `content_raw` text,
  `text_converter` int(11) UNSIGNED,
  `apply_macros` enum('0','1') NOT NULL DEFAULT '0',
  `lang` varchar(2),
  `date_added` datetime,
  `date_modified` timestamp,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  INDEX `text_converter`(`text_converter`),
  CONSTRAINT `content_abbreviations.id2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_abbreviations.text_converter2text_converters.id` FOREIGN KEY (`text_converter`)
    REFERENCES `application_text_converters`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_global_boxes
DROP TABLE IF EXISTS `content_global_boxes`;

CREATE TABLE `content_global_boxes` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `content_raw` text,
  `content` text,
  `text_converter` int(11) UNSIGNED,
  `apply_macros` enum('0','1') NOT NULL DEFAULT '0',
  `priority` int(11) UNSIGNED,
  `date_modified` timestamp,
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  INDEX `text_converter`(`text_converter`),
  CONSTRAINT `content_global_boxes.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_global_boxes.text_converter2text_converters.id` FOREIGN KEY (`text_converter`)
    REFERENCES `application_text_converters`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table templating_template_sets2templating_templates
DROP TABLE IF EXISTS `templating_template_sets2templating_templates`;

CREATE TABLE `templating_template_sets2templating_templates` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template` int(11) UNSIGNED NOT NULL,
  `set` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  INDEX `template`(`template`),
  INDEX `set`(`set`),
  CONSTRAINT `templating_templates.id2templating_template_sets.id` FOREIGN KEY (`set`)
    REFERENCES `templating_template_sets`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `templating_template_sets.id2templating_templates.id` FOREIGN KEY (`template`)
    REFERENCES `templating_templates`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table user_groups2user_rights
DROP TABLE IF EXISTS `user_groups2user_rights`;

CREATE TABLE `user_groups2user_rights` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` int(11) UNSIGNED NOT NULL,
  `right` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  INDEX `group`(`group`),
  INDEX `right`(`right`),
  CONSTRAINT `user_groups.id2user_rights.id` FOREIGN KEY (`right`)
    REFERENCES `user_rights`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `user_rights.id2user_groups.id` FOREIGN KEY (`group`)
    REFERENCES `user_groups`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_pages
DROP TABLE IF EXISTS `content_pages`;

CREATE TABLE `content_pages` (
  `id` int(11) UNSIGNED NOT NULL,
  `project` int(11) UNSIGNED NOT NULL,
  `type` int(11) UNSIGNED NOT NULL,
  `template_set` int(11) UNSIGNED,
  `name` varchar(255),
  `name_url` varchar(255),
  `alternate_name` varchar(255),
  `description` text,
  `optional_text` text,
  `url` varchar(255),
  `protect` enum('0','1') DEFAULT '0',
  `index_page` enum('0','1') DEFAULT '0',
  `draft` enum('0','1') DEFAULT '0',
	`exclude` enum('0','1') DEFAULT '0',
  `no_follow` enum('0','1') DEFAULT '0',
  `image_small` int(11) UNSIGNED,
  `image_medium` int(11) UNSIGNED,
  `image_big` int(11) UNSIGNED,
  `sitemap_priority` decimal(2,1) UNSIGNED DEFAULT '0.5',
  `sitemap_changefreq` enum('always','hourly','daily','weekly','monthly','yearly','never') DEFAULT 'monthly',
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  INDEX `type`(`type`),
  INDEX `template_set`(`template_set`),
  INDEX `index_page`(`index_page`),
  INDEX `image_small`(`image_small`),
  INDEX `image_medium`(`image_medium`),
  INDEX `image_big`(`image_big`),
  UNIQUE INDEX `name_url`(`project`, `name_url`),
  CONSTRAINT `content_pages.type2content_page_types.id` FOREIGN KEY (`type`)
    REFERENCES `content_page_types`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_pages.template_set2templating_template_sets.id` FOREIGN KEY (`template_set`)
    REFERENCES `templating_template_sets`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `content_pages.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_pages.image_small2media_images.id` FOREIGN KEY (`image_small`)
    REFERENCES `media_objects`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `content_pages.image_medium2media_images.id` FOREIGN KEY (`image_medium`)
    REFERENCES `media_objects`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `content_pages.image_big2media_images.id` FOREIGN KEY (`image_big`)
    REFERENCES `media_objects`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `content_pages.id2content_nodes.id` FOREIGN KEY (`id`)
    REFERENCES `content_nodes`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table community_settings
DROP TABLE IF EXISTS `community_settings`;

CREATE TABLE `community_settings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `project` int(11) UNSIGNED NOT NULL,
  `blog_comment_display_status` int(11) UNSIGNED,
  `blog_comment_default_status` int(11) UNSIGNED,
  `blog_comment_spam_status` int(11) UNSIGNED,
  `blog_comment_ham_status` int(11) UNSIGNED,
  `blog_comment_use_captcha` enum('no','image','numeral') DEFAULT 'no',
  `blog_comment_timeframe_threshold` int(11) UNSIGNED,
  `blog_comment_bayes_autolearn` enum('0','1') DEFAULT '1',
  `blog_comment_bayes_autolearn_threshold` decimal(6,5) UNSIGNED,
  `blog_comment_bayes_spam_threshold` decimal(6,5) UNSIGNED,
  `blog_comment_text_converter` int(11) UNSIGNED,
  `blog_trackback_display_status` int(11) UNSIGNED,
  `blog_trackback_default_status` int(11) UNSIGNED,
  `blog_trackback_spam_status` int(11) UNSIGNED,
  `blog_trackback_ham_status` int(11) UNSIGNED,
  `blog_trackback_timeframe_threshold` int(11) UNSIGNED,
  `blog_trackback_bayes_autolearn` enum('0','1') DEFAULT '1',
  `blog_trackback_bayes_autolearn_threshold` decimal(6,5) UNSIGNED,
  `blog_trackback_bayes_spam_threshold` decimal(6,5) UNSIGNED,
  PRIMARY KEY(`id`),
  INDEX `project`(`project`),
  INDEX `blog_comment_display_status`(`blog_comment_display_status`),
  INDEX `blog_comment_default_status`(`blog_comment_default_status`),
  INDEX `blog_comment_possible_spam_status`(`blog_comment_spam_status`),
  INDEX `blog_comment_possible_ham_status`(`blog_comment_ham_status`),
  INDEX `blog_trackback_display_status`(`blog_trackback_display_status`),
  INDEX `blog_trackback_default_status`(`blog_trackback_default_status`),
  INDEX `blog_trackback_possible_spam_status`(`blog_trackback_spam_status`),
  INDEX `blog_trackback_possible_ham_status`(`blog_trackback_ham_status`),
  INDEX `blog_comment_text_converter`(`blog_comment_text_converter`),
  CONSTRAINT `community_settings.project2application_projects.id` FOREIGN KEY (`project`)
    REFERENCES `application_projects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `community_settings.comment_possible_spam_status2cbcs.id` FOREIGN KEY (`blog_comment_spam_status`)
    REFERENCES `community_blog_comment_statuses`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `community_settings.comment_possible_ham_status2cbcs.id` FOREIGN KEY (`blog_comment_ham_status`)
    REFERENCES `community_blog_comment_statuses`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `community_settings.comment_display_status2cbcs.id` FOREIGN KEY (`blog_comment_display_status`)
    REFERENCES `community_blog_comment_statuses`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `community_settings.comment_default_status2cbcs.id` FOREIGN KEY (`blog_comment_default_status`)
    REFERENCES `community_blog_comment_statuses`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `community_settings.trackback_default_status2cbtc.id` FOREIGN KEY (`blog_trackback_default_status`)
    REFERENCES `community_blog_trackback_statuses`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `community_settings.trackback_display_status2cbtc.id` FOREIGN KEY (`blog_trackback_display_status`)
    REFERENCES `community_blog_trackback_statuses`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `community_settings.trackback_possible_ham_status2cbtc.id` FOREIGN KEY (`blog_trackback_ham_status`)
    REFERENCES `community_blog_trackback_statuses`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `community_settings.trackback_possible_spam_status2cbtc.id` FOREIGN KEY (`blog_trackback_spam_status`)
    REFERENCES `community_blog_trackback_statuses`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `community_settings.blog_comment_text_converter2atc.id` FOREIGN KEY (`blog_comment_text_converter`)
    REFERENCES `application_text_converters`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_blog_tags
DROP TABLE IF EXISTS `content_blog_tags`;

CREATE TABLE `content_blog_tags` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page` int(11) UNSIGNED NOT NULL,
  `first_char` char(1),
  `word` varchar(255),
  `word_url` varchar(255),
  `occurrences` int(11) UNSIGNED,
  PRIMARY KEY(`id`),
  INDEX `page`(`page`),
  INDEX `first_char`(`first_char`),
  CONSTRAINT `content_blog_tags.page2content_pages.id` FOREIGN KEY (`page`)
    REFERENCES `content_pages`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_event_tags
DROP TABLE IF EXISTS `content_event_tags`;

CREATE TABLE `content_event_tags` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page` int(11) UNSIGNED NOT NULL,
  `first_char` char(1),
  `word` varchar(255),
  `word_url` varchar(255),
  `occurrences` int(11) UNSIGNED,
  PRIMARY KEY(`id`),
  INDEX `page`(`page`),
  INDEX `first_char`(`first_char`),
  CONSTRAINT `content_event_tags.page2content_pages.id` FOREIGN KEY (`page`)
    REFERENCES `content_pages`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_boxes
DROP TABLE IF EXISTS `content_boxes`;

CREATE TABLE `content_boxes` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page` int(11) UNSIGNED NOT NULL,
  `name` varchar(255),
  `content_raw` text,
  `content` text,
  `text_converter` int(11) UNSIGNED,
  `apply_macros` enum('0','1') NOT NULL DEFAULT '0',
  `priority` int(11) UNSIGNED,
  `date_modified` timestamp,
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `page`(`page`),
  INDEX `text_converter`(`text_converter`),
  UNIQUE INDEX `unique_name_per_page`(`page`, `name`),
  CONSTRAINT `content_boxes.page2content_pages.id` FOREIGN KEY (`page`)
    REFERENCES `content_pages`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_boxes.text_converter2application_text_converter.id` FOREIGN KEY (`text_converter`)
    REFERENCES `application_text_converters`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table application_ping_service_configurations
DROP TABLE IF EXISTS `application_ping_service_configurations`;

CREATE TABLE `application_ping_service_configurations` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page` int(11) UNSIGNED NOT NULL,
  `ping_service` int(11) UNSIGNED NOT NULL,
  `site_name` varchar(255),
  `site_url` varchar(255),
  `site_index` varchar(255),
  `site_feed` varchar(255),
  PRIMARY KEY(`id`),
  INDEX `page`(`page`),
  INDEX `ping_service`(`ping_service`),
  CONSTRAINT `application_ping_services.id2content_pages.id` FOREIGN KEY (`page`)
    REFERENCES `content_pages`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_pages.id2application_ping_services.id` FOREIGN KEY (`ping_service`)
    REFERENCES `application_ping_services`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_pages2user_groups
DROP TABLE IF EXISTS `content_pages2user_groups`;

CREATE TABLE `content_pages2user_groups` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page` int(11) UNSIGNED NOT NULL,
  `group` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  INDEX `page`(`page`),
  INDEX `group`(`group`),
  CONSTRAINT `content_pages.id2user_groups.id` FOREIGN KEY (`group`)
    REFERENCES `user_groups`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `user_groups.id2content_pages.id` FOREIGN KEY (`page`)
    REFERENCES `content_pages`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_simple_pages
DROP TABLE IF EXISTS `content_simple_pages`;

CREATE TABLE `content_simple_pages` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `title` varchar(255),
  `title_url` varchar(255),
  `content_raw` text,
  `content` text,
  `text_converter` int(11) UNSIGNED,
  `apply_macros` enum('0','1') NOT NULL DEFAULT '0',
  `meta_use` enum('0','1') DEFAULT '0',
  `meta_title_raw` varchar(255),
  `meta_title` varchar(255),
  `meta_keywords` text,
  `meta_description` text,
  `date_modified` timestamp,
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `user`(`user`),
  INDEX `text_converter`(`text_converter`),
  CONSTRAINT `content_simple_page.id2content_pages.id` FOREIGN KEY (`id`)
    REFERENCES `content_pages`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_simple_page.user2user_users.id` FOREIGN KEY (`user`)
    REFERENCES `user_users`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_simple_page.text_converter2application_text_converter.id` FOREIGN KEY (`text_converter`)
    REFERENCES `application_text_converters`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_simple_forms
DROP TABLE IF EXISTS `content_simple_forms`;

CREATE TABLE `content_simple_forms` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `title` varchar(255),
  `title_url` varchar(255),
  `content_raw` text,
  `content` text,
  `text_converter` int(11) UNSIGNED,
  `apply_macros` enum('0','1') NOT NULL DEFAULT '0',
  `meta_use` enum('0','1') DEFAULT '0',
  `meta_title_raw` varchar(255),
  `meta_title` varchar(255),
  `meta_keywords` text,
  `meta_description` text,
  `type` varchar(255) NOT NULL DEFAULT 'PersonalForm',
  `email_from` varchar(255),
  `email_to` varchar(255),
  `email_subject` varchar(255),
  `use_captcha` enum('no','image','numeral') DEFAULT 'no',
  `date_modified` timestamp,
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `user`(`user`),
  CONSTRAINT `content_simple_forms.user2user_users.id` FOREIGN KEY (`user`)
    REFERENCES `user_users`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_simple_forms.id2content_pages.id` FOREIGN KEY (`id`)
    REFERENCES `content_pages`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_simple_forms.text_converter2text_converters.id` FOREIGN KEY (`text_converter`)
    REFERENCES `application_text_converters`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_generator_forms
DROP TABLE IF EXISTS `content_generator_forms`;

CREATE TABLE `content_generator_forms` (
  `id` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `title` varchar(255),
  `title_url` varchar(255),
  `content_raw` text,
  `content` text,
  `text_converter` int(11) UNSIGNED,
  `apply_macros` enum('0','1') DEFAULT '0',
  `meta_use` enum('0','1') DEFAULT '0',
  `meta_title_raw` varchar(255),
  `meta_title` varchar(255),
  `meta_keywords` text,
  `meta_description` text,
  `email_from` varchar(255),
  `email_to` varchar(255),
  `email_subject` varchar(255),
  `use_captcha` enum('no','image','numeral') DEFAULT 'no',
  `date_modified` timestamp,
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `user`(`user`),
  INDEX `text_converter`(`text_converter`),
  CONSTRAINT `content_generator_forms.id2content_pages.id` FOREIGN KEY (`id`)
    REFERENCES `content_pages`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_generator_forms2application_text_converter` FOREIGN KEY (`text_converter`)
    REFERENCES `application_text_converters`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `content_generator_forms.user2user_user.id` FOREIGN KEY (`user`)
    REFERENCES `user_users`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_blog_postings
DROP TABLE IF EXISTS `content_blog_postings`;

CREATE TABLE `content_blog_postings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `title` varchar(255),
  `title_url` varchar(255),
  `summary_raw` text,
  `summary` text,
  `content_raw` text,
  `content` text,
  `feed_summary_raw` text,
  `feed_summary` text,
  `text_converter` int(11) UNSIGNED,
  `apply_macros` enum('0','1') NOT NULL DEFAULT '0',
  `meta_use` enum('0','1') DEFAULT '0',
  `meta_title_raw` varchar(255),
  `meta_title` varchar(255),
  `meta_keywords` text,
  `meta_description` text,
  `optional_content_1` text,
  `optional_content_2` text,
  `optional_content_3` text,
  `draft` enum('0','1') DEFAULT '0',
  `ping` enum('0','1') DEFAULT '1',
  `comments_enable` enum('0','1') DEFAULT '1',
  `comment_count` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `trackbacks_enable` enum('0','1') DEFAULT '1',
  `trackback_count` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `pingbacks_enable` enum('0','1') DEFAULT '1',
  `pingback_count` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `tag_count` int(11) UNSIGNED DEFAULT '0',
  `tag_array` text,
  `date_modified` timestamp,
  `date_added` datetime,
  `day_added` char(2),
  `month_added` char(2),
  `year_added` char(4),
  PRIMARY KEY(`id`),
  INDEX `page`(`page`),
  INDEX `user`(`user`),
  INDEX `text_converter`(`text_converter`),
  INDEX `day_added`(`day_added`),
  INDEX `month_added`(`month_added`),
  INDEX `year_added`(`year_added`),
  CONSTRAINT `content_blog_postings.page2content_pages.id` FOREIGN KEY (`page`)
    REFERENCES `content_pages`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_blog_postings.user2user_users.id` FOREIGN KEY (`user`)
    REFERENCES `user_users`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_blog_postings.text_conv2application_text_conv.id` FOREIGN KEY (`text_converter`)
    REFERENCES `application_text_converters`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_event_postings
DROP TABLE IF EXISTS `content_event_postings`;

CREATE TABLE `content_event_postings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `title` varchar(255),
  `title_url` varchar(255),
  `content_raw` text,
  `content` text,
  `text_converter` int(11) UNSIGNED,
  `apply_macros` enum('0','1') NOT NULL DEFAULT '0',
  `draft` enum('0','1') DEFAULT '0',
  `tag_count` int(11) UNSIGNED DEFAULT '0',
  `tag_array` text,
  `date_modified` timestamp,
  `date_added` datetime,
  `day_added` char(2),
  `month_added` char(2),
  `year_added` char(4),
  `date_start` date DEFAULT NULL,
  `date_start_time_start` time DEFAULT NULL,
  `date_start_time_end` time DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `date_end_time_start` time DEFAULT NULL,
  `date_end_time_end` time DEFAULT NULL,
  PRIMARY KEY(`id`),
  INDEX `page`(`page`),
  INDEX `user`(`user`),
  INDEX `text_converter`(`text_converter`),
  INDEX `day_added`(`day_added`),
  INDEX `month_added`(`month_added`),
  INDEX `year_added`(`year_added`),
  CONSTRAINT `content_event_postings.page2content_pages.id` FOREIGN KEY (`page`)
    REFERENCES `content_pages`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_event_postings.user2user_users.id` FOREIGN KEY (`user`)
    REFERENCES `user_users`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_event_postings.text_conv2application_text_conv.id` FOREIGN KEY (`text_converter`)
    REFERENCES `application_text_converters`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_simple_dates
DROP TABLE IF EXISTS `content_simple_dates`;

CREATE TABLE `content_simple_dates` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED NOT NULL,
  `date_start` datetime,
  `date_end` datetime,
  `location_raw` text,
  `location` text,
  `link_1` varchar(255),
  `link_2` varchar(255),
  `link_3` varchar(255),
  `sold_out_1` enum('0','1') DEFAULT '0',
  `sold_out_2` enum('0','1') DEFAULT '0',
  `sold_out_3` enum('0','1') DEFAULT '0',
  `text_converter` int(11) UNSIGNED,
  `apply_macros` enum('0','1') NOT NULL DEFAULT '0',
  `draft` enum('0','1') DEFAULT '0',
  `ping` enum('0','1') DEFAULT '1',
  `pingbacks_enable` enum('0','1') DEFAULT '1',
  `pingback_count` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `date_modified` timestamp,
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `page`(`page`),
  INDEX `user`(`user`),
  INDEX `text_converter`(`text_converter`),
  CONSTRAINT `content_simple_dates.page2content_pages.id` FOREIGN KEY (`page`)
    REFERENCES `content_pages`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_simple_dates.user2user_users.id` FOREIGN KEY (`user`)
    REFERENCES `user_users`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_simple_dates.text_conv2application_text_conv.id` FOREIGN KEY (`text_converter`)
    REFERENCES `application_text_converters`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table community_blog_comments
DROP TABLE IF EXISTS `community_blog_comments`;

CREATE TABLE `community_blog_comments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `posting` int(11) UNSIGNED NOT NULL,
  `user` int(11) UNSIGNED,
  `status` int(11) UNSIGNED,
  `name` varchar(255),
  `email` varchar(255),
  `homepage` varchar(255),
  `content_raw` text,
  `content` text,
  `original_raw` text,
  `original` text,
  `text_converter` int(11) UNSIGNED,
  `spam_report` text,
  `edited` enum('0','1') DEFAULT '0',
  `date_modified` timestamp,
  `date_added` datetime,
  PRIMARY KEY(`id`),
  INDEX `posting`(`posting`),
  INDEX `user`(`user`),
  INDEX `status`(`status`),
  INDEX `text_converter`(`text_converter`),
  CONSTRAINT `community_blog_comments.user2user_users.id` FOREIGN KEY (`user`)
    REFERENCES `user_users`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `community_blog_comments.posting2content_blog_postings.id` FOREIGN KEY (`posting`)
    REFERENCES `content_blog_postings`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `community_blog_comments2community_blog_comment_statuses` FOREIGN KEY (`status`)
    REFERENCES `community_blog_comment_statuses`(`id`)
    ON DELETE SET NULL
    ON UPDATE SET NULL,
  CONSTRAINT `community_blog_comments.text_converter2atc.id` FOREIGN KEY (`text_converter`)
    REFERENCES `application_text_converters`(`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_blog_podcasts
DROP TABLE IF EXISTS `content_blog_podcasts`;

CREATE TABLE `content_blog_podcasts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `blog_posting` int(11) UNSIGNED NOT NULL,
  `media_object` int(11) UNSIGNED NOT NULL,
  `title` varchar(255),
  `description_source` enum('summary','content','feed_summary','empty') DEFAULT 'content',
  `description` text,
  `summary_source` enum('summary','content','feed_summary','empty') DEFAULT 'summary',
  `summary` text,
  `keywords_source` enum('tags','empty') DEFAULT 'tags',
  `keywords` text,
  `category_1` int(11) UNSIGNED NOT NULL,
  `category_2` int(11) UNSIGNED,
  `category_3` int(11) UNSIGNED,
  `pub_date` datetime,
  `author` varchar(50),
  `block` enum('0','1') DEFAULT '0',
  `duration` int(11) UNSIGNED,
  `explicit` enum('yes','no','clean') DEFAULT 'no',
  `date_added` datetime,
  `date_modified` timestamp,
  PRIMARY KEY(`id`),
  INDEX `blog_posting`(`blog_posting`),
  INDEX `media_object`(`media_object`),
  INDEX `category_1`(`category_1`),
  INDEX `category_2`(`category_2`),
  INDEX `category_3`(`category_3`),
  CONSTRAINT `content_blog_podcasts.media_object2media_object.id` FOREIGN KEY (`media_object`)
    REFERENCES `media_objects`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_blog_podcasts.blog_posting2content_blog_postings.id` FOREIGN KEY (`blog_posting`)
    REFERENCES `content_blog_postings`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_blog_podcasts.category_12blog_podcast_categories.id` FOREIGN KEY (`category_1`)
    REFERENCES `content_blog_podcast_categories`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_blog_podcasts.category_22blog_podcast_categories.id` FOREIGN KEY (`category_2`)
    REFERENCES `content_blog_podcast_categories`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_blog_podcasts.category_32blog_podcast_categories.id` FOREIGN KEY (`category_3`)
    REFERENCES `content_blog_podcast_categories`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_generator_form_fields
DROP TABLE IF EXISTS `content_generator_form_fields`;

CREATE TABLE `content_generator_form_fields` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `form` int(11) UNSIGNED NOT NULL,
  `type` enum('hidden','text','textarea','submit','reset','radio','checkbox','select', 'file', 'email', 'url', 'tel', 'number', 'search', 'range') NOT NULL DEFAULT 'text',
  `label` varchar(255),
  `name` varchar(255),
  `value` text,
  `class` varchar(255),
  `required` enum('0','1') DEFAULT '0',
  `required_message` varchar(255),
  `validator_regex` varchar(255),
  `validator_message` varchar(255),
	`placeholder` varchar(255),
	`pattern` varchar(255),
	`maxlength` varchar(255),
	`min` varchar(255),
	`max` varchar(255),
	`step` varchar(255),
	`required_attr` enum('0','1') DEFAULT '0',
	`autofocus` enum('0','1') DEFAULT '0',
	`readonly` enum('0','1') DEFAULT '0',
  PRIMARY KEY(`id`),
  INDEX `form`(`form`),
  CONSTRAINT `content_generator_form_fields.form2content_generator_forms.id` FOREIGN KEY (`form`)
    REFERENCES `content_generator_forms`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_simple_guestbooks
DROP TABLE IF EXISTS `content_simple_guestbooks`;

CREATE TABLE `content_simple_guestbooks` (
  `id` int(11) unsigned NOT NULL,
  `user` int(11) unsigned NOT NULL,
  `title` varchar(255) default NULL,
  `title_url` varchar(255) default NULL,
  `content_raw` text,
  `content` text,
  `text_converter` int(11) unsigned default NULL,
  `apply_macros` enum('0','1') default '0',
  `meta_use` enum('0','1') DEFAULT '0',
  `meta_title_raw` varchar(255),
  `meta_title` varchar(255),
  `meta_keywords` text,
  `meta_description` text,
  `use_captcha` enum('no','image','numeral') default NULL,
  `allow_entry` enum('0','1') default '0',
  `send_notification` enum('0','1') default '0',
  `notification_email_from` varchar(255) default NULL,
  `notification_email_to` varchar(255) default NULL,
  `notification_email_subject` varchar(255) default NULL,
  `date_modified` timestamp,
  `date_added` datetime,
  PRIMARY KEY  (`id`),
  INDEX `user` (`user`),
  INDEX `text_converter` (`text_converter`),
  CONSTRAINT `content_simple_guestbooks.id2content_pages.id` FOREIGN KEY (`id`)
    REFERENCES `content_pages` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_simple_guestbooks.user2user_user.id` FOREIGN KEY (`user`)
    REFERENCES `user_users` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_simple_guestbooks2application_text_converter` FOREIGN KEY (`text_converter`)
    REFERENCES `application_text_converters` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) 
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_simple_guestbook_entries
DROP TABLE IF EXISTS `content_simple_guestbook_entries`;

CREATE TABLE `content_simple_guestbook_entries` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `book` int(11) unsigned NOT NULL,
  `user` int(11) default NULL,
  `name` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `subject` varchar(255) default NULL,
  `content` text,
  `content_raw` text,
  `text_converter` int(11) default NULL,
  `date_modified` timestamp,
  `date_added` datetime,
  PRIMARY KEY  (`id`),
  INDEX `book` (`book`),
  INDEX `text_converter` (`text_converter`),
  CONSTRAINT `content_simple_gb_entries.book2content_simple_gb.id` FOREIGN KEY (`book`)
    REFERENCES `content_simple_guestbooks` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) 
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_blog_tags2content_blog_postings
DROP TABLE IF EXISTS `content_blog_tags2content_blog_postings`;

CREATE TABLE `content_blog_tags2content_blog_postings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `posting` int(11) UNSIGNED NOT NULL,
  `tag` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  INDEX `posting`(`posting`),
  INDEX `tag`(`tag`),
  CONSTRAINT `content_blog_postings.id2content_blog_tags.id` FOREIGN KEY (`posting`)
    REFERENCES `content_blog_postings`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_blog_tags.id2content_blog_postings.id` FOREIGN KEY (`tag`)
    REFERENCES `content_blog_tags`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Drop table content_event_tags2content_event_postings
DROP TABLE IF EXISTS `content_event_tags2content_event_postings`;

CREATE TABLE `content_event_tags2content_event_postings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `posting` int(11) UNSIGNED NOT NULL,
  `tag` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY(`id`),
  INDEX `posting`(`posting`),
  INDEX `tag`(`tag`),
  CONSTRAINT `content_event_postings.id2content_event_tags.id` FOREIGN KEY (`posting`)
    REFERENCES `content_event_postings`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `content_event_tags.id2content_event_postings.id` FOREIGN KEY (`tag`)
    REFERENCES `content_event_tags`(`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
)
ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
