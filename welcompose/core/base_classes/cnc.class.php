<?php

/**
 * Project: Welcompose
 * File: cnc.class.php
 * 
 * Copyright (c) 2008 creatics
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id: cnc.class.php 40 2006-06-18 12:05:47Z andreas $
 * 
 * @copyright 2008 creatics, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

class Base_Cnc {

/**
 * Remove slashes array
 * 
 * stripslashes() replacement for multidimensional arrays,
 * if magic_quotes_gpc is true, it will be applied twice.
 *
 * @param array
 * @return bool
 */
public static function removeSlashesArray (&$array)
{
	if (!is_array($array)) {
		return false;	
	}

	foreach ($array as $key => $value) {
		if (is_array($value)) {
			Base_Cnc::removeSlashesArray($value);
		}
		if (is_string($value)) {
			if (ini_get('magic_quotes_gpc')) {
				$value = stripslashes(stripslashes($value));
			} else {
				$value = stripslashes($value);
			}
		}
	}
	return true;
}

/**
 * Remove slashes string
 * 
 * Replacement for stripslashes(), that takes care
 * of the actual magic_quotes_gpc setting.
 * 
 * @param string
 * @return bool
 */
public static function removeSlashesString (&$str)
{
	if(ini_get("magic_quotes_gpc")){
		$str = stripslashes(stripslashes($str));
	} else {
		$str = stripslashes($str);
	}
	
	return true;
}

/**
 * Unique id
 * 
 * Creates unique id using rand() and sha1() or md5().
 * Two different id lengths are supported:
 * 
 * <ul>
 * <li>40 (sha1() will be applied on the id)</li>
 * <li>32 (mh5() will be applied on the id)</li>
 * </ul>
 * 
 * @param int Id length
 * @return string
 * @return bool
 */
public static function uniqueId ($length = 40)
{
	switch ($length) {
		case 40:
			return sha1(uniqid(rand(), true));
		case 32:
			return md5(uniqid(rand(), true));
	}
}

/**
 * Remove trailing slash
 *
 * Removes the trailing slash from a path to a directory
 *
 * @param string path to directory (with trailing slash)
 * @return string path to directory (without trailing slash)
 */
public static function removeTrailingSlash ($dir)
{
	if (substr($dir, -1) == DIRECTORY_SEPARATOR) {
		$dir = substr($dir, 0, -1);
	}
	
	return $dir;
}

/**
 * Filter request vars
 *
 * Function to check if request vars (GPC etc.) match the given
 * regex. You can pass undefined variables without getting
 * a notice. When everything's fine, the function will return
 * the input. Otherwise it will return NULL.
 *
 * @var mixed Input
 * @var string Regular expression
 * @return mixed
 */
public static function filterRequest (&$var, $regex)
{	
	if (isset($var) && preg_match($regex, $var)) {
		return $var;
	} else {
		return null;
	}
}

/**
 * Ifsetor
 * 
 * Userspace implementation of ifsetor. Short hand version
 * of if ... else ... check for unitialized vars.
 * 
 * @param mixed Var to check
 * @param mixed Alternative to return
 * @return mixed
 */
public static function ifsetor (&$var, $alt)
{
	if (isset($var)) {
		return $var;	
	} else {
		return $alt;	
	}
}

/**
 * Random string
 * 
 * Generates random string consisting of
 * chars and numbers.
 *
 * @param int String length
 * @return string
 */
public function randomString ($length) 
{
	// cast length to int
	$length = (int)$length;
	
	// initialize string
	$str = null;
	
	// compose string
	for($i=0;$i<$length;$i++) {
  		switch(rand(1,3)) {
			case 1:
					// 0-9
					$str .= chr(rand(48,57)); 
				break;
			case 2:
					// A-Z
					$str .= chr(rand(65,90));
				break;
			case 3:
					// a-z
					$str .= chr(rand(97,122));
				break;
		}
	}
	
	return $str;
}

/**
 * Tests if keys in supplied array are numeric. Takes array to test as first
 * argument. Returns bool.
 *
 * @param array Array to test
 * @return bool
 */
public static function testArrayForNumericKeys (&$array)
{
	// check input
	if (!is_array($array)) {
		return false;
	}
	
	// test sql-in-array for numeric keys	
	foreach ($array as $_key => $_value) {
		if (!is_numeric($_key)) {
			return false;
		}
	}
	
	return true;
}

/**
 * Tests if values in supplied array are numeric. Takes array to test as first
 * argument. Returns bool.
 *
 * @param array Array to test
 * @return bool
 */
public static function testArrayForNumericValues (&$array)
{
	// check input
	if (!is_array($array)) {
		return false;
	}
	
	// test sql-in-array for numeric values	
	foreach ($array as $_key => $_value) {
		if (!is_numeric($_key)) {
			return false;
		}
	}
	
	return true;
}

// End of class
}

?>