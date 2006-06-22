<?php

/**
 * Project: Oak
 * File: deregister_globals.inc.php
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

function oak_deregister_globals ()
{
	// if register_globals is disabled, there's
	// nothing to do
	if (!ini_get('register_globals')) {
		return true;
	}
	
	// if $_REQUEST['GLOBALS'] is set, someone
	// tries to overwrite $GLOBALS
	if (isset($_REQUEST['GLOBALS'])) {
		unset($_REQUEST['GLOBALS']);	
	}
	
	// Super globals that shouldn't be deregistred
	$protected_globals = array(
		'GLOBALS',
		'_GET',
		'_POST',
		'_COOKIE',
		'_REQUEST',
		'_SERVER',
		'_ENV',
		'_FILES',
		'_SESSION'
	);
	
	// Input
	$input = array_merge(
		$_GET,
		$_POST,
		$_COOKIE,
		$_SERVER,
		$_ENV,
		$_FILES,
		(isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array())
	);
	
	// deregister globals
	foreach ($input as $_key => $_value) {
		if (!in_array($_key, $protected_globals) && isset($GLOBALS[$_key])) {
			unset($GLOBALS[$_key]);
		}
	}
}

// call the function
oak_deregister_globals();

?>