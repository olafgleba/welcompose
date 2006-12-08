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
	
	protected $_skeleton = null;

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
	// access check
	if (!oak_check_access('Application', 'Project', 'Manage')) {
		throw new Application_ProjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Application_ProjectException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	return $this->base->db->insert(OAK_DB_APPLICATION_PROJECTS, $sqlData);
}

/**
 * Updates project. Takes the project id as first argument, a field=>value
 * array with the new row data as second argument. Returns amount of
 * affected rows.
 *
 * @throws Application_ProjectException
 * @param int Project id
 * @param array Row data
 * @return int Affected rows
 */
public function updateProject ($id, $sqlData)
{
	// access check
	if (!oak_check_access('Application', 'Project', 'Manage')) {
		throw new Application_ProjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_ProjectException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Application_ProjectException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `editable` = '1' ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(OAK_DB_APPLICATION_PROJECTS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes project from the project table. Takes the project id as
 * first argument. Returns amount of affected rows.
 * 
 * @throws Application_ProjectException
 * @param int Project id
 * @return int Amount of affected rows
 */
public function deleteProject ($id)
{
	// access check
	if (!oak_check_access('Application', 'Project', 'Manage')) {
		throw new Application_ProjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_ProjectException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `editable` = '1' ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_APPLICATION_PROJECTS, $where, $bind_params);
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
			`application_projects`.`name_url` AS `name_url`,
			`application_projects`.`default` AS `default`,
			`application_projects`.`editable` AS `editable`,
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
			`application_projects`.`name_url` AS `name_url`,
			`application_projects`.`default` AS `default`,
			`application_projects`.`editable` AS `editable`,
			`application_projects`.`date_modified` AS `date_modified`,
			`application_projects`.`date_added` AS `date_added`
		FROM
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		LEFT JOIN
			".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
		  ON
			`application_projects`.`id` = `user_users2application_projects`.`project`
		WHERE 
			`user_users2application_projects`.`user` = :user
	";
	
	// prepare bind params
	$bind_params = array(
		'user' => OAK_CURRENT_USER
	);
	
	// prepare where clauses
	if (!empty($owner) && is_numeric($owner)) {
		$sql .= " AND `application_projects`.`owner` = :owner ";
		$bind_params['owner'] = (int)$owner;
	}
	if (!empty($name) && is_scalar($name)) {
		$sql .= " AND `application_projects`.`name` = :name ";
		$bind_params['name'] = (int)$name;
	}
	
	$sql .= " GROUP BY `application_projects`.`id` ";
	
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
public function selectProjectUsingUrlName ($name_url)
{
	// input check
	if (empty($name_url) || !preg_match(OAK_REGEX_PROJECT_NAME_URL, $name_url)) {
		throw new Application_ProjectException("Input for project's url name may not be empty");
	}
	
	// get id of the default project
	$sql = "
		SELECT 
			`application_projects`.`id` AS `id`
		FROM
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		WHERE
			`application_projects`.`name_url` = :name_url
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'name_url' => $name_url
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
 * Tests given project name for uniqueness. Takes the project name as
 * first argument and an optional project id as second argument. If
 * the project id is given, this project won't be considered when checking
 * for uniqueness (useful for updates). Returns boolean true if project
 * name is unique.
 *
 * @throws Application_ProjectException
 * @param string Project name
 * @param int Project id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	/*
	// access check
	if (!oak_check_access('Application', 'Project', 'Use')) {
		throw new Application_ProjectException("You are not allowed to perform this action");
	}
	*/
	// input check
	if (empty($name)) {
		throw new Application_ProjectException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Application_ProjectException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Application_ProjectException("Input for parameter id is expected to be numeric");
	}
	
	// load helper class
	$HELPER = load('Utility:Helper');
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		WHERE
			(
				`name` = :name
		  	  OR
				`name_url` = :name_url
			)
	";
	
	// prepare bind params
	$bind_params = array(
		'name' => $name,
		'name_url' => $HELPER->createMeaningfulString($name)
	);
	
	// if id isn't empty, add id check
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `id` != :id ";
		$bind_params['id'] = (int)$id;
	} 
	
	// execute query and evaluate result
	if (intval($this->base->db->select($sql, 'field', $bind_params)) > 0) {
		return false;
	} else {
		return true;
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
	
	// get current project id from session
	$current_project = Base_Cnc::filterRequest($_SESSION['admin']['project'], OAK_REGEX_NUMERIC); 
	
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
	} else {
		throw new Application_ProjectException("No usable project found");
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
		OAK_REGEX_PROJECT_NAME_URL);
		
	// if the user supplied name is null, we need to look for the default
	// project
	if (is_null($user_supplied_name)) {
		// get default project
		$default_project = $this->selectDefaultProject();
		
		// define current project constant
		define("OAK_CURRENT_PROJECT", (int)$default_project['id']);
		define("OAK_CURRENT_PROJECT_NAME", $default_project['name_url']);
		
		// return project id
		return (int)$default_project['id'];
	} else {
		// let's see if the project exists
		$project = $this->selectProjectUsingUrlName($user_supplied_name);
		
		// define current project constant
		define("OAK_CURRENT_PROJECT", (int)$project['id']);
		define("OAK_CURRENT_PROJECT_NAME", $project['name_url']);
		
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
	// access check
	if (!oak_check_access(null, null, null)) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
	if (empty($new_project) || is_numeric($new_project)) {
		// load login class
		$LOGIN = load('user:login');
		return $LOGIN->switchAdminProject($new_project);
	}
	
	return false;
}

/**
 * Initializes project using the provided skeleton definition.
 * Takes the project id as first argument. Returns bool.
 *
 * @throws Application_Project
 * @param int Project id
 * @return bool
 */
public function initFromSkeleton ($project)
{
	// input check
	if (empty($project) || !is_numeric($project)) {
		throw new Application_ProjectException("Input for parameter project is not numeric");
	}
	
	// init project
	$this->syncRightsWithSkeleton($project);
	$this->syncGroupsWithSkeleton($project);
	$this->syncLinksBetweenGroupsAndRightsWithSkeleton($project);
	$this->syncUsersWithSkeleton($project);
	$this->syncLinksBetweenUsersAndProjectsWithSkeleton($project);
	$this->syncLinksBetweenUsersAndGroupsWithSkeleton($project);
	$this->syncPageTypesWithSkeleton($project);
	$this->syncTemplateTypesWithSkeleton($project);
	$this->syncTextMacrosWithSkeleton($project);
	$this->syncTextConvertersWithSkeleton($project);
	$this->syncPodcastCategoriesWithSkeleton($project);

	return true;
}

/**
 * Loads skeleton from xml file and stores result in self::_skeleton. If
 * the skeleton was already loaded once, the function will return the cached
 * result.
 *
 * @throws Application_ProjectException
 * @return array
 */
protected function loadSkeleton ()
{
	// create simplexml object from project skeleton if there's none
	if (!($this->_skeleton instanceof SimpleXMLElement)) {
		$path = dirname(__FILE__).DIRECTORY_SEPARATOR.'project.skeleton.xml';
		$this->_skeleton = simplexml_load_file($path);
	}
	
	// let's see if the object creation was successful
	if (!($this->_skeleton instanceof SimpleXMLElement)) {
		throw new Application_ProjectException("Import of project skeleton definition failed");
	}
	
	return $this->_skeleton;
}

/**
 * Returns array of rights configured in the skeleton.
 * 
 * @throws Application_ProjectException
 * @return array
 */
protected function getRightsFromSkeleton ()
{
	// get skeleton
	$skeleton = $this->loadSkeleton();
	
	// collect rights
	$rights = array();
	$result = $skeleton->xpath("/skeleton/rights/right");
	while (list(, $right) = each($result)) {
		// extract list of groups
		$groups = array();
		foreach ($right->groups->name as $group) {
			$groups[] = utf8_decode($group);
		}
		
		// append right to list of rights
		$rights[] = array(
			'name' => utf8_decode($right->name),
			'description' => utf8_decode($right->description),
			'editable' => utf8_decode($right->editable),
			'groups' => $groups
		);
	}
	
	return $rights;
}

/**
 * Returns array of groups configured in the skeleton.
 * 
 * @throws Application_ProjectException
 * @return array
 */
protected function getGroupsFromSkeleton ()
{
	// get skeleton
	$skeleton = $this->loadSkeleton();
	
	// collect groups
	$groups = array();
	$result = $skeleton->xpath("/skeleton/groups/group");
	while (list(, $group) = each($result)) {
		// append group to list of groups
		$groups[] = array(
			'name' => utf8_decode($group->name),
			'description' => utf8_decode($group->description),
			'editable' => utf8_decode($group->editable),
			'creator_group' => utf8_decode($group->creator_group)
		);
	}
	
	return $groups;
}

/**
 * Returns array of users configured in the skeleton.
 * 
 * @throws Application_ProjectException
 * @return array
 */
protected function getUsersFromSkeleton ()
{
	// get skeleton
	$skeleton = $this->loadSkeleton();
	
	// collect users
	$users = array();
	$result = $skeleton->xpath("/skeleton/users/user");
	while (list(, $user) = each($result)) {
		// extract list of groups
		$groups = array();
		foreach ($user->groups->name as $group) {
			$groups[] = utf8_decode($group);
		}
		
		// append user to list of users
		$users[] = array(
			'email' => utf8_decode($user->email),
			'secret' => utf8_decode($user->secret),
			'editable' => utf8_decode($user->editable),
			'active' => utf8_decode($user->active),
			'author' => utf8_decode($user->author),
			'groups' => $groups
		);
	}
	
	return $users;
}

/**
 * Returns array of page types configured in the skeleton.
 * 
 * @throws Application_ProjectException
 * @return array
 */
protected function getPageTypesFromSkeleton ()
{
	// get skeleton
	$skeleton = $this->loadSkeleton();
	
	// collect page types
	$page_types = array();
	$result = $skeleton->xpath("/skeleton/page-types/page-type");
	while (list(, $page_type) = each($result)) {
		// append page type to list of page types
		$page_types[] = array(
			'name' => utf8_decode($page_type->name),
			'internal_name' => utf8_decode($page_type->internal_name),
			'editable' => utf8_decode($page_type->editable)
		);
	}
	
	return $page_types;
}

/**
 * Returns array of template types configured in the skeleton.
 * 
 * @throws Application_ProjectException
 * @return array
 */
protected function getTemplateTypesFromSkeleton ()
{
	// get skeleton
	$skeleton = $this->loadSkeleton();
	
	// collect template types
	$template_types = array();
	$result = $skeleton->xpath("/skeleton/template-types/template-type");
	while (list(, $template_type) = each($result)) {
		// append template type to list of template types
		$template_types[] = array(
			'name' => utf8_decode($template_type->name),
			'description' => utf8_decode($template_type->description),
			'editable' => utf8_decode($template_type->editable)
		);
	}
	
	return $template_types;
}

/**
 * Returns array of text macros configured in the skeleton.
 * 
 * @throws Application_ProjectException
 * @return array
 */
protected function getTextMacrosFromSkeleton ()
{
	// get skeleton
	$skeleton = $this->loadSkeleton();
	
	// collect text macros
	$text_macros = array();
	$result = $skeleton->xpath("/skeleton/text-macros/text-macro");
	while (list(, $text_macro) = each($result)) {
		// append text macro to list of text macros
		$text_macros[] = array(
			'name' => utf8_decode($text_macro->name),
			'internal_name' => utf8_decode($text_macro->internal_name),
			'type' => utf8_decode($text_macro->type)
		);
	}
	
	return $text_macros;
}

/**
 * Returns array of text converters configured in the skeleton.
 * 
 * @throws Application_ProjectException
 * @return array
 */
protected function getTextConvertersFromSkeleton ()
{
	// get skeleton
	$skeleton = $this->loadSkeleton();
	
	// collect text converters
	$text_converters = array();
	$result = $skeleton->xpath("/skeleton/text-converters/text-converter");
	while (list(, $text_converter) = each($result)) {
		// append text converter to list of text converters
		$text_converters[] = array(
			'name' => utf8_decode($text_converter->name),
			'internal_name' => utf8_decode($text_converter->internal_name)
		);
	}
	
	return $text_converters;
}

/**
 * Returns array of podcast categories configured in the skeleton.
 * 
 * @throws Application_ProjectException
 * @return array
 */
protected function getPodcastCategoriesFromSkeleton ()
{
	// get skeleton
	$skeleton = $this->loadSkeleton();
	
	// collect podcast_categories
	$podcast_categories = array();
	$result = $skeleton->xpath("/skeleton/podcast_categories/podcast_category");
	while (list(, $podcast_category) = each($result)) {
		// extract list of subcategories
		$subcategories = array();
		foreach ($podcast_category->subcategories->name as $subcategory) {
			$subcategories[] = utf8_decode($subcategory);
		}
		
		// append podcast category to list of podcast categories
		$podcast_categories[] = array(
			'name' => utf8_decode($podcast_category->name),
			'subcategories' => $subcategories
		);
	}
	
	return $podcast_categories;
}

/**
 * Synchronises rights in database with the list of rights deposited
 * in the skeleton. Rights in the database that are not in the
 * skeleton anymore will be removed, differences in descriptions etc.
 * will be synchronised and new rights in the skeleton will be added.
 * Links between rights and groups won't be updated.
 * 
 * Takes the project id as first argument. Returns bool.
 *
 * @throws Application_ProjectException
 * @param int Project id
 * @return bool 
 */
protected function syncRightsWithSkeleton ($project)
{
	// input check
	if (empty($project) || !is_numeric($project)) {
		throw new Application_ProjectException("Input for parameter project is not numeric");
	}
	
	// get rights from skeleton
	$skeleton_rights = $this->getRightsFromSkeleton();
	
	// prepare query to get rights from database
	$sql = "
		SELECT
			`id`,
			`project`,
			`name`,
			`description`,
			`editable`
		FROM
			".OAK_DB_USER_RIGHTS."
		WHERE
			`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => (int)$project
	);
	
	// get rights from database
	$database_rights = $this->base->db->select($sql, 'multi', $bind_params);
	
	// on the next few lines we're going to drop obsolete rights from database
	foreach ($database_rights as $_right) {
		// compare list of rights in skeleton and database. database rights that
		// are not found in the list of skeleton rights have to be dropped.
		$drop = true;
		foreach ($skeleton_rights as $_skel_right) {
			if ($_right['name'] == $_skel_right['name']) {
				$drop = false;
			}
		}
		
		// if the drop bit is true, we have to remove the database right
		if ($drop === true) {
			// prepare where clause
			$where = " WHERE `id` = :id ";
			
			// prepare bind params
			$bind_params = array(
				'id' => $_right['id']
			);
			
			// drop right
			$this->base->db->delete(OAK_DB_USER_RIGHTS, $where, $bind_params);
		}
	}
	
	// now we have to add missing rights to the database and to sync differences
	// between skeleton and database
	foreach ($skeleton_rights as $_right) {
		// compare list of rights in skeleton and database. skeleton rights that
		// are not found in the list of database rights have to be added. rights that
		// exist have to be checked for differences and updated.
		$add = true;
		foreach ($database_rights as $_db_right) {
			if ($_right['name'] == $_db_right['name']) {
				// look at description/editable bit and update them if
				// necessessary
				$update = false;
				
				if ($_right['description'] != $_db_right['description']) {
					$update = true;
				} elseif ((int)$_right['editable'] != (int)$_db_right['editable']) {
					$update = true;
				}
				
				// update rights if necessary
				if ($update === true) {
					// prepare sql data
					$sqlData = array(
						'description' => $_right['description'],
						'editable' => (string)intval($_right['editable'])
					);
					
					// prepare where clause
					$where = " WHERE `id` = :id AND `project` = :project ";
					
					// prepare bind params
					$bind_params = array(
						'project' => $project,
						'id' => (int)$_db_right['id']
					);
					
					// update right
					$this->base->db->update(OAK_DB_USER_RIGHTS, $sqlData, $where, $bind_params);
				}
				
				// set add bit to false
				$add = false;
			}
		}
		
		// if the add bit is still true, we have to insert the right into the database
		if ($add === true) {
			// prepare insert data
			$sqlData = array(
				'project' => (int)$project,
				'name' => $_right['name'],
				'description' => $_right['description'],
				'editable' => (string)intval($_right['editable'])
			);
			
			// insert right
			$this->base->db->insert(OAK_DB_USER_RIGHTS, $sqlData);
		}
	}
	
	return true;
}

/**
 * Synchronises links between groups and rights using the skeleton.
 * Only links to rights that are configured in the skeleton will
 * be touched. So it's recommended that you first run the functions
 * to sync rights and groups with the skeleton.
 *
 * Takes the project id as fisrt argument. Returns bool.
 *
 * @throws Application_ProjectException
 * @param int Project id
 * @return bool
 */
protected function syncLinksBetweenGroupsAndRightsWithSkeleton ($project)
{
	// input check
	if (empty($project) || !is_numeric($project)) {
		throw new Application_ProjectException("Input for parameter project is not numeric");
	}
	
	// prepare query to get groups from database
	$sql = "
		SELECT
			`id`,
			`project`,
			`name`,
			`description`,
			`editable`,
			`date_modified`,
			`date_added`
		FROM
			".OAK_DB_USER_GROUPS."
		WHERE
			`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => (int)$project
	);
	
	// get groups from database
	$database_groups = $this->base->db->select($sql, 'multi', $bind_params);
	
	// prepare query to get rights from database
	$sql = "
		SELECT
			`id`,
			`project`,
			`name`,
			`description`,
			`editable`
		FROM
			".OAK_DB_USER_RIGHTS."
		WHERE
			`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => (int)$project
	);
	
	// get rights from database
	$database_rights = $this->base->db->select($sql, 'multi', $bind_params);
	
	// get rights from skeleton
	$skeleton_rights = $this->getRightsFromSkeleton();
	
	// loop through all skeleton rights to create new links between groups
	// and rights 
	foreach ($skeleton_rights as $_right) {
		// search for right in the list of database rights to get its id
		foreach ($database_rights as $_db_right) {
			if ($_db_right['name']== $_right['name']) {
				$current_right = (int)$_db_right['id'];
			}
		}
		
		// if the current right could not be found, we have to skip the
		// current right
		if (empty($current_right)) {
			continue;
		}
		
		// delete all links between the current right and it's associated
		// groups
		$sql = "
			DELETE FROM
				".OAK_DB_USER_GROUPS2USER_RIGHTS."
			WHERE
				`right` = :right
		";
		
		// prepare bind params
		$bind_params = array(
			'right' => (int)$current_right
		);
		
		// drop rows
		$this->base->db->execute($sql, $bind_params);
		
		// create new links between rights and groups
		foreach ($_right['groups'] as $_group) {
			// search for group in the list of database groups to get its id
			foreach ($database_groups as $_db_group) {
				if ($_db_group['name'] == $_group) {
					// prepare sql data to create new link
					$sqlData = array(
						'group' => (int)$_db_group['id'],
						'right' => (int)$current_right
					);
					
					// create new link
					$this->base->db->insert(OAK_DB_USER_GROUPS2USER_RIGHTS,
						$sqlData);
				}
			}
		}
	}
	
	return true;
}

/**
 * Synchronises groups in database with the list of groups deposited
 * in the skeleton. Groups in the database that are not in the
 * skeleton anymore will be removed, differences in descriptions etc.
 * will be synchronised and new groups in the skeleton will be added.
 * 
 * Takes the project id as first argument and a boolean value whether
 * obsolete groups (= not mentioned in the skeleton anymore) should be
 * dropped or not. Returns bool.
 * 
 * Note: The paramater $drop_obsolete should be used with care. That's
 * because the sync function cannot distinguish whether a group was
 * created from skeleton or by the user through the admin interface. So
 * if drop_obsolete evaluates to true, groups created by the user would
 * be deleted too.
 * 
 * @throws Application_ProjectException
 * @param int Project id
 * @param bool Drop obsolete groups
 * @return bool 
 */
protected function syncGroupsWithSkeleton ($project, $drop_obsolete = false)
{
	// input check
	if (empty($project) || !is_numeric($project)) {
		throw new Application_ProjectException("Input for parameter project is not numeric");
	}
	if (!is_bool($drop_obsolete)) {
		throw new Application_ProjectException("Input for parameter drop_obsolete is exptected to be bool");
	}
	
	// get groups from skeleton
	$skeleton_groups = $this->getGroupsFromSkeleton();
	
	// prepare query to get groups from database
	$sql = "
		SELECT
			`id`,
			`project`,
			`name`,
			`description`,
			`editable`
		FROM
			".OAK_DB_USER_GROUPS."
		WHERE
			`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => (int)$project
	);
	
	// get groups from database
	$database_groups = $this->base->db->select($sql, 'multi', $bind_params);
	
	// on the next few lines we're going to drop obsolete groups from database if
	// we're supposed to do so
	if ($drop_obsolete === true) {
		foreach ($database_groups as $_group) {
			// compare list of groups in skeleton and database. database groups that
			// are not found in the list of skeleton groups have to be dropped.
			$drop = true;
			foreach ($skeleton_groups as $_skel_group) {
				if ($_group['name'] == $_skel_group['name']) {
					$drop = false;
				}
			}
		
			// if the drop bit is true, we have to remove the database group
			if ($drop === true) {
				// prepare where clause
				$where = " WHERE `id` = :id ";
			
				// prepare bind params
				$bind_params = array(
					'id' => $_group['id']
				);
			
				// drop group
				$this->base->db->delete(OAK_DB_USER_GROUPS, $where, $bind_params);
			}
		}
	}
	
	// now we have to add missing groups to the database and to sync differences
	// between skeleton and database
	foreach ($skeleton_groups as $_group) {
		// compare list of groups in skeleton and database. skeleton groups that
		// are not found in the list of database groups have to be added. groups that
		// exist have to be checked for differences and updated.
		$add = true;
		foreach ($database_groups as $_db_group) {
			if ($_group['name'] == $_db_group['name']) {
				// look at description/editable bit and update them if
				// necessessary
				$update = false;
				
				if ($_group['description'] != $_db_group['description']) {
					$update = true;
				} elseif ((int)$_group['editable'] != (int)$_db_group['editable']) {
					$update = true;
				}
				
				// update groups if necessary
				if ($update === true) {
					// prepare sql data
					$sqlData = array(
						'description' => $_group['description'],
						'editable' => (string)intval($_group['editable'])
					);
					
					// prepare where clause
					$where = " WHERE `id` = :id AND `project` = :project ";
					
					// prepare bind params
					$bind_params = array(
						'project' => $project,
						'id' => (int)$_db_group['id']
					);
					
					// update group
					$this->base->db->update(OAK_DB_USER_GROUPS, $sqlData, $where, $bind_params);
				}
				
				// set add bit to false
				$add = false;
			}
		}
		
		// if the add bit is still true, we have to insert the group into the database
		if ($add === true) {
			// prepare insert data
			$sqlData = array(
				'project' => (int)$project,
				'name' => $_group['name'],
				'description' => $_group['description'],
				'editable' => (string)intval($_group['editable']),
				'date_added' => date('Y-m-d H:i:s')
			);
			
			// insert group
			$this->base->db->insert(OAK_DB_USER_GROUPS, $sqlData);
		}
	}
	
	return true;
}

/**
 * Sychronises users with skeleton. Adds new users and updates existing
 * ones. If drop_obsolete is true, obsolete/orphaned users will be deleted.
 * A user is obsolete or orphaned, if it's not attached to any project.
 * 
 * Takes the project id as first argument and a boolean value whether
 * obsolete/orphaned users should be deleted as second argument. Returns
 * bool.
 * 
 * @throws Application_ProjectException
 * @param int Project id
 * @param bool Drop obsolete
 * @return bool
 */
protected function syncUsersWithSkeleton ($project, $drop_obsolete = false)
{
	// input check
	if (empty($project) || !is_numeric($project)) {
		throw new Application_ProjectException("Input for parameter project is not numeric");
	}
	
	// load helper class
	$HELPER = load('Utility:Helper');
	
	// get users from skeleton
	$skeleton_users = $this->getUsersFromSkeleton();
	
	// collect email addresses of skeleton users
	$email_addresses = array();
	foreach ($skeleton_users as $_user) {
		$email_addresses[] = $_user['email'];
	}
	
	// prepare query to get users from database
	$sql = "
		SELECT
			`id`,
			`email`,
			`secret`,
			`editable`
		FROM
			".OAK_DB_USER_USERS."
		WHERE
			1
	";
	
	// add IN clause to reduce result set size
	$sql .= " AND ".$HELPER->_sqlInFromArray('`email`', $email_addresses);
	
	// get users from database
	$database_users = $this->base->db->select($sql, 'multi', array());
	
	// now we have to add missing users to the database and to sync differences
	// between skeleton and database
	foreach ($skeleton_users as $_user) {
		// compare list of users in skeleton and database. skeleton users that
		// are not found in the list of database users have to be added. users that
		// exist have to be checked for differences and updated.
		$add = true;
		foreach ($database_users as $_db_user) {
			if ($_user['email'] == $_db_user['email']) {
				// look at secret/editable bit and update them if
				// necessessary
				$update = false;
				
				if ($_user['secret'] != $_db_user['secret']) {
					$update = true;
				} elseif ((int)$_user['editable'] != (int)$_db_user['editable']) {
					$update = true;
				}
				
				// update users if necessary
				if ($update === true) {
					// prepare sql data
					$sqlData = array(
						'secret' => $_user['secret'],
						'editable' => (string)intval($_user['editable'])
					);
					
					// prepare where clause
					$where = " WHERE `id` = :id ";
					
					// prepare bind params
					$bind_params = array(
						'id' => (int)$_db_user['id']
					);
					
					// update user
					$this->base->db->update(OAK_DB_USER_USERS, $sqlData, $where, $bind_params);
				}
				
				// set add bit to false
				$add = false;
			}
		}
		
		// if the add bit is still true, we have to insert the user into the database
		if ($add === true) {
			// prepare insert data
			$sqlData = array(
				'email' => $_user['email'],
				'secret' => $_user['secret'],
				'editable' => (string)intval($_user['editable']),
				'date_added' => date('Y-m-d H:i:s')
			);
			
			// insert user
			$this->base->db->insert(OAK_DB_USER_USERS, $sqlData);
		}
	}
	
	// drop obsolete/orphaned users if we're supposed to do so.
	if ($drop_obsolete === true) {
		// prepare query to get users from database
		$sql = "
			DELETE FROM
				".OAK_DB_USER_USERS." AS `user_users`
			USING
				`user_users`
			LEFT JOIN
				".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
			  ON
				`user_users`.`id` = `user_users2application_projects`.`user`
			WHERE
				`user_users2application_projects`.`id` IS NULL
		";

		// add IN clause to reduce result set size
		$sql .= " AND ".$HELPER->_sqlNotInFromArray('`email`', $email_addresses);
		
		// drop obsolete users
		$this->base->db->execute($sql);
	}
	
	return true;
}

/**
 * Synchronises links between users and projects with skeleton.
 * Only users that are configured in the skeleton will be touched.
 * 
 * Takes the project id as first argument. Returns bool.
 * 
 * @throws Application_ProjectException
 * @param in Project Id
 * @return bool
 */
public function syncLinksBetweenUsersAndProjectsWithSkeleton ($project)
{
	// input check
	if (empty($project) || !is_numeric($project)) {
		throw new Application_ProjectException("Input for parameter project is not numeric");
	}
	
	// load helper class
	$HELPER = load('Utility:Helper');
	
	// get users from skeleton
	$skeleton_users = $this->getUsersFromSkeleton();
	
	// collect email addresses of skeleton users
	$email_addresses = array();
	foreach ($skeleton_users as $_user) {
		$email_addresses[] = $_user['email'];
	}
	
	// prepare query to get users from database
	$sql = "
		SELECT
			`user_users`.`id`,
			`user_users`.`email`,
			`user_users`.`secret`,
			`user_users`.`editable`
		FROM
			".OAK_DB_USER_USERS." AS `user_users`
		LEFT JOIN
			".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
		  ON
			`user_users`.`id` = `user_users2application_projects`.`user`
		WHERE
			1
	";
	
	// add IN clause to reduce result set size
	$sql .= " AND ".$HELPER->_sqlInFromArray('`email`', $email_addresses);
	
	// get users from database
	$database_users = $this->base->db->select($sql, 'multi', array());
	
	foreach ($skeleton_users as $_user) {
		foreach ($database_users as $_db_user) {
			if ($_user['email'] == $_db_user['email']) {
				$current_user = $_db_user['id'];
				
				// if the user from the database is the CURRENT_USER,
				// we have to set a flag that prevents double creation
				// of links between CURRENT_USER and the project
				// to be created
				if ($current_user == OAK_CURRENT_USER) {
					define("OAK_CURRENT_USER_LINK_CREATED", true);
				}
			}
		}
		
		// if the current user could not be found, we have to skip the
		// current user
		if (empty($current_user)) {
			continue;
		}
		
		// delete all links between the current user and it's associated
		// groups
		$sql = "
			DELETE FROM
				".OAK_DB_USER_USERS2APPLICATION_PROJECTS."
			WHERE
				`user` = :user
			  AND
				`project` = :project
		";
		
		// prepare bind params
		$bind_params = array(
			'user' => (int)$current_user,
			'project' => (int)$project
		);
		
		// drop rows
		$this->base->db->execute($sql, $bind_params);
		
		// create new link
		$sqlData = array(
			'user' => $current_user,
			'project' => $project,
			'active' => (string)intval($_user['active']),
			'author' => (string)intval($_user['author'])
		);
		
		$this->base->db->insert(OAK_DB_USER_USERS2APPLICATION_PROJECTS, $sqlData);
	}
	
	// create link to current user if it wasn't done yet
	if (!defined("OAK_CURRENT_USER_LINK_CREATED")) {
		$sqlData = array(
			'user' => OAK_CURRENT_USER,
			'project' => $project,
			'active' => "1",
			'author' => "1"
		);
		$this->base->db->insert(OAK_DB_USER_USERS2APPLICATION_PROJECTS, $sqlData);
	}
	
	return true;
}

/**
 * Synchronises links between users and groups with skeleton.
 * Only users configured in the skeleton will be touched.
 * 
 * Takes the project id as first argument. Returns bool.
 * 
 * @throws Application_ProjectException
 * @param int Project id
 * @return bool
 */
protected function syncLinksBetweenUsersAndGroupsWithSkeleton ($project)
{
	// input check
	if (empty($project) || !is_numeric($project)) {
		throw new Application_ProjectException("Input for parameter project is not numeric");
	}
	
	// prepare query to get groups from database
	$sql = "
		SELECT
			`id`,
			`project`,
			`name`,
			`description`,
			`editable`
		FROM
			".OAK_DB_USER_GROUPS."
		WHERE
			`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => (int)$project
	);
	
	// get groups from database
	$database_groups = $this->base->db->select($sql, 'multi', $bind_params);
	
	// prepare query to get users from database
	$sql = "
		SELECT
			`user_users`.`id`,
			`user_users`.`email`,
			`user_users`.`secret`,
			`user_users`.`editable`
		FROM
			".OAK_DB_USER_USERS." AS `user_users`
		JOIN
			".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
		  ON
			`user_users`.`id` = `user_users2application_projects`.`user`
		WHERE
			`user_users2application_projects`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => (int)$project
	);
	
	// get users from database
	$database_users = $this->base->db->select($sql, 'multi', $bind_params);
	
	// get skeleton groups
	$skeleton_groups = $this->getGroupsFromSkeleton();
	
	// get users from skeleton
	$skeleton_users = $this->getUsersFromSkeleton();
	
	// loop through all skeleton users to create new links between groups
	// and users 
	foreach ($skeleton_users as $_user) {
		// search for user in the list of database users to get its id
		foreach ($database_users as $_db_user) {
			if ($_db_user['email']== $_user['email']) {
				$current_user = (int)$_db_user['id'];
				
				// if the user from the database is the CURRENT_USER,
				// we have to set a flag that prevents double creation
				// of links between CURRENT_USER and the groups
				if ($current_user == OAK_CURRENT_USER) {
					define("OAK_CURRENT_USER_LINK_CREATED", true);
				}
			}
		}
		
		// if the current user could not be found, we have to skip the
		// current user
		if (empty($current_user)) {
			continue;
		}
		
		// delete all links between the current user and it's associated
		// groups
		$sql = "
			DELETE FROM
				".OAK_DB_USER_USERS2USER_GROUPS." AS `user_users2user_groups`
			USING
				`user_users2user_groups`
			JOIN
				".OAK_DB_USER_GROUPS." AS `user_groups`
			  ON
				`user_users2user_groups`.`group` = `user_groups`.`id`
			WHERE
				`user_users2user_groups`.`user` = :user
			  AND
				`user_groups`.`project` = :project
		";
		
		// prepare bind params
		$bind_params = array(
			'user' => (int)$current_user,
			'project' => (int)$project
		);
		
		// drop rows
		$this->base->db->execute($sql, $bind_params);
		
		// create new links between users and groups
		foreach ($_user['groups'] as $_group) {
			// search for group in the list of database groups to get its id
			foreach ($database_groups as $_db_group) {
				if ($_db_group['name'] == $_group) {
					// prepare sql data to create new link
					$sqlData = array(
						'group' => (int)$_db_group['id'],
						'user' => (int)$current_user
					);
					
					// create new link
					$this->base->db->insert(OAK_DB_USER_USERS2USER_GROUPS,
						$sqlData);
				}
			}
		}
	}
	
	// create link between current user and creator group if it wasn't
	// created yet
	if (!defined("OAK_CURRENT_USER_LINK_CREATED")) {
		foreach ($skeleton_groups as $_skel_group) {
			if ($_skel_group['creator_group']) {
				foreach ($database_groups as $_db_group) {
					if ($_db_group['name'] == $_skel_group['name']) {
						// prepare sql data to create new link
						$sqlData = array(
							'group' => (int)$_db_group['id'],
							'user' => (int)OAK_CURRENT_USER
						);
					
						// create new link
						$this->base->db->insert(OAK_DB_USER_USERS2USER_GROUPS,
							$sqlData);
					}
				}
			}
		}
	}
	
	return true;
}

/**
 * Synchronises page types in database with the list of page types
 * deposited in the skeleton. Page types in the database that are not
 * in the skeleton anymore will be removed, differences in descriptions
 * etc. will be synchronised and new page types in the skeleton will be
 * added.
 * 
 * Takes the project id as first argument. Returns bool.
 *
 * @throws Application_ProjectException
 * @param int Project id
 * @return bool 
 */
protected function syncPageTypesWithSkeleton ($project)
{
	// input check
	if (empty($project) || !is_numeric($project)) {
		throw new Application_ProjectException("Input for parameter project is not numeric");
	}
	
	// get page types from skeleton
	$skeleton_page_types = $this->getPageTypesFromSkeleton();
	
	// prepare query to get page types from database
	$sql = "
		SELECT
			`id`,
			`project`,
			`name`,
			`internal_name`,
			`editable`
		FROM
			".OAK_DB_CONTENT_PAGE_TYPES."
		WHERE
			`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => (int)$project
	);
	
	// get rights from database
	$database_page_types = $this->base->db->select($sql, 'multi', $bind_params);
	
	// on the next few lines we're going to drop obsolete page tpyes from database
	foreach ($database_page_types as $_page_type) {
		// compare list of page types in skeleton and database. database page types
		// that are not found in the list of skeleton page types have to be dropped.
		$drop = true;
		foreach ($skeleton_page_types as $_skel_page_type) {
			if ($_page_type['name'] == $_skel_page_type['name']) {
				$drop = false;
			}
		}
		
		// if the drop bit is true, we have to remove the database page type
		if ($drop === true) {
			// prepare where clause
			$where = " WHERE `id` = :id ";
			
			// prepare bind params
			$bind_params = array(
				'id' => $_right['id']
			);
			
			// drop right
			$this->base->db->delete(OAK_DB_CONTENT_PAGE_TYPES, $where, $bind_params);
		}
	}
	
	// now we have to add missing page types to the database and to sync differences
	// between skeleton and database
	foreach ($skeleton_page_types as $_page_type) {
		// compare list of page types in skeleton and database. skeleton page types that
		// are not found in the list of database page types have to be added. page types
		// that exist have to be checked for differences and updated.
		$add = true;
		foreach ($database_page_types as $_db_page_type) {
			if ($_page_type['name'] == $_db_page_type['name']) {
				// look at internal name/editable bit and update them if
				// necessessary
				$update = false;
				
				if ($_page_type['internal_name'] != $_db_page_type['internal_name']) {
					$update = true;
				} elseif ((int)$_page_type['editable'] != (int)$_db_page_type['editable']) {
					$update = true;
				}
				
				// update page_types if necessary
				if ($update === true) {
					// prepare sql data
					$sqlData = array(
						'internal_name' => $_page_type['internal_name'],
						'editable' => (string)intval($_page_type['editable'])
					);
					
					// prepare where clause
					$where = " WHERE `id` = :id AND `project` = :project ";
					
					// prepare bind params
					$bind_params = array(
						'project' => $project,
						'id' => (int)$_db_page_type['id']
					);
					
					// update page_type
					$this->base->db->update(OAK_DB_CONTENT_PAGE_TYPES, $sqlData, $where, $bind_params);
				}
				
				// set add bit to false
				$add = false;
			}
		}
		
		// if the add bit is still true, we have to insert the page_type into the database
		if ($add === true) {
			// prepare insert data
			$sqlData = array(
				'project' => (int)$project,
				'name' => $_page_type['name'],
				'internal_name' => $_page_type['internal_name'],
				'editable' => (string)intval($_page_type['editable'])
			);
			
			// insert page_type
			$this->base->db->insert(OAK_DB_CONTENT_PAGE_TYPES, $sqlData);
		}
	}
	
	return true;
}

/**
 * Synchronises template types in database with the list of template types
 * deposited in the skeleton. Template types in the database that are not
 * in the skeleton anymore will be removed, differences in descriptions
 * etc. will be synchronised and new template types in the skeleton will be
 * added.
 * 
 * Takes the project id as first argument. Returns bool.
 *
 * @throws Application_ProjectException
 * @param int Project id
 * @return bool 
 */
protected function syncTemplateTypesWithSkeleton ($project)
{
	// input check
	if (empty($project) || !is_numeric($project)) {
		throw new Application_ProjectException("Input for parameter project is not numeric");
	}
	
	// get template types from skeleton
	$skeleton_template_types = $this->getTemplateTypesFromSkeleton();
	
	// prepare query to get template types from database
	$sql = "
		SELECT
			`id`,
			`project`,
			`name`,
			`description`,
			`editable`
		FROM
			".OAK_DB_TEMPLATING_TEMPLATE_TYPES."
		WHERE
			`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => (int)$project
	);
	
	// get template_types from database
	$database_template_types = $this->base->db->select($sql, 'multi', $bind_params);
	
	// on the next few lines we're going to drop obsolete template tpyes from database
	foreach ($database_template_types as $_template_type) {
		// compare list of template types in skeleton and database. database template types
		// that are not found in the list of skeleton template types have to be dropped.
		$drop = true;
		foreach ($skeleton_template_types as $_skel_template_type) {
			if ($_template_type['name'] == $_skel_template_type['name']) {
				$drop = false;
			}
		}
		
		// if the drop bit is true, we have to remove the database template type
		if ($drop === true) {
			// prepare where clause
			$where = " WHERE `id` = :id ";
			
			// prepare bind params
			$bind_params = array(
				'id' => $_template_type['id']
			);
			
			// drop template_type
			$this->base->db->delete(OAK_DB_TEMPLATING_TEMPLATE_TYPES, $where, $bind_params);
		}
	}
	
	// now we have to add missing template types to the database and to sync differences
	// between skeleton and database
	foreach ($skeleton_template_types as $_template_type) {
		// compare list of template types in skeleton and database. skeleton template types that
		// are not found in the list of database template types have to be added. template types
		// that exist have to be checked for differences and updated.
		$add = true;
		foreach ($database_template_types as $_db_template_type) {
			if ($_template_type['name'] == $_db_template_type['name']) {
				// look at internal name/editable bit and update them if
				// necessessary
				$update = false;
				
				if ($_template_type['description'] != $_db_template_type['description']) {
					$update = true;
				} elseif ((int)$_template_type['editable'] != (int)$_db_template_type['editable']) {
					$update = true;
				}
				
				// update template_types if necessary
				if ($update === true) {
					// prepare sql data
					$sqlData = array(
						'description' => $_template_type['description'],
						'editable' => (string)intval($_template_type['editable'])
					);
					
					// prepare where clause
					$where = " WHERE `id` = :id AND `project` = :project ";
					
					// prepare bind params
					$bind_params = array(
						'project' => $project,
						'id' => (int)$_db_template_type['id']
					);
					
					// update template_type
					$this->base->db->update(OAK_DB_TEMPLATING_TEMPLATE_TYPES, $sqlData, $where, $bind_params);
				}
				
				// set add bit to false
				$add = false;
			}
		}
		
		// if the add bit is still true, we have to insert the template_type into the database
		if ($add === true) {
			// prepare insert data
			$sqlData = array(
				'project' => (int)$project,
				'name' => $_template_type['name'],
				'description' => $_template_type['description'],
				'editable' => (string)intval($_template_type['editable'])
			);
			
			// insert template_type
			$this->base->db->insert(OAK_DB_TEMPLATING_TEMPLATE_TYPES, $sqlData);
		}
	}
	
	return true;
}

/**
 * Synchronises text macros in database with the list of text macros
 * deposited in the skeleton. Text macros in the database that are not
 * in the skeleton anymore will be removed, differences in internal_names
 * etc. will be synchronised and new text macros in the skeleton will be
 * added.
 * 
 * Takes the project id as first argument. Returns bool.
 *
 * @throws Application_ProjectException
 * @param int Project id
 * @return bool 
 */
protected function syncTextMacrosWithSkeleton ($project)
{
	// input check
	if (empty($project) || !is_numeric($project)) {
		throw new Application_ProjectException("Input for parameter project is not numeric");
	}
	
	// get text macros from skeleton
	$skeleton_text_macros = $this->getTextMacrosFromSkeleton();
	
	// prepare query to get text macros from database
	$sql = "
		SELECT
			`id`,
			`project`,
			`name`,
			`internal_name`,
			`type`
		FROM
			".OAK_DB_APPLICATION_TEXT_MACROS."
		WHERE
			`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => (int)$project
	);
	
	// get text_macros from database
	$database_text_macros = $this->base->db->select($sql, 'multi', $bind_params);
	
	// on the next few lines we're going to drop obsolete text tpyes from database
	foreach ($database_text_macros as $_text_macro) {
		// compare list of text macros in skeleton and database. database text macros
		// that are not found in the list of skeleton text macros have to be dropped.
		$drop = true;
		foreach ($skeleton_text_macros as $_skel_text_macro) {
			if ($_text_macro['internal_name'] == $_skel_text_macro['internal_name']) {
				$drop = false;
			}
		}
		
		// if the drop bit is true, we have to remove the database text macro
		if ($drop === true) {
			// prepare where clause
			$where = " WHERE `id` = :id ";
			
			// prepare bind params
			$bind_params = array(
				'id' => $_text_macro['id']
			);
			
			// drop text_macro
			$this->base->db->delete(OAK_DB_APPLICATION_TEXT_MACROS, $where, $bind_params);
		}
	}
	
	// now we have to add missing text macros to the database and to sync differences
	// between skeleton and database
	foreach ($skeleton_text_macros as $_text_macro) {
		// compare list of text macros in skeleton and database. skeleton text macros that
		// are not found in the list of database text macros have to be added. text macros
		// that exist have to be checked for differences and updated.
		$add = true;
		foreach ($database_text_macros as $_db_text_macro) {
			if ($_text_macro['internal_name'] == $_db_text_macro['internal_name']) {
				// look at name update it if necessessary
				$update = false;
				
				if ($_text_macro['name'] != $_db_text_macro['name']) {
					$update = true;
				}
				
				// update text macros if necessary
				if ($update === true) {
					// prepare sql data
					$sqlData = array(
						'name' => $_text_macro['name'],
						'type' => (int)$_text_macro['type']
					);
					
					// prepare where clause
					$where = " WHERE `id` = :id AND `project` = :project ";
					
					// prepare bind params
					$bind_params = array(
						'project' => $project,
						'id' => (int)$_db_text_macro['id']
					);
					
					// update text_macro
					$this->base->db->update(OAK_DB_APPLICATION_TEXT_MACROS, $sqlData, $where, $bind_params);
				}
				
				// set add bit to false
				$add = false;
			}
		}
		
		// if the add bit is still true, we have to insert the text_macro into the database
		if ($add === true) {
			// prepare insert data
			$sqlData = array(
				'project' => (int)$project,
				'name' => $_text_macro['name'],
				'internal_name' => $_text_macro['internal_name'],
				'type' => $_text_macro['type']
			);
			
			// insert text_macro
			$this->base->db->insert(OAK_DB_APPLICATION_TEXT_MACROS, $sqlData);
		}
	}
	
	return true;
}

