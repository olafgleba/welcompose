<?php

/**
 * Project: Welcompose
 * File: database.php
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
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @link http://welcompose.de
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
	if (empty($_SESSION['setup']['license_confirm_license']) || !$_SESSION['setup']['license_confirm_license']) {
		header("Location: license.php");
		exit;
	}
	
	// prepare array with connection methods
	$connection_methods = array(
		'tcp_ip' => gettext('TCP/IP (Network)'),
		'socket' => gettext('Unix socket')
	);
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('database');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');
	
	// textfield for database
	$database = $FORM->addElement('text', 'database', 
		array('id' => 'database_database', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('Database'))
		);
	$database->addRule('required', gettext('Please enter a database name'));
	$database->addRule('regex', gettext('Please enter a valid database name'), WCOM_REGEX_DATABASE_NAME);	
	
	// textfield for user
	$user = $FORM->addElement('text', 'user', 
		array('id' => 'database_user', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('User'))
		);
	$user->addRule('required', gettext('Please enter a user name'));
	
	// textfield for password
	$password = $FORM->addElement('password', 'password', 
		array('id' => 'database_password', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Password'))
		);

	// textfield for connection method
	$connection_method = $FORM->addElement('select', 'connection_method',
	 	array('id' => 'database_connection_method'),
		array('label' => gettext('Connection Method'), 'options' => $connection_methods)
		);
	$connection_method->addRule('required', gettext('Please select a connection method'));	
	
	// textfield for host
	$host = $FORM->addElement('text', 'host', 
		array('id' => 'database_host', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Host'))
		);
	
	// textfield for port	
	$port = $FORM->addElement('text', 'port', 
		array('id' => 'database_port', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('Port'))
		);
	
	// textfield for unix socket
	$unix_socket = $FORM->addElement('text', 'unix_socket', 
		array('id' => 'database_unix_socket', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('Unix socket'))
		);
	$unix_socket->addRule('regex', gettext('Please enter a valid socket address'), WCOM_REGEX_DATABASE_SOCKET);	
		
	if ($connection_method->getValue() == 'tcp_ip') {
		$host->addRule('required', gettext('Please enter a host name'));
		$port->addRule('required', gettext('Please enter a port for your database connection'));
		$port->addRule('regex', gettext('The input must be numeric'), WCOM_REGEX_NUMERIC);
	}
	
	// add connection validation rule
	$database->addRule('callback', gettext('Unable to connect to database server'), 
		array(
			'callback' => 'setup_database_connection_test_callback',
			'arguments' => array($user->getValue(), $password->getValue(), $connection_method->getValue(), $host->getValue(), $port->getValue(), $unix_socket->getValue())
		)
	);
	$database->addRule('callback', gettext('Selected database server is too old'), 
		array(
			'callback' => 'setup_database_version_test_callback',
			'arguments' => array($user->getValue(), $password->getValue(), $connection_method->getValue(), $host->getValue(), $port->getValue(), $unix_socket->getValue())
		)
	);
	$database->addRule('callback', gettext('Selected database does not support InnoDB'), 
		array(
			'callback' => 'setup_database_innodb_test_callback',
			'arguments' => array($user->getValue(), $password->getValue(), $connection_method->getValue(), $host->getValue(), $port->getValue(), $unix_socket->getValue())
		)
	);
		
	// submit button
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'submit240', 'value' => gettext('Next step'))
		);
		
	// set defaults
	$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
		'connection_method' => 'tcp_ip'
	)));
		
	// validate it
	if (!$FORM->validate()) {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();

		// fetch {function} template to set
		// required/error markup on each form fields
		$BASE->utility->smarty->fetch(dirname(__FILE__).'/quickform.tpl');
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $FORM->render($renderer)->toArray());
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('database.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->toggleFrozen(true);
		
		// save all the database settings to the session
		$_SESSION['setup']['database_database'] = $database->getValue();
		$_SESSION['setup']['database_user'] = $user->getValue();
		$_SESSION['setup']['database_password'] = $password->getValue();
		$_SESSION['setup']['database_connection_method'] = $connection_method->getValue();
		$_SESSION['setup']['database_host'] = $host->getValue();
		$_SESSION['setup']['database_port'] = $port->getValue();
		$_SESSION['setup']['database_unix_socket'] = $unix_socket->getValue();
		
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

function setup_database_connection_test_callback ($database, $user, $password, $connection_method, $host, $port, $unix_socket) {
	// prepare params to create connections
	if ($connection_method == 'tcp_ip') {
		$dsn = sprintf("mysql:host=%s;port=%u;dbname=%s", $host,
			$port, $database);
	} elseif ($connection_method == 'socket') {
		$dsn = sprintf("mysql:unix_socket=%s;dbname=%s", $unix_socket,
			$database);
	}
	
	// prepare params array
	$params = array(
		'dsn' => $dsn,
		'username' => $user,
		'password' => $password
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

function setup_database_version_test_callback ($database, $user, $password, $connection_method, $host, $port, $unix_socket) {
	// prepare params to create connections
	$params = array();
	if ($connection_method == 'tcp_ip') {
		if ($host != "") {
			$params['host'] = $host;
		}
		if ($port != "") {
			$params['port'] = $port;
		}
		if ($database != "") {
			$params['dbname'] = $database;
		}
	} elseif ($connection_method == 'socket') {
		if ($unix_socket != "") {
			$params['unix_socket'] = $unix_socket;
		}
		if ($database != "") {
			$params['dbname'] = $database;
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
		'username' => $user,
		'password' => $password
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
 
function setup_database_innodb_test_callback ($database, $user, $password, $connection_method, $host, $port, $unix_socket) {
	// prepare params to create connections
	$params = array();
	if ($connection_method == 'tcp_ip') {
		if ($host != "") {
			$params['host'] = $host;
		}
		if ($port != "") {
			$params['port'] = $port;
		}
		if ($database != "") {
			$params['dbname'] = $database;
		}
	} elseif ($connection_method == 'socket') {
		if ($unix_socket != "") {
			$params['unix_socket'] = $unix_socket;
		}
		if ($database != "") {
			$params['dbname'] = $database;
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
		'username' => $user,
		'password' => $password
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