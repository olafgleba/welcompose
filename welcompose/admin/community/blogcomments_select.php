<?php

/**
 * Project: Welcompose
 * File: blogcomments_select.php
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
	/* @var $SESSION Base_Session */
	$SESSION = load('Base:Session');

	// load User_User
	/* @var $USER User_User */
	$USER = load('User:User');
	
	// load User_Login
	/* @var $LOGIN User_Login */
	$LOGIN = load('User:Login');
	
	// load Application_Project
	/* @var $PROJECT Application_Project */
	$PROJECT = load('Application:Project');
	
	// load Content_Page
	/* @var $PAGE Content_Page */
	$PAGE = load('Content:Page');
	
	// load Community_BlogComment
	/* @var $BLOGCOMMENT Community_BlogComment */
	$BLOGCOMMENT = load('Community:BlogComment');
	
	// load Community_BlogCommentStatus
	/* @var $BLOGCOMMENTSTATUS Community_BlogCommentStatus */
	$BLOGCOMMENTSTATUS = load('Community:BlogCommentStatus');
	
	// load Utility_Helper
	/* @var $HELPER Utility_Helper */
	$HELPER = load('Utility:Helper');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Community', 'BlogComment', 'Manage')) {
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
	
	// get available Blog Comment Status
	$BASE->utility->smarty->assign('blog_comment_statuses',
		$BLOGCOMMENTSTATUS->selectBlogCommentStatuses());
	
	// get available timeframes
	$BASE->utility->smarty->assign('timeframes', $HELPER->getTimeframes());
	
	// get pages
	$BASE->utility->smarty->assign('pages', $PAGE->selectPages());
	
	// get available comments
	$select_params = array(
		'page' => Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC),
		'posting' => Base_Cnc::filterRequest($_REQUEST['posting'], WCOM_REGEX_NUMERIC),
		'status' => Base_Cnc::filterRequest($_REQUEST['status'], WCOM_REGEX_NUMERIC),
		'timeframe' => Base_Cnc::filterRequest($_REQUEST['timeframe'], WCOM_REGEX_TIMEFRAME),
		'start' => Base_Cnc::filterRequest($_REQUEST['start'], WCOM_REGEX_NUMERIC),
		'order_macro' => 'DATE_ADDED:DESC',
		'limit' => 20
	);
	$BASE->utility->smarty->assign('blog_comments',
		$BLOGCOMMENT->selectBlogComments($select_params));
	
	// get total comment count
	$total_comment_count = $BLOGCOMMENT->countBlogComments();
	$BASE->utility->smarty->assign('total_blog_comment_count', $total_comment_count);
	
	// count available blog comments
	$select_params = array(
		'page' => Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC),
		'posting' => Base_Cnc::filterRequest($_REQUEST['posting'], WCOM_REGEX_NUMERIC),
		'status' => Base_Cnc::filterRequest($_REQUEST['status'], WCOM_REGEX_NUMERIC),
		'timeframe' => Base_Cnc::filterRequest($_REQUEST['timeframe'], WCOM_REGEX_TIMEFRAME)
	);
	$comment_count = $BLOGCOMMENT->countBlogComments($select_params);
	$BASE->utility->smarty->assign('blog_comment_count', $comment_count);
	
	// prepare and assign page index
	$BASE->utility->smarty->assign('page_index', $HELPER->calculatePageIndex($comment_count, 20));
	
	// import and assign request params
	$request = array(
		'page' => Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC),
		'posting' => Base_Cnc::filterRequest($_REQUEST['posting'], WCOM_REGEX_NUMERIC),
		'status' => Base_Cnc::filterRequest($_REQUEST['status'], WCOM_REGEX_TIMEFRAME),
		'timeframe' => Base_Cnc::filterRequest($_REQUEST['timeframe'], WCOM_REGEX_TIMEFRAME),
		'start' => Base_Cnc::filterRequest($_REQUEST['start'], WCOM_REGEX_NUMERIC)
	);
	$BASE->utility->smarty->assign('request', $request);
	
	// display the template
	define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
	$BASE->utility->smarty->display('community/blogcomments_select.html', WCOM_TEMPLATE_KEY);
	
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