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

	// prepare quality array
	$quality = array(
		'0' => gettext('Default'),
		'low' => gettext('Low'),
		'high' => gettext('High'),
		'autolow' => gettext('Auto low'),
		'autohigh' => gettext('Auto high')
	);
	
	// prepare scale array
	$scale = array(
		'0' => gettext('Default'),
		'showall' => gettext('Show all'),
		'noborder' => gettext('No border'),
		'exactfit' => gettext('Exact fit')
	);
	
	// prepare wmode array
	$wmode = array(
		'0' => gettext('Default'),
		'window' => gettext('Window'),
		'opaque' => gettext('Opaque'),
		'transparent' => gettext('Transparent')
	);
			
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('insert_x-shockwave-flash', 'post');
	
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

	// select for param quality
	$FORM->addElement('select', 'quality', gettext('Quality'), $quality,
		array('id' => 'quality'));
	$FORM->applyFilter('quality', 'trim');
	$FORM->applyFilter('quality', 'strip_tags');
	
	// select for param scale
	$FORM->addElement('select', 'scale', gettext('Scale'), $scale,
		array('id' => 'scale'));
	$FORM->applyFilter('scale', 'trim');
	$FORM->applyFilter('scale', 'strip_tags');
	
	// select for param wmode
	$FORM->addElement('select', 'wmode', gettext('WMode'), $wmode,
		array('id' => 'wmode'));
	$FORM->applyFilter('wmode', 'trim');
	$FORM->applyFilter('wmode', 'strip_tags');
	
	// textfield for param bgcolor
	$FORM->addElement('text', 'bgcolor', gettext('Background color'), 
		array('id' => 'bgcolor', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('bgcolor', 'trim');
	$FORM->applyFilter('bgcolor', 'strip_tags');
	
	// checkbox for param play
	$FORM->addElement('checkbox', 'play', gettext('Avoid instant 
	playing'), null,
		array('id' => 'play', 'class' => 'chbx'));
	$FORM->applyFilter('play', 'trim');
	$FORM->applyFilter('play', 'strip_tags');
	
	// checkbox for param loop
	$FORM->addElement('checkbox', 'loop', gettext('Avoid looping'), null,
		array('id' => 'loop', 'class' => 'chbx'));
	$FORM->applyFilter('loop', 'trim');
	$FORM->applyFilter('loop', 'strip_tags');
	
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
		'text' => Base_Cnc::filterRequest($_REQUEST['text'], WCOM_REGEX_NUMERIC),
		'form_target' => Base_Cnc::filterRequest($_REQUEST['form_target'], WCOM_REGEX_CSS_IDENTIFIER)
	));
		
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
	
	// assign target field identifier
	$BASE->utility->smarty->assign('form_target', Base_Cnc::filterRequest($_REQUEST['form_target'], WCOM_REGEX_CSS_IDENTIFIER));
	
	/*
	 * execute text converter callback if request method ist post
	 */
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	// debug
	print_r($_POST);
	
	/*	// get object
		$object = $OBJECT->selectObject(intval($FORM->exportValue('id')));
	
		// prepare callback args
		$args = array(
			'text' => $FORM->exportValue('text'),
			'src' => sprintf('{get_media id="%u"}', $object['id']),
			'width' => $object['file_width'],
			'height' => $object['file_height'],
			'alt' => $FORM->exportValue('alt'),
			'title' => $FORM->exportValue('title'),
			'longdesc' => $FORM->exportValue('longdesc')
		);
	
		// execute text converter callback
		$text_converter = (int)$FORM->exportValue('text_converter');
		$callback_result = $TEXTCONVERTER->insertCallback($text_converter, 'Image', $args);	
		
		// assign target field identifier
		$BASE->utility->smarty->assign('callback_result', $callback_result);
		
	*/
	}
	
	// assign current user and project id
	$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);

	// display the form
	define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
	$BASE->utility->smarty->display('mediamanager/callbacks_insert_x-shockwave-flash.html', WCOM_TEMPLATE_KEY);

	// flush the buffer
	@ob_end_flush();
	
	exit;
} catch (Exception $e) {
	// clean the buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}

	// define new error_tpl
	Base_Error::$_error_tpl = 'error_popup_723.html';
	
	// raise error
	Base_Error::triggerException($BASE->utility->smarty, $e);	

	// exit
	exit;
}

?>