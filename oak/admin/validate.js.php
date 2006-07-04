<?php

/**
 * Project: Oak
 * File: validate.js.php
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
 * @copyright 2006 creatics media.systems
 * @author Olaf Gleba
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
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
	$smarty_admin_conf = dirname(__FILE__).'/../core/conf/smarty_admin.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_admin_conf), true);
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../core/includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);
	
	// map field id names to regexps and error messages 
	if (Base_Cnc::filterRequest($_POST['elemID'], OAK_REGEX_FORM_FIELD_ID)) {
		switch ((string)$_POST['elemID']) {
			case 'group_name':
					$reg = OAK_REGEX_GROUP_NAME;
					$desc = gettext('Only capitalized prefixed literal string');
				break;
			case 'navigation_name':
					$reg = OAK_REGEX_NON_EMPTY;
					$desc = gettext('Field may not be empty');
				break;
			case 'page_type_name':
					$reg = OAK_REGEX_PAGE_TYPE_NAME;
					$desc = gettext('Only capitalized prefixed literal string');
				break;
			case 'ping_service_host':
					$reg = OAK_REGEX_PING_SERVICE_HOST;
					$desc = gettext('Alphanumeric with dots and hyphens');
				break;
			case 'ping_service_path':
					$reg = OAK_REGEX_PING_SERVICE_PATH;
					$desc = gettext('Alphanumeric string with slashes');
				break;
			case 'ping_service_port':
					$reg = OAK_REGEX_NUMERIC;
					$desc = gettext('Numbers only');
				break;
			case 'right_name':
					$reg = OAK_REGEX_RIGHT_NAME;
					$desc = gettext('Only capitalized prefixed literal string');
				break;
			case 'template_set_name':
					$reg = OAK_REGEX_TEMPLATE_SET_NAME;
					$desc = gettext('Alphanumeric literal string with dashes');
				break;
			case 'template_type_name':
					$reg = OAK_REGEX_TEMPLATE_TYPE_NAME;
					$desc = gettext('Alphanumeric literal string with dashes');
				break;
			case 'user_email':
					$reg = OAK_REGEX_EMAIL;
					$desc = gettext('Invalid e-mail address');
				break;
			case 'user_password':
					$reg = OAK_REGEX_PASSWORD;
					$desc = gettext('Five characters or more, no whitespace');
				break;
			default :
				$reg = null;
				$desc = null;
		}	
	}
	
	if (!empty($_POST['elemVal'])) {
		if (!empty($reg)) {
			if (Base_Cnc::filterRequest($_POST['elemVal'], $reg)) {
				print '<img src="../static/img/icons/success.gif" />';
			} else {
				print '<img src="../static/img/icons/error.gif" /> '.$desc;
			}
		} else {
			print '<img src="../static/img/icons/success.gif" />';
		}
	} else {
		// print non-breaking space
		// safari doesn't recognized void properly
		print '&nbsp;';
	}
	
	
		
	// flush the buffer
	@ob_end_flush();
	exit;

} catch (Exception $e) {
	// clean buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	Base_Error::triggerException($BASE->utility->smarty, $e);	
	
	// exit
	exit;
}
?>