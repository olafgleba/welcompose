<?php

/**
 * Project: Oak
 * File: loader.php
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
 * // singleton etc. goes here
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
			if (!preg_match("=^([a-z0-9-_.]+):([a-z0-9-_.]+)$=i", $token)) {
				trigger_error("Unable to recognize token format $token", E_USER_ERROR);
			}
			
			// split token into "namespace" and class
			list($namespace, $class) = explode(':', $token);
			
			// check if namespace exists and class within namespace exists too
			$namespace_path = dirname(__FILE__).DIRECTORY_SEPARATOR.$namespace.'_classes';
			if (!is_dir($namespace_path)) {
				trigger_error("Unknown namespace $namespace", E_USER_ERROR);
			}
			$class_path = $namespace_path.DIRECTORY_SEPARATOR.$class.'.class.php';
			if (!file_exists($class_path)) {
				trigger_error("Unknown class path $class_path", E_USER_ERROR);
			}
			
			// prepare class name
			$class_name = $namespace.'_'.$class;
			
			// try to load class and create new instance from it
			if (!class_exists($class_name)) {
				require($class_path);

				// make sure the class exists
				if (!class_exists($class_name)) {
					trigger_error("Unknown class $namespace:$class", E_USER_ERROR);
				}
			}
			
			return call_user_func_array(array($class_name, 'instance'), $args);
	}
}

?>