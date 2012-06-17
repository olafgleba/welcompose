<?php

/**
 * Project: Welcompose
 * File: 0001-020.php
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
	define('TASK_MINOR', '020');
	
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
	 * Commit: ba1b8b4a4966b98bb88b5646ac73c390decab4dd
	 * Ticket: 40
	 * 
	 * Changes to be applied
	 * ---------------------
	 *
	 * - Add new generator form field optional attributes to table 'content_generator_form_fields'
	 *   `placeholder` varchar
	 *   `pattern` varchar(255)
	 *   `maxlength` varchar(255)
	 *   `min` varchar(255)
	 *   `max` varchar(255)
	 *   `step` varchar(255)
	 *   `required_attr` enum('0','1')
	 *   `autofocus` enum('0','1')
	 *   `readonly` enum('0','1')
	 */
	if ($major < TASK_MAJOR || ($major == TASK_MAJOR && $minor < TASK_MINOR)) {
		try {
			// begin transaction
			$BASE->db->begin();

			// add new fields
			$sql = "
				ALTER TABLE
					".WCOM_DB_CONTENT_GENERATOR_FORM_FIELDS."
				ADD
					`placeholder`
				varchar(255)
					NULL DEFAULT NULL
					AFTER `validator_message`,
				ADD
					`pattern`
				varchar(255)
					NULL DEFAULT NULL
					AFTER `placeholder`,
				ADD
					`maxlength`
				varchar(255)
					NULL DEFAULT NULL
					AFTER `pattern`,
				ADD
					`min`
				varchar(255)
					NULL DEFAULT NULL
					AFTER `maxlength`,
				ADD
					`max`
				varchar(255)
					NULL DEFAULT NULL
					AFTER `min`,
				ADD
					`step`
				varchar(255)
					NULL DEFAULT NULL
					AFTER `max`,
				ADD
					`required_attr`
				ENUM
					('0', '1') NULL DEFAULT '0'
					AFTER `step`,
				ADD
					`autofocus`
				ENUM
					('0', '1') NULL DEFAULT '0'
					AFTER `required_attr`,
				ADD
					`readonly`
				ENUM
					('0', '1') NULL DEFAULT '0'
					AFTER `autofocus`
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
	$BASE->error->displayException($e, $BASE->utility->smarty);
	$BASE->error->triggerException($e);

	// exit
	exit;
}

?>