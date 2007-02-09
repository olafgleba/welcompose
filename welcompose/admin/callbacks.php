<?php

/**
 * Project: Welcompose
 * File: callbacks.php
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
 * $Id: callbacks_insert_document.php 873 2007-02-04 14:25:02Z olaf $
 *
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
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

// admin_navigation
$admin_navigation_path = dirname(__FILE__).'/../core/includes/admin_navigation.inc.php';
require(Base_Compat::fixDirectorySeparator($admin_navigation_path));

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
	
	// start Base_Session
	$SESSION = load('Base:Session');
	
	// load User_User
	$USER = load('User:User');
	
	// load User_Login
	$LOGIN = load('User:Login');
	
	// load Application_Project
	$PROJECT = load('Application:Project');
	
	// load Application_TextConverter
	$TEXTCONVERTER = load('Application:TextConverter');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	
	// preparation
	// set insert_type var
	$insert_type = Base_Cnc::filterRequest($_REQUEST['insert_type'], WCOM_REGEX_CALLBACK_STRING);
	
	// differ href handling
	if ($insert_type == 'InternalLink') {
		$_text = (!empty($_REQUEST['text'])) ? $_REQUEST['text'] : gettext('Your link description');
	}
	elseif ($insert_type == 'InternalReference') {
		$_text = (!empty($_REQUEST['text'])) ? $_REQUEST['text'] : '';
	}
	
	// process callbacks	
	if ($_REQUEST['type'] == 'page') {	
		// load page class
		$PAGE = load('content:page');
	
		// get object(s)
		$object = $PAGE->selectPage(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
	
		// prepare callback args
		$args = array(
			'text' => $_text,
			'href' => sprintf('{get_url id="%u"}', $object['id'])
		);
	
	} elseif ($_REQUEST['type'] == 'blog_posting') {	
		// load blog posting class
		$BLOGPOSTING = load('Content:BlogPosting');
		
		// get object(s)
		$page_id = Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC);
		$posting_id = Base_Cnc::filterRequest($_REQUEST['posting_id'], WCOM_REGEX_NUMERIC);
		
		// get variables
		$object = $BLOGPOSTING->selectBlogPosting($posting_id);
	
		// prepare callback args
		$args = array(
			'text' => $_text,
			'href' => sprintf('{get_url page_id="%u" action=Item posting_id="%u"}', $page_id, $object['id'])
		);
		
	} elseif ($_REQUEST['type'] == 'archive_year') {	
		// load blog posting class
		$BLOGPOSTING = load('Content:BlogPosting');
		
		// get object(s)
		$page_id = Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC);
		$year = Base_Cnc::filterRequest($_REQUEST['year'], WCOM_REGEX_NUMERIC);
	
		// prepare callback args
		$args = array(
			'text' => $_text,
			'href' => sprintf('{get_url page_id="%u" action=ArchiveYear posting_year_added="%u"}', $page_id, $year)
		);
	
	} elseif ($_REQUEST['type'] == 'archive_month') {	
		// load blog posting class
		$BLOGPOSTING = load('Content:BlogPosting');
		
		// get object(s)
		$page_id = Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC);
		$year = Base_Cnc::filterRequest($_REQUEST['year'], WCOM_REGEX_NUMERIC);
		$month = Base_Cnc::filterRequest($_REQUEST['month'], WCOM_REGEX_NUMERIC);
	
		// prepare callback args
		$args = array(
			'text' => $_text,
			'href' => sprintf('{get_url page_id="%u" action=ArchiveMonth posting_year_added="%u" posting_month_added="%u"}',
			 	$page_id, $year, $month)
		);
	
	} elseif ($_REQUEST['type'] == 'globaltemplate') {	
		// load Templating_GlobalTemplate
		$GLOBALTEMPLATE = load('Templating:GlobalTemplate');
	
		// get object(s)
		$object = $GLOBALTEMPLATE->selectGlobalTemplate(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
		
		// prepare callback args
		$args = array(
			'text' => $_text,
			'href' => (!empty($object['change_delimiter']) || !empty($_REQUEST['delimiter'])) ? 
				sprintf('<%%global_template name="%s"%%>', $object['name']) : 
				sprintf('{global_template name="%s"}', $object['name'])	
		);
	
	} elseif ($_REQUEST['type'] == 'globalfile') {	
		// load global file class
		$GLOBALFILE = load('Templating:GlobalFile');
	
		// get object(s)
		$object = $GLOBALFILE->selectGlobalFile(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));

		// prepare callback args
		$args = array(
			'text' => $_text,
			'href' => (!empty($_REQUEST['delimiter'])) ? 
				sprintf('<%%global_file name="%s"%%>', $object['name']) : 
				sprintf('{global_file name="%s"}', $object['name'])	
		);
		
	 } elseif ($_REQUEST['type'] == 'globalbox') {	
		// load Content_GlobalBox class
		$GLOBALBOX = load('Content:GlobalBox');
	
		// get object(s)
		$object = $GLOBALBOX->selectGlobalBox(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));

		// prepare callback args
		$args = array(
			'text' => $_text,
			'href' => sprintf('{select_simple ns="Content" class="GlobalBox" method="selectGlobalBox" var="" id="%u"}',
			 	$object['id'])
		);
		
	} elseif ($_REQUEST['type'] == 'box') {	
		// load box class
		$BOX = load('Content:Box');
	
		// get object(s)
		$object = $BOX->selectBox(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));

		// prepare callback args
		$args = array(
			'text' => $_text,
			'href' => sprintf('{select_simple ns="Content" class="Box" method="selectBoxUsingName" var="box" page="%u" name="%s"}',
			 	$object['id'], $object['page'], $object['name'])
		);
	
	} elseif ($_REQUEST['type'] == 'structuraltemplate') {	
		// load structural template class
		$STRUCTURALTEMPLATE = load('Content:StructuralTemplate');
	
		// get object(s)
		$object = $STRUCTURALTEMPLATE->selectStructuralTemplate(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
		
		// define insert type as reference altough it is in the content path
		// this is because we want to put the raw content
		$insert_type = 'InternalReference';

		// prepare callback args
		$args = array(
			'text' => $_text,
			'href' => utf8_encode($object['content'])
		);
	
	// follow ups
	} elseif ($_REQUEST['type'] == '') {	
		// other types
	}

	// execute text converter callback
	$text_converter = Base_Cnc::filterRequest($_REQUEST['text_converter'], WCOM_REGEX_NUMERIC);
	
	// if no text_converter is set, fetch default
	// make sure we have can fall back on default 
	if ($text_converter == '0') {
		$text_converters = $TEXTCONVERTER->selectTextConverters();
		
		foreach ($text_converters as $val) {	
			if ($val['internal_name'] == 'xhtml') {
				$text_converter = $val['id'];
			}
		}
	}

	// process callback
	$callback_result = $TEXTCONVERTER->insertCallback($text_converter, $insert_type, $args);

	// return response
	if (!empty($callback_result)) {
		print $callback_result;
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