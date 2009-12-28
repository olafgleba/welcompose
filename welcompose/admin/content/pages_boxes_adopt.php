<?php

/**
 * Project: Welcompose
 * File: pages_boxes_adopt.php
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
	
	// load page class
	/* @var $PAGE Content_Page */
	$PAGE = load('content:page');
	
	// load textconverter class
	/* @var $TEXTCONVERTER Application_Textconverter */
	$TEXTCONVERTER = load('application:textconverter');
	
	// load textmacro class
	/* @var $TEXTMACRO Application_Textmacro */
	$TEXTMACRO = load('application:textmacro');
	
	// load box class
	/* @var $BOX Content_Box */
	$BOX = load('content:box');
	
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
	if (!wcom_check_access('Content', 'Box', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);

	// assign paths
	$BASE->utility->smarty->assign('wcom_admin_root_www',
		$BASE->_conf['path']['wcom_admin_root_www']);
				
	// define page
	$page = Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC);
	
	// get box
	$box = $BOX->selectBox(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
	
	// get all boxes related to provided page id
	$boxes = $BOX->selectBoxes(array(
		'page' => Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC),
		'start' => Base_Cnc::filterRequest($_REQUEST['start'], WCOM_REGEX_NUMERIC),
		'limit' => 20
	));

	// prepare sql data
	$sqlData = array();
	$sqlData['page'] = $page;
	
	// differ box names to avoid duplicates
	$_box = $box['name'];	
	foreach ($boxes as $_boxes) {
		if (in_array($box['name'], $_boxes)) {		
			$_box = $box['name'].'_copy_of_'.strtolower($_REQUEST['page_name']);
		}
	}
		
	$sqlData['name'] = $_box;
	$sqlData['content_raw'] = $box['content_raw'];
	$sqlData['content'] = $box['content'];
	$sqlData['text_converter'] = ($box['text_converter'] > 0) ? 
		$box['text_converter'] : null;
	$sqlData['apply_macros'] = (string)intval($box['apply_macros']);
	
	// apply text macros and text converter if required
	if ($box['text_converter'] > 0 || $box['apply_macros'] > 0) {
		// extract content
		$content = $box['content'];
	
		// apply startup and pre text converter text macros 
		if ($box['apply_macros'] > 0) {
			$content = $TEXTMACRO->applyTextMacros($content, 'pre');
		}
	
		// apply text converter
		if ($box['text_converter'] > 0) {
			$content = $TEXTCONVERTER->applyTextConverter(
				$box['text_converter'],
				$content
			);
		}
	
		// apply post text converter and shutdown text macros 
		if ($box['apply_macros'] > 0) {
			$content = $TEXTMACRO->applyTextMacros($content, 'post');
		}
	
		// assign content to sql data array
		$sqlData['content'] = $content;
	}
	
	// test sql data for pear errors
	$HELPER->testSqlDataForPearErrors($sqlData);
			
	//insert it
	try {
		// begin transaction
		$BASE->db->begin();
		
		// save returned insert_id and execute operation
		$_insert_id = $BOX->addBox($sqlData);
	
		// commit transaction
		$BASE->db->commit();
	} catch (Exception $e) {
		// do rollback
		$BASE->db->rollback();
	
		// re-throw exception
		throw $e;
	}
	
	// clean buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
		
	// print response 
	print "<tr>\n";
	print "<td>$_box</td>\n";
	print "<td><a class=\"edit\" href=\"pages_boxes_edit.php?page=$page&amp;id=$_insert_id\" title=\"Bearbeiten\"></a></td>\n";
	print "<td><a class=\"delete\" href=\"pages_boxes_delete.php?page=$page&amp;id=$_insert_id\" title=\"loeschen\"></a></td>\n";
	print "</tr>\n";
		

	// flush the buffer
	@ob_end_flush();
	exit;

} catch (Exception $e) {
	// clean buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error, print inline
	print '<div id="error">';
	print '<h1>'.gettext('An error occured').'</h1>';
	print '<h2>'.gettext('Welcompose says').':</h2>';
	print '<p>';
	$BASE->error->printExceptionMessage($e);
	print '</p>';
	print '</div>';
	
	$BASE->error->triggerException($e);
	
	// exit
	exit;
}
?>