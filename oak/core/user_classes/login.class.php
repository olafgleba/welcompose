<?php

/**
 * Project: Oak
 * File: login.class.php
 * 
 * Copylogin (c) 2006 sopic GmbH
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
 * @copylogin 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/apache2.0.php Apache License, Version 2.0
 */

class User_Login {
	
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
 * Singleton. Returns instance of the User_Login object.
 * 
 * @return object
 */
public function instance()
{ 
	if (User_Login::$instance == null) {
		User_Login::$instance = new User_Login(); 
	}
	return User_Login::$instance;
}

/**
 * Proceeds user login. Compares supplied email address and secret
 * with the email addresses and secrets stored in database. If the
 * the combination of email address and secret is found, the user id
 * will be stored in the current session.
 *
 * Takes the email address of the user to login as first argument,
 * its secret as second argument. Returns bool.
 * 
 * @throws User_LoginException
 * @param string E-mail address
 * @param string Secret
 * @return bool
 */
public function logIntoAdmin ($input_email, $input_secret)
{
	// input check
	if (empty($input_secret)) {
		throw new User_LoginException('Input for parameter input_secret is not expected to be empty');
	}
	if (empty($input_email)) {
		throw new User_LoginException('Input for parameter input_email is not expected to be empty');
	}
	
	// let's see if email/secret matches the email/secret stored in database
	if (!$this->testSecret($input_secret, $input_email)) {
		throw new User_LoginException("Invalid login");
	}
	
	// as we know now that the user is authenticated, we need to get it's id
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
	$user = $this->base->db->select($sql, 'field', $bind_params);
	
	// the next step is to look for projects where the user is active and
	// an author.
	$sql = "
		SELECT
			`application_projects`.`id`
		FROM
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		JOIN
			".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
		  ON
			`application_projects`.`id` = `user_users2application_projects`.`project`
		WHERE
			`user_users2application_projects`.`user` = :user
		  AND
			`user_users2application_projects`.`active` = '1'
		  AND
			`user_users2application_projects`.`author` = '1'
	";
	
	// prepare bind params
	$bind_params = array(
		'user' => $user
	);
	
	// get user id from database
	$possible_projects = $this->base->db->select($sql, 'multi', $bind_params);
	
	// if the list of possible projects is empty, we have to skip here
	if (empty($possible_projects)) {
		throw new User_LoginException("No usable project found");
	}
	
	// let's see if the user already used the admin interface and has a
	// cookie with a project id in it
	$COOKIE = load('utility:cookie');
	$cookie_project = $COOKIE->adminGetCurrentProject();
	
	// let's see if we can find a matching project in the list of possible
	// projects. if we find one, we can carry on using it.
	$project = null;
	if (!is_null($cookie_project)) {
		foreach ($possible_projects as $_project) {
			if ($_project['id'] == $cookie_project) {
				$project = $cookie_project;
			}
		}
	}
	
	// if there is no cookie project or it isn't valid, we use the
	// first one from the list of possible projects.
	if (is_null($project)) {
		foreach ($possible_projects as $_project) {
			if (!empty($_project['id']) && is_numeric($_project['id'])) {
				$project = $_project['id'];
				break;
			}
		}
	}
	
	// if there's still no current project, we have to stop here.
	if (is_null($project)) {
		throw new User_LoginException("No usable project found");
	}
	
	// ok, we have now the user id and the current project id. now we
	// need to get its group and its access rights. let's start with
	// its user group.
	$sql = "
		SELECT
			`user_groups`.`id`
		FROM
			".OAK_DB_USER_GROUPS." AS `user_groups`
		JOIN
			".OAK_DB_USER_USERS2USER_GROUPS." AS `user_users2user_groups`
		  ON
			`user_groups`.`id` = `user_users2user_groups`.`group`
		WHERE
			`user_groups`.`project` = :project
		  AND
			`user_users2user_groups`.`user` = :user
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => $project,
		'user' => $user
	);
	
	// get possible group
	$possible_group = $this->base->db->select($sql, 'row', $bind_params);
	
	// make sure that we got a group
	if (empty($possible_group) || empty($possible_group['id']) || !is_numeric($possible_group['id'])) {
		throw new User_LoginException("No usable group found");
	}
	$group = $possible_group['id'];
	
