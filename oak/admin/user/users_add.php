<?php

/**
 * Project: Oak
 * File: users_add.php
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
	
	// start session
	/* @var $SESSION session */
	$SESSION = load('base:session');
	
	// load user class
	/* @var $USER User_User */
	$USER = load('user:user');
	
	// load project class
	/* @var $PROJECT Application_Project */
	$PROJECT = load('application:project');
	
	// load group class
	/* @var $GROUP User_Group */
	$GROUP = load('user:group');
	
	// init user and project
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(OAK_CURRENT_USER);
	
	// prepare group array
	$groups = array();
	$select_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	foreach ($GROUP->selectGroups($select_params) as $_group) {
		$groups[(int)$_group['id']] = htmlspecialchars($_group['name']);
	}
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('user', 'post');
	$FORM->registerRule('testForEmailUniqueness', 'callback', 'testForUniqueEmail', $USER);
	
	// select for group
	$FORM->addElement('select', 'group', gettext('Group'), $groups,
		array('id' => 'user_group'));
	$FORM->applyFilter('group', 'trim');
	$FORM->applyFilter('group', 'strip_tags');
	$FORM->addRule('group', gettext('Please select a group'), 'required');
	
	// textfield for name
	$FORM->addElement('text', 'email', gettext('E-mail'), 
		array('id' => 'user_email', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('email', 'trim');
	$FORM->applyFilter('email', 'strip_tags');
	$FORM->addRule('email', gettext('Please enter an e-mail address'), 'required');
	$FORM->addRule('email', gettext('Please enter a valid e-mail address'), 'email');
	$FORM->addRule('email', gettext('A user with this e-mail address already exists'),
		'testForEmailUniqueness');
	
	// textfield for homepage
	$FORM->addElement('password', 'password', gettext('Password'), 
		array('id' => 'user_password', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->applyFilter('password', 'trim');
	$FORM->applyFilter('password', 'strip_tags');
	$FORM->addRule('password', gettext('Please enter a password'), 'required');
	$FORM->addRule('password', gettext('Please enter a password with at least five characters'),
		'minlength', 5);
	
	// checkbox for author
	$FORM->addElement('checkbox', 'author', gettext('Author'), null,
		array('id' => 'user_author', 'class' => 'chbx'));
	$FORM->applyFilter('author', 'trim');
	$FORM->applyFilter('author', 'strip_tags');
	$FORM->addRule('author', gettext('The field author accepts only 0 or 1'),
		'regex', OAK_REGEX_ZERO_OR_ONE);
	
	// checkbox for active
	$FORM->addElement('checkbox', 'active', gettext('Active'), null,
		array('id' => 'user_active', 'class' => 'chbx'));
	$FORM->applyFilter('active', 'trim');
	$FORM->applyFilter('active', 'strip_tags');
	$FORM->addRule('active', gettext('The field active accepts only 0 or 1'),
		'regex', OAK_REGEX_ZERO_OR_ONE);
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Add user'),
		array('class' => 'submitbut140'));
	
	// set defaults
	$FORM->setDefaults(array(
		'active' => 1
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
		
	    // build $session
	    $session = array(
			'response' => Base_Cnc::filterRequest($_SESSION['response'], OAK_REGEX_NUMERIC)
	    );
	    
	    // assign $_SESSION to smarty
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
		$BASE->utility->smarty->display('user/users_add.html', OAK_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// create the article group
		$sqlData = array();
		$sqlData['email'] = $FORM->exportValue('email');
		$sqlData['secret'] = crypt($FORM->exportValue('password'));
		$sqlData['editable'] = "1";
		$sqlData['date_added'] = date('Y-m-d H:i:s');
		
		// check sql data
		$HELPER = load('utility:helper');
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$user_id = $USER->addUser($sqlData);
			
			// map user to group
			$USER->mapUserToGroup($user_id, $FORM->exportValue('group'));
			
			// map user to project
			$USER->mapUserToProject($user_id, (int)$FORM->exportValue('active'),
				(int)$FORM->exportValue('author'));
			
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
		header("Location: users_add.php");
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