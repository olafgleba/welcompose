<?php

/**
 * Project: Welcompose
 * File: function.get_tag_word.php
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
 * @author Olaf Gleba
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/* Description
 * 
 * Enhance the meta title with tag information.
 * 
 * Due to the database layout there is no content related information
 * provided on tagged blog index pages. So we grab the request
 * variable and return the corresponding natural word.
 */

/* Example
 * <head>
 * ...
 * <title>{$page.name} | {get_tag_word tag=$get.tag}</title>
 * ...
 * </head>
 */
function smarty_function_get_tag_word ($params, &$smarty)
{	
	// check input vars
	if (!is_array($params)) {
		throw new Exception("get_tag_word: Functions params are not in an array");	
	}
	
	// import tag from params array
	$tag = Base_Cnc::filterRequest($params['tag'], WCOM_REGEX_MEANINGFUL_STRING);
	
	// process if tag is set, otherwise return false
	if (!empty($tag)) {
		// load blogtag class
		$BLOGTAG = load('content:blogtag');
	
		// fetch result
		$result = $BLOGTAG->selectBlogTagByWordUrl($tag);
	
		// return partial result array
		return $result['word'];
	} else {
		return false;
	}
}

?>