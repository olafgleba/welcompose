<?php

/**
 * Project: Welcompose
 * File: validate.js.php
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
 * @copyright 2006 creatics media.systems
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

// define area constant
define('WCOM_CURRENT_AREA', 'ADMIN');

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
	if (Base_Cnc::filterRequest($_POST['elemID'], WCOM_REGEX_FORM_FIELD_ID)) {
		switch ((string)$_POST['elemID']) {
			case 'blog_comment_status_name':
					$reg = WCOM_REGEX_BLOG_COMMENT_STATUS_NAME;
					$desc = gettext('Only capitalized literal string');
				break;
			case 'generator_form_field_name':
					$reg = WCOM_REGEX_OPERATOR_NAME;
					$desc = gettext('Alphanumeric with underscores');
				break;
			case 'global_template_name':
					$reg = WCOM_REGEX_GLOBAL_TEMPLATE_NAME;
					$desc = gettext('Alphanumeric string with spaces');
				break;
			case 'group_name':
					$reg = WCOM_REGEX_GROUP_NAME;
					$desc = gettext('Only capitalized prefixed literal string');
				break;
			case 'navigation_name':
					$reg = WCOM_REGEX_NON_EMPTY;
					$desc = gettext('Field may not be empty');
				break;
			case 'page_sitemap_priority':
					$reg = WCOM_REGEX_SITEMAP_PRIORITY;
					$desc = gettext('Value between 0.1 and 1.0');
				break;
			case 'page_type_name':
					$reg = WCOM_REGEX_PAGE_TYPE_NAME;
					$desc = gettext('Only capitalized prefixed literal string');
				break;
			case 'page_type_internal_name':
					$reg = WCOM_REGEX_PAGE_TYPE_INTERNAL_NAME;
					$desc = gettext('Literal string, CamelCase');
				break;
			case 'blog_comment_homepage':
			case 'user_homepage':
			case 'ping_service_configuration_site_url':
			case 'ping_service_configuration_site_index':
			case 'ping_service_configuration_site_feed':
					$reg = WCOM_REGEX_URL;
					$desc = gettext('Full URL with protocol (http:// etc.)');
				break;
			case 'ping_service_host':
					$reg = WCOM_REGEX_PING_SERVICE_HOST;
					$desc = gettext('Alphanumeric with dots and hyphens');
				break;
			case 'ping_service_path':
					$reg = WCOM_REGEX_PING_SERVICE_PATH;
					$desc = gettext('Alphanumeric string with slashes');
				break;
			case 'ping_service_port':
					$reg = WCOM_REGEX_NUMERIC;
					$desc = gettext('Numbers only');
				break;
			case 'right_name':
					$reg = WCOM_REGEX_RIGHT_NAME;
					$desc = gettext('Only capitalized prefixed literal string');
				break;
			case 'template_set_name':
					$reg = WCOM_REGEX_TEMPLATE_SET_NAME;
					$desc = gettext('Alphanumeric literal string with dashes');
				break;
			case 'template_type_name':
					$reg = WCOM_REGEX_TEMPLATE_TYPE_NAME;
					$desc = gettext('Alphanumeric literal string with dashes');
				break;
			case 'text_converter_internal_name':
					$reg = WCOM_REGEX_TEXT_CONVERTER_INTERNAL_NAME;
					$desc = gettext('Alphanumeric literal string with dashes');
				break;
			case 'blog_comment_email':
			case 'generator_form_email_from':
			case 'generator_form_email_to':
			case 'simple_form_email_from':
			case 'simple_form_email_to':
			case 'user_email':
					$reg = WCOM_REGEX_EMAIL;
					$desc = gettext('Invalid e-mail address');
				break;
			case 'simple_form_title_url':
			case 'blog_posting_title_url':
			case 'page_name_url':
					$reg = WCOM_REGEX_URL_NAME;
					$desc = gettext('Invalid URL name');
				break;
			case 'user_password':
					$reg = WCOM_REGEX_PASSWORD;
					$desc = gettext('Five characters or more, no whitespace');
				break;
			case 'box_name':
					$reg = WCOM_REGEX_NON_EMPTY;
					$desc = gettext('Field may not be empty');
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
	$BASE->error->displayException($e, $BASE->utility->smarty);
	$BASE->error->triggerException($e);
	
	// exit
	exit;
}
?>