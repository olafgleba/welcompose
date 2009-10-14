<?php

/**
 * Project: Welcompose
 * File: callbacks_insert_blog_tags.php
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
 * $Id$
 *
 * @copyright 2009 creatics media.systems, Olaf Gleba
 * @author Olaf Gleba
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
	
	// load blogtag class
	/* @var $BLOGTAG Content_Blogtag */
	$BLOGTAG = load('content:blogtag');
	
	// load navigation class
	/* @var $NAVIGATION Content_Navigation */
	$NAVIGATION = load('Content:Navigation');
	
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
	if (!wcom_check_access('Content', 'BlogPosting', 'Use')) {
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
	
	/**
	* Two additional request params are available, if this callback
	* is invoked from a blog posting edit page. Both params has no
	* meaning for the callback params array above.
	* 
	* 'dId => posting id
	* 'dPage' => page id
	**/
	
	// assign only tags which are not already used for a particular page
	if (!empty($_REQUEST['dId'])) {

		// get all blog tags
		$blog_tags_all = $BLOGTAG->selectBlogTags();
			
		// get blog tags of the current posting
		$blog_tags_page = $BLOGTAG->selectBlogTags(array('page' => Base_Cnc::filterRequest($_REQUEST['dPage'], WCOM_REGEX_NUMERIC),'posting' => Base_Cnc::filterRequest($_REQUEST['dId'], WCOM_REGEX_NUMERIC)));

		// reduce arrays to get a useable result within array_diff() function
		foreach ($blog_tags_all as $_key => $_field) {
			$_blog_tags_all[$_field['id']] = $_field['word'];
		}	
		foreach ($blog_tags_page as $_key => $_field) {
			$_blog_tags_page[$_field['id']] = $_field['word'];
		}
		
		// compare both arrays and save the different pairs  
		$diff = array_diff($_blog_tags_all, $_blog_tags_page);
				
		// build the new tag array
		foreach ($diff as $_key => $_field) {
			$blog_tags[] = array(
				'id' => $_key,
				'word' => $_field
			);
		}
	} else {
		$blog_tags = $BLOGTAG->selectBlogTags();
	}
	
	// assign blog tags to smarty
	$BASE->utility->smarty->assign('blog_tags', $blog_tags);
	
	// display the page
	$BASE->utility->smarty->display('content/callbacks_insert_blog_tags.html');
	
	// flush the buffer
	@ob_end_flush();
	exit;

} catch (Exception $e) {
	// clean buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	$BASE->error->displayException($e, $BASE->utility->smarty, 'error_popup_403.html');
	$BASE->error->triggerException($e);
	
	// exit
	exit;
}
?>