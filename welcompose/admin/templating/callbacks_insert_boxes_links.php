<?php

/**
 * Project: Welcompose
 * File: callbacks_insert_boxes_links.php
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
 * $Id: pages_links_select.php 720 2006-12-09 09:52:38Z olaf $
 *
 * @copyright 2008 creatics media.systems, Olaf Gleba
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
	$PAGE = load('Content:Page');
	
	// load box class
	/* @var $BOX Content_Box */
	$BOX = load('Content:Box');
	
	// load helper class
	/* @var $HELPER Utility_Helper */
	$HELPER = load('utility:helper');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Content', 'Box', 'Use')) {
		throw new Exception("Access denied");
	}
	
	// assign paths
	$BASE->utility->smarty->assign('wcom_admin_root_www',
		$BASE->_conf['path']['wcom_admin_root_www']);
	
	// assign current user and project id
	$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('wcom_current_project', WCOM_CURRENT_PROJECT);
	
	// collect callback parameters
	$callback_params = array(
		'form_target' => Base_Cnc::filterRequest($_REQUEST['form_target'], WCOM_REGEX_CALLBACK_STRING),
		'delimiter' => Base_Cnc::filterRequest($_REQUEST['delimiter'], WCOM_REGEX_NUMERIC),
		'text' => Base_Cnc::ifsetor(utf8_decode($_REQUEST['text']), null),
		'text_converter' => Base_Cnc::filterRequest($_REQUEST['text_converter'], WCOM_REGEX_NUMERIC),
		'pager_page' => Base_Cnc::filterRequest($_REQUEST['pager_page'], WCOM_REGEX_NUMERIC),
		'insert_type' => Base_Cnc::filterRequest($_REQUEST['insert_type'], WCOM_REGEX_CALLBACK_STRING)
	);
		
	// assign callbacks params
	$BASE->utility->smarty->assign('callback_params', $callback_params);
	
	// prepare template key
	define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
	
	// display template depending on the selection level
	if (empty($_REQUEST['nextNode'])) {

		// get boxes
		$boxes = $BOX->selectBoxes();
		
		// get related pages
		$pages_arrays = array();
		foreach ($boxes as $_box) {
			$pages_arrays[$_box['page']] = $PAGE->selectPage((int)$_box['page']);
		}
		$BASE->utility->smarty->assign('pages_arrays', $pages_arrays);
				
		// display the page
		$BASE->utility->smarty->display('templating/callbacks_insert_boxes_links.html', WCOM_TEMPLATE_KEY);
		
	} elseif (!empty($_REQUEST['nextNode']) && $_REQUEST['nextNode'] == 'secondNode') {

		$page_id = Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC);
		
		// get all related boxes
		$select_params = array(
			'page' => $page_id
		);
		$boxes = $BOX->selectBoxes($select_params);
		$BASE->utility->smarty->assign('boxes', $boxes);
		
		// assign page id
		$BASE->utility->smarty->assign('page_id', $page_id);
		
		// display the page
		$BASE->utility->smarty->display('templating/callbacks_insert_boxes_links_second.html', WCOM_TEMPLATE_KEY);	
	}
	
	// flush the buffer
	@ob_end_flush();
	exit;

} catch (Exception $e) {
	// clean buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	$BASE->error->displayException($e, $BASE->utility->smarty, 'error_popup_723.html');
	$BASE->error->triggerException($e);
	
	// exit
	exit;
}
?>