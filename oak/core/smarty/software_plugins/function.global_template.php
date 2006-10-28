<?php

/**
 * Project: Oak
 * File: function.global_template.php
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

function smarty_function_global_template ($params, &$smarty)
{
	// define some vars
	$name = null;
	
	// check input vars
	if (!is_array($params)) {
		throw new Exception("global_template: Functions params are not in an array");	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'name':
					$$_key = (string)$_value;
				break;
			default:
					throw new Exception("global_template: Unknown argument");
				break;
		}
	}
	
	// load class loader
	$path_parts = array(
		dirname(__FILE__),
		'..',
		'..',
		'loader.php'
	);
	require_once(implode(DIRECTORY_SEPARATOR, $path_parts));
	
	// load base instance
	$BASE = load('Base:Base');
	
	// get url
	$url = $BASE->_conf['urls']['global_template_url'];
	
	// prepare patterns
	$patterns = array(
		'<project_id>',
		'<project_name>',
		'<global_file_name>'
	);
	ksort($patterns);
	
	// prepare replacements
	$replacements = array(
		OAK_CURRENT_PROJECT,
		OAK_CURRENT_PROJECT_NAME,
		$name
	);
	ksort($replacements);
	
	return str_replace($patterns, $replacements, $url);
}

?>