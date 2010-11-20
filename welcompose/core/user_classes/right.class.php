<?php

/**
 * Project: Welcompose
 * File: right.class.php
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
 * $Id$
 * 
 * @copyright 2008 creatics, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton. Returns instance of the User_Right object.
 * 
 * @return object
 */
function User_Right ()
{ 
	if (User_Right::$instance == null) {
		User_Right::$instance = new User_Right(); 
	}
	return User_Right::$instance;
}

class User_Right {
	
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
 * Adds right to the right table. Takes a field=>value array
 * with the right data as first argument. Returns insert id.
 * 
 * @throws User_RightException
 * @param array Row data
 * @return int Right id
 */
public function addRight ($sqlData)
{
	// access check
	if (!wcom_check_access('User', 'Right', 'Manage')) {
		throw new User_RightException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new User_RightException('Input for parameter sqlData is not an array');	
	}
	
	$sqlData['project'] = WCOM_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_USER_RIGHTS, $sqlData);
	
	// test if right belongs to current user/project
	if (!$this->rightBelongsToCurrentUser($insert_id)) {
		throw new User_RightException('Right does not belong to current user or project');
	}
	
	return $insert_id;
}

/**
 * Updates right. Takes the right id as first argument, a field=>value
 * array with the new row data as second argument. Returns amount of
 * affected rows.
 *
 * @throws User_RightException
 * @param int Right id
 * @param array Row data
 * @return int Affected rows
 */
