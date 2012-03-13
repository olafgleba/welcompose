<?php

/**
 * Project: Welcompose
 * File: callbacks.php
 *
 * Copyright (c) 2008-2012 creatics, Olaf Gleba <og@welcompose.de>
 *
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 *
 * @author Olaf Gleba
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
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
		$_text = (!empty($_REQUEST['text'])) ? stripslashes($_REQUEST['text']) : gettext('Your link description');
	}
	elseif ($insert_type == 'InternalReference') {
		$_text = (!empty($_REQUEST['text'])) ? stripslashes($_REQUEST['text']) : '';
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
			'href' => sprintf('{get_url page_id="%u"}', $object['id'])
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
			'href' => (!empty($_REQUEST['delimiter'])) ? 
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
			'href' => sprintf('{select_simple ns="Content" class="GlobalBox" method="selectGlobalBox" var="global_box" id="%u"}',
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
			'href' => sprintf('{select_simple ns="Content" class="Box" method="selectBoxUsingName" var="box" page=$page.id name="%s"}',
			 	$object['name'])
		);
	
	} elseif ($_REQUEST['type'] == 'structuraltemplate') {	
		// load structural template class
		$STRUCTURALTEMPLATE = load('Content:StructuralTemplate');
	
		// get object(s)
		$object = $STRUCTURALTEMPLATE->selectStructuralTemplate(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
		
		// define insert type static as reference
		// this is because we want to insert the raw content nonetheless the path references
		$insert_type = 'InternalReference';

		// redefine var _text
		// this is because we defined the insert type manually and therefore
		// the href preparation in line 92 - 97 could not take place here.
		// so we have to make a condition again
		$_text = (!empty($_REQUEST['text'])) ? stripslashes($_REQUEST['text']) : '';
		
		// prepare callback args
		$args = array(
			'text' => $_text,
			'href' => $object['content']
		);

	} elseif ($_REQUEST['type'] == 'smartyplugin') {	
	
		// This callback is not pulled from database as it insert
		// only syntax for correponding smarty plugin/snippet.
		
		// get target
		$plugin = Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_SMARTY_CLASS_NAME);
		
		switch ((string)$plugin) {
			case 'select_simple' :
				$syntax = '{select_simple ns="[Application|Community|Content|Media]" class="[classToUse]" method="[classMethodToUse]" var="[variable]" id="[id]"}';
			break;
			case 'select_simple_page' :
				$syntax = '{select_simple ns="[Application|Community|Content|Media]" class="[classToUse]" method="[classMethodToUse]" var="[variable]" page="[page_id]" id="[id]"}';
			break;
			case 'select_named' :
				$syntax = '{select_named ns="[Application|Community|Content|Media]" class="[classToUse]" method="[classMethodToUse]" var="[variable]"}';
			break;
			case 'tag_cloud' :
				$syntax = '{tag_cloud page=$page.id var="[variable]" limit="[number]" range="[number]"}';
			break;
			case 'get_glossary' :
				$syntax = '{get_glossary var="[variable]" action="[pager|content]"}';
			break;
			case 'breadcrumb' :
				$syntax = '{breadcrumb current_page=$page.id var="[variable]"}';
			break;
			case 'social_bookmarks' :
				$syntax = '{social_bookmarks page=$page.id var="[variable]"}';
			break;
			case 'include' :
				$syntax = '{include file="wcom:[nameOfTemplateType].`$page.id`"}';
			break;
		}
		// define insert type static as reference
		// this is because we want to insert the raw content nonetheless the path references
		$insert_type = 'InternalReference';

		// redefine var _text
		// this is because we defined the insert type manually and therefore
		// the href preparation in line 92 - 97 could not take place here.
		// so we have to make a condition again
		$_text = (!empty($_REQUEST['text'])) ? stripslashes($_REQUEST['text']) : '';
		
		// prepare callback args
		$args = array(
			'text' => $_text,
			'href' => $syntax
		);

	} elseif ($_REQUEST['type'] == 'abbreviation') {	
		// load abbreviation class
		$ABBREVIATION = load('Content:Abbreviation');
	
		// get object(s)
		$object = $ABBREVIATION->selectAbbreviation(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
		
		// define insert type static as reference
		$insert_type = 'Abbreviation';
		
		// prepare callback args
		$args = array(
			'long_form' => sprintf('{get_abbreviation id="%u" value="long_form"}', $object['id']),
			'name' => sprintf('{get_abbreviation id="%u" value="name"}', $object['id']),
			'lang' => (!empty($object['lang'])) ? sprintf('{get_abbreviation id="%u" value="lang"}', $object['id']) : NULL
		);
		
	} elseif ($_REQUEST['type'] == 'blogtag') {
		
		// define insert type static as reference
		// this is because we want to insert the raw content nonetheless the path references
		$insert_type = 'InternalReference';
		
		// As we directly deliver the content to the textconverter without the
		// need to query the database, there is nothing else to do here.
		
		// prepare callback args
		$args = array(
			'text' => $_REQUEST['text'],
			'href' => ''
		);
		
	} elseif ($_REQUEST['type'] == 'eventtag') {
		
		// define insert type static as reference
		// this is because we want to insert the raw content nonetheless the path references
		$insert_type = 'InternalReference';
		
		// As we directly deliver the content to the textconverter without the
		// need to query the database, there is nothing else to do here.
		
		// prepare callback args
		$args = array(
			'text' => $_REQUEST['text'],
			'href' => ''
		);
	
	// follow ups
	} elseif ($_REQUEST['type'] == '') {	
		// other types
	}

	// execute text converter callback
	$text_converter = Base_Cnc::filterRequest($_REQUEST['text_converter'], WCOM_REGEX_NUMERIC);
	
	// if no text_converter is set make
	// sure we can fall back on default 
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
	$BASE->error->displayException($e, $BASE->utility->smarty);
	$BASE->error->triggerException($e);
	
	// exit
	exit;
}

?>