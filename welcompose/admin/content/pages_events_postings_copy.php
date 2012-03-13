<?php

/**
 * Project: Welcompose
 * File: pages_events_postings_copy.php
 *
 * Copyright (c) 2008-2012 creatics, Olaf Gleba <og@welcompose.de> media.systems
 *
 * Project owner:
 * creatics media.systems, Olaf Gleba
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
	$FORM = $BASE->utility->loadQuickForm('event_posting');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');

	// hidden for id
	$id = $FORM->addElement('hidden', 'id', array('id' => 'id'));
	$id->addRule('required', gettext('Id is not expected to be empty'));
	$id->addRule('regex', gettext('Id is expected to be numeric'), WCOM_REGEX_NUMERIC);
	
	// hidden for page
	$page_id = $FORM->addElement('hidden', 'page', array('id' => 'page'));
	$page_id->addRule('required', gettext('Page is not expected to be empty'));
	$page_id->addRule('regex', gettext('Page is expected to be numeric'), WCOM_REGEX_NUMERIC);
	
	// hidden for start	
	$start = $FORM->addElement('hidden', 'start', array('id' => 'start'));
	$start->addRule('regex', gettext('start is expected to be numeric'), WCOM_REGEX_NUMERIC);
	
	// hidden for timeframe
	$timeframe = $FORM->addElement('hidden', 'timeframe', array('id' => 'timeframe'));
	$timeframe->addRule('regex', gettext('timeframe may only contain chars and underscores'), WCOM_REGEX_TIMEFRAME);
	
	// hidden for draft	
	$draft_filter = $FORM->addElement('hidden', 'draft_filter', array('id' => 'draft_filter'));
	$draft_filter->addRule('regex', gettext('draft is expected to be numeric'), WCOM_REGEX_NUMERIC);
	
	// hidden for limit
	$limit = $FORM->addElement('hidden', 'limit', array('id' => 'limit'));
	$limit->addRule('regex', gettext('limit is expected to be numeric'), WCOM_REGEX_NUMERIC);
	
	// hidden for search_name
	$search_name = $FORM->addElement('hidden', 'search_name', array('id' => 'search_name'));

	// hidden for macro
	$macro = $FORM->addElement('hidden', 'macro', array('id' => 'macro'));
	
	// textfield for title	
	$title = $FORM->addElement('text', 'title', 
		array('id' => 'event_posting_title', 'maxlength' => 255, 'class' => 'w300 urlify'),
		array('label' => gettext('Title'))
		);
	$title->addRule('required', gettext('Please enter a title'));
		
	// textfield for URL title
	$title_url = $FORM->addElement('text', 'title_url', 
		array('id' => 'event_posting_title_url', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('URL title'))
		);
	$title_url->addRule('required', gettext('Enter an URL title'));
	$title_url->addRule('regex', gettext('The URL title may only contain chars, numbers and hyphens'), WCOM_REGEX_URL_NAME);
		
	// date element for date_start
	$date_start = $FORM->addElement('date', 'date_start', null,
		array('label' => gettext('Start date'),'language' => 'en', 'addEmptyOption' => true, 'format' => 'd.m.Y','minYear' => date('Y')-5, 'maxYear' => date('Y')+5)
	);
	$date_start->addRule('required', gettext('Please enter a start date at least'));
		
	// date element for date_start_time_start
	$date_start_time_start = $FORM->addElement('date', 'date_start_time_start', null,
		array('label' => gettext('Start time'),'language' => 'en', 'addEmptyOption' => true, 'format' => 'H:i')
	);
		
	// date element for date_start_time_end
	$date_start_time_end = $FORM->addElement('date', 'date_start_time_end', null,
		array('label' => gettext('End time'),'language' => 'en', 'addEmptyOption' => true, 'format' => 'H:i')
	);
	
	// date element for date_end
	$date_end = $FORM->addElement('date', 'date_end', null,
		array('label' => gettext('End date'),'language' => 'en', 'addEmptyOption' => true, 'format' => 'd.m.Y','minYear' => date('Y')-5, 'maxYear' => date('Y')+5)
	);
		
	// date element for date_end_time_start
	$date_end_time_start = $FORM->addElement('date', 'date_end_time_start', null,
		array('label' => gettext('Start time'),'language' => 'en', 'addEmptyOption' => true, 'format' => 'H:i')
	);
		
	// date element for date_end_time_end1
	$date_end_time_end = $FORM->addElement('date', 'date_end_time_end', null,
		array('label' => gettext('End time'),'language' => 'en', 'addEmptyOption' => true, 'format' => 'H:i')
	);	
	
	// textarea for content
	$content = $FORM->addElement('textarea', 'content', 
		array('id' => 'event_posting_content', 'cols' => 3, 'rows' => '2', 'class' => 'w540h550'),
		array('label' => gettext('Content'))
		);
				
	// select for text_converter
	$text_converter = $FORM->addElement('select', 'text_converter',
	 	array('id' => 'event_posting_text_converter'),
		array('label' => gettext('Text converter'), 'options' => $TEXTCONVERTER->getTextConverterListForForm())
		);
		
	// textarea for tags
	$tags = $FORM->addElement('textarea', 'tags', 
		array('id' => 'event_posting_tags', 'cols' => 3, 'rows' => '2', 'class' => 'w540h50'),
		array('label' => gettext('Tags'))
		);
		
	// checkbox for apply_macros
	$apply_macros = $FORM->addElement('checkbox', 'apply_macros',
		array('id' => 'event_posting_apply_macros', 'class' => 'chbx'),
		array('label' => gettext('Apply text macros'))
		);
	$apply_macros->addRule('regex', gettext('The field whether to apply text macros accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
		
	// checkbox for draft
	$draft = $FORM->addElement('checkbox', 'draft',
		array('id' => 'event_posting_draft', 'class' => 'chbx'),
		array('label' => gettext('Draft'))
		);
	$draft->addRule('regex', gettext('The field whether the posting is a draft accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);

	// date element for date_added
	$date_added = $FORM->addElement('date', 'date_added', null,
		array('label' => gettext('Creation date'),'language' => 'de', 'format' => 'd.m.Y H:i','minYear' => date('Y')-5, 'maxYear' => date('Y')+5)
	);
		
	// submit button (save and stay)
	$save = $FORM->addElement('submit', 'save', 
		array('class' => 'submit200', 'value' => gettext('Duplicate Event Posting'))
		);
	
	// set defaults
	$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
		'page' => Base_Cnc::ifsetor($page['id'], null),
		'id' => Base_Cnc::ifsetor($event_posting['id'], null),
		'timeframe' => Base_Cnc::filterRequest($_REQUEST['timeframe'], WCOM_REGEX_TIMEFRAME),
		'start' => Base_Cnc::filterRequest($_REQUEST['start'], WCOM_REGEX_NUMERIC),
		'limit' => Base_Cnc::filterRequest($_REQUEST['limit'], WCOM_REGEX_NUMERIC),
		'draft_filter' => Base_Cnc::filterRequest($_REQUEST['draft_filter'], WCOM_REGEX_NUMERIC),
		'search_name' => Base_Cnc::filterRequest($_REQUEST['search_name'], WCOM_REGEX_SEARCH_NAME),
		'macro' => Base_Cnc::filterRequest($_REQUEST['macro'], WCOM_REGEX_ORDER_MACRO),
		'title' => Base_Cnc::ifsetor($event_posting['title'], null),
		'title_url' => Base_Cnc::ifsetor($event_posting['title_url'], null),
		'content' => Base_Cnc::ifsetor($event_posting['content_raw'], null),
		'text_converter' => Base_Cnc::ifsetor($event_posting['text_converter'], null),
		'apply_macros' => Base_Cnc::ifsetor($event_posting['apply_macros'], null),
		'tags' => $EVENTTAG->getTagStringFromSerializedArray(Base_Cnc::ifsetor($event_posting['tag_array'], null)),
		'draft' => Base_Cnc::ifsetor($event_posting['draft'], null),
		'date_added' => date('Y-m-d H:i:s'),
		'date_start' => Base_Cnc::ifsetor($event_posting['date_start'], null),
		'date_start_time_start' => Base_Cnc::ifsetor($event_posting['date_start_time_start'], null),
		'date_start_time_end' => Base_Cnc::ifsetor($event_posting['date_start_time_end'], null),
		'date_end' => Base_Cnc::ifsetor($event_posting['date_end'], null),
		'date_end_time_start' => Base_Cnc::ifsetor($event_posting['date_end_time_start'], null),
		'date_end_time_end' => Base_Cnc::ifsetor($event_posting['date_end_time_end'], null)
	)));
	
	// validate it
	if (!$FORM->validate()) {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $FORM->render($renderer)->toArray());
		
		// assign paths
		$BASE->utility->smarty->assign('wcom_admin_root_www',
			$BASE->_conf['path']['wcom_admin_root_www']);
	    
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
		
		// assign posting id
		$BASE->utility->smarty->assign('event_posting', $event_posting);
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('content/pages_events_postings_copy.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->toggleFrozen(true);
		
		// prepare sql data
		$sqlData = array();
		$sqlData['page'] = $page_id->getValue();
		$sqlData['user'] = WCOM_CURRENT_USER;
		$sqlData['title'] = $title->getValue();
		$sqlData['title_url'] = $title_url->getValue();
		$sqlData['content_raw'] = $content->getValue();
		$sqlData['content'] = $content->getValue();
		$sqlData['text_converter'] = ($text_converter->getValue() > 0) ? 
			$text_converter->getValue() : null;
		$sqlData['apply_macros'] = (string)intval($apply_macros->getValue());
		$sqlData['draft'] = (string)intval($draft->getValue());
		$sqlData['date_added'] = $date_added = $HELPER->datetimeFromQuickFormDate($date_added->getValue());
		$sqlData['year_added'] = date('Y', strtotime($date_added));
		$sqlData['month_added'] = date('m', strtotime($date_added));
		$sqlData['day_added'] = date('d', strtotime($date_added));
		$sqlData['date_start'] = $HELPER->dateFromQuickFormDate($date_start->getValue());
		$sqlData['date_start_time_start'] = $HELPER->timeFromQuickFormDate($date_start_time_start->getValue());
		$sqlData['date_start_time_end'] = $HELPER->timeFromQuickFormDate($date_start_time_end->getValue());
		$sqlData['date_end'] = $HELPER->dateFromQuickFormDate($date_end->getValue());
		$sqlData['date_end_time_start'] = $HELPER->timeFromQuickFormDate($date_end_time_start->getValue());
		$sqlData['date_end_time_end'] = $HELPER->timeFromQuickFormDate($date_end_time_end->getValue());
		
		// apply text macros and text converter if required
		if ($text_converter->getValue() > 0 || $apply_macros->getValue() > 0) {
			// extract content
			$content = $content->getValue();

			// apply startup and pre text converter text macros 
			if ($apply_macros->getValue() > 0) {
				$content = $TEXTMACRO->applyTextMacros($content, 'pre');
			}

			// apply text converter
			if ($text_converter->getValue() > 0) {
				$content = $TEXTCONVERTER->applyTextConverter(
					$text_converter->getValue(),
					$content
				);
			}

			// apply post text converter and shutdown text macros 
			if ($apply_macros->getValue() > 0) {
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
			$EVENTTAG->addPostingTags($page_id->getValue(), $posting_id,
				$EVENTTAG->_tagStringToArray($tags->getValue()));
			
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
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}

		// save request params 
		$start = $start->getValue();
		$limit = $limit->getValue();
		$draft_filter = $draft_filter->getValue();
		$timeframe = $timeframe->getValue();
		$macro = $macro->getValue();
		$search_name = $search_name->getValue();
		
		// append request params
		$redirect_params = (!empty($start)) ? '&start='.$start : '';
		$redirect_params .= (!empty($limit)) ? '&limit='.$limit : '&limit=20';
		$redirect_params .= (!empty($draft_filter) || $draft_filter === (string)intval(0)) ? '&draft_filter='.$draft_filter : '';
		$redirect_params .= (!empty($timeframe)) ? '&timeframe='.$timeframe : '';
		$redirect_params .= (!empty($macro)) ? '&macro='.$macro : '';
		$redirect_params .= (!empty($search_name)) ? '&search_name='.$search_name : '';
		
		// redirect
		header("Location: pages_events_postings_select.php?page=".$page_id->getValue().$redirect_params);
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
