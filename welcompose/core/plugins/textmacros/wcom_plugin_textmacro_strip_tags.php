<?php

/**
 * Project: Welcompose_Plugins
 * File: wcom_plugin_textmacro_strip_tags.php
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
 * Applies PHP's strip_tags() on given string. Takes the string to 
 * strip as first argument. Returns the stripped string.
 *
 * @param string String to strip
 * @return string
 */
function wcom_plugin_textmacro_strip_tags ($str)
{
	// input check
	if (!is_scalar($str)) {
		trigger_error("Input for parameter str is expected to be scalar", E_USER_ERROR);
	}
	
	return strip_tags($str);
}

?>