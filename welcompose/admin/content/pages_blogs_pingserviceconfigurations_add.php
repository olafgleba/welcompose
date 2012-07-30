<?php

/**
 * Project: Welcompose
 * File: pages_blogs_pingserviceconfigurations_add.php
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
	
	// load pingservice class
	/* @var $PINGSERVICE Application_Pingservice */
	$PINGSERVICE = load('application:pingservice');
	
	// load pingserviceconfiguration class
	/* @var $PINGSERVICECONFIGURATION Application_Pingserviceconfiguration */
	$PINGSERVICECONFIGURATION = load('application:pingserviceconfiguration');
	
	// load helper class
	/* @var $HELPER Utility_Helper */
	$HELPER = load('utility:helper');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Application', 'PingServiceConfiguration', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
	// assign current project values
	$_wcom_current_project = $PROJECT->selectProject(WCOM_CURRENT_PROJECT);
	$BASE->utility->smarty->assign('_wcom_current_project', $_wcom_current_project);
	
	// get page
	$page = $PAGE->selectPage(Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC));
	
	// prepare ping services
	$ping_services = array();
	foreach ($PINGSERVICE->selectPingServices() as $_ping_service) {
		$ping_services[(int)$_ping_service['id']] = htmlspecialchars($_ping_service['name']);
	}
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('ping_service_configuration');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');
	
	// hidden for page
	$page_id = $FORM->addElement('hidden', 'page', array('id' => 'page'));

	
	// select for ping service	
	$ping_service = $FORM->addElement('select', 'ping_service',
	 	array('id' => 'ping_service_configuration_ping_service'),
		array('label' => gettext('Ping service'), 'options' => $ping_services)
		);
	$ping_service->addRule('required', gettext('Select a ping service'));

	// textfield for site_name
	$site_name = $FORM->addElement('text', 'site_name', 
		array('id' => 'ping_service_configuration_site_name', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Site name'))
		);
	$site_name->addRule('required', gettext('Please enter a site name'));
	
	// textfield for site_url
	$site_url = $FORM->addElement('text', 'site_url', 
		array('id' => 'ping_service_configuration_site_url', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('Weblog URL'))
		);
	$site_url->addRule('required', gettext('Please enter a site URL'));
	$site_url->addRule('regex', gettext('Please enter a valid site URL'), WCOM_REGEX_URL);
	
	// textfield for site_index
	$site_index = $FORM->addElement('text', 'site_index', 
		array('id' => 'ping_service_configuration_site_index', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('Changes URL'))
		);
	$site_index->addRule('required', gettext('Please enter a home page URL'));
	$site_index->addRule('regex', gettext('Please enter a valid home page URL'), WCOM_REGEX_URL);
	
	// textfield for site_feed
	$site_feed = $FORM->addElement('text', 'site_feed', 
		array('id' => 'ping_service_configuration_site_feed', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('Feed URL'))
		);
	$site_feed->addRule('required', gettext('Please enter a feed URL'));
	$site_feed->addRule('regex', gettext('Please enter a valid feed URL'), WCOM_REGEX_URL);

	// submit button
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'submit200', 'value' => gettext('Save'))
		);
	
	// set defaults
	$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
		'page' => Base_Cnc::ifsetor($page['id'], null)
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
		
		// calculate and assign ping service count
		$BASE->utility->smarty->assign('ping_service_count', count($ping_services));
		
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
		$BASE->utility->smarty->display('content/pages_blogs_pingserviceconfigurations_add.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->toggleFrozen(true);
		
		// prepare sql data
		$sqlData = array();
		$sqlData['page'] = $page_id->getValue();
		$sqlData['ping_service'] = $ping_service->getValue();
		$sqlData['site_name'] = $site_name->getValue();
		$sqlData['site_url'] = $site_url->getValue();
		$sqlData['site_index'] = $site_index->getValue();
		$sqlData['site_feed'] = $site_feed->getValue();
		
		// test sql data for pear errors
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$PINGSERVICECONFIGURATION->addPingServiceConfiguration($sqlData);
			
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
		header("Location: pages_blogs_pingserviceconfigurations_add.php?page=".$page_id->getValue());
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