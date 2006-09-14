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

function smarty_function_global_file ($params, &$smarty)
{
	// define some vars
	$id = null;
	
	// check input vars
	if (!is_array($params)) {
		throw new Exception("global_file: Functions params are not in an array");	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'id':
					$$_key = (int)$_value;
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
	$file = $GLOBALFILE->selectGlobalFile($id);
	
	// make sure that there's a file
	if (empty($file['name_on_disk'])) {
		throw new Exception("File not found");
	}
	
	// execute method and return requested data
	return sprintf("%s/%s", $GLOBALFILE->base->_conf['file']['store_www'],
		$file['name_on_disk']);
}

?>