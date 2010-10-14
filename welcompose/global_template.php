<?php

/**
 * Project: Welcompose
 * File: index.php
 * 
 * Copyright (c) 2008 creatics
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id$
 * 
 * @copyright 2008 creatics, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

// define current area constant
define('WCOM_CURRENT_AREA', 'PUBLIC');

// get loader
$path_parts = array(
	dirname(__FILE__),
	'core',
	'loader.php'
);
$loader_path = implode(DIRECTORY_SEPARATOR, $path_parts);
require($loader_path);

// start base
/* @var $BASE base */
$BASE = load('base:base');

// deregister globals
$deregister_globals_path = dirname(__FILE__).'/core/includes/deregister_globals.inc.php';
require(Base_Compat::fixDirectorySeparator($deregister_globals_path));

try {
	// start output buffering
	@ob_start();
	
	// load smarty
	$smarty_public_conf = dirname(__FILE__).'/core/conf/smarty_public.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_public_conf), true);
	
	// start Base_Session
	$SESSION = load('Base:Session');
	
	// init project for public area
	$PROJECT = load('Application:Project');
	$PROJECT->initProjectPublicArea();
	
	// init user for public area
	$USER = load('User:User');
	$USER->initUserPublicArea();
	
	// load Templating_GlobalTemplate
	$GLOBALTEMPLATE = load('Templating:GlobalTemplate');
	
	// get global template from database because we need to 
	// change the header mime type and maybe the delimiter
	$template = $GLOBALTEMPLATE->smartyFetchGlobalTemplate(Base_Cnc::ifsetor($_REQUEST['name'], null));
	
	// set mime type
	header(sprintf("Content-Type: %s", (!empty($template['mime_type']) ? $template['mime_type'] : 'text/plain')));
	
	// change delimiter if required
	if (isset($template['change_delimiter']) && $template['change_delimiter']) {
		$BASE->utility->smarty->left_delimiter = '<%';
		$BASE->utility->smarty->right_delimiter = '%>';
	}

	// preparge the template name
	define("WCOM_TEMPLATE", sprintf("wcomgtpl:%s", Base_Cnc::ifsetor($_REQUEST['name'], null)).".".WCOM_CURRENT_PROJECT);
	
	// start gunzip compression
	if ($BASE->_conf['output']['gunzip'] == 1) {
		ob_start("ob_gzhandler");
	}
	
	// display page
	define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
	$BASE->utility->smarty->display(WCOM_TEMPLATE, WCOM_TEMPLATE_KEY);
	
	@ob_end_flush();
	exit;
} catch (Exception $e) {
	// clean buffer
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