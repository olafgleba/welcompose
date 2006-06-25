<?php

/**
 * Project: Oak
 * File: imagecategory.class.php
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

class Media_ImageCategory {
	
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
 * Singleton. Returns instance of the Media_ImageCategory object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Media_ImageCategory::$instance == null) {
		Media_ImageCategory::$instance = new Media_ImageCategory(); 
	}
	return Media_ImageCategory::$instance;
}

/**
 * Adds image category to the image category table. Takes a field=>value
 * array with category data as first argument. Returns insert id. 
 * 
 * @throws Media_ImageCategoryException
 * @param array Row data
 * @return int Image category id
 */
public function addImageCategory ($sqlData)
{
	if (!is_array($sqlData)) {
		throw new Media_ImageCategoryException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new row will be assigned to the current project
	$sqlData['project'] = OAK_CURRENT_PROJECT;
	
	// insert row
	return $this->base->db->insert(OAK_DB_MEDIA_DOCUMENT_CATEGORIES, $sqlData);
}

/**
 * Updates image category. Takes the category id as first argument, a
 * field=>value array with the new category data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Media_ImageCategoryException
 * @param int Image category id
 * @param array Row data
 * @return int Affected rows
*/
public function updateImageCategory ($id, $sqlData)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_ImageCategoryException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Media_ImageCategoryException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(OAK_DB_MEDIA_DOCUMENT_CATEGORIES, $sqlData,
		$where, $bind_params);
}

/**
 * Removes image category from the image category table. Takes the
 * category id as first argument. Returns amount of affected rows.
 * 
 * @throws Media_ImageCategoryException
 * @param int Image category id
 * @return int Amount of affected rows
 */
public function deleteImageCategory ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_ImageCategoryException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_MEDIA_DOCUMENT_CATEGORIES, $where, $bind_params);
}

/**
 * Selects one image category. Takes the category id as first argument.
 * Returns array with category information.
 * 
 * @throws Media_ImageCategoryException
 * @param int Image category id
 * @return array
 */
public function selectImageCategory ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_ImageCategoryException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`media_image_categories`.`id` AS `id`,
			`media_image_categories`.`project` AS `project`,
			`media_image_categories`.`name` AS `name`
		FROM
			".OAK_DB_MEDIA_DOCUMENT_CATEGORIES." AS `media_image_categories`
		WHERE 
			`media_image_categories`.`id` = :id
		  AND
			`media_image_categories`.`project` = :project
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
 * Method to select one or more image categories. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Media_ImageCategoryException
 * @param array Select params
 * @return array
 */
public function selectImageCategories ($params = array())
{
	// define some vars
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Media_ImageCategoryException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Media_ImageCategoryException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`media_image_categories`.`id` AS `id`,
			`media_image_categories`.`project` AS `project`,
			`media_image_categories`.`name` AS `name`
		FROM
			".OAK_DB_MEDIA_DOCUMENT_CATEGORIES." AS `media_image_categories`
		WHERE 
			`media_image_categories`.`project` = :project
		ORDER BY
			`media_image_categories`.`name`
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
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
 * Counts image categories saved in the image category table.
 * Returns int.
 * 
 * @return int
 */
public function countImageCategories ()
{
	// define some vars
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_MEDIA_DOCUMENT_CATEGORIES." AS `media_image_categories`
		WHERE 
			1
	";
	
	// execute query and return result
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Tests given image category name for uniqueness. Takes the category name as
 * first argument and an optional category id as second argument. If
 * the category id is given, this category won't be considered when checking
 * for uniqueness (useful for updates). Returns boolean true if the category
 * name is unique.
 *
 * @throws User_GroupException
 * @param string Image category name
 * @param int Image category id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// input check
	if (empty($name)) {
		throw new User_ImageCategoryException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new User_ImageCategoryException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new User_ImageCategoryException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_MEDIA_DOCUMENT_CATEGORIES." AS `media_image_categories`
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

class Media_ImageCategoryException extends Exception { }

?>