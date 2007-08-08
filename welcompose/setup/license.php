<?php

/**
 * Project: Welcompose
 * File: license.php
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

// get loader
$path_parts = array(
	dirname(__FILE__),
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
$deregister_globals_path = dirname(__FILE__).'/../core/includes/deregister_globals.inc.php';
require(Base_Compat::fixDirectorySeparator($deregister_globals_path));

try {
	// start output buffering
	@ob_start();
	
	// load smarty
	$smarty_update_conf = dirname(__FILE__).'/smarty.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_update_conf), true);
	
	// start Base_Session
	/* @var $SESSION session */
	$SESSION = load('base:session');
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('license', 'post');
	
	// checkbox for confirm_license
	$FORM->addElement('checkbox', 'confirm_license', 'Confirm license', null,
		array('id' => 'license_confirm_license', 'class' => 'chbx'));
	$FORM->applyFilter('confirm_license', 'trim');
	$FORM->applyFilter('confirm_license', 'strip_tags');
	$FORM->addRule('confirm_license', 'Please confirm the license', 'required');
	$FORM->addRule('confirm_license', 'The field to cofirm the license accepts only 0 or 1',
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// submit button
	$FORM->addElement('submit', 'submit', 'Go to next step',
		array('class' => 'submit200nomargin'));
		
	// validate it
	if (!$FORM->validate()) {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();
		$quickform_tpl_path = dirname(__FILE__).'/quickform.tpl.php';
		include(Base_Compat::fixDirectorySeparator($quickform_tpl_path));

		// remove attribute on form tag for XHTML compliance
		$FORM->removeAttribute('name');
		$FORM->removeAttribute('target');
		
		$FORM->accept($renderer);
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $renderer->toArray());
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('license.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// save input to session
		$_SESSION['setup'] = array();
		$_SESSION['setup']['license_confirm_license'] = true;
	
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: requirements.php");
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