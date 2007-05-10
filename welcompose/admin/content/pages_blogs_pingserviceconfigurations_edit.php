<?php

/**
 * Project: Welcompose
 * File: pages_blogs_pingserviceconfigurations_edit.php
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
	
	// get page
	$page = $PAGE->selectPage(Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC));
	
	// get ping service configuration
	$ping_service_configuration = $PINGSERVICECONFIGURATION->selectPingServiceConfiguration(
		Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
	
	// prepare ping services
	$ping_services = array();
	foreach ($PINGSERVICE->selectPingServices() as $_ping_service) {
		$ping_services[(int)$_ping_service['id']] = htmlspecialchars($_ping_service['name']);
	}
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('ping_service_configuration', 'post');
	
	// hidden for page
	$FORM->addElement('hidden', 'page');
	$FORM->applyFilter('page', 'trim');
	$FORM->applyFilter('page', 'strip_tags');
	$FORM->addRule('page', gettext('Page is not expected to be empty'), 'required');
	$FORM->addRule('page', gettext('Page is expected to be numeric'), 'numeric');
	
	// hidden for id
	$FORM->addElement('hidden', 'id');
	$FORM->applyFilter('id', 'trim');
	$FORM->applyFilter('id', 'strip_tags');
	$FORM->addRule('id', gettext('Id is not expected to be empty'), 'required');
	$FORM->addRule('id', gettext('Id is expected to be numeric'), 'numeric');
	
	// select for ping service
	$FORM->addElement('select', 'ping_service', gettext('Ping service'), $ping_services,
		array('id' => 'ping_service_configuration_ping_service'));
	$FORM->applyFilter('ping_service', 'trim');
	$FORM->applyFilter('ping_service', 'strip_tags');
	$FORM->addRule('ping_service', gettext('Select a ping service'), 'required');
	$FORM->addRule('ping_service', gettext('Chosen ping service is out of range'),
		'in_array_keys', $ping_services);
	
	// textfield for site_name
	$FORM->addElement('text', 'site_name', gettext('Site name'),
		array('id' => 'ping_service_configuration_site_name', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('site_name', 'trim');
	$FORM->applyFilter('site_name', 'strip_tags');
	$FORM->addRule('site_name', gettext('Please enter a site name'), 'required');
	
	// textfield for site_url
	$FORM->addElement('text', 'site_url', gettext('Weblog URL'),
		array('id' => 'ping_service_configuration_site_url', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('site_url', 'trim');
	$FORM->applyFilter('site_url', 'strip_tags');
	$FORM->addRule('site_url', gettext('Please enter a site URL'), 'required');
	$FORM->addRule('site_url', gettext('Please enter a valid site URL'), 'regex', WCOM_REGEX_URL);
	
	// textfield for site_index
	$FORM->addElement('text', 'site_index', gettext('Changes URL'),
		array('id' => 'ping_service_configuration_site_index', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('site_index', 'trim');
	$FORM->applyFilter('site_index', 'strip_tags');
	$FORM->addRule('site_index', gettext('Please enter a home page URL'), 'required');
	$FORM->addRule('site_index', gettext('Please enter a valid home page URL'), 'regex', WCOM_REGEX_URL);
	
	// textfield for site_feed
	$FORM->addElement('text', 'site_feed', gettext('Feed URL'),
		array('id' => 'ping_service_configuration_site_feed', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('site_feed', 'trim');
	$FORM->applyFilter('site_feed', 'strip_tags');
	$FORM->addRule('site_feed', gettext('Please enter a feed URL'), 'required');
	$FORM->addRule('site_feed', gettext('Please enter a valid feed URL'), 'regex', WCOM_REGEX_URL);
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Save edit'),
		array('class' => 'submit200'));
	
	// set defaults
	$FORM->setDefaults(array(
		'page' => Base_Cnc::ifsetor($ping_service_configuration['page'], null),
		'id' => Base_Cnc::ifsetor($ping_service_configuration['id'], null),
		'ping_service' => Base_Cnc::ifsetor($ping_service_configuration['ping_service'], null),
		'site_name' => Base_Cnc::ifsetor($ping_service_configuration['site_name'], null),
		'site_url' => Base_Cnc::ifsetor($ping_service_configuration['site_url'], null),
		'site_index' => Base_Cnc::ifsetor($ping_service_configuration['site_index'], null),
		'site_feed' => Base_Cnc::ifsetor($ping_service_configuration['site_feed'], null)
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
		$BASE->utility->smarty->display('content/pages_blogs_pingserviceconfigurations_edit.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// prepare sql data
		$sqlData = array();
		$sqlData['ping_service'] = $FORM->exportValue('ping_service');
		$sqlData['site_name'] = $FORM->exportValue('site_name');
		$sqlData['site_url'] = $FORM->exportValue('site_url');
		$sqlData['site_index'] = $FORM->exportValue('site_index');
		$sqlData['site_feed'] = $FORM->exportValue('site_feed');
		
		// test sql data for pear errors
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$PINGSERVICECONFIGURATION->updatePingServiceConfiguration($FORM->exportValue('id'),
				$sqlData);
			
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
		header("Location: pages_blogs_pingserviceconfigurations_select.php?page=".$FORM->exportValue('page'));
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