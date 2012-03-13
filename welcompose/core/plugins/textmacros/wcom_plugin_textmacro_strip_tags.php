<?php

/**
 * Project: Welcompose_Plugins
 * File: wcom_plugin_textmacro_strip_tags.php
 * 
 * Copyright (c) 2008-2012 creatics, Olaf Gleba <og@welcompose.de>
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 *  
 * @author Andreas Ahlenstorf
 * @package Welcompose_Plugins
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
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