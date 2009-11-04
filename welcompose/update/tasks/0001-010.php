<?php

/**
 * Project: Welcompose
 * File: 0001-010.php
 *
 * Copyright (c) 2009 creatics media.systems
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
 * @copyright 2009 creatics media.systems, Olaf Gleba
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
	define('TASK_MINOR', '010');
	
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
	 * Changeset: 1373
	 * Ticket: 104
	 * 
	 * Changes to be applied
	 * ---------------------
	 *
	 *  - Add new fields to table 'content_generator_forms', 'content_simple_forms'
	 *    `meta_use` enum ('0','1')
	 *    `meta_title_raw` varchar(255)
	 *    `meta_title` varchar(255)
	 *    `meta_keywords` text
	 *    `meta_description` text
	 */
	if ($major < TASK_MAJOR || ($major == TASK_MAJOR && $minor < TASK_MINOR)) {
		try {
			// begin transaction
			$BASE->db->begin();
		
			// add new fields
			$sql = "
				ALTER TABLE
					".WCOM_DB_CONTENT_GENERATOR_FORMS."
				ADD
					`meta_use`
				ENUM
					('0', '1') NULL DEFAULT '0' 
				AFTER `apply_macros`,
				ADD
					`meta_title_raw` 
				VARCHAR
					(255)
				AFTER `meta_use`,
				ADD
					`meta_title` 
				VARCHAR
					(255)
				AFTER `meta_title_raw`,
				ADD
					`meta_keywords` 
				TEXT 
				AFTER `meta_title`,
				ADD
					`meta_description` 
				TEXT 
				AFTER `meta_keywords`
				
			";

			$BASE->db->execute($sql);
			
			$sql = "
				ALTER TABLE
					".WCOM_DB_CONTENT_SIMPLE_FORMS."
				ADD
					`meta_use`
				ENUM
					('0', '1') NULL DEFAULT '0' 
				AFTER `apply_macros`,
				ADD
					`meta_title_raw` 
				VARCHAR
					(255)
				AFTER `meta_use`,
				ADD
					`meta_title` 
				VARCHAR
					(255)
				AFTER `meta_title_raw`,
				ADD
					`meta_keywords` 
				TEXT 
				AFTER `meta_title`,
				ADD
					`meta_description` 
				TEXT 
				AFTER `meta_keywords`
				
			";

			$BASE->db->execute($sql);
			
			$sql = "
				ALTER TABLE
					".WCOM_DB_CONTENT_BLOG_POSTINGS."
				ADD
					`meta_use`
				ENUM
					('0', '1') NULL DEFAULT '0' 
				AFTER `apply_macros`,
				ADD
					`meta_title_raw` 
				VARCHAR
					(255)
				AFTER `meta_use`,
				ADD
					`meta_title` 
				VARCHAR
					(255)
				AFTER `meta_title_raw`,
				ADD
					`meta_keywords` 
				TEXT 
				AFTER `meta_title`,
				ADD
					`meta_description` 
				TEXT 
				AFTER `meta_keywords`
				
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