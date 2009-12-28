<?php

/**
 * Project: Welcompose_Plugins
 * File: wcom_plugin_textmacro_get_abbreviation.php
 * 
 * Copyright (c) 2009 creatics
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id$
 * 
 * @copyright 2009 creatics, Olaf Gleba
 * @author Olaf Gleba
 * @package Welcompose_Plugins
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/** 
 * Enhance abbreviation occurrences with provided attributes.  
 *
 * @param string Abbreviation
 * @return string
 */
function wcom_plugin_textmacro_get_abbreviation ($str)
{
	// input check
	if (!is_scalar($str)) {
		trigger_error("Input for parameter str is expected to be scalar", E_USER_ERROR);
	}
			
	// parse abbreviation instructions and replace them with real values
	return preg_replace_callback("={get_abbreviation(.*?)}=i",
		'wcom_plugin_textmacro_get_abbreviation_callback', $str);
}

/**
 * Callback function for the abbreviation text macro.
 *
 * @param array
 * @return string
 */
function wcom_plugin_textmacro_get_abbreviation_callback ($args)
{
	// input check
	if (!is_array($args)) {
		trigger_error("Input for parameter args is expected to be an array", E_USER_ERROR);
	}
	
	// import tag & params from args array
	$tag = $args[0];
	$params = $args[1];
	
	// parse params
	preg_match_all("=\s*(\w+)\s*\=\s*((\w+)|\"(.*?)\"|\'(.*?)\')\s*=i", $params, $matches, PREG_SET_ORDER);
	$params = array();
	foreach ($matches as $_match) {
		$params[trim($_match[1])] = trim($_match[count($_match) - 1]);
	}
	
	// load media object class
	$ABBREVIATION = load('Content:Abbreviation');
	
	// get abbreviation tag and return it
	return $ABBREVIATION->getAbbreviation($params);
}
	
?>