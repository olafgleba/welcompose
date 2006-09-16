<?php

/**
 * Project: Oak
 * File: pluginutil.class.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/apache2.0.php Apache License, Version 2.0
 */

class Utility_PluginUtil {
	
	/**
	 * Singleton
	 * @var object
	 */
	private static $instance = null;
	
	/**
	 * Reference to base class
	 * @var object
	 */
	public $base = null;
	
/**
 * Start instance of base class, load configuration and
 * establish database connection. Please don't call the
 * constructor direcly, use the singleton pattern instead.
 */
protected function __construct()
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
 * Singleton. Returns instance of the Utility_PluginUtil object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Utility_PluginUtil::$instance == null) {
		Utility_PluginUtil::$instance = new Utility_PluginUtil(); 
	}
	return Utility_PluginUtil::$instance;
}

/**
 * Returns PCRE search pattern for extraction of a text converter tag. Takes the name
 * of the text converter tag as first argument. The second argument is a switch to
 * enable/disable multi line tags. 
 *
 * @throws Utility_PluginUtilException
 * @param string Tag name
 * @param bool Multi line tags
 * @return string
 */
public function getTextConverterTagPattern ($name, $dotall = false)
{
	// test name
	if (empty($name) || !preg_match(OAK_REGEX_TEXT_MACRO_INTERNAL_NAME, $name)) {
		throw new Utility_PluginUtilException("name is expected to be a valid internal text macro name");
	}
	if (!is_bool($dotall)) {
		throw new Utility_PluginUtilException("dotall is expected to be boolean");
	}
	
	// prepare pattern
	$pattern = "={%s(.*?)}=%s";
	$pattern = sprintf($pattern, preg_quote($name, '='), (($dotall === true) ? 's' : null));
	
	return $pattern;
}

/**
 * Creates key=>value array from a text converter tag string. Takes array with
 * preg_replace_callback() output as first argument. The second argument is a switch
 * to enable/disable key/value security/sanitizing. Returns array.
 * 
 * @throws Utility_PluginUtilException
 * @param array preg_replace_callback() output
 * @param bool Enable/disable security
 * @return array
 */
public function getTextConverterTagArgs ($args, $security = true)
{
	// input check
	if (!is_array($args)) {
		throw new Utility_PluginUtilException("Input for parameter args is expected to be an array");
	}
	if (!is_bool($security)) {
		throw new Utility_PluginUtilException("security is expected to be boolean");
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

class Utility_PluginUtilException extends Exception { }

?>