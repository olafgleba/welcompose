<?php

/**
 * Project: Oak
 * File: mediamanager.php
 *
 * Copyright (c) 2006 sopic GmbH
 *
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License
 * http://www.opensource.org/licenses/osl-2.1.php
 *
 * $Id: parse.navigation.php 291 2006-07-31 19:46:13Z andreas $
 *
 * @copyright 2006 creatics media.systems
 * @author Olaf Gleba
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

// define area constant
define('OAK_CURRENT_AREA', 'ADMIN');

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
	$smarty_admin_conf = dirname(__FILE__).'/../../core/conf/smarty_admin.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_admin_conf), true);

	// load gettext
	$gettext_path = dirname(__FILE__).'/../../core/includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);

	// load Base_Session
	$SESSION = load('Base:Session');

	// load User_User
	$USER = load('User:User');

	// load User_Login
	/* @var $LOGIN User_Login */
	$LOGIN = load('User:Login');

	// load Application_Project
	$PROJECT = load('Application:Project');
	
	// load Media_Object
	$OBJECT = load('Media:Object');
	
	// load Utility_Helper
	$HELPER = load('Utility:Helper');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(OAK_CURRENT_USER);
	
	// get page name
	$page = Base_Cnc::filterRequest($_REQUEST['page'], OAK_REGEX_ALPHANUMERIC);
	
	// get and assign timeframes
	$BASE->utility->smarty->assign('timeframes', $HELPER->getTimeframes());
	
	// prepare select params
	$select_params = array(
		'types' => array(
			'document' => Base_Cnc::filterRequest($_REQUEST['mm_include_types_doc'], OAK_REGEX_ZERO_OR_ONE),
			'image' => Base_Cnc::filterRequest($_REQUEST['mm_include_types_img'], OAK_REGEX_ZERO_OR_ONE),
			'audio' => Base_Cnc::filterRequest($_REQUEST['mm_include_types_audio'], OAK_REGEX_ZERO_OR_ONE),
			'video' => Base_Cnc::filterRequest($_REQUEST['mm_include_types_video'], OAK_REGEX_ZERO_OR_ONE),
			'other' => Base_Cnc::filterRequest($_REQUEST['mm_include_types_other'], OAK_REGEX_ZERO_OR_ONE)
		),
		'tags' => Base_Cnc::filterRequest($_REQUEST['mm_tags'], OAK_REGEX_NON_EMPTY),
		'timeframe' => Base_Cnc::filterRequest($_REQUEST['mm_timeframe'], OAK_REGEX_TIMEFRAME),
		'order_macro' => 'DATE_ADDED:DESC',
		'start' => Base_Cnc::filterRequest($_REQUEST['mm_start'], OAK_REGEX_NUMERIC),
		//'limit' => Base_Cnc::filterRequest($_REQUEST['mm_limit'], OAK_REGEX_NUMERIC)
		'limit' => 100
	);
	$BASE->utility->smarty->assign('objects', $OBJECT->selectObjects($select_params));
	
	// assign request params
	$request = array(
		'types' => array(
			'document' => Base_Cnc::filterRequest($_REQUEST['mm_include_types_doc'], OAK_REGEX_ZERO_OR_ONE),
			'image' => Base_Cnc::filterRequest($_REQUEST['mm_include_types_img'], OAK_REGEX_ZERO_OR_ONE),
			'audio' => Base_Cnc::filterRequest($_REQUEST['mm_include_types_audio'], OAK_REGEX_ZERO_OR_ONE),
			'video' => Base_Cnc::filterRequest($_REQUEST['mm_include_types_video'], OAK_REGEX_ZERO_OR_ONE),
			'other' => Base_Cnc::filterRequest($_REQUEST['mm_include_types_other'], OAK_REGEX_ZERO_OR_ONE)
		),
		'tags' => Base_Cnc::filterRequest($_REQUEST['mm_tags'], OAK_REGEX_NON_EMPTY),
		'timeframe' => Base_Cnc::filterRequest($_REQUEST['mm_timeframe'], OAK_REGEX_TIMEFRAME),
		'start' => Base_Cnc::filterRequest($_REQUEST['mm_start'], OAK_REGEX_NUMERIC),
		'limit' => Base_Cnc::filterRequest($_REQUEST['mm_limit'], OAK_REGEX_NUMERIC)
	);
	
	$BASE->utility->smarty->assign('request', $request);
	
	// assign image path
	$BASE->utility->smarty->assign('image_store_www', $BASE->_conf['image']['store_www']);
	
	// display the correlated mediamanager template
	$BASE->utility->smarty->display('mediamanager/mediamanager.html');
		
	// flush the buffer
	@ob_end_flush();
	exit;

} catch (Exception $e) {
	// clean buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	Base_Error::triggerException($BASE->utility->smarty, $e);	
	
	// exit
	exit;
}
?>