<?php

/**
 * Project: Welcompose
 * File: 0001-003.php
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
 * @copyright 2008 creatics, Olaf Gleba
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
	define('TASK_MINOR', '003');
	
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
	 * Changeset: 1234
	 * Ticket: 67
	 * 
	 * Changes to be applied
	 * ---------------------
	 *
	 *  - Add new rights: APPLICATION_SITEMAP{,FIELD}_{USE,MANAGE}
	 */
	if ($major < TASK_MAJOR || ($major == TASK_MAJOR && $minor < TASK_MINOR)) {
		try {
			// begin transaction
			$BASE->db->begin();

			// get projects
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
					'APPLICATION_SITEMAP_USE' => 'Allows usage of sitemaps.',
					'APPLICATION_SITEMAP_MANAGE' => 'Allows management of sitemaps.'
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
					'APPLICATION_SITEMAP_USE' => array(
						'WCOM_ADMIN',
						'WCOM_REGULAR'
					),
					'APPLICATION_SITEMAP_MANAGE' => array(
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
	$BASE->error->printExceptionMessage($e);
	$BASE->error->triggerException($e);
	
	// exit
	exit;
}

?>