	// get access rights
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
		WHERE
			`user_groups2user_rights`.`group` = :group
		  AND
			`user_rights`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'group' => $group,
		'project' => $project
	);
	
	// execute query
	$access_rights = $this->base->db->select($sql, 'multi', $bind_params);
	
	// now it's time to save everything to the session/cookie
	$_SESSION['admin']['user'] = $user;
	$_SESSION['admin']['group'] = $group;
	$_SESSION['admin']['project'] = $project;
	$_SESSION['admin']['projects'] = $possible_projects;
	$_SESSION['admin']['rights'] = array();
	
	foreach ($access_rights as $_right) {
		$_SESSION['admin']['rights'][(int)$_right['id']] = $_right['name'];
	}
	
	// save current project id to cookie
	$COOKIE->adminSwitchCurrentProject($project);
	
	// we're done
	return true;
}

/**
 * Tests if current visitor is logged into the admin area using
 * the user id supplied by the current session. Returns bool.
 * 
 * @return bool
 */
public function loggedIntoAdmin ()
{
	// import user and project id from session
	$user_id = Base_Cnc::filterRequest($_SESSION['admin']['user'], OAK_REGEX_NUMERIC);
	$project_id = Base_Cnc::filterRequest($_SESSION['admin']['project'], OAK_REGEX_NUMERIC);
	
	if (is_null($user_id)) {
		return false;
	}
	
	// load user class
	$USER = load('User:User');
	
	// make sure that the user exists
	if (!$USER->userExists($user_id)) {
		return false;
	}
	
	// make sure that the user is active
	if (!$USER->userIsActive($user_id, $project_id)) {
		return false;
	}
	
	// make sure that the user is an author
	if (!$USER->userIsAuthor($user_id, $project_id)) {
		return false;
	}
	
	return true;
}

/**
 * Allows change of current project in admin area without previous
 * logout. Takes the id of the new project as first argument. Returns
 * bool.
 * 
 * @throws User_LoginException
 * @param int Project id
 * @return bool
 */
public function switchAdminProject ($new_project)
{
	// test if user is logged into admin area
	if (!$this->loggedIntoAdmin()) {
		throw new User_LoginException("User is not logged into admin area");
	}
	
	// get user id from session
	$user = Base_Cnc::filterRequest($_SESSION['admin']['user'], OAK_REGEX_NUMERIC);
	
	// the first step is to look for projects where the user is active and
	// an author.
	$sql = "
		SELECT
			`application_projects`.`id`
		FROM
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		JOIN
			".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
		  ON
			`application_projects`.`id` = `user_users2application_projects`.`project`
		WHERE
			`user_users2application_projects`.`user` = :user
		  AND
			`user_users2application_projects`.`active` = '1'
		  AND
			`user_users2application_projects`.`author` = '1'
	";
	
	// prepare bind params
	$bind_params = array(
		'user' => $user
	);
	
	// get list of possible projects from database
	$possible_projects = $this->base->db->select($sql, 'multi', $bind_params);
	
	// if the list of possible projects is empty, we have to skip here
	if (empty($possible_projects)) {
		throw new User_LoginException("No usable project found");
	}
	
	// let's see if the project where the user likes to switch to is in the
	// list of possible projects
	$project = null;
	foreach ($possible_projects as $_project) {
		if ($new_project == $_project['id'] && !empty($_project['id']) && is_numeric($_project['id'])) {
			$project = $_project['id'];
			break;
		}
	}
	
	// if there's no current project, we have to stop here.
	if (is_null($project)) {
		throw new User_LoginException("No usable project found");
	}
	
	// ok, we have now the user id and the current project id. now we
	// need to get its group and its access rights. let's start with
	// its user group.
	$sql = "
		SELECT
			`user_groups`.`id`
		FROM
			".OAK_DB_USER_GROUPS." AS `user_groups`
		JOIN
			".OAK_DB_USER_USERS2USER_GROUPS." AS `user_users2user_groups`
		  ON
			`user_groups`.`id` = `user_users2user_groups`.`group`
		WHERE
			`user_groups`.`project` = :project
		  AND
			`user_users2user_groups`.`user` = :user
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => $project,
		'user' => $user
	);
	
	// get possible group
	$possible_group = $this->base->db->select($sql, 'row', $bind_params);
	
	// make sure that we got a group
	if (empty($possible_group) || empty($possible_group['id']) || !is_numeric($possible_group['id'])) {
		throw new User_LoginException("No usable group found");
	}
	$group = $possible_group['id'];
	
	// get access rights
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
		WHERE
			`user_groups2user_rights`.`group` = :group
		  AND
			`user_rights`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'group' => $group,
		'project' => $project
	);
	
	// execute query
	$access_rights = $this->base->db->select($sql, 'multi', $bind_params);
	
	// now it's time to save everything to the session/cookie
	$_SESSION['admin']['group'] = $group;
	$_SESSION['admin']['project'] = $project;
	$_SESSION['admin']['projects'] = $possible_projects;
	$_SESSION['admin']['rights'] = array();
	
	foreach ($access_rights as $_right) {
		$_SESSION['admin']['rights'][(int)$_right['id']] = $_right['name'];
	}
	
	// save current project id to cookie
	$COOKIE = load('utility:cookie');
	$COOKIE->adminSwitchCurrentProject($project);
	
	// we're done
	return true;
}

