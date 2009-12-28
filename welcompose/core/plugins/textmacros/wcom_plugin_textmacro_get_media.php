<?php

/**
 * Project: Welcompose_Plugins
 * File: wcom_plugin_textmacro_get_media.php
 * 
 * Copyright (c) 2008 creatics
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
 * @copyright 2008 creatics, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose_Plugins
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/** 
 * Replaces link generator instructions with real urls. Format looks
 * something like this: {get_media arg1=value1 arg2="value2"} 
 *
 * @param string Text
 * @return string
 */
function wcom_plugin_textmacro_get_media ($str)
{
	// input check
	if (!is_scalar($str)) {
		trigger_error("Input for parameter str is expected to be scalar", E_USER_ERROR);
	}
	
	// load plugin utility
	$PLUGINUTILITY = load('Utility:PluginUtility');
	
	// parse link generator instructions and replace them with real urls
	return preg_replace_callback($PLUGINUTILITY->getTextMacroTagPattern('get_media'),
		'wcom_plugin_textmacro_get_media_callback', $str);
}

/**
 * Callback function for the get_media text macro.
 *
 * @param array
 * @return string
 */
function wcom_plugin_textmacro_get_media_callback ($args)
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