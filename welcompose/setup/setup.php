<?php

/**
 * Project: Welcompose
 * File: setup.php
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
	
	// start Base_Session
	/* @var $SESSION session */
	$SESSION = load('base:session');
	
	// let's see if the user passed step one
	if (empty($_SESSION['setup']['license_confirm_license']) || !$_SESSION['setup']['license_confirm_license']) {
		header("Location: license.php");
		exit;
	}
	if (empty($_SESSION['setup']['database_database']) || empty($_SESSION['setup']['database_user'])) {
		header("Location: database.php");
		exit;
	}
	if (empty($_SESSION['setup']['configuration_project']) || empty($_SESSION['setup']['configuration_locale'])) {
		header("Location: configuration.php");
		exit;
	}
	
	/**
	 * write config file
	 */
	
	// prepare path to config file
	$config_path = Base_Compat::fixDirectorySeparator(dirname(__FILE__).'/../core/conf/sys.inc.php');
	
	// prepare dsn
	$params = array();
	if ($_SESSION['setup']['database_connection_method'] == 'tcp_ip') {
		if (!empty($_SESSION['setup']['database_host'])) {
			$params['host'] = $_SESSION['setup']['database_host'];
		}
		if (!empty($_SESSION['setup']['database_port'])) {
			$params['port'] = $_SESSION['setup']['database_port'];
		}
		if (!empty($_SESSION['setup']['database_database'])) {
			$params['dbname'] = $_SESSION['setup']['database_database'];
		}
	} elseif ($_SESSION['setup']['database_connection_method'] == 'socket') {
		if (!empty($_SESSION['setup']['database_unix_socket'])) {
			$params['unix_socket'] = $_SESSION['setup']['database_unix_socket'];
		}
		if (!empty($_SESSION['setup']['database_database'])) {
			$params['dbname'] = $_SESSION['setup']['database_database'];
		}
	}
	$dsn = 'mysql:';
	foreach ($params as $_key => $_value) {
		$dsn .= $_key.'='.$_value.';';
	}
	if (substr($dsn, -1, 1) == ';') {
		$dsn = substr($dsn, 0, -1);
	}
	
	// prepare array with configuration information
	$configuration = array(
		'root_www' => preg_replace("=^(.*?)/setup(.*)=", '$1', $_SERVER['SCRIPT_NAME']),
		'root_disk' => preg_replace("=^(.*?)/setup(.*)=", '$1', str_replace('\\', '/', dirname(__FILE__))),
		'locale' => $_SESSION['setup']['configuration_locale'],
		'app_key' => Base_Cnc::uniqueId(),
		'dsn' => $dsn,
		'user' => $_SESSION['setup']['database_user'],
		'password' => $_SESSION['setup']['database_password']
	);
	$BASE->utility->smarty->assign('configuration', $configuration);
	
	// write config file
	file_put_contents($config_path, $BASE->utility->smarty->fetch('sys.inc.txt'));
	
	/**
	 * reload configuration
	 */
	
	$BASE->reloadConfiguration();
	$BASE->reconfigureLocales();
	
	/**
	 * create database
	 */
	
	// start Application_Project
	/* @var $PROJECT Application_Project */
	$PROJECT = load('Application:Project');

	// start Utility_Helper
	/* @var $HELPER Utility_Helper */
	$HELPER = load('Utility:Helper');
	
	// establish database connection
	$BASE->loadClass('database');
	
	// read sql file
	$sql_path = Base_Compat::fixDirectorySeparator(dirname(__FILE__).'/wcom.sql');
	
	$contents = null;
	$handle = @fopen($sql_path, "r");
	if ($handle) {
		while (!feof($handle)) {
			$buffer = trim(fgets($handle));
			if (substr($buffer, 0, 2) != '--') {
				$contents .= $buffer."\r\n";
			}
		}
		fclose($handle);
	}
	
	
	foreach (explode(";", $contents) as $_statement) {
		$_statement = trim($_statement);
		if (preg_match("=(^CREATE TABLE|^DROP TABLE|^SET FOREIGN_KEY_CHECKS|^INSERT)=", $_statement)) {
			$BASE->db->execute($_statement);
		}
	}
	
	/**
	 * create user 
	 */
	$sqlData = array(
		'email' => 'default@welcompose.local',
		'date_added' => date('Y-m-d H:i:s')
	);
	
	$user_id = $BASE->db->insert(WCOM_DB_USER_USERS, $sqlData);
	
	define("WCOM_CURRENT_USER", $user_id);
	
	// create new project
	$sqlData = array(
		'owner' => $user_id,
		'name' => $_SESSION['setup']['configuration_project'],
		'name_url' => $HELPER->createMeaningfulString($_SESSION['setup']['configuration_project']),
		'default' => '1',
		'editable' => '1',
		'date_added' => date('Y-m-d H:i:s')
	);
	$project_id = $BASE->db->insert(WCOM_DB_APPLICATION_PROJECTS, $sqlData);
	
	// init the project from skeleton
	$PROJECT->initFromSkeleton($project_id);
	
	// create row for community settings
	$sqlData = array(
		'project' => $project_id
	);
	$BASE->db->insert(WCOM_DB_COMMUNITY_SETTINGS, $sqlData);
	
	// clean the buffer
	@ob_end_clean();
	
	// redirect
	header("Location: finish.php");
	exit;
} catch (Exception $e) {
	// clean the buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	echo $e->getMessage();
	$BASE->error->displayException($e, $BASE->utility->smarty);
	$BASE->error->triggerException($e);
	
	// exit
	exit;
}

?>