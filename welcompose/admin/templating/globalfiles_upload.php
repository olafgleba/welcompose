<?php

/**
 * Project: Welcompose
 * File: globalfiles_upload.php
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
	$file = $GLOBALFILE->selectGlobalFile(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));
	
	// let's see if we received a file
	if ($_FILES['file']['error'] == UPLOAD_ERR_OK && !empty($file)) {
		
		// get upload data
		$data = $_FILES['file'];
		
		// clean file data
		foreach ($data as $_key => $_value) {
			$data[$_key] = trim(strip_tags($_value));
		}
		
		// test if a file with prepared file name exits already
		$check_global_file = $GLOBALFILE->testForUniqueFilename($data['name'], $file['id'], 'upload');
	
		if ($check_global_file === true) {
			
			// remove old file
			$GLOBALFILE->removeGlobalFileFromStore($file['id']);
		
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
			
				// insert row into database
				$GLOBALFILE->updateGlobalFile($file['id'], $sqlData);
			
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
	
	// redirect
	header("Location: globalfiles_select.php");
	exit;

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