public function updateRight ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('User', 'Right', 'Manage')) {
		throw new User_RightException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new User_RightException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new User_RightException('Input for parameter sqlData is not an array');	
	}
	
	// test if right belongs to current user/project
	if (!$this->rightBelongsToCurrentUser($id)) {
		throw new User_RightException('Right does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `editable` = '1' AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_USER_RIGHTS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes right from the user right table. Takes the user right id as
 * first argument. Returns amount of affected rows.
 * 
 * @throws User_RightException
 * @param int Right id
 * @return int Amount of affected rows
 */
public function deleteRight ($id)
{
	// access check
	if (!wcom_check_access('User', 'Right', 'Manage')) {
		throw new User_RightException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new User_RightException('Input for parameter id is not numeric');
	}
	
	// test if right belongs to current user/project
	if (!$this->rightBelongsToCurrentUser($id)) {
		throw new User_RightException('Right does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `editable` = '1' AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => (int)WCOM_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_USER_RIGHTS, $where, $bind_params);
}

/**
 * Selects right. Takes the right id as first argument.
 * Returns array.
 * 
 * @throws User_RightException
 * @param int Right id
 * @return array
 */
public function selectRight ($id)
{
	// access check
	if (!wcom_check_access('User', 'Right', 'Use')) {
		throw new User_RightException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new User_RightException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`user_rights`.`id` AS `id`,
			`user_rights`.`project` AS `project`,
			`user_rights`.`name` AS `name`,
			`user_rights`.`description` AS `description`,
			`user_rights`.`editable` AS `editable`
		FROM
			".WCOM_DB_USER_RIGHTS." AS `user_rights`
		WHERE
			`user_rights`.`id` = :id
		  AND
			`user_rights`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more user rights. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>group, int, optional: Group id</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws User_RightException
 * @param array Select params
 * @return array
 */
public function selectRights ($params = array())
{
	// access check
	if (!wcom_check_access('User', 'Right', 'Use')) {
		throw new User_RightException("You are not allowed to perform this action");
	}
	
	// define some vars
	$group = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new User_RightException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'group':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new User_RightException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`user_rights`.`id` AS `id`,
			`user_rights`.`project` AS `project`,
			`user_rights`.`name` AS `name`,
			`user_rights`.`description` AS `description`,
			`user_rights`.`editable` AS `editable`
		FROM
			".WCOM_DB_USER_RIGHTS." AS `user_rights`
		LEFT JOIN
			".WCOM_DB_USER_GROUPS2USER_RIGHTS." AS `user_groups2user_rights`
		  ON
			`user_rights`.`id` = `user_groups2user_rights`.`right`
		WHERE 
			`user_rights`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($group)) {
		$sql .= " AND `user_groups2user_rights`.`group` = :group ";
		$bind_params['group'] = $group;
	}
	
	// add result set aggregation
	$sql .= " GROUP BY `user_rights`.`id` ";
	
	// add sorting
	$sql .= " ORDER BY `user_rights`.`name` ";
	
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
 * Method to select one or more user rights. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>group, int, optional: Group id</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws User_RightException
 * @param array Select params
 * @return array
 */
public function selectTargetRights ($params = array())
{
	// access check
	if (!wcom_check_access('User', 'Right', 'Use')) {
		throw new User_RightException("You are not allowed to perform this action");
	}
	
	// define some vars
	$project = null;
	$group = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new User_RightException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'group':
			case 'project':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new User_RightException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`user_rights`.`id` AS `id`,
			`user_rights`.`project` AS `project`,
			`user_rights`.`name` AS `name`,
			`user_rights`.`description` AS `description`,
			`user_rights`.`editable` AS `editable`
		FROM
			".WCOM_DB_USER_RIGHTS." AS `user_rights`
		LEFT JOIN
			".WCOM_DB_USER_GROUPS2USER_RIGHTS." AS `user_groups2user_rights`
		  ON
			`user_rights`.`id` = `user_groups2user_rights`.`right`
		WHERE 
			`user_rights`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => (int)$project
	);
	
	// add where clauses
	if (!empty($group)) {
		$sql .= " AND `user_groups2user_rights`.`group` = :group ";
		$bind_params['group'] = $group;
	}
	
	// add result set aggregation
	$sql .= " GROUP BY `user_rights`.`id` ";
	
	// add sorting
	$sql .= " ORDER BY `user_rights`.`name` ";
	
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
 * Method to count user rights. Takes key=>value array
 * with counting params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>group, int, optional: Group id</li>
 * </ul>
 * 
 * @throws User_RightException
 * @param array Count params
 * @return int
 */
public function countRights ($params = array())
{
	// access check
	if (!wcom_check_access('User', 'Right', 'Use')) {
		throw new User_RightException("You are not allowed to perform this action");
	}
	
	// define some vars
	$group = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new User_RightException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'group':
					$$_key = (int)$_value;
				break;
			default:
				throw new User_RightException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(DISTINCT `user_rights`.`id`) AS `total`
		FROM
			".WCOM_DB_USER_RIGHTS." AS `user_rights`
		LEFT JOIN
			".WCOM_DB_USER_GROUPS2USER_RIGHTS." AS `user_groups2user_rights`
		  ON
			`user_rights`.`id` = `user_groups2user_rights`.`right`
		WHERE 
			`user_rights`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($group)) {
		$sql .= " AND `user_groups2user_rights`.`group` = :group ";
		$bind_params['group'] = $group;
	}
	
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Tests given right name for uniqueness. Takes the right name as
 * first argument and an optional right id as second argument. If
 * the right id is given, this right won't be considered when checking
 * for uniqueness (useful for updates). Returns boolean true if right
 * name is unique.
 *
 * @throws User_RightException
 * @param string Right name
 * @param int Right id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!wcom_check_access('User', 'Right', 'Use')) {
		throw new User_RightException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new User_RightException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new User_RightException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new User_RightException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_USER_RIGHTS." AS `user_rights`
		WHERE
			`name` = :name
		  AND
			`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'name' => $name,
		'project' => (int)$project
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
 * Tests whether given right belongs to current project. Takes the right
 * id as first argument. Returns boolean true or false.
 *
 * @throws User_RightException
 * @param int Right id
 * @return bool
 */
public function rightBelongsToCurrentProject ($right)
{
	// access check
	if (!wcom_check_access('User', 'Right', 'Use')) {
		throw new User_RightException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($right) || !is_numeric($right)) {
		throw new User_RightException('Input for parameter right is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".WCOM_DB_USER_RIGHTS." AS `user_rights`
		WHERE
			`user_rights`.`id` = :right
		AND
			`user_rights`.`project` = :project
	";
	// prepare bind params
	$bind_params = array(
		'right' => (int)$right,
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
 * Tests whether right belongs to current user or not. Takes
 * the right id as first argument. Returns bool.
 *
 * @throws User_RightException
 * @param int Right id
 * @return bool
 */
public function rightBelongsToCurrentUser ($right)
{
	// access check
	if (!wcom_check_access('User', 'Right', 'Use')) {
		throw new User_RightException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($right) || !is_numeric($right)) {
		throw new User_RightException('Input for parameter right is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->rightBelongsToCurrentProject($right)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class User_RightException extends Exception { }

?>