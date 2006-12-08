<?php

/**
 * Project: Welcompose_Plugins
 * File: wcom_plugin_textmacro_get_url.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-3.0.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Welcompose_Plugins
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

/** 
 * Replaces link generator instructions with real urls. Format looks
 * something like this: {get_url arg1=value1 arg2="value2"} 
 *
 * @param string Text
 * @return string
 */
function wcom_plugin_textmacro_get_url ($str)
{
	// input check
	if (!is_scalar($str)) {
		trigger_error("Input for parameter str is expected to be scalar", E_USER_ERROR);
	}
	
	// parse link generator instructions and replace them with real urls
	return preg_replace_callback("={get_url(.*?)}=i",
		'wcom_plugin_textmacro_get_url_callback', $str);
}

/**
 * Callback function for the get_url text macro.
 *
 * @param array
 * @return string
 */
function wcom_plugin_textmacro_get_url_callback ($args)
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
	
	// send params to url generator. we hope to get back something useful.
	$URLGENERATOR = load('Utility:UrlGenerator');
	$url = $URLGENERATOR->generateInternalLink($params);
	
	// return the url or a hash mark if the url is empty 
	if (empty($url)) {
		return '#';
	} else {
		return $url;
	}
}

?>