/**
 * Terminates admin session.
 *
 * @return true
 */
public function logOutFromAdmin ()
{
	$_SESSION['admin'] = array();
	return true;
}

/**
 * Performs user login into public area. Compares supplied email address
 * and secret with the email addresses and secrets stored in database. If
 * the the combination of email address and secret is found, the user id
 * will be stored in the current session.
 *
 * Takes the email address of the user to login as first argument,
 * its secret as second argument. Returns bool.
 * 
 * @throws User_LoginException
 * @param string E-mail address
 * @param string Secret
 * @return bool
 */
public function logIntoPublicArea ($input_email, $input_secret)
{
	// input check
	if (empty($input_secret)) {
		throw new User_LoginException('Input for parameter input_secret is not expected to be empty');
	}
	if (empty($input_email)) {
		throw new User_LoginException('Input for parameter input_email is not expected to be empty');
	}
	
	// let's see if email/secret matches the email/secret stored in database
	if (!$this->testSecret($input_secret, $input_email)) {
		throw new User_LoginException("Invalid login");
	}
	
	// as we know now that the user is authenticated, we need to get it's id
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
	$user = $this->base->db->select($sql, 'field', $bind_params);
	
	// the next step is to look for projects where the user is active
	$sql = "
		SELECT
			`application_projects`.`id`
		FROM
			".OAK_DB_APPLICATION_PROJECTS." AS `application_projects`
		JOIN
			".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
		  ON
			`application_projects`.`id` = `user_users2application_projects`.`project`
		WHERE
			`user_users2application_projects`.`user` = :user
		  AND
			`user_users2application_projects`.`active` = '1'
	";
	
	// prepare bind params
	$bind_params = array(
		'user' => $user
	);
	
	// get user id from database
	$possible_projects = $this->base->db->select($sql, 'multi', $bind_params);
	
	// if the list of possible projects is empty, we have to skip here
	if (empty($possible_projects)) {
		throw new User_LoginException("No usable project found");
	}
	
	// let's see if the project where the user likes to switch to is in the
	// list of possible projects
	$project = null;
	foreach ($possible_projects as $_project) {
		if (OAK_CURRENT_PROJECT == $_project['id'] && !empty($_project['id']) && is_numeric($_project['id'])) {
			$project = $_project['id'];
			break;
		}
	}
	
	// if there's no current project, we have to stop here.
	if (is_null($project)) {
		throw new User_LoginException("No usable project found");
	}
	
	// ok, we have now the user id and the current project id. now we
	// need to get its group and its access rights. let's start with
	// its user group.
	$sql = "
		SELECT
			`user_groups`.`id`
		FROM
			".OAK_DB_USER_GROUPS." AS `user_groups`
		JOIN
			".OAK_DB_USER_USERS2USER_GROUPS." AS `user_users2user_groups`
		  ON
			`user_groups`.`id` = `user_users2user_groups`.`group`
		WHERE
			`user_groups`.`project` = :project
		  AND
			`user_users2user_groups`.`user` = :user
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => $project,
		'user' => $user
	);
	
	// get possible group
	$possible_group = $this->base->db->select($sql, 'row', $bind_params);
	
	// make sure that we got a group
	if (empty($possible_group) || empty($possible_group['id']) || !is_numeric($possible_group['id'])) {
		throw new User_LoginException("No usable group found");
	}
	$group = $possible_group['id'];
	
	// get access rights
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
		WHERE
			`user_groups2user_rights`.`group` = :group
		  AND
			`user_rights`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'group' => $group,
		'project' => $project
	);
	
	// execute query
	$access_rights = $this->base->db->select($sql, 'multi', $bind_params);
	
	// now it's time to save everything to the session/cookie
	$_SESSION['public_area'][OAK_CURRENT_PROJECT]['user'] = $user;
	$_SESSION['public_area'][OAK_CURRENT_PROJECT]['anon'] = false;
	$_SESSION['public_area'][OAK_CURRENT_PROJECT]['group'] = $group;
	$_SESSION['public_area'][OAK_CURRENT_PROJECT]['rights'] = array();
	
	foreach ($access_rights as $_right) {
		$_SESSION['public_area'][OAK_CURRENT_PROJECT]['rights'][(int)$_right['id']] = $_right['name'];
	}
	
	// we're done
	return true;
}

