<?php

/**
 * Project: Welcompose
 * File: pages_simpleguestbooks_entries_add.php
 *
 * Copyright (c) 2008-2012 creatics, Olaf Gleba <og@welcompose.de>
 *
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * @author Olaf Gleba
 * @package Welcompose
 * @link http://welcompose.de
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
	$FORM = $BASE->utility->loadQuickForm('simple_guestbook_entry');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');

	// hidden for id
	$id = $FORM->addElement('hidden', 'id', array('id' => 'id'));		
		
	// hidden for page
	$page_id = $FORM->addElement('hidden', 'page', array('id' => 'page'));
	
	// hidden for start	
	$start = $FORM->addElement('hidden', 'start', array('id' => 'start'));
	
	// hidden for timeframe
	$timeframe = $FORM->addElement('hidden', 'timeframe', array('id' => 'timeframe'));	
	
	// hidden for limit
	$limit = $FORM->addElement('hidden', 'limit', array('id' => 'limit'));
	
	// hidden for search_name
	$search_name = $FORM->addElement('hidden', 'search_name', array('id' => 'search_name'));

	// hidden for macro
	$macro = $FORM->addElement('hidden', 'macro', array('id' => 'macro'));

	// hidden for text_converter
	$text_converter = $FORM->addElement('hidden', 'text_converter', array('id' => 'text_converter'));
	
	// textfield for name
	$name = $FORM->addElement('text', 'name', 
		array('id' => 'simple_guestbook_entry_name', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Name'))
		);
	$name->addRule('required', gettext('Please enter a name'));
		
	// textfield for email	
	$email = $FORM->addElement('text', 'email', 
		array('id' => 'simple_guestbook_entry_email', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('E-mail'))
		);
	$email->addRule('regex', gettext('Please enter a valid e-mail address'), WCOM_REGEX_EMAIL);

	// textfield for subject
	$subject = $FORM->addElement('text', 'subject', 
		array('id' => 'simple_guestbook_entry_subject', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Subject'))
		);
	
	// textarea for value
	$content = $FORM->addElement('textarea', 'content', 
		array('id' => 'simple_guestbook_entry_content', 'cols' => 3, 'rows' => '2', 'class' => 'w540h150'),
		array('label' => gettext('Message'))
		);
	$content->addRule('required', gettext('Please enter your message'));
	
	// submit button (save and stay)
	$save = $FORM->addElement('submit', 'save', 
		array('class' => 'submit200', 'value' => gettext('Save edit'))
		);
		
	// submit button (save and go back)
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'submit200go', 'value' => gettext('Save edit and go back'))
		);
	
	// set defaults
	$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
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
	)));
	
	
	// validate it
	if (!$FORM->validate()) {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();
		
		// fetch {function} template to set
		// required/error markup on each form fields
		$BASE->utility->smarty->fetch(dirname(__FILE__).'/../quickform.tpl');
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $FORM->render($renderer)->toArray());
		
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
		$FORM->toggleFrozen(true);
		
		// prepare sql data
		$sqlData = array();
		$sqlData['book'] = $page_id->getValue();
		$sqlData['user'] = ((WCOM_CURRENT_USER_ANONYMOUS !== true) ? WCOM_CURRENT_USER : null);
		$sqlData['name'] = $name->getValue();
		$sqlData['email'] = $email->getValue();
		$sqlData['subject'] = $subject->getValue();
		$sqlData['content'] = $content->getValue();
		$sqlData['content_raw'] = $content->getValue();
			
		// apply text converter
		$sqlData['content'] = $TEXTCONVERTER->applyTextConverter($text_converter->getValue(),
					$content->getValue());
		
		// test sql data for pear errors
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$SIMPLEGUESTBOOKENTRY->updateSimpleGuestbookEntry($id->getValue(), $sqlData);
			
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
			
			// re-throw exception
			throw $e;
		}
		
		// controll value
		$saveAndRemainOnPage = $save->getValue();
		
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
		$start = $start->getValue();
		$limit = $limit->getValue();
		$timeframe = $timeframe->getValue();
		$macro = $macro->getValue();
		$search_name = $search_name->getValue();
		
		// append request params
		$redirect_params = (!empty($start)) ? '&start='.$start : '';
		$redirect_params .= (!empty($limit)) ? '&limit='.$limit : '&limit=20';
		$redirect_params .= (!empty($timeframe)) ? '&timeframe='.$timeframe : '';
		$redirect_params .= (!empty($macro)) ? '&macro='.$macro : '';
		$redirect_params .= (!empty($search_name)) ? '&search_name='.$search_name : '';	
		
		// redirect
		if (!empty($saveAndRemainOnPage)) {
			header("Location: pages_simpleguestbooks_entries_edit.php?page=".
						$page_id->getValue()."&id=".$id->getValue().$redirect_params);
		} else {
			header("Location: pages_simpleguestbooks_entries_select.php?page=".
						$page_id->getValue().$redirect_params);
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