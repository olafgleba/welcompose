<?php

/**
 * Project: Welcompose
 * File: function.global_template.php
 * 
 * Copyright (c) 2008 creatics media.systems
 * 
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id$
 * 
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
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
		WCOM_CURRENT_PROJECT,
		WCOM_CURRENT_PROJECT_NAME,
		$name
	);
	ksort($replacements);
	
	return str_replace($patterns, $replacements, $url);
}

?>