<?php

/**
 * Project: Oak_Plugins
 * File: oak_plugin_textmacro_get_media.php
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
 * @package Oak_Plugins
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

/** 
 * Replaces link generator instructions with real urls. Format looks
 * something like this: {get_media arg1=value1 arg2="value2"} 
 *
 * @param string Text
 * @return string
 */
function oak_plugin_textmacro_get_media ($str)
{
	// input check
	if (!is_scalar($str)) {
		trigger_error("Input for parameter str is expected to be scalar", E_USER_ERROR);
	}
	
	// load plugin utility
	$PLUGINUTILITY = load('Utility:PluginUtility');
	
	// parse link generator instructions and replace them with real urls
	return preg_replace_callback($PLUGINUTILITY->getTextMacroTagPattern('get_media'),
		'oak_plugin_textmacro_get_media_callback', $str);
}

/**
 * Callback function for the get_media text macro.
 *
 * @param array
 * @return string
 */
function oak_plugin_textmacro_get_media_callback ($args)
{
	// input check
	if (!is_array($args)) {
		trigger_error("Input for parameter args is expected to be an array", E_USER_ERROR);
	}
	
	// load plugin utility
	$PLUGINUTILITY = load('Utility:PluginUtility');
	
	// load media object class
	$OBJECT = load('Media:Object');
	
	// parse params
	$params = $PLUGINUTILITY->getTextMacroTagArgs($args);
	
	// make sure that we got an object id. if there's none, we have to
	// return an empty url.
	if (empty($params['id']) || !is_numeric($params['id'])) {
		return '';
	}
	
	// generate url using the built-in methods and return it
	return $OBJECT->getWwwPathToObjectUsingId($params['id']);
}

?>