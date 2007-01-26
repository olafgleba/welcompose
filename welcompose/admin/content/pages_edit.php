<?php

/**
 * Project: Welcompose
 * File: pages_edit.php
 *
 * Copyright (c) 2006 sopic GmbH
 *
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-3.0.php
 *
 * $Id$
 *
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
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
	
	// load helper class
	/* @var $HELPER Utility_Helper */
	$HELPER = load('utility:helper');

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
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Content', 'Page', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// get page
	$page = $PAGE->selectPage(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
	
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
	
	// get selected groups
	$selected_groups = array();
	foreach ($PAGE->selectPageToGroupsMap($page['id']) as $_link) {
		$selected_groups[] = (int)$_link['group'];
	}
	
	// prepare sitemap change frequencies
	$sitemap_change_frequencies = array(
		'always' => gettext('always'),
		'hourly' => gettext('hourly'),
		'daily' => gettext('daily'),
		'weekly' => gettext('weekly'),
		'monthly' => gettext('monthly'),
		'yearly' => gettext('yearly'),
		'never' => gettext('never')
	);
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('page', 'post');
	
	// hidden for id
	$FORM->addElement('hidden', 'id');
	$FORM->applyFilter('id', 'trim');
	$FORM->applyFilter('id', 'strip_tags');
	$FORM->addRule('id', gettext('Id is not expected to be empty'), 'required');
	$FORM->addRule('id', gettext('Id is expected to be numeric'), 'numeric');
	
	// textfield for name
	$FORM->addElement('text', 'name', gettext('Name'), 
		array('id' => 'page_name', 'maxlength' => 255, 'class' => 'w300 validate urlify'));
	$FORM->applyFilter('name', 'trim');
	$FORM->applyFilter('name', 'strip_tags');
	$FORM->addRule('name', gettext('Please enter a name'), 'required');
	
	// textfield for url_name
	$FORM->addElement('text', 'name_url', gettext('URL name'), 
		array('id' => 'page_name_url', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('name_url', 'trim');
	$FORM->applyFilter('name_url', 'strip_tags');
	$FORM->addRule('name_url', gettext('Enter an URL name'), 'required');
	$FORM->addRule('name_url', gettext('The URL name may only contain chars, numbers and hyphens'),
		WCOM_REGEX_URL_NAME);
	
	// textfield for alternate name
	$FORM->addElement('text', 'alternate_name', gettext('Alternate name'), 
		array('id' => 'page_alternate_name', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('alternate_name', 'trim');
	$FORM->applyFilter('alternate_name', 'strip_tags');
	
	// textarea for description
	$FORM->addElement('textarea', 'description', gettext('Description'),
		array('id' => 'page_description', 'class' => 'w298h50', 'cols' => 3, 'rows' => 2));
	$FORM->applyFilter('description', 'trim');
	$FORM->applyFilter('description', 'strip_tags');
	
	// textarea for optional_text
	$FORM->addElement('textarea', 'optional_text', gettext('Optional text'),
		array('id' => 'page_optional_text', 'class' => 'w298h50', 'cols' => 3, 'rows' => 2));
	$FORM->applyFilter('optional_text', 'trim');
	$FORM->applyFilter('optional_text', 'strip_tags');
	
	// select for template set
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
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// checkbox for protect
	$FORM->addElement('checkbox', 'protect', gettext('Protect'), null,
		array('id' => 'page_protect', 'class' => 'chbx'));
	$FORM->applyFilter('protect', 'trim');
	$FORM->applyFilter('protect', 'strip_tags');
	$FORM->addRule('protect', gettext('The field protect accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// multi select for rights
	$FORM->addElement('select', 'groups', gettext('Groups'), $groups,
		array('id' => 'page_groups', 'class' => 'multisel', 'multiple' => 'multiple', 'size' => 10));
	$FORM->applyFilter('groups', 'trim');
	$FORM->applyFilter('groups', 'strip_tags');
	$FORM->addRule('groups', gettext('One of the chosen groups is out of range'), 'in_array_keys', $groups);
	
	// multi select for rights
	$FORM->addElement('select', 'sitemap_changefreq', gettext('Sitemap change frequency'),
		$sitemap_change_frequencies, array('id' => 'page_sitemap_changefreq'));
	$FORM->applyFilter('sitemap_changefreq', 'trim');
	$FORM->applyFilter('sitemap_changefreq', 'strip_tags');
	$FORM->addRule('sitemap_changefreq', gettext('Chosen sitemap change frequency is out of range'),
		'in_array_keys', $sitemap_change_frequencies);
	
	// textfield for name
	$FORM->addElement('text', 'sitemap_priority', gettext('Sitemap Priority'), 
		array('id' => 'page_sitemap_priority', 'maxlength' => 3, 'class' => 'w300 validate'));
	$FORM->applyFilter('sitemap_priority', 'trim');
	$FORM->applyFilter('sitemap_priority', 'strip_tags');
	$FORM->addRule('sitemap_priority', gettext('Please enter a sitemap priority between 0.1 and 1.0'),
		'regex', WCOM_REGEX_SITEMAP_PRIORITY);
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Edit page'),
		array('class' => 'submit200'));
	
	// set defaults
	$FORM->setDefaults(array(
		'id' => Base_Cnc::ifsetor($page['id'], null),
		'name' => Base_Cnc::ifsetor($page['name'], null),
		'name_url' => Base_Cnc::ifsetor($page['name_url'], null),
		'alternate_name' => Base_Cnc::ifsetor($page['alternate_name'], null),
		'description' => Base_Cnc::ifsetor($page['description'], null),
		'optional_text' => Base_Cnc::ifsetor($page['optional_text'], null),
		'template_set' => Base_Cnc::ifsetor($page['template_set'], null),
		'index_page' => Base_Cnc::ifsetor($page['index_page'], null),
		'protect' => Base_Cnc::ifsetor($page['protect'], null),
		'groups' => $selected_groups,
		'sitemap_changefreq' => Base_Cnc::ifsetor($page['sitemap_changefreq'], null),
		'sitemap_priority' => Base_Cnc::ifsetor($page['sitemap_priority'], null)
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
		
		// assign template set count
		$BASE->utility->smarty->assign('template_set_count', count($template_sets));
		
		// assign page
		$BASE->utility->smarty->assign('page', $page);
		
		// select available projects
		$select_params = array(
			'user' => WCOM_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('content/pages_edit.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// test url name for uniqueness
		//$url_name = $HELPER->createMeaningfulString($FORM->exportValue('name'));
		$name_url = $FORM->exportValue('name_url');
		if (!$PAGE->testForUniqueUrlName($name_url, $FORM->exportValue('id'))) {
			$name_url = $name_url.'-'.$FORM->exportValue('id');
		}
		
		// create the article group
		$sqlData = array();
		$sqlData['name'] = $FORM->exportValue('name');
		$sqlData['name_url'] = $name_url;
		$sqlData['alternate_name'] = $FORM->exportValue('alternate_name');
		$sqlData['description'] = $FORM->exportValue('description');
		$sqlData['optional_text'] = $FORM->exportValue('optional_text');
		$sqlData['template_set'] = $FORM->exportValue('template_set');
		$sqlData['index_page'] = $FORM->exportValue('index_page');
		$sqlData['protect'] = $FORM->exportValue('protect');
		$sqlData['sitemap_changefreq'] = $FORM->exportValue('sitemap_changefreq');
		$sqlData['sitemap_priority'] = $FORM->exportValue('sitemap_priority');
		
		// test sql data for pear errors
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$PAGE->updatePage($FORM->exportValue('id'), $sqlData);
			
			// map page to groups
			if (intval($FORM->exportValue('protect')) === 1) {
				$PAGE->mapPageToGroups($FORM->exportValue('id'), (array)$FORM->exportValue('groups'));
			} else {
				$PAGE->mapPageToGroups($FORM->exportValue('id'), array());
			}
			
			// look at the index page field
			if (intval($FORM->exportValue('index_page')) === 1) {
				$PAGE->setIndexPage($FORM->exportValue('id'));
			}
			
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
		header("Location: pages_edit.php?id=".$FORM->exportValue('id'));
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