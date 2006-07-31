<?php

/**
 * Project: Oak
 * File: project.class.php
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

class Application_Project {
	
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
		
	} catch (Exception $e) {
		
		// trigger error
		printf('%s on Line %u: Unable to start base class. Reason: %s.', $e->getFile(),
			$e->getLine(), $e->getMessage());
		exit;
	}
}

/**
 * Singleton. Returns instance of the Application_Project object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Application_Project::$instance == null) {
		Application_Project::$instance = new Application_Project(); 
	}
	return Application_Project::$instance;
}

/**
 * Not implemented in the free version of this software.
 */
public function addProject ($sqlData)
{
	return false;
}

/**
 * Not implemented in the free version of this software.
 */
public function updateProject ($id, $sqlData)
{
	return false;
}

/**
 * Not implemented in the free version of this software.
 */
public function deleteProject ($id)
{
	return false;
}

/**
 * Selects one project. Takes the project id as first argument.
 * Returns array with project information.
 * 
 * @throws Application_ProjectException
 * @param int Project id
 * @return array
 */
public function selectProject ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_ProjectException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`application_projects`.`id` AS `id`,
			`application_projects`.`owner` AS `owner`,
			`application_projects`.`name` AS `name`,
			`application_projects`.`url_name` AS `url_name`,
			`application_projects`.`date_modified` AS `date_modified`,
			`application_projects`.`date_added` AS `date_added`
		FROM
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		WHERE 
			1
	";
	
	// prepare where clauses
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `application_projects`.`id` = :id ";
		$bind_params['id'] = (int)$id;
	}
	
	// add limits
	$sql .= ' LIMIT 1';
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more projects. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>owner, int, optional: Project owner id</li>
 * <li>name, string, optional: Project name</li>
 * <li>user, int, optional: User id</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * <li>order_marco, string, otpional: How to sort the result set.
 * Supported macros:
 *    <ul>
 *        <li>DATE_ADDED: sort by date added</li>
 *        <li>DATE_MODIFIED: sort by date modified</li>
 *        <li>NAME: sort by name</li>
 *    </ul>
 * </li>
 * </ul>
 * 
 * @throws Application_ProjectException
 * @param array Select params
 * @return array
 */
public function selectProjects ($params = array())
{
	// define some vars
	$owner = null;
	$name = null;
	$user = null;
	$start = null;
	$limit = null;
	$order_macro = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Application_ProjectException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'name':
			case 'order_macro':
					$$_key = (string)$_value;
				break;
			case 'owner':
			case 'user':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Application_ProjectException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'NAME' => '`application_projects`.`name`',
		'DATE_ADDED' => '`application_projects`.`date_added`',
		'DATE_MODIFIED' => '`application_projects`.`date_modified`'
	);
	
	// prepare query
	$sql = "
		SELECT 
			`application_projects`.`id` AS `id`,
			`application_projects`.`owner` AS `owner`,
			`application_projects`.`name` AS `name`,
			`application_projects`.`url_name` AS `url_name`,
			`application_projects`.`date_modified` AS `date_modified`,
			`application_projects`.`date_added` AS `date_added`
		FROM
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		LEFT JOIN
			".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
		  ON
			`application_projects`.`id` = `user_users2application_projects`.`project`
		WHERE 
			1
	";
	
	// prepare where clauses
	if (!empty($owner) && is_numeric($owner)) {
		$sql .= " AND `application_projects`.`owner` = :owner ";
		$bind_params['owner'] = (int)$owner;
	}
	if (!empty($user) && is_numeric($user)) {
		$sql .= " AND `user_users2application_projects`.`user` = :user ";
		$bind_params['user'] = (int)$user;
	}
	if (!empty($name) && is_scalar($name)) {
		$sql .= " AND `application_projects`.`name` = :name ";
		$bind_params['name'] = (int)$name;
	}
	
	// add sorting
	if (!empty($order_macro)) {
		$HELPER = load('utility:helper');
		$sql .= " ORDER BY ".$HELPER->_sqlForOrderMacro($order_macro, $macros);
	}
	
	// add limits
	if (empty($start) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u", $limit);
	}
	if (!empty($start) && is_numeric($start) && !empty($limit) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u, %u", $start, $limit);
	}

	return $this->base->db->select($sql, 'multi', $bind_params);
}

