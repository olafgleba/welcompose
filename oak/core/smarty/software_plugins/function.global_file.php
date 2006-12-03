<?php

/**
 * Project: Oak
 * File: function.global_file.php
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
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
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