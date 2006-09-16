<?php

/**
 * Project: Oak_Plugins
 * File: oak_plugin_textmacro_lang.php
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
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak_Plugins
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
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
function oak_plugin_textmacro_lang ($str)
{
	// input check
	if (!is_scalar($str)) {
		trigger_error("Input for parameter str is expected to be scalar", E_USER_ERROR);
	}
	
	// parse gmap tags and replace it with a google map.
	return preg_replace_callback("={lang\s*value\=(\"(\w+)\"|'(\w+)')}(.*?){\s*/lang\s*}=",
		'oak_plugin_textmacro_lang_callback', $str);
}

/**
 * Callback function for the lang tag macro.
 *
 * @param array
 * @return string
 */
function oak_plugin_textmacro_lang_callback ($args)
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