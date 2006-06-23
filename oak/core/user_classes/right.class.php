<?php

/**
 * Project: Oak
 * File: right.class.php
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

class User_Right {
	
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
 * Singleton. Returns instance of the User_Right object.
 * 
 * @return object
 */
public function instance()
{ 
	if (User_Right::$instance == null) {
		User_Right::$instance = new User_Right(); 
	}
	return User_Right::$instance;
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
	if (!is_array($sqlData)) {
		throw new User_RightException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	return $this->base->db->insert(OAK_DB_USER_RIGHTS, $sqlData);
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
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new User_RightException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new User_RightException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `editable` = '1' ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(OAK_DB_USER_RIGHTS, $sqlData,
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
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new User_RightException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `editable` = '1' ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_USER_RIGHTS, $where, $bind_params);
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
			`user_rights`.`name` AS `name`,
			`user_rights`.`description` AS `description`,
			`user_rights`.`editable` AS `editable`,
			`application_projects`.`id` AS `project_id`,
			`application_projects`.`owner` AS `project_owner`,
			`application_projects`.`name` AS `project_name`,
			`application_projects`.`url_name` AS `project_url_name`,
			`application_projects`.`date_modified` AS `project_date_modified`,
			`application_projects`.`date_added` AS `project_date_added`
		FROM
			".OAK_DB_USER_RIGHTS." AS `user_rights`
		LEFT JOIN
			".OAK_DB_USER_RIGHTS2APPLICATION_PROJECTS." AS `user_rights2application_projects`
		  ON
			`user_rights`.`id` = `user_rights2application_projects`.`right`
		LEFT JOIN
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		  ON
			`user_rights2application_projects`.`project` = `application_projects`.`id`
		WHERE 
			`user_rights`.`id` = :id
		  AND
			`application_projects`.`id` = :project
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
 * Method to select one or more user rights. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
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
	// define some vars
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
			`user_rights`.`name` AS `name`,
			`user_rights`.`description` AS `description`,
			`user_rights`.`editable` AS `editable`,
			`application_projects`.`id` AS `project_id`,
			`application_projects`.`owner` AS `project_owner`,
			`application_projects`.`name` AS `project_name`,
			`application_projects`.`url_name` AS `project_url_name`,
			`application_projects`.`date_modified` AS `project_date_modified`,
			`application_projects`.`date_added` AS `project_date_added`
		FROM
			".OAK_DB_USER_RIGHTS." AS `user_rights`
		LEFT JOIN
			".OAK_DB_USER_RIGHTS2APPLICATION_PROJECTS." AS `user_rights2application_projects`
		  ON
			`user_rights`.`id` = `user_rights2application_projects`.`right`
		LEFT JOIN
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		  ON
			`user_rights2application_projects`.`project` = `application_projects`.`id`
		WHERE 
			`application_projects`.`id` = :project	
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
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
 * Maps right to the current project. Takes right id as first argument.
 * Returns link id. To detach a right from a project, see
 * User_Right::detachRightFromProject().
 * 
 * @throws User_RightException
 * @param int Right id
 * @return int Link id
 */
public function mapRightToProject ($right)
{
	// input check
	if (empty($right) || !is_numeric($right)) {
		throw new User_RightException("Input for parameter right is expected to be numeric");
	}
	
	// detach right from project
	$this->detachRightFromProject($right);
	
	// prepare sql data
	$sqlData = array(
		'right' => (int)$right,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// create new mapping
	return $this->base->db->insert(OAK_DB_USER_RIGHTS2APPLICATION_PROJECTS, $sqlData);
}

/**
 * Detaches right from current project. Takes right id as
 * first argument. Returns amount of affected rows.
 *
 * @throws User_RightException
 * @param int Right id
 * @return int Amount of affected rows
 */
public function detachRightFromProject ($right)
{
	// input check
	if (empty($right) || !is_numeric($right)) {
		throw new User_RightException("Input for parameter right is expected to be numeric");
	}
	
	// prepare where clause
	$where = " WHERE `right` = :right AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'right' => (int)$right,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// delete right<->project mapping
	return $this->base->db->delete(OAK_DB_USER_RIGHTS2APPLICATION_PROJECTS, $where, $bind_params);
}

/**
 * Tests given right name for uniqueness. Takes the right name as
 * first argument and an optional right id as second argument. If
 * the right id is given, this right won't be considered when checking
 * for uniqueness (useful for updates). Returns boolean true if right
 * name is unique.
 *
 * @throws User_RightException
 * @param string Right name
 * @param int Right id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
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
			".OAK_DB_USER_RIGHTS." AS `user_rights`
		WHERE
			`name` = :name
	";
	
	// prepare bind params
	$bind_params = array(
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

class User_RightException extends Exception { }

?>