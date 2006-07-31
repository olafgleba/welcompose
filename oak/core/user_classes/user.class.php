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

/**
 * Wrapper for User_User::userCheckAccess();
 */
function oak_check_access ($area = null, $component = null, $action = null)
{
	// get instance of user class
	$USER = User_User::instance();
	
	// run access check
	return $USER->userCheckAccess($area, $component, $action);
}

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
	// access check
	if (!oak_check_access('User', 'User', 'Manage')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
	// input check
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
	// access check
	if (!oak_check_access('User', 'User', 'Manage')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
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
	// access check
	if (!oak_check_access('User', 'User', 'Manage')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
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
			`user_users`.`editable` AS `editable`,
			`user_users`.`date_modified` AS `date_modified`,
			`user_users`.`date_added` AS `date_added`,
			`user_users2application_projects`.`author` AS `author`,
			`user_users2application_projects`.`active` AS `active`,
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
 * <li>email, string, optional: E-mail address</li>
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
	$email = null;
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
			case 'email':
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
			`user_users`.`editable` AS `editable`,
			`user_users2application_projects`.`active` AS `active`,
			`user_users2application_projects`.`author` AS `author`,
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
 * Returns anonymous user of the current project. Throws exception
 * if no anonymous user can be found. 
 * 
 * @throws User_UserException
 * @return array
 */
public function selectAnonymousUser ()
{
	$sql = "
		SELECT
			`user_users`.`id` AS `id`
		FROM
			".OAK_DB_USER_USERS." AS `user_users`
		JOIN
			".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
		  ON
			`user_users`.`id` = `user_users2application_projects`.`user`
		JOIN
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		  ON
			`user_users2application_projects`.`project` = `application_projects`.`id`
		WHERE
			`user_users`.`email` = 'OAK_ANONYMOUS'
		  AND
			`application_projects`.`id` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query
	$result = (int)$this->base->db->select($sql, 'field', $bind_params);
	
	// make sure that there is some anonymous user
	if ($result < 1) {
		throw new User_UserException("Unable to find the anonymouser user of the current project");
	}
	
	// return complete user information
	return $this->selectUser($result);
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
	// access check
	if (!oak_check_access('User', 'User', 'Manage')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
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
		'user' => (int)$user,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query
	$this->base->db->execute($sql, $bind_params);
	
	// if group is not empty, add new link
	if (!empty($group) && is_numeric($group)) {	
		// load group class
		$GROUP = load('user:group');
		
		// test if group belongs to current user
		if (!$GROUP->groupBelongsToCurrentUser($group)) {
			throw new User_UserException('Group does not belong to current project');
		}
		
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
 * Maps user to the current project. Takes user id as first argument, activity and
 * author switch as second and third argument. Returns link id. To detach a user from
 * a project, see User_User::detachUserFromProject().
 * 
 * @throws User_UserException
 * @param int User id
 * @param int Group id
 * @return int Link id
 */
public function mapUserToProject ($user, $active = 1, $author = 0)
{
	// access check
	if (!oak_check_access('User', 'User', 'Manage')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($user) || !is_numeric($user)) {
		throw new User_UserException("Input for parameter user is expected to be numeric");
	}
	
	// detach user from project
	$this->detachUserFromProject($user);
	
	// prepare sql data
	$sqlData = array(
		'user' => (int)$user,
		'project' => OAK_CURRENT_PROJECT,
		'active' => (((int)$active === 1) ? "1" : "0"),
		'author' => (((int)$author === 1) ? "1" : "0")
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
	// access check
	if (!oak_check_access('User', 'User', 'Manage')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
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
	// access check
	if (!oak_check_access('User', 'User', 'Use')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
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

/**
 * Finds out, whether a user is deletable or not. Takes the user id as first
 * argument. Returns bool.
 *
 * @throws User_UserException
 * @param int User id
 * @return bool
 */
public function isDeletable ($user)
{
	// access check
	if (!oak_check_access('User', 'User', 'Use')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
	// check input
	if (empty($user) || !is_numeric($user)) {
		throw new User_UserException("Input for parameter user is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".OAK_DB_USER_USERS." AS `user_users`
		JOIN
			".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
		ON
			`user_users`.`id` = `user_users2application_projects`.`user`
		WHERE
			`user_users`.`id` = :user
		AND 
			`user_users`.`editable` = '1'
	";
	
	// prepare bind params
	$bind_params = array(
		'user' => (int)$user
	);
	
	// execute query and evaluate result
	if (intval($this->base->db->select($sql, 'field', $bind_params)) < 1) {
		return true;
	} else {
		return false;
	}
}

/**
 * Proceeds user login. Compares supplied email address and secret
 * with the email addresses and secrets stored in database. If the
 * the combination of email address and secret is found, the user id
 * will be stored in the current session.
 *
 * Please be aware that a successful login is not a guarantee, that the
 * current user is active or an author. Please use functions like
 * User_User::userIsAuthor() or User_User::userIsActive() to check
 * this.
 *
 * Takes the email address of the user to login as first argument,
 * its secret as second argument. Returns bool.
 * 
 * @throws User_UserException
 * @param string E-mail address
 * @param string Secret
 * @return bool
 */
public function logIntoAdmin ($input_email, $input_secret)
{
	// input check
	if (empty($input_secret)) {
		throw new User_UserException('Input for parameter input_secret is not expected to be empty');
	}
	if (empty($input_email)) {
		throw new User_UserException('Input for parameter input_email is not expected to be empty');
	}
	
	// let's see if email/secret matches the email/secret stored in database
	if (!$this->testSecret($input_secret, $input_email)) {
		throw new User_UserException("Invalid login");
	}
	
	// get user's encrypted secret
	$sql = "
		SELECT
			`id`
		FROM
			".OAK_DB_USER_USERS." AS `user_users`
		WHERE
			`user_users`.`email` = :email
	";
	
	// prepare bind params
	$bind_params = array(
		'email' => $input_email
	);
	
	// get user id from database
	$id = $this->base->db->select($sql, 'field', $bind_params);
	
	// save user id to session
	$_SESSION['admin']['user'] = $id;
	
	// init project admin
	$PROJECT = load('application:project');
	$PROJECT->initProjectAdmin($id);
	
	// get user's rights
	$sql = "
		SELECT
			`user_rights`.`id`,
			`user_rights`.`name`
		FROM
			".OAK_DB_USER_RIGHTS." AS `user_rights`
		JOIN
			".OAK_DB_USER_GROUPS2USER_RIGHTS." AS `user_groups2user_rights`
		  ON
			`user_rights`.`id` = `user_groups2user_rights`.`right`
		JOIN
			".OAK_DB_USER_GROUPS." AS `user_groups`
		  ON
			`user_groups2user_rights`.`group` = `user_groups`.`id`
		  AND
			`user_groups`.`project` = `user_rights`.`project`
		JOIN
			".OAK_DB_USER_USERS2USER_GROUPS." AS `user_users2user_groups`
		  ON
			`user_groups`.`id` = `user_users2user_groups`.`group`
		WHERE
			`user_users2user_groups`.`user` = :user
		  AND
			`user_rights`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'user' => $id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// prepare rights array
	$_SESSION['admin']['rights'] = array();
	foreach ($this->base->db->select($sql, 'multi', $bind_params) as $_right) {
		$_SESSION['admin']['rights'][(int)$_right['id']] = $_right['name'];
	}
	
	// get group
	$GROUP = load('user:group');
	foreach ($GROUP->selectGroups(array('user' => $id)) as $_group) {
		if (!empty($_group['id']) && is_numeric($_group['id'])) {
			$_SESSION['admin']['group'] = $_group['id'];
			break;
		}
	}
	
	return true;
}

/**
 * Tests if current visitor is logged into the admin area using
 * the user id supplied by the current session. Returns bool.
 * 
 * Please be aware that positive return value is not a guarantee,
 * that the current user is active or an author. Please use functions
 * like User_User::userIsAuthor() or User_User::userIsActive() to
 * check this.
 * 
 * @return bool
 */
public function userIsLoggedIntoAdmin ()
{
	// import user id from session
	$user_id = Base_Cnc::filterRequest($_SESSION['admin']['user'], OAK_REGEX_NUMERIC);
	
	if (is_null($user_id)) {
		return false;
	}
	
	// make sure that the user exists
	if (!$this->userExists($user_id)) {
		return false;
	}
	
	return true;
}

public function logIntoPublicAreaAsAnonymous ($anon_user)
{
	// let's see if this user is really an anonymous user
	$sql = "
		SELECT
			COUNT(*) AS `total`
		FROM
			".OAK_DB_USER_USERS." AS `user_users`
		WHERE
			`user_users`.`id` = :id
		  AND
			`user_users`.`email` = 'OAK_ANONYMOUS'
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => $anon_user
	);
	
	// evaluate result
	if (intval($this->base->db->select($sql, 'field', $bind_params)) !== 1) {
		throw new User_UserException('Given user does not exist or is not an anonymous user');
	}
	
	// make sure that the user id is empty, so that a new "login" will be done
	// on every new request
	$_SESSION['public_area']['user'] = null;
	
	// get user's rights
	$sql = "
		SELECT
			`user_rights`.`id`,
			`user_rights`.`name`
		FROM
			".OAK_DB_USER_RIGHTS." AS `user_rights`
		JOIN
			".OAK_DB_USER_GROUPS2USER_RIGHTS." AS `user_groups2user_rights`
		  ON
			`user_rights`.`id` = `user_groups2user_rights`.`right`
		JOIN
			".OAK_DB_USER_GROUPS." AS `user_groups`
		  ON
			`user_groups2user_rights`.`group` = `user_groups`.`id`
		  AND
			`user_groups`.`project` = `user_rights`.`project`
		JOIN
			".OAK_DB_USER_USERS2USER_GROUPS." AS `user_users2user_groups`
		  ON
			`user_groups`.`id` = `user_users2user_groups`.`group`
		WHERE
			`user_users2user_groups`.`user` = :user
		  AND
			`user_rights`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'user' => $anon_user,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// prepare rights array
	$_SESSION['public_area']['rights'] = array();
	foreach ($this->base->db->select($sql, 'multi', $bind_params) as $_right) {
		$_SESSION['public_area']['rights'][(int)$_right['id']] = $_right['name'];
	}
	
	// get group
	$GROUP = load('user:group');
	foreach ($GROUP->selectGroups(array('user' => $anon_user)) as $_group) {
		if (!empty($_group['id']) && is_numeric($_group['id'])) {
			$_SESSION['public_area']['group'] = $_group['id'];
			break;
		}
	}
	
	return true;
}

/** 
 * Sets OAK_CURRENT_USER constant using the user id saved in the
 * open session. Returns user id.
 *
 * @throws User_UserException
 * @return int User id
 */
public function initUserAdmin ()
{
	// import user id from session
	$user_id = Base_Cnc::filterRequest($_SESSION['admin']['user'], OAK_REGEX_NUMERIC);
	
	// make sure that the user exists
	if (!$this->userExists($user_id)) {
		throw new User_UserException("User does not exist");
	}
	
	// define constant for current user
	define('OAK_CURRENT_USER', $user_id);
	
	// return id of current user
	return $user_id;
}

/**
 * Initialises "user environment" for the public area. Sets the
 * OAK_CURRENT_USER and the OAK_CURRENT_USER_ANONYMOUS constants
 * using the saved user id in the open session or the anonymous
 * user configured for the current project. Returns user id.
 *
 * @return int User id
 */
public function initUserPublicArea ()
{
	// import user id from session
	$user_id = Base_Cnc::filterRequest($_SESSION['public']['user'], OAK_REGEX_NUMERIC);
	
	// if there's no user id or the user does not exist, register the
	// user as anonymous user
	if (is_null($user_id) || !$this->userExists($user_id) || !$this->userBelongsToCurrentProject($user_id)) {
		// get anonymous user of the current project
		$anon_user = $this->selectAnonymousUser();
		
		// now we have to login the guy here as anonymous user. sucks a bit, but
		// what else shall we do?
		$this->logIntoPublicAreaAsAnonymous($anon_user['id']);
		
		// define constant for current user
		define('OAK_CURRENT_USER', (int)$anon_user['id']);
		
		// define anon user constant
		define('OAK_CURRENT_USER_ANONYMOUS', true);
		
		return OAK_CURRENT_USER;
	} else {
		// define constant for current user
		define('OAK_CURRENT_USER', (int)$user_id);
		
		// define anon user constant
		define('OAK_CURRENT_USER_ANONYMOUS', false);
		
		return OAK_CURRENT_USER;
	}
}

/**
 * Finds out whether user exists or nut. Takes the user id
 * as first argument, the project id as second argument.
 * Returns bool.
 * 
 * @throws User_UserException
 * @param int User id
 * @param int Project id
 * @return bool
 */
public function userExists ($user, $project = null)
{
	// input check
	if (empty($user) || !is_numeric($user)) {
		throw new User_UserException('Input for parameter user is not expected to be empty');
	}
	if (!empty($project) && !is_numeric($project)) {
		throw new User_UserException('Input for parameter project is not expected to be empty');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*) AS `total`
		FROM
			".OAK_DB_USER_USERS." AS `user_users`
		LEFT JOIN
			".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
		  ON
			`user_users`.`id` = `user_users2application_projects`.`user`
		WHERE
			`user_users`.`id` = :user
	";
	
	// prepare bind params
	$bind_params = array(
		'user' => $user
	);
	
	// add where clauses
	if (!empty($project)) {
		$sql .= " AND `user_users2application_projects`.`project` = :project ";
		$bind_params['project'] = $project;
	}
	
	// execute query and evaluate result
	if (intval($this->base->db->select($sql, 'field', $bind_params)) < 1) {
		return false;
	} else {
		return true;
	}
}

/**
 * Tests if given user is an author of the given project. Takes
 * the user id as first argument, the project id as second
 * argument. Returns bool.
 * 
 * @throws User_UserException
 * @param int User id
 * @param int Project id
 * @return bool
 */ 
public function userIsAuthor ($user, $project)
{
	// input check
	if (empty($user) || !is_numeric($user)) {
		throw new User_UserException('Input for parameter user is not expected to be empty');
	}
	if (empty($project) || !is_numeric($project)) {
		throw new User_UserException('Input for parameter project is not expected to be empty');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*) AS `total`
		FROM
			".OAK_DB_USER_USERS." AS `user_users`
		JOIN
			".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
		  ON
			`user_users`.`id` = `user_users2application_projects`.`user`
		WHERE
			`user_users`.`id` = :user
		AND
			`user_users2application_projects`.`project` = :project 
		AND
			`user_users2application_projects`.`author` = '1'
	";
	
	// prepare bind params
	$bind_params = array(
		'user' => $user,
		'project' => $project
	);
	
	// execute query and evaluate result
	if (intval($this->base->db->select($sql, 'field', $bind_params)) < 1) {
		return false;
	} else {
		return true;
	}
}

/**
 * Test if user is an active user of the given project. Takes
 * the user id as first argument, the project id as second
 * argument. Returns bool.
 * 
 * @throws User_UserException
 * @param int User id
 * @param int Project id
 * @return bool
 */
public function userIsActive ($user, $project)
{
	// input check
	if (empty($user) || !is_numeric($user)) {
		throw new User_UserException('Input for parameter user is not expected to be empty');
	}
	if (empty($project) || !is_numeric($project)) {
		throw new User_UserException('Input for parameter project is not expected to be empty');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*) AS `total`
		FROM
			".OAK_DB_USER_USERS." AS `user_users`
		JOIN
			".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
		  ON
			`user_users`.`id` = `user_users2application_projects`.`user`
		WHERE
			`user_users`.`id` = :user
		AND
			`user_users2application_projects`.`project` = :project 
		AND
			`user_users2application_projects`.`active` = '1'
	";
	
	// prepare bind params
	$bind_params = array(
		'user' => $user,
		'project' => $project
	);
	
	// execute query and evaluate result
	if (intval($this->base->db->select($sql, 'field', $bind_params)) < 1) {
		return false;
	} else {
		return true;
	}
}

/**
 * Tests if given secret matches the secret stored in the database. Takes
 * user's secret as first argument and user's email address as second
 * argument. Returns bool.
 *
 * @throws User_UserException
 * @param string Secret
 * @param string E-mail address
 * @return bool 
 */
public function testSecret ($input_secret, $input_email)
{
	// input test
	if (empty($input_secret)) {
		throw new User_UserException('Input for parameter input_secret is not expected to be empty');
	}
	if (empty($input_email)) {
		throw new User_UserException('Input for parameter input_email is not expected to be empty');
	}	
	
	// get user's encrypted secret
	$sql = "
		SELECT
			`secret`
		FROM
			".OAK_DB_USER_USERS." AS `user_users`
		WHERE
			`user_users`.`email` = :email
	";
	
	// prepare bind params
	$bind_params = array(
		'email' => $input_email
	);
	
	// get secret from database
	$secret = $this->base->db->select($sql, 'field', $bind_params);
	
	// if the secret is empty, return false
	if (empty($secret)) {
		return false;
	}
	
	// test password
	if (crypt($input_secret, $secret) === $secret) {
		return true;
	}
	
	return false;
}

/**
 * Test if user has access to given area/component/action. Takes the
 * area name as first argument, the component name as second argument
 * and the action name as third argument. Returns bool.
 * 
 * @throws User_UserException
 * @param string Area name
 * @param string Component name
 * @param string Action name
 * @return bool
 */
public function userCheckAccess($area = null, $component = null, $action = null) 
{
	// if every parameter is null, we can simply return true
	if (is_null($area) && is_null($component) && is_null($action)) {
		return true;
	}
	
	// build right components array
	$action_components = array();
	if (!empty($area) && !is_null(Base_Cnc::filterRequest($area, OAK_REGEX_ALPHANUMERIC))) {
		$action_components[] = strtoupper($area);
	}
	if (!empty($component) && !is_null(Base_Cnc::filterRequest($component, OAK_REGEX_ALPHANUMERIC))) {
		$action_components[] = strtoupper($component);
	}
	if (!empty($action) && !is_null(Base_Cnc::filterRequest($action, OAK_REGEX_ALPHANUMERIC))) {
		$action_components[] = strtoupper($action);
	}
		
	// turn components array into string
	$action_string = implode('_', $action_components);
	
	if (OAK_CURRENT_AREA == "ADMIN") {
		// make sure that there's a rights array
		if (empty($_SESSION['admin']['rights']) || !is_array($_SESSION['admin']['rights'])) {
			throw new User_UserException("No rights array found");
		}
	
		// look for the right string in the list of user rights
		foreach ($_SESSION['admin']['rights'] as $_right_id => $_right) {
			if ($_right === $action_string) {
				return true;
			}
		}
	} elseif (OAK_CURRENT_AREA == "PUBLIC") {
		// make sure that there's a rights array
		if (empty($_SESSION['public_area']['rights']) || !is_array($_SESSION['public_area']['rights'])) {
			throw new User_UserException("No rights array found");
		}
		
		// look for the right string in the list of user rights
		foreach ($_SESSION['public_area']['rights'] as $_right_id => $_right) {
			if ($_right === $action_string) {
				return true;
			}
		}
	}
	
	return false;
}

/**
 * Tests whether user belongs to current project. Takes user
 * id as first argument. Returns bool.
 *
 * @throws User_UserException
 * @param int User id
 * @return bool
 */
public function userBelongsToCurrentProject ($user)
{
	// access check
	if (!oak_check_access('User', 'User', 'Use')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($user) || !is_numeric($user)) {
		throw new User_UserException('Input for parameter user is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".OAK_DB_USER_USERS." AS `user_users`
		JOIN
			".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
		  ON
			`user_users`.`id` = `user_users2application_projects`.`user`
		WHERE
			`user_users`.`id` = :user
		  AND
			`user_users2application_projects`.`project` = :project
		  AND
			`user_users2application_projects`.`active` = '1'
	";
	
	// prepare bind params
	$bind_params = array(
		'user' => (int)$user,
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
 * Tests whether user belongs to current user or not. Takes
 * the user id as first argument. Returns bool.
 *
 * @throws User_GroupException
 * @param int Group id
 * @return bool
 */
public function userBelongsToCurrentUser ($user)
{
	// access check
	if (!oak_check_access('User', 'User', 'Use')) {
		throw new User_GroupException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($user) || !is_numeric($user)) {
		throw new User_GroupException('Input for parameter user is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->userBelongsToCurrentProject($user)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(OAK_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class User_UserException extends Exception { }

?>