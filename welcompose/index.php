<?php

/**
 * Project: Welcompose
 * File: index.php
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

// define current area constant
define('WCOM_CURRENT_AREA', 'PUBLIC');

// get loader
$path_parts = array(
	dirname(__FILE__),
	'core',
	'loader.php'
);
$loader_path = implode(DIRECTORY_SEPARATOR, $path_parts);
require($loader_path);

// start base
/* @var $BASE base */
$BASE = load('base:base');

// deregister globals
$deregister_globals_path = dirname(__FILE__).'/core/includes/deregister_globals.inc.php';
require(Base_Compat::fixDirectorySeparator($deregister_globals_path));

try {
	// start output buffering
	@ob_start();
	
	// load smarty
	$smarty_public_conf = dirname(__FILE__).'/core/conf/smarty_public.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_public_conf), true);
	
	// start session
	/* @var $SESSION session */
	$SESSION = load('base:session');
	
	// init project for public area
	$PROJECT = load('application:project');
	$PROJECT->initProjectPublicArea();
	
	// init user for public area
	$USER = load('user:user');
	$USER->initUserPublicArea();
	
	// get project information
	$project = $PROJECT->selectProject(WCOM_CURRENT_PROJECT);
	
	// get page information
	$PAGE = load('content:page');
	$page = $PAGE->selectPage($PAGE->resolvePage());
	
	// define constant CURRENT_PAGE
	define('WCOM_CURRENT_PAGE', $page['id']);
	
	// import url params
	$import_globals_path = dirname(__FILE__).'/import_globals.inc.php';
	require(Base_Compat::fixDirectorySeparator($import_globals_path));
	
	// assign page information to smarty
	$BASE->utility->smarty->assign('page', $page);
	
	// import action
	$action = Base_Cnc::filterRequest($_REQUEST['action'], WCOM_REGEX_ALPHANUMERIC);
	$action = (!is_null($action) ? $action : 'Index');
	
	// authenticate user if required
	if (!$PAGE->checkAccess($page['id'], $page['protect'])) {
		$action = 'Login';
	}
	
	// call the display class
	$display_class = "Display:".$page['page_type_internal_name'].$action;
	$DISPLAY = load($display_class, array($project, $page));
	
	// execute the renderer
	$DISPLAY->render();
	
	// assign action, script name
	$BASE->utility->smarty->assign('action', $action);
	$BASE->utility->smarty->assign('SCRIPT_NAME', $DISPLAY->getLocationSelf());
	
	// enable/disable caching
	$BASE->utility->smarty->caching = $DISPLAY->getMainTemplateCacheMode();
	$BASE->utility->smarty->cache_lifetime = $DISPLAY->getMainTemplateCacheLifeTime();
	
	// get the tempalte name from the current display class and
	// register the WCOM_TEMPLATE constant
	define("WCOM_TEMPLATE", $DISPLAY->getMainTemplateName());
	
	// display page
	define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
	$BASE->utility->smarty->display(WCOM_TEMPLATE, WCOM_TEMPLATE_KEY);
	
	@ob_end_flush();
	exit;
} catch (Exception $e) {
	// clean buffer
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
