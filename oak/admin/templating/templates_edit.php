<?php

/**
 * Project: Oak
 * File: templates_edit.php
 *
 * Copyright (c) 2006 sopic GmbH
 *
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License
 * http://www.opensource.org/licenses/osl-2.1.php
 *
 * $Id$
 *
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

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
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(OAK_CURRENT_USER);
	
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
	$template = $TEMPLATE->selectTemplate(Base_Cnc::filterRequest($_REQUEST['id'], OAK_REGEX_NUMERIC));
	
	// get selected template sets
	$selected_template_sets = array();
	foreach ($TEMPLATE->selectTemplateToSetsMap(Base_Cnc::ifsetor($template['id'], null)) as $_link) {
		$selected_template_sets[] = $_link['set'];
	}
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('template', 'post');
	$FORM->registerRule('testForNameUniqueness', 'callback', 'testForUniqueName', $TEMPLATE);
	
	// hidden for id
	$FORM->addElement('hidden', 'id');
	$FORM->applyFilter('id', 'trim');
	$FORM->applyFilter('id', 'strip_tags');
	$FORM->addRule('id', gettext('Id is not expected to be empty'), 'required');
	$FORM->addRule('id', gettext('Id is expected to be numeric'), 'numeric');
	
	// select for group
	$FORM->addElement('select', 'type', gettext('Type'), $template_types,
		array('id' => 'template_type'));
	$FORM->applyFilter('type', 'trim');
	$FORM->applyFilter('type', 'strip_tags');
	$FORM->addRule('type', gettext('Please select a template type'), 'required');
	
	// select for set
	$template_set_element = $FORM->addElement('select', 'sets', gettext('Sets'), $template_sets,
		array('id' => 'template_sets', 'class' => 'multisel', 'multiple' => 'multiple', 'size' => 10));
	$template_set_element->setSelected($selected_template_sets);
	$FORM->applyFilter('set', 'trim');
	$FORM->applyFilter('set', 'strip_tags');
	$FORM->addRule('set', gettext('Please select a template set'), 'required');
	
	// textfield for name
	$FORM->addElement('text', 'name', gettext('Name'), 
		array('id' => 'template_name', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('name', 'trim');
	$FORM->applyFilter('name', 'strip_tags');
	$FORM->addRule('name', gettext('Please enter a name'), 'required');
	$FORM->addRule('name', gettext('A template with the given name already exists'),
		'testForNameUniqueness', $FORM->exportValue('id'));
	
	// textarea for description
	$FORM->addElement('textarea', 'description', gettext('Description'),
		array('id' => 'template_description', 'class' => 'w298h50', 'cols' => 3, 'rows' => 2));
	$FORM->applyFilter('description', 'trim');
	$FORM->applyFilter('description', 'strip_tags');
	
	// textarea for content
	$FORM->addElement('textarea', 'content', gettext('Content'),
		array('id' => 'template_content', 'class' => 'w530h400', 'cols' => 3, 'rows' => 2));
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Update template'),
		array('class' => 'submitbut200'));
		
	// set defaults
	$FORM->setDefaults(array(
		'id' => Base_Cnc::ifsetor($template['id'], null),
		'type' => Base_Cnc::ifsetor($template['type'], null),
		'name' => Base_Cnc::ifsetor($template['name'], null),
		'description' => Base_Cnc::ifsetor($template['description'], null),
		'content' => Base_Cnc::ifsetor($template['content'], null)
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
		$BASE->utility->smarty->assign('oak_admin_root_www',
			$BASE->_conf['path']['oak_admin_root_www']);
		
	    // build session
	    $session = array(
			'response' => Base_Cnc::filterRequest($_SESSION['response'], OAK_REGEX_NUMERIC)
	    );
	    
	    // assign prepared session array to smarty
	    $BASE->utility->smarty->assign('session', $session);
	    
	    // empty $_SESSION
	    if (!empty($_SESSION['response'])) {
	        $_SESSION['response'] = '';
	    }
	    
		// assign current user and project id
		$BASE->utility->smarty->assign('oak_current_user', OAK_CURRENT_USER);
		$BASE->utility->smarty->assign('oak_current_project', OAK_CURRENT_PROJECT);

		// select available projects
		$select_params = array(
			'user' => OAK_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// display the form
		define("OAK_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('templating/templates_edit.html', OAK_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// create the article group
		$sqlData = array();
		$sqlData['type'] = $FORM->exportValue('type');
		$sqlData['name'] = $FORM->exportValue('name');
		$sqlData['description'] = $FORM->exportValue('description');
		$sqlData['content'] = $FORM->exportValue('content');
		
		// check sql data
		$HELPER = load('utility:helper');
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$TEMPLATE->updateTemplate($FORM->exportValue('id'), $sqlData);
			
			// map template to selected sets
			if (is_array($FORM->exportValue('sets'))) {
				$TEMPLATE->mapTemplateToSets($FORM->exportValue('id'), $FORM->exportValue('sets'));
			} else {
				$TEMPLATE->mapTemplateToSets($FORM->exportValue('id'), array());
			}
			
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
			
			// re-throw exception
			throw $e;
		}
	
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: templates_select.php");
		exit;
	}
} catch (Exception $e) {
	// clean the buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	Base_Error::triggerException($BASE->utility->smarty, $e);	
	
	// exit
	exit;
}

?>