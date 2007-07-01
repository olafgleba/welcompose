<?php

/**
 * Project: Welcompose
 * File: mediamanager_local.php
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
 * @copyright 2006 creatics media.systems
 * @author Olaf Gleba
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
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Exception("Access denied");
	}
	
	// get and assign timeframes
	$BASE->utility->smarty->assign('timeframes', $HELPER->getTimeframes());
	
	// import request params
	$request = array(
		'mm_include_types_doc' => Base_Cnc::filterRequest($_REQUEST['mm_include_types_doc'],
			WCOM_REGEX_ZERO_OR_ONE), 
		'mm_include_types_img' => Base_Cnc::filterRequest($_REQUEST['mm_include_types_img'],
			WCOM_REGEX_ZERO_OR_ONE), 
		'mm_include_types_audio' => Base_Cnc::filterRequest($_REQUEST['mm_include_types_audio'],
			WCOM_REGEX_ZERO_OR_ONE), 
		'mm_include_types_video' => Base_Cnc::filterRequest($_REQUEST['mm_include_types_video'],
			WCOM_REGEX_ZERO_OR_ONE), 
		'mm_include_types_other' => Base_Cnc::filterRequest($_REQUEST['mm_include_types_other'],
			WCOM_REGEX_ZERO_OR_ONE), 
		'mm_tags' => Base_Cnc::filterRequest($_REQUEST['mm_tags'], WCOM_REGEX_NON_EMPTY),
		'mm_timeframe' => Base_Cnc::filterRequest($_REQUEST['mm_timeframe'], WCOM_REGEX_TIMEFRAME),
		'mm_start' => Base_Cnc::filterRequest($_REQUEST['mm_start'], WCOM_REGEX_NUMERIC),
		'mm_limit' => Base_Cnc::filterRequest($_REQUEST['mm_limit'], WCOM_REGEX_NUMERIC),
		'mm_pagetype' => Base_Cnc::filterRequest($_REQUEST['mm_pagetype'], WCOM_REGEX_PAGE_TYPE)
	);
	
	// prepare types for select
	$types = array();
	if ($request['mm_include_types_doc'] == 1) {
		$types[] = 'document';
	}
	if ($request['mm_include_types_img'] == 1) {
		$types[] = 'image';
	}
	if ($request['mm_include_types_audio'] == 1) {
		$types[] = 'audio';
	}
	if ($request['mm_include_types_video'] == 1) {
		$types[] = 'video';
	}
	if ($request['mm_include_types_other'] == 1) {
		$types[] = 'other';
	}
	
	// prepare select params
	$select_params = array(
		'types' => $types,
		'tags' => $request['mm_tags'],
		'timeframe' => $request['mm_timeframe'],
		'order_macro' => 'DATE_ADDED:DESC',
		'start' => $request['mm_start'],
		'limit' => (($request['mm_limit'] < 1) ? 8 : $request['mm_limit'])
	);
	$BASE->utility->smarty->assign('objects', $OBJECT->selectObjects($select_params));
	
	// count objects
	$count_params = array(
		'types' => $types,
		'tags' => $request['mm_tags'],
		'timeframe' => $request['mm_timeframe']
	);
	$object_count = $OBJECT->countObjects($count_params);
	$BASE->utility->smarty->assign('object_count', $object_count);
	
	// create page index
	$BASE->utility->smarty->assign('page_index', $HELPER->calculatePageIndex($object_count,
		(($request['mm_limit'] < 1) ? 8 : $request['mm_limit'])));
	
	// assign request params
	$BASE->utility->smarty->assign('request', $request);
	
	// assign image path
	$BASE->utility->smarty->assign('media_store_www', $BASE->_conf['media']['store_www']);
	
	// set header
	header("Content-Type: text/html; charset=iso-8859-1");
	
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
	$BASE->error->displayException($e, $BASE->utility->smarty, 'error_mediamanager.html');
	$BASE->error->triggerException($e);
	
	// exit
	exit;
}
?>