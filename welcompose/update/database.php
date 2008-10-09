<?php

/**
 * Project: Welcompose
 * File: database.php
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
	'core',
	'loader.php'
);
$loader_path = implode(DIRECTORY_SEPARATOR, $path_parts);
require($loader_path);

// start base
/* @var $BASE base */
$BASE = load('base:base');

// deregister globals
$deregister_globals_path = dirname(__FILE__).'/../core/includes/deregister_globals.inc.php';
require(Base_Compat::fixDirectorySeparator($deregister_globals_path));

try {
	// start output buffering
	@ob_start();
	
	// load smarty
	$smarty_update_conf = dirname(__FILE__).'/smarty.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_update_conf), true);
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../core/includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);
	
	// start Base_Session
	/* @var $SESSION session */
	$SESSION = load('base:session');
	
	// let's see if the user passed step one
	if (empty($_SESSION['update']['license_confirm_license']) || !$_SESSION['update']['license_confirm_license']) {
		header("Location: license.php");
		exit;
	}
	
	// connect to database
	$BASE->loadClass('database');
	
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
	
	// make sure that we got a schema_version
	
	// Temporary change to erase obsolete tasks in intallations == 0.8.0
	// We have to zero all
	// DELETE this mod with release 0.8.4 !!!
	
	//orig.
	//if ($version == "") {

	// make sure that we got a schema_version
	if ($version == "" || $version > '0001') {
		// make sure that there's a row in application_info
		$sql = "
			SELECT
				COUNT(*)
			FROM
				".WCOM_DB_APPLICATION_INFO."
		";
		$count = $BASE->db->select($sql, 'field');
		
		if ($count == 0) {
			$BASE->db->insert(WCOM_DB_APPLICATION_INFO, array('schema_version' => null));
		}
		
		//mod, s. above
		if ($count > 0) {
			$BASE->db->update(WCOM_DB_APPLICATION_INFO, array('schema_version' => null));
		}
		// eof mod		
		
		// define major/minor initial task number
		$major = '0000';
		$minor = '000';	
	} else {
		list($major, $minor) = explode('-', $version);
	}
	
	// determine tasks to be executed
	$tasks = array();
	$task_dir_contents = scandir(dirname(__FILE__).DIRECTORY_SEPARATOR.'tasks');
	foreach ($task_dir_contents as $_file) {
		if ($_file == '.' || $_file == '..') {
			continue;
		}
		
		if (preg_match('=([0-9]{4})-([0-9]{3})\.php=', $_file, $matches)) {
			if ($matches[1] > $major || ($matches[1] == $major && $matches[2] > $minor)) {
				$tasks[$matches[1].'-'.$matches[2]] = $matches[0];
			}
		}
	}
	$BASE->utility->smarty->assign('tasks', $tasks);
	
	// display the form
	define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
	$BASE->utility->smarty->display('database.html', WCOM_TEMPLATE_KEY);
	
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