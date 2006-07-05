<?php

/**
 * Project: Oak
 * File: templates_select.php
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
	
	// load helper class
	/* @var $HELPER Utility_Helper */
	$HELPER = load('utility:helper');
	
	// init user and project
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(OAK_CURRENT_USER);
	
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
	
	// get available templates
	$select_params = array(
		'set' => Base_Cnc::filterRequest($_REQUEST['set'], OAK_REGEX_NUMERIC),
		'type' => Base_Cnc::filterRequest($_REQUEST['type'], OAK_REGEX_NUMERIC),
		'start' => Base_Cnc::filterRequest($_REQUEST['start'], OAK_REGEX_NUMERIC),
		'limit' => 20
	);
	$BASE->utility->smarty->assign('templates', $TEMPLATE->selectTemplates($select_params));
	
	// count available templates
	$select_params = array(
		'set' => Base_Cnc::filterRequest($_REQUEST['set'], OAK_REGEX_NUMERIC),
		'type' => Base_Cnc::filterRequest($_REQUEST['type'], OAK_REGEX_NUMERIC)
	);
	$template_count = $TEMPLATE->countTemplates($select_params);
	$BASE->utility->smarty->assign('template_count', $template_count);
	
	// prepare and assign page index
	$BASE->utility->smarty->assign('page_index', $HELPER->calculatePageIndex($template_count, 20));
	
	// get available template types
	$BASE->utility->smarty->assign('template_types', $TEMPLATETYPE->selectTemplateTypes());
	
	// get available template sets
	$BASE->utility->smarty->assign('template_sets', $TEMPLATESET->selectTemplateSets());
	
	// import and assign request params
	$request = array(
		'set' => Base_Cnc::filterRequest($_REQUEST['set'], OAK_REGEX_NUMERIC),
		'type' => Base_Cnc::filterRequest($_REQUEST['type'], OAK_REGEX_NUMERIC),
		'start' => Base_Cnc::filterRequest($_REQUEST['start'], OAK_REGEX_NUMERIC)
	);
	$BASE->utility->smarty->assign('request', $request);
	
	// display the template
	define("OAK_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
	$BASE->utility->smarty->display('templating/templates_select.html', OAK_TEMPLATE_KEY);
		
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