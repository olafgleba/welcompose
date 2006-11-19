<?php

/**
 * Project: Oak
 * File: admin_navigation.inc.php
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
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

function oak_admin_navigation_in_area ($link, $file_path)
{
	$link_area = preg_replace("=^([a-z]+)_(add|edit|select).php$=", '$1', $link);
	$current_file_area = preg_replace("=^([a-z]+)_(add|edit|select).php$=", '$1', basename($file_path));
	
	if ($link_area == $current_file_area) {
		return true;
	} else {
		return false;
	}
}

function oak_admin_navigation_is_self ($link, $file_path)
{
	$current_file_name = basename($file_path);
	
	if ($current_file_name == $link) {
		return true;
	} else {
		return false;
	}
}

?>
