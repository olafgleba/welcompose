<?php

/**
 * Project: Oak
 * File: group.class.php
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

class User_Group {
	
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
 * Singleton. Returns instance of the User_Group object.
 * 
 * @return object
 */
public function instance()
{ 
	if (User_Group::$instance == null) {
		User_Group::$instance = new User_Group(); 
	}
	return User_Group::$instance;
}

/**
 * Adds group to the group table. Takes a field=>value array
 * with the group data as first argument. Returns insert id.
 * 
 * @throws User_GroupException
 * @param array Row data
 * @return int Group id
 */
public function addGroup ($sqlData)
{
	// access check
	if (!oak_check_access('User', 'Group', 'Manage')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new User_GroupException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new group will be assigned to the current project
	$sqlData['project'] = OAK_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(OAK_DB_USER_GROUPS, $sqlData);
	
	// test if group belongs to current project/user
	if (!$this->groupBelongsToCurrentUser($insert_id)) {
		throw new User_GroupException('Group does not belong to current user or project');
	}
	
	return $insert_id;
}

/**
 * Updates group. Takes the group id as first argument, a field=>value
 * array with the new row data as second argument. Returns amount of
 * affected rows.
 *
 * @throws User_GroupException
 * @param int Group id
 * @param array Row data
 * @return int Affected rows
 */
public function updateGroup ($id, $sqlData)
{
	// access check
	if (!oak_check_access('User', 'Group', 'Manage')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new User_GroupException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new User_GroupException('Input for parameter sqlData is not an array');	
	}
	
	// test if group belongs to current project/user
	if (!$this->groupBelongsToCurrentUser($id)) {
		throw new User_GroupException('Group does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project  AND `editable` = '1' ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(OAK_DB_USER_GROUPS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes group from the user group table. Takes the user group id as
 * first argument. Returns amount of affected rows.
 * 
 * @throws User_GroupException
 * @param int Group id
 * @return int Amount of affected rows
 */
public function deleteGroup ($id)
{
	// access check
	if (!oak_check_access('User', 'Group', 'Manage')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new User_GroupException('Input for parameter id is not numeric');
	}
	
	// test if group belongs to current project/user
	if (!$this->groupBelongsToCurrentUser($id)) {
		throw new User_GroupException('Group does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project AND `editable` = '1' ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_USER_GROUPS, $where, $bind_params);
}

/**
 * Selects group. Takes the group id as first argument.
 * Returns array.
 * 
 * @throws User_GroupException
 * @param int Group id
 * @return array
 */
public function selectGroup ($id)
{
	// access check
	if (!oak_check_access('User', 'Group', 'Use')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new User_GroupException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`user_groups`.`id` AS `id`,
			`user_groups`.`project` AS `project`,
			`user_groups`.`name` AS `name`,
			`user_groups`.`description` AS `description`,
			`user_groups`.`editable` AS `editable`,
			`user_groups`.`date_modified` AS `date_modified`,
			`user_groups`.`date_added` AS `date_added`,
			`application_projects`.`id` AS `project_id`,
			`application_projects`.`owner` AS `project_owner`,
			`application_projects`.`name` AS `project_name`,
			`application_projects`.`name_url` AS `project_name_url`,
			`application_projects`.`date_modified` AS `project_date_modified`,
			`application_projects`.`date_added` AS `project_date_added`
		FROM
			".OAK_DB_USER_GROUPS." AS `user_groups`
		LEFT JOIN
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		  ON
			`user_groups`.`project` = `application_projects`.`id`
		WHERE
			`user_groups`.`id` = :id
		  AND
			`user_groups`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more user groups. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws User_GroupException
 * @param array Select params
 * @return array
 */
public function selectGroups ($params = array())
{
	// access check
	if (!oak_check_access('User', 'Group', 'Use')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
	// define some vars
	$user = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new User_GroupException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'user':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new User_GroupException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`user_groups`.`id` AS `id`,
			`user_groups`.`project` AS `project`,
			`user_groups`.`name` AS `name`,
			`user_groups`.`description` AS `description`,
			`user_groups`.`editable` AS `editable`,
			`user_groups`.`date_modified` AS `date_modified`,
			`user_groups`.`date_added` AS `date_added`,
			`application_projects`.`id` AS `project_id`,
			`application_projects`.`owner` AS `project_owner`,
			`application_projects`.`name` AS `project_name`,
			`application_projects`.`name_url` AS `project_name_url`,
			`application_projects`.`date_modified` AS `project_date_modified`,
			`application_projects`.`date_added` AS `project_date_added`
		FROM
			".OAK_DB_USER_GROUPS." AS `user_groups`
		JOIN
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		  ON
			`user_groups`.`project` = `application_projects`.`id`
		LEFT JOIN
			".OAK_DB_USER_USERS2USER_GROUPS." AS `user_users2user_groups`
		  ON
			`user_groups`.`id` = `user_users2user_groups`.`group`
		WHERE 
			`user_groups`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($user)) {
		$sql .= " AND `user_users2user_groups`.`user` = :user ";
		$bind_params['user'] = (int)$user;
	}
	
	// add sorting
	$sql .= " ORDER BY `user_groups`.`name` ";
	
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
 * Maps groups to one or more rights. Takes group id as first argument,
 * a list of right ids as second argument. Returns boolean true.
 *
 * If the list of right ids is omitted, the group will be detached from
 * all groups.
 *
 * @throws User_GroupException
 * @param int group id
 * @param array Right ids
 * @return bool
 */
public function mapGroupToRights ($group, $rights = array())
{
	// access check
	if (!oak_check_access('User', 'Group', 'Manage')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($group) || !is_numeric($group)) {
		throw new User_GroupException("Input for parameter group is expected to be numeric");
	}
	if (empty($group) || !is_numeric($group)) {
		throw new User_GroupException("Input for parameter group is expected to be numeric");
	}
	
	// test if group belongs to current project/user
	if (!$this->groupBelongsToCurrentUser($group)) {
		throw new User_GroupException('Group does not belong to current user or project');
	}
	
	// detach group from all rights
	$sql = "
		DELETE FROM
			 ".OAK_DB_USER_GROUPS2USER_RIGHTS." AS `user_groups2user_rights`
		USING
			`user_groups2user_rights`
		LEFT JOIN
			".OAK_DB_USER_GROUPS." AS `user_groups`
		ON
			`user_groups2user_rights`.`group` = `user_groups`.`id`
		WHERE
			`user_groups2user_rights`.`group` = :group
		AND
			`user_groups`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'group' => (int)$group,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query
	$this->base->db->execute($sql, $bind_params);
	
	// load right class
	$RIGHT = load('user:right');
	
	// add new links if necessary
	foreach ($rights as $_right) {
		
		// input check
		if (!empty($_right) && is_numeric($_right)) {
			
			// test if right belongs to current project/user
			if (!$RIGHT->rightBelongsToCurrentUser($_right)) {
				throw new User_GroupException("Right does not belong to current user or project");
			}
			
			// prepare sql data
			$sqlData = array(
				'group' => (int)$group,
				'right' => (int)$_right
			);
			
			// insert new link
			$this->base->db->insert(OAK_DB_USER_GROUPS2USER_RIGHTS, $sqlData);
		}
	}
	
	return true;
}

/**
 * Selects links between the given group and their associated rights. Takes
 * the group id as first argument. Returns array.
 *
 * @throws User_GroupException
 * @param int Group id
 * @return array
 */
public function selectGroupToRightsMap ($group)
{
	// access check
	if (!oak_check_access('User', 'Group', 'Use')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($group) || !is_numeric($group)) {
		throw new User_GroupException("Input for parameter group is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT
			`user_groups2user_rights`.`id`,
			`user_groups2user_rights`.`group`,
			`user_groups2user_rights`.`right`
		FROM
			".OAK_DB_USER_GROUPS2USER_RIGHTS." AS `user_groups2user_rights`
		JOIN
			".OAK_DB_USER_RIGHTS." AS `user_rights`
		  ON
			`user_groups2user_rights`.`right` = `user_rights`.`id`
		WHERE
			`user_groups2user_rights`.`group` = :group
		  AND
			`user_rights`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'group' => $group,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'multi', $bind_params);
}

/**
 * Tests whether given group belongs to current project. Takes the group
 * id as first argument. Returns boolean true or false.
 *
 * @throws User_GroupException
 * @param int Group id
 * @return bool
 */
public function groupBelongsToCurrentProject ($group)
{
	// access check
	if (!oak_check_access('User', 'Group', 'Use')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($group) || !is_numeric($group)) {
		throw new User_GroupException('Input for parameter group is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".OAK_DB_USER_GROUPS." AS `user_groups`
		WHERE
			`user_groups`.`id` = :group
		AND
			`user_groups`.`project` = :project
	";
	// prepare bind params
	$bind_params = array(
		'group' => (int)$group,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query and evaluate result
	if (intval($this->base->db->select($sql, 'field', $bind_params)) === 1) {
		return true;
	} else {
		return false;
	}
}

/**
 * Tests whether group belongs to current user or not. Takes
 * the group id as first argument. Returns bool.
 *
 * @throws User_GroupException
 * @param int Group id
 * @return bool
 */
public function groupBelongsToCurrentUser ($group)
{
	// access check
	if (!oak_check_access('User', 'Group', 'Use')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($group) || !is_numeric($group)) {
		throw new User_GroupException('Input for parameter group is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->groupBelongsToCurrentProject($group)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(OAK_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

/**
 * Tests given group name for uniqueness. Takes the group name as
 * first argument and an optional group id as second argument. If
 * the group id is given, this group won't be considered when checking
 * for uniqueness (useful for updates). Returns boolean true if group
 * name is unique.
 *
 * @throws User_GroupException
 * @param string Group name
 * @param int Group id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!oak_check_access('User', 'Group', 'Use')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new User_GroupException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new User_GroupException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new User_GroupException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_USER_GROUPS." AS `user_groups`
		WHERE
			`project` = :project
		  AND
			`name` = :name
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT,
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

// end of class
}

class User_GroupException extends Exception { }

?>