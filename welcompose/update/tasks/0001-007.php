<?php

/**
 * Project: Welcompose
 * File: 0001-007.php
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
	define('TASK_MINOR', '007');
	
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
	 * Changeset: 931
	 * Ticket: 27
	 * 
	 * Changes to be applied
	 * ---------------------
	 *
	 * - Add template types blog_comments_atom10 and blog_comments_rss20 to
	 *   every project
	 */
	if ($major < TASK_MAJOR || ($major == TASK_MAJOR && $minor < TASK_MINOR)) {
		try {
			// begin transaction
			$BASE->db->begin();
			
			// get available projects
			$sql = "
				SELECT
					`id`
				FROM
					".WCOM_DB_APPLICATION_PROJECTS."
			";
			
			$projects = $BASE->db->select($sql, 'multi');
			
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
					'blog_comments_rss20',
					'blog_comments_atom10'
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
