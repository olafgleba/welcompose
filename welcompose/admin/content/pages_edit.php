<?php

/**
 * Project: Welcompose
 * File: pages_edit.php
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
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
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
	$FORM = $BASE->utility->loadQuickForm('page');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');
	
	// hidden for id
	$id = $FORM->addElement('hidden', 'id', array('id' => 'id'));
	$id->addRule('required', gettext('Id is not expected to be empty'));
	$id->addRule('regex', gettext('Id is expected to be numeric'), WCOM_REGEX_NUMERIC);
	
	// textfield for name
	$name = $FORM->addElement('text', 'name', 
		array('id' => 'page_name', 'maxlength' => 255, 'class' => 'w300 urlify'),
		array('label' => gettext('Name'))
		);
	$name->addRule('required', gettext('Please enter a name'));
	
	// textfield for url_name		
	$name_url = $FORM->addElement('text', 'name_url', 
		array('id' => 'page_name_url', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('URL name'))
		);
	$name_url->addRule('required', gettext('Enter an URL name'));
	$name_url->addRule('regex', gettext('The URL name may only contain chars, numbers and hyphens'), WCOM_REGEX_URL_NAME);

	// select for type
	$template_set = $FORM->addElement('select', 'template_set',
	 	array('id' => 'page_template_set'),
		array('label' => gettext('Template set'), 'options' => $template_sets)
		);	
	$template_set->addRule('required', gettext('Please choose a template set'));
	
	// checkbox for index_page		
	$index_page = $FORM->addElement('checkbox', 'index_page',
		array('id' => 'page_index_page', 'class' => 'chbx'),
		array('label' => gettext('Index page'))
		);
	$index_page->addRule('regex', gettext('The field index_page accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
	
	// checkbox for protect
	$protect = $FORM->addElement('checkbox', 'protect',
		array('id' => 'page_protect', 'class' => 'chbx'),
		array('label' => gettext('Protect'))
		);
	$protect->addRule('regex', gettext('The field protect accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);	

	// checkbox for draft
	$draft = $FORM->addElement('checkbox', 'draft',
		array('id' => 'page_draft', 'class' => 'chbx'),
		array('label' => gettext('Draft'))
		);
	$draft->addRule('regex', gettext('The field draft accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);	

	// checkbox for exclude
	$exclude = $FORM->addElement('checkbox', 'exclude',
		array('id' => 'page_exclude', 'class' => 'chbx'),
		array('label' => gettext('Exclude'))
		);
	$exclude->addRule('regex', gettext('The field exclude accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);

	// checkbox for nofollow
	$no_follow = $FORM->addElement('checkbox', 'no_follow',
		array('id' => 'page_no_follow', 'class' => 'chbx'),
		array('label' => gettext('No Follow'))
		);
	$no_follow->addRule('regex', gettext('The field no_follow accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);	

	// multi select for rights
	$groups = $FORM->addElement('select', 'groups',
	 	array('id' => 'page_groups', 'class' => 'multisel', 'multiple' => 'multiple', 'size' => 10),
		array('label' => gettext('Groups'), 'options' => $groups)
		);
		
	// textfield for alternate name
	$alternate_name = $FORM->addElement('text', 'alternate_name', 
		array('id' => 'page_alternate_name', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('Alternate name'))
		);
	
	// textarea for description
	$description = $FORM->addElement('textarea', 'description', 
		array('id' => 'page_description', 'cols' => 3, 'rows' => 2, 'class' => 'w298h50'),
		array('label' => gettext('Description'))
		);
	
	// textarea for optional_text
	$optional_text = $FORM->addElement('textarea', 'optional_text', 
		array('id' => 'page_optional_text', 'cols' => 3, 'rows' => 2, 'class' => 'w298h50'),
		array('label' => gettext('Optional text'))
		);

	// select for sitemap change priority
	$sitemap_changefreq = $FORM->addElement('select', 'sitemap_changefreq',
	 	array('id' => 'page_sitemap_changefreq'),
		array('label' => gettext('Sitemap change frequency'), 'options' => $sitemap_change_frequencies)
		);
		
	// textfield sitemap priority value
	$sitemap_priority = $FORM->addElement('text', 'sitemap_priority', 
		array('id' => 'page_sitemap_priority', 'maxlength' => 3, 'class' => 'w300 validate'),
		array('label' => gettext('Sitemap Priority'))
		);
	$sitemap_priority->addRule('regex', gettext('Please enter a sitemap priority between 0.1 and 1.0'), WCOM_REGEX_SITEMAP_PRIORITY);
	
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
		'id' => Base_Cnc::ifsetor($page['id'], null),
		'name' => Base_Cnc::ifsetor($page['name'], null),
		'name_url' => Base_Cnc::ifsetor($page['name_url'], null),
		'alternate_name' => Base_Cnc::ifsetor($page['alternate_name'], null),
		'description' => Base_Cnc::ifsetor($page['description'], null),
		'optional_text' => Base_Cnc::ifsetor($page['optional_text'], null),
		'template_set' => Base_Cnc::ifsetor($page['template_set'], null),
		'index_page' => Base_Cnc::ifsetor($page['index_page'], null),
		'protect' => Base_Cnc::ifsetor($page['protect'], null),
		'draft' => Base_Cnc::ifsetor($page['draft'], null),
		'exclude' => Base_Cnc::ifsetor($page['exclude'], null),
		'no_follow' => Base_Cnc::ifsetor($page['no_follow'], null),
		'groups' => $selected_groups,
		'sitemap_changefreq' => Base_Cnc::ifsetor($page['sitemap_changefreq'], null),
		'sitemap_priority' => Base_Cnc::ifsetor($page['sitemap_priority'], null)
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
		$FORM->toggleFrozen(true);
		
		// test url name for uniqueness
		//$url_name = $HELPER->createMeaningfulString($name->getValue());
		$name_url = $name_url->getValue();
		if (!$PAGE->testForUniqueUrlName($name_url, $id->getValue())) {
			$name_url = $name_url.'-'.$id->getValue();
		}
		
		// create the article group
		$sqlData = array();
		$sqlData['name'] = $name->getValue();
		$sqlData['name_url'] = $name_url;
		$sqlData['alternate_name'] = $alternate_name->getValue();
		$sqlData['description'] = $description->getValue();
		$sqlData['optional_text'] = $optional_text->getValue();
		$sqlData['template_set'] = $template_set->getValue();
		$sqlData['index_page'] = $index_page->getValue();
		$sqlData['protect'] = $protect->getValue();
		$sqlData['draft'] = $draft->getValue();
		$sqlData['exclude'] = $exclude->getValue();
		$sqlData['no_follow'] = $no_follow->getValue();
		$sqlData['sitemap_changefreq'] = $sitemap_changefreq->getValue();
		$sqlData['sitemap_priority'] = $sitemap_priority->getValue();
			
		// test sql data for pear errors
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$PAGE->updatePage($id->getValue(), $sqlData);
			
			// map page to groups
			if (intval($protect->getValue()) === 1) {
				$PAGE->mapPageToGroups($id->getValue(), (array)$groups->getValue());
			} else {
				$PAGE->mapPageToGroups($id->getValue(), array());
			}
			
			// look at the index page field
			if (intval($index_page->getValue()) === 1) {
				$PAGE->setIndexPage($id->getValue());
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
		
		// redirect
		if (!empty($saveAndRemainOnPage)) {
			header("Location: pages_edit.php?id=".$id->getValue());
		} else {
			header("Location: pages_select.php");
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