/**
 * Method to count available projects. Takes key=>value array
 * with select params as first argument. Returns int.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>owner, int, optional: Project owner id</li>
 * <li>name, string, optional: Project name</li>
 * </ul>
 * 
 * @throws Application_ProjectException
 * @param array Select params
 * @return int
 */
public function countProjects ($params = array())
{
	// define some vars
	$owner = null;
	$name = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Application_ProjectException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'name':
					$$_key = (string)$_value;
				break;
			case 'owner':
					$$_key = (int)$_value;
				break;
			default:
				throw new Application_ProjectException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		WHERE 
			1
	";
	
	// prepare where clauses
	if (!empty($owner) && is_numeric($owner)) {
		$sql .= " AND `application_projects`.`owner` = :owner ";
		$bind_params['owner'] = (int)$owner;
	}
	if (!empty($name) && is_scalar($name)) {
		$sql .= " AND `application_projects`.`name` = :name ";
		$bind_params['name'] = (int)$name;
	}

	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Looks for default project and returns array with project
 * information. Throws exception if no default project
 * can be found.
 * 
 * @throws Application_ProjectException
 * @return array
 */
public function selectDefaultProject ()
{
	// get id of the default project
	$sql = "
		SELECT 
			`application_projects`.`id` AS `id`
		FROM
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		WHERE
			`application_projects`.`default` = '1'
		LIMIT
			1
	";
	
	// execute query
	$result = (int)$this->base->db->select($sql, 'field');
	
	// make sure that there is some default page
	if ($result < 1) {
		throw new Application_ProjectException("Unable to find a default project");
	}
	
	// return complete project information
	return $this->selectProject($result);
}

/**
 * Looks for a project with the given url name and returns array 
 * with project information. Throws exception if no project with
 * the given name can be found.
 * 
 * @throws Application_ProjectException
 * @param string Project's url name
 * @return array
 */
public function selectProjectUsingUrlName ($url_name)
{
	// input check
	if (empty($url_name) || !preg_match(OAK_REGEX_PROJECT_URL_NAME, $url_name)) {
		throw new Application_ProjectException("Input for project's url name may not be empty");
	}
	
	// get id of the default project
	$sql = "
		SELECT 
			`application_projects`.`id` AS `id`
		FROM
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		WHERE
			`application_projects`.`url_name` = :url_name
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'url_name' => $url_name
	);
	
	// execute query
	$result = (int)$this->base->db->select($sql, 'field', $bind_params);
	
	// make sure that there is some default page
	if ($result < 1) {
		throw new Application_ProjectException("Unable to find a project with the given name");
	}
	
	// return complete project information
	return $this->selectProject($result);
}

/**
 * Checks if a project with the given id exists or not. Takes the
 * project id as first argument, returns bool.
 *
 * @throws Application_ProjectException
 * @param int Project id
 * @return bool
 */
public function projectExists ($id) 
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_ProjectException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*) AS `total`
		FROM
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		WHERE 
			`application_projects`.`id` = :id
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// evaluate result
	if (intval($this->base->db->select($sql, 'field', $bind_params)) === 1) {
		return true;
	} else {
		return false;
	}
}

/**
 * Makes sure that a user session is always attached to a project. Returns
 * id of the current project.
 *
 * @throws Application_ProjectException
 * @param int User id
 * @return int Project id
 */
