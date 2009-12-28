<?php

/**
 * Project: Welcompose
 * File: 0001-008.php
 *
 * Copyright (c) 2008 creatics
 *
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 *
 * $Id$
 *
 * @copyright 2009 creatics, Olaf Gleba
 * @author Olaf Gleba
 * @package Welcompose
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
	define('TASK_MINOR', '008');
	
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
	 * Changeset: 1376
	 * Ticket: 92
	 * 
	 * Changes to be applied
	 * ---------------------
	 *
	 * - Create tables content_simple_guestbooks, content_simple_guestbook_entries
	 * - Add new rights: CONTENT_SIMPLEGUESTBOOK{,ENTRY}_{USE,MANAGE}
	 * - Add new template types: simple_guestbook_index, simple_guestbook_form_mail
	 * - Add new page type: WCOM_SIMPLE_GUESTBOOK
	 */
	if ($major < TASK_MAJOR || ($major == TASK_MAJOR && $minor < TASK_MINOR)) {
		try {
			// begin transaction
			$BASE->db->begin();

			// disable foreign key checks
			$BASE->db->execute('SET FOREIGN_KEY_CHECKS = 0');

			// create content_simple_guestbooks
			$BASE->db->execute('DROP TABLE IF EXISTS '.WCOM_DB_CONTENT_SIMPLE_GUESTBOOKS);

			$sql = "
				CREATE TABLE ".WCOM_DB_CONTENT_SIMPLE_GUESTBOOKS." (
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
				  `date_modified` timestamp(14),
				  `date_added` datetime,
				  PRIMARY KEY  (`id`),
				  INDEX `user` (`user`),
				  INDEX `text_converter` (`text_converter`),
				  CONSTRAINT `content_simple_guestbooks.id2content_pages.id` FOREIGN KEY (`id`)
				    REFERENCES ".WCOM_DB_CONTENT_PAGES."(`id`)
				    ON DELETE CASCADE
				    ON UPDATE CASCADE,
				  CONSTRAINT `content_simple_guestbooks.user2user_user.id` FOREIGN KEY (`user`)
				    REFERENCES ".WCOM_DB_USER_USERS."(`id`)
				    ON DELETE CASCADE
				    ON UPDATE CASCADE,
				  CONSTRAINT `content_simple_guestbooks2application_text_converter` FOREIGN KEY (`text_converter`)
				    REFERENCES ".WCOM_DB_APPLICATION_TEXT_CONVERTERS."(`id`)
				    ON DELETE SET NULL
				    ON UPDATE CASCADE
				) 
				ENGINE=InnoDB;
			";

			$BASE->db->execute($sql);

			// create content_simple_guestbook_entries
			$BASE->db->execute('DROP TABLE IF EXISTS '.WCOM_DB_CONTENT_SIMPLE_GUESTBOOK_ENTRIES);

			$sql = "
				CREATE TABLE ".WCOM_DB_CONTENT_SIMPLE_GUESTBOOK_ENTRIES." (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `book` int(11) unsigned NOT NULL,
				  `user` int(11) default NULL,
				  `name` varchar(255) default NULL,
				  `email` varchar(255) default NULL,
				  `subject` varchar(255) default NULL,
				  `content` text,
				  `content_raw` text,
				  `text_converter` int(11) default NULL,
				  `date_modified` timestamp(14),
				  `date_added` datetime,
				  PRIMARY KEY  (`id`),
				  INDEX `book` (`book`),
				  INDEX `text_converter` (`text_converter`),
				  CONSTRAINT `content_simple_gb_entries.book2content_simple_gb.id` FOREIGN KEY (`book`)
				    REFERENCES ".WCOM_DB_CONTENT_SIMPLE_GUESTBOOKS." (`id`)
				    ON DELETE CASCADE
				    ON UPDATE CASCADE
				) 
				ENGINE=InnoDB;
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
					'CONTENT_SIMPLEGUESTBOOK_USE' => 'Allows usage of simple guestbooks.',
					'CONTENT_SIMPLEGUESTBOOK_MANAGE' => 'Allows management of simple guestbooks.',
					'CONTENT_SIMPLEGUESTBOOKENTRY_USE' => 'Allows usage of simple guestbook entries.',
					'CONTENT_SIMPLEGUESTBOOKENTRY_MANAGE' => 'Allows management of simple guestbook entries.'
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
					'CONTENT_SIMPLEGUESTBOOK_USE' => array(
						'WCOM_ADMIN',
						'WCOM_REGULAR',
						'WCOM_ANONYMOUS'
					),
					'CONTENT_SIMPLEGUESTBOOK_MANAGE' => array(
						'WCOM_ADMIN'
					),
					'CONTENT_SIMPLEGUESTBOOKENTRY_USE' => array(
						'WCOM_ADMIN',
						'WCOM_REGULAR',
						'WCOM_ANONYMOUS'
					),
					'CONTENT_SIMPLEGUESTBOOKENTRY_MANAGE' => array(
						'WCOM_ADMIN'
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
			
			// add new template type simple_guestbook_index to every project
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
					'name' => 'simple_guestbook_index',
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
			

			// add new template type simple_guestbook_form_mail to every project
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
					'name' => 'simple_guestbook_form_mail',
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
			
			// add new page type WCOM_SIMPLE_GUESTBOOK to every project
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
					'name' => 'WCOM_SIMPLE_GUESTBOOK',
					'internal_name' => 'SimpleGuestbook',
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