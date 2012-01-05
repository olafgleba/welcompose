<?php

/**
 * Project: Welcompose
 * File: pages_events_postings_edit.php
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
	
	// get page
	$page = $PAGE->selectPage(Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC));
	
	// get event posting
	$event_posting = $EVENTPOSTING->selectEventPosting(Base_Cnc::filterRequest($_REQUEST['id'],
		WCOM_REGEX_NUMERIC));
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('event_posting', 'post');
	
	// hidden for id
	$FORM->addElement('hidden', 'id');
	$FORM->applyFilter('id', 'trim');
	$FORM->applyFilter('id', 'strip_tags');
	$FORM->addRule('id', gettext('Id is not expected to be empty'), 'required');
	$FORM->addRule('id', gettext('Id is expected to be numeric'), 'numeric');
	
	// hidden for page
	$FORM->addElement('hidden', 'page');
	$FORM->applyFilter('page', 'trim');
	$FORM->applyFilter('page', 'strip_tags');
	$FORM->addRule('page', gettext('Page is not expected to be empty'), 'required');
	$FORM->addRule('page', gettext('Page is expected to be numeric'), 'numeric');
	
	// hidden for start
	$FORM->addElement('hidden', 'start');
	$FORM->applyFilter('start', 'trim');
	$FORM->applyFilter('start', 'strip_tags');
	$FORM->addRule('start', gettext('start is expected to be numeric'), 'numeric');
	
	// hidden for limit
	$FORM->addElement('hidden', 'limit');
	$FORM->applyFilter('limit', 'trim');
	$FORM->applyFilter('limit', 'strip_tags');
	$FORM->addRule('limit', gettext('limit is expected to be numeric'), 'numeric');
	
	// hidden for search_name
	$FORM->addElement('hidden', 'search_name');
	$FORM->applyFilter('search_name', 'trim');
	$FORM->applyFilter('search_name', 'strip_tags');
	
	// hidden for search_name
	$FORM->addElement('hidden', 'macro');
	$FORM->applyFilter('macro', 'trim');

	// textfield for title
	$FORM->addElement('text', 'title', gettext('Title'),
		array('id' => 'event_posting_title', 'maxlength' => 255, 'class' => 'w300'));
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
	
	// submit button (save and stay)
	$FORM->addElement('submit', 'save', gettext('Save edit'),
		array('class' => 'submit200'));
		
	// submit button (save and go back)
	$FORM->addElement('submit', 'submit', gettext('Save edit and go back'),
		array('class' => 'submit200go'));
	
	// set defaults
	$FORM->setDefaults(array(
		'page' => Base_Cnc::ifsetor($page['id'], null),
		'id' => Base_Cnc::ifsetor($event_posting['id'], null),
		'start' => Base_Cnc::filterRequest($_REQUEST['start'], WCOM_REGEX_NUMERIC),
		'limit' => Base_Cnc::filterRequest($_REQUEST['limit'], WCOM_REGEX_NUMERIC),
		'search_name' => Base_Cnc::filterRequest($_REQUEST['search_name'], WCOM_REGEX_SEARCH_NAME),
		'macro' => Base_Cnc::filterRequest($_REQUEST['macro'], WCOM_REGEX_ORDER_MACRO),
		'title' => Base_Cnc::ifsetor($event_posting['title'], null),
		'title_url' => Base_Cnc::ifsetor($event_posting['title_url'], null),
		'content' => Base_Cnc::ifsetor($event_posting['content_raw'], null),
		'text_converter' => Base_Cnc::ifsetor($event_posting['text_converter'], null),
		'apply_macros' => Base_Cnc::ifsetor($event_posting['apply_macros'], null),
		'tags' => $EVENTTAG->getTagStringFromSerializedArray(Base_Cnc::ifsetor($event_posting['tag_array'], null)),
		'draft' => Base_Cnc::ifsetor($event_posting['draft'], null),
		'date_added' => Base_Cnc::ifsetor($event_posting['date_added'], null),
		'date_start' => Base_Cnc::ifsetor($event_posting['date_start'], null),
		'date_start_time_start' => Base_Cnc::ifsetor($event_posting['date_start_time_start'], null),
		'date_start_time_end' => Base_Cnc::ifsetor($event_posting['date_start_time_end'], null),
		'date_end' => Base_Cnc::ifsetor($event_posting['date_end'], null),
		'date_end_time_start' => Base_Cnc::ifsetor($event_posting['date_end_time_start'], null),
		'date_end_time_end' => Base_Cnc::ifsetor($event_posting['date_end_time_end'], null)
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
	    
		// assign current user and project id
		$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);
		$BASE->utility->smarty->assign('wcom_current_project', WCOM_CURRENT_PROJECT);
		
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

		// select available projects
		$select_params = array(
			'user' => WCOM_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// assign page
		$BASE->utility->smarty->assign('page', $page);
		
		// assign posting id
		$BASE->utility->smarty->assign('event_posting', $event_posting);
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('content/pages_events_postings_edit.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// prepare sql data
		$sqlData = array();
		$sqlData['title'] = $FORM->exportValue('title');
		$sqlData['title_url'] = $FORM->exportValue('title_url');
		$sqlData['content_raw'] = $FORM->exportValue('content');
		$sqlData['content'] = $FORM->exportValue('content');
		$sqlData['text_converter'] = ($FORM->exportValue('text_converter') > 0) ? 
			$FORM->exportValue('text_converter') : null;
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
			$EVENTPOSTING->updateEventPosting($FORM->exportValue('id'), $sqlData);
			
			// update tags
			$EVENTTAG->updatePostingTags($FORM->exportValue('page'), $FORM->exportValue('id'),
				$EVENTTAG->_tagStringToArray($FORM->exportValue('tags')));
			
			// get tags
			$tags = $EVENTTAG->selectEventTags(array('posting' => $FORM->exportValue('id')));
			
			// update event posting
			$sqlData = array(
				'tag_count' => count($tags),
				'tag_array' => $EVENTTAG->getSerializedTagArrayFromTagArray($tags)
			);
			$EVENTPOSTING->updateEventPosting($FORM->exportValue('id'), $sqlData);
			
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
			
			// re-throw exception
			throw $e;
		}
		
		// controll value
		$saveAndRemainOnPage = $FORM->exportValue('save');
		
		// add response to session
		if (!empty($saveAndRemainOnPage)) {
			$_SESSION['response'] = 1;
		}
				
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}

		// save request params 
		$start = $FORM->exportValue('start');
		$limit = $FORM->exportValue('limit');
		$search_name = $FORM->exportValue('search_name');
		$macro = $FORM->exportValue('macro');
		
		// append request params
		$redirect_params = (!empty($start)) ? '&start='.$start : '&start=0';
		$redirect_params .= (!empty($limit)) ? '&limit='.$limit : '&limit=20';
		$redirect_params .= (!empty($macro)) ? '&macro='.$macro : '';
		$redirect_params .= (!empty($search_name)) ? '&search_name='.$search_name : '';
		
		// redirect
		if (!empty($saveAndRemainOnPage)) {
			header("Location: pages_events_postings_edit.php?page=".
						$FORM->exportValue('page')."&id=".$FORM->exportValue('id').$redirect_params);
		} else {
			header("Location: pages_events_postings_select.php?page=".
						$FORM->exportValue('page').$redirect_params);
		}
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
