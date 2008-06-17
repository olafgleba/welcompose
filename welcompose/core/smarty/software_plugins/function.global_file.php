<?php

/**
 * Project: Welcompose
 * File: function.global_file.php
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

function smarty_function_global_file ($params, &$smarty)
{
	// define some vars
	$name = null;
	
	// check input vars
	if (!is_array($params)) {
		throw new Exception("global_file: Functions params are not in an array");	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'name':
					$$_key = (string)$_value;
				break;
			default:
					throw new Exception("global_file: Unknown argument");
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
	
	// load global file class
	$GLOBALFILE = load('Templating:GlobalFile');
	
	// get global file
	$file = $GLOBALFILE->selectGlobalFileUsingName($name);
	
	// make sure that there's a file
	if (empty($file['name_on_disk'])) {
		throw new Exception("File not found");
	}
	
	// execute method and return requested data
	return sprintf("%s/%s", $GLOBALFILE->base->_conf['global_file']['store_www'],
		$file['name_on_disk']);
}

?>