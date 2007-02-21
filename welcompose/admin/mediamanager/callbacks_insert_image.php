<?php

/**
 * Project: Welcompose
 * File: callbacks_insert_image.php
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
	
	// start Base_Session
	$SESSION = load('Base:Session');
	
	// load User_User
	$USER = load('User:User');
	
	// load User_Login
	$LOGIN = load('User:Login');
	
	// load Application_Project
	$PROJECT = load('Application:Project');
	
	// load Media_Object
	$OBJECT = load('Media:Object');
	
	// load Application_TextConverter
	$TEXTCONVERTER = load('Application:TextConverter');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Exception("Access denied");
	}
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('insert_image', 'post');
	
	// hidden for object id
	$FORM->addElement('hidden', 'id');
	$FORM->applyFilter('id', 'trim');
	$FORM->applyFilter('id', 'strip_tags');
	
	// hidden for text converter id
	$FORM->addElement('hidden', 'text_converter');
	$FORM->applyFilter('text_converter', 'trim');
	$FORM->applyFilter('text_converter', 'strip_tags');
	
	// hidden for text
	$FORM->addElement('hidden', 'text');
	$FORM->applyFilter('text', 'htmlentities');
	
	// hidden field for pager_page
	$FORM->addElement('hidden', 'form_target');
	
	// textfield for name
	$FORM->addElement('text', 'alt', gettext('Alternative text'), 
		array('id' => 'alt', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('alt', 'trim');
	$FORM->applyFilter('alt', 'strip_tags');
	
	// textfield for name
	$FORM->addElement('text', 'title', gettext('Title'), 
		array('id' => 'title', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('title', 'trim');
	$FORM->applyFilter('title', 'strip_tags');
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Insert object'),
		array('class' => 'submit200insertcallback'));

	// reset button
	$FORM->addElement('reset', 'reset', gettext('Cancel'),
		array('class' => 'close200'));
		
	// set defaults
	$FORM->setDefaults(array(
		'id' => Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC),
		'text_converter' => Base_Cnc::filterRequest($_REQUEST['text_converter'], WCOM_REGEX_NUMERIC),
		'text' => Base_Cnc::ifsetor($_REQUEST['text'], null),
		'form_target' => Base_Cnc::filterRequest($_REQUEST['form_target'], WCOM_REGEX_CSS_IDENTIFIER)
	));
	
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
	
		// assign form target
		$BASE->utility->smarty->assign('form_target', Base_Cnc::filterRequest($_REQUEST['form_target'], WCOM_REGEX_CSS_IDENTIFIER));
		
		// assign current user and project id
		$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);

		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('mediamanager/callbacks_insert_image.html', WCOM_TEMPLATE_KEY);
		
	} else {
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
	
		// assign form target
		$BASE->utility->smarty->assign('form_target', Base_Cnc::filterRequest($_REQUEST['form_target'], WCOM_REGEX_CSS_IDENTIFIER));
		
		// assign current user and project id
		$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);

		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('mediamanager/callbacks_insert_image.html', WCOM_TEMPLATE_KEY);
			
		// get object
		$object = $OBJECT->selectObject(intval($FORM->exportValue('id')));
	
		// prepare callback args
		$args = array(
			'text' => $FORM->exportValue('text'),
			'src' => sprintf('{get_media id="%u"}', $object['id']),
			'width' => $object['file_width'],
			'height' => $object['file_height'],
			'alt' => $FORM->exportValue('alt'),
			'title' => $FORM->exportValue('title')
		);
	
		// execute text converter callback
		$text_converter = (int)$FORM->exportValue('text_converter');
		$callback_media_result = $TEXTCONVERTER->insertCallback($text_converter, 'Image', $args);	
		
		// assign callback build
		$BASE->utility->smarty->assign('callback_media_result', $callback_media_result);
	
		// assign current user and project id
		$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);

		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('mediamanager/callbacks_insert_image.html', WCOM_TEMPLATE_KEY);
	}

	// flush the buffer
	@ob_end_flush();
	
	exit;
} catch (Exception $e) {
	// clean the buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	$BASE->error->displayException($e, $BASE->utility->smarty, 'error_popup_723.html');
	$BASE->error->triggerException($e);

	// exit
	exit;
}

?>