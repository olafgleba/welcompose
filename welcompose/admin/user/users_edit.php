<?php

/**
 * Project: Welcompose
 * File: users_edit.php
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
	
	// load group class
	/* @var $GROUP User_Group */
	$GROUP = load('user:group');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('User', 'User', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// select user
	$user = $USER->selectUser(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
	
	// prepare group array
	$groups = array();
	foreach ($GROUP->selectGroups() as $_group) {
		$groups[(int)$_group['id']] = htmlspecialchars($_group['name']);
	}
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('user', 'post');
	$FORM->registerRule('testForEmailUniqueness', 'callback', 'testForUniqueEmail', $USER);
	
	// hidden for id
	$FORM->addElement('hidden', 'id');
	$FORM->applyFilter('id', 'trim');
	$FORM->applyFilter('id', 'strip_tags');
	$FORM->addRule('id', gettext('Id is not expected to be empty'), 'required');
	$FORM->addRule('id', gettext('Id is expected to be numeric'), 'numeric');
	
	// textfield for name
	$FORM->addElement('text', 'name', gettext('Name'), 
		array('id' => 'user_name', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('name', 'trim');
	$FORM->applyFilter('name', 'strip_tags');
	$FORM->addRule('name', gettext('Please enter a name'), 'required');
	
	// select for group
	$FORM->addElement('select', 'group', gettext('Group'), $groups,
		array('id' => 'user_group'));
	$FORM->applyFilter('group', 'trim');
	$FORM->applyFilter('group', 'strip_tags');
	$FORM->addRule('group', gettext('Please select a group'), 'required');
	$FORM->addRule('group', gettext('Selected group is out of range'), 'in_array_keys',
		$groups);
	
	// textfield for name
	$FORM->addElement('text', 'email', gettext('E-mail'), 
		array('id' => 'user_email', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('email', 'trim');
	$FORM->applyFilter('email', 'strip_tags');
	$FORM->addRule('email', gettext('Please enter an e-mail address'), 'required');
	$FORM->addRule('email', gettext('Please enter a valid e-mail address'), 'email');
	$FORM->addRule('email', gettext('A user with this e-mail address already exists'),
		'testForEmailUniqueness', $FORM->exportValue('id'));
	
	// textfield for homepage
	$FORM->addElement('password', 'password', gettext('Password'), 
		array('id' => 'user_password', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('password', 'trim');
	$FORM->applyFilter('password', 'strip_tags');
	$FORM->addRule('password', gettext('Please enter a password with at least five characters'),
		'minlength', 5);
	
	// textfield for homepage
	$FORM->addElement('text', 'homepage', gettext('Homepage'), 
		array('id' => 'user_homepage', 'maxlength' => 255, 'class' => 'w300 validate'));
	$FORM->applyFilter('homepage', 'trim');
	$FORM->applyFilter('homepage', 'strip_tags');
	$FORM->addRule('homepage', gettext("Please enter a valid homepage URL"), 'regex',
		WCOM_REGEX_URL);
	
	// checkbox for author
	$FORM->addElement('checkbox', 'author', gettext('Author'), null,
		array('id' => 'user_author', 'class' => 'chbx'));
	$FORM->applyFilter('author', 'trim');
	$FORM->applyFilter('author', 'strip_tags');
	$FORM->addRule('author', gettext('The field author accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// checkbox for active
	$FORM->addElement('checkbox', 'active', gettext('Active'), null,
		array('id' => 'user_active', 'class' => 'chbx'));
	$FORM->applyFilter('active', 'trim');
	$FORM->applyFilter('active', 'strip_tags');
	$FORM->addRule('active', gettext('The field active accepts only 0 or 1'),
		'regex', WCOM_REGEX_ZERO_OR_ONE);
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Edit user'),
		array('class' => 'submit200'));
	
	// set defaults
	$FORM->setDefaults(array(
		'id' => Base_Cnc::ifsetor($user['id'], null),
		'group' => Base_Cnc::ifsetor($user['group_id'], null),
		'name' => Base_Cnc::ifsetor($user['name'], null),
		'email' => Base_Cnc::ifsetor($user['email'], null),
		'homepage' => Base_Cnc::ifsetor($user['homepage'], null),
		'author' => Base_Cnc::ifsetor($user['author'], null),
		'active' => Base_Cnc::ifsetor($user['active'], null)
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
		$BASE->utility->smarty->assign('wcom_admin_root_www',
			$BASE->_conf['path']['wcom_admin_root_www']);
	    
		// assign current user and project id
		$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);
		$BASE->utility->smarty->assign('wcom_current_project', WCOM_CURRENT_PROJECT);
		
		// calculate and assign group count
		$BASE->utility->smarty->assign('group_count', count($groups));
		
		// select available projects
		$select_params = array(
			'user' => WCOM_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		$BASE->utility->smarty->assign('projects', $PROJECT->selectProjects($select_params));
		
		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('user/users_edit.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// create the article group
		$sqlData = array();
		$sqlData['name'] = $FORM->exportValue('name');
		$sqlData['email'] = $FORM->exportValue('email');
		$sqlData['homepage'] = $FORM->exportValue('homepage');
		if ($FORM->exportValue('password') != "") {
			$sqlData['secret'] = crypt($FORM->exportValue('password'));
		}		
		
		// check sql data
		$HELPER = load('utility:helper');
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$USER->updateUser($FORM->exportValue('id'), $sqlData);
			
			// map user to group
			$USER->mapUserToGroup($FORM->exportValue('id'), $FORM->exportValue('group'));
			
			// map user to project
			$USER->mapUserToProject($FORM->exportValue('id'), (int)$FORM->exportValue('active'),
				(int)$FORM->exportValue('author'));
			
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
		header("Location: users_select.php");
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
