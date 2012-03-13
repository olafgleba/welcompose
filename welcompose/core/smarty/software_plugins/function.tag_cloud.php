<?php

/**
 * Project: Welcompose
 * File: function.tag_cloud.php
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
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

function smarty_function_tag_cloud ($params, &$smarty)
{
	// define some vars
	$var = null;
	$page = null;
	$limit = null;
	$range = null;
	
	// check input vars
	if (!is_array($params)) {
		throw new Exception("tag_cloud: Functions params are not in an array");	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'var':
					$$_key = (string)$_value;
				break;
			case 'page':
			case 'limit':
			case 'range':
					$$_key = (int)$_value;
				break;
			default:
					throw new Exception("tag_cloud: Unknown argument");
				break;
		}
	}
	
	// check input
	if (is_null($var) || !preg_match(WCOM_REGEX_SMARTY_VAR_NAME, $var)) {
		throw new Exception("tag_cloud: Invalid var name supplied");
	}
	
	// load class loader
	$path_parts = array(
		dirname(__FILE__),
		'..',
		'..',
		'loader.php'
	);
	require_once(implode(DIRECTORY_SEPARATOR, $path_parts));
	
	// load Content_BlogTag class
	$BLOGTAG = load('Content:BlogTag');
	
	// get blog tags
	$params = array(
		'page' => $page,
		'order_macro' => 'OCCURRENCES:DESC',
		'limit' => $limit
	);
	$tags = $BLOGTAG->selectBlogTags($params);
	
	// execute prepare & return tag cloud
	$smarty->assign($var, $BLOGTAG->prepareTagsForCloud($tags, $range));
}

?>