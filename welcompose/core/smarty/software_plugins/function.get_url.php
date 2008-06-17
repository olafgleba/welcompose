<?php

/**
 * Project: Welcompose
 * File: function.get_url.php
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

function smarty_function_get_url ($params, &$smarty)
{
	// send params to url generator. we hope to get back something useful.
	$URLGENERATOR = load('Utility:UrlGenerator');
	$url = $URLGENERATOR->generateInternalLink($params);
	
	// return the url or a hash mark if the url is empty 
	if (empty($url)) {
		return '#';
	} else {
		return $url;
	}
}

?>