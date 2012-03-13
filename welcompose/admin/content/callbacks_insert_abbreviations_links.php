<?php

/**
 * Project: Welcompose
 * File: callbacks_insert_abbreviations_links.php
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
	
	// load Application_TextConverter class
	/* @var $TEXTCONVERTER Application_Textconverter */
	$TEXTCONVERTER = load('Application:TextConverter');
	
	// load Application_TextMacro class
	/* @var $TEXTMACRO Application_TextMacro */
	$TEXTMACRO = load('Application:TextMacro');
	
	// load structural template class
	$ABBREVIATION = load('Content:Abbreviation');
	
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
	if (!wcom_check_access('Content', 'Abbreviation', 'Use')) {
		throw new Exception("Access denied");
	}
	
	// get enviroment value
	if (!empty($_REQUEST['form_target'])) {
		$form_target = Base_Cnc::filterRequest($_REQUEST['form_target'], WCOM_REGEX_CALLBACK_STRING);
	} else {
		$form_target = Base_Cnc::filterRequest($_SESSION['form_target'], WCOM_REGEX_CALLBACK_STRING);
	}	
	if (!empty($_REQUEST['text_converter'])) {
		$text_converter = Base_Cnc::filterRequest($_REQUEST['text_converter'], WCOM_REGEX_NUMERIC);
	} else {
		$text_converter = Base_Cnc::filterRequest($_SESSION['text_converter'], WCOM_REGEX_NUMERIC);
	}
	if (!empty($_REQUEST['insert_type'])) {
		$insert_type = Base_Cnc::filterRequest($_REQUEST['insert_type'], WCOM_REGEX_CALLBACK_STRING);
	} else {
		$insert_type = Base_Cnc::filterRequest($_SESSION['insert_type'], WCOM_REGEX_CALLBACK_STRING);
	}	
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('abbreviation');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');
	
	// hidden field for form_target
	$_form_target = $FORM->addElement('hidden', 'form_target', array('id' => 'form_target'));
	$_text_converter = $FORM->addElement('hidden', 'text_converter', array('id' => 'text_converter'));
	$_insert_type = $FORM->addElement('hidden', 'insert_type', array('id' => 'insert_type'));
	
	// textfield for name
	$name = $FORM->addElement('text', 'name', 
		array('id' => 'abbreviation_name', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Name'))
		);
	$name->addRule('required', gettext('Please enter a name'));
		
	// textarea for long form
	$long_form = $FORM->addElement('textarea', 'long_form', 
		array('id' => 'abbreviation_long_form', 'cols' => 3, 'rows' => '2', 'class' => 'w540h50'),
		array('label' => gettext('Long form'))
		);
	$long_form->addRule('required', gettext('Please enter a long form for the abbreviation'));
	$long_form->addRule('callback', gettext('A abbreviation with the given long form already exists'), 
		array(
			'callback' => array($ABBREVIATION, 'testForUniqueLongForm')
		)
	);
			
	// textarea for glossary form
	$content = $FORM->addElement('textarea', 'content', 
		array('id' => 'abbreviation_content', 'cols' => 3, 'rows' => '2', 'class' => 'w540h150'),
		array('label' => gettext('Glossary form'))
		);
	
	// textfield for language
	$lang = $FORM->addElement('text', 'lang', 
		array('id' => 'abbreviation_lang', 'maxlength' => 2, 'class' => 'w300'),
		array('label' => gettext('Language'))
		);
	
	// submit button
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'submit200', 'value' => gettext('Save'))
		);
		
	// set defaults
	$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
		'form_target' => Base_Cnc::ifsetor($form_target, null),
		'text_converter' => Base_Cnc::ifsetor($text_converter, null),
		'insert_type' => Base_Cnc::ifsetor($insert_type, null)
	)));

	// validate it
	if (!$FORM->validate()) {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $FORM->render($renderer)->toArray());
		
		// assign paths
		$BASE->utility->smarty->assign('wcom_admin_root_www',
			$BASE->_conf['path']['wcom_admin_root_www']);
			
		// assign delivered pager location
		$BASE->utility->smarty->assign('form_target', $form_target);
		
		// build session
		$session = array(
			'response' => Base_Cnc::filterRequest($_SESSION['response'], WCOM_REGEX_NUMERIC)
		);
		
		// assign prepared session array to smarty
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
				
		// collect callback parameters
		$callback_params = array(
			'form_target' => $form_target,
			'text_converter' => $text_converter,
			'insert_type' => $insert_type
		);

		// assign callbacks params
		$BASE->utility->smarty->assign('callback_params', $callback_params);

		// get structural templates
		$abbreviations = $ABBREVIATION->selectAbbreviations(array('order_macro' => 'NAME'));
		$BASE->utility->smarty->assign('abbreviations', $abbreviations);
				
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('content/callbacks_insert_abbreviations_links.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->toggleFrozen(true);
		
		// create the abbreviation
		$sqlData = array();
		$sqlData['name'] = $name->getValue();
		$sqlData['first_char'] = strtoupper(substr($name->getValue(),0,1));
		$sqlData['long_form'] = $long_form->getValue();
		$sqlData['content_raw'] = $content->getValue();
		$sqlData['content'] = $content->getValue();
		$sqlData['lang'] = $lang->getValue();
		$sqlData['text_converter'] = ($text_converter > 0) ? $text_converter : null;
		$sqlData['apply_macros'] = (string)intval(1);
		$sqlData['date_added'] = date('Y-m-d H:i:s');
		
		// check sql data for pear errors
		$HELPER = load('utility:helper');
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$ABBREVIATION->addAbbreviation($sqlData);
			
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
			
			// re-throw exception
			throw $e;
		}
	
		// add response to session
		$_SESSION['response'] = 1;
		
		// add enviroment vars to session
		$_SESSION['form_target'] = $_form_target->getValue();
		$_SESSION['text_converter'] = $_text_converter->getValue();
		$_SESSION['insert_type'] = $_insert_type->getValue();
	
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: callbacks_insert_abbreviations_links.php");
	}

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