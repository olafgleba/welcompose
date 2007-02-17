<?php

/**
 * Project: Welcompose
 * File: requirements.php
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

// get loader
$path_parts = array(
	dirname(__FILE__),
	'..',
	'core',
	'loader.php'
);
$loader_path = implode(DIRECTORY_SEPARATOR, $path_parts);
require($loader_path);

// start base
/* @var $BASE base */
$BASE = load('base:base');

// deregister globals
$deregister_globals_path = dirname(__FILE__).'/../core/includes/deregister_globals.inc.php';
require(Base_Compat::fixDirectorySeparator($deregister_globals_path));

try {
	// start output buffering
	@ob_start();
	
	// load smarty
	$smarty_update_conf = dirname(__FILE__).'/smarty.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_update_conf), true);
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../core/includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);
	
	// start Base_Session
	/* @var $SESSION session */
	$SESSION = load('base:session');
	
	// let's see if the user passed step one
	if (empty($_SESSION['update']['license_confirm_license']) || !$_SESSION['update']['license_confirm_license']) {
		header("Location: license.php");
		exit;
	}
	
	// initialize error counter
	$error_counter = 0;
	
	// prepare array of required extensions
	$extensions = array(
		'gettext',
		'pdo',
		'pdo_mysql',
		'gd',
		'xml',
		'simplexml',
		'dom',
		'session',
		'pcre'
	);
	sort($extensions);
	
	// let's see if all required extensions are loaded
	$extension_statuses = array();
	foreach ($extensions as $_extension) {
		if (extension_loaded($_extension)) {
			$extension_statuses[$_extension] = array(
				'text' => gettext('OK'),
				'marker' => 'fine'
			);
		} else {
			$extension_statuses[$_extension] = array(
				'text' => gettext('Not installed'),
				'marker' => 'error'
			);
			
			// increment error counter
			$error_counter++;
		}
	}
	
	// let's see if up-to-date software is available
	$software_statuses = array();
	
	// php versions
	if (version_compare(phpversion(), '5.0.3', '<')) {
		$software_statuses['PHP '.phpversion()] = array(
			'text' => gettext('Too old'),
			'marker' => 'error'
		);
		
		// increment error counter
		$error_counter++;
	} elseif (version_compare(phpversion(), '5.0.3', '>=') && version_compare(phpversion(), '5.1.3', '<')) {
		$software_statuses['PHP '.phpversion()] = array(
			'text' => gettext('May cause troubles'),
			'marker' => 'warning'
		);
	} elseif (version_compare(phpversion(), '5.1.3', '>=')) {
		$software_statuses['PHP '.phpversion()] = array(
			'text' => gettext('OK'),
			'marker' => 'fine'
		);
	}
	
	// gd versions
	$gd_info = gd_info();
	if (!preg_match("=2\.[0-9]+=", $gd_info['GD Version'])) {
		$software_statuses['GD Version '.$gd_info['GD Version']] = array(
			'text' => gettext('Too old'),
			'marker' => 'error'
		);
		
		// increment error counter
		$error_counter++;
	} elseif (!preg_match("=bundled=i", $gd_info['GD Version'])) {
		$software_statuses['GD Version '.$gd_info['GD Version']] = array(
			'text' => gettext('May cause troubles, use the bundled one'),
			'marker' => 'warning'
		);
	} else {
		$software_statuses['GD Version '.$gd_info['GD Version']] = array(
			'text' => gettext('OK'),
			'marker' => 'fine'
		);
	}
	
	// pdo versions
	if (!defined("PDO::ATTR_EMULATE_PREPARES")) {
		$software_statuses['pdo'] = array(
			'text' => gettext('Update to PDO 1.0.3 and pdo_mysql 1.0.2; or install PHP 5.1.3 or higher'),
			'marker' => 'error'
		);
		
		// increment error counter
		$error_counter++;
	} else {
		$software_statuses['pdo'] = array(
			'text' => gettext('OK'),
			'marker' => 'fine'
		);
	}
	
	// assign extension & software statuses, error counter
	$BASE->utility->smarty->assign('extension_statuses', $extension_statuses);
	$BASE->utility->smarty->assign('software_statuses', $software_statuses);
	$BASE->utility->smarty->assign('error_counter', $error_counter);
	
	// display the form
	define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
	$BASE->utility->smarty->display('requirements.html', WCOM_TEMPLATE_KEY);
	
	// flush the buffer
	@ob_end_flush();
	
	exit;
} catch (Exception $e) {
	// clean the buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	$BASE->error->displayException($e, $BASE->utility->smarty);
	$BASE->error->triggerException($e);
	
	// exit
	exit;
}

?>