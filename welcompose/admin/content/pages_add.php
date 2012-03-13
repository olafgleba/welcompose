<?php

/**
 * Project: Welcompose
 * File: pages_add.php
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
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Content', 'Page', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
	// prepare positions
	$positions = array(
		UTILITY_NESTEDSET_CREATE_BEFORE => gettext('Create node above the reference page'),
		UTILITY_NESTEDSET_CREATE_AFTER => gettext('Create node below the reference page')
	);
	
	// prepare page types
	$types = array();
	foreach ($PAGETYPE->selectPageTypes() as $_type) {
		$types[(int)$_type['id']] = htmlspecialchars($_type['name']);
	}
	
	// prepare navigations
	$navigations = array();
	foreach ($NAVIGATION->selectNavigations() as $_navigation) {
		$navigations[(int)$_navigation['id']] = htmlspecialchars($_navigation['name']);
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
	
	// hidden for navigation
	$navigation = $FORM->addElement('hidden', 'navigation', array('id' => 'navigation'));
	
	// hidden for reference
	$reference = $FORM->addElement('hidden', 'reference', array('id' => 'reference'));
	
	// select for navigations
	$navigations = $FORM->addElement('select', 'navigations',
	 	array('id' => 'page_navigations', 'onchange' => 'Helper_getNavigationPages(this);'),
		array('label' => gettext('Within Navigation'), 'options' => $navigations)
		);
	$navigations->addRule('required', gettext('Please choose a navigation'));
		
	// select for reference pages
	$pages = $FORM->addElement('select', 'pages',
	 	array('id' => 'page_pages', 'onchange' => 'Helper_setPageToReference(this);'),
		array('label' => gettext('Reference page'))
		);
	$pages->addRule('regex', gettext('Input for reference page is expected to be numeric'), WCOM_REGEX_NUMERIC);	

	// select for position
	$position = $FORM->addElement('select', 'position',
	 	array('id' => 'page_position'),
		array('label' => gettext('Position'), 'options' => $positions)
		);
	$position->addRule('required', gettext('Please choose position'));
		
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
	$type = $FORM->addElement('select', 'type',
	 	array('id' => 'page_type', 'onchange' => 'Helper_getRelatedPages(this);'),
		array('label' => gettext('Type'), 'options' => $types)
		);
	$type->addRule('required', gettext('Please choose a page type'));

	// checkbox for apply foreign content
	$apply_content = $FORM->addElement('checkbox', 'apply_content',
		array('id' => 'page_apply_content', 'class' => 'chbx'),
		array('label' => gettext('Apply content'))
		);
	$apply_content->addRule('regex', gettext('The field whether to apply foreign content accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
			
	// select to apply foreign contents		
	$apply_content_selection = $FORM->addElement('select', 'apply_content_selection',
	 	array('id' => 'page_apply_content_selection'),
		array('label' => gettext('Choose page'), 'options' => array('' => gettext('There is no content available for this page type')))
		);

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

	// submit button
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'submit200', 'value' => gettext('Save'))
		);
	
	// set defaults
	$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
		'navigation' => Base_Cnc::filterRequest($_REQUEST['navigation'], WCOM_REGEX_NUMERIC),
		'reference' => Base_Cnc::filterRequest($_REQUEST['reference'], WCOM_REGEX_NUMERIC),
		'position' => UTILITY_NESTEDSET_CREATE_AFTER,
		'sitemap_changefreq' => 'monthly',
		'sitemap_priority' => '0.5'
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

		$select_params = array(
			'navigation' => (int)$_REQUEST['navigation'],
			'draft' => 1,
			'exclude' => 1
		);
					
		$page_arrays = $PAGE->selectPages($select_params);
		$BASE->utility->smarty->assign('page_arrays', $page_arrays);
		
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
		
		// assign page type and template set counts
		$BASE->utility->smarty->assign('page_type_count', count($types));
		$BASE->utility->smarty->assign('template_set_count', count($template_sets));
		
		// select available projects
		$select_params = array(
			'user' => WCOM_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('content/pages_add.html', WCOM_TEMPLATE_KEY);
		
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
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();	
								
			// create node
			$page_id = $NESTEDSET->createNode($navigation->getValue(), $reference->getValue(),
				$position->getValue());
			
			// test url name for uniqueness
			//$url_name = $HELPER->createMeaningfulString($name->getValue());
			$name_url = $name_url->getValue();
			if (!$PAGE->testForUniqueUrlName($name_url)) {
				$name_url = $name_url.'-'.$page_id;
			}
			
			// prepare sql data for page create
			$sqlData = array();
			$sqlData['id'] = $page_id;
			$sqlData['project'] = WCOM_CURRENT_PROJECT;
			$sqlData['template_set'] = $template_set->getValue();
			$sqlData['name'] = $name->getValue();
			$sqlData['name_url'] = $name_url;
			$sqlData['type'] = $type->getValue();
			$sqlData['index_page'] = $index_page->getValue();
			$sqlData['protect'] = $protect->getValue();
			$sqlData['draft'] = $draft->getValue();
			$sqlData['exclude'] = $exclude->getValue();
			$sqlData['no_follow'] = $no_follow->getValue();
			$sqlData['alternate_name'] = $alternate_name->getValue();
			$sqlData['description'] = $description->getValue();
			$sqlData['optional_text'] = $optional_text->getValue();
			$sqlData['sitemap_changefreq'] = $sitemap_changefreq->getValue();
			$sqlData['sitemap_priority'] = $sitemap_priority->getValue();
			
			$HELPER->testSqlDataForPearErrors($sqlData);		
			
			// execute operation
			$PAGE->addPage($sqlData);
			
			// map page to groups
			if (intval($protect->getValue()) === 1) {
				$PAGE->mapPageToGroups($page_id, (array)$groups->getValue());
			}
			
			// look at the index page field
			if (intval($index_page->getValue()) === 1) {
				$PAGE->setIndexPage($page_id);
			}
			
			// use former applied page content to populate the new page and corresponding boxes
			if (intval($apply_content->getValue()) === 1) {
			
				// get id of page to apply
				$page_id_to_apply = Base_Cnc::filterRequest($_REQUEST['apply_content_selection'], WCOM_REGEX_NUMERIC);
				
				// init page contents and apply page contents
				$PAGE->initPageContents($page_id, $page_id_to_apply);
				
				// apply page boxes to the new page if available
				$PAGE->applyPageBoxes($page_id, $page_id_to_apply);
				
			} else {
				
				// if not set, just plain init the new page
				$PAGE->initPageContents($page_id);
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
		header("Location: pages_select.php");
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