/**
 * Synchronises text converters in database with the list of text converters
 * deposited in the skeleton. Text converters in the database that are not
 * in the skeleton anymore will be removed, differences in internal_names
 * etc. will be synchronised and new text converters in the skeleton will be
 * added.
 * 
 * Takes the project id as first argument. Returns bool.
 *
 * @throws Application_ProjectException
 * @param int Project id
 * @return bool 
 */
protected function syncTextConvertersWithSkeleton ($project)
{
	// input check
	if (empty($project) || !is_numeric($project)) {
		throw new Application_ProjectException("Input for parameter project is not numeric");
	}
	
	// get text converters from skeleton
	$skeleton_text_converters = $this->getTextConvertersFromSkeleton();
	
	// prepare query to get text converters from database
	$sql = "
		SELECT
			`id`,
			`project`,
			`name`,
			`internal_name`
		FROM
			".OAK_DB_APPLICATION_TEXT_CONVERTERS."
		WHERE
			`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => (int)$project
	);
	
	// get text_converters from database
	$database_text_converters = $this->base->db->select($sql, 'multi', $bind_params);
	
	// on the next few lines we're going to drop obsolete text tpyes from database
	foreach ($database_text_converters as $_text_converter) {
		// compare list of text converters in skeleton and database. database text converters
		// that are not found in the list of skeleton text converters have to be dropped.
		$drop = true;
		foreach ($skeleton_text_converters as $_skel_text_converter) {
			if ($_text_converter['internal_name'] == $_skel_text_converter['internal_name']) {
				$drop = false;
			}
		}
		
		// if the drop bit is true, we have to remove the database text converter
		if ($drop === true) {
			// prepare where clause
			$where = " WHERE `id` = :id ";
			
			// prepare bind params
			$bind_params = array(
				'id' => $_text_converter['id']
			);
			
			// drop text_converter
			$this->base->db->delete(OAK_DB_APPLICATION_TEXT_CONVERTERS, $where, $bind_params);
		}
	}
	
	// now we have to add missing text converters to the database and to sync differences
	// between skeleton and database
	foreach ($skeleton_text_converters as $_text_converter) {
		// compare list of text converters in skeleton and database. skeleton text converters that
		// are not found in the list of database text converters have to be added. text converters
		// that exist have to be checked for differences and updated.
		$add = true;
		foreach ($database_text_converters as $_db_text_converter) {
			if ($_text_converter['internal_name'] == $_db_text_converter['internal_name']) {
				// look at name and update it if necessessary
				$update = false;
				
				if ($_text_converter['name'] != $_db_text_converter['name']) {
					$update = true;
				}
				
				// update text converters if necessary
				if ($update === true) {
					// prepare sql data
					$sqlData = array(
						'name' => $_text_converter['name']
					);
					
					// prepare where clause
					$where = " WHERE `id` = :id AND `project` = :project ";
					
					// prepare bind params
					$bind_params = array(
						'project' => $project,
						'id' => (int)$_db_text_converter['id']
					);
					
					// update text_converter
					$this->base->db->update(OAK_DB_APPLICATION_TEXT_CONVERTERS, $sqlData, $where, $bind_params);
				}
				
				// set add bit to false
				$add = false;
			}
		}
		
		// if the add bit is still true, we have to insert the text_converter into the database
		if ($add === true) {
			// prepare insert data
			$sqlData = array(
				'project' => (int)$project,
				'name' => $_text_converter['name'],
				'internal_name' => $_text_converter['internal_name']
			);
			
			// insert text_converter
			$this->base->db->insert(OAK_DB_APPLICATION_TEXT_CONVERTERS, $sqlData);
		}
	}
	
	return true;
}

/**
 * Synchronises podcast categories in database with the list of podcast categories
 * deposited in the skeleton. Podcast categories in the database that are not
 * in the skeleton anymore will be removed, differences will be synchronised and
 * new podcast categories in the skeleton will be added.
 * 
 * Takes the project id as first argument. Returns bool.
 *
 * @throws Application_ProjectException
 * @param int Project id
 * @return bool 
 */
protected function syncPodcastCategoriesWithSkeleton ($project)
{
	// input check
	if (empty($project) || !is_numeric($project)) {
		throw new Application_ProjectException("Input for parameter project is not numeric");
	}
	
	// get podcast categories from skeleton
	$skeleton_podcast_categories = $this->getPodcastCategoriesFromSkeleton();
	
	// prepare query to get podcast categories from database
	$sql = "
		SELECT
			`id`,
			`project`,
			`name`
		FROM
			".OAK_DB_CONTENT_BLOG_PODCAST_CATEGORIES."
		WHERE
			`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => (int)$project
	);
	
	// get podcast_categories from database
	$database_podcast_categories = $this->base->db->select($sql, 'multi', $bind_params);
	
	// sync podcast categories from skeleton with database
	foreach ($skeleton_podcast_categories as $_category) {
		$add = true;
		foreach ($database_podcast_categories as $_db_category) {
			if ($_db_category['name'] == $_category['name']) {
				$add = false;
				break;
			}
		}
		
		if ($add) {
			$sqlData = array(
				'project' => (int)$project,
				'name' => $_category['name'],
				'category' => $_category['name'],
				'date_added' => date('Y-m-d H:i:s')
			);
			
			$this->base->db->insert(OAK_DB_CONTENT_BLOG_PODCAST_CATEGORIES, $sqlData);
		}
		
		// sync podcast subcategories from skeleton with database
		foreach ($_category['subcategories'] as $_subcategory) {
			// compose category name from category and subcategory name
			$name = $_category['name'].' > '.$_subcategory;
			
			$add = true;
			foreach ($database_podcast_categories as $_db_category) {
				if ($_db_category['name'] == $name) {
					$add = false;
					break;
				}
			}
		
			if ($add) {
				$sqlData = array(
					'project' => (int)$project,
					'name' => $name,
					'category' => $_category['name'],
					'subcategory' => $_subcategory,
					'date_added' => date('Y-m-d H:i:s')
				);
			
				$this->base->db->insert(OAK_DB_CONTENT_BLOG_PODCAST_CATEGORIES, $sqlData);
			}
		}
	}
	
	// sync podcast categories from database with skeleton
	foreach ($database_podcast_categories as $_category) {
		$delete = true;
		foreach ($skeleton_podcast_categories as $_skel_category) {
			if ($_skel_category['name'] == $_category['name']) {
				$delete = false;
				break;
			}
		}
		
		if ($delete) {
			// prepare where clause
			$where = " WHERE `id` = :id ";
			
			// prepare bind params
			$bind_params = array(
				'id' => $_category['id']
			);
			
			// drop podcast category
			$this->base->db->delete(OAK_DB_BLOG_PODCAST_CATEGORIES, $where, $bind_params);
		}
		
		// sync podcast categories from database with skeleton
		$delete = true;
		foreach ($skeleton_podcast_categories as $_skel_category) {
			foreach ($_skel_category['subcategories'] as $_skel_subcategory) {
				// compose category name from category and subcategory name
				$name = $_skel_category['name'].' > '.$_skel_subcategory;
				
				if ($name == $_category['name']) {
					$delete = false;
					break;
				}
			}
		}
		
		if ($delete) {
			// prepare where clause
			$where = " WHERE `id` = :id ";
			
			// prepare bind params
			$bind_params = array(
				'id' => $_category['id']
			);
			
			// drop podcast category
			$this->base->db->delete(OAK_DB_BLOG_PODCAST_CATEGORIES, $where, $bind_params);
		}
	}
	
	return true;
}

// end of class
}

class Application_ProjectException extends Exception { }

?>