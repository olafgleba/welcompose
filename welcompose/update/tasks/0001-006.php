<?php

/**
 * Project: Welcompose
 * File: 0001-006.php
 *
 * Copyright (c) 2006 sopic GmbH
 *
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-3.0.php
 *
 * $Id$
 *
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
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
	define('TASK_MINOR', '006');
	
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
	 * Changeset: 852, 862
	 * Ticket: 11
	 * 
	 * Changes to be applied
	 * ---------------------
	 *
	 * - Create tables content_generator_forms and content_generator_form_fields 
	 * - Add page type WCOM_GENERATOR_FORM to every project
	 * - Add template types generator_form_index and generator_form_mail to
	 *   every project
	 */
	if ($major < TASK_MAJOR || ($major == TASK_MAJOR && $minor < TASK_MINOR)) {
		try {
			// begin transaction
			$BASE->db->begin();
			
			// disable foreign key checks
			$BASE->db->execute('SET FOREIGN_KEY_CHECKS = 0');
			
			// create content_generator_forms
			$BASE->db->execute('DROP TABLE IF EXISTS '.WCOM_DB_CONTENT_GENERATOR_FORMS);
			
			$sql = "
				CREATE TABLE ".WCOM_DB_CONTENT_GENERATOR_FORMS." (
				  `id` int(11) UNSIGNED NOT NULL,
				  `user` int(11) UNSIGNED NOT NULL,
				  `title` varchar(255),
				  `title_url` varchar(255),
				  `content_raw` text,
				  `content` text,
				  `text_converter` int(11) UNSIGNED,
				  `apply_macros` enum('0','1') DEFAULT '0',
				  `email_from` varchar(255),
				  `email_to` varchar(255),
				  `email_subject` varchar(255),
				  `use_captcha` enum('no','image','numeral') DEFAULT 'no',
				  `date_modified` timestamp(14),
				  `date_added` datetime,
				  PRIMARY KEY(`id`),
				  INDEX `user`(`user`),
				  INDEX `text_converter`(`text_converter`),
				  CONSTRAINT `content_generator_forms.id2content_pages.id` FOREIGN KEY (`id`)
				    REFERENCES ".WCOM_DB_CONTENT_PAGES."(`id`)
				    ON DELETE CASCADE
				    ON UPDATE CASCADE,
				  CONSTRAINT `content_generator_forms2application_text_converter` FOREIGN KEY (`text_converter`)
				    REFERENCES ".WCOM_DB_APPLICATION_TEXT_CONVERTERS."(`id`)
				    ON DELETE SET NULL
				    ON UPDATE CASCADE,
				  CONSTRAINT `content_generator_forms.user2user_user.id` FOREIGN KEY (`user`)
				    REFERENCES ".WCOM_DB_USER_USERS."(`id`)
				    ON DELETE CASCADE
				    ON UPDATE CASCADE
				)
				ENGINE=INNODB;
			";
			$BASE->db->execute($sql);
			
			// create content_generator_form_fields
			$BASE->db->execute('DROP TABLE IF EXISTS '.WCOM_DB_CONTENT_GENERATOR_FORM_FIELDS);
			
			$sql = "
				CREATE TABLE ".WCOM_DB_CONTENT_GENERATOR_FORM_FIELDS." (
				  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				  `form` int(11) UNSIGNED NOT NULL,
				  `type` enum('hidden','text','textarea','submit','reset','radio','checkbox','select') NOT NULL DEFAULT 'text',
				  `label` varchar(255),
				  `name` varchar(255),
				  `value` text,
				  `required` enum('0','1') DEFAULT '0',
				  `required_message` varchar(255),
				  `validator_regex` varchar(255),
				  `validator_message` varchar(255),
				  `sorting` char(2),
				  PRIMARY KEY(`id`),
				  INDEX `form`(`form`),
				  CONSTRAINT `content_generator_form_fields.form2content_generator_forms.id` FOREIGN KEY (`form`)
				    REFERENCES ".WCOM_DB_CONTENT_GENERATOR_FORMS."(`id`)
				    ON DELETE CASCADE
				    ON UPDATE CASCADE
				)
				ENGINE=INNODB;
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
			
			// add new page types
			foreach ($projects as $_project) {
				// get existing page types
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
					'name' => 'WCOM_GENERATOR_FORM',
					'internal_name' => 'GeneratorForm',
					'editable' => '0'
				);
				
				// if the page type already exists, force update
				$insert = true;
				foreach ($page_types as $_page_type) {
					if ($_page_type['name'] == 'WCOM_GENERATOR_FORM') {
						// prepare where clause
						$where = " WHERE `id` = :id ";
						
						// prepare bind params
						$bind_params = array(
							'id' => (int)$_page_type['id']
						);
						
						// update page type
						$BASE->db->update(WCOM_DB_CONTENT_PAGE_TYPES, $sqlData, $where, $bind_params);
						
						$insert = false;
						break;
					}
				}
				
				// create new page type
				if ($insert) {
					$BASE->db->insert(WCOM_DB_CONTENT_PAGE_TYPES, $sqlData);
				}
			}
			
			// add new template types
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
				
				// define template types to create
				$types_to_create = array(
					'generator_form_index',
					'generator_form_mail'
				);
				
				foreach ($types_to_create as $_type_name) {
					// prepare sql data
					$sqlData = array(
						'project' => (int)$_project['id'],
						'name' => $_type_name,
						'description' => null,
						'editable' => '0'
					);
				
					// if the template  already exists, force update
					$insert = true;
					foreach ($template_types as $_template_type) {
						if ($_template_type['name'] == $_type_name) {
							// prepare where clause
							$where = " WHERE `id` = :id ";
						
							// prepare bind params
							$bind_params = array(
								'id' => (int)$_template_type['id']
							);
							
							// update template_type
							$BASE->db->update(WCOM_DB_TEMPLATING_TEMPLATE_TYPES, $sqlData, $where, $bind_params);
							
							$insert = false;
							break;
						}
					}
					
					// create new template_type
					if ($insert) {
						$BASE->db->insert(WCOM_DB_TEMPLATING_TEMPLATE_TYPES, $sqlData);
					}
				}
			}
			
			// update schema version
			$sqlData = array(
				'schema_version' => TASK_MAJOR.'-'.TASK_MINOR
			);
			
			$BASE->db->update(WCOM_DB_APPLICATION_INFO, $sqlData);
			
			// commit
			$BASE->db->commit();
			
			// assign task number
			$BASE->utility->smarty->assign('task', '0001-006');
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
		
			// re-throw exception
			throw $e;
		}
	}
	
	// display the form
	define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
	$BASE->utility->smarty->display('tasks/0001-006.html', WCOM_TEMPLATE_KEY);
	
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
