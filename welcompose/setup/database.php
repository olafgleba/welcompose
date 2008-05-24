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
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-3.0.php
 *
 * $Id$
 *
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
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
	
	// prepare array with connection methods
	$connection_methods = array(
		'tcp_ip' => 'TCP/IP (Network)',
		'socket' => 'Unix socket'
	);
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('database', 'post');
	
	// textfield for database
	$FORM->addElement('text', 'database', 'Database', 
		array('id' => 'database_database', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('database', 'trim');
	$FORM->applyFilter('database', 'strip_tags');
	$FORM->addRule('database', 'Please enter a database to use', 'required');
	$FORM->addRule('database', 'Please enter a valid database name', WCOM_REGEX_DATABASE_NAME);
	
	// textfield for user
	$FORM->addElement('text', 'user', 'User', 
		array('id' => 'database_user', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('user', 'trim');
	$FORM->applyFilter('user', 'strip_tags');
	$FORM->addRule('user', 'Please enter a user to use for the connection', 'required');
	
	// textfield for password
	$FORM->addElement('password', 'password', 'Password', 
		array('id' => 'database_password', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('password', 'trim');
	$FORM->applyFilter('password', 'strip_tags');

	// textfield for connection method
	$FORM->addElement('select', 'connection_method', 'Connection method',
		$connection_methods, array('id' => 'database_connection_method'));
	$FORM->applyFilter('connection_method', 'trim');
	$FORM->applyFilter('connection_method', 'strip_tags');
	$FORM->addRule('connection_method', 'Please choose which connection method to use', 'required');
	$FORM->addRule('connection_method', 'Selected connection method is out of range',
		'in_array_keys', $connection_methods);
	
	// textfield for host
	$FORM->addElement('text', 'host', 'Host', 
		array('id' => 'database_host', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('host', 'trim');
	$FORM->applyFilter('host', 'strip_tags');
	if ($FORM->exportValue('connection_method') == 'tcp_ip') {
		$FORM->addRule('host', 'Please enter a host to use for the connection', 'required');
	}
	
	// textfield for port
	$FORM->addElement('text', 'port', 'Port', 
		array('id' => 'database_port', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('port', 'trim');
	$FORM->applyFilter('port', 'strip_tags');
	if ($FORM->exportValue('connection_method') == 'tcp_ip') {
		$FORM->addRule('port', 'Please enter a port to use for the connection', 'required');
		$FORM->addRule('port', 'The port number must be numeric', 'numeric');
	}
	
	// textfield for unix socket
	$FORM->addElement('text', 'unix_socket', 'Unix socket', 
		array('id' => 'database_unix_socket', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('unix_socket', 'trim');
	$FORM->applyFilter('unix_socket', 'strip_tags');
	$FORM->addRule('unix_socket', 'Please enter a valid Unix socket', 'regex', WCOM_REGEX_DATABASE_SOCKET);
	
	// add connection validation rule
	$FORM->registerRule('testConnection', 'callback', 'setup_database_connection_test_callback');
	$FORM->registerRule('testVersion', 'callback', 'setup_database_version_test_callback');
	$FORM->registerRule('testInnoDb', 'callback', 'setup_database_innodb_test_callback');
	$FORM->addRule('database', 'Unable to connect to database server', 'testConnection', $FORM);
	$FORM->addRule('database', 'Selected database server is too old', 'testVersion', $FORM);
	$FORM->addRule('database', 'Selected database does not support InnoDB', 'testInnoDb', $FORM);
	
	// submit button
	$FORM->addElement('submit', 'submit', 'Go to next step',
		array('class' => 'submit200'));
		
	// validate it
	if (!$FORM->validate()) {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();
		$quickform_tpl_path = dirname(__FILE__).'/quickform.tpl.php';
		include(Base_Compat::fixDirectorySeparator($quickform_tpl_path));

		// remove attribute on form tag for XHTML compliance
		//$FORM->removeAttribute('name');
		$FORM->removeAttribute('target');
		
		$FORM->accept($renderer);
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $renderer->toArray());
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('database.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// save all the database settings to the session
		$_SESSION['setup']['database_database'] = $FORM->exportValue('database');
		$_SESSION['setup']['database_user'] = $FORM->exportValue('user');
		$_SESSION['setup']['database_password'] = $FORM->exportValue('password');
		$_SESSION['setup']['database_connection_method'] = $FORM->exportValue('connection_method');
		$_SESSION['setup']['database_host'] = $FORM->exportValue('host');
		$_SESSION['setup']['database_port'] = $FORM->exportValue('port');
		$_SESSION['setup']['database_unix_socket'] = $FORM->exportValue('unix_socket');
		
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: configuration.php");
		exit;
	}
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

function setup_database_connection_test_callback ($database, &$FORM) {
	// prepare params to create connections
	if ($FORM->exportValue('connection_method') == 'tcp_ip') {
		$dsn = sprintf("mysql:host=%s;port=%u;dbname=%s", $FORM->exportValue('host'),
			$FORM->exportValue('port'), $FORM->exportValue('database'));
	} elseif ($FORM->exportValue('connection_method') == 'socket') {
		$dsn = sprintf("mysql:unix_socket=%s;dbname=%s", $FORM->exportValue('unix_socket'),
			$FORM->exportValue('database'));
	}
	
	// prepare params array
	$params = array(
		'dsn' => $dsn,
		'username' => $FORM->exportValue('user'),
		'password' => $FORM->exportValue('password')
	);
	
	$path = dirname(__FILE__).'/../core/base_classes/pdo.class.php';
	require_once(Base_Compat::fixDirectorySeparator($path));
	
	try {
		$db = new Base_Database($params);
		return true;
	} catch (Exception $e) {
		return false;
	}
}

function setup_database_version_test_callback ($database, &$FORM) {
	// prepare params to create connections
	$params = array();
	if ($FORM->exportValue('connection_method') == 'tcp_ip') {
		if ($FORM->exportValue('host') != "") {
			$params['host'] = $FORM->exportValue('host');
		}
		if ($FORM->exportValue('port') != "") {
			$params['port'] = $FORM->exportValue('port');
		}
		if ($FORM->exportValue('database') != "") {
			$params['dbname'] = $FORM->exportValue('database');
		}
	} elseif ($FORM->exportValue('connection_method') == 'socket') {
		if ($FORM->exportValue('unix_socket') != "") {
			$params['unix_socket'] = $FORM->exportValue('unix_socket');
		}
		if ($FORM->exportValue('database') != "") {
			$params['dbname'] = $FORM->exportValue('database');
		}
	}
	$dsn = 'mysql:';
	foreach ($params as $_key => $_value) {
		$dsn .= $_key.'='.$_value.';';
	}
	if (substr($dsn, -1, 1) == ';') {
		$dsn = substr($dsn, 0, -1);
	}
	
	// prepare params array
	$params = array(
		'dsn' => $dsn,
		'username' => $FORM->exportValue('user'),
		'password' => $FORM->exportValue('password')
	);
	
	$path = dirname(__FILE__).'/../core/base_classes/pdo.class.php';
	require_once(Base_Compat::fixDirectorySeparator($path));
	
	try {
		$db = new Base_Database($params);
		$version = $db->select("SHOW VARIABLES LIKE 'version'", 'row');
	} catch (Exception $e) {
		return false;
	}
	
	$version_number = preg_replace("=(.*)([0-9]+\.[0-9]+\.[0-9]+)(.*)=", '$2', $version['Value']);
	if (version_compare($version_number, '4.1.7', '<')) {
		return false;
	} else {
		return true;
	}
}

function setup_database_innodb_test_callback ($database, &$FORM) {
	// prepare params to create connections
	$params = array();
	if ($FORM->exportValue('connection_method') == 'tcp_ip') {
		if ($FORM->exportValue('host') != "") {
			$params['host'] = $FORM->exportValue('host');
		}
		if ($FORM->exportValue('port') != "") {
			$params['port'] = $FORM->exportValue('port');
		}
		if ($FORM->exportValue('database') != "") {
			$params['dbname'] = $FORM->exportValue('database');
		}
	} elseif ($FORM->exportValue('connection_method') == 'socket') {
		if ($FORM->exportValue('unix_socket') != "") {
			$params['unix_socket'] = $FORM->exportValue('unix_socket');
		}
		if ($FORM->exportValue('database') != "") {
			$params['dbname'] = $FORM->exportValue('database');
		}
	}
	$dsn = 'mysql:';
	foreach ($params as $_key => $_value) {
		$dsn .= $_key.'='.$_value.';';
	}
	if (substr($dsn, -1, 1) == ';') {
		$dsn = substr($dsn, 0, -1);
	}
	
	// prepare params array
	$params = array(
		'dsn' => $dsn,
		'username' => $FORM->exportValue('user'),
		'password' => $FORM->exportValue('password')
	);
	
	$path = dirname(__FILE__).'/../core/base_classes/pdo.class.php';
	require_once(Base_Compat::fixDirectorySeparator($path));
	
	try {
		$db = new Base_Database($params);
		$innodb = $db->select("SHOW VARIABLES LIKE 'have_innodb'", 'row');
	} catch (Exception $e) {
		return false;
	}
	
	if (strtolower($innodb['Value']) != 'yes') {
		return false;
	} else {
		return true;
	}
}
?>