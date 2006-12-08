<?php

/**
 * Project: Welcompose
 * File: smarty_public.inc.php
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

/**
 * Whitelist for select plugins. Prevents undesired execution of
 * internal functions/methods. Takes the namespace name as first
 * argument, the class name as second argument and the method name
 * as third argument. These will be tested against the whitelist
 * patterns defined within the function. Returns true if the
 * argument combination passed the whitelist.
 * 
 * @param string Namespace name
 * @param string Class name
 * @param string Method name
 * @param bool 
 */
function wcom_smarty_select_whitelist ($ns, $class, $method)
{
	// configure white list
	$whitelist = array(
		array(
			'namespaces' => '=^(.*)$=',
			'classes' => '=^(.*)$=',
			'methods' => '=^(select|count)([a-z]+)$=i'
		)
	);
	
	foreach ($whitelist as $_entry) {
		if (preg_match($_entry['namespaces'], $ns) && preg_match($_entry['classes'], $class)
		&& preg_match($_entry['methods'], $method)) {
			return true;
		}
	}
	
	return false;
}

// define constants
if (!defined('SMARTY_DIR')) {
	define('SMARTY_DIR', dirname(__FILE__).'/smarty/');
}
if (!defined('SMARTY_TPL_DIR')) {
	define('SMARTY_TPL_DIR', realpath(dirname(__FILE__).'/../../smarty/'));
}

// load the wcom resource plugins
require_once(SMARTY_DIR.'software_extensions/resource.wcom.php');
$resource_functions = array(
	"oakresource_FetchTemplate",
	"oakresource_FetchTimestamp",
	"oakresource_isSecure",
	"oakresource_isTrusted"
);
$smarty->register_resource("wcom", $resource_functions);
unset($resource_functions);

require_once(SMARTY_DIR.'software_extensions/resource.wcomgtpl.php');
$resource_functions = array(
	"wcomgtplresource_FetchTemplate",
	"wcomgtplresource_FetchTimestamp",
	"wcomgtplresource_isSecure",
	"wcomgtplresource_isTrusted"
);
$smarty->register_resource("wcomgtpl", $resource_functions);
unset($resource_functions);

// configure smarty
$smarty->template_dir = SMARTY_TPL_DIR.'/templates';
$smarty->compile_dir = SMARTY_TPL_DIR.'/compiled';
$smarty->cache_dir = SMARTY_TPL_DIR.'/cache';
$smarty->plugins_dir = array(
	SMARTY_DIR.'my_plugins',
	SMARTY_DIR.'plugins',
	SMARTY_DIR.'software_plugins'
);

?>
