<?php

/**
 * Project: Welcompose
 * File: actions_apply_glossary_page.php
 *
 * Copyright (c) 2009 creatics media.systems
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
 * @copyright 2009 creatics media.systems, Olaf Gleba
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
	
	// load textconverter class
	/* @var $TEXTCONVERTER Application_Textconverter */
	$TEXTCONVERTER = load('application:textconverter');

	// load textmacro class
	/* @var $TEXTMACRO Application_Textmacro */
	$TEXTMACRO = load('application:textmacro');
	
	// load abbreviation class
	/* @var $ABBREVIATION Content_Abbreviation */
	$ABBREVIATION = load('content:abbreviation');

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
	if (!wcom_check_access('Application', 'Action', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
	// assign paths
	$BASE->utility->smarty->assign('wcom_admin_root_www',
		$BASE->_conf['path']['wcom_admin_root_www']);
	
	// assign current user and project id
	$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('wcom_current_project', WCOM_CURRENT_PROJECT);
	

	// prepare sql data
	$sqlData = array();
	
	// class loader array
	$classLoad = array(
		'SIMPLEPAGE' => array('selectSimplePages', 'updateSimplePage'),
		'SIMPLEFORM' => array('selectSimpleForms', 'updateSimpleForm'),
		'GENERATORFORM' => array('selectGeneratorForms', 'updateGeneratorForm'),
		'BLOGPOSTING' => array('selectBlogPostings', 'updateBlogPosting'),
		'BOX' => array('selectBoxes', 'updateBox'),
		'GLOBALBOX' => array('selectGlobalBoxes', 'updateGlobalBox'),
		'ABBREVIATION' => array('selectAbbreviations', 'updateAbbreviation')
	);
	
	// filter request data
	$_page_id = Base_Cnc::filterRequest($_REQUEST['page_id'], WCOM_REGEX_NUMERIC);
	$_apply_page = Base_Cnc::ifsetor($_REQUEST['apply_page'], null);
	
	// throw exception if no glossary page is selected if its considered to
	if ($_apply_page == 1 && empty($_page_id)) {
		throw new Exception(gettext('Please select a page to use as your glossary target'));
	}
	
	foreach ($classLoad as $classRef => $classFunc) {
	
		// define some vars
		$_class = strtolower($classRef);
		$_class_reference = $classRef;
			
		// load the appropriate class
		$_class_reference = load('content:'.$_class);
			
		// collect results within var $_class
		// example: $simplepages = $SIMPLEPAGE->selectSimplePages();
		$_class = $_class_reference->$classFunc['0']();
	
		// Iterate through the results
		foreach ($_class as $_key => $_value) {	
	
			// make sure field content is not NULL
			// this may occur when a page is added but still not edited
			if (!is_null($_value['content_raw'])) {	
			
				// fetch current abbreviation tags
				preg_match_all("=(<abbr\s*(lang\=\"({get_abbreviation\s*id\=\"(.*?)\"\s*value\=\"lang\"})\"\s*|)title\=\"({get_abbreviation\s*id\=\"(.*?)\"\s*(.*?)})\"(\s*xml:lang\=\"({get_abbreviation\s*(.*?)\s*value\=\"lang\"})\"|)>)(<a href\=\"(.*?)\">|)(.*?)(</a>|)(</abbr>)=",
						 			$_value['content_raw'],
									$matches,
									PREG_SET_ORDER);			
				
				foreach ($matches as $_key_match => $_value_match) {
					
					// abbreviation Id
					$abbreviation_id = $_value_match[6];
					
					// preg all result set
					$search = $_value_match[0];				
				
					// get abbreviation
					$abbreviation = $ABBREVIATION->selectAbbreviation($abbreviation_id);
					
					// predefine patterns
					// match contains glossary and lang values
					$pattern_active_glossary_lang = sprintf('<abbr lang="{get_abbreviation id="%d" value="lang"}" title="{get_abbreviation id="%d" value="long_form"}" xml:lang="{get_abbreviation id="%d" value="lang"}"><a href="{get_url page_id="%d"}#{get_abbreviation id="%d" value="name"}" title="{get_abbreviation id="%d" value="long_form"}">{get_abbreviation id="%d" value="name"}</a></abbr>', $abbreviation_id, $abbreviation_id, $abbreviation_id, $_page_id, $abbreviation_id, $abbreviation_id, $abbreviation_id);
					
					// match contains glossary and but no lang values
					$pattern_active_glossary = sprintf('<abbr title="{get_abbreviation id="%d" value="long_form"}"><a href="{get_url page_id="%d"}#{get_abbreviation id="%d" value="name"}" title="{get_abbreviation id="%d" value="long_form"}">{get_abbreviation id="%d" value="name"}</a></abbr>', $abbreviation_id, $_page_id, $abbreviation_id, $abbreviation_id, $abbreviation_id);
					
					// match contains no glossary but lang values
					$pattern_nonactive_glossary_lang = sprintf('<abbr lang="{get_abbreviation id="%d" value="lang"}" title="{get_abbreviation id="%d" value="long_form"}" xml:lang="{get_abbreviation id="%d" value="lang"}">{get_abbreviation id="%d" value="name"}</abbr>', $abbreviation_id, $abbreviation_id, $abbreviation_id, $abbreviation_id);
					
					// match contains no glossary and no lang values
					$pattern_nonactive_glossary = sprintf('<abbr title="{get_abbreviation id="%d" value="long_form"}">{get_abbreviation id="%d" value="name"}</abbr>', $abbreviation_id, $abbreviation_id);
					
					// process only abbreviations with a glossary entry
					if (!empty($abbreviation['content_raw']) && $_apply_page == 1) {						
						if (!empty($abbreviation['lang'])) {							
							$_value['content_raw'] = str_replace($search, $pattern_active_glossary_lang, $_value['content_raw']);
						} else {
							$_value['content_raw'] = str_replace($search, $pattern_active_glossary, $_value['content_raw']);
						}
					}
										
					// process only abbreviations with no glossary entry
					// but with checked glossar applying
					elseif (empty($abbreviation['content_raw']) && $_apply_page == 1) {		
						if (!empty($abbreviation['lang'])) {
							$_value['content_raw'] = str_replace($search, $pattern_nonactive_glossary_lang, $_value['content_raw']);
						} else {							
							$_value['content_raw'] = str_replace($search, $pattern_nonactive_glossary, $_value['content_raw']);
						}
					}

					// process only abbreviations with no glossary entry
					// and  unchecked glossar applying
					elseif (empty($abbreviation['content_raw']) && $_apply_page != 1) {		
						if (!empty($abbreviation['lang'])) {
							$_value['content_raw'] = str_replace($search, $pattern_nonactive_glossary_lang, $_value['content_raw']);
						} else {							
							$_value['content_raw'] = str_replace($search, $pattern_nonactive_glossary, $_value['content_raw']);
						}
					}
										
					// assign content raw to sql data array
					 $sqlData['content_raw'] = $_value['content_raw'];
										
					// apply text macros and text converter if required
					if ($_value['text_converter'] > 0 || $_value['apply_macros'] > 0) {
											
						// extract content
						$content = $sqlData['content_raw'];
											
						// apply startup and pre text converter text macros 
						if ($_value['apply_macros'] > 0) {
							$content = $TEXTMACRO->applyTextMacros($content, 'pre');
						}
											
						// apply text converter
						if ($_value['text_converter'] > 0) {
							$content = $TEXTCONVERTER->applyTextConverter(
								$_value['text_converter'],
								$content
							);
						}
											
						// apply post text converter and shutdown text macros 
						if ($_value['apply_macros'] > 0) {
							$content = $TEXTMACRO->applyTextMacros($content, 'post');
						}
											
						// assign content to sql data array
						$sqlData['content'] = $content;
											
					} // text converter eof
									
					
					// test sql data for pear errors
					$HELPER->testSqlDataForPearErrors($sqlData);		
					
					// insert it
					try {
						// begin transaction
						$BASE->db->begin();
					
						// execute operation
						$_class_reference->$classFunc['1']($_value['id'], $sqlData);
					
						// commit
						$BASE->db->commit();
					} catch (Exception $e) {
						// do rollback
						$BASE->db->rollback();
					
						// re-throw exception
						throw $e;
					}
					
				} // foreach match eof
				
			} // !is_null($_value['content_raw']
	
		} // foreach eof
	
	} // foreach eof
	
	// clean buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}

	// redirect
	header("Location: actions_select.php");
	exit;

} catch (Exception $e) {
	// clean buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}

	// raise error, print inline
	print '<div id="error" class="inline">';
	print '<h1>'.gettext('An error occured').'</h1>';
	print '<h2>'.gettext('Welcompose says').':</h2>';
	print '<p>';
	$BASE->error->printExceptionMessage($e);
	print '</p>';
	print '</div>';

	$BASE->error->triggerException($e);

	// exit
	exit;
}
?>