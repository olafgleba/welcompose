<?php

/**
 * Project: Welcompose
 * File: mediamanager_upload.php
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
 * @author Olaf Gleba
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
	
	// load Media_Tag
	/* @var $TAG Media_Tag */
	$TAG = load('Media:Tag');
	
	// load textconverter class
	/* @var $TEXTCONVERTER Application_Textconverter */
	$TEXTCONVERTER = load('application:textconverter');

	// load textmacro class
	/* @var $TEXTMACRO Application_Textmacro */
	$TEXTMACRO = load('application:textmacro');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Media', 'Object', 'Manage')) {
		throw new Exception("Access denied");
	}
	
	// default media types
	$types = array (
		'image' => gettext('Image'),
		'document' => gettext('Document'),
		'audio' => gettext('Audio'),
		'video' => gettext('Video'),
		'other' => gettext('Other')
	);
	
	// get object
	$object = $OBJECT->selectObject(Base_Cnc::filterRequest($_REQUEST['id'], WCOM_REGEX_NUMERIC));

	// get pager_page value
	if (!empty($_REQUEST['pager_page'])) {
		$pager_page = Base_Cnc::filterRequest($_REQUEST['pager_page'], WCOM_REGEX_NUMERIC);
	} else {
		$pager_page = Base_Cnc::filterRequest($_SESSION['pager_page'], WCOM_REGEX_NUMERIC);
	}
		
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('media_edit');
	$FORM->setAttribute('enctype', 'multipart/form-data');

	// apply filters to all fields
	$FORM->addRecursiveFilter('trim');

	// hidden for id
	$id = $FORM->addElement('hidden', 'id', array('id' => 'id'));
	
	// hidden field for pager_page
	$pager_page = $FORM->addElement('hidden', 'pager_page', array('id' => 'pager_page'));
	
	// file upload field
	$file = $FORM->addElement('file', 'file', 
		array('id' => 'file'),
		array('label' => gettext('File'))
		);
	//$file->addRule('maxfilesize', gettext('File is too big, allowed size 20kB'), 20480);
	
	// textarea for description
	$description = $FORM->addElement('textarea', 'description', 
		array('id' => 'description', 'cols' => 3, 'rows' => 2, 'class' => 'w540h50'),
		array('label' => gettext('Description'))
		);

	// textarea for tags
	$tags = $FORM->addElement('textarea', 'tags', 
		array('id' => 'tags', 'cols' => 3, 'rows' => 2, 'class' => 'w540h50'),
		array('label' => gettext('Tags'))
		);
	$tags->addRule('required', gettext('Please add at least one tag'));

	// submit button
	$submit = $FORM->addElement('submit', 'submit', 
		array('class' => 'submit200upload', 'value' => gettext('Save edit'))
		);

	// close button
	$close = $FORM->addElement('reset', 'close', 
		array('class' => 'cancel140', 'value' => gettext('Close'))
		);
	
	// set defaults
	$FORM->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
		'id' => Base_Cnc::ifsetor($object['id'], null),
		'type' => Base_Cnc::ifsetor($object['type'], null),
		'description' => Base_Cnc::ifsetor($object['description'], null),
		'tags' => Base_Cnc::ifsetor($object['tags'], null),
		'pager_page' => Base_Cnc::ifsetor($pager_page, null)
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

		// assign delivered pager location
		$BASE->utility->smarty->assign('pager_page', $pager_page);
		
		// assign the whole object
		$BASE->utility->smarty->assign('object', $object);
		
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
	    if (!empty($_SESSION['pager_page'])) {
	        $_SESSION['pager_page'] = '';
	    }

		// assign current user and project id
		$BASE->utility->smarty->assign('wcom_current_user', WCOM_CURRENT_USER);

		// select available projects
		$select_params = array(
			'user' => WCOM_CURRENT_USER,
			'order_macro' => 'NAME'
		);
		
		// assign currently used media tags
		$BASE->utility->smarty->assign('current_tags', $TAG->selectTags());
		
		// assign image path
		$BASE->utility->smarty->assign('media_store_www', $BASE->_conf['media']['store_www']);

		// display the form
		define("WCOM_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('mediamanager/mediamanager_edit.html', WCOM_TEMPLATE_KEY);

		// flush the buffer
		@ob_end_flush();

		exit;
	} else {
		// freeze the form
		$FORM->toggleFrozen(true);
		
		// load helper class
		$HELPER = load('utility:helper');
		
		$sqlData['description'] = $description->getValue();
		$sqlData['tags'] = $tags->getValue();
		
		// check sql data
		$HELPER->testSqlDataForPearErrors($sqlData);
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// update tags
			$TAG->updateTags($id->getValue(), $TAG->_tagStringToArray($tags->getValue()));
			
			// update row in database
			$OBJECT->updateObject($id->getValue(), $sqlData);
			
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
			$check_file = $OBJECT->testForUniqueFilename($data['name'], $id->getValue());
		
			// Unique file? 
			if ($check_file === true) {
								
				// remove old thumbnail and object
				$OBJECT->removeImageThumbnail(Base_Cnc::filterRequest($id->getValue(), WCOM_REGEX_NUMERIC));
				$OBJECT->removeObjectFromStore(Base_Cnc::filterRequest($id->getValue(), WCOM_REGEX_NUMERIC));
					
				// move file to file store
				$name_on_disk = $OBJECT->moveObjectToStore($data['name'], $data['tmp_name']);
		
				// create thumbnail
				$thumbnail = $OBJECT->createImageThumbnail($data['name'], $name_on_disk, 200, 200, true, 'ffffff');
		
				// if the file on disk is an image, get the image size
				list($width, $height) = @getimagesize($OBJECT->getPathToObject($name_on_disk));
		
				// prepare sql data
				$sqlData = array();
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
		
				// check sql data
				$HELPER->testSqlDataForPearErrors($sqlData);
		
				// insert it
				try {
					// begin transaction
					$BASE->db->begin();
			
					// update row in database
					$OBJECT->updateObject($id->getValue(), $sqlData);
			
					// commit
					$BASE->db->commit();
				} catch (Exception $e) {
					// do rollback
					$BASE->db->rollback();
			
					// re-throw exception
					throw $e;
				}
		
				// Load and resave all pages
				// abstract of file: actions_apply_url_patterns.php
		
				// prepare sql data
				$sqlDataSave = array();

				// class loader array
				$classLoad = array(
					'SIMPLEPAGE' => array('selectSimplePages', 'updateSimplePage'),
					'SIMPLEFORM' => array('selectSimpleForms', 'updateSimpleForm'),
					'SIMPLEGUESTBOOK' => array('selectSimpleGuestbooks', 'updateSimpleGuestbook'),
					'GENERATORFORM' => array('selectGeneratorForms', 'updateGeneratorForm'),
					'BLOGPOSTING' => array('selectBlogPostings', 'updateBlogPosting'),
					'EVENTPOSTING' => array('selectEventPostings', 'updateEventPosting'),
					'BOX' => array('selectBoxes', 'updateBox'),
					'GLOBALBOX' => array('selectGlobalBoxes', 'updateGlobalBox')
				);

				foreach ($classLoad as $classRef => $classFunc) {

					// define some vars
					$_class = strtolower($classRef);
					$_class_reference = $classRef;
			
					// load the appropriate class
					$_class_reference = load('content:'.$_class);
			
					// collect results within var $_class
					// example: $simplepages = $SIMPLEPAGE->selectSimplePages();
					$_class = $_class_reference->$classFunc['0']();
	
					// Iterate through the results
					foreach ($_class as $_key => $_value) {	

						// make sure field content is not NULL
						// this may occur when a page is added but still not edited
						if (!is_null($_value['content_raw'])) {	
						
							// apply text macros and text converter if required
							if ($_value['text_converter'] > 0 || $_value['apply_macros'] > 0) {
					
								// extract content
								$content = $_value['content_raw'];
									
								// apply startup and pre text converter text macros 
								if ($_value['apply_macros'] > 0) {
									$content = $TEXTMACRO->applyTextMacros($content, 'pre');
								}
									
								// apply text converter
								if ($_value['text_converter'] > 0) {
									$content = $TEXTCONVERTER->applyTextConverter(
										$_value['text_converter'],
										$content
									);
								}
									
								// apply post text converter and shutdown text macros 
								if ($_value['apply_macros'] > 0) {
									$content = $TEXTMACRO->applyTextMacros($content, 'post');
								}
							
								// assign content to sql data array
								$sqlDataSave['content'] = $content;
							
							
								// test sql data for pear errors
								$HELPER->testSqlDataForPearErrors($sqlDataSave);						
							
								// insert it
								try {
									// begin transaction
									$BASE->db->begin();
									
									// execute operation
									$_class_reference->$classFunc['1']($_value['id'], $sqlDataSave);
									
									// commit
									$BASE->db->commit();
								} catch (Exception $e) {
									// do rollback
									$BASE->db->rollback();
									
									// re-throw exception
									throw $e;
								}						
							}
						}
					
					} // foreach eof	
				} // foreach eof
			
				// add response to session
				$_SESSION['response'] = 1;
	
				// add pager_page to session
				$_SESSION['pager_page'] = $pager_page->getValue();	
		
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
		header("Location: mediamanager_edit.php?id=".$id->getValue());
		exit;
	}
} catch (Exception $e) {
	// clean the buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	$BASE->error->displayException($e, $BASE->utility->smarty, 'error_popup_723.html');
	$BASE->error->triggerException($e);

	// exit
	exit;
}

?>