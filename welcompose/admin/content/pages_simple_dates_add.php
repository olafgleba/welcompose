<?php

/**
 * Project: Welcompose
 * File: pages_simple_dates_add.php
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
	
	// assign current project values
	$_wcom_current_project = $PROJECT->selectProject(WCOM_CURRENT_PROJECT);
	$BASE->utility->smarty->assign('_wcom_current_project', $_wcom_current_project);

	// get default text converter if set
	$default_text_converter = $TEXTCONVERTER->selectDefaultTextConverter();
		
	// get page
	$page = $PAGE->selectPage(Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC));
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('simple_date');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');

	// hidden for page
	$page_id = $FORM->addElement('hidden', 'page', array('id' => 'page'));

	// date element for date_start
	$date_start = $FORM->addElement('date', 'date_start', null,
		array('label' => gettext('Start date'),'language' => 'en', 'addEmptyOption' => true, 'format' => 'd.m.Y','minYear' => date('Y')-5, 'maxYear' => date('Y')+5)
	);
	$date_start->addRule('required', gettext('Please enter a start date at least'));	
	$date_start->addRule('each', gettext('Please enter a full start date at least'),
		$date_start->createRule('nonempty')
		);
		
	// date element for date_end
	$date_end = $FORM->addElement('date', 'date_end', null,
		array('label' => gettext('End date'),'language' => 'en', 'addEmptyOption' => true, 'format' => 'd.m.Y','minYear' => date('Y')-5, 'maxYear' => date('Y')+5)
	);
	
	// textarea for location
	$location = $FORM->addElement('textarea', 'location', 
		array('id' => 'simple_date_location', 'cols' => 3, 'rows' => '2', 'class' => 'w540h50'),
		array('label' => gettext('Location'))
		);		
	
	// inputs for links
	$link_1 = $FORM->addElement('text', 'link_1', 
		array('id' => 'simple_date_link_1', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Link 1'))
		);
	$link_1->addRule('regex', gettext('Please enter a valid target URL for field Link 1'), WCOM_REGEX_URL);
		
	$link_2 = $FORM->addElement('text', 'link_2', 
		array('id' => 'simple_date_link_2', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Link 2'))
		);
	$link_2->addRule('regex', gettext('Please enter a valid target URL for field Link 2'), WCOM_REGEX_URL);
	
	$link_3 = $FORM->addElement('text', 'link_3', 
		array('id' => 'simple_date_link_3', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Link 3'))
		);
	$link_3->addRule('regex', gettext('Please enter a valid target URL for field Link 3'), WCOM_REGEX_URL);
	
	// checkbox for sold out
	$sold_out_1 = $FORM->addElement('checkbox', 'sold_out_1',
		array('id' => 'simple_date_sold_out_1', 'class' => 'chbx'),
		array('label' => gettext('Sold out'))
		);
	$sold_out_1->addRule('regex', gettext('The field whether to apply a sold_out status accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
	
	$sold_out_2 = $FORM->addElement('checkbox', 'sold_out_2',
		array('id' => 'simple_date_sold_out_2', 'class' => 'chbx'),
		array('label' => gettext('Sold out'))
		);
	$sold_out_2->addRule('regex', gettext('The field whether to apply a sold_out status accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
	
	$sold_out_3 = $FORM->addElement('checkbox', 'sold_out_3',
		array('id' => 'simple_date_sold_out_3', 'class' => 'chbx'),
		array('label' => gettext('Sold out'))
		);
	$sold_out_3->addRule('regex', gettext('The field whether to apply a sold_out status accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
	
	// select for text_converter
	$text_converter = $FORM->addElement('select', 'text_converter',
	 	array('id' => 'simple_date_text_converter'),
		array('label' => gettext('Text converter'), 'options' => $TEXTCONVERTER->getTextConverterListForForm())
		);
		
	// checkbox for apply_macros
	$apply_macros = $FORM->addElement('checkbox', 'apply_macros',
		array('id' => 'simple_date_apply_macros', 'class' => 'chbx'),
		array('label' => gettext('Apply text macros'))
		);
	$apply_macros->addRule('regex', gettext('The field whether to apply text macros accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
	
	// checkbox for draft
	$draft = $FORM->addElement('checkbox', 'draft',
		array('id' => 'simple_date_draft', 'class' => 'chbx'),
		array('label' => gettext('Draft'))
		);
	$draft->addRule('regex', gettext('The field whether the posting is a draft accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
		
	// submit button
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'submit200', 'value' => gettext('Save'))
		);
	
	// set defaults
	$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
		'page' => Base_Cnc::ifsetor($page['id'], null),
		'text_converter' => ($default_text_converter > 0) ? $default_text_converter['id'] : null,
		'apply_macros' => 1
	)));
	
	// validate it
	if (!$FORM->validate()) {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();
		
		// fetch {function} template to set
		// required/error markup on each form fields
		$BASE->utility->smarty->fetch(dirname(__FILE__).'/../quickform.tpl');
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $FORM->render($renderer)->toArray());
		
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
		$FORM->toggleFrozen(true);
		
		// prepare sql data
		$sqlData = array();
		$sqlData['page'] = $page_id->getValue();
		$sqlData['user'] = WCOM_CURRENT_USER;
		$sqlData['date_start'] = $HELPER->dateFromQuickFormDate($date_start->getValue());
		$sqlData['date_end'] = $HELPER->dateFromQuickFormDate($date_end->getValue());
		$sqlData['location_raw'] = $location->getValue();
		$sqlData['location'] = $location->getValue();
		$sqlData['link_1'] = $link_1->getValue();
		$sqlData['link_2'] = $link_2->getValue();
		$sqlData['link_3'] = $link_3->getValue();
		$sqlData['sold_out_1'] = (string)intval($sold_out_1->getValue());
		$sqlData['sold_out_2'] = (string)intval($sold_out_2->getValue());
		$sqlData['sold_out_3'] = (string)intval($sold_out_3->getValue());
		$sqlData['text_converter'] = ($text_converter->getValue() > 0) ? 
			$text_converter->getValue() : null;
		$sqlData['apply_macros'] = (string)intval($apply_macros->getValue());
		$sqlData['draft'] = (string)intval($draft->getValue());
		$sqlData['date_added'] = date('Y-m-d H:i:s');
		
		// apply text macros and text converter if required
		if ($text_converter->getValue() > 0 || $apply_macros->getValue() > 0) {
			// extract summary/content
			$location = $location->getValue();

			// apply startup and pre text converter text macros 
			if ($apply_macros->getValue() > 0) {
				$location = $TEXTMACRO->applyTextMacros($location, 'pre');
			}

			// apply text converter
			if ($text_converter->getValue() > 0) {
				$location = $TEXTCONVERTER->applyTextConverter(
					$text_converter->getValue(),
					$location
				);
			}

			// apply post text converter and shutdown text macros 
			if ($apply_macros->getValue() > 0) {
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
	
		// add response to session
		$_SESSION['response'] = 1;
		
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: pages_simple_dates_add.php?page=".$page_id->getValue());
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