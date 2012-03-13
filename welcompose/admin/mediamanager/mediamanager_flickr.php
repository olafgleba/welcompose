<?php

/**
 * Project: Welcompose
 * File: mediamanager_flickr.php
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

	// load Base_Session
	$SESSION = load('Base:Session');

	// load User_User
	$USER = load('User:User');

	// load User_Login
	/* @var $LOGIN User_Login */
	$LOGIN = load('User:Login');

	// load Application_Project
	$PROJECT = load('Application:Project');
	
	// load Media_Flickr
	$FLICKR = load('Media:Flickr');
	
	// load Utility_Helper
	$HELPER = load('Utility:Helper');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(WCOM_CURRENT_USER);
	
	// check access
	if (!wcom_check_access('Media', 'Object', 'Use')) {
		throw new Exception("Access denied");
	}
	
	// import request params
	$request = array(
		'mm_user' => Base_Cnc::filterRequest($_REQUEST['mm_user'], WCOM_REGEX_FLICKR_SCREENNAME),
		'mm_photoset' => Base_Cnc::filterRequest($_REQUEST['mm_photoset'], WCOM_REGEX_NUMERIC),
		'mm_flickrtags' => trim(strip_tags(Base_Cnc::ifsetor($_REQUEST['mm_flickrtags'], null))),
		'mm_pagetype' => Base_Cnc::filterRequest($_REQUEST['mm_pagetype'], WCOM_REGEX_NUMERIC),
		'mm_start' => ((Base_Cnc::filterRequest($_REQUEST['mm_start'], WCOM_REGEX_NUMERIC) > 0) ?
			(int)$_REQUEST['mm_start'] : 1)
	);
	
	try {
		// find flickr user using it's name
		$user = array();
		if (!is_null($request['mm_user'])) {
			$user = $FLICKR->peopleFindByUsername($request['mm_user']);
		}
		$BASE->utility->smarty->assign('user', $user);
	
		// get user's photosets
		$photosets = array();
		if (is_array($user) && !empty($user['user_id'])) {
			$photosets = $FLICKR->photosetsGetList($user['user_id']);
		}
		$BASE->utility->smarty->assign('photosets', $photosets);
	
		// get user's photos using the photoset or the supplied tags
		$photos = array();
		if (!is_null($request['mm_photoset'])) {
			$photos = $FLICKR->photosetsGetPhotos($request['mm_photoset'], null, 1, 5,
				$request['mm_start']);
		} elseif (is_null($request['mm_photoset']) && !empty($request['mm_flickrtags'])) {
			// prepare search params
			$params = array(
				'user_id' => $user['user_id'],
				'tags' => $request['mm_flickrtags'],
				'page' => $request['mm_start'],
				'per_page' => 5
			);
		
			// look for photos matching the supplied criteria
			$photos = $FLICKR->photosSearch($params);
		}
		$BASE->utility->smarty->assign('photos', $photos);
		
		// create page index
		$page_count = intval(Base_Cnc::ifsetor($photos['pages'], null));
		$BASE->utility->smarty->assign('page_index', $FLICKR->_flickrPageIndex($page_count));		
	} catch (Exception $e) {
		$BASE->utility->smarty->assign('error', $e->getMessage());
	}
		
	// assign request params
	$BASE->utility->smarty->assign('request', $request);
	
	// set header
	header("Content-Type: text/html; charset=utf-8");
	
	// display the template
	$BASE->utility->smarty->display('mediamanager/mediamanager.html');
	
	// flush the buffer
	@ob_end_flush();
	exit;

} catch (Exception $e) {
	// clean buffer
	if (!$BASE->debug_enabled()) {
		@ob_end_clean();
	}
	
	// raise error
	$BASE->error->displayException($e, $BASE->utility->smarty, 'error_mediamanager.html');
	$BASE->error->triggerException($e);
	
	// exit
	exit;
}
?>