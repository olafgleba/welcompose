<?php

/**
 * Project: Welcompose
 * File: pluginutility.class.php
 * 
 * Copyright (c) 2008 creatics media.systems
 * 
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-3.0.php
 * 
 * $Id$
 * 
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

/**
 * Singleton. Returns instance of the Utility_PluginUtility object.
 * 
 * @return object
 */
function Utility_PluginUtility ()
{ 
	if (Utility_PluginUtility::$instance == null) {
		Utility_PluginUtility::$instance = new Utility_PluginUtility(); 
	}
	return Utility_PluginUtility::$instance;
}


class Utility_PluginUtility {
	
	/**
	 * Singleton
	 * 
	 * @var object
	 */
	public static $instance = null;
	
	/**
	 * Reference to base class
	 * 
	 * @var object
	 */
	public $base = null;
	
/**
 * Start instance of base class, load configuration and
 * establish database connection. Please don't call the
 * constructor direcly, use the singleton pattern instead.
 */
public function __construct()
{
	try {
		// get base instance
		$this->base = load('base:base');
		
		// establish database connection
		$this->base->loadClass('database');
		
	} catch (Exception $e) {
		
		// trigger error
		printf('%s on Line %u: Unable to start base class. Reason: %s.', $e->getFile(),
			$e->getLine(), $e->getMessage());
		exit;
	}
}

/**
 * Returns PCRE search pattern for extraction of a text macro tag. Takes the name
 * of the text macro tag as first argument. The second argument is a switch to
 * enable/disable multi line tags. 
 *
 * @throws Utility_PluginUtilityException
 * @param string Tag name
 * @param bool Multi line tags
 * @return string
 */
public function getTextMacroTagPattern ($name, $dotall = false)
{
	// test name
	if (empty($name) || !preg_match(WCOM_REGEX_TEXT_MACRO_INTERNAL_NAME, $name)) {
		throw new Utility_PluginUtilityException("name is expected to be a valid internal text macro name");
	}
	if (!is_bool($dotall)) {
		throw new Utility_PluginUtilityException("dotall is expected to be boolean");
	}
	
	// prepare pattern
	$pattern = "={%s(.*?)}=%s";
	$pattern = sprintf($pattern, preg_quote($name, '='), (($dotall === true) ? 's' : null));
	
	return $pattern;
}

/**
 * Creates key=>value array from a text macro tag string. Takes array with
 * preg_replace_callback() output as first argument. The second argument is a switch
 * to enable/disable key/value security/sanitizing. Returns array.
 * 
 * @throws Utility_PluginUtilityException
 * @param array preg_replace_callback() output
 * @param bool Enable/disable security
 * @return array
 */
public function getTextMacroTagArgs ($args, $security = true)
{
	// input check
	if (!is_array($args)) {
		throw new Utility_PluginUtilityException("Input for parameter args is expected to be an array");
	}
	if (!is_bool($security)) {
		throw new Utility_PluginUtilityException("security is expected to be boolean");
	}
	
	// import params from args array
	$params = $args[1];
	
	// parse params
	preg_match_all("=\s*(\w+)\s*\=\s*((\w+)|\"(.*?)\"|\'(.*?)\')\s*=i", $params, $matches, PREG_SET_ORDER);
	$params = array();
	foreach ($matches as $_match) {
		if ($security) {
			$params[strip_tags(trim($_match[1]))] = strip_tags(trim($_match[count($_match) - 1]));
		} else {
			$params[trim($_match[1])] = trim($_match[count($_match) - 1]);
		}
	}
	
	return $params;
}

// end of class
}

class Utility_PluginUtilityException extends Exception { }

?>