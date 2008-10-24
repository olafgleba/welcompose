<?php

/**
 * Project: Welcompose
 * File: box.class.php
 * 
 * Copyright (c) 2008 creatics media.systems
 * 
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id$
 * 
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton for Content_Box.
 * 
 * @return object
 */
function Content_Box ()
{
	if (Content_Box::$instance == null) {
		Content_Box::$instance = new Content_Box(); 
	}
	return Content_Box::$instance;
}

class Content_Box {
	
	/**
	 * Singleton
	 * 
	 * @var object
	 */
	public static $instance = null;
	
	/**
	 * Reference to base class
	 * 
	 * @var object
	 */
	public $base = null;

/**
 * Start instance of base class, load configuration and
 * establish database connection. Please don't call the
 * constructor direcly, use the singleton pattern instead.
 */
public function __construct()
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
 * Adds box to the box table. Takes a field=>value array with
 * box data as first argument. Returns insert id. 
 * 
 * @throws Content_BoxException
 * @param array Row data
 * @return int Box id
 */
public function addBox ($sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'Box', 'Manage')) {
		throw new Content_BoxException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Content_BoxException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_CONTENT_BOXES, $sqlData);
	
	// test if box belongs tu current user/project
	if (!$this->boxBelongsToCurrentUser($insert_id)) {
		throw new Content_BoxException('Box does not belong to current user/project');
	}
	
	return $insert_id;
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
	// access check
	if (!wcom_check_access('Content', 'Box', 'Manage')) {
		throw new Content_BoxException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_BoxException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_BoxException('Input for parameter sqlData is not an array');	
	}
	
	// test if box belongs tu current user/project
	if (!$this->boxBelongsToCurrentUser($id)) {
		throw new Content_BoxException('Box does not belong to current user/project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_CONTENT_BOXES, $sqlData,
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
	// access check
	if (!wcom_check_access('Content', 'Box', 'Manage')) {
		throw new Content_BoxException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_BoxException('Input for parameter id is not numeric');
	}
	
	// test if box belongs tu current user/project
	if (!$this->boxBelongsToCurrentUser($id)) {
		throw new Content_BoxException('Box does not belong to current user/project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_CONTENT_BOXES, $where, $bind_params);
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
	// access check
	if (!wcom_check_access('Content', 'Box', 'Use')) {
		throw new Content_BoxException("You are not allowed to perform this action");
	}
	
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
			`content_boxes`.`text_converter` AS `text_converter`,
			`content_boxes`.`apply_macros` AS `apply_macros`,
			`content_boxes`.`date_modified` AS `date_modified`,
			`content_boxes`.`date_added` AS `date_added`
		FROM
			".WCOM_DB_CONTENT_BOXES." AS `content_boxes`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_boxes`.`page` = `content_pages`.`id`
		WHERE
			`content_boxes`.`id` = :id
		  AND
			`content_pages`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => $id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Selects box unsing its page and name. Takes the page id as
 * first argument, the box name as second argument. Returns
 * array.
 *
 * @throws Content_BoxException
 * @param int Page id
 * @param string Page name
 * @return array
 */
public function selectBoxUsingName ($page, $name)
{
	// access check
	if (!wcom_check_access('Content', 'Box', 'Use')) {
		throw new Content_BoxException("You are not allowed to perform this action");
	}
	
	// arg check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_BoxException('Input for parameter page has to be a non-empty numeric value');
	}
	if (empty($name) || !is_scalar($name)) {
		throw new Content_BoxException('Input for parameter name has to be a non-empty scalar value');
	}
	
	// prepare query
	$sql = "
		SELECT
			`id`
		FROM
			".WCOM_DB_CONTENT_BOXES." AS `content_boxes`
		WHERE
			`content_boxes`.`name` = :name
		  AND
			`content_boxes`.`page` = :page
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'name' => $name,
		'page' => (int)$page
	);
	
	// get box id
	$box_id = $this->base->db->select($sql, 'field', $bind_params);
	
	// if no box could be found, fail silent
	if (empty($box_id)) {
		return array();
	}
	
	// test if box belongs tu current user/project
	if (!$this->boxBelongsToCurrentUser($box_id)) {
		throw new Content_BoxException('Requested box does not belong to current user or project');
	}
	
	// return box using self::selectBox()
	return $this->selectBox($box_id);
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
	// access check
	if (!wcom_check_access('Content', 'Box', 'Use')) {
		throw new Content_BoxException("You are not allowed to perform this action");
	}
	
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
			`content_boxes`.`text_converter` AS `text_converter`,
			`content_boxes`.`apply_macros` AS `apply_macros`,
			`content_boxes`.`date_modified` AS `date_modified`,
			`content_boxes`.`date_added` AS `date_added`
		FROM
			".WCOM_DB_CONTENT_BOXES." AS `content_boxes`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_boxes`.`page` = `content_pages`.`id`
		WHERE
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT 
	);
	
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

/**
 * Method to select one or more boxes and the related page info fields. If param 'page' is set
 * the result set will gets all boxes except the ones related to the provided param page.
 * Takes key=>value array with select params as first argument. Returns array.
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
public function selectBoxesAndPages ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'Box', 'Use')) {
		throw new Content_BoxException("You are not allowed to perform this action");
	}
	
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
			`content_boxes`.`text_converter` AS `text_converter`,
			`content_boxes`.`apply_macros` AS `apply_macros`,
			`content_boxes`.`date_modified` AS `date_modified`,
			`content_boxes`.`date_added` AS `date_added`,
			`content_pages`.`id` AS `page_id`,
			`content_pages`.`project` AS `project`,
			`content_pages`.`type` AS `type`,
			`content_pages`.`template_set` AS `template_set`,
			`content_pages`.`name` AS `page_name`,
			`content_pages`.`name_url` AS `name_url`,
			`content_pages`.`alternate_name` AS `alternate_name`,
			`content_pages`.`description` AS `description`,
			`content_pages`.`optional_text` AS `optional_text`,
			`content_pages`.`url` AS `url`,
			`content_pages`.`protect` AS `protect`,
			`content_pages`.`index_page` AS `index_page`,
			`content_pages`.`sitemap_changefreq` AS `sitemap_changefreq`,
			`content_pages`.`sitemap_priority` AS `sitemap_priority`
		FROM
			".WCOM_DB_CONTENT_BOXES." AS `content_boxes`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_boxes`.`page` = `content_pages`.`id`
		WHERE
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT 
	);
	
	// add where clauses
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `content_pages`.`id` != :page ";
		$bind_params['page'] = $page;
	}
	
	// add where clause; do not include existing boxes
	$sql .= " AND `content_pages`.`name` != `content_boxes`.`name` ";
	
	// add sorting
	$sql .= " ORDER BY `content_pages`.`name` ";
	
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
 * Method to count boxes. Takes key=>value array with count params as first
 * argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>page, int, optional: Page id</li>
 * </ul>
 * 
 * @throws Content_BoxException
 * @param array Count params
 * @return array
 */
public function countBoxes ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'Box', 'Use')) {
		throw new Content_BoxException("You are not allowed to perform this action");
	}
	
	// define some vars
	$page = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_BoxException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'page':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_BoxException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_BOXES." AS `content_boxes`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_boxes`.`page` = `content_pages`.`id`
		WHERE
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `content_pages`.`id` = :page ";
		$bind_params['page'] = $page;
	}
	
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Tests given box name for uniqueness. Takes the box name as first
 * argument and an array consisting of page id and optional box id as
 * second argument. If the box id is given, this box won't be
 * considered when checking for uniqueness (useful for updates).
 * Returns boolean true if box name is unique.
 * 
 * Sample for $page_id_array():
 * 
 * <code>
 * $page_id_array = array(
 *     'page' => $page,
 *     'id' => $id
 * );
 * </code>
 *
 * @throws Content_BoxException
 * @param string Box name
 * @param array Page id and box id
 * @return bool
 */
