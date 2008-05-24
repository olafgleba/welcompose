<?php

/**
 * Project: Welcompose
 * File: function.mm_is_podcast_format.php
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

function smarty_function_mm_is_podcast_format ($params, &$smarty)
{
	// input check
	if (!is_array($params)) {
		$smarty->trigger_error("Input for parameter params is not an array");
	}
	
	// load media object class
	$OBJECT = load('Media:Object');
	
	// import mime type & var name from params array
	$mime_type = Base_Cnc::filterRequest($params['mime_type'], WCOM_REGEX_MIME_TYPE);
	$var = Base_Cnc::filterRequest($params['var'], WCOM_REGEX_SMARTY_VAR_NAME);
	
	// find out if the object with the given mime type can be used for a podcast
	// and assign the result to the var with the given name.
	$smarty->assign($var, $OBJECT->isPodcastFormat($mime_type));
}

?>