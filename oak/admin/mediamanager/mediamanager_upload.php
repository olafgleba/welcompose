<?php

/**
 * Project: Oak
 * File: mediamanager_upload.php
 *
 * Copyright (c) 2006 sopic GmbH
 *
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-2.1.php
 *
 * $Id: navigations_select.php 308 2006-08-08 12:42:23Z andreas $
 *
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License 3.0
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
	/* @var $SESSION Base_Session */
	$SESSION = load('Base:Session');
	
	// load user class
	/* @var $USER User_User */
	$USER = load('User:User');
	
	// load login class
	/* @var $LOGIN User_Login */
	$LOGIN = load('User:Login');
	
	// load Application_Project
	/* @var $PROJECT Application_Project */
	$PROJECT = load('Application:Project');
	
	// load Media_Object
	/* @var $OBJECT Media_Object */
	$OBJECT = load('Media:Object');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(OAK_CURRENT_USER);
	
	// default media types
	$types = array (
		'image' => gettext('Image'),
		'document' => gettext('Document'),
		'audio' => gettext('Audio'),
		'video' => gettext('Video'),
		'other' => gettext('Other')
	);
	
	// get pager_page value
	if (!empty($_REQUEST['pager_page'])) {
		$pager_page = Base_Cnc::filterRequest($_REQUEST['pager_page'], OAK_REGEX_NUMERIC);
	} else {
		$pager_page = Base_Cnc::filterRequest($_SESSION['pager_page'], OAK_REGEX_NUMERIC);
	}
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('media_upload', 'post');
	
	// hidden field for pager_page
	$FORM->addElement('hidden', 'pager_page');
	
	// file upload field
	$file_upload = $FORM->addElement('file', 'file', gettext('File'), 
		array('id' => 'file', 'maxlength' => 255, 'class' => 'w300'));
	$FORM->addRule('file', gettext('Please select a file'), 'uploadedfile');
	
	// textarea for description
	$FORM->addElement('textarea', 'description', gettext('Description'),
		array('id' => 'description', 'class' => 'w540h150', 'cols' => 3, 'rows' => 2));
	$FORM->applyFilter('description', 'trim');
	$FORM->applyFilter('description', 'strip_tags');
	
	// textarea for tags
	$FORM->addElement('textarea', 'tags', gettext('Tags'),
		array('id' => 'tags', 'class' => 'w540h150', 'cols' => 3, 'rows' => 2));
	$FORM->applyFilter('tags', 'trim');
	$FORM->applyFilter('tags', 'strip_tags');
	$FORM->addRule('tags', gettext('Please add at least one tag'), 'required');	
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Upload Media'),
		array('class' => 'submit200upload'));

	// reset button
	$FORM->addElement('reset', 'reset', gettext('Close'),
		array('class' => 'cancel200'));
		
	// set defaults
	$FORM->setDefaults(array(
		'pager_page' => Base_Cnc::ifsetor($pager_page, null)
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

		// assign delivered pager location
		$BASE->utility->smarty->assign('pager_page', $pager_page);
		
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

		// select available projects
		$select_params = array(
			'user' => OAK_CURRENT_USER,
			'order_macro' => 'NAME'
		);

		// display the form
		define("OAK_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('mediamanager/mediamanager_upload.html', OAK_TEMPLATE_KEY);

		// flush the buffer
		@ob_end_flush();

		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// handle file upload
		if ($file_upload->isUploadedFile()) {
			// get file data
			$data = $file_upload->getValue();
			
			// clean file data
			foreach ($data as $_key => $_value) {
				$data[$_key] = trim(strip_tags($_value));
			}
			
			// move file to file store
			$name_on_disk = $OBJECT->moveObjectToStore($data['name'], $data['tmp_name']);
			
			// create thumbnail
			$thumbnail = $OBJECT->createImageThumbnail($data['name'], $name_on_disk, 40, 40, true, 'ffffff');
			
			// if the file on disk is an image, get the image size
			list($width, $height) = @getimagesize($OBJECT->getPathToObject($name_on_disk));
			
			// prepare sql data
			$sqlData = array();
			$sqlData['project'] = OAK_CURRENT_PROJECT;
			$sqlData['description'] = $FORM->exportValue('description');
			$sqlData['tags'] = $FORM->exportValue('tags');
			$sqlData['file_name'] = $data['name'];
			$sqlData['file_name_on_disk'] = $name_on_disk;
			$sqlData['file_mime_type'] = $data['type'];
			$sqlData['file_width'] = $width;
			$sqlData['file_height'] = $height;
			$sqlData['file_size'] = (int)$data['size'];
			$sqlData['preview_name_on_disk'] = Base_Cnc::ifsetor($thumbnail['name'], null);
			$sqlData['preview_mime_type'] = Base_Cnc::ifsetor($thumbnail['type'], null);
			$sqlData['preview_width'] = Base_Cnc::ifsetor($thumbnail['width'], null);
			$sqlData['preview_height'] = Base_Cnc::ifsetor($thumbnail['height'], null);
			$sqlData['preview_size'] = Base_Cnc::ifsetor($thumbnail['size'], null);
			$sqlData['date_added'] = date('Y-m-d H:i:s');
			
			// check sql data
			$HELPER = load('utility:helper');
			$HELPER->testSqlDataForPearErrors($sqlData);
			
			// load tag class
			$TAG = load('Media:Tag');
			
			// insert it
			try {
				// begin transaction
				$BASE->db->begin();
				
				// insert row into database
				$object = $OBJECT->addObject($sqlData);
				
				// insert tags
				$TAG->addTags($object, $TAG->_tagStringToArray($FORM->exportValue('tags')));
				
				// commit
				$BASE->db->commit();
			} catch (Exception $e) {
				// do rollback
				$BASE->db->rollback();
				
				// re-throw exception
				throw $e;
			}
		}
		
		// add response to session
		$_SESSION['response'] = 1;
		
		// add pager_page to session
		$_SESSION['pager_page'] = $FORM->exportValue('pager_page');
		
		// redirect
		$SESSION->save();
		
		// clean the buffer
		if (!$BASE->debug_enabled()) {
			@ob_end_clean();
		}
		
		// redirect
		header("Location: mediamanager_upload.php");
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