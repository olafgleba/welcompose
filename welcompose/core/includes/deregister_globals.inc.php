<?php

/**
 * Project: Welcompose
 * File: deregister_globals.inc.php
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
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

function wcom_deregister_globals ()
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
wcom_deregister_globals();

?>