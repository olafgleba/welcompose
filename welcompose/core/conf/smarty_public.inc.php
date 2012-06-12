<?php

/**
 * Project: Welcompose
 * File: smarty_public.inc.php
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
 * @author Andreas Ahlenstorf, Olaf Gleba
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
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

// load the wcom resource plugin
require_once(SMARTY_DIR.'software_extensions/resource.wcom.php');
$smarty->registerResource("wcom", new Smarty_Resource_Wcom());

// load the wcomgtpl resource plugin
require_once(SMARTY_DIR.'software_extensions/resource.wcomgtpl.php');
$smarty->registerResource("wcomgtpl", new Smarty_Resource_Wcomgtpl());

// configure smarty
$smarty->muteExpectedErrors();
//$smarty->compile_check = false;

$smarty->setTemplateDir(SMARTY_TPL_DIR.'/templates');
$smarty->setCompileDir(SMARTY_TPL_DIR.'/compiled');
$smarty->setCacheDir(SMARTY_TPL_DIR.'/cache');
$smarty->setPluginsDir(array(
	SMARTY_DIR.'plugins',
	SMARTY_DIR.'software_plugins'
	)
);

?>
