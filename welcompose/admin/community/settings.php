<?php

/**
 * Project: Welcompose
 * File: antispamplugins_add.php
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
	/* @var $SESSION session */
	$SESSION = load('Base:Session');
	
	// load User_User
	/* @var $USER User_User */
	$USER = load('User:User');
	
	// load User_Login
	/* @var $LOGIN User_Login */
	$LOGIN = load('User:Login');
	
	// load Application_Project
	/* @var $PROJECT Application_Project */
	$PROJECT = load('Application:Project');
	
	// load Community_Settings
	$SETTINGS = load('Community:Settings');
	
	// load Community_BlogCommentStatus
	$BLOGCOMMENTSTATUS = load('Community:BlogCommentStatus');
	
	// load Application_TextConverter
	/* @var $TEXTCONVERTER Application_Textconverter */
	$TEXTCONVERTER = load('Application:TextConverter');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Community', 'Settings', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
	// get settings
	$settings = $SETTINGS->getSettings();
	
	// prepare captcha types array
	$captcha_types = array(
		'no' => gettext('Disable captcha'),
		'image' => gettext('Use image captcha'),
		'numeral' => gettext('Use numeral captcha')
	);
	
	// get Blog Comment Status
	$blog_comment_statuses = array(
		'' => gettext('None')
	);
	foreach ($BLOGCOMMENTSTATUS->selectBlogCommentStatuses() as $_status) {
		$blog_comment_statuses[(int)$_status['id']] = htmlspecialchars($_status['name']);
	}
	
	// get trackback statuses
	$blog_trackback_statuses = array(
		'' => gettext('None')	
	);
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('community_settings');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');
	
	// Comments
		
	// select for comment display status
	$blog_comment_display_status = $FORM->addElement('select', 'blog_comment_display_status',
	 	array('id' => 'community_settings_blog_comment_display_status'),
		array('label' => gettext('Display status'), 'options' => $blog_comment_statuses)
		);
	
	// select for comment default status
	$blog_comment_default_status = $FORM->addElement('select', 'blog_comment_default_status',
	 	array('id' => 'community_settings_blog_comment_default_status'),
		array('label' => gettext('Default status'), 'options' => $blog_comment_statuses)
		);
	
	// select for comment spam status
	$blog_comment_spam_status = $FORM->addElement('select', 'blog_comment_spam_status',
	 	array('id' => 'community_settings_blog_comment_spam_status'),
		array('label' => gettext('Spam status'), 'options' => $blog_comment_statuses)
		);
	
	// select for comment ham status
	$blog_comment_ham_status = $FORM->addElement('select', 'blog_comment_ham_status',
	 	array('id' => 'community_settings_blog_comment_ham_status'),
		array('label' => gettext('Ham status'), 'options' => $blog_comment_statuses)
		);
	
	// select for wether to use captcha or not
	$blog_comment_use_captcha = $FORM->addElement('select', 'blog_comment_use_captcha',
	 	array('id' => 'community_settings_blog_comment_use_captcha'),
		array('label' => gettext('Use captcha'), 'options' => $captcha_types)
		);
	
	// textfield for timeframe threshold
	$blog_comment_timeframe_threshold = $FORM->addElement('text', 'blog_comment_timeframe_threshold', 
		array('id' => 'community_settings_blog_comment_timeframe_threshold', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Timeframe threshold'))
		);
	$blog_comment_timeframe_threshold->addRule('required', gettext('Please enter a comment timeframe threshold'));
	$blog_comment_timeframe_threshold->addRule('regex', gettext('Please enter a numeric comment timeframe threshold'), WCOM_REGEX_NUMERIC);
	
	// checkbox for enable autolearn
	$blog_comment_bayes_autolearn = $FORM->addElement('checkbox', 'blog_comment_bayes_autolearn',
		array('id' => 'community_settings_blog_comment_bayes_autolearn', 'class' => 'chbx'),
		array('label' => gettext('Enable Bayes autolearning'))
		);
	$blog_comment_bayes_autolearn->addRule('regex', gettext('The field whether autolearning is enabled accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
	
	// textfield for bayes autolearn threshold
	$blog_comment_bayes_autolearn_threshold = $FORM->addElement('text', 'blog_comment_bayes_autolearn_threshold', 
		array('id' => 'community_settings_blog_comment_bayes_autolearn_threshold', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Bayes autolearn threshold'))
		);
	$blog_comment_bayes_autolearn_threshold->addRule('required', gettext('Please enter a comment bayes autolearn threshold'));
	$blog_comment_bayes_autolearn_threshold->addRule('regex', gettext('Please enter a numeric comment bayes autolearn threshold'), WCOM_REGEX_BAYES);

	// textfield for bayes spam threshold
	$blog_comment_bayes_spam_threshold = $FORM->addElement('text', 'blog_comment_bayes_spam_threshold', 
		array('id' => 'community_settings_blog_comment_bayes_spam_threshold', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Bayes spam threshold'))
		);
	$blog_comment_bayes_spam_threshold->addRule('required', gettext('Please enter a comment bayes spam threshold'));
	$blog_comment_bayes_spam_threshold->addRule('regex', gettext('Please enter a numeric comment bayes spam threshold'), WCOM_REGEX_BAYES);
	
	// select for comment text converter
	$blog_comment_text_converter = $FORM->addElement('select', 'blog_comment_text_converter',
	 	array('id' => 'community_settings_blog_comment_text_converter'),
		array('label' => gettext('Text converter to apply'), 'options' => $TEXTCONVERTER->getTextConverterListForForm())
		);
		
		// Trackbacks
		
	// select for trackback display status
	$blog_trackback_display_status = $FORM->addElement('select', 'blog_trackback_display_status',
	 	array('id' => 'community_settings_blog_trackback_display_status'),
		array('label' => gettext('Display status'), 'options' => $blog_trackback_statuses)
		);
	
	// select for trackback default status
	$blog_trackback_default_status = $FORM->addElement('select', 'blog_trackback_default_status',
	 	array('id' => 'community_settings_blog_trackback_default_status'),
		array('label' => gettext('Default status'), 'options' => $blog_trackback_statuses)
		);
	
	// select for trackback spam status
	$blog_trackback_spam_status = $FORM->addElement('select', 'blog_trackback_spam_status',
	 	array('id' => 'community_settings_blog_trackback_spam_status'),
		array('label' => gettext('Spam status'), 'options' => $blog_trackback_statuses)
		);
	
	// select for trackback ham status
	$blog_trackback_ham_status = $FORM->addElement('select', 'blog_trackback_ham_status',
	 	array('id' => 'community_settings_blog_trackback_ham_status'),
		array('label' => gettext('Ham status'), 'options' => $blog_trackback_statuses)
		);
		
			
	// textfield for timeframe threshold
	$blog_trackback_timeframe_threshold = $FORM->addElement('text', 'blog_trackback_timeframe_threshold', 
		array('id' => 'community_settings_blog_trackback_timeframe_threshold', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Timeframe threshold'))
		);
	$blog_trackback_timeframe_threshold->addRule('required', gettext('Please enter a trackback timeframe threshold'));
	$blog_trackback_timeframe_threshold->addRule('regex', gettext('Please enter a numeric trackback timeframe threshold'), WCOM_REGEX_NUMERIC);
	
	// checkbox for enable autolearn
	$blog_trackback_bayes_autolearn = $FORM->addElement('checkbox', 'blog_trackback_bayes_autolearn',
		array('id' => 'community_settings_blog_trackback_bayes_autolearn', 'class' => 'chbx'),
		array('label' => gettext('Enable Bayes autolearning'))
		);
	$blog_trackback_bayes_autolearn->addRule('regex', gettext('The field whether autolearning is enabled accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
	
	// textfield for bayes autolearn threshold
	$blog_trackback_bayes_autolearn_threshold = $FORM->addElement('text', 'blog_trackback_bayes_autolearn_threshold', 
		array('id' => 'community_settings_blog_trackback_bayes_autolearn_threshold', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Bayes autolearn threshold'))
		);
	$blog_trackback_bayes_autolearn_threshold->addRule('required', gettext('Please enter a trackback bayes autolearn threshold'));
	$blog_trackback_bayes_autolearn_threshold->addRule('regex', gettext('Please enter a numeric trackback bayes autolearn threshold'), WCOM_REGEX_BAYES);

	// textfield for bayes spam threshold
	$blog_trackback_bayes_spam_threshold = $FORM->addElement('text', 'blog_trackback_bayes_spam_threshold', 
		array('id' => 'community_settings_blog_trackback_bayes_spam_threshold', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Bayes spam threshold'))
		);
	$blog_trackback_bayes_spam_threshold->addRule('required', gettext('Please enter a trackback bayes spam threshold'));
	$blog_trackback_bayes_spam_threshold->addRule('regex', gettext('Please enter a numeric trackback bayes spam threshold'), WCOM_REGEX_BAYES);
	
	// submit button
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'submit240bez260', 'value' => gettext('Save edit'))
		);
	
	// set defaults
	$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
		'blog_comment_display_status' => Base_Cnc::ifsetor($settings['blog_comment_display_status'], null),
		'blog_comment_default_status' => Base_Cnc::ifsetor($settings['blog_comment_default_status'], null),
		'blog_comment_spam_status' => Base_Cnc::ifsetor($settings['blog_comment_spam_status'], null),
		'blog_comment_ham_status' => Base_Cnc::ifsetor($settings['blog_comment_ham_status'], null),
		'blog_comment_use_captcha' => Base_Cnc::ifsetor($settings['blog_comment_use_captcha'], null),
		'blog_comment_timeframe_threshold' => Base_Cnc::ifsetor($settings['blog_comment_timeframe_threshold'], null),
		'blog_comment_bayes_autolearn' => Base_Cnc::ifsetor($settings['blog_comment_bayes_autolearn'], null),
		'blog_comment_bayes_autolearn_threshold' => Base_Cnc::ifsetor($settings['blog_comment_bayes_autolearn_threshold'], null),
		'blog_comment_bayes_spam_threshold' => Base_Cnc::ifsetor($settings['blog_comment_bayes_spam_threshold'], null),
		'blog_comment_text_converter' => Base_Cnc::ifsetor($settings['blog_comment_text_converter'], null),
		'blog_trackback_display_status' => Base_Cnc::ifsetor($settings['blog_trackback_display_status'], null),
		'blog_trackback_default_status' => Base_Cnc::ifsetor($settings['blog_trackback_default_status'], null),
		'blog_trackback_spam_status' => Base_Cnc::ifsetor($settings['blog_trackback_spam_status'], null),
		'blog_trackback_ham_status' => Base_Cnc::ifsetor($settings['blog_trackback_ham_status'], null),
		'blog_trackback_timeframe_threshold' => Base_Cnc::ifsetor($settings['blog_trackback_timeframe_threshold'], null),
		'blog_trackback_bayes_autolearn' => Base_Cnc::ifsetor($settings['blog_trackback_bayes_autolearn'], null),
		'blog_trackback_bayes_autolearn_threshold' => Base_Cnc::ifsetor($settings['blog_trackback_bayes_autolearn_threshold'], null),
		'blog_trackback_bayes_spam_threshold' => Base_Cnc::ifsetor($settings['blog_trackback_bayes_spam_threshold'], null)
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
		
		// assign prepared session array to smarty
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
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('community/settings.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->toggleFrozen(true);
		
		// create the article group
		$sqlData = array();
		$sqlData['blog_comment_display_status'] = $blog_comment_display_status->getValue();
		$sqlData['blog_comment_default_status'] = $blog_comment_default_status->getValue();
		$sqlData['blog_comment_spam_status'] = $blog_comment_spam_status->getValue();
		$sqlData['blog_comment_ham_status'] = $blog_comment_ham_status->getValue();
		$sqlData['blog_comment_use_captcha'] = $blog_comment_use_captcha->getValue();
		$sqlData['blog_comment_timeframe_threshold'] = $blog_comment_timeframe_threshold->getValue();
		$sqlData['blog_comment_bayes_autolearn'] = (string)intval($blog_comment_bayes_autolearn->getValue());
		$sqlData['blog_comment_bayes_autolearn_threshold'] = $blog_comment_bayes_autolearn_threshold->getValue();
		$sqlData['blog_comment_bayes_spam_threshold'] = $blog_comment_bayes_spam_threshold->getValue();
		$sqlData['blog_comment_text_converter'] = $blog_comment_text_converter->getValue();
		$sqlData['blog_trackback_display_status'] = $blog_trackback_display_status->getValue();
		$sqlData['blog_trackback_default_status'] = $blog_trackback_default_status->getValue();
		$sqlData['blog_trackback_spam_status'] = $blog_trackback_spam_status->getValue();
		$sqlData['blog_trackback_ham_status'] = $blog_trackback_ham_status->getValue();
		$sqlData['blog_trackback_timeframe_threshold'] = $blog_trackback_timeframe_threshold->getValue();
		$sqlData['blog_trackback_bayes_autolearn'] = (string)intval($blog_trackback_bayes_autolearn->getValue());
		$sqlData['blog_trackback_bayes_autolearn_threshold'] = $blog_trackback_bayes_autolearn_threshold->getValue();
		$sqlData['blog_trackback_bayes_spam_threshold'] = $blog_trackback_bayes_spam_threshold->getValue();
		
		// check sql data
		$HELPER = load('utility:helper');
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$SETTINGS->saveSettings($sqlData);
			
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
		header("Location: settings.php");
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