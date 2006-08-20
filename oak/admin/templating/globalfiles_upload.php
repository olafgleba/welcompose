<?php

/**
 * Project: Oak
 * File: globalfiles_upload.php
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
	$PROJECT->initProjectAdmin(OAK_CURRENT_USER);
	
	// get global file
	$file = $GLOBALFILE->selectGlobalFile(Base_Cnc::filterRequest($_REQUEST['id'], OAK_REGEX_NUMERIC));
	
	// let's see if we received a file
	if ($_FILES['file']['error'] == UPLOAD_ERR_OK && !empty($file)) {
		// remove old file
		$GLOBALFILE->removeGlobalFileFromStore($file['id']);
		
		// get upload data
		$data = $_FILES['file'];
		
		// clean file data
		foreach ($data as $_key => $_value) {
			$data[$_key] = trim(strip_tags($_value));
		}
		
		// move file to file store
		$name_on_disk = $GLOBALFILE->moveGlobalFileToStore($data['name'], $data['tmp_name']);
		
		// prepare sql data
		$sqlData = array();
		$sqlData['project'] = OAK_CURRENT_PROJECT;
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
	}
	
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
	Base_Error::triggerException($BASE->utility->smarty, $e);	
	
	// exit
	exit;
}

?>