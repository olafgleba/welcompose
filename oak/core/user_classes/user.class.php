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
 * $Id: user.class.php 2 2006-03-20 11:43:20Z andreas $
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
		throw new User_UserException('Input for parameter id is not an array');
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
			`User_Users`.`id` AS `id`,
			`User_Users`.`name` AS `name`,
			`User_Users`.`email` AS `email`,
			`User_Users`.`homepage` AS `homepage`,
			`User_Users`.`pwd` AS `pwd`,
			`User_Users`.`public_email` AS `public_email`,
			`User_Users`.`public_profile` AS `public_profile`,
			`User_Users`.`author` AS `author`,
			`User_Users`.`date_modified` AS `date_modified`,
			`User_Users`.`date_added` AS `date_added`
		FROM
			".OAK_DB_USER_USERS." AS `User_Users`
		WHERE 
			1
	";
	
	// prepare where clauses
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `User_Users`.`id` = :id ";
		$bind_params['id'] = (int)$id;
	}
	
	// add limits
	$sql .= ' LIMIT 1';
	
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
		'NAME' => '`User_Users`.`name`',
		'DATE_ADDED' => '`User_Users`.`date_added`'
	);
	
	// prepare query
	$sql = "
		SELECT 
			`User_Users`.`id` AS `id`,
			`User_Users`.`name` AS `name`,
			`User_Users`.`email` AS `email`,
			`User_Users`.`homepage` AS `homepage`,
			`User_Users`.`pwd` AS `pwd`,
			`User_Users`.`public_email` AS `public_email`,
			`User_Users`.`public_profile` AS `public_profile`,
			`User_Users`.`author` AS `author`,
			`User_Users`.`date_modified` AS `date_modified`,
			`User_Users`.`date_added` AS `date_added`
		FROM
			".OAK_DB_USER_USERS." AS `User_Users`
		WHERE 
			1
	";
	
	// prepare where clauses
	if (!empty($group) && is_numeric($group)) {
		$sql .= " AND `application_groups`.`id` = :group ";
		$bind_params['group'] = (int)$group;
	}
	if (!empty($name) && is_scalar($name)) {
		$sql .= " AND `User_Users`.`name` = :name ";
		$bind_params['name'] = (int)$name;
	}
	if (!empty($author) && is_numeric($author)) {
		$sql .= " AND `User_Users`.`author` = :author ";
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

public function initUserAdmin ()
{
	define('OAK_CURRENT_USER', 1);
	
	return 1;
}

// end of class
}

class User_UserException extends Exception { }

?>