public function initProjectAdmin ($user)
{
	// input check
	if (empty($user) || !is_numeric($user)) {
		throw new Application_ProjectException("Input for parameter user is expected to be numeric");
	}
	
	// load cookie class
	$COOKIE = load('utility:cookie');
		
	// let's see if there's a valid project id embedded in the cookie
	$current_project = $COOKIE->adminGetCurrentProject();
	$current_project = Base_Cnc::filterRequest($current_project, OAK_REGEX_NUMERIC);
	
	// if the project id is valid and if there's a project with the given id, set
	// the current project constant and exit.
	if (!is_null($current_project) && $this->projectExists($current_project)) {
		// load user class
		$USER = load('user:user');
		
		// let's see if the current user is part of the current project
		if (!$USER->userIsAuthor($user, $current_project)) {
			throw new Application_ProjectException("User is not an author of the found project");
		}
		if (!$USER->userIsActive($user, $current_project)) {
			throw new Application_ProjectException("User is not active");
		}
		
		// define constant
		define('OAK_CURRENT_PROJECT', (int)$current_project);
		
		// return id of the current project
		return OAK_CURRENT_PROJECT;
		
	// if the project id is invalid or if there isn't a project with the given id,
	// simply fetch one of the other projects.
	} else {
		// search project table for one of the other projects
		$select_params = array(
			'order_macro' => 'NAME',
			'limit' => 1
		);
		$result = $this->selectProjects($select_params);
		
		// let's see if we found a project
		if (!empty($result[0]['id']) && is_numeric($result[0]['id']) && intval($result[0]['id']) > 0) {
			// define constant
			define('OAK_CURRENT_PROJECT', (int)$result[0]['id']);
			
			// load user class
			$USER = load('user:user');

			// let's see if the current user is part of the current project
			if (!$USER->userIsAuthor($user, OAK_CURRENT_PROJECT)) {
				throw new Application_ProjectException("User is not an author of the found project");
			}
			if (!$USER->userIsActive($user, OAK_CURRENT_PROJECT)) {
				throw new Application_ProjectException("User is not active");
			}
			
			// put the project id into the cookie
			$COOKIE->adminSwitchCurrentProject(OAK_CURRENT_PROJECT);
			
			// return id of the current project
			return OAK_CURRENT_PROJECT;
			
		// ok. there's no usable project. let's give up.
		} else {
			throw new Application_ProjectException("Unable to find a usable project");
		}
	}
}

/** 
 * Sets OAK_CURRENT_PROJECT constant. Returns id of the current
 * project.
 * 
 * @return int Project id
 */ 
public function initProjectPublicArea ()
{
	// get user supplied project name
	$user_supplied_name = Base_Cnc::filterRequest($_REQUEST['project'],
		OAK_REGEX_PROJECT_URL_NAME);
		
	// if the user supplied name is null, we need to look for the default
	// project
	if (is_null($user_supplied_name)) {
		// get default project
		$default_project = $this->selectDefaultProject();
		
		// define current project constant
		define("OAK_CURRENT_PROJECT", (int)$default_project['id']);
		
		// return project id
		return (int)$default_project['id'];
	} else {
		// let's see if the project exists
		$project = $this->selectProjectUsingName($user_supplied_name);
		
		// define current project constant
		define("OAK_CURRENT_PROJECT", (int)$project['id']);
		
		// return project id
		return (int)$project['id'];
	}
}

/**
 * Switches current project. Takes the id of the new project as
 * first argument. Returns bool.
 *
 * @throws Application_ProjectException
 * @param int Project id
 * @return bool
 */
public function switchProject ($new_project)
{
	if (empty($new_project) || is_numeric($new_project)) {
		// do access check here
		if (true == false) {
			throw new Application_ProjectException("You are not allowed to execute this operation");
		}
		
		// get cookie class
		$COOKIE = load('utility:cookie');
		
		// switch project
		return $COOKIE->adminSwitchCurrentProject($new_project);
	}
	
	return false;
}

// end of class
}

class Application_ProjectException extends Exception { }

?>