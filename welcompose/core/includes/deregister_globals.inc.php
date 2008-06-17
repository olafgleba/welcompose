<?php

/**
 * Project: Welcompose
 * File: deregister_globals.inc.php
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