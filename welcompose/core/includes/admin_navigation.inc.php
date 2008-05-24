<?php

/**
 * Project: Welcompose
 * File: admin_navigation.inc.php
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
