<?php

/**
 * Project: Welcompose
 * File: callbacks_insert_image.php
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
 * @author Andreas Ahlenstorf
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
	$qualities = array(
		'' => gettext('Default'),
		'low' => gettext('Low'),
		'high' => gettext('High'),
		'autolow' => gettext('Auto low'),
		'autohigh' => gettext('Auto high')
	);
	
	// prepare scale array
	$scales = array(
		'' => gettext('Default'),
		'showall' => gettext('Show all'),
		'noborder' => gettext('No border'),
		'exactfit' => gettext('Exact fit')
	);
	
	// prepare wmode array
	$wmodes = array(
		'' => gettext('Default'),
		'window' => gettext('Window'),
		'opaque' => gettext('Opaque'),
		'transparent' => gettext('Transparent')
	);
			
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('insert_x-shockwave-flash');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');
	
	// hidden for object id
	$id = $FORM->addElement('hidden', 'id', array('id' => 'id'));
	$id->addRule('required', gettext('Id is not expected to be empty'));
	$id->addRule('regex', gettext('Id is expected to be numeric'), WCOM_REGEX_NUMERIC);
	
	// hidden for text converter id
	$text_converter = $FORM->addElement('hidden', 'text_converter', array('id' => 'text_converter'));

	// hidden for text
	$text = $FORM->addElement('hidden', 'text', array('id' => 'text'));
	$text = $FORM->addFilter('htmlentities');
	
	// hidden field for pager_page
	$form_target = $FORM->addElement('hidden', 'form_target', array('id' => 'form_target'));

	// select for param quality
	$quality = $FORM->addElement('select', 'quality',
	 	array('id' => 'quality'),
		array('label' => gettext('Quality'), 'options' => $qualities)
		);
		
	// select for param scale
	$scale = $FORM->addElement('select', 'scale',
	 	array('id' => 'scale'),
		array('label' => gettext('Scale'), 'options' => $scales)
		);

	// select for param wmode
	$wmode = $FORM->addElement('select', 'wmode',
	 	array('id' => 'wmode'),
		array('label' => gettext('WMode'), 'options' => $wmodes)
		);

	// textfield for param bgcolor
	$bgcolor = $FORM->addElement('text', 'bgcolor', 
		array('id' => 'bgcolor', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Background color'))
		);
	$bgcolor->addRule('regex', gettext('Please use a valid hexadezimal syntax'), WCOM_REGEX_HEXADEZIMAL);
	
	// checkbox for param play
	$play = $FORM->addElement('checkbox', 'play',
		array('id' => 'play', 'class' => 'chbx'),
		array('label' => gettext('Avoid instant playing'))
	);

	// checkbox for param loop
	$loop = $FORM->addElement('checkbox', 'loop',
		array('id' => 'loop', 'class' => 'chbx'),
		array('label' => gettext('Avoid looping'))
	);
	
	// checkbox to insert shockwave as reference only
	$insert_as_reference = $FORM->addElement('checkbox', 'insert_as_reference',
		array('id' => 'insert_as_reference', 'class' => 'chbx'),
		array('label' => gettext('Insert as reference'))
	);
	$insert_as_reference->addRule('regex', gettext('The field whether to insert as reference accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
	
	// submit button
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'submit200insertcallback', 'value' => gettext('Insert object'))
		);

	// reset button
	$reset = $FORM->addElement('reset', 'reset', 
		array('class' => 'close140', 'value' => gettext('Cancel'))
		);
		
	// set defaults
	$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
		'id' => Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC),
		'text_converter' => Base_Cnc::filterRequest($_REQUEST['text_converter'], WCOM_REGEX_NUMERIC),
		'text' => Base_Cnc::ifsetor($_REQUEST['text'], null),
		'form_target' => Base_Cnc::filterRequest($_REQUEST['form_target'], WCOM_REGEX_CSS_IDENTIFIER)
	)));	
			
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
			
		// assign target field identifier
		$BASE->utility->smarty->assign('form_target', Base_Cnc::filterRequest($_REQUEST['form_target'], WCOM_REGEX_CSS_IDENTIFIER));
		
		// assign current user and project id
		$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);

		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('mediamanager/callbacks_insert_application-x-shockwave-flash.html', WCOM_TEMPLATE_KEY);

	} else {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();
	
		// remove attribute on form tag for XHTML compliance
		$FORM->removeAttribute('name');
		$FORM->removeAttribute('target');
	
		$FORM->accept($renderer);
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $FORM->render($renderer)->toArray());
	
		// assign paths
		$BASE->utility->smarty->assign('wcom_admin_root_www',
			$BASE->_conf['path']['wcom_admin_root_www']);
					
		// assign target field identifier
		$BASE->utility->smarty->assign('form_target', Base_Cnc::filterRequest($_REQUEST['form_target'], WCOM_REGEX_CSS_IDENTIFIER));

		// get object
		$object = $OBJECT->selectObject(intval($id->getValue()));

		// get text converter	
		$text_converter = (int)$text_converter->getValue();		
		
		// insert media as reference or full html
		if ((int)($insert_as_reference->getValue()) > 0) {			
			// define insert_type
			$insert_type = 'InternalReference';
			
			// prepare callback args
			$args = array(
				'text' => '',
				'href' => sprintf('{get_media id="%u"}', $object['id'])
			);		
		} else {
			// define insert_type
			$insert_type = 'Shockwave';
			
			// prepare callback args
			$args = array(
				'text' => $text->getValue(),
				'data' => sprintf('{get_media id="%u"}', $object['id']),
				'width' => $object['file_width'],
				'height' => $object['file_height'],
				'quality' => $quality->getValue(),
				'scale' => $scale->getValue(),
				'wmode' => $wmode->getValue(),
				'bgcolor' => $bgcolor->getValue(),
				'play' => $play->getValue(),
				'loop' => $loop->getValue()
			);
		}

		// execute text converter callback
		$callback_media_result = $TEXTCONVERTER->insertCallback($text_converter, $insert_type, $args);

		// assign callback build
		$BASE->utility->smarty->assign('callback_media_result', $callback_media_result);
		
		// assign current user and project id
		$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);

		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('mediamanager/callbacks_insert_application-x-shockwave-flash.html', WCOM_TEMPLATE_KEY);
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