<?php

/**
 * Project: Oak
 * File: index.php
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
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

// define current area constant
define('OAK_CURRENT_AREA', 'PUBLIC');

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
	
	// init project for public area
	$PROJECT = load('application:project');
	$PROJECT->initProjectPublicArea();
	
	// init user for public area
	$USER = load('user:user');
	$USER->initUserPublicArea();
	
	// get project information
	$project = $PROJECT->selectProject(OAK_CURRENT_PROJECT);
	
	// get page information
	$PAGE = load('content:page');
	$possible_page = Base_Cnc::filterRequest($_REQUEST['page'], OAK_REGEX_NUMERIC);
	if (is_null($possible_page)) {
		$page = $PAGE->selectIndexPage();
	} else {
		$page = $PAGE->selectPage($possible_page);
		if (empty($page)) {
			throw new Exception("Requested page not found");
		}
	}
	
	// define constant CURRENT_PAGE
	define('OAK_CURRENT_PAGE', $page['id']);
	
	// import url params
	$import_globals_path = dirname(__FILE__).'/import_globals.inc.php';
	require(Base_Compat::fixDirectorySeparator($import_globals_path));
	
	// assign page information to smarty
	$BASE->utility->smarty->assign('page', $page);
	
	// import action
	$action = Base_Cnc::filterRequest($_REQUEST['action'], OAK_REGEX_ALPHANUMERIC);
	$action = (!is_null($action) ? $action : 'index');
	
	// create display class name from action and page id
	switch ((string)$page['page_type_name']) {
		case 'OAK_BLOG':
				$action_class_name = ucfirst(strtolower($action));
				$display_class = "Display:Blog".$action_class_name;
				
				// prepare args
				$args = array($project, $page);
			break;
		case 'OAK_SIMPLE_PAGE':
				$action_class_name = ucfirst(strtolower($action));
				$display_class = "Display:SimplePage".$action_class_name;
				
				// get simple page
				$SIMPLEPAGE = load('content:simplepage');
				$simple_page = $SIMPLEPAGE->selectSimplePage(OAK_CURRENT_PAGE);
				
				// assign simple page to smarty
				$BASE->utility->smarty->assign('simple_page', $simple_page);
				
				// prepare args
				$args = array($project, $page, $simple_page);
			break;
		case 'OAK_SIMPLE_FORM':
				$action_class_name = ucfirst(strtolower($action));
				$display_class = "Display:SimpleForm".$action_class_name;
				
				// get simple form
				$SIMPLEFORM = load('content:simpleform');
				$simple_form = $SIMPLEFORM->selectSimpleForm(OAK_CURRENT_PAGE);
				
				// assign simple form to smarty
				$BASE->utility->smarty->assign('simple_form', $simple_form);
				
				// prepare args
				$args = array($project, $page, $simple_form);
			break;
		default:
			throw new Exception("Unknown page type requested");
	}
	
	// call the display class
	$DISPLAY = load($display_class, $args);
	
	// execute the renderer
	$DISPLAY->render();
	
	// enable/disable caching
	$BASE->utility->smarty->caching = $DISPLAY->getMainTemplateCacheMode();
	$BASE->utility->smarty->cache_lifetime = $DISPLAY->getMainTemplateCacheLifeTime();
	
	// get the tempalte name from the current display class and
	// register the OAK_TEMPLATE constant
	define("OAK_TEMPLATE", $DISPLAY->getMainTemplateName());
	
	// display page
	define("OAK_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
	$BASE->utility->smarty->display(OAK_TEMPLATE, OAK_TEMPLATE_KEY);
	
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
