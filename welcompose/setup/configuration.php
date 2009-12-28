<?php

/**
 * Project: Welcompose
 * File: configuration.php
 *
 * Copyright (c) 2008 creatics
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
 * @copyright 2008 creatics, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
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
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../core/includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);
	
	// start Base_Session
	/* @var $SESSION session */
	$SESSION = load('base:session');
	
	// let's see if the user passed step one
	if (empty($_SESSION['setup']['license_confirm_license']) || !$_SESSION['setup']['license_confirm_license']) {
		header("Location: license.php");
		exit;
	}
	if (empty($_SESSION['setup']['database_database']) || empty($_SESSION['setup']['database_user'])) {
		header("Location: database.php");
		exit;
	}
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('configuration', 'post');
	
	// textfield for project
	$FORM->addElement('text', 'project', gettext('Project'), 
		array('id' => 'configuration_project', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('project', 'trim');
	$FORM->applyFilter('project', 'strip_tags');
	$FORM->addRule('project', gettext('Please enter a project name'), 'required');
	
	// textfield for locale
	$FORM->addElement('text', 'locale', gettext('Locale'),
		array('id' => 'configuration_locale', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('locale', 'trim');
	$FORM->applyFilter('locale', 'strip_tags');
	$FORM->addRule('locale', gettext('Please enter a locale'), 'required');
	$FORM->addRule('locale', gettext('Please enter a valid locale'), 'regex',
		WCOM_REGEX_LOCALE_NAME);
	
	// add locale validation rule
	$FORM->registerRule('testLocale', 'callback', 'setup_locale_test_callback');
	$FORM->addRule('locale', gettext('Your chosen locale is not available on your system'), 'testLocale');
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Next step'),
		array('class' => 'submit240'));
		
	// set defaults
	$FORM->setDefaults(array(
		'locale' => 'de_DE'
	));
		
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
		$BASE->utility->smarty->display('configuration.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// save inputs to session
		$_SESSION['setup']['configuration_project'] = $FORM->exportValue('project');
		$_SESSION['setup']['configuration_locale'] = $FORM->exportValue('locale');
		
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: setup.php");
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

function setup_locale_test_callback ($locale) {
	
	// get current locale
	$current_locale = setlocale(LC_ALL, 0);
	
	// test if new locale can be used
	if (setlocale(LC_ALL, $locale) === false) {
		// reset locale
		setlocale(LC_ALL, $current_locale);
		
		return false;
	}
	
	// reset locale
	setlocale(LC_ALL, $current_locale);
	
	return true;
}

?>