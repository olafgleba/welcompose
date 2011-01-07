<?php

/**
 * Project: Welcompose
 * File: pages_simple_dates_add.php
 *
 * Copyright (c) 2009 creatics
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
 * @copyright 2009 creatics, Olaf Gleba
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
	
	// load simpledate class
	/* @var $SIMPLEDATE Content_SimpleDate */
	$SIMPLEDATE = load('content:SimpleDate');
	
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
	if (!wcom_check_access('Content', 'SimpleDate', 'Manage')) {
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
	$FORM = $BASE->utility->loadQuickForm('simple_date', 'post');
	
	// register new callack rule
	$FORM->registerRule('checkDateOnEmptiness', 'callback', 'checkDateOnEmpty', $SIMPLEDATE);
	
	// hidden for page
	$FORM->addElement('hidden', 'page');
	$FORM->applyFilter('page', 'trim');
	$FORM->applyFilter('page', 'strip_tags');
	$FORM->addRule('page', gettext('Page is not expected to be empty'), 'required');
	$FORM->addRule('page', gettext('Page is expected to be numeric'), 'numeric');

	// date element for date_start
	$FORM->addElement('date', 'date_start', gettext('Start date'),
		array('language' => 'en', 'format' => 'd.m.Y \u\m H:i', 'addEmptyOption' => true, 'minYear' => date('Y')-1, 'maxYear' => date('Y')+5),
		array('id' => 'simple_date_date_start'));
		$FORM->addRule('date_start', gettext('Please select a full start date at least'), 'required');
		$FORM->addRule('date_start', gettext('Please select a start date with day, month and year assigned at least'), 'checkDateOnEmptiness');
		
	// date element for date_end
	$FORM->addElement('date', 'date_end', gettext('End date'),
		array('language' => 'en', 'format' => 'd.m.Y \u\m H:i', 'addEmptyOption' => true,'minYear' => '2011', 'maxYear' => '2020', 'minYear' => date('Y')-1, 'maxYear' => date('Y')+5),
		array('id' => 'simple_date_date_end'));
	
	// textarea for location
	$FORM->addElement('textarea', 'location', gettext('Location'),
		array('id' => 'simple_date_location', 'cols' => 3, 'rows' => '2', 'class' => 'w540h50'));
	$FORM->applyFilter('location', 'trim');
	$FORM->addRule('location', gettext('Please enter a location for your date entry'), 'required');
	
	// inputs for links
	$FORM->addElement('text', 'link_1', gettext('Link 1'),
		array('id' => 'simple_date_link_1', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('link_1', 'trim');
	$FORM->addRule('link_1', gettext('Please enter a valid target URL for field Link 1'), 'regex',
		WCOM_REGEX_URL);

	$FORM->addElement('text', 'link_2', gettext('Link 2'),
		array('id' => 'simple_date_link_2', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('link_2', 'trim');
	$FORM->addRule('link_2', gettext('Please enter a valid target URL for field Link 2'), 'regex',
		WCOM_REGEX_URL);
	
	$FORM->addElement('text', 'link_3', gettext('Link 3'),
		array('id' => 'simple_date_link_3', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('link_3', 'trim');
	$FORM->addRule('link_3', gettext('Please enter a valid target URL for field Link 3'), 'regex',
		WCOM_REGEX_URL);
	
	// checkbox for sold out
	$FORM->addElement('checkbox', 'sold_out_1', gettext('Sold out'), null,
		array('id' => 'simple_date_sold_out_1', 'class' => 'chbx'));
	$FORM->applyFilter('sold_out_1', 'trim');
	$FORM->applyFilter('sold_out_1', 'strip_tags');
	$FORM->addRule('sold_out_1', gettext('The field whether to apply a sold_out status accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
		
	$FORM->addElement('checkbox', 'sold_out_2', gettext('Sold out'), null,
		array('id' => 'simple_date_sold_out_2', 'class' => 'chbx'));
	$FORM->applyFilter('sold_out_2', 'trim');
	$FORM->applyFilter('sold_out_2', 'strip_tags');
	$FORM->addRule('sold_out_2', gettext('The field whether to apply a sold_out status accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
		
	$FORM->addElement('checkbox', 'sold_out_3', gettext('Sold out'), null,
		array('id' => 'simple_date_sold_out_3', 'class' => 'chbx'));
	$FORM->applyFilter('sold_out_3', 'trim');
	$FORM->applyFilter('sold_out_3', 'strip_tags');
	$FORM->addRule('sold_out_3', gettext('The field whether to apply a sold_out status accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// select for text_converter
	$FORM->addElement('select', 'text_converter', gettext('Text converter'),
		$TEXTCONVERTER->getTextConverterListForForm(), array('id' => 'simple_date_text_converter'));
	$FORM->applyFilter('text_converter', 'trim');
	$FORM->applyFilter('text_converter', 'strip_tags');
	$FORM->addRule('text_converter', gettext('Chosen text converter is out of range'),
		'in_array_keys', $TEXTCONVERTER->getTextConverterListForForm());
	
	// checkbox for apply_macros
	$FORM->addElement('checkbox', 'apply_macros', gettext('Apply text macros'), null,
		array('id' => 'simple_date_apply_macros', 'class' => 'chbx'));
	$FORM->applyFilter('apply_macros', 'trim');
	$FORM->applyFilter('apply_macros', 'strip_tags');
	$FORM->addRule('apply_macros', gettext('The field whether to apply text macros accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// checkbox for draft
	$FORM->addElement('checkbox', 'draft', gettext('Draft'), null,
		array('id' => 'simple_date_draft', 'class' => 'chbx'));
	$FORM->applyFilter('draft', 'trim');
	$FORM->applyFilter('draft', 'strip_tags');
	$FORM->addRule('draft', gettext('The field whether the date is a draft accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// checkbox for ping
	$FORM->addElement('checkbox', 'ping', gettext('Ping'), null,
		array('id' => 'simple_date_ping', 'class' => 'chbx'));
	$FORM->applyFilter('ping', 'trim');
	$FORM->applyFilter('ping', 'strip_tags');
	$FORM->addRule('ping', gettext('The field whether a ping should be issued accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Save'),
		array('class' => 'submit200'));
	
	// set defaults
	$FORM->setDefaults(array(
		'page' => Base_Cnc::ifsetor($page['id'], null),
		'text_converter' => ($default_text_converter > 0) ? $default_text_converter['id'] : null,
		'apply_macros' => 1,
		'ping' => 0,
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
		$BASE->utility->smarty->display('content/pages_simple_dates_add.html', WCOM_TEMPLATE_KEY);
		
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
		$sqlData['date_start'] = $HELPER->datetimeFromQuickFormDateWithNull($FORM->exportValue('date_start'));
		$sqlData['date_end'] = $HELPER->datetimeFromQuickFormDateWithNull($FORM->exportValue('date_end'));
		$sqlData['location_raw'] = $FORM->exportValue('location');
		$sqlData['location'] = $FORM->exportValue('location');
		$sqlData['link_1'] = $FORM->exportValue('link_1');
		$sqlData['link_2'] = $FORM->exportValue('link_2');
		$sqlData['link_3'] = $FORM->exportValue('link_3');
		$sqlData['sold_out_1'] = (string)intval($FORM->exportValue('sold_out_1'));
		$sqlData['sold_out_2'] = (string)intval($FORM->exportValue('sold_out_2'));
		$sqlData['sold_out_3'] = (string)intval($FORM->exportValue('sold_out_3'));
		$sqlData['text_converter'] = ($FORM->exportValue('text_converter') > 0) ? 
			$FORM->exportValue('text_converter') : null;
		$sqlData['apply_macros'] = (string)intval($FORM->exportValue('apply_macros'));
		$sqlData['draft'] = (string)intval($FORM->exportValue('draft'));
		$sqlData['ping'] = (string)intval($FORM->exportValue('ping'));
		$sqlData['date_added'] = date('Y-m-d H:i:s');
		
		// apply text macros and text converter if required
		if ($FORM->exportValue('text_converter') > 0 || $FORM->exportValue('apply_macros') > 0) {
			// extract summary/content
			$location = $FORM->exportValue('location');

			// apply startup and pre text converter text macros 
			if ($FORM->exportValue('apply_macros') > 0) {
				$location = $TEXTMACRO->applyTextMacros($location, 'pre');
			}

			// apply text converter
			if ($FORM->exportValue('text_converter') > 0) {
				$location = $TEXTCONVERTER->applyTextConverter(
					$FORM->exportValue('text_converter'),
					$location
				);
			}

			// apply post text converter and shutdown text macros 
			if ($FORM->exportValue('apply_macros') > 0) {
				$location = $TEXTMACRO->applyTextMacros($location, 'post');
			}

			// assign summary/content to sql data array
			$sqlData['location'] = $location;
		}
		
		// test sql data for pear errors
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$SIMPLEDATE->addSimpleDate($sqlData);
			
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
			
			// re-throw exception
			throw $e;
		}
		
		// issue pings if required
		if ($FORM->exportValue('ping') == 1) {	
			
			// load ping service configuration class
			$PINGSERVICECONFIGURATION = load('application:pingserviceconfiguration');
			
			// load ping service class
			$PINGSERVICE = load('application:pingservice');
			
			// get configured ping service configurations
			$configurations = $PINGSERVICECONFIGURATION->selectPingServiceConfigurations(array('page' => $page['id']));
			
			// issue pings if configurations exits
			if (!empty($configurations)) {
				foreach ($configurations as $_configuration) {
					$PINGSERVICE->pingService($_configuration['id']);
				}
			}
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
		header("Location: pages_simple_dates_add.php?page=".$FORM->exportValue('page'));
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