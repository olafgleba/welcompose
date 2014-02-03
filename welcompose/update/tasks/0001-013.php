<?php

/**
 * Project: Welcompose
 * File: 0001-013.php
 *
 * Copyright (c) 2008-2012 creatics, Olaf Gleba <og@welcompose.de>
 *
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 *
 * @author Olaf Gleba
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// get loader
$path_parts = array(
	dirname(__FILE__),
	'..',
	'..',
	'core',
	'loader.php'
);
$loader_path = implode(DIRECTORY_SEPARATOR, $path_parts);
require($loader_path);

// start base
/* @var $BASE base */
$BASE = load('base:base');

// deregister globals
$deregister_globals_path = dirname(__FILE__).'/../../core/includes/deregister_globals.inc.php';
require(Base_Compat::fixDirectorySeparator($deregister_globals_path));

try {
	// start output buffering
	@ob_start();

	// load smarty
	$smarty_update_conf = dirname(__FILE__).'/../smarty.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_update_conf), true);

	// load gettext
	$gettext_path = dirname(__FILE__).'/../../core/includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);

	// start Base_Session
	/* @var $SESSION session */
	$SESSION = load('base:session');

	// connect to database
	$BASE->loadClass('database');

	// define major/minor task number
	define('TASK_MAJOR', '0001');
	define('TASK_MINOR', '013');

	// get schema version from database
	$sql = "
		SELECT
			`schema_version`
		FROM
			".WCOM_DB_APPLICATION_INFO."
		LIMIT
			1
	";
	$version = $BASE->db->select($sql, 'field');
	list($major, $minor) = explode('-', $version);

	/*
	 * References
	 * ----------
	 *
	 * Changeset: 6e782baabffa83c7ee97a1e60d946e4b64ea86bf
	 * Ticket: 21
	 *
	 * Changes to be applied
	 * ---------------------
	 *
	 * - Create tables content_event_postings, content_event_tags, content_event_tags2content_event_postings
	 * - Add new rights: CONTENT_EVENTPOSTING_{USE,MANAGE}
	 * - Add new template types: event_index, event_rss20, event_atom10
	 * - Add new page type: WCOM_EVENT
	 */
	if ($major < TASK_MAJOR || ($major == TASK_MAJOR && $minor < TASK_MINOR)) {
		try {
			// begin transaction
			$BASE->db->begin();

			// disable foreign key checks
			$BASE->db->execute('SET FOREIGN_KEY_CHECKS = 0');

			// create content_event_postings
			$BASE->db->execute('DROP TABLE IF EXISTS '.WCOM_DB_CONTENT_EVENT_POSTINGS);

			$sql = "
				CREATE TABLE ".WCOM_DB_CONTENT_EVENT_POSTINGS." (
				  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				  `page` int(11) UNSIGNED NOT NULL,
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
			";

			$BASE->db->execute($sql);

			// create content_event_tags
			$BASE->db->execute('DROP TABLE IF EXISTS '.WCOM_DB_CONTENT_EVENT_TAGS);

			$sql = "
				CREATE TABLE ".WCOM_DB_CONTENT_EVENT_TAGS." (
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
			";

			$BASE->db->execute($sql);

			// create content_event_tags
			$BASE->db->execute('DROP TABLE IF EXISTS '.WCOM_DB_CONTENT_EVENT_TAGS2CONTENT_EVENT_POSTINGS);

			$sql = "
				CREATE TABLE ".WCOM_DB_CONTENT_EVENT_TAGS2CONTENT_EVENT_POSTINGS." (
				  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				  `posting` int(11) UNSIGNED NOT NULL,
				  `tag` int(11) UNSIGNED NOT NULL,
				  PRIMARY KEY(`id`),
				  INDEX `posting`(`posting`),
				  INDEX `tag`(`tag`),
				  CONSTRAINT `content_event_postings.id2content_event_tags.id` FOREIGN KEY (`posting`)
				    REFERENCES `content_event_postings`(`id`)
				    ON DELETE NO ACTION
				    ON UPDATE NO ACTION,
				  CONSTRAINT `content_event_tags.id2content_event_postings.id` FOREIGN KEY (`tag`)
				    REFERENCES `content_event_tags`(`id`)
				    ON DELETE NO ACTION
				    ON UPDATE NO ACTION
				)
				ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci;
			";

			$BASE->db->execute($sql);

			// enable foreign key checks
			$BASE->db->execute('SET FOREIGN_KEY_CHECKS = 1');


			// add new template/page types to every project
			$sql = "
				SELECT
					`id`
				FROM
					".WCOM_DB_APPLICATION_PROJECTS."
			";

			$projects = $BASE->db->select($sql, 'multi');

			// add new rights
			foreach ($projects as $_project) {
				// get existing rights
				$sql = "
					SELECT
						`id`,
						`project`,
						`name`
					FROM
						".WCOM_DB_USER_RIGHTS."
					WHERE
						`project` = :project
				";
				$rights = $BASE->db->select($sql, 'multi', array('project' => (int)$_project['id']));

				// define rights
				$rights_to_create = array(
					'CONTENT_EVENTPOSTING_USE' => 'Allows usage of event postings.',
					'CONTENT_EVENTPOSTING_MANAGE' => 'Allows management of event postings.'
				);

				foreach ($rights_to_create as $_name => $_description) {
					// prepare sql data
					$sqlData = array(
						'project' => (int)$_project['id'],
						'name' => $_name,
						'description' => $_description,
						'editable' => '0'
					);

					// if the right already exists, force update
					$insert = true;
					foreach ($rights as $_right) {
						if ($_right['name'] == $_name) {
							// prepare where clause
							$where = " WHERE `id` = :id ";

							// prepare bind params
							$bind_params = array(
								'id' => (int)$_right['id']
							);

							// update right
							$BASE->db->update(WCOM_DB_USER_RIGHTS, $sqlData, $where, $bind_params);

							$insert = false;
							break;
						}
					}

					// create new right
					if ($insert) {
						$BASE->db->insert(WCOM_DB_USER_RIGHTS, $sqlData);
					}
				}
			}

			// create links between new rights and user groups
			foreach ($projects as $_project) {
				// define list with group/right mappings
				$rights = array(
					'CONTENT_EVENTPOSTING_USE' => array(
						'WCOM_ADMIN',
						'WCOM_REGULAR',
						'WCOM_ANONYMOUS'
					),
					'CONTENT_EVENTPOSTING_MANAGE' => array(
						'WCOM_ADMIN',
						'WCOM_REGULAR'
					)
				);

				// sync database with list of group/right mappings
				foreach ($rights as $_right => $_groups) {
					// get right id
					$sql = "
						SELECT
							`id`
						FROM
							".WCOM_DB_USER_RIGHTS."
						WHERE
							`project` = :project
						  AND
							`name` = :name
						LIMIT
							1
					";
					$right_id = $BASE->db->select($sql, 'field', array('project' => (int)$_project['id'], 'name' => $_right));

					// if the right id is empty, we have to stop here
					if (empty($right_id) || !is_numeric($right_id)) {
						throw new Exception('Required user right could not be found');
					}

					foreach ($_groups as $_group) {
						// get right id
						$sql = "
							SELECT
								`id`
							FROM
								".WCOM_DB_USER_GROUPS."
							WHERE
								`project` = :project
							  AND
								`name` = :name
							LIMIT
								1
						";
						$group_id = $BASE->db->select($sql, 'field', array('project' => (int)$_project['id'], 'name' => $_group));

						// if the group id is empty, we have to stop here
						if (empty($group_id) || !is_numeric($group_id)) {
							throw new Exception('Required user group could not be found');
						}

						// let's see if there already is a link between group and right
						$sql = "
							SELECT
								COUNT(*)
							FROM
								".WCOM_DB_USER_GROUPS2USER_RIGHTS."
							WHERE
								`right` = :right
							  AND
								`group` = :group
						";
						$count = $BASE->db->select($sql, 'field', array('right' => $right_id, 'group' => $group_id));

						if ((int)$count > 0) {
							continue;
						} else {
							// create new link
							$sqlData = array(
								'group' => $group_id,
								'right' => $right_id
							);
							$BASE->db->insert(WCOM_DB_USER_GROUPS2USER_RIGHTS, $sqlData);
						}
					}
				}
			}

			// add new template type event_index to every project
			foreach ($projects as $_project) {
				// get existing template types
				$sql = "
					SELECT
						`id`,
						`project`,
						`name`
					FROM
						".WCOM_DB_TEMPLATING_TEMPLATE_TYPES."
					WHERE
						`project` = :project
				";
				$template_types = $BASE->db->select($sql, 'multi', array('project' => (int)$_project['id']));

				// prepare sql data
				$sqlData = array(
					'project' => (int)$_project['id'],
					'name' => 'event_index',
					'description' => '',
					'editable' => '0'
				);

				// if the template type already exists, force update
				$insert = true;
				foreach ($template_types as $_template_type) {
					if ($_template_type['name'] == $sqlData['name']) {
						// prepare where clause
						$where = " WHERE `id` = :id ";

						// prepare bind params
						$bind_params = array(
							'id' => (int)$_template_type['id']
						);

						// update right
						$BASE->db->update(WCOM_DB_TEMPLATING_TEMPLATE_TYPES, $sqlData, $where, $bind_params);

						$insert = false;
						break;
					}
				}

				// otherwise create new macro
				if ($insert) {
					$BASE->db->insert(WCOM_DB_TEMPLATING_TEMPLATE_TYPES, $sqlData);
				}
			}


			// add new template type event_rss20 to every project
			foreach ($projects as $_project) {
				// get existing template types
				$sql = "
					SELECT
						`id`,
						`project`,
						`name`
					FROM
						".WCOM_DB_TEMPLATING_TEMPLATE_TYPES."
					WHERE
						`project` = :project
				";
				$template_types = $BASE->db->select($sql, 'multi', array('project' => (int)$_project['id']));

				// prepare sql data
				$sqlData = array(
					'project' => (int)$_project['id'],
					'name' => 'event_rss20',
					'description' => '',
					'editable' => '0'
				);

				// if the template type already exists, force update
				$insert = true;
				foreach ($template_types as $_template_type) {
					if ($_template_type['name'] == $sqlData['name']) {
						// prepare where clause
						$where = " WHERE `id` = :id ";

						// prepare bind params
						$bind_params = array(
							'id' => (int)$_template_type['id']
						);

						// update right
						$BASE->db->update(WCOM_DB_TEMPLATING_TEMPLATE_TYPES, $sqlData, $where, $bind_params);

						$insert = false;
						break;
					}
				}

				// otherwise create new macro
				if ($insert) {
					$BASE->db->insert(WCOM_DB_TEMPLATING_TEMPLATE_TYPES, $sqlData);
				}
			}

			// add new template type event_atom10 to every project
			foreach ($projects as $_project) {
				// get existing template types
				$sql = "
					SELECT
						`id`,
						`project`,
						`name`
					FROM
						".WCOM_DB_TEMPLATING_TEMPLATE_TYPES."
					WHERE
						`project` = :project
				";
				$template_types = $BASE->db->select($sql, 'multi', array('project' => (int)$_project['id']));

				// prepare sql data
				$sqlData = array(
					'project' => (int)$_project['id'],
					'name' => 'event_atom10',
					'description' => '',
					'editable' => '0'
				);

				// if the template type already exists, force update
				$insert = true;
				foreach ($template_types as $_template_type) {
					if ($_template_type['name'] == $sqlData['name']) {
						// prepare where clause
						$where = " WHERE `id` = :id ";

						// prepare bind params
						$bind_params = array(
							'id' => (int)$_template_type['id']
						);

						// update right
						$BASE->db->update(WCOM_DB_TEMPLATING_TEMPLATE_TYPES, $sqlData, $where, $bind_params);

						$insert = false;
						break;
					}
				}

				// otherwise create new macro
				if ($insert) {
					$BASE->db->insert(WCOM_DB_TEMPLATING_TEMPLATE_TYPES, $sqlData);
				}
			}

			// add new page type WCOM_EVENT to every project
			foreach ($projects as $_project) {
				// get existing template types
				$sql = "
					SELECT
						`id`,
						`project`,
						`name`
					FROM
						".WCOM_DB_CONTENT_PAGE_TYPES."
					WHERE
						`project` = :project
				";
				$page_types = $BASE->db->select($sql, 'multi', array('project' => (int)$_project['id']));

				// prepare sql data
				$sqlData = array(
					'project' => (int)$_project['id'],
					'name' => 'WCOM_EVENT',
					'internal_name' => 'Event',
					'editable' => '0'
				);

				// if the template type already exists, force update
				$insert = true;
				foreach ($page_types as $_page_type) {
					if ($_page_type['name'] == $sqlData['name']) {
						// prepare where clause
						$where = " WHERE `id` = :id ";

						// prepare bind params
						$bind_params = array(
							'id' => (int)$_page_type['id']
						);

						// update right
						$BASE->db->update(WCOM_DB_CONTENT_PAGE_TYPES, $sqlData, $where, $bind_params);

						$insert = false;
						break;
					}
				}

				// otherwise create new macro
				if ($insert) {
					$BASE->db->insert(WCOM_DB_CONTENT_PAGE_TYPES, $sqlData);
				}
			}


			// update schema version
			$sqlData = array(
				'schema_version' => TASK_MAJOR.'-'.TASK_MINOR
			);

			$BASE->db->update(WCOM_DB_APPLICATION_INFO, $sqlData);

			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();

			// re-throw exception
			throw $e;
		}
	}

	// flush the buffer
	@ob_end_flush();

	exit;
} catch (Exception $e) {
	// clean the buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}

	// raise error
	$BASE->error->displayException($e, $BASE->utility->smarty);
	$BASE->error->triggerException($e);

	// exit
	exit;
}

?>