<?php

/**
 * Project: Welcompose
 * File: function.mm_icon.php
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
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

function smarty_function_mm_icon ($params, &$smarty)
{
	// input check
	if (!is_array($params)) {
		$smarty->trigger_error("Input for parameter params is not an array");
	}
	
	// load media object class
	$OBJECT = load('Media:Object');
	
	// import mime type from params array
	$mime_type = Base_Cnc::filterRequest($params['mime_type'], WCOM_REGEX_MIME_TYPE);
	
	return $OBJECT->mimeTypeToIcon($mime_type);
}

?>