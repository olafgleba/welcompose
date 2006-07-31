<?php

/**
 * Project: Oak
 * File: function.select_named.class.php
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

function smarty_function_select_named ($params, &$smarty)
{
	// define some vars
	$var = null;
	$ns = null;
	$class = null;
	$method = null;
	$select_params = array();
	
	// load class loader
	$path_parts = array(
		dirname(__FILE__),
		'..',
		'..',
		'loader.php'
	);
	require_once(implode(DIRECTORY_SEPARATOR, $path_parts));
	
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

	// load requested class
	$OBJECT = load($ns.':'.$class);
	
	// check if the requested method is callable
	if (!is_callable(array($OBJECT, $method))) {
		throw new Exception("select_named: Requested method is not callable");	
	}
	
	// execute method and return requested data
	$smarty->assign($var, call_user_func(array($OBJECT, $method),
		$select_params));
}

?>