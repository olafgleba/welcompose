<?php

/**
 * Project: Oak
 * File: pages_blogs_pingserviceconfigurations_select.php
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
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(OAK_CURRENT_USER);
	
	// make sure, that the page parameter is present
	if (is_null(Base_Cnc::filterRequest($_REQUEST['page'], OAK_REGEX_NUMERIC))) {
		header("Location: pages_select.php");
		exit;
	}
	
	// assign paths
	$BASE->utility->smarty->assign('oak_admin_root_www',
		$BASE->_conf['path']['oak_admin_root_www']);
	
	// assign current user and project id
	$BASE->utility->smarty->assign('oak_current_user', OAK_CURRENT_USER);
	$BASE->utility->smarty->assign('oak_current_project', OAK_CURRENT_PROJECT);
	
	// select available projects
	$select_params = array(
		'user' => OAK_CURRENT_USER,
		'order_macro' => 'NAME'
	);
	$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
	
	// get page
	$page = $PAGE->selectPage(Base_Cnc::filterRequest($_REQUEST['page'], OAK_REGEX_NUMERIC));
	$BASE->utility->smarty->assign('page', $page);
	
	// get ping service configurations
	$ping_service_configurations = $PINGSERVICECONFIGURATION->selectPingServiceConfigurations(array(
		'page' => $page['id'],
		'start' => Base_Cnc::filterRequest($_REQUEST['start'], OAK_REGEX_NUMERIC),
		'limit' => 20
	));
	$BASE->utility->smarty->assign('ping_service_configurations', $ping_service_configurations);
	
	// count available ping service configurations
	$select_params = array(
		'page' => $page['id']
	);
	$configuration_count = $PINGSERVICECONFIGURATION->countPingServiceConfigurations($select_params);
	$BASE->utility->smarty->assign('configuration_count', $configuration_count);
	
	// prepare and assign page index
	$BASE->utility->smarty->assign('page_index', $HELPER->calculatePageIndex($configuration_count, 20));
	
	// import and assign request params
	$request = array(
		'start' => Base_Cnc::filterRequest($_REQUEST['start'], OAK_REGEX_NUMERIC)
	);
	$BASE->utility->smarty->assign('request', $request);
	
	// display the page
	define("OAK_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
	$BASE->utility->smarty->display('content/pages_blogs_pingserviceconfigurations_select.html', OAK_TEMPLATE_KEY);
	
	// flush the buffer
	@ob_end_flush();
	exit;

} catch (Exception $e) {
	// clean buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	Base_Error::triggerException($BASE->utility->smarty, $e);	
	
	// exit
	exit;
}
?>