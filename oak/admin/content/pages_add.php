<?php

/**
 * Project: Oak
 * File: pages_add.php
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

// define area constant
define('OAK_CURRENT_AREA', 'ADMIN');

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
	
	// load login class
	/* @var $LOGIN User_Login */
	$LOGIN = load('User:Login');
	
	// load project class
	/* @var $PROJECT Application_Project */
	$PROJECT = load('application:project');
	
	// load page class
	/* @var $PAGE Content_Page */
	$PAGE = load('content:page');
	
	// load helper class
	/* @var $HELPER Utility_Helper */
	$HELPER = load('utility:helper');
	
	// load nestedset class
	/* @var $NESTEDSET Utility_Nestedset */
	$NESTEDSET = load('utility:nestedset');
	
	// load navigation class
	/* @var $NAVIGATION Content_Navigation */
	$NAVIGATION = load('content:navigation');
	
	// load pagetype class
	/* @var $PAGETYPE Content_Pagetype */
	$PAGETYPE = load('content:pagetype');
	
	// load templateset class
	/* @var $TEMPLATESET Templating_Templateset */
	$TEMPLATESET = load('templating:templateset');
	
	// load group class
	/* @var $GROUP User_Group */
	$GROUP = load('user:group');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(OAK_CURRENT_USER);
	
	// prepare positions
	$positions = array();
	
	// prepare positions
	$positions = array(
		UTILITY_NESTEDSET_CREATE_BEFORE => gettext('Create node above'),
		UTILITY_NESTEDSET_CREATE_AFTER => gettext('Create node below')
	);
	
	// prepare page types
	$types = array();
	foreach ($PAGETYPE->selectPageTypes() as $_type) {
		$types[(int)$_type['id']] = htmlspecialchars($_type['name']);
	}
	
	// prepare template sets
	$template_sets = array();
	foreach ($TEMPLATESET->selectTemplateSets() as $_template_set) {
		$template_sets[(int)$_template_set['id']] = htmlspecialchars($_template_set['name']);
	}
	
	// prepare groups
	$groups = array();
	foreach ($GROUP->selectGroups() as $_group) {
		$groups[(int)$_group['id']] = htmlspecialchars($_group['name']);
	}
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('page', 'post');
	
	// hidden for navigation
	$FORM->addElement('hidden', 'navigation');
	$FORM->applyFilter('navigation', 'trim');
	$FORM->applyFilter('navigation', 'strip_tags');
	$FORM->addRule('navigation', gettext('Navigation is not expected to be empty'), 'required');
	$FORM->addRule('navigation', gettext('Navigation is expected to be numeric'), 'numeric');
	
	// hidden for reference
	$FORM->addElement('hidden', 'reference');
	$FORM->applyFilter('reference', 'trim');
	$FORM->applyFilter('reference', 'strip_tags');
	$FORM->addRule('reference', gettext('Reference is expected to be numeric'), 'numeric');
	
	// select for type
	$FORM->addElement('select', 'position', gettext('Position'), $positions,
		array('id' => 'page_position'));
	$FORM->applyFilter('position', 'trim');
	$FORM->applyFilter('position', 'strip_tags');
	$FORM->addRule('position', gettext('Please choose position'), 'required');
	$FORM->addRule('position', gettext('Chosen position is out of range'), 'in_array_keys', $positions);
	
	// textfield for name
	$FORM->addElement('text', 'name', gettext('Name'), 
		array('id' => 'page_name', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('name', 'trim');
	$FORM->applyFilter('name', 'strip_tags');
	$FORM->addRule('name', gettext('Please enter a name'), 'required');
	
	// select for type
	$FORM->addElement('select', 'type', gettext('Type'), $types,
		array('id' => 'page_type'));
	$FORM->applyFilter('type', 'trim');
	$FORM->applyFilter('type', 'strip_tags');
	$FORM->addRule('type', gettext('Please choose a page type'), 'required');
	$FORM->addRule('type', gettext('Chosen page type is out of range'), 'in_array_keys', $types);
	
	// select for type
	$FORM->addElement('select', 'template_set', gettext('Template set'), $template_sets,
		array('id' => 'page_template_set'));
	$FORM->applyFilter('template_set', 'trim');
	$FORM->applyFilter('template_set', 'strip_tags');
	$FORM->addRule('template_set', gettext('Please choose a template set'), 'required');
	$FORM->addRule('template_set', gettext('Chosen template set is out of range'), 'in_array_keys',
		$template_sets);
	
	// checkbox for index_page
	$FORM->addElement('checkbox', 'index_page', gettext('Index page'), null,
		array('id' => 'page_index_page', 'class' => 'chbx'));
	$FORM->applyFilter('index_page', 'trim');
	$FORM->applyFilter('index_page', 'strip_tags');
	$FORM->addRule('index_page', gettext('The field index_page accepts only 0 or 1'),
		'regex', OAK_REGEX_ZERO_OR_ONE);
	
	// checkbox for protect
	$FORM->addElement('checkbox', 'protect', gettext('Protect'), null,
		array('id' => 'page_protect', 'class' => 'chbx'));
	$FORM->applyFilter('protect', 'trim');
	$FORM->applyFilter('protect', 'strip_tags');
	$FORM->addRule('protect', gettext('The field protect accepts only 0 or 1'),
		'regex', OAK_REGEX_ZERO_OR_ONE);
	
	// multi select for rights
	$FORM->addElement('select', 'groups', gettext('Groups'), $groups,
		array('id' => 'page_groups', 'class' => 'multisel', 'multiple' => 'multiple', 'size' => 10));
	$FORM->applyFilter('groups', 'trim');
	$FORM->applyFilter('groups', 'strip_tags');
	$FORM->addRule('groups', gettext('One of the chosen groups is out of range'), 'in_array_keys', $groups);
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Add page'),
		array('class' => 'submit140'));
	
	// set defaults
	$FORM->setDefaults(array(
		'navigation' => Base_Cnc::filterRequest($_REQUEST['navigation'], OAK_REGEX_NUMERIC),
		'reference' => Base_Cnc::filterRequest($_REQUEST['reference'], OAK_REGEX_NUMERIC),
		'position' => UTILITY_NESTEDSET_CREATE_AFTER
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
		
		// build $session
	    $session = array(
			'response' => Base_Cnc::filterRequest($_SESSION['response'], OAK_REGEX_NUMERIC)
	    );
	    
	    // assign $_SESSION to smarty
	    $BASE->utility->smarty->assign('session', $session);
	    
	    // empty $_SESSION
	    if (!empty($_SESSION['response'])) {
	        $_SESSION['response'] = '';
	    }
	    
		// assign current user and project id
		$BASE->utility->smarty->assign('oak_current_user', OAK_CURRENT_USER);
		$BASE->utility->smarty->assign('oak_current_project', OAK_CURRENT_PROJECT);
		
		// assign page type and template set counts
		$BASE->utility->smarty->assign('page_type_count', count($types));
		$BASE->utility->smarty->assign('template_set_count', count($template_sets));
		
		// select available projects
		$select_params = array(
			'user' => OAK_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// display the form
		define("OAK_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('content/pages_add.html', OAK_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// create the article group
		$sqlData = array();
		$sqlData['project'] = OAK_CURRENT_PROJECT;
		$sqlData['name'] = $FORM->exportValue('name');
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// create node
			$page_id = $NESTEDSET->createNode($FORM->exportValue('navigation'), $FORM->exportValue('reference'),
				$FORM->exportValue('position'));
			
			// test url name for uniqueness
			$url_name = $HELPER->createMeaningfulString($FORM->exportValue('name'));
			if (!$PAGE->testForUniqueUrlName($url_name)) {
				$url_name = $url_name.'-'.$page_id;
			}
			
			// prepare sql data for page create
			$sqlData = array();
			$sqlData['id'] = $page_id;
			$sqlData['project'] = OAK_CURRENT_PROJECT;
			$sqlData['template_set'] = $FORM->exportValue('template_set');
			$sqlData['name'] = $FORM->exportValue('name');
			$sqlData['name_url'] = $url_name;
			$sqlData['type'] = $FORM->exportValue('type');
			$sqlData['index_page'] = $FORM->exportValue('index_page');
			$sqlData['protect'] = $FORM->exportValue('protect');
			
			$HELPER->testSqlDataForPearErrors($sqlData);
			
			// execute operation
			$PAGE->addPage($sqlData);
			
			// map page to groups
			if (intval($FORM->exportValue('protect')) === 1) {
				$PAGE->mapPageToGroups($page_id, (array)$FORM->exportValue('groups'));
			}
			
			// look at the index page field
			if (intval($FORM->exportValue('index_page')) === 1) {
				$PAGE->setIndexPage($page_id);
			}
			
			// init page contents
			$PAGE->initPageContents($page_id);
			
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
		header("Location: pages_select.php");
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