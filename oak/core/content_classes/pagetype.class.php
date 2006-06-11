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
	if (!is_array($sqlData)) {
		throw new Content_PagetypeException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the page type will be assigned to the right project
	$sqlData['project'] = OAK_CURRENT_PROJECT;
	
	// insert row
	return $this->base->db->insert(OAK_DB_CONTENT_PAGE_TYPES, $sqlData);
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
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_PagetypeException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_PagetypeException('Input for parameter sqlData is not an array');	
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
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_PagetypeException('Input for parameter id is not numeric');
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

// end of class
}

class Content_PagetypeException extends Exception { }

?>