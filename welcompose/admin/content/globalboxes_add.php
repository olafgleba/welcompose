<?php

/**
 * Project: Welcompose
 * File: globalboxes_add.php
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
 * @author Andreas Ahlenstorf
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
	
	// load Content_GlobalBox class
	/* @var $GLOBALBOX Content_GlobalBox */
	$GLOBALBOX = load('Content:GlobalBox');
	
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
	if (!wcom_check_access('Content', 'GlobalBox', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
	// assign current project values
	$_wcom_current_project = $PROJECT->selectProject(WCOM_CURRENT_PROJECT);
	$BASE->utility->smarty->assign('_wcom_current_project', $_wcom_current_project);

	// get default text converter if set
	$default_text_converter = $TEXTCONVERTER->selectDefaultTextConverter();
		
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('global_box');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');
	
	// textfield for name
	$name = $FORM->addElement('text', 'name',
		array('id' => 'global_box_name', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Name'))
		);
	$name->addRule('required', gettext('Please enter a name'));
	$name->addRule('callback', gettext('A global box with the given name already exists'), 
		array(
			'callback' => array($GLOBALBOX, 'testForUniqueName')
		)
	);
	
	// textarea for content
	$content = $FORM->addElement('textarea', 'content',
		array('id' => 'global_box_content', 'cols' => 3, 'rows' => '2', 'class' => 'w540h550'),
		array('label' => gettext('Content'))
		);

	// select for text_converter
	$text_converter = $FORM->addElement('select', 'text_converter',
	 	array('id' => 'global_box_text_converter'),
		array('label' => gettext('Text converter'), 'options' => $TEXTCONVERTER->getTextConverterListForForm())
		);
		
	// checkbox for apply_macros
	$apply_macros = $FORM->addElement('checkbox', 'apply_macros',
		array('id' => 'abbreviation_apply_macros', 'class' => 'chbx'),
		array('label' => gettext('Apply text macros'))
		);
	$apply_macros->addRule('regex', gettext('The field whether to apply text macros accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
		
	// submit button
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'submit200', 'value' => gettext('Save'))
		);
		
	// set defaults	
	$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
		'apply_macros' => 1,
		'text_converter' => ($default_text_converter > 0) ? $default_text_converter['id'] : null
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
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('content/globalboxes_add.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->toggleFrozen(true);
		
		// create the global box
		$sqlData = array();
		$sqlData['project'] = WCOM_CURRENT_PROJECT;
		$sqlData['name'] = $name->getValue();
		$sqlData['content_raw'] = $content->getValue();
		$sqlData['content'] = $content->getValue();
		$sqlData['text_converter'] = ($text_converter->getValue() > 0) ? 
			$text_converter->getValue() : null;
		$sqlData['apply_macros'] = (string)intval($apply_macros->getValue());
		$sqlData['date_added'] = date('Y-m-d H:i:s');
			
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
			$GLOBALBOX->addGlobalBox($sqlData);
			
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
	
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: globalboxes_add.php");
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