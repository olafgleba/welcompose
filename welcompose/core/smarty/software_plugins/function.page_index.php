<?php

/**
 * Project: Welcompose
 * File: function.page_index.php
 * 
 * Copyright (c) 2008 creatics media.systems
 * 
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-3.0.php
 * 
 * $Id$
 * 
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
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
	if (is_null($var) || !preg_match(WCOM_REGEX_SMARTY_VAR_NAME, $var)) {
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