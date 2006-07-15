<?php

/**
 * Project: Oak_Plugins
 * File: oak_plugin_textconverter_markdown.php
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
 * Applies markdown on given string. Takes the string to convert as
 * first argument. Returns the converted string.
 *
 * @param string String to convert
 * @return string
 */
function oak_plugin_textconverter_markdown ($str)
{
	// input check
	if (!is_scalar($str)) {
		trigger_error("Input for parameter str is expected to be scalar", E_USER_ERROR);
	}
	
	// load markdown
	if (!function_exists('Markdown')) {
		$path = dirname(__FILE__).'/../../third_party/markdown.php';
		require(Base_Compat::fixDirectorySeparator($path));
	}
	
	// apply markdown
	return Markdown($str);
}

?>