<?php

/**
 * Project: Welcompose
 * File: structuraltemplates_copy.php
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
	
	// load Content_StructuralTemplate class
	/* @var $STRUCTURALTEMPLATE Content_StructuralTemplate */
	$STRUCTURALTEMPLATE = load('Content:StructuralTemplate');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Content', 'StructuralTemplate', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
	// get structural template
	$template_id = Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC);
	$structural_template = $STRUCTURALTEMPLATE->selectStructuralTemplate($template_id);
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('structural_template');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');
	
	// hidden id
	$id = $FORM->addElement('hidden', 'id', array('id' => 'id'));	
		
	$name = $FORM->addElement('text', 'name', 
		array('id' => 'structural_template_name', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Name'))
		);
	$name->addRule('required', gettext('Please enter a name'));
	$name->addRule('callback', gettext('A structural template with the given name already exists'), 
		array(
			'callback' => array($STRUCTURALTEMPLATE, 'testForUniqueName'),
			'arguments' => array($id->getValue())
		)
	);
	
	// textarea for description
	$description = $FORM->addElement('textarea', 'description', 
		array('id' => 'structural_template_description', 'cols' => 3, 'rows' => '2', 'class' => 'w298h50'),
		array('label' => gettext('Description'))
		);
	
	// textarea for content
	$content = $FORM->addElement('textarea', 'content', 
		array('id' => 'structural_template_content', 'cols' => 3, 'rows' => '2', 'class' => 'w540h550'),
		array('label' => gettext('Content'))
		);
		
	// submit button (save and stay)
	$save = $FORM->addElement('submit', 'save', 
		array('class' => 'submit200', 'value' => gettext('Duplicate structural template'))
		);
	
	// set defaults
	$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
		'id' => Base_Cnc::ifsetor($structural_template['id'], null),
		'name' => Base_Cnc::ifsetor($structural_template['name'], null),
		'description' => Base_Cnc::ifsetor($structural_template['description'], null),
		'content' => Base_Cnc::ifsetor($structural_template['content'], null)
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

		// select available projects
		$select_params = array(
			'user' => WCOM_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('content/structuraltemplates_copy.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->toggleFrozen(true);
		
		// create the article group
		$sqlData = array();
		$sqlData['project'] = WCOM_CURRENT_PROJECT;
		$sqlData['name'] = $name->getValue();
		$sqlData['description'] = $description->getValue();
		$sqlData['content'] = $content->getValue();
		$sqlData['date_added'] = date('Y-m-d H:i:s');
		
		// check sql data for pear errors
		$HELPER = load('utility:helper');
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$STRUCTURALTEMPLATE->addStructuralTemplate($sqlData);
			
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
			
			// re-throw exception
			throw $e;
		}
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: structuraltemplates_select.php");
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