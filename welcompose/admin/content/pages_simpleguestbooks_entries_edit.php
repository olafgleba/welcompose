<?php

/**
 * Project: Welcompose
 * File: pages_simpleguestbooks_entries_add.php
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
	
	// load Content_SimpleGuestbookEntry class
	$SIMPLEGUESTBOOKENTRY = load('Content:SimpleGuestbookEntry');
	
	// load helper class
	/* @var $HELPER Utility_Helper */
	$HELPER = load('utility:helper');
	
	// load Application_TextConverter class
	$TEXTCONVERTER = load('Application:TextConverter');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Content', 'SimpleGuestbookEntry', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
	// get page
	$page = $PAGE->selectPage(Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC));
	
	// get guestbook entry
	$guestbook_entry = $SIMPLEGUESTBOOKENTRY->selectSimpleGuestbookEntry(Base_Cnc::filterRequest($_REQUEST['id'],
		WCOM_REGEX_NUMERIC));
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('simple_guestbook_entry', 'post');

	// hidden for id
	$FORM->addElement('hidden', 'id');
	$FORM->applyFilter('id', 'trim');
	$FORM->applyFilter('id', 'strip_tags');
	$FORM->addRule('id', gettext('Id is not expected to be empty'), 'required');
	$FORM->addRule('id', gettext('Id is expected to be numeric'), 'numeric');
		
	// hidden for page
	$FORM->addElement('hidden', 'page');
	$FORM->applyFilter('page', 'trim');
	$FORM->applyFilter('page', 'strip_tags');
	$FORM->addRule('page', gettext('Page is not expected to be empty'), 'required');
	$FORM->addRule('page', gettext('Page is expected to be numeric'), 'numeric');
	
	// hidden for start
	$FORM->addElement('hidden', 'start');
	$FORM->applyFilter('start', 'trim');
	$FORM->applyFilter('start', 'strip_tags');
	$FORM->addRule('start', gettext('start is expected to be numeric'), 'numeric');
	
	// hidden for limit
	$FORM->addElement('hidden', 'limit');
	$FORM->applyFilter('limit', 'trim');
	$FORM->applyFilter('limit', 'strip_tags');
	$FORM->addRule('limit', gettext('limit is expected to be numeric'), 'numeric');
	
	// hidden for search_name
	$FORM->addElement('hidden', 'search_name');
	$FORM->applyFilter('search_name', 'trim');
	$FORM->applyFilter('search_name', 'strip_tags');
	
	// hidden for macro
	$FORM->addElement('hidden', 'macro');
	$FORM->applyFilter('macro', 'trim');
	
	// hidden for timeframe
	$FORM->addElement('hidden', 'timeframe');
	$FORM->applyFilter('timeframe', 'trim');
	$FORM->applyFilter('timeframe', 'strip_tags');
	$FORM->addRule('timeframe', gettext('timeframe may only contain chars and underscores'), WCOM_REGEX_TIMEFRAME);
	
	// hidden for text_converter
	$FORM->addElement('hidden', 'text_converter');
	$FORM->applyFilter('text_converter', 'trim');
	$FORM->applyFilter('text_converter', 'strip_tags');
	$FORM->addRule('text_converter', gettext('Id is not expected to be empty'), 'required');
	$FORM->addRule('text_converter', gettext('Id is expected to be numeric'), 'numeric');
	
	// textfield for name
	$FORM->addElement('text', 'name', gettext('Name'),
		array('id' => 'simple_guestbook_entry_name', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('name', 'trim');
	$FORM->applyFilter('name', 'strip_tags');
	$FORM->addRule('name', gettext('Please enter a name'), 'required');
	
	// textfield for email
	$FORM->addElement('text', 'email', gettext('E-mail'),
		array('id' => 'simple_guestbook_entry_email', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('email', 'trim');
	$FORM->applyFilter('email', 'strip_tags');
	$FORM->addRule('email', gettext('Please enter a valid e-mail address'), 'email');
	
	// textfield for subject
	$FORM->addElement('text', 'subject', gettext('Subject'),
		array('id' => 'simple_guestbook_entry_subject', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('subject', 'trim');
	$FORM->applyFilter('subject', 'strip_tags');
	
	// textarea for value
	$FORM->addElement('textarea', 'content', gettext('Message'),
		array('id' => 'simple_guestbook_entry_content', 'cols' => 3, 'rows' => '2', 'class' => 'w540h150'));
	$FORM->applyFilter('content', 'trim');
	$FORM->applyFilter('content', 'strip_tags');
	$FORM->addRule('content', gettext('Please enter your message'), 'required');
	
	// submit button (save and stay)
	$FORM->addElement('submit', 'save', gettext('Save edit'),
		array('class' => 'submit200'));
		
	// submit button (save and go back)
	$FORM->addElement('submit', 'submit', gettext('Save edit and go back'),
		array('class' => 'submit200go'));
	
	// set defaults
	$FORM->setDefaults(array(
		'id' => Base_Cnc::ifsetor($guestbook_entry['id'], null),
		'page' => Base_Cnc::ifsetor($guestbook_entry['book'], null),
		'timeframe' => Base_Cnc::filterRequest($_REQUEST['timeframe'], WCOM_REGEX_TIMEFRAME),
		'start' => Base_Cnc::filterRequest($_REQUEST['start'], WCOM_REGEX_NUMERIC),
		'limit' => Base_Cnc::filterRequest($_REQUEST['limit'], WCOM_REGEX_NUMERIC),
		'search_name' => Base_Cnc::filterRequest($_REQUEST['search_name'], WCOM_REGEX_SEARCH_NAME),
		'macro' => Base_Cnc::filterRequest($_REQUEST['macro'], WCOM_REGEX_ORDER_MACRO),
		'name' => Base_Cnc::ifsetor($guestbook_entry['name'], null),
		'email' => Base_Cnc::ifsetor($guestbook_entry['email'], null),
		'subject' => Base_Cnc::ifsetor($guestbook_entry['subject'], null),
		'content' => Base_Cnc::ifsetor($guestbook_entry['content_raw'], null),
		'text_converter' => Base_Cnc::ifsetor($guestbook_entry['text_converter'], null)
	));
	
	// validate it
	if (!$FORM->validate()) {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();
		$quickform_tpl_path = dirname(__FILE__).'/../quickform.tpl.php';
		include(Base_Compat::fixDirectorySeparator($quickform_tpl_path));
		
		// remove attribute on form tag for XHTML compliance
		$FORM->removeAttribute('name');
		$FORM->removeAttribute('target');
		
		$FORM->accept($renderer);
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $renderer->toArray());
		
		// assign paths
		$BASE->utility->smarty->assign('wcom_admin_root_www',
			$BASE->_conf['path']['wcom_admin_root_www']);
		
		// build session
		$session = array(
			'response' => Base_Cnc::filterRequest($_SESSION['response'], WCOM_REGEX_NUMERIC)
		);
		
		// assign $_SESSION to smarty
		$BASE->utility->smarty->assign('session', $session);
		
		// empty $_SESSION
		if (!empty($_SESSION['response'])) {
			$_SESSION['response'] = '';
		}
		
		// assign current user and project id
		$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);
		$BASE->utility->smarty->assign('wcom_current_project', WCOM_CURRENT_PROJECT);

		// select available projects
		$select_params = array(
			'user' => WCOM_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// assign page
		$BASE->utility->smarty->assign('page', $page);
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('content/pages_simpleguestbooks_entries_edit.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// prepare sql data
		$sqlData = array();
		$sqlData['book'] = $FORM->exportValue('page');
		$sqlData['user'] = ((WCOM_CURRENT_USER_ANONYMOUS !== true) ? WCOM_CURRENT_USER : null);
		$sqlData['name'] = $FORM->exportValue('name');
		$sqlData['email'] = $FORM->exportValue('email');
		$sqlData['subject'] = $FORM->exportValue('subject');
		$sqlData['content'] = $FORM->exportValue('content');
		$sqlData['content_raw'] = $FORM->exportValue('content');
			
		// apply text converter
		$sqlData['content'] = $TEXTCONVERTER->applyTextConverter($FORM->exportValue('text_converter'),
					$FORM->exportValue('content'));
		
		// test sql data for pear errors
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$SIMPLEGUESTBOOKENTRY->updateSimpleGuestbookEntry($FORM->exportValue('id'), $sqlData);
			
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
			
			// re-throw exception
			throw $e;
		}
		
		// controll value
		$saveAndRemainOnPage = $FORM->exportValue('save');
		
		// add response to session
		if (!empty($saveAndRemainOnPage)) {
			$_SESSION['response'] = 1;
		}
		
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// save request params 
		$start = $FORM->exportValue('start');
		$limit = $FORM->exportValue('limit');
		$timeframe = $FORM->exportValue('timeframe');
		$macro = $FORM->exportValue('macro');
		$search_name = $FORM->exportValue('search_name');
		
		// append request params
		$redirect_params = (!empty($start)) ? '&start='.$start : '';
		$redirect_params .= (!empty($limit)) ? '&limit='.$limit : '&limit=20';
		$redirect_params .= (!empty($timeframe)) ? '&timeframe='.$timeframe : '';
		$redirect_params .= (!empty($macro)) ? '&macro='.$macro : '';
		$redirect_params .= (!empty($search_name)) ? '&search_name='.$search_name : '';	
		
		// redirect
		if (!empty($saveAndRemainOnPage)) {
			header("Location: pages_simpleguestbooks_entries_edit.php?page=".
						$FORM->exportValue('page')."&id=".$FORM->exportValue('id').$redirect_params);
		} else {
			header("Location: pages_simpleguestbooks_entries_select.php?page=".
						$FORM->exportValue('page').$redirect_params);
		}
		exit;		
	}
} catch (Exception $e) {
	// clean the buffer
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