<?php

/**
 * Project: Oak
 * File: function.select_simple.class.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-2.1.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License 3.0
 */

function smarty_function_select_simple ($params, &$smarty)
{
	// define some vars
	$var = null;
	$ns = null;
	$class = null;
	$method = null;
	$select_params = array();
	
	// check input vars
	if (!is_array($params)) {
		throw new Exception("select_named: Functions params are not in an array");	
	}
	
	// separate function params from the rest
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'ns':
					$ns = (string)$_value;
				break;
			case 'var':
					$var = (string)$_value;
				break;
			case 'class':
					$class = (string)$_value;
				break;
			case 'method':
					$method = (string)$_value;
				break;
			default:
					$select_params[$_key] = $_value;
				break;
		}
	}
	
	// check input
	if (is_null($var) || !preg_match(OAK_REGEX_SMARTY_VAR_NAME, $var)) {
		throw new Exception("select_named: Invalid var name supplied");
	}
	if (is_null($ns) || !preg_match(OAK_REGEX_SMARTY_NS_NAME, $ns)) {
		throw new Exception("select_named: Invalid namespace name supplied");
	}
	if (is_null($class) || !preg_match(OAK_REGEX_SMARTY_CLASS_NAME, $class)) {
		throw new Exception("select_named: Invalid class name supplied");
	}
	if (is_null($method) || !preg_match(OAK_REGEX_SMARTY_METHOD_NAME, $method)) {
		throw new Exception("select_named: Invalid method name supplied");
	}
	
	// let's see if we can safely call the desired method
	if (!oak_smarty_select_whitelist($ns, $class, $method)) {
		throw new Exception("select_named: Function call did not pass the whitelist");	
	}
	
	// load class loader
	$path_parts = array(
		dirname(__FILE__),
		'..',
		'..',
		'loader.php'
	);
	require_once(implode(DIRECTORY_SEPARATOR, $path_parts));
	
	// load requested class
	$OBJECT = load($ns.':'.$class);
	
	// check if the requested method is callable
	if (!is_callable(array($OBJECT, $method))) {
		throw new Exception("select_simple: Requested method is not callable");	
	}
	
	// execute method and return requested data
	$smarty->assign($var, call_user_func_array(array($OBJECT, $method),
		$select_params));
}

?>