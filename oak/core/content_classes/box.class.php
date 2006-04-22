<?php

/**
 * Project: Oak
 * File: box.class.php
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
 * $Id: box.class.php 2 2006-03-20 11:43:20Z andreas $
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/apache2.0.php Apache License, Version 2.0
 */

class Content_Box {
	
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
 * Singleton. Returns instance of the Content_Box object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Content_Box::$instance == null) {
		Content_Box::$instance = new Content_Box(); 
	}
	return Content_Box::$instance;
}

/**
 * Adds box to the box table. Takes a field=>value array with
 * box data as first argument. Returns insert id. 
 * 
 * @throws Content_BoxException
 * @param array Row data
 * @return int Box id
 */
public function addBox ($sqlData)
{
	if (!is_array($sqlData)) {
		throw new Content_BoxException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	return $this->base->db->insert(OAK_DB_CONTENT_BOXES, $sqlData);
}

/**
 * Updates box. Takes the box id as first argument, a
 * field=>value array with the new box data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_BoxException
 * @param int Box id
 * @param array Row data
 * @return int Affected rows
*/
public function updateBox ($id, $sqlData)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_BoxException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_BoxException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(OAK_DB_CONTENT_BOXES, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes box from the box table. Takes the box id
 * as first argument. Returns amount of affected rows.
 * 
 * @throws Content_BoxException
 * @param int Box id
 * @return int Amount of affected rows
 */
public function deleteBox ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_BoxException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_CONTENT_BOXES, $where, $bind_params);
}

/**
 * Selects one box. Takes the box id as first argument.
 * Returns array with box information.
 * 
 * @throws Content_BoxException
 * @param int Box id
 * @return array
 */
public function selectBox ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_BoxException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`content_boxes`.`id` AS `id`,
			`content_boxes`.`page` AS `page`,
			`content_boxes`.`name` AS `name`,
			`content_boxes`.`content_raw` AS `content_raw`,
			`content_boxes`.`content` AS `content`,
			`content_boxes`.`date_modified` AS `date_modified`,
			`content_boxes`.`date_added` AS `date_added`,
			`content_pages`.`id` AS `page_id`,
			`content_pages`.`navigation` AS `page_navigation`,
			`content_pages`.`root_node` AS `page_root_node`,
			`content_pages`.`parent` AS `page_parent`,
			`content_pages`.`level` AS `page_level`,
			`content_pages`.`sorting` AS `page_sorting`,
			`content_pages`.`type` AS `page_type`,
			`content_pages`.`template_set` AS `page_template_set`,
			`content_pages`.`name` AS `page_name`,
			`content_pages`.`name_url` AS `page_name_url`,
			`content_pages`.`protect` AS `page_protect`
		FROM
			".OAK_DB_CONTENT_BOXES." AS `content_boxes`
		LEFT JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_boxes`.`page` = `content_pages`.`id`
		WHERE
			1
	";
	
	// prepare where clauses
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `content_boxes`.`id` = :id ";
		$bind_params['id'] = (int)$id;
	}
	
	// add limits
	$sql .= ' LIMIT 1';
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more boxes. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>page, int, optional: Page id</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Content_BoxException
 * @param array Select params
 * @return array
 */
public function selectBoxes ($params = array())
{
	// define some vars
	$page = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_BoxException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'page':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_BoxException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`content_boxes`.`id` AS `id`,
			`content_boxes`.`page` AS `page`,
			`content_boxes`.`name` AS `name`,
			`content_boxes`.`content_raw` AS `content_raw`,
			`content_boxes`.`content` AS `content`,
			`content_boxes`.`date_modified` AS `date_modified`,
			`content_boxes`.`date_added` AS `date_added`,
			`content_pages`.`id` AS `page_id`,
			`content_pages`.`navigation` AS `page_navigation`,
			`content_pages`.`root_node` AS `page_root_node`,
			`content_pages`.`parent` AS `page_parent`,
			`content_pages`.`level` AS `page_level`,
			`content_pages`.`sorting` AS `page_sorting`,
			`content_pages`.`type` AS `page_type`,
			`content_pages`.`template_set` AS `page_template_set`,
			`content_pages`.`name` AS `page_name`,
			`content_pages`.`name_url` AS `page_name_url`,
			`content_pages`.`protect` AS `page_protect`
		FROM
			".OAK_DB_CONTENT_BOXES." AS `content_boxes`
		LEFT JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_boxes`.`page` = `content_pages`.`id`
		WHERE 
			1
	";
	
	// add where clauses
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `content_pages`.`id` = :page ";
		$bind_params['page'] = $page;
	}
	
	// add sorting
	$sql .= " ORDER BY `content_boxes`.`name` ";
	
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

class Content_BoxException extends Exception { }

?>