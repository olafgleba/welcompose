<?php

/**
 * Project: Welcompose
 * File: admin_navigation.inc.php
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

function wcom_admin_navigation_in_area ($link, $file_path)
{
	$link_area = preg_replace("=^([a-z]+)(.*)=", '$1', $link);
	$current_file_area = preg_replace("=^([a-z]+)(.*)=", '$1', basename($file_path));
	
	if ($link_area == $current_file_area) {
		return true;
	} else {
		return false;
	}
}

function wcom_admin_navigation_is_self ($link, $file_path)
{
	$current_file_name = basename($file_path);
	
	if ($current_file_name == $link) {
		return true;
	} else {
		return false;
	}
}

?>
