<?php

/**
 * Project: Welcompose
 * File: abbreviations_edit.php
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
 * author Olaf Gleba
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
	
	// start Base_Session
	/* @var $SESSION session */
	$SESSION = load('base:session');

	// load User_User class
	/* @var $USER User_User */
	$USER = load('User:User');
	
	// load User_Login class
	/* @var $LOGIN User_Login */
	$LOGIN = load('User:Login');
	
	// load Application_Project class
	/* @var $PROJECT Application_Project */
	$PROJECT = load('Application:Project');
	
	// load Content_Abbreviation class
	/* @var $ABBREVIATION Content_Abbreviation */
	$ABBREVIATION = load('Content:Abbreviation');
	
	// load Application_TextConverter class
	/* @var $TEXTCONVERTER Application_Textconverter */
	$TEXTCONVERTER = load('Application:TextConverter');
	
	// load Application_TextMacro class
	/* @var $TEXTMACRO Application_TextMacro */
	$TEXTMACRO = load('Application:TextMacro');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Content', 'Abbreviation', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
	// get abbreviation
	$abbreviation = $ABBREVIATION->selectAbbreviation(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('abbreviation');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');
	
	// hidden for id
	$id = $FORM->addElement('hidden', 'id', array('id' => 'id'));

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
			'callback' => array($ABBREVIATION, 'testForUniqueLongForm'),
			'arguments' => array($id->getValue())
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
		
	// select for text_converter
	$text_converter = $FORM->addElement('select', 'text_converter',
	 	array('id' => 'abbreviation_text_converter'),
		array('label' => gettext('Text converter'), 'options' => $TEXTCONVERTER->getTextConverterListForForm())
		);
		
	// checkbox for apply_macros
	$apply_macros = $FORM->addElement('checkbox', 'apply_macros',
		array('id' => 'abbreviation_apply_macros', 'class' => 'chbx'),
		array('label' => gettext('Apply text macros'))
		);
	$apply_macros->addRule('regex', gettext('The field whether to apply text macros accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);


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
		'id' => Base_Cnc::ifsetor($abbreviation['id'], null),
		'name' => Base_Cnc::ifsetor($abbreviation['name'], null),
		'long_form' => Base_Cnc::ifsetor($abbreviation['long_form'], null),
		'content' => Base_Cnc::ifsetor($abbreviation['content_raw'], null),
		'lang' => Base_Cnc::ifsetor($abbreviation['lang'], null),
		'text_converter' => Base_Cnc::ifsetor($abbreviation['text_converter'], null),
		'apply_macros' => Base_Cnc::ifsetor($abbreviation['apply_macros'], null)
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
	    
		// assign current user and project id
		$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);
		$BASE->utility->smarty->assign('wcom_current_project', WCOM_CURRENT_PROJECT);
		
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

		// select available projects
		$select_params = array(
			'user' => WCOM_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('content/abbreviations_edit.html', WCOM_TEMPLATE_KEY);
		
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
		$sqlData['text_converter'] = ($text_converter->getValue() > 0) ? 
			$text_converter->getValue() : null;
		$sqlData['apply_macros'] = (string)intval($apply_macros->getValue());
		$sqlData['lang'] = $lang->getValue();
		
		// apply text macros and text converter if required
		if ($text_converter->getValue() > 0 || $apply_macros->getValue() > 0) {
			// extract content
			$content = $content->getValue();

			// apply startup and pre text converter text macros 
			if ($apply_macros->getValue() > 0) {
				$content = $TEXTMACRO->applyTextMacros($content, 'pre');
			}

			// apply text converter
			if ($text_converter->getValue() > 0) {
				$content = $TEXTCONVERTER->applyTextConverter(
					$text_converter->getValue(), $content
				);
			}

			// apply post text converter and shutdown text macros 
			if ($apply_macros->getValue() > 0) {
				$content = $TEXTMACRO->applyTextMacros($content, 'post');
			}

			// assign content to sql data array
			$sqlData['content'] = $content;
		}

		// check sql data for pear errors
		$HELPER = load('utility:helper');
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$ABBREVIATION->updateAbbreviation($id->getValue(), $sqlData);
			
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
		
		// redirect
		if (!empty($saveAndRemainOnPage)) {
			header("Location: abbreviations_edit.php?id=".$id->getValue());
		} else {
			header("Location: abbreviations_select.php");
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