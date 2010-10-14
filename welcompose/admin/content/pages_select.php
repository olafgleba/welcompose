<?php

/**
 * Project: Welcompose
 * File: pages_select.php
 *
 * Copyright (c) 2008 creatics
 *
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 *
 * $Id$
 *
 * @copyright 2008 creatics, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
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
	
	// start session
	/* @var $SESSION session */
	$SESSION = load('base:session');

	// load user class
	/* @var $USER User_User */
	$USER = load('user:user');
	
	// load login class
	/* @var $LOGIN User_Login */
	$LOGIN = load('User:Login');
	
	// load project class
	/* @var $PROJECT Application_Project */
	$PROJECT = load('application:project');
	
	// load page class
	/* @var $PAGE Content_Page */
	$PAGE = load('content:page');
	
	// load box class
	/* @var $BOX Content_Box */
	$BOX = load('content:box');

	// load navigation class
	/* @var $NAVIGATION Content_Navigation */
	$NAVIGATION = load('content:navigation');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Content', 'Page', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
	// assign paths
	$BASE->utility->smarty->assign('wcom_admin_root_www',
		$BASE->_conf['path']['wcom_admin_root_www']);
	
	// assign current user and project id
	$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('wcom_current_project', WCOM_CURRENT_PROJECT);

	// select available projects
	$select_params = array(
		'user' => WCOM_CURRENT_USER,
		'order_macro' => 'NAME'
	);
	$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
	
	// select available navigations
	$navigations = $NAVIGATION->selectNavigations();
	$BASE->utility->smarty->assign('navigations', $navigations);
	
	// let's see how many index pages there are
	$index_page_count = $PAGE->countPages(array('index_page' => 1));
	$BASE->utility->smarty->assign('index_page_count', $index_page_count);
	
	// get pages
	$page_arrays = array();
	foreach ($navigations as $_navigation) {
		$select_params = array(
			'navigation' => (int)$_navigation['id'],
			'draft' => 1
		);
		$page_arrays[$_navigation['id']] = $PAGE->selectPages($select_params);
	}
	$BASE->utility->smarty->assign('page_arrays', $page_arrays);
	
	// get page boxes
	$box_arrays = array();
	foreach ($page_arrays as $_outer_page) {
		foreach ($_outer_page as $_page) {
			$select_box_params = array(
				'page' => (int)$_page['id']
			);
			$box_arrays[$_page['id']] = $BOX->selectBoxes($select_box_params);
		}
	}
	$BASE->utility->smarty->assign('box_arrays', $box_arrays);
	
	// do we have to display warning because of existing install files?
	$package_file = Base_Compat::fixDirectorySeparator(dirname(__FILE__).'/../../installer.php');
	$setup_dir = Base_Compat::fixDirectorySeparator(dirname(__FILE__).'/../../setup');
	$BASE->utility->smarty->assign('package_warning', file_exists($package_file));
	$BASE->utility->smarty->assign('setup_warning', file_exists($setup_dir));
	
	// display the page
	define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
	$BASE->utility->smarty->display('content/pages_select.html', WCOM_TEMPLATE_KEY);
		
	// flush the buffer
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