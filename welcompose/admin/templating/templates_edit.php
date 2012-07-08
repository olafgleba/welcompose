<?php

/**
 * Project: Welcompose
 * File: templates_edit.php
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
	
	// load template class
	/* @var $TEMPLATE Templating_Template */
	$TEMPLATE = load('templating:template');
	
	// load templatetype class
	/* @var $TEMPLATETYPE Templating_Templatetype */
	$TEMPLATETYPE = load('templating:templatetype');
	
	// load templateset class
	/* @var $TEMPLATESET Templating_Templateset */
	$TEMPLATESET = load('templating:templateset');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Templating', 'Template', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
	// prepare template types
	$template_types = array();
	foreach ($TEMPLATETYPE->selectTemplateTypes() as $_template_type) {
		$template_types[(int)$_template_type['id']] = htmlspecialchars($_template_type['name']);
	}
	
	// prepare template sets
	$template_sets = array();
	foreach ($TEMPLATESET->selectTemplateSets() as $_template_set) {
		$template_sets[(int)$_template_set['id']] = htmlspecialchars($_template_set['name']);
	}
	
	// get template
	$template = $TEMPLATE->selectTemplate(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
	
	// get selected template sets
	$selected_template_sets = array();
	foreach ($TEMPLATE->selectTemplateToSetsMap(Base_Cnc::ifsetor($template['id'], null)) as $_link) {
		$selected_template_sets[] = $_link['set'];
	}
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('template');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');

	// hidden for id
	$id = $FORM->addElement('hidden', 'id', array('id' => 'id'));

	// hidden for set
	$set_request = $FORM->addElement('hidden', 'set_request', array('id' => 'set_request'));
	$set_request->addRule('regex', gettext('set_request is expected to be numeric'), WCOM_REGEX_NUMERIC);

	// hidden for type
	$type_request = $FORM->addElement('hidden', 'type_request', array('id' => 'type_request'));
	$type_request->addRule('regex', gettext('type_request is expected to be numeric'), WCOM_REGEX_NUMERIC);
		
	// hidden for start
	$start = $FORM->addElement('hidden', 'start', array('id' => 'start'));

	
	// textfield for type
	$type = $FORM->addElement('select', 'type',
	 	array('id' => 'template_type'),
		array('label' => gettext('Type'), 'options' => $template_types)
		);
	$type->addRule('required', gettext('Please select a template type'));
	$type->addRule('callback', gettext('A template with the same type for your choosen set(s) already exists'), 
		array(
			'callback' => array($TEMPLATE, 'testForUniqueTypeAndSet'),
			'arguments' => array($id->getValue())
		)
	);
	
	// textfield for name
	$name = $FORM->addElement('text', 'name', 
		array('id' => 'template_name', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Name'))
		);
	$name->addRule('required', gettext('Please enter a name'));
	$name->addRule('callback', gettext('A template with the given name already exists'), 
		array(
			'callback' => array($TEMPLATE, 'testForUniqueName'),
			'arguments' => array($id->getValue())
		)
	);
		
	// textarea for description
	$description = $FORM->addElement('textarea', 'description', 
		array('id' => 'template_description', 'cols' => 3, 'rows' => 2, 'class' => 'w298h50'),
		array('label' => gettext('Description'))
		);
		
	// textarea for content
	$content = $FORM->addElement('textarea', 'content', 
		array('id' => 'template_content', 'cols' => 3, 'rows' => 2, 'class' => 'w540h550'),
		array('label' => gettext('Content'))
		);
		
	// select for set	
	$sets = $FORM->addElement('select', 'sets',
	 	array('id' => 'template_sets', 'class' => 'multisel', 'multiple' => 'multiple', 'size' => 10),
		array('label' => gettext('Sets'), 'options' => $template_sets)
		);
	$sets->addRule('required', gettext('Please select a template set'));
	$sets->setValue($selected_template_sets);
		
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
		'set_request' => Base_Cnc::filterRequest($_REQUEST['set'], WCOM_REGEX_NUMERIC),
		'type_request' => Base_Cnc::filterRequest($_REQUEST['type'], WCOM_REGEX_NUMERIC),
		'start' => Base_Cnc::filterRequest($_REQUEST['start'], WCOM_REGEX_NUMERIC),
		'id' => Base_Cnc::ifsetor($template['id'], null),
		'type' => Base_Cnc::ifsetor($template['type'], null),
		'name' => Base_Cnc::ifsetor($template['name'], null),
		'description' => Base_Cnc::ifsetor($template['description'], null),
		'content' => Base_Cnc::ifsetor($template['content'], null)
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
		
		// calculate and assign template type count
		$BASE->utility->smarty->assign('template_type_count', count($template_types));
		
		// select available projects
		$select_params = array(
			'user' => WCOM_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('templating/templates_edit.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->toggleFrozen(true);
		
		// create the article group
		$sqlData = array();
		$sqlData['type'] = $type->getValue();
		$sqlData['name'] = $name->getValue();
		$sqlData['description'] = $description->getValue();
		$sqlData['content'] = $content->getValue();
		
		// check sql data
		$HELPER = load('utility:helper');
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$TEMPLATE->updateTemplate($id->getValue(), $sqlData);
			
			// map template to selected sets
			if (is_array($sets->getValue())) {
				$TEMPLATE->mapTemplateToSets($id->getValue(), $sets->getValue());
			} else {
				$TEMPLATE->mapTemplateToSets($id->getValue(), array());
			}
			
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

		// save request set value
		$set_request = $set_request->getValue();
		$set_request = (!empty($set_request)) ? $set_request : 0;
		
		// save request type value
		$type_request = $type_request->getValue();
		$type_request = (!empty($type_request)) ? $type_request : 0;
		
		// save request start range
		$start = $start->getValue();
		$start = (!empty($start)) ? $start : 0;
		
		// redirect
		if (!empty($saveAndRemainOnPage)) {
			header("Location: templates_edit.php?id=".$id->getValue().
			"&set=".$set_request.
			"&type=".$type_request.
			"&start=".$start
			);
		} else {
			header("Location: templates_select.php?set=".$set_request.
			"&type=".$type_request.
			"&start=".$start
			);
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
