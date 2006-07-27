<?php

/**
 * Project: Oak
 * File: documentcategory.class.php
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

class Media_DocumentCategory {
	
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
 * Singleton. Returns instance of the Media_DocumentCategory object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Media_DocumentCategory::$instance == null) {
		Media_DocumentCategory::$instance = new Media_DocumentCategory(); 
	}
	return Media_DocumentCategory::$instance;
}

/**
 * Adds document category to the document category table. Takes a field=>value
 * array with category data as first argument. Returns insert id. 
 * 
 * @throws Media_DocumentCategoryException
 * @param array Row data
 * @return int Document category id
 */
public function addDocumentCategory ($sqlData)
{
	// access check
	if (!oak_check_access('Media', 'DocumentCategory', 'Manage')) {
		throw new Media_DocumentCategoryException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Media_DocumentCategoryException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new row will be assigned to the current project
	$sqlData['project'] = OAK_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(OAK_DB_MEDIA_DOCUMENT_CATEGORIES, $sqlData);

	// test if document category belongs to current project/user
	if (!$this->documentCategoryBelongsToCurrentUser($insert_id)) {
		throw new Media_DocumentCategoryException('Document category does not belong to current project or user');
	}
	
	return $insert_id;
}

/**
 * Updates document category. Takes the category id as first argument, a
 * field=>value array with the new category data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Media_DocumentCategoryException
 * @param int Document category id
 * @param array Row data
 * @return int Affected rows
*/
public function updateDocumentCategory ($id, $sqlData)
{
	// access check
	if (!oak_check_access('Media', 'DocumentCategory', 'Manage')) {
		throw new Media_DocumentCategoryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_DocumentCategoryException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Media_DocumentCategoryException('Input for parameter sqlData is not an array');	
	}
	
	// test if document category belongs to current project/user
	if (!$this->documentCategoryBelongsToCurrentUser($id)) {
		throw new Media_DocumentCategoryException('Document category does not belong to current project or user');
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
 * Removes document category from the document category table. Takes the
 * category id as first argument. Returns amount of affected rows.
 * 
 * @throws Media_DocumentCategoryException
 * @param int Document category id
 * @return int Amount of affected rows
 */
public function deleteDocumentCategory ($id)
{
	// access check
	if (!oak_check_access('Media', 'DocumentCategory', 'Manage')) {
		throw new Media_DocumentCategoryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_DocumentCategoryException('Input for parameter id is not numeric');
	}
	
	// test if document category belongs to current project/user
	if (!$this->documentCategoryBelongsToCurrentUser($id)) {
		throw new Media_DocumentCategoryException('Document category does not belong to current project or user');
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
 * Selects one document category. Takes the category id as first argument.
 * Returns array with category information.
 * 
 * @throws Media_DocumentCategoryException
 * @param int Document category id
 * @return array
 */
public function selectDocumentCategory ($id)
{
	// access check
	if (!oak_check_access('Media', 'DocumentCategory', 'Use')) {
		throw new Media_DocumentCategoryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_DocumentCategoryException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`media_document_categories`.`id` AS `id`,
			`media_document_categories`.`project` AS `project`,
			`media_document_categories`.`name` AS `name`
		FROM
			".OAK_DB_MEDIA_DOCUMENT_CATEGORIES." AS `media_document_categories`
		WHERE 
			`media_document_categories`.`id` = :id
		  AND
			`media_document_categories`.`project` = :project
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
 * Method to select one or more document categories. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Media_DocumentCategoryException
 * @param array Select params
 * @return array
 */
public function selectDocumentCategories ($params = array())
{
	// access check
	if (!oak_check_access('Media', 'DocumentCategory', 'Use')) {
		throw new Media_DocumentCategoryException("You are not allowed to perform this action");
	}
	
	// define some vars
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Media_DocumentCategoryException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Media_DocumentCategoryException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`media_document_categories`.`id` AS `id`,
			`media_document_categories`.`project` AS `project`,
			`media_document_categories`.`name` AS `name`
		FROM
			".OAK_DB_MEDIA_DOCUMENT_CATEGORIES." AS `media_document_categories`
		WHERE 
			`media_document_categories`.`project` = :project
		ORDER BY
			`media_document_categories`.`name`
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
 * Counts document categories saved in the document category table.
 * Returns int.
 * 
 * @return int
 */
public function countDocumentCategories ()
{
	// access check
	if (!oak_check_access('Media', 'DocumentCategory', 'Use')) {
		throw new Media_DocumentCategoryException("You are not allowed to perform this action");
	}
	
	// define some vars
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_MEDIA_DOCUMENT_CATEGORIES." AS `media_document_categories`
		WHERE 
			1
	";
	
	// execute query and return result
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Tests given document category name for uniqueness. Takes the category name as
 * first argument and an optional category id as second argument. If
 * the category id is given, this category won't be considered when checking
 * for uniqueness (useful for updates). Returns boolean true if the category
 * name is unique.
 *
 * @throws User_GroupException
 * @param string Document category name
 * @param int Document category id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!oak_check_access('Media', 'DocumentCategory', 'Use')) {
		throw new Media_DocumentCategoryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new User_DocumentCategoryException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new User_DocumentCategoryException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new User_DocumentCategoryException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_MEDIA_DOCUMENT_CATEGORIES." AS `media_document_categories`
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
 * Tests whether given document category belongs to current project. Takes the
 * document category id as first argument. Returns bool.
 *
 * @throws Media_DocumentCategoryException
 * @param int Document category id
 * @return int bool
 */
public function documentCategoryBelongsToCurrentProject ($document_category)
{
	// access check
	if (!oak_check_access('Media', 'DocumentCategory', 'Use')) {
		throw new Media_DocumentCategoryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($document_category) || !is_numeric($document_category)) {
		throw new Media_DocumentCategoryException('Input for parameter document_category is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_MEDIA_DOCUMENT_CATEGORIES." AS `media_document_categories`
		WHERE
			`media_document_categories`.`id` = :document_category
		  AND
			`media_document_categories`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'document_category' => (int)$document_category,
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
 * Test whether document category belongs to current user or not. Takes
 * the document category id as first argument. Returns bool.
 *
 * @throws Media_DocumentCategoryException
 * @param int Document category id
 * @return bool
 */
public function documentCategoryBelongsToCurrentUser ($document_category)
{
	// access check
	if (!oak_check_access('Media', 'DocumentCategory', 'Use')) {
		throw new Media_DocumentCategoryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($document_category) || !is_numeric($document_category)) {
		throw new Media_DocumentCategoryException('Input for parameter document_category is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->documentCategoryBelongsToCurrentProject($document_category)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(OAK_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Media_DocumentCategoryException extends Exception { }

?>