/**
 * Performs login into public area as anonymous user of the
 * respective current project. Returns bool.
 * 
 * @throws User_LoginException
 */
public function logIntoPublicAreaAsAnonymous ()
{
	// get anonymous user for current project
	$sql = "
		SELECT
			`user_users`.`id`
		FROM
			".OAK_DB_USER_USERS." AS `user_users`
		JOIN
			".OAK_DB_USER_USERS2APPLICATION_PROJECTS." AS `user_users2application_projects`
		  ON
			`user_users`.`id` = `user_users2application_projects`.`user`
		WHERE
			`user_users`.`email` = 'OAK_ANONYMOUS'
		  AND
			`user_users2application_projects`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query
	$possible_user = $this->base->db->select($sql, 'field', $bind_params);
	
	// make sure that we've found a usable anonymous user
	if (empty($possible_user) || !is_numeric($possible_user)) {
		throw new User_LoginException("No usable user found");
	}
	$user = $possible_user;
	
	// ok, we have now the user id and the current project id. now we
	// need to get its group and its access rights. let's start with
	// its user group.
	$sql = "
		SELECT
			`user_groups`.`id`
		FROM
			".OAK_DB_USER_GROUPS." AS `user_groups`
		JOIN
			".OAK_DB_USER_USERS2USER_GROUPS." AS `user_users2user_groups`
		  ON
			`user_groups`.`id` = `user_users2user_groups`.`group`
		WHERE
			`user_groups`.`project` = :project
		  AND
			`user_users2user_groups`.`user` = :user
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT,
		'user' => $user
	);
	
	// get possible group
	$possible_group = $this->base->db->select($sql, 'row', $bind_params);
	
	// make sure that we got a group
	if (empty($possible_group) || empty($possible_group['id']) || !is_numeric($possible_group['id'])) {
		throw new User_LoginException("No usable group found");
	}
	$group = $possible_group['id'];
	
	// get access rights
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
	
	// execute query
	$access_rights = $this->base->db->select($sql, 'multi', $bind_params);
	
	// initialize session container
	if (!array_key_exists('public_area', $_SESSION) || !is_array($_SESSION['public_area'])) {
		$_SESSION['public_area'] = array();
	}
	$_SESSION['public_area'][OAK_CURRENT_PROJECT] = array();
	
	// now it's time to save everything to the session/cookie
	$_SESSION['public_area'][OAK_CURRENT_PROJECT]['user'] = $user;
	$_SESSION['public_area'][OAK_CURRENT_PROJECT]['anon'] = true;
	$_SESSION['public_area'][OAK_CURRENT_PROJECT]['group'] = $group;
	$_SESSION['public_area'][OAK_CURRENT_PROJECT]['rights'] = array();
	
	foreach ($access_rights as $_right) {
		$_SESSION['public_area'][OAK_CURRENT_PROJECT]['rights'][(int)$_right['id']] = $_right['name'];
	}
	
	// we're done
	return true;
}

/**
 * Tests if given secret matches the secret stored in the database. Takes
 * user's secret as first argument and user's email address as second
 * argument. Returns bool.
 *
 * @throws User_LoginException
 * @param string Secret
 * @param string E-mail address
 * @return bool 
 */
public function testSecret ($input_secret, $input_email)
{
	// input test
	if (empty($input_secret)) {
		throw new User_LoginException('Input for parameter input_secret is not expected to be empty');
	}
	if (empty($input_email)) {
		throw new User_LoginException('Input for parameter input_email is not expected to be empty');
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

// end of class
}

class User_LoginException extends Exception { }

?>