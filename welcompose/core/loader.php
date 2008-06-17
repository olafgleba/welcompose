<?php

/**
 * Project: Welcompose
 * File: loader.php
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

/**
 * Class loader
 * 
 * Common function to load a class. Takes the class name as first
 * argument and returns the object. Makes it easier to change the
 * functionality of a class.
 * 
 * <b>How to modify a class</b>
 * 
 * Let's say you like to modify a class named balance, especially
 * their method add(). The first step is to create an empty file
 * called my_balance.class.php. Then you create a new class that
 * extends balance and add your own add() method:
 * 
 * <pre>
 * <?php
 * 
 * class my_balance extends balance {
 * 
 * public function add()
 * {
 * 	// your code goes here
 * }
 * 
 * }
 *
 * ?>
 * </pre>
 * 
 * Now you can change the loader for balance within load(). If
 * the old loader looks like
 * 
 * <pre>
 * case 'balance':
 * 	require_once('balance.class.php');
 * 	return balance::instance();
 * </pre>
 * 
 * the new loader for my_balance is
 * 
 * <pre>
 * case 'balance': // <- leave this one alone!
 * 	require_once('my_balance.class.php');
 * 	return my_balance::instance();
 * </pre>
 * 
 * Now, every part of the application will invoke my_balance
 * instead of balance.
 * 
 * PLEASE NOTE: ONLY MODIFY THE ORIGINAL CLASSES IF IT'S
 * IMPOSSIBLE TO AVOID THAT. AND IF YOU DO THAT, CREATE A
 * PATCH (diff -Naur) SO THAT YOU CAN REPRODUCE THE CHANGES
 * AFTER AN UPDATE! IF YOU CREATE A FORK YOU'LL END IN HELL
 * WHEN IT COMES TO AN UPDATE!
 * 
 * @param string Name of the class to load
 * @param bool Array of arguments to be passed to the singleton
 * @return object
 */
function load ($token, $args = array())
{	
	$token = strtolower($token);
	switch ((string)$token) {
		// handling of own/modified classes goes here
		
		// unified class loader
		default:
			// check token
			if (!preg_match('=^([a-z0-9-_.]+):([a-z0-9-_.]+)$=i', $token)) {
				trigger_error('Unable to recognize token format', E_USER_ERROR);
			}
			
			// split token into "namespace" and class
			list($namespace, $class) = explode(':', $token);
			
			// prepare namespace & class_path;
			$namespace_path = dirname(__FILE__).DIRECTORY_SEPARATOR.$namespace.'_classes';
			$class_path = $namespace_path.DIRECTORY_SEPARATOR.$class.'.class.php';
			
			// prepare class name
			$class_name = $namespace.'_'.$class;
			
			// try to load class and create new instance from it
			if (!class_exists($class_name)) {
				require($class_path);
			}
			
			if (!function_exists($class_name)) {
				trigger_error('Singleton for class to load does not exist', E_USER_ERROR);
			}
			
			return $class_name($args);
	}
}

?>