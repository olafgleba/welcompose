<?php

/**
 * Project: Oak
 * File: function.page_index.php
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

function smarty_function_page_index ($params, &$smarty)
{
	// define some vars
	$var = null;
	$item_count = null;
	$interval = null;
	
	// check input vars
	if (!is_array($params)) {
		throw new Exception("page_index: Functions params are not in an array");	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'var':
					$$_key = (string)$_value;
				break;
			case 'item_count':
			case 'interval':
					$$_key = (int)$_value;
				break;
			default:
					throw new Exception("page_index: Unknown argument");
				break;
		}
	}
	
	// check input
	if (is_null($var) || !preg_match(OAK_REGEX_SMARTY_VAR_NAME, $var)) {
		throw new Exception("page_index: Invalid var name supplied");
	}
	
	// load class loader
	$path_parts = array(
		dirname(__FILE__),
		'..',
		'..',
		'loader.php'
	);
	require_once(implode(DIRECTORY_SEPARATOR, $path_parts));
	
	// load Utility_Helper class
	$HELPER = load('Utility:Helper');
		
	// execute method and return requested data
	$smarty->assign($var, $HELPER->calculatePageIndex($item_count, $interval));
}

?>