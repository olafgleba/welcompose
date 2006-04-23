<?php

/**
 * Project: Oak
 * File: page.class.php
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

class Content_Page {
	
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
 * Singleton. Returns instance of the Content_Page object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Content_Page::$instance == null) {
		Content_Page::$instance = new Content_Page(); 
	}
	return Content_Page::$instance;
}

/**
 * Adds page to the page table. Takes a field=>value array with
 * page data as first argument. Returns insert id. 
 * 
 * @throws Content_PageException
 * @param array Row data
 * @return int Page id
 */
public function addPage ($sqlData)
{
	// input check
	if (!is_array($sqlData)) {
		throw new Content_PageException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	return $this->base->db->insert(OAK_DB_CONTENT_PAGES, $sqlData);
}

/**
 * Updates page. Takes the page id as first argument, a
 * field=>value array with the new page data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_PageException
 * @param int Page id
 * @param array Row data
 * @return int Affected rows
*/
public function updatePage ($id, $sqlData)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_PageException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_PageException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(OAK_DB_CONTENT_PAGES, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes page from the page table. Takes the page id
 * as first argument. Returns amount of affected rows
 * 
 * @throws Content_PageException
 * @param int Page id
 * @return int Amount of affected rows
 */
public function deletePage ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_PageException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_CONTENT_PAGES, $where, $bind_params);
}

/**
 * Selects one page. Takes the page id as first argument.
 * Returns array with page information.
 * 
 * @throws Content_PageException
 * @param int Page id
 * @return array
 */
public function selectPage ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_PageException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`content_pages`.`id` AS `id`,
			`content_nodes`.`navigation` AS `navigation`,
			`content_nodes`.`root_node` AS `root_node`,
			`content_nodes`.`parent` AS `parent`,
			`content_nodes`.`lft` AS `lft`,
			`content_nodes`.`rgt` AS `rgt`,
			`content_nodes`.`level` AS `level`,
			`content_nodes`.`sorting` AS `sorting`,
			`content_pages`.`node` AS `node`,
			`content_pages`.`type` AS `type`,
			`content_pages`.`template_set` AS `template_set`,
			`content_pages`.`name` AS `name`,
			`content_pages`.`name_url` AS `name_url`,
			`content_pages`.`protect` AS `protect`
		FROM
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		LEFT JOIN
			".OAK_DB_CONTENT_NODES." AS `content_nodes`
		  ON
			`content_pages`.`node` = `content_nodes`.`id`
		WHERE 
			1
	";
	
	// prepare where clauses
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `content_pages`.`id` = :id ";
		$bind_params['id'] = (int)$id;
	}
	
	// add limits
	$sql .= ' LIMIT 1';
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more pages. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>navigation, int, optional: Navigation id</li>
 * <li>root_node, int, optional: Root node id</li>
 * <li>parent, int, optional: Parent node id</li>
 * <li>level, int, optional: Level count</li>
 * <li>sorting, int, optional: Sorting count</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Content_PageException
 * @param array Select params
 * @return array
 */
public function selectPages ($params = array())
{
	// define some vars
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_PageException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'navigation':
			case 'root_node':
			case 'parent':
			case 'level':
			case 'sorting':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_PageException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`content_pages`.`id` AS `id`,
			`content_nodes`.`navigation` AS `navigation`,
			`content_nodes`.`root_node` AS `root_node`,
			`content_nodes`.`parent` AS `parent`,
			`content_nodes`.`lft` AS `lft`,
			`content_nodes`.`rgt` AS `rgt`,
			`content_nodes`.`level` AS `level`,
			`content_nodes`.`sorting` AS `sorting`,
			`content_pages`.`node` AS `node`,
			`content_pages`.`type` AS `type`,
			`content_pages`.`template_set` AS `template_set`,
			`content_pages`.`name` AS `name`,
			`content_pages`.`name_url` AS `name_url`,
			`content_pages`.`protect` AS `protect`
		FROM
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		LEFT JOIN
			".OAK_DB_CONTENT_NODES." AS `content_nodes`
		  ON
			`content_pages`.`node` = `content_nodes`.`id`
		WHERE 
			1
	";
	
	// add where clauses
	if (!empty($navigation) && is_numeric($navigation)) {
		$sql .= " AND `content_nodes`.`navigation` = :navigation ";
		$bind_params['navigation'] = $navigation;
	}
	if (!empty($root_node) && is_numeric($root_node)) {
		$sql .= " AND `content_nodes`.`root_node` = :root_node ";
		$bind_params['root_node'] = $root_node;
	}
	if (!empty($parent) && is_numeric($parent)) {
		$sql .= " AND `content_nodes`.`parent` = :parent ";
		$bind_params['parent'] = $parent;
	}
	if (!empty($level) && is_numeric($level)) {
		$sql .= " AND `content_nodes`.`level` = :level ";
		$bind_params['level'] = $level;
	}
	if (!empty($sorting) && is_numeric($sorting)) {
		$sql .= " AND `content_nodes`.`sorting` = :sorting ";
		$bind_params['sorting'] = $sorting;
	}
	
	// add sorting
	$sql .= " ORDER BY `content_nodes`.`sorting`, `content_nodes`.`lft` ";
	
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

class Content_PageException extends Exception { }

?>