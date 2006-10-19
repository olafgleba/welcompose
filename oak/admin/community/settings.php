<?php

/**
 * Project: Oak
 * File: antispamplugins_add.php
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

// define area constant
define('OAK_CURRENT_AREA', 'ADMIN');

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
	$PROJECT->initProjectAdmin(OAK_CURRENT_USER);
	
	// get settings
	$settings = $SETTINGS->getSettings();
	
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
	
	// prepare text converters array
	$text_converters = array(
		'' => gettext('None')
	);
	foreach ($TEXTCONVERTER->selectTextConverters() as $_converter) {
		$text_converters[(int)$_converter['id']] = htmlspecialchars($_converter['name']);
	}
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('settings', 'post');
		
	// select for comment display status
	$FORM->addElement('select', 'blog_comment_display_status', gettext('Display status'),
		$blog_comment_statuses, array('id' => 'settings_blog_comment_display_status'));
	$FORM->applyFilter('blog_comment_display_status', 'trim');
	$FORM->applyFilter('blog_comment_display_status', 'strip_tags');
	$FORM->addRule('blog_comment_display_status', gettext('Chosen comment display status is out of range'),
		'in_array_keys', $blog_comment_statuses);
	
	// select for comment default status
	$FORM->addElement('select', 'blog_comment_default_status', gettext('Default status'),
		$blog_comment_statuses, array('id' => 'settings_blog_comment_default_status'));
	$FORM->applyFilter('blog_comment_default_status', 'trim');
	$FORM->applyFilter('blog_comment_default_status', 'strip_tags');
	$FORM->addRule('blog_comment_default_status', gettext('Chosen comment default status is out of range'),
		'in_array_keys', $blog_comment_statuses);
	
	// select for comment spam status
	$FORM->addElement('select', 'blog_comment_spam_status', gettext('Spam status'),
		$blog_comment_statuses, array('id' => 'settings_blog_comment_spam_status'));
	$FORM->applyFilter('blog_comment_spam_status', 'trim');
	$FORM->applyFilter('blog_comment_spam_status', 'strip_tags');
	$FORM->addRule('blog_comment_spam_status', gettext('Chosen comment spam status is out of range'),
		'in_array_keys', $blog_comment_statuses);
	
	// select for comment ham status
	$FORM->addElement('select', 'blog_comment_ham_status', gettext('Ham status'),
		$blog_comment_statuses, array('id' => 'settings_blog_comment_ham_status'));
	$FORM->applyFilter('blog_comment_ham_status', 'trim');
	$FORM->applyFilter('blog_comment_ham_status', 'strip_tags');
	$FORM->addRule('blog_comment_ham_status', gettext('Chosen comment ham status is out of range'),
		'in_array_keys', $blog_comment_statuses);
	
	// checkbox for use captcha
	$FORM->addElement('checkbox', 'blog_comment_use_captcha', gettext('Use CAPTCHAs'), null,
		array('id' => 'settings_blog_comment_use_captcha', 'class' => 'chbx'));
	$FORM->applyFilter('blog_comment_use_captcha', 'trim');
	$FORM->applyFilter('blog_comment_use_captcha', 'strip_tags');
	$FORM->addRule('active', gettext('The field whether to use comment captchas accepts only 0 or 1'),
		'regex', OAK_REGEX_ZERO_OR_ONE);
	
	// textfield for timeframe threshold
	$FORM->addElement('text', 'blog_comment_timeframe_threshold', gettext('Timeframe threshold'), 
		array('id' => 'settings_blog_comment_timeframe_threshold', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('blog_comment_timeframe_threshold', 'trim');
	$FORM->applyFilter('blog_comment_timeframe_threshold', 'strip_tags');
	$FORM->addRule('blog_comment_timeframe_threshold',
		gettext('Please enter a comment timeframe threshold'), 'required');
	$FORM->addRule('blog_comment_timeframe_threshold',
		gettext('Please enter a numeric comment timeframe threshold'), 'numeric');
	
	// checkbox for enable autolearn
	$FORM->addElement('checkbox', 'blog_comment_bayes_autolearn', gettext('Enable Bayes autolearning'), null,
		array('id' => 'settings_blog_comment_bayes_autolearn', 'class' => 'chbx'));
	$FORM->applyFilter('blog_comment_bayes_autolearn', 'trim');
	$FORM->applyFilter('blog_comment_bayes_autolearn', 'strip_tags');
	$FORM->addRule('active', gettext('The field whether autolearning is enabled accepts only 0 or 1'),
		'regex', OAK_REGEX_ZERO_OR_ONE);
	
	// textfield for bayes autolearn threshold
	$FORM->addElement('text', 'blog_comment_bayes_autolearn_threshold', gettext('Bayes autolearn threshold'), 
		array('id' => 'settings_blog_comment_bayes_autolearn_threshold', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('blog_comment_bayes_autolearn_threshold', 'trim');
	$FORM->applyFilter('blog_comment_bayes_autolearn_threshold', 'strip_tags');
	$FORM->addRule('blog_comment_bayes_autolearn_threshold',
		gettext('Please enter a comment bayes autolearn threshold'), 'required');
	$FORM->addRule('blog_comment_bayes_autolearn_threshold',
		gettext('Please enter a numeric comment bayes autolearn threshold'), 'numeric');
	
	// textfield for bayes spam threshold
	$FORM->addElement('text', 'blog_comment_bayes_spam_threshold', gettext('Bayes spam threshold'), 
		array('id' => 'settings_blog_comment_bayes_spam_threshold', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('blog_comment_bayes_spam_threshold', 'trim');
	$FORM->applyFilter('blog_comment_bayes_spam_threshold', 'strip_tags');
	$FORM->addRule('blog_comment_bayes_spam_threshold',
		gettext('Please enter a comment bayes spam threshold'), 'required');
	$FORM->addRule('blog_comment_bayes_spam_threshold',
		gettext('Please enter a numeric comment bayes spam threshold'), 'numeric');
	
	// select for comment text converter
	$FORM->addElement('select', 'blog_comment_text_converter', gettext('Text converter to apply'),
		$text_converters, array('id' => 'settings_blog_comment_text_converter'));
	$FORM->applyFilter('blog_comment_text_converter', 'trim');
	$FORM->applyFilter('blog_comment_text_converter', 'strip_tags');
	$FORM->addRule('blog_comment_text_converter', gettext('Chosen blog comment text converter is out of range'),
		'in_array_keys', $text_converters);
	
	// select for trackback display status
	$FORM->addElement('select', 'blog_trackback_display_status', gettext('Display status'),
		$blog_trackback_statuses, array('id' => 'settings_blog_trackback_display_status'));
	$FORM->applyFilter('blog_trackback_display_status', 'trim');
	$FORM->applyFilter('blog_trackback_display_status', 'strip_tags');
	$FORM->addRule('blog_trackback_display_status', gettext('Chosen trackback display status is out of range'),
		'in_array_keys', $blog_trackback_statuses);

	// select for trackback default status
	$FORM->addElement('select', 'blog_trackback_default_status', gettext('Default status'),
		$blog_trackback_statuses, array('id' => 'settings_blog_trackback_default_status'));
	$FORM->applyFilter('blog_trackback_default_status', 'trim');
	$FORM->applyFilter('blog_trackback_default_status', 'strip_tags');
	$FORM->addRule('blog_trackback_default_status', gettext('Chosen trackback default status is out of range'),
		'in_array_keys', $blog_trackback_statuses);

	// select for trackback spam status
	$FORM->addElement('select', 'blog_trackback_spam_status', gettext('Spam status'),
		$blog_trackback_statuses, array('id' => 'settings_blog_trackback_spam_status'));
	$FORM->applyFilter('blog_trackback_spam_status', 'trim');
	$FORM->applyFilter('blog_trackback_spam_status', 'strip_tags');
	$FORM->addRule('blog_trackback_spam_status', gettext('Chosen trackback spam status is out of range'),
		'in_array_keys', $blog_trackback_statuses);

	// select for trackback ham status
	$FORM->addElement('select', 'blog_trackback_ham_status', gettext('Ham status'),
		$blog_trackback_statuses, array('id' => 'settings_blog_trackback_ham_status'));
	$FORM->applyFilter('blog_trackback_ham_status', 'trim');
	$FORM->applyFilter('blog_trackback_ham_status', 'strip_tags');
	$FORM->addRule('blog_trackback_ham_status', gettext('Chosen trackback ham status is out of range'),
		'in_array_keys', $blog_trackback_statuses);
			
	// textfield for timeframe threshold
	$FORM->addElement('text', 'blog_trackback_timeframe_threshold', gettext('Timeframe threshold'), 
		array('id' => 'settings_blog_trackback_timeframe_threshold', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('blog_trackback_timeframe_threshold', 'trim');
	$FORM->applyFilter('blog_trackback_timeframe_threshold', 'strip_tags');
	$FORM->addRule('blog_trackback_timeframe_threshold',
		gettext('Please enter a trackback timeframe threshold'), 'required');
	$FORM->addRule('blog_trackback_timeframe_threshold',
		gettext('Please enter a numeric trackback timeframe threshold'), 'numeric');

	// checkbox for enable autolearn
	$FORM->addElement('checkbox', 'blog_trackback_bayes_autolearn', gettext('Enable Bayes autolearning'), null,
		array('id' => 'settings_blog_trackback_bayes_autolearn', 'class' => 'chbx'));
	$FORM->applyFilter('blog_trackback_bayes_autolearn', 'trim');
	$FORM->applyFilter('blog_trackback_bayes_autolearn', 'strip_tags');
	$FORM->addRule('active', gettext('The field whether autolearning is enabled accepts only 0 or 1'),
		'regex', OAK_REGEX_ZERO_OR_ONE);

	// textfield for bayes autolearn threshold
	$FORM->addElement('text', 'blog_trackback_bayes_autolearn_threshold', gettext('Bayes autolearn threshold'), 
		array('id' => 'settings_blog_trackback_bayes_autolearn_threshold', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('blog_trackback_bayes_autolearn_threshold', 'trim');
	$FORM->applyFilter('blog_trackback_bayes_autolearn_threshold', 'strip_tags');
	$FORM->addRule('blog_trackback_bayes_autolearn_threshold',
		gettext('Please enter a trackback bayes autolearn threshold'), 'required');
	$FORM->addRule('blog_trackback_bayes_autolearn_threshold',
		gettext('Please enter a numeric trackback bayes autolearn threshold'), 'numeric');

	// textfield for bayes spam threshold
	$FORM->addElement('text', 'blog_trackback_bayes_spam_threshold', gettext('Bayes spam threshold'), 
		array('id' => 'settings_blog_trackback_bayes_spam_threshold', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('blog_trackback_bayes_spam_threshold', 'trim');
	$FORM->applyFilter('blog_trackback_bayes_spam_threshold', 'strip_tags');
	$FORM->addRule('blog_trackback_bayes_spam_threshold',
		gettext('Please enter a trackback bayes spam threshold'), 'required');
	$FORM->addRule('blog_trackback_bayes_spam_threshold',
		gettext('Please enter a numeric trackback bayes spam threshold'), 'numeric');
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Update community settings'),
		array('class' => 'submit200bez260'));
	
	// set defaults
	$FORM->setDefaults(array(
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
		
	    // build session
	    $session = array(
			'response' => Base_Cnc::filterRequest($_SESSION['response'], OAK_REGEX_NUMERIC)
	    );
	    
	    // assign prepared session array to smarty
	    $BASE->utility->smarty->assign('session', $session);
	    
	    // empty $_SESSION
	    if (!empty($_SESSION['response'])) {
	        $_SESSION['response'] = '';
	    }
	    
		// assign current user and project id
		$BASE->utility->smarty->assign('oak_current_user', OAK_CURRENT_USER);
		$BASE->utility->smarty->assign('oak_current_project', OAK_CURRENT_PROJECT);

		// select available projects
		$select_params = array(
			'user' => OAK_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// display the form
		define("OAK_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('community/settings.html', OAK_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// create the article group
		$sqlData = array();
		$sqlData['blog_comment_display_status'] = $FORM->exportValue('blog_comment_display_status');
		$sqlData['blog_comment_default_status'] = $FORM->exportValue('blog_comment_default_status');
		$sqlData['blog_comment_spam_status'] = $FORM->exportValue('blog_comment_spam_status');
		$sqlData['blog_comment_ham_status'] = $FORM->exportValue('blog_comment_ham_status');
		$sqlData['blog_comment_use_captcha'] = $FORM->exportValue('blog_comment_use_captcha');
		$sqlData['blog_comment_timeframe_threshold'] = $FORM->exportValue('blog_comment_timeframe_threshold');
		$sqlData['blog_comment_bayes_autolearn'] = $FORM->exportValue('blog_comment_bayes_autolearn');
		$sqlData['blog_comment_bayes_autolearn_threshold'] = $FORM->exportValue('blog_comment_bayes_autolearn_threshold');
		$sqlData['blog_comment_bayes_spam_threshold'] = $FORM->exportValue('blog_comment_bayes_spam_threshold');
		$sqlData['blog_comment_text_converter'] = $FORM->exportValue('blog_comment_text_converter');
		$sqlData['blog_trackback_display_status'] = $FORM->exportValue('blog_trackback_display_status');
		$sqlData['blog_trackback_default_status'] = $FORM->exportValue('blog_trackback_default_status');
		$sqlData['blog_trackback_spam_status'] = $FORM->exportValue('blog_trackback_spam_status');
		$sqlData['blog_trackback_ham_status'] = $FORM->exportValue('blog_trackback_ham_status');
		$sqlData['blog_trackback_timeframe_threshold'] = $FORM->exportValue('blog_trackback_timeframe_threshold');
		$sqlData['blog_trackback_bayes_autolearn'] = $FORM->exportValue('blog_trackback_bayes_autolearn');
		$sqlData['blog_trackback_bayes_autolearn_threshold'] = $FORM->exportValue('blog_trackback_bayes_autolearn_threshold');
		$sqlData['blog_trackback_bayes_spam_threshold'] = $FORM->exportValue('blog_trackback_bayes_spam_threshold');
		
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
	Base_Error::triggerException($BASE->utility->smarty, $e);	
	
	// exit
	exit;
}

?>