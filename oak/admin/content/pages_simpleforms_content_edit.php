<?php

/**
 * Project: Oak
 * File: pages_simpleforms_content_edit.php
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
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

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
	
	// load project class
	/* @var $PROJECT Application_Project */
	$PROJECT = load('application:project');
	
	// load page class
	/* @var $PAGE Content_Page */
	$PAGE = load('content:page');
	
	// load simpleform class
	/* @var $SIMPLEFORM Content_Simpleform */
	$SIMPLEFORM = load('content:simpleform');
	
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
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(OAK_CURRENT_USER);
	
	// get page
	$page = $PAGE->selectPage(Base_Cnc::filterRequest($_REQUEST['id'], OAK_REGEX_NUMERIC));
	
	// get simple form
	$simple_form = $SIMPLEFORM->selectSimpleForm(Base_Cnc::filterRequest($_REQUEST['id'], OAK_REGEX_NUMERIC));
	
	// prepare form types array
	$types = array(
		'business' => gettext('Business form'),
		'personal' => gettext('Personal form')
	);
	
	// prepare text converters array
	$text_converters = array(
		'' => gettext('None')
	);
	foreach ($TEXTCONVERTER->selectTextConverters() as $_converter) {
		$text_converters[(int)$_converter['id']] = htmlspecialchars($_converter['name']);
	}
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('simple_form', 'post');
	
	// hidden for navigation
	$FORM->addElement('hidden', 'id');
	$FORM->applyFilter('id', 'trim');
	$FORM->applyFilter('id', 'strip_tags');
	$FORM->addRule('id', gettext('Id is not expected to be empty'), 'required');
	$FORM->addRule('id', gettext('Id is expected to be numeric'), 'numeric');
	
	// textfield for title
	$FORM->addElement('text', 'title', gettext('Title'),
		array('id' => 'simple_form_title', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('title', 'trim');
	$FORM->applyFilter('title', 'strip_tags');
	$FORM->addRule('title', gettext('Please enter a title'), 'required');
	
	// textarea for content
	$FORM->addElement('textarea', 'content', gettext('Content'),
		array('id' => 'simple_form_content', 'cols' => 3, 'rows' => '2', 'class' => 'w540h400'));
	$FORM->applyFilter('content', 'trim');
	
	// select for text_converter
	$FORM->addElement('select', 'text_converter', gettext('Text converter'), $text_converters,
		array('id' => 'simple_form_text_converter'));
	$FORM->applyFilter('text_converter', 'trim');
	$FORM->applyFilter('text_converter', 'strip_tags');
	$FORM->addRule('text_converter', gettext('Chosen text converter is out of range'),
		'in_array_keys', $text_converters);
	
	// checkbox for apply_macros
	$FORM->addElement('checkbox', 'apply_macros', gettext('Apply text macros'), null,
		array('id' => 'simple_form_apply_macros', 'class' => 'chbx'));
	$FORM->applyFilter('apply_macros', 'trim');
	$FORM->applyFilter('apply_macros', 'strip_tags');
	$FORM->addRule('apply_macros', gettext('The field whether to apply text macros accepts only 0 or 1'),
		'regex', OAK_REGEX_ZERO_OR_ONE);
	
	// select for type
	$FORM->addElement('select', 'type', gettext('Form type'), $types,
		array('id' => 'simple_form_type'));
	$FORM->applyFilter('type', 'trim');
	$FORM->applyFilter('type', 'strip_tags');
	$FORM->addRule('type', gettext('Chosen form type is out of range'),
		'in_array_keys', $types);
	
	// textfield for email_from
	$FORM->addElement('text', 'email_from', gettext('From: address'),
		array('id' => 'simple_form_email_from', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('email_from', 'trim');
	$FORM->applyFilter('email_from', 'strip_tags');
	$FORM->addRule('email_from', gettext('Please enter a From: address'), 'required');
	$FORM->addRule('email_from', gettext('Please enter a valid From: address'), 'email');
	
	// textfield for email_to
	$FORM->addElement('text', 'email_to', gettext('To: address'),
		array('id' => 'simple_form_email_to', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('email_to', 'trim');
	$FORM->applyFilter('email_to', 'strip_tags');
	$FORM->addRule('email_to', gettext('Please enter a To: address'), 'required');
	$FORM->addRule('email_to', gettext('Please enter a valid To: address'), 'email');
	
	// textfield for email_subject
	$FORM->addElement('text', 'email_subject', gettext('Subject'),
		array('id' => 'simple_form_email_subject', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('email_subject', 'trim');
	$FORM->applyFilter('email_subject', 'strip_tags');
	$FORM->addRule('email_subject', gettext('Please enter a subject'), 'required');
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Update form'),
		array('class' => 'submitbut140'));
	
	// set defaults
	$FORM->setDefaults(array(
		'id' => Base_Cnc::ifsetor($simple_form['id'], null),
		'title' => Base_Cnc::ifsetor($simple_form['title'], null),
		'content' => Base_Cnc::ifsetor($simple_form['content_raw'], null),
		'text_converter' => Base_Cnc::ifsetor($simple_form['text_converter'], null),
		'apply_macros' => Base_Cnc::ifsetor($simple_form['apply_macros'], null),
		'type' => Base_Cnc::ifsetor($simple_form['type'], null),
		'email_from' => Base_Cnc::ifsetor($simple_form['email_from'], null),
		'email_to' => Base_Cnc::ifsetor($simple_form['email_to'], null),
		'email_subject' => Base_Cnc::ifsetor($simple_form['email_subject'], null)
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
		$BASE->utility->smarty->assign('oak_admin_root_www',
			$BASE->_conf['path']['oak_admin_root_www']);
		
		// build $session
	    $session = array(
			'response' => Base_Cnc::filterRequest($_SESSION['response'], OAK_REGEX_NUMERIC)
	    );
	    
	    // assign $_SESSION to smarty
	    $BASE->utility->smarty->assign('session', $session);
	    
	    // empty $_SESSION
	    if (!empty($_SESSION['response'])) {
	        $_SESSION['response'] = '';
	    }
	    
		// assign current user and project id
		$BASE->utility->smarty->assign('oak_current_user', OAK_CURRENT_USER);
		$BASE->utility->smarty->assign('oak_current_project', OAK_CURRENT_PROJECT);
		
		// assign page
		$BASE->utility->smarty->assign("page", $page);
		
		// select available projects
		$select_params = array(
			'user' => OAK_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// display the form
		define("OAK_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('content/pages_simpleforms_content_edit.html', OAK_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// prepare sql data
		$sqlData = array();
		$sqlData['title'] = $FORM->exportValue('title');
		$sqlData['title_url'] = $HELPER->createMeaningfulString($FORM->exportValue('title'));
		$sqlData['content_raw'] = $FORM->exportValue('content');
		$sqlData['content'] = $FORM->exportValue('content');
		$sqlData['text_converter'] = ($FORM->exportValue('text_converter') > 0) ? 
			$FORM->exportValue('text_converter') : null;
		$sqlData['apply_macros'] = (string)intval($FORM->exportValue('apply_macros'));
		$sqlData['type'] = $FORM->exportValue('type');
		$sqlData['email_from'] = $FORM->exportValue('email_from');
		$sqlData['email_to'] = $FORM->exportValue('email_to');
		$sqlData['email_subject'] = $FORM->exportValue('email_subject');
		
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
			
			// assign content to sql data array
			$sqlData['content'] = $content;
		}
		
		// test sql data for pear errors
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$SIMPLEFORM->updateSimpleForm($FORM->exportValue('id'), $sqlData);
			
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();
			
			// re-throw exception
			throw $e;
		}
		
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: pages_select.php");
		exit;
	}
} catch (Exception $e) {
	// clean the buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	Base_Error::triggerException($BASE->utility->smarty, $e);	
	
	// exit
	exit;
}
?>