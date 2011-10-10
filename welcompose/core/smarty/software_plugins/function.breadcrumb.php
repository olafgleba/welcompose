<?php

/**
 * Project: Welcompose
 * File: function.breadcrumb.php
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
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

function smarty_function_breadcrumb ($params, &$smarty)
{
	// input check
	if (!is_array($params)) {
		$smarty->trigger_error("Input for parameter params is not an array");
	}
	
	// import object name from params array
	$current_page = Base_Cnc::filterRequest($params['current_page'], WCOM_REGEX_NUMERIC);
	$var = Base_Cnc::filterRequest($params['var'], WCOM_REGEX_ALPHANUMERIC);
		
	// load page class
	$PAGE = load('content:page');
	
	// assign paths
	$smarty->assign(index_page, $PAGE->selectIndexPage());
	$smarty->assign($var, $PAGE->selectPath($current_page));
}
?>