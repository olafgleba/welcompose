<?php

/**
 * Project: Welcompose
 * File: callbacks_insert_document.php
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

// define area constant
define('WCOM_CURRENT_AREA', 'ADMIN');

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

// admin_navigation
$admin_navigation_path = dirname(__FILE__).'/../../core/includes/admin_navigation.inc.php';
require(Base_Compat::fixDirectorySeparator($admin_navigation_path));

try {
	// start output buffering
	@ob_start();
	
	// load smarty
	$smarty_admin_conf = dirname(__FILE__).'/../../core/conf/smarty_admin.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_admin_conf), true);
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../../core/includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);
	
	// start Base_Session
	$SESSION = load('Base:Session');
	
	// load User_User
	$USER = load('User:User');
	
	// load User_Login
	$LOGIN = load('User:Login');
	
	// load Application_Project
	$PROJECT = load('Application:Project');
	
	// load Media_Object
	$OBJECT = load('Media:Object');
	
	// load Application_TextConverter
	$TEXTCONVERTER = load('Application:TextConverter');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Exception("Access denied");
	}

	// get object
	$object = $OBJECT->selectObject(Base_Cnc::FilterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
	
	// prepare callback args
	$args = array(
		'text' => htmlentities(Base_Cnc::ifsetor($_REQUEST['text'], null)),
		'href' => sprintf('{get_media id="%u"}', $object['id'])
	);
	
	// execute text converter callback
	$text_converter = Base_Cnc::filterRequest($_REQUEST['text_converter'], WCOM_REGEX_NUMERIC);
	print $TEXTCONVERTER->insertCallback($text_converter, 'Document', $args);
	
} catch (Exception $e) {
	// clean the buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}

	// define new error_tpl
	Base_Error::$_error_tpl = 'error_popup_723.html';
	
	// raise error
	Base_Error::triggerException($BASE->utility->smarty, $e);	

	// exit
	exit;
}

?>