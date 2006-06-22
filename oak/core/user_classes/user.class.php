<?php

/**
 * Project: Oak
 * File: user.class.php
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

class User_User {
	
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
 * Singleton. Returns instance of the User_User object.
 * 
 * @return object
 */
public function instance()
{ 
	if (User_User::$instance == null) {
		User_User::$instance = new User_User(); 
	}
	return User_User::$instance;
}

/**
 * Adds user to the user table. Takes a field=>value array with user
 * data as first argument. Returns insert id. 
 * 
 * @throws User_UserException
 * @param array Row data
 * @return int User id
 */
public function addUser ($sqlData)
{
	if (!is_array($sqlData)) {
		throw new User_UserException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	return $this->base->db->insert(OAK_DB_USER_USERS, $sqlData);
}

/**
 * Updates user. Takes the user id as first argument, a field=>value
 * array with the new user data as second argument. Returns amount
 * of affected rows.
 *
 * @throws User_UserException
 * @param int User id
 * @param array Row data
 * @return int Affected rows
*/
public function updateUser ($id, $sqlData)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new User_UserException('Input for parameter id is not numeric');
	}
	if (!is_array($sqlData)) {
		throw new User_UserException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(OAK_DB_USER_USERS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes user from the user table. Takes the user id as first argument.
 * Returns amount of affected rows
 * 
 * @throws User_UserException
 * @param int User id
 * @return int Amount of affected rows
 */
public function deleteUser ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new User_UserException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_USER_USERS, $where, $bind_params);
}

/**
 * Selects one user. Takes the user id as first argument.
 * Returns array with user information.
 * 
 * @throws User_UserException
 * @param int User id
 * @return array
 */
public function selectUser ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new User_UserException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`user_users`.`id` AS `id`,
			`user_users`.`email` AS `email`,
			`user_users`.`secret` AS `secret`,
			`user_users`.`author` AS `author`,
			`user_users`.`editable` AS `editable`,
			`user_users`.`active` AS `active`,
			`user_users`.`date_modified` AS `date_modified`,
			`user_users`.`date_added` AS `date_added`,
			`user_groups`.`id` AS `group_id`,
			`user_groups`.`project` AS `group_project`,
			`user_groups`.`name` AS `group_name`,
			`user_groups`.`description` AS `group_description`,
			`user_groups`.`editable` AS `group_editable`,
			`user_groups`.`date_modified` AS `group_date_modified`,
			`user_groups`.`date_added` AS `group_date_added`,
			`application_projects`.`id` AS `project_id`,
			`application_projects`.`owner` AS `project_owner`,
			`application_projects`.`name` AS `project_name`,
			`application_projects`.`url_name` AS `project_url_name`,
			`application_projects`.`date_modified` AS `project_date_modified`,
			`application_projects`.`date_added` AS `project_date_added`
		FROM
			".OAK_DB_USER_USERS." AS `user_users`
		LEFT JOIN
			".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
		  ON
			`user_users`.`id` = `user_users2application_projects`.`user`
		LEFT JOIN
			".OAK_DB_USER_USERS2USER_GROUPS." AS `user_users2user_groups`
		  ON
			`user_users`.`id` = `user_users2user_groups`.`user`
		LEFT JOIN
			".OAK_DB_USER_GROUPS." AS `user_groups`
		  ON
			`user_users2user_groups`.`group` = `user_groups`.`id`
		LEFT JOIN
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		  ON
			`user_users2application_projects`.`project` = `application_projects`.`id`
		WHERE 
			`user_users`.`id` = :id
		  AND
			`application_projects`.`id` = :project
		  AND
			`user_groups`.`project` = :group_project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT,
		'group_project' => OAK_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more users. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>group, int, optional: User group id</li>
 * <li>name, string, optional: User name</li>
 * <li>author, int, optional: Author bit (either 0 or 1)</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * <li>order_marco, string, otpional: How to sort the result set.
 * Supported macros:
 *    <ul>
 *        <li>DATE_ADDED: sort by date added</li>
 *        <li>NAME: sort by name</li>
 *    </ul>
 * </li>
 * </ul>
 * 
 * @throws User_UserException
 * @param array Select params
 * @return array
 */