public function testForUniqueName ($name, $page_id_array)
{
	// access check
	if (!wcom_check_access('Content', 'Box', 'Use')) {
		throw new Content_BoxException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Content_BoxException("Input for parameter name is not expected to be empty");
	}
	if (empty($page_id_array) || !is_array($page_id_array)) {
		throw new Content_BoxException("Input for parameter page_id_array is expected to be an array");
	}
	if (!is_scalar($name)) {
		throw new Content_BoxException("Input for parameter name is expected to be scalar");
	}
	
	// extract page_id_array
	$page = Base_Cnc::ifsetor($page_id_array['page'], null);
	$id = Base_Cnc::ifsetor($page_id_array['id'], null);
	
	// finish input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_BoxException("Input for parameter page is expected to be numeric");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Content_BoxException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_BOXES." AS `content_boxes`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_boxes`.`page` = `content_pages`.`id`
		WHERE
			`content_pages`.`project` = :project
		  AND
			`content_boxes`.`page` = :page
		  AND
			`content_boxes`.`name` = :name
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT,
		'page' => (int)$page,
		'name' => $name
	);
	
	// if id isn't empty, add id check
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `content_boxes`.`id` != :id ";
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
 * Tests whether given box belongs to current project. Takes the
 * box id as first argument. Returns bool.
 *
 * @throws Content_BoxException
 * @param int Box id
 * @return int bool
 */
public function boxBelongsToCurrentProject ($box)
{
	// access check
	if (!wcom_check_access('Content', 'Box', 'Use')) {
		throw new Content_BoxException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($box) || !is_numeric($box)) {
		throw new Content_BoxException('Input for parameter box is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_BOXES." AS `content_boxes`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_boxes`.`page` = `content_pages`.`id`
		WHERE
			`content_boxes`.`id` = :box
		  AND
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'box' => (int)$box,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and evaluate result
	if (intval($this->base->db->select($sql, 'field', $bind_params)) === 1) {
		return true;
	} else {
		return false;
	}
}

/**
 * Test whether box belongs to current user or not. Takes
 * the box id as first argument. Returns bool.
 *
 * @throws Content_BoxException
 * @param int box id
 * @return bool
 */
public function boxBelongsToCurrentUser ($box)
{
	// access check
	if (!wcom_check_access('Content', 'Box', 'Use')) {
		throw new Content_BoxException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($box) || !is_numeric($box)) {
		throw new Content_BoxException('Input for parameter box is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->boxBelongsToCurrentProject($box)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}


// end of class
}

class Content_BoxException extends Exception { }

?>