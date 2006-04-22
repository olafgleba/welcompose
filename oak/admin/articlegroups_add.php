<?php

/**
 * Project: Shop
 * File: articlegroups_add.php
 *
 * Copyright (c) 2004 - 2006 sopic GmbH
 *
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License
 * http://www.opensource.org/licenses/osl-2.1.php
 *
 * $Id: articlegroups_add.php 2 2006-03-20 11:43:20Z andreas $
 *
 * @copyright 2004 - 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Shop
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

// get loader
$path_parts = array(
	dirname(__FILE__),
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
$deregister_globals_path = dirname(__FILE__).'/../core/includes/deregister_globals.inc.php';
require(Base_Compat::fixDirectorySeparator($deregister_globals_path));

try {
	// start output buffering
	@ob_start();
	
	// load smarty
	$smarty_admin_conf = dirname(__FILE__).'/../core/conf/smarty_admin.inc.php';
	$BASE->utility->loadSmarty(Base_Compat::fixDirectorySeparator($smarty_admin_conf), true);
	
	// load gettext
	$gettext_path = dirname(__FILE__).'/../core/includes/gettext.inc.php';
	include(Base_Compat::fixDirectorySeparator($gettext_path));
	gettextInitSoftware($BASE->_conf['locales']['all']);
	
	// start session
	/* @var $SESSION session */
	$SESSION = load('base:session');
	
	// load articlegroup class
	/* @var $ARTICLEGROUP articlegroup */
	$ARTICLEGROUP = load('catalogue:articlegroup');
	
	// start new HTML_QuickForm
	$FORM = $BASE->utility->loadQuickForm('articlegroup', 'post');
	
	// textfield for group number
	$FORM->addElement('text', 'number', gettext('Number'), 
		array('id' => 'groupNumber', 'maxlength' => 11, 'class' => 'iMiddle'));
	$FORM->applyFilter('number', 'trim');
	$FORM->applyFilter('number', 'strip_tags');
	$FORM->addRule('number', gettext('The Article group number is not numeric'), 'numeric');
	
	// textfield for article title
	$FORM->addElement('text',  'name', gettext('Name'),
		array('id' => 'groupName', 'maxlength' => 255, 'class' => 'iLong'));
	$FORM->applyFilter('title', 'trim');
	$FORM->applyFilter('title', 'strip_tags');
	$FORM->addRule('name', gettext('Please enter an article group name'), 'required');
	
	// textarea for description
	$FORM->addElement('textarea', 'description', gettext('Description'),
		array('id' => 'groupDescription', 'cols' => 62, 'rows' => 4));
	$FORM->applyFilter('description', 'trim');
	$FORM->applyFilter('description', 'strip_tags');
	
	// textfield for url
	$FORM->addElement('text', 'url', gettext('URL'), 
		array('id' => 'groupUrl', 'maxlength' => 255, 'class' => 'iLong'));
	$FORM->applyFilter('url', 'trim');
	$FORM->applyFilter('url', 'strip_tags');
	$FORM->addRule('url', gettext('Please enter a valid URL'), 'regex',
		SHOP_REGEX_URL);
	
	// submit button
	$FORM->addElement('submit', 'submit', gettext('Add article group'),
		array('class' => 'iSubmit'));
		
	// validate it
	if (!$FORM->validate()) {
		// render it
		$renderer = $BASE->utility->loadQuickFormSmartyRenderer();
		include('quickform.tpl.php');
		$FORM->accept($renderer);
	
		// assign the form to smarty
		$BASE->utility->smarty->assign('form', $renderer->toArray());
	
	    // build $session
	    $session = array(
			'response' => Base_Cnc::filterRequest($_SESSION['response'], SHOP_REGEX_NUMERIC)
	    );
	    
	    // assign $_SESSION to smarty
	    $BASE->utility->smarty->assign('session', $session);
	    
	    // empty $_SESSION
	    if (!empty($_SESSION['response'])) {
	        $_SESSION['response'] = '';
	    }
	    
	    // build $request
	    $request = array(
			'response' => Base_Cnc::filterRequest($_REQUEST['response'], SHOP_REGEX_NUMERIC)
	    );
	    
	    // assign $_REQUEST to smarty
	    $BASE->utility->smarty->assign('request', $_REQUEST);
	    
	    // include the navigation
		include('navigation.inc.php');
		
		// display the form
		define("SHOP_TEMPLATE_KEY", md5($_SERVER['REQUEST_URI']));
		$BASE->utility->smarty->display('articlegroups_add.html', SHOP_TEMPLATE_KEY);
		
		// flush the buffer
		@ob_end_flush();
		
		exit;
	} else {
		// freeze the form
		$FORM->freeze();
		
		// create the article group
		$sqlData = array();
		$sqlData['number'] = $FORM->exportValue('number');
		$sqlData['name'] = $FORM->exportValue('name');
		$sqlData['description'] = $FORM->exportValue('description');
		$sqlData['url'] = $FORM->exportValue('url');
		
		// insert it
		try {
			// begin transaction
			$BASE->db->begin();
			
			// execute operation
			$ARTICLEGROUP->addArticleGroup($sqlData);
			
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
		@ob_end_clean();
		
		// redirect
		header("Location: articlegroups_add.php");
		exit;
	}
} catch (Exception $e) {
	// clean buffer
	@ob_end_clean();
	
	// raise error
	Base_Error::triggerException($BASE->utility->smarty, $e);	
	
	// exit
	exit;
}
?>