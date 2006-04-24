<?php

/**
 * Project: Oak
 * File: index.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/apache2.0.php Apache License, Version 2.0
 */

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
	
	// enable/disable caching
	$BASE->utility->smarty->caching = (int)$BASE->_conf['caching']['index.php_mode'];
	$BASE->utility->smarty->cache_lifetime = (int)$BASE->_conf['caching']['index.php_lifetime'];
	
	// get page information
	$PAGE = load('content:page');
	$page_information = $PAGE->selectIndexPage();
	
	// define constant CURRENT_PAGE
	define(OAK_CURRENT_PAGE, $page_information['id']);
	
	// inject page id to mimic behaviour of a normal page
	$_GET['page'] = $_POST['page'] = $_REQUEST['page'] = OAK_CURRENT_PAGE;
	
	// import url params
	$import_globals_path = dirname(__FILE__).'/import_globals.inc.php';
	require(Base_Compat::fixDirectorySeparator($import_globals_path));
	
	// prepare template name
	switch ((string)$page_information['page_type_name']) {
		case 'weblog':
				define("OAK_TEMPLATE_NAME", 'weblog_index');
			break;
		case 'simple_page':
				define("OAK_TEMPLATE_NAME", 'simple_page_index');
			break;
	}
	define("OAK_TEMPLATE", sprintf("dom:%s.%u", OAK_TEMPLATE_NAME, OAK_CURRENT_PAGE));
	
	// display page
	define("OAK_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
	$BASE->utility->smarty->display(OAK_TEMPLATE_NAME, OAK_TEMPLATE_KEY);
	
	@ob_end_flush();
	exit;
} catch (Exception $e) {
	// clean buffer
	@ob_end_clean();
	
	// raise error
	Base_Error::triggerException($BASE->utility->smarty, $e);	
	
	// exit
	exit;
}

?>