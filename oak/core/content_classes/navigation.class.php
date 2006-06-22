<?php

/**
 * Project: Oak
 * File: navigation.class.php
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

class Content_Navigation {
	
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
 * Singleton. Returns instance of the Content_Navigation object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Content_Navigation::$instance == null) {
		Content_Navigation::$instance = new Content_Navigation(); 
	}
	return Content_Navigation::$instance;
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
	if (!is_array($sqlData)) {
		throw new Content_NavigationException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the navigation will be assigned to the right project
	$sqlData['project'] = OAK_CURRENT_PROJECT;
	
	// insert row
	return $this->base->db->insert(OAK_DB_CONTENT_NAVIGATIONS, $sqlData);
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
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_NavigationException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_NavigationException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(OAK_DB_CONTENT_NAVIGATIONS, $sqlData,
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
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_NavigationException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_CONTENT_NAVIGATIONS, $where, $bind_params);
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
			".OAK_DB_CONTENT_NAVIGATIONS." AS `content_navigations`
		WHERE 
			`content_navigations`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
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
			".OAK_DB_CONTENT_NAVIGATIONS." AS `content_navigations`
		WHERE 
			`content_navigations`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
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
 * @param string Navigation name
 * @param int Navigation id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
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
			".OAK_DB_CONTENT_NAVIGATIONS." AS `content_navigations`
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

class Content_NavigationException extends Exception { }

?>