<?php

/**
 * Project: Welcompose
 * File: pages_events_postings_add.php
 *
 * Copyright (c) 2008 creatics
 *
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 *
 * $Id$
 *
 * @copyright 2011 creatics, Olaf Gleba
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// define area constant
define('WCOM_CURRENT_AREA', 'ADMIN');

// get loader
$path_parts = array(
	dirname(__FILE__),
	'..',
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
$deregister_globals_path = dirname(__FILE__).'/../../core/includes/deregister_globals.inc.php';
require(Base_Compat::fixDirectorySeparator($deregister_globals_path));

// admin_navigation
$admin_navigation_path = dirname(__FILE__).'/../../core/includes/admin_navigation.inc.php';
require(Base_Compat::fixDirectorySeparator($admin_navigation_path));

try {
	// start output buffering
	@ob_start();
	
	// load smarty
	$smarty_admin_conf = dirname(__FILE__).'/../../core/conf/smarty_admin.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_admin_conf), true);
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../../core/includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);
	
	// start session
	/* @var $SESSION session */
	$SESSION = load('base:session');
	
	// load user class
	/* @var $USER User_User */
	$USER = load('user:user');
	
	// load login class
	/* @var $LOGIN User_Login */
	$LOGIN = load('User:Login');
	
	// load project class
	/* @var $PROJECT Application_Project */
	$PROJECT = load('application:project');
	
	// load page class
	/* @var $PAGE Content_Page */
	$PAGE = load('content:page');
	
	// load eventposting class
	/* @var $EVENTPOSTING Content_Eventposting */
	$EVENTPOSTING = load('content:eventposting');
	
	// load eventtag class
	/* @var $EVENTTAG Content_Eventtag */
	$EVENTTAG = load('content:eventtag');
	
	// load textconverter class
	/* @var $TEXTCONVERTER Application_Textconverter */
	$TEXTCONVERTER = load('application:textconverter');
	
	// load textmacro class
	/* @var $TEXTMACRO Application_Textmacro */
	$TEXTMACRO = load('application:textmacro');
	
	// load helper class
	/* @var $HELPER Utility_Helper */
	$HELPER = load('utility:helper');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Content', 'EventPosting', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);

	// get default text converter if set
	$default_text_converter = $TEXTCONVERTER->selectDefaultTextConverter();
		
	// get page
	$page = $PAGE->selectPage(Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC));
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('event_posting', 'post');
	
	// hidden for page
	$FORM->addElement('hidden', 'page');
	$FORM->applyFilter('page', 'trim');
	$FORM->applyFilter('page', 'strip_tags');
	$FORM->addRule('page', gettext('Page is not expected to be empty'), 'required');
	$FORM->addRule('page', gettext('Page is expected to be numeric'), 'numeric');

	// textfield for title
	$FORM->addElement('text', 'title', gettext('Title'),
		array('id' => 'event_posting_title', 'maxlength' => 255, 'class' => 'w300 urlify'));
	$FORM->applyFilter('title', 'trim');
	$FORM->applyFilter('title', 'strip_tags');
	$FORM->addRule('title', gettext('Please enter a title'), 'required');
	
	// textfield for URL title
	$FORM->addElement('text', 'title_url', gettext('URL title'),
		array('id' => 'event_posting_title_url', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('title_url', 'trim');
	$FORM->applyFilter('title_url', 'strip_tags');
	$FORM->addRule('title_url', gettext('Enter an URL title'), 'required');
	$FORM->addRule('title_url', gettext('The URL title may only contain chars, numbers and hyphens'),
		WCOM_REGEX_URL_NAME);
		
	// date element for date_start
	$FORM->addElement('date', 'date_start', gettext('Start date'),
		array('language' => 'en', 'format' => 'd.m.Y', 'addEmptyOption' => true,'minYear' => date('Y')-5, 'maxYear' => date('Y')+5),
		array('id' => 'event_posting_date_start'));
	$FORM->addGroupRule('date_start', gettext('Please enter a start date at least'), 'required');
		
	// date element for date_start_time_start
	$FORM->addElement('date', 'date_start_time_start', gettext('Start time'),
		array('language' => 'en', 'format' => 'H:i \U\h\r', 'addEmptyOption' => true), array('id' => 'event_posting_date_start_time_start'));
		
	// date element for date_start_time_end
	$FORM->addElement('date', 'date_start_time_end', gettext('End time'),
		array('language' => 'en', 'format' => 'H:i \U\h\r', 'addEmptyOption' => true), array('id' => 'event_posting_date_start_time_end'));
	
	// date element for date_end
	$FORM->addElement('date', 'date_end', gettext('End date'),
		array('language' => 'en', 'format' => 'd.m.Y', 'addEmptyOption' => true,'minYear' => date('Y')-5, 'maxYear' => date('Y')+5),
		array('id' => 'event_posting_date_end'));
		
	// date element for date_end_time_start
	$FORM->addElement('date', 'date_end_time_start', gettext('Start time'),
		array('language' => 'en', 'format' => 'H:i \U\h\r', 'addEmptyOption' => true), array('id' => 'event_posting_date_end_time_start'));
		
	// date element for date_end_time_end
	$FORM->addElement('date', 'date_end_time_end', gettext('End time'),
		array('language' => 'en', 'format' => 'H:i \U\h\r', 'addEmptyOption' => true), array('id' => 'event_posting_date_end_time_end'));
	
	// textarea for content
	$FORM->addElement('textarea', 'content', gettext('Content'),
		array('id' => 'event_posting_content', 'cols' => 3, 'rows' => '2', 'class' => 'w540h550'));
	$FORM->applyFilter('content', 'trim');
	
	// select for text_converter
	$FORM->addElement('select', 'text_converter', gettext('Text converter'),
		$TEXTCONVERTER->getTextConverterListForForm(), array('id' => 'event_posting_text_converter'));
	$FORM->applyFilter('text_converter', 'trim');
	$FORM->applyFilter('text_converter', 'strip_tags');
	$FORM->addRule('text_converter', gettext('Chosen text converter is out of range'),
		'in_array_keys', $TEXTCONVERTER->getTextConverterListForForm());
	
	// checkbox for apply_macros
	$FORM->addElement('checkbox', 'apply_macros', gettext('Apply text macros'), null,
		array('id' => 'event_posting_apply_macros', 'class' => 'chbx'));
	$FORM->applyFilter('apply_macros', 'trim');
	$FORM->applyFilter('apply_macros', 'strip_tags');
	$FORM->addRule('apply_macros', gettext('The field whether to apply text macros accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// textarea for tags
	$FORM->addElement('textarea', 'tags', gettext('Tags'),
		array('id' => 'event_posting_tags', 'cols' => 3, 'rows' => '2', 'class' => 'w540h50'));
	$FORM->applyFilter('tags', 'trim');
	$FORM->applyFilter('tags', 'strip_tags');
	
	// checkbox for draft
	$FORM->addElement('checkbox', 'draft', gettext('Draft'), null,
		array('id' => 'event_posting_draft', 'class' => 'chbx'));
	$FORM->applyFilter('draft', 'trim');
	$FORM->applyFilter('draft', 'strip_tags');
	$FORM->addRule('draft', gettext('The field whether the posting is a draft accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// date element for date_added
	$FORM->addElement('date', 'date_added', gettext('Creation date'),
		array('language' => 'en', 'format' => 'd.m.Y \u\m H:i', 'addEmptyOption' => true,'minYear' => date('Y')-5, 'maxYear' => date('Y')+5),
		array('id' => 'event_posting_date_added'));
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Save'),
		array('class' => 'submit200'));
	
	// set defaults
	$FORM->setDefaults(array(
		'page' => Base_Cnc::ifsetor($page['id'], null),
		'text_converter' => ($default_text_converter > 0) ? $default_text_converter['id'] : null,
		'apply_macros' => 1,
		'date_added' => date('Y-m-d H:i:s')
	));
	
	// validate it
	if (!$FORM->validate()) {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();
		$quickform_tpl_path = dirname(__FILE__).'/../quickform.tpl.php';
		include(Base_Compat::fixDirectorySeparator($quickform_tpl_path));
		
		// remove attribute on form tag for XHTML compliance
		$FORM->removeAttribute('name');
		$FORM->removeAttribute('target');
		
		$FORM->accept($renderer);
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $renderer->toArray());
		
		// assign paths
		$BASE->utility->smarty->assign('wcom_admin_root_www',
			$BASE->_conf['path']['wcom_admin_root_www']);
		
		// build session
		$session = array(
			'response' => Base_Cnc::filterRequest($_SESSION['response'], WCOM_REGEX_NUMERIC)
		);
		
		// assign $_SESSION to smarty
		$BASE->utility->smarty->assign('session', $session);
		
		// empty $_SESSION
		if (!empty($_SESSION['response'])) {
			$_SESSION['response'] = '';
		}
		
		// assign current user and project id
		$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);
		$BASE->utility->smarty->assign('wcom_current_project', WCOM_CURRENT_PROJECT);

		// select available projects
		$select_params = array(
			'user' => WCOM_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// assign page
		$BASE->utility->smarty->assign('page', $page);
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('content/pages_events_postings_add.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// prepare sql data
		$sqlData = array();
		$sqlData['page'] = $FORM->exportValue('page');
		$sqlData['user'] = WCOM_CURRENT_USER;
		$sqlData['title'] = $FORM->exportValue('title');
		$sqlData['title_url'] = $FORM->exportValue('title_url');
		$sqlData['content_raw'] = $FORM->exportValue('content');
		$sqlData['content'] = $FORM->exportValue('content');
		$sqlData['text_converter'] = ($FORM->exportValue('text_converter') > 0) ? $FORM->exportValue('text_converter') : null;
		$sqlData['apply_macros'] = (string)intval($FORM->exportValue('apply_macros'));
		$sqlData['draft'] = (string)intval($FORM->exportValue('draft'));
		$sqlData['date_added'] = $date_added = $HELPER->datetimeFromQuickFormDate($FORM->exportValue('date_added'));
		$sqlData['year_added'] = date('Y', strtotime($date_added));
		$sqlData['month_added'] = date('m', strtotime($date_added));
		$sqlData['day_added'] = date('d', strtotime($date_added));
		$sqlData['date_start'] = $HELPER->dateFromQuickFormDate($FORM->exportValue('date_start'));
		$sqlData['date_start_time_start'] = $HELPER->timeFromQuickFormDate($FORM->exportValue('date_start_time_start'));
		$sqlData['date_start_time_end'] = $HELPER->timeFromQuickFormDate($FORM->exportValue('date_start_time_end'));
		$sqlData['date_end'] = $HELPER->dateFromQuickFormDate($FORM->exportValue('date_end'));
		$sqlData['date_end_time_start'] = $HELPER->timeFromQuickFormDate($FORM->exportValue('date_end_time_start'));
		$sqlData['date_end_time_end'] = $HELPER->timeFromQuickFormDate($FORM->exportValue('date_end_time_end'));
		
		// apply text macros and text converter if required
		if ($FORM->exportValue('text_converter') > 0 || $FORM->exportValue('apply_macros') > 0) {
			// extract content
			$content = $FORM->exportValue('content');

			// apply startup and pre text converter text macros 
			if ($FORM->exportValue('apply_macros') > 0) {
				$content = $TEXTMACRO->applyTextMacros($content, 'pre');
			}

			// apply text converter
			if ($FORM->exportValue('text_converter') > 0) {
				$content = $TEXTCONVERTER->applyTextConverter(
					$FORM->exportValue('text_converter'),
					$content
				);
			}

			// apply post text converter and shutdown text macros 
			if ($FORM->exportValue('apply_macros') > 0) {
				$content = $TEXTMACRO->applyTextMacros($content, 'post');
			}

			// assign summary/content to sql data array
			$sqlData['content'] = $content;
		}
		
		// test sql data for pear errors
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$posting_id = $EVENTPOSTING->addEventPosting($sqlData);
			
			// add tags
			$EVENTTAG->addPostingTags($FORM->exportValue('page'), $posting_id,
				$EVENTTAG->_tagStringToArray($FORM->exportValue('tags')));
			
			// get tags
			$tags = $EVENTTAG->selectEventTags(array('posting' => $posting_id));
			
			// update blog posting
			$sqlData = array(
				'tag_count' => count($tags),
				'tag_array' => $EVENTTAG->getSerializedTagArrayFromTagArray($tags)
			);
			
			$EVENTPOSTING->updateEventPosting($posting_id, $sqlData);
			
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
			
			// re-throw exception
			throw $e;
		}
	
		// add response to session
		$_SESSION['response'] = 1;
		
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: pages_events_postings_add.php?page=".$FORM->exportValue('page'));
		exit;
	}
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