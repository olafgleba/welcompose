<?php

/**
 * Project: Oak
 * File: pagetype.class.php
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

class Content_Pagetype {
	
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
 * Singleton. Returns instance of the Content_Pagetype object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Content_Pagetype::$instance == null) {
		Content_Pagetype::$instance = new Content_Pagetype(); 
	}
	return Content_Pagetype::$instance;
}

/**
 * Adds page type to the page type table. Takes a field=>value array with
 * page type data as first argument. Returns insert id. 
 * 
 * @throws Content_PagetypeException
 * @param array Row data
 * @return int PageType id
 */
public function addPageType ($sqlData)
{
	// access check
	if (!oak_check_access('pagetype', 'manage')) {
		throw new Content_PagetypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Content_PagetypeException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the page type will be assigned to the right project
	$sqlData['project'] = OAK_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(OAK_DB_CONTENT_PAGE_TYPES, $sqlData);
	
	// test if page type belongs to current user/project
	if (!$this->pageTypeBelongsToCurrentUser($insert_id)) {
		throw new Content_PagetypeException('Page type does not belong to current user or project');
	}
	
	return $insert_id;
}

/**
 * Updates page type. Takes the page type id as first argument, a
 * field=>value array with the new page type data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_PagetypeException
 * @param int Page type id
 * @param array Row data
 * @return int Affected rows
*/
public function updatePageType ($id, $sqlData)
{
	// access check
	if (!oak_check_access('pagetype', 'manage')) {
		throw new Content_PagetypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_PagetypeException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_PagetypeException('Input for parameter sqlData is not an array');	
	}
	
	// test if page type belongs to current user/project
	if (!$this->pageTypeBelongsToCurrentUser($id)) {
		throw new Content_PagetypeException('Page type does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project AND `editable` = '1' ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(OAK_DB_CONTENT_PAGE_TYPES, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes page type from the page type table. Takes the page type id
 * as first argument. Returns amount of affected rows
 * 
 * @throws Content_PagetypeException
 * @param int Page type id
 * @return int Amount of affected rows
 */
public function deletePageType ($id)
{
	// access check
	if (!oak_check_access('pagetype', 'manage')) {
		throw new Content_PagetypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_PagetypeException('Input for parameter id is not numeric');
	}
	
	// test if page type belongs to current user/project
	if (!$this->pageTypeBelongsToCurrentUser($id)) {
		throw new Content_PagetypeException('Page type does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project AND `editable` = '1' ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_CONTENT_PAGE_TYPES, $where, $bind_params);
}

/**
 * Selects one page type. Takes the page type id as first argument.
 * Returns array with page type information.
 * 
 * @throws Content_PagetypeException
 * @param int Page type id
 * @return array
 */
public function selectPageType ($id)
{
	// access check
	if (!oak_check_access('pagetype', 'use')) {
		throw new Content_PagetypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_PagetypeException('Input for parameter id is not numeric');
	}
	
	// prepare query
	$sql = "
		SELECT 
			`content_page_types`.`id` AS `id`,
			`content_page_types`.`project` AS `project`,
			`content_page_types`.`name` AS `name`,
			`content_page_types`.`editable` AS `editable`
		FROM
			".OAK_DB_CONTENT_PAGE_TYPES." AS `content_page_types`
		WHERE 
			`content_page_types`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	// prepare where clauses
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `content_page_types`.`id` = :id ";
		$bind_params['id'] = (int)$id;
	}
	
	// add limits
	$sql .= ' LIMIT 1';
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more page types. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Content_PagetypeException
 * @param array Select params
 * @return array
 */
public function selectPageTypes ($params = array())
{
	// access check
	if (!oak_check_access('pagetype', 'use')) {
		throw new Content_PagetypeException("You are not allowed to perform this action");
	}
	
	// define some vars
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_PagetypeException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_PagetypeException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`content_page_types`.`id` AS `id`,
			`content_page_types`.`project` AS `project`,
			`content_page_types`.`name` AS `name`,
			`content_page_types`.`editable` AS `editable`
		FROM
			".OAK_DB_CONTENT_PAGE_TYPES." AS `content_page_types`
		WHERE 
			`content_page_types`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	// add sorting
	$sql .= " ORDER BY `content_page_types`.`name` ";
	
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
 * Tests given page type name for uniqueness. Takes the page type
 * name as first argument and an optional page type id as second
 * argument. If the page type id is given, this page type won't be
 * considered when checking for uniqueness (useful for updates).
 * Returns boolean true if page type name is unique.
 *
 * @throws Content_PagetypeException
 * @param string Page type name
 * @param int Page type id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!oak_check_access('pagetype', 'use')) {
		throw new Content_PagetypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Content_PagetypeException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Content_PagetypeException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Content_PagetypeException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_CONTENT_PAGE_TYPES." AS `content_page_types`
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

/**
 * Tests whether given page type belongs to current project. Takes the
 * page type id as first argument. Returns bool.
 *
 * @throws Content_PagetypeException
 * @param int Page type id
 * @return int bool
 */
public function pageTypeBelongsToCurrentProject ($page_type)
{
	// access check
	if (!oak_check_access('pagetype', 'use')) {
		throw new Content_PagetypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($page_type) || !is_numeric($page_type)) {
		throw new Content_PagetypeException('Input for parameter page_type is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_CONTENT_PAGE_TYPES." AS `content_page_types`
		WHERE
			`content_page_types`.`id` = :page_type
		  AND
			`content_page_types`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'page_type' => (int)$page_type,
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
 * Test whether page type belongs to current user or not. Takes
 * the page type id as first argument. Returns bool.
 *
 * @throws Content_PagetypeException
 * @param int Page type id
 * @return bool
 */
public function pageTypeBelongsToCurrentUser ($page_type)
{
	// access check
	if (!oak_check_access('pagetype', 'use')) {
		throw new Content_PagetypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($page_type) || !is_numeric($page_type)) {
		throw new Content_PagetypeException('Input for parameter page_type is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->pageTypeBelongsToCurrentProject($page_type)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(OAK_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Content_PagetypeException extends Exception { }

?>