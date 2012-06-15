<?php

/**
 * Project: Welcompose
 * File: users_edit.php
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
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
	// select user
	$user = $USER->selectUser(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
	
	// prepare group array
	$groups = array();
	foreach ($GROUP->selectGroups() as $_group) {
		$groups[(int)$_group['id']] = htmlspecialchars($_group['name']);
	}
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('user');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');
	
	// hidden for id
	$id = $FORM->addElement('hidden', 'id', array('id' => 'id'));
	$id->addRule('required', gettext('Id is not expected to be empty'));
	$id->addRule('regex', gettext('Id is expected to be numeric'), WCOM_REGEX_NUMERIC);
	
	// select for group		
	$group = $FORM->addElement('select', 'group',
	 	array('id' => 'user_group'),
		array('label' => gettext('Group'), 'options' => $groups)
		);
	$group->addRule('required', gettext('Please select a group'));
	
	// textfield for name
	$name = $FORM->addElement('text', 'name', 
		array('id' => 'user_name', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Name'))
		);
	$name->addRule('required', gettext('Please enter a name'));
	
	// textfield for email
	$email = $FORM->addElement('text', 'email', 
		array('id' => 'user_email', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('E-mail'))
		);
	$email->addRule('required', gettext('Please enter an e-mail address'));
	$email->addRule('regex', gettext('Please enter a valid e-mail address'), WCOM_REGEX_EMAIL);
	$email->addRule('callback', gettext('A user with this e-mail address already exists'), 
		array(
			'callback' => array($USER, 'testForUniqueEmail'),
			'arguments' => array($id->getValue())
		)
	);
	
	// textfield for password
	$password = $FORM->addElement('password', 'password', 
		array('id' => 'user_password', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('Password'))
		);
	$password->addRule('minlength', gettext('Please enter a password with at least five characters'), 5);
	
	// textfield for password compare
	$password_verify = $FORM->addElement('password', 'password_verify', 
		array('id' => 'user_password_verify', 'maxlength' => 255, 'class' => 'w300'),
		array('label' => gettext('Password verify'))
		);
	$password_verify->addRule('compare', gettext('It seems your entered password does not compare to your former input'), 
		array('operand' => $password->getValue())
	);
	
	// textfield for homepage
	$homepage = $FORM->addElement('text', 'homepage', 
		array('id' => 'user_homepage', 'maxlength' => 255, 'class' => 'w300 validate'),
		array('label' => gettext('Homepage'))
		);
	$homepage->addRule('regex', gettext('Please enter a valid homepage URL'), WCOM_REGEX_URL);
	
	// checkbox for author
	$author = $FORM->addElement('checkbox', 'author',
		array('id' => 'user_author', 'class' => 'chbx'),
		array('label' => gettext('Author'))
		);
	$author->addRule('regex', gettext('The field author accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
	
	// checkbox for active
	$active = $FORM->addElement('checkbox', 'active',
		array('id' => 'user_active', 'class' => 'chbx'),
		array('label' => gettext('Active'))
		);
	$active->addRule('regex', gettext('The field active accepts only 0 or 1'), WCOM_REGEX_ZERO_OR_ONE);
	
	// submit button
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'submit200', 'value' => gettext('Save edit'))
		);
	
	// set defaults
	$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
		'id' => Base_Cnc::ifsetor($user['id'], null),
		'group' => Base_Cnc::ifsetor($user['group_id'], null),
		'name' => Base_Cnc::ifsetor($user['name'], null),
		'email' => Base_Cnc::ifsetor($user['email'], null),
		'homepage' => Base_Cnc::ifsetor($user['homepage'], null),
		'author' => Base_Cnc::ifsetor($user['author'], null),
		'active' => Base_Cnc::ifsetor($user['active'], null)
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
		$FORM->toggleFrozen(true);
		
		// create the article group
		$sqlData = array();
		$sqlData['name'] = $name->getValue();
		$sqlData['email'] = $email->getValue();
		$sqlData['homepage'] = $homepage->getValue();
		if ($password->getValue() != "") {
			$sqlData['secret'] = crypt($password->getValue());
		}		
		
		// check sql data
		$HELPER = load('utility:helper');
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$USER->updateUser($id->getValue(), $sqlData);
			
			// map user to group
			$USER->mapUserToGroup($id->getValue(), $group->getValue());
			
			// map user to project
			$USER->mapUserToProject($id->getValue(), (int)$active->getValue(),
				(int)$author->getValue());
			
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
