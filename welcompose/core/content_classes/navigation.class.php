<?php

/**
 * Project: Welcompose
 * File: navigation.class.php
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
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

/**
 * Singleton for Content_Navigation.
 * 
 * @return object
 */
function Content_Navigation ()
{
	if (Content_Navigation::$instance == null) {
		Content_Navigation::$instance = new Content_Navigation(); 
	}
	return Content_Navigation::$instance;
}

class Content_Navigation {
	
	/**
	 * Singleton
	 * 
	 * @var object
	 */
	public static $instance = null;
	
	/**
	 * Reference to base class
	 * 
	 * @var object
	 */
	public $base = null;

/**
 * Start instance of base class, load configuration and
 * establish database connection. Please don't call the
 * constructor direcly, use the singleton pattern instead.
 */
public function __construct()
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
 * Adds navigation to the navigation table. Takes a field=>value array with
 * navigation data as first argument. Returns insert id. 
 * 
 * @throws Content_NavigationException
 * @param array Row data
 * @return int Navigation id
 */
public function addNavigation ($sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'Navigation', 'Manage')) {
		throw new Content_NavigationException("You are not allowed to perform this action");
	}
	
	if (!is_array($sqlData)) {
		throw new Content_NavigationException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the navigation will be assigned to the right project
	$sqlData['project'] = WCOM_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_CONTENT_NAVIGATIONS, $sqlData);
	
	// test if navigation belongs to current user/project
	if (!$this->navigationBelongsToCurrentUser($insert_id)) {
		throw new Content_NavigationException('Navigation does not belong to current user or project');
	}
	
	return $insert_id;
}

/**
 * Updates navigation. Takes the navigation id as first argument, a
 * field=>value array with the new navigation data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_NavigationException
 * @param int Navigation id
 * @param array Row data
 * @return int Affected rows
*/
public function updateNavigation ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'Navigation', 'Manage')) {
		throw new Content_NavigationException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_NavigationException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_NavigationException('Input for parameter sqlData is not an array');	
	}
	
	// test if navigation belongs to current user/project
	if (!$this->navigationBelongsToCurrentUser($id)) {
		throw new Content_NavigationException('Navigation does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_CONTENT_NAVIGATIONS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes navigation from the navigation table. Takes the navigation id
 * as first argument. Returns amount of affected rows
 * 
 * @throws Content_NavigationException
 * @param int Navigation id
 * @return int Amount of affected rows
 */
public function deleteNavigation ($id)
{
	// access check
	if (!wcom_check_access('Content', 'Navigation', 'Manage')) {
		throw new Content_NavigationException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_NavigationException('Input for parameter id is not numeric');
	}
	
	// test if navigation belongs to current user/project
	if (!$this->navigationBelongsToCurrentUser($id)) {
		throw new Content_NavigationException('Navigation does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_CONTENT_NAVIGATIONS, $where, $bind_params);
}

/**
 * Selects one navigation. Takes the navigation id as first argument.
 * Returns array with navigation information.
 * 
 * @throws Content_NavigationException
 * @param int Navigation id
 * @return array
 */
public function selectNavigation ($id)
{
	// access check
	if (!wcom_check_access('Content', 'Navigation', 'Use')) {
		throw new Content_NavigationException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_NavigationException('Input for parameter id is not numeric');
	}
	
	// prepare query
	$sql = "
		SELECT 
			`content_navigations`.`id` AS `id`,
			`content_navigations`.`project` AS `project`,
			`content_navigations`.`name` AS `name`
		FROM
			".WCOM_DB_CONTENT_NAVIGATIONS." AS `content_navigations`
		WHERE 
			`content_navigations`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// prepare where clauses
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `content_navigations`.`id` = :id ";
		$bind_params['id'] = (int)$id;
	}
	
	// add limits
	$sql .= ' LIMIT 1';
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more navigations. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Content_NavigationException
 * @param array Select params
 * @return array
 */
public function selectNavigations ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'Navigation', 'Use')) {
		throw new Content_NavigationException("You are not allowed to perform this action");
	}
	
	// define some vars
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_NavigationException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_NavigationException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`content_navigations`.`id` AS `id`,
			`content_navigations`.`project` AS `project`,
			`content_navigations`.`name` AS `name`
		FROM
			".WCOM_DB_CONTENT_NAVIGATIONS." AS `content_navigations`
		WHERE 
			`content_navigations`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add sorting
	$sql .= " ORDER BY `content_navigations`.`name` ";
	
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
 * Tests given navigation name for uniqueness. Takes the navigation
 * name as first argument and an optional navigation id as second
 * argument. If the navigation id is given, this navigation won't be
 * considered when checking for uniqueness (useful for updates).
 * Returns boolean true if navigation name is unique.
 *
 * @throws Content_NavigationException
 * @param string Navigation name
 * @param int Navigation id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!wcom_check_access('Content', 'Navigation', 'Use')) {
		throw new Content_NavigationException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Content_NavigationException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Content_NavigationException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Content_NavigationException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_NAVIGATIONS." AS `content_navigations`
		WHERE
			`project` = :project
		  AND
			`name` = :name
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT,
		'name' => $name
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
 * Tests whether given navigation belongs to current project. Takes the
 * navigation id as first argument. Returns bool.
 *
 * @throws Content_NavigationException
 * @param int Navigation id
 * @return int bool
 */
public function navigationBelongsToCurrentProject ($navigation)
{
	// access check
	if (!wcom_check_access('Content', 'Navigation', 'Use')) {
		throw new Content_NavigationException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Content_NavigationException('Input for parameter navigation is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_NAVIGATIONS." AS `content_navigations`
		WHERE
			`content_navigations`.`id` = :navigation
		  AND
			`content_navigations`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'navigation' => (int)$navigation,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and evaluate result
	if (intval($this->base->db->select($sql, 'field', $bind_params)) === 1) {
		return true;
	} else {
		return false;
	}
}

/**
 * Test whether navigation belongs to current user or not. Takes
 * the navigation id as first argument. Returns bool.
 *
 * @throws Content_NavigationException
 * @param int navigation id
 * @return bool
 */
public function navigationBelongsToCurrentUser ($navigation)
{
	// access check
	if (!wcom_check_access('Content', 'Navigation', 'Use')) {
		throw new Content_NavigationException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($navigation) || !is_numeric($navigation)) {
		throw new Content_NavigationException('Input for parameter navigation is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->navigationBelongsToCurrentProject($navigation)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Content_NavigationException extends Exception { }

?>