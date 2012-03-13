<?php

/**
 * Project: Welcompose
 * File: pages_generatorforms_fields_edit.php
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
	
	// load page class
	/* @var $PAGE Content_Page */
	$PAGE = load('content:page');
	
	// load Content_GeneratorFormField class
	$GENERATORFORMFIELD = load('Content:GeneratorFormField');
	
	// load helper class
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
	if (!wcom_check_access('Content', 'GeneratorFormField', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
	// get page
	$page = $PAGE->selectPage(Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC));
	
	// get form field
	$form_field = $GENERATORFORMFIELD->selectGeneratorFormField(Base_Cnc::filterRequest($_REQUEST['id'],
		WCOM_REGEX_NUMERIC));
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('generator_form_field');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');
	
	// hidden for id
	$id = $FORM->addElement('hidden', 'id', array('id' => 'id'));
	$id->addRule('required', gettext('Page is not expected to be empty'));
	$id->addRule('regex', gettext('Id is expected to be numeric'), WCOM_REGEX_NUMERIC);
	
	// hidden for page
	$page_id = $FORM->addElement('hidden', 'page', array('id' => 'page'));
	$page_id->addRule('required', gettext('Page is not expected to be empty'));
	$page_id->addRule('regex', gettext('Page is expected to be numeric'), WCOM_REGEX_NUMERIC);
	
	// hidden for start	
	$start = $FORM->addElement('hidden', 'start', array('id' => 'start'));
	$start->addRule('regex', gettext('start is expected to be numeric'), WCOM_REGEX_NUMERIC);

	// hidden for limit
	$limit = $FORM->addElement('hidden', 'limit', array('id' => 'limit'));
	$limit->addRule('regex', gettext('limit is expected to be numeric'), WCOM_REGEX_NUMERIC);

	// hidden for macro
	$macro = $FORM->addElement('hidden', 'macro', array('id' => 'macro'));

	// select for type
	$type = $FORM->addElement('select', 'type',
	 	array('id' => 'generator_form_field_type'),
		array('label' => gettext('Type'), 'options' => $GENERATORFORMFIELD->getTypeListForForm())
		);
	$type->addRule('required', gettext('Select a field type'));	
	
	// textfield for label
	$label = $FORM->addElement('text', 'label', 
		array('id' => 'generator_form_field_label', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Label'))
		);	
	
	// textfield for name
	$name = $FORM->addElement('text', 'name', 
		array('id' => 'generator_form_field_name', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('Name'))
		);
	$name->addRule('required', gettext('Please enter a field name'));
	$name->addRule('regex', gettext('Only alphanumeric field names are allowed'), WCOM_REGEX_OPERATOR_NAME);
	$name->addRule('callback', gettext('A field with the given name already exists'), 
		array(
			'callback' => array($GENERATORFORMFIELD, 'testForUniqueName'),
			'arguments' => array(array("form" => $page_id->getValue(), "id" => $id->getValue()))
		)
	);
	
	// textarea for value
	$value = $FORM->addElement('textarea', 'value', 
		array('id' => 'generator_form_field_value', 'cols' => 3, 'rows' => '2', 'class' => 'w540h50'),
		array('label' => gettext('Value'))
		);
	
	// textfield for class
	$class = $FORM->addElement('text', 'class', 
		array('id' => 'generator_form_field_class', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('CSS class'))
		);

	// checkbox for required		
	$required = $FORM->addElement('checkbox', 'required',
		array('id' => 'generator_form_field_required', 'class' => 'chbx'),
		array('label' => gettext('Required'))
		);
	$required->addRule('regex', gettext('The field required accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
	
	// textfield for required message
	$required_message = $FORM->addElement('text', 'required_message', 
		array('id' => 'generator_form_field_required_message', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Required message'))
		);		
	if ($required->getValue() == 1) {
		$required_message->addRule('required', gettext('Please enter a required message'));
	}		

	// textfield for regular_expression
	$validator_regex = $FORM->addElement('text', 'validator_regex', 
		array('id' => 'generator_form_field_validator_regex', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Regular expression'))
		);
		
	// textfield for validator message
	$validator_message = $FORM->addElement('text', 'validator_message', 
		array('id' => 'generator_form_field_validator_message', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Validator message'))
		);		
	if ($validator_regex->getValue() == 1) {
		$validator_message->addRule('required', gettext('Please enter a validator message'));
	}	
		
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
		'id' => Base_Cnc::ifsetor($form_field['id'], null),
		'page' => Base_Cnc::ifsetor($form_field['form'], null),
		'start' => Base_Cnc::filterRequest($_REQUEST['start'], WCOM_REGEX_NUMERIC),
		'limit' => Base_Cnc::filterRequest($_REQUEST['limit'], WCOM_REGEX_NUMERIC),
		'macro' => Base_Cnc::filterRequest($_REQUEST['macro'], WCOM_REGEX_ORDER_MACRO),
		'type' => Base_Cnc::ifsetor($form_field['type'], null),
		'label' => Base_Cnc::ifsetor($form_field['label'], null),
		'name' => Base_Cnc::ifsetor($form_field['name'], null),
		'value' => Base_Cnc::ifsetor($form_field['value'], null),
		'class' => Base_Cnc::ifsetor($form_field['class'], null),
		'required' => Base_Cnc::ifsetor($form_field['required'], null),
		'required_message' => Base_Cnc::ifsetor($form_field['required_message'], null),
		'validator_regex' => Base_Cnc::ifsetor($form_field['validator_regex'], null),
		'validator_message' => Base_Cnc::ifsetor($form_field['validator_message'], null)
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
		$BASE->utility->smarty->display('content/pages_generatorforms_fields_edit.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->toggleFrozen(true);
		
		// prepare sql data
		$sqlData = array();
		$sqlData['type'] = $type->getValue();
		$sqlData['label'] = $label->getValue();
		$sqlData['name'] = $name->getValue();
		$sqlData['value'] = $value->getValue();
		$sqlData['class'] = $class->getValue();
		$sqlData['required'] = (string)intval($required->getValue());
		$sqlData['required_message'] = $required_message->getValue();
		$sqlData['validator_regex'] = $validator_regex->getValue();
		$sqlData['validator_message'] = $validator_message->getValue();
		
		// test sql data for pear errors
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$GENERATORFORMFIELD->updateGeneratorFormField($id->getValue(), $sqlData);
			
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
		$macro = $macro->getValue();
		
		// append request params
		$redirect_params = (!empty($start)) ? '&start='.$start : '';
		$redirect_params .= (!empty($limit)) ? '&limit='.$limit : '&limit=20';
		$redirect_params .= (!empty($macro)) ? '&macro='.$macro : '';
		
		// redirect
		if (!empty($saveAndRemainOnPage)) {
			header("Location: pages_generatorforms_fields_edit.php?page=".
						$page_id->getValue()."&id=".$id->getValue().$redirect_params);
		} else {
			header("Location: pages_generatorforms_fields_select.php?page=".
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