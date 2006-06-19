<?php

/**
 * Project: Oak
 * File: cookie.class.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/apache2.0.php Apache License, Version 2.0
 */

class Utility_Cookie {
	
	/**
	 * Singleton
	 * @var object
	 */
	private static $instance = null;
	
	/**
	 * Reference to base class
	 * @var object
	 */
	public $base = null;
	
	/**
	 * Reference to admin cookie
	 * @var object
	 */
	protected $admin_cookie = null;

/**
 * Start instance of base class, load configuration and
 * establish database connection. Please don't call the
 * constructor direcly, use the singleton pattern instead.
 */
protected function __construct()
{
	try {
		// get base instance
		$this->base = load('base:base');
		
		// establish database connection
		$this->base->loadClass('database');
		
		// load cookie class
		if (!class_exists('Base_Cookie')) {
			$cookie_class_path = dirname(__FILE__).'/../base_classes/cookie.class.php';
			require(Base_Compat::fixDirectorySeparator($cookie_class_path));
		}
		
		// create instance of cookie class for admin cookie
		$this->admin_cookie = new Base_Cookie($this->base->_conf['environment']['app_key'], 'oak_admin');
		
	} catch (Exception $e) {
		
		// trigger error
		printf('%s on Line %u: Unable to start base class. Reason: %s.', $e->getFile(),
			$e->getLine(), $e->getMessage());
		exit;
	}
}

/**
 * Singleton. Returns instance of the Utility_Cookie object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Utility_Cookie::$instance == null) {
		Utility_Cookie::$instance = new Utility_Cookie(); 
	}
	return Utility_Cookie::$instance;
}

/**
 * Sets cookie with new default project. Takes new project id as first
 * argument. Returns bool.
 *
 * @throws Utility_CookieException
 * @param int Project id
 * @return bool
 */
public function adminSwitchCurrentProject ($new_project = null)
{
	// input check
	if (!empty($new_project) && !is_numeric($new_project)) {
		throw new Utility_CookieProject("Input for parameter new_project is expected to be numeric");
	}
	
	// check cookie data
	$this->adminCheckCookieData();
	
	// get cookie data
	$data = $this->admin_cookie->getCookieData();
	
	// set new default project
	$data['current_project'] = $new_project;
	
	// write data back to cookie
	$this->admin_cookie->setCookieData($data);
	
	// set new cookie
	return $this->admin_cookie->setCookie();	
}

/**
 * Returns current project id from admin cookie. If no current project
 * can be found, null will be returned.
 * 
 * @return int
 */
public function adminGetCurrentProject ()
{
	// check cookie data
	$this->adminCheckCookieData();
	
	// get cookie data
	$data = $this->admin_cookie->getCookieData();
	
	// get current project
	$current_project = Base_Cnc::filterRequest($data['current_project'], OAK_REGEX_NUMERIC);
	
	// return current project
	return $current_project;
}

/**
 * Makes sure that the cookie data structure is an array.
 */
protected function adminCheckCookieData ()
{
	// get cookie data
	$data = $this->admin_cookie->getCookieData();
	
	// make sure that the data structure is an array
	if (!is_array($data)) {
		$data = array();
	}
	
	// write data back to cookie class
	$this->admin_cookie->setCookieData($data);
}

// end of class
}

class Utility_CookieException extends Exception { }

?>