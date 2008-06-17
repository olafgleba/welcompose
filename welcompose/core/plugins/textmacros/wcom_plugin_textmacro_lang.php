<?php

/**
 * Project: Welcompose_Plugins
 * File: wcom_plugin_textmacro_lang.php
 * 
 * Copyright (c) 2008 creatics media.systems
 * 
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id$
 * 
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose_Plugins
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/** 
 * Wraps a lang tag (<span lang="<lang>">) around a some text. This may
 * be a word, a sentence or a whole paragraph. Syntax:
 *
 * <code>
 * If you like to get a beer in Germany, ask for a {lang value="de"}Bier{/lang}.
 * </code> 
 *
 * @param string Text
 * @return string
 */
function wcom_plugin_textmacro_lang ($str)
{
	// input check
	if (!is_scalar($str)) {
		trigger_error("Input for parameter str is expected to be scalar", E_USER_ERROR);
	}
	
	// parse gmap tags and replace it with a google map.
	return preg_replace_callback("={lang\s*value\=(\"(\w+)\"|'(\w+)')}(.*?){\s*/lang\s*}=",
		'wcom_plugin_textmacro_lang_callback', $str);
}

/**
 * Callback function for the lang tag macro.
 *
 * @param array
 * @return string
 */
function wcom_plugin_textmacro_lang_callback ($args)
{
	// input check
	if (!is_array($args)) {
		trigger_error("Input for parameter args is expected to be an array", E_USER_ERROR);
	}
	
	// create lang tag
	return sprintf("<span lang=\"%s\">%s</span>", (empty($args[2]) ? $args[3] : $args[2]),
		$args[4]);

}

?>