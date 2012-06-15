<?php

/**
 * Project: Welcompose
 * File: login.php
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
	$smarty_admin_conf = dirname(__FILE__).'/../core/conf/smarty_admin.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_admin_conf), true);
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../core/includes/gettext.inc.php';
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

	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('login');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');
	
	// textfield for email
	$email = $FORM->addElement('text', 'email', 
		array('id' => 'user_login', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('E-mail address'))
		);						
	$email->addRule('required', gettext('Please enter your e-mail address'));
	
	// password for secret
	$secret = $FORM->addElement('password', 'secret', 
		array('id' => 'user_secret', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Password'))
		);
	$secret->addRule('required', gettext('Please enter your password'));
	$secret->addRule('callback', gettext('Invalid password'), 
		array(
			'callback' => array($LOGIN, 'testSecret'),
			'arguments' => array($email->getValue())
		)
	);
			
	// submit button
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'submit200', 'value' => gettext('Login'))
		);
	
	// validate it
	if (!$FORM->validate()) {

		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();

		// fetch {function} template to set
		// required/error markup on each form fields
		$BASE->utility->smarty->fetch(dirname(__FILE__).'/quickform.tpl');
		
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $FORM->render($renderer)->toArray());
		
		// assign paths
		$BASE->utility->smarty->assign('wcom_admin_root_www',
			$BASE->_conf['path']['wcom_admin_root_www']);
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('login.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {		
		// freeze the form
		$FORM->toggleFrozen(true);
		
		// load login class
		$LOGIN = load('user:login');
		
		// log into admin area
		$LOGIN->logIntoAdmin($email->getValue(), $secret->getValue());
		
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: content/pages_select.php");
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