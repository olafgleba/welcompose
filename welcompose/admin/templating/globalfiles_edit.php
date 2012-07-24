<?php

/**
 * Project: Welcompose
 * File: globalfiles_edit.php
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
	
	// start Base_Sessio
	/* @var $SESSION Session */
	$SESSION = load('Base:Session');

	// load user class
	/* @var $USER User_User */
	$USER = load('User:User');
	
	// load User_Login
	/* @var $LOGIN User_Login */
	$LOGIN = load('User:Login');
	
	// load Application_Project
	/* @var $PROJECT Application_Project */
	$PROJECT = load('application:project');
	
	// load Templating_GlobalFile
	/* @var $GLOBALFILE Templating_GlobalFile */
	$GLOBALFILE = load('Templating:GlobalFile');
	
	// load helper class
	/* @var $HELPER Utility_Helper */
	$HELPER = load('Utility:Helper');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Templating', 'GlobalFile', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// assign current user values
	$_wcom_current_user = $USER->selectUser(WCOM_CURRENT_USER);
	$BASE->utility->smarty->assign('_wcom_current_user', $_wcom_current_user);
	
	// assign current project values
	$_wcom_current_project = $PROJECT->selectProject(WCOM_CURRENT_PROJECT);
	$BASE->utility->smarty->assign('_wcom_current_project', $_wcom_current_project);
	
	// get global file
	$globalfile = $GLOBALFILE->selectGlobalFile(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('global_file');
	$FORM->setAttribute('enctype', 'multipart/form-data');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');

	// hidden for id
	$id = $FORM->addElement('hidden', 'id', array('id' => 'id'));
	
	// hidden for start
	$start = $FORM->addElement('hidden', 'start', array('id' => 'start'));

		
	// file upload field
	$file = $FORM->addElement('file', 'file', 
		array('id' => 'global_file_file'),
		array('label' => gettext('File'))
		);
		
	// textarea for description
	$description = $FORM->addElement('textarea', 'description', 
		array('id' => 'global_file_description', 'cols' => 3, 'rows' => 2, 'class' => 'w298h50'),
		array('label' => gettext('Description'))
		);
		
	// submit button
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'submit200', 'value' => gettext('Save edit'))
		);
	
	// set defaults
	$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
		'start' => Base_Cnc::filterRequest($_REQUEST['start'], WCOM_REGEX_NUMERIC),
		'id' => Base_Cnc::ifsetor($globalfile['id'], null),
		'description' => Base_Cnc::ifsetor($globalfile['description'], null)
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
	
	    // assign file array 
		$BASE->utility->smarty->assign('file', $globalfile);

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
		$BASE->utility->smarty->display('templating/globalfiles_edit.html', WCOM_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->toggleFrozen(true);
		
		// handle changes of the description field
		try {
			// begin transaction
			$BASE->db->begin();
	
			// prepare sql data
			$sqlData = array(
				'description' => $description->getValue()
			);

			// check sql data
			$HELPER = load('utility:helper');
			$HELPER->testSqlDataForPearErrors($sqlData);
	
			// update row
			$GLOBALFILE->updateGlobalFile($id->getValue(), $sqlData);
	
			// commit
			$BASE->db->commit();
		} catch (Exception $e) {
			// do rollback
			$BASE->db->rollback();

			// re-throw exception
			throw $e;
		}
		
		// handle file upload
		// get file data
		$data = $file->getValue();
		
		// clean file data
		foreach ($data as $_key => $_value) {
			$data[$_key] = trim(strip_tags($_value));
		}
		
		// file available to upload?
		if (!empty($data['name'])) {
			
			// test if a file with prepared file name exits already
			$check_global_file = $GLOBALFILE->testForUniqueFilename($data['name'], $id->getValue(), 'edit');
			
			if ($check_global_file === true) {
			
				// remove old file from file store
				$GLOBALFILE->removeGlobalFileFromStore($id->getValue());
			
				// move file to file store
				$name_on_disk = $GLOBALFILE->moveGlobalFileToStore($data['name'], $data['tmp_name']);
			
				// prepare sql data
				$sqlData = array();
				$sqlData['name'] = $data['name'];
				$sqlData['name_on_disk'] = $name_on_disk;
				$sqlData['mime_type'] = $data['type'];
				$sqlData['size'] = (int)$data['size'];
			
				// insert it
				try {
					// begin transaction
					$BASE->db->begin();
				
					// update row
					$GLOBALFILE->updateGlobalFile($id->getValue(), $sqlData);
				
					// commit
					$BASE->db->commit();
				} catch (Exception $e) {
					// do rollback
					$BASE->db->rollback();

					// re-throw exception
					throw $e;
				}
		
				// save request start range
				$start = $start->getValue();
				$start = (!empty($start)) ? $start : 0;
				
			} else {
				// add response to session
				$_SESSION['response'] = 2;
			}
		}

		// redirect
		$SESSION->save();

		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
						
		if ($check_global_file === true) {
			// redirect
			header("Location: globalfiles_select.php?start=".$start);
			exit;
		} else {
			// redirect
			header("Location: globalfiles_edit.php?id=".$id->getValue());
			exit;
		}
					
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