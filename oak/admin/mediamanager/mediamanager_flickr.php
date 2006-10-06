<?php

/**
 * Project: Oak
 * File: mediamanager_flickr.php
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
 * $Id: parse.navigation.php 291 2006-07-31 19:46:13Z andreas $
 *
 * @copyright 2006 creatics media.systems
 * @author Olaf Gleba
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

	// load Base_Session
	$SESSION = load('Base:Session');

	// load User_User
	$USER = load('User:User');

	// load User_Login
	/* @var $LOGIN User_Login */
	$LOGIN = load('User:Login');

	// load Application_Project
	$PROJECT = load('Application:Project');
	
	// load Media_Object
	$OBJECT = load('Media:Object');
	
	// load Utility_Helper
	$HELPER = load('Utility:Helper');
	
	// init user and project
	if (!$LOGIN->loggedIntoAdmin()) {
		header("Location: ../login.php");
		exit;
	}
	$USER->initUserAdmin();
	$PROJECT->initProjectAdmin(OAK_CURRENT_USER);
	

	/*
	
	Testweise kann folgender API-Key genommen werden:
	11bcda9f77519a4f44121ce5ee5b6a8f
	
	Ich brauche sicher folgende wrapper:
	
	flickr.people.findByUsername
	flickr.urls.getUserPhotos
	flickr.photos.search
	flickr.photosets.getList
	flickr.photosets.getPhotos

	als Argumente brauchen wir immer
	
	api_key
	user_id, bzw. photoset_id
	
	bei flickr.photos.search noch mind. das argument 'tags' (da ist die funktionalität des input feldes, das MM nutzt)


	Procedere soll folgend skizziert werden:
	
	- Bei Aufruf des Tabs myFlickr ist nur der 'Flickr User' Input offen.
	- Um myFlickr überhaupt nutzen zu können, wird es einen Button geben (noch nicht drin!, 6.10.),
	  der einen Request an Flickr schickt.
	  und erstmal das select 'Find by photosets' über die nsid des Users füllt.
	- 'Find by Flickr tags' macht das, was der title aussagt. Es wird innerhalb des user accounts nach
	   bilder mit den entsprechenen tags gesucht. Kommaseparierte Angaben sind möglich.
	- Bei auswahl eines sets in 'Find by photoset' werden die jeweiligen bilder des sets zusammengesucht und angezeigt.
	
	*/

	// wat unten kommt, ist nur zum testing (gewesen)

	// prepare select params
	$request = array(
		'user' => $_REQUEST['mm_user'],
		'photoset' => $_REQUEST['mm_photoset'],
		'flickrtags' => $_REQUEST['mm_flickrtags']
	);
	
	$BASE->utility->smarty->assign('request', $request);
	
	$BASE->utility->smarty->assign('pagetype', Base_Cnc::filterRequest($_REQUEST['mm_pagetype'], OAK_REGEX_NUMERIC));
	
	// assign image path
	$BASE->utility->smarty->assign('image_store_www', $BASE->_conf['image']['store_www']);
	
	// display the correlated mediamanager template
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
	Base_Error::triggerException($BASE->utility->smarty, $e);	
	
	// exit
	exit;
}
?>