public function selectUsers ($params = array())
{
	// define some vars
	$group = null;
	$name = null;
	$author = null;
	$start = null;
	$limit = null;
	$order_macro = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new User_UserException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'name':
			case 'order_marco':
					$$_key = (string)$_value;
				break;
			case 'group':
			case 'author':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new User_UserException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'NAME' => '`user_users`.`name`',
		'DATE_ADDED' => '`user_users`.`date_added`'
	);
	
	// prepare query
	$sql = "
		SELECT 
			`user_users`.`id` AS `id`,
			`user_users`.`email` AS `email`,
			`user_users`.`secret` AS `secret`,
			`user_users`.`author` AS `author`,
			`user_users`.`editable` AS `editable`,
			`user_users`.`active` AS `active`,
			`user_users`.`date_modified` AS `date_modified`,
			`user_users`.`date_added` AS `date_added`,
			`user_groups`.`id` AS `group_id`,
			`user_groups`.`project` AS `group_project`,
			`user_groups`.`name` AS `group_name`,
			`user_groups`.`description` AS `group_description`,
			`user_groups`.`editable` AS `group_editable`,
			`user_groups`.`date_modified` AS `group_date_modified`,
			`user_groups`.`date_added` AS `group_date_added`,
			`application_projects`.`id` AS `project_id`,
			`application_projects`.`owner` AS `project_owner`,
			`application_projects`.`name` AS `project_name`,
			`application_projects`.`url_name` AS `project_url_name`,
			`application_projects`.`date_modified` AS `project_date_modified`,
			`application_projects`.`date_added` AS `project_date_added`
		FROM
			".OAK_DB_USER_USERS." AS `user_users`
		LEFT JOIN
			".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
		  ON
			`user_users`.`id` = `user_users2application_projects`.`user`
		LEFT JOIN
			".OAK_DB_USER_USERS2USER_GROUPS." AS `user_users2user_groups`
		  ON
			`user_users`.`id` = `user_users2user_groups`.`user`
		LEFT JOIN
			".OAK_DB_USER_GROUPS." AS `user_groups`
		  ON
			`user_users2user_groups`.`group` = `user_groups`.`id`
		LEFT JOIN
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		  ON
			`user_users2application_projects`.`project` = `application_projects`.`id`
		WHERE 
			`application_projects`.`id` = :project
		  AND
			`user_groups`.`project` = :group_project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT,
		'group_project' => OAK_CURRENT_PROJECT
	);
	
	// prepare where clauses
	if (!empty($group) && is_numeric($group)) {
		$sql .= " AND `user_groups`.`id` = :group ";
		$bind_params['group'] = (int)$group;
	}
	if (!empty($name) && is_scalar($name)) {
		$sql .= " AND `user_users`.`name` = :name ";
		$bind_params['name'] = (int)$name;
	}
	if (!empty($author) && is_numeric($author)) {
		$sql .= " AND `user_users`.`author` = :author ";
		$bind_params['author'] = (int)$author;
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
 * Maps user to one group. Takes user id as first argument, group id
 * as second argument. Returns the id of the new link.
 *
 * If the second parameter is omitted, the user will be detached from
 * all groups and the function returns boolean true.  
 * 
 * @throws User_UserException
 * @param int User id
 * @param int Group id
 * @return int Link id 
 */
public function mapUserToGroup ($user, $group = null)
{
	// input check
	if (empty($user) || !is_numeric($user)) {
		throw new User_UserException("Input for parameter user is expected to be numeric");
	}
	if (!empty($group) && !is_numeric($group)) {
		throw new User_UserException("Input for parameter group is expected to bei either null or numeric");
	}
	
	// delete user<->group mappings if necessary
	$sql = "
		DELETE FROM
			 ".OAK_DB_USER_USERS2USER_GROUPS." AS `user_users2user_groups`
		USING
			`user_users2user_groups`
		LEFT JOIN
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
		'user' => (int)$user,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query
	$this->base->db->execute($sql, $bind_params);
	
	// if group is not empty, add new link
	if (!empty($group) && is_numeric($group)) {	
		// prepare sql data
		$sqlData = array(
			'user' => (int)$user,
			'group' => (int)$group
		);
	
		// create new mapping
		return $this->base->db->insert(OAK_DB_USER_USERS2USER_GROUPS, $sqlData);
	}
	
	return true;
}

/**
 * Maps user to the current project. Takes user id as first argument.
 * Returns link id. To detach a user from a project, see
 * User_User::detachUserFromProject().
 * 
 * @throws User_UserException
 * @param int User id
 * @param int Group id
 * @return int Link id
 */
public function mapUserToProject ($user)
{
	// input check
	if (empty($user) || !is_numeric($user)) {
		throw new User_UserException("Input for parameter user is expected to be numeric");
	}
	
	// detach user from project
	$this->detachUserFromProject($user);
	
	// prepare sql data
	$sqlData = array(
		'user' => (int)$user,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// create new mapping
	return $this->base->db->insert(OAK_DB_USER_USERS2APPLICATION_PROJECTS, $sqlData);
}

/**
 * Detaches user from current project. Takes user id as
 * first argument. Returns amount of affected rows.
 *
 * @throws User_UserException
 * @param int User id
 * @return int Amount of affected rows
 */
public function detachUserFromProject ($user)
{
	// input check
	if (empty($user) || !is_numeric($user)) {
		throw new User_UserException("Input for parameter user is expected to be numeric");
	}
	
	// prepare where clause
	$where = " WHERE `user` = :user AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'user' => (int)$user,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// delete user<->project mapping
	return $this->base->db->delete(OAK_DB_USER_USERS2APPLICATION_PROJECTS, $where, $bind_params);
}

/**
 * Tests given email address for uniqueness. Takes the email address as
 * first argument and an optional user id as second argument. If
 * the user id is given, this user won't be considered when checking
 * for uniqueness (useful for updates). Returns boolean true if the email
 * address is unique.
 *
 * @throws User_UserException
 * @param string User's email address
 * @param int User id
 * @return bool
 */
public function testForUniqueEmail ($email, $id = null)
{
	// input check
	if (empty($email)) {
		throw new User_GroupException("Input for parameter email is not expected to be empty");
	}
	if (!is_scalar($email)) {
		throw new User_GroupException("Input for parameter email is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new User_GroupException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_USER_USERS." AS `users_users`
		WHERE
			`email` = :email
	";
	
	// prepare bind params
	$bind_params = array(
		'email' => $email
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

public function initUserAdmin ()
{
	define('OAK_CURRENT_USER', 1);
	
	return 1;
}

// end of class
}

class User_UserException extends Exception { }

?>