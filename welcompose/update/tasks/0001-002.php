<?php

/**
 * Project: Welcompose
 * File: 0001-001.php
 *
 * Copyright (c) 2008 creatics media.systems
 *
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 *
 * $Id$
 *
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Andreas Ahlenstorf
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
	define('TASK_MINOR', '002');
	
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
	 * Changeset: 821
	 * Ticket: 13
	 * 
	 * Changes to be applied
	 * ---------------------
	 *
	 *  - New definition for content_simple_forms.type:
	 *    `type` varchar(255) NOT NULL DEFAULT 'PersonalForm'
	 *  - Change old ENUM values to new values:
	 *    personal becomes PersonalForm
	 *    business becomes BusinessForm
	 * 
	 */
	if ($major < TASK_MAJOR || ($major == TASK_MAJOR && $minor < TASK_MINOR)) {
		try {
			// begin transaction
			$BASE->db->begin();
		
			// convert content_simple_forms.type to new type
			$sql = "
				ALTER TABLE
					".WCOM_DB_CONTENT_SIMPLE_FORMS."
				CHANGE
					`type` `type` VARCHAR(255) NOT NULL DEFAULT 'PersonalForm'
			";
			$BASE->db->execute($sql);
		
			// convert old enum default values to the new ones
			$sql = "
				UPDATE
					".WCOM_DB_CONTENT_SIMPLE_FORMS."
				SET
					`type` = 'PersonalForm'
				WHERE
					`type` = 'personal'
			";
			$BASE->db->execute($sql);
		
			$sql = "
				UPDATE
					".WCOM_DB_CONTENT_SIMPLE_FORMS."
				SET
					`type` = 'BusinessForm'
				WHERE
					`type` = 'business'
			";
			$BASE->db->execute($sql);
			
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
	
	// assign task number
	$BASE->utility->smarty->assign('task', TASK_MAJOR.'-'.TASK_MINOR);
	
	// display the form
	define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
	$BASE->utility->smarty->display('tasks/0001-002.html', WCOM_TEMPLATE_KEY);
	
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