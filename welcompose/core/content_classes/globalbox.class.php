<?php

/**
 * Project: Welcompose
 * File: globalbox.class.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-3.0.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

/**
 * Singleton for Content_GlobalBox.
 * 
 * @return object
 */
function Content_GlobalBox ()
{
	if (Content_GlobalBox::$instance == null) {
		Content_GlobalBox::$instance = new Content_GlobalBox(); 
	}
	return Content_GlobalBox::$instance;
}

class Content_GlobalBox {
	
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
 * Adds global box to the global box table. Takes a field=>value array with
 * global box data as first argument. Returns insert id. 
 * 
 * @throws Content_GlobalBoxException
 * @param array Row data
 * @return int Global box id
 */
public function addGlobalBox ($sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'GlobalBox', 'Manage')) {
		throw new Content_GlobalBoxException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Content_GlobalBoxException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new global box will be linked to the current project
	$sqlData['project'] = WCOM_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_CONTENT_GLOBAL_BOXES, $sqlData);
	
	// test if global box belongs tu current user/project
	if (!$this->globalBoxBelongsToCurrentUser($insert_id)) {
		throw new Content_GlobalBoxException('GlobalBox does not belong to current user/project');
	}
	
	return $insert_id;
}

/**
 * Updates global box. Takes the global box id as first argument, a
 * field=>value array with the new global box data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_GlobalBoxException
 * @param int Global box id
 * @param array Row data
 * @return int Affected rows
*/
public function updateGlobalBox ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'GlobalBox', 'Manage')) {
		throw new Content_GlobalBoxException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_GlobalBoxException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_GlobalBoxException('Input for parameter sqlData is not an array');	
	}
	
	// test if global box belongs tu current user/project
	if (!$this->globalBoxBelongsToCurrentUser($id)) {
		throw new Content_GlobalBoxException('GlobalBox does not belong to current user/project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_CONTENT_GLOBAL_BOXES, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes global box from the global box table. Takes the global box id
 * as first argument. Returns amount of affected rows.
 * 
 * @throws Content_GlobalBoxException
 * @param int Global box id
 * @return int Amount of affected rows
 */
public function deleteGlobalBox ($id)
{
	// access check
	if (!wcom_check_access('Content', 'GlobalBox', 'Manage')) {
		throw new Content_GlobalBoxException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_GlobalBoxException('Input for parameter id is not numeric');
	}
	
	// test if global box belongs tu current user/project
	if (!$this->globalBoxBelongsToCurrentUser($id)) {
		throw new Content_GlobalBoxException('GlobalBox does not belong to current user/project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_CONTENT_GLOBAL_BOXES, $where, $bind_params);
}

/**
 * Selects one global box. Takes the global box id as first argument.
 * Returns array with global box information.
 * 
 * @throws Content_GlobalBoxException
 * @param int Global box id
 * @return array
 */
public function selectGlobalBox ($id)
{
	// access check
	if (!wcom_check_access('Content', 'GlobalBox', 'Use')) {
		throw new Content_GlobalBoxException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_GlobalBoxException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`content_global_boxes`.`id` AS `id`,
			`content_global_boxes`.`project` AS `project`,
			`content_global_boxes`.`name` AS `name`,
			`content_global_boxes`.`content_raw` AS `content_raw`,
			`content_global_boxes`.`content` AS `content`,
			`content_global_boxes`.`text_converter` AS `text_converter`,
			`content_global_boxes`.`apply_macros` AS `apply_macros`,
			`content_global_boxes`.`priority` AS `priority`,
			`content_global_boxes`.`date_modified` AS `date_modified`,
			`content_global_boxes`.`date_added` AS `date_added`
		FROM
			".WCOM_DB_CONTENT_GLOBAL_BOXES." AS `content_global_boxes`
		WHERE
			`content_global_boxes`.`id` = :id
		  AND
			`content_global_boxes`.`project` = :project
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
 * Method to select one or more global boxes. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>order_marco, string, otpional: How to sort the result set.
 * Supported macros:
 *    <ul>
 *        <li>NAME: sort by name</li>
 *        <li>PRIORITY: sort by priority</li>
 *        <li>DATE_MODIFIED: sort by date modified</li>
 *        <li>DATE_ADDED: sort by date added</li>
 *    </ul>
 * </li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Content_GlobalBoxException
 * @param array Select params
 * @return array
 */
public function selectGlobalBoxes ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'GlobalBox', 'Use')) {
		throw new Content_GlobalBoxException("You are not allowed to perform this action");
	}
	
	// define some vars
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_GlobalBoxException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'order_macro':
					$$_key = (string)$_value;
				break;
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_GlobalBoxException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'NAME' => '`name`',
		'PRIORITY' => '`priority`',
		'DATE_ADDED' => '`date_added`',
		'DATE_MODIFIED' => '`date_modified`'
	);
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// prepare query
	$sql = "
		SELECT 
			`content_global_boxes`.`id` AS `id`,
			`content_global_boxes`.`project` AS `project`,
			`content_global_boxes`.`name` AS `name`,
			`content_global_boxes`.`content_raw` AS `content_raw`,
			`content_global_boxes`.`content` AS `content`,
			`content_global_boxes`.`text_converter` AS `text_converter`,
			`content_global_boxes`.`apply_macros` AS `apply_macros`,
			`content_global_boxes`.`priority` AS `priority`,
			`content_global_boxes`.`date_modified` AS `date_modified`,
			`content_global_boxes`.`date_added` AS `date_added`
		FROM
			".WCOM_DB_CONTENT_GLOBAL_BOXES." AS `content_global_boxes`
		WHERE
			`content_global_boxes`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT 
	);
	
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

/**
 * Method to count global boxes. Takes key=>value array with count params as first
 * argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>none</li>
 * </ul>
 * 
 * @throws Content_GlobalBoxException
 * @param array Count params
 * @return array
 */
public function countGlobalBoxes ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'GlobalBox', 'Use')) {
		throw new Content_GlobalBoxException("You are not allowed to perform this action");
	}
	
	// define some vars
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_GlobalBoxException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			default:
				throw new Content_GlobalBoxException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_GLOBAL_BOXES." AS `content_global_boxes`
		WHERE
			`content_global_boxes`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Tests given global box name for uniqueness. Takes the global box
 * name as first argument and an optional global box id as second
 * argument. If the global box id is given, this global box won't be
 * considered when checking for uniqueness (useful for updates).
 * Returns boolean true if global box name is unique.
 *
 * @throws Content_GlobalBoxException
 * @param string Global box name
 * @param int Global box id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!wcom_check_access('Content', 'GlobalBox', 'Use')) {
		throw new Content_GlobalBoxException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Content_GlobalBoxException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Content_GlobalBoxException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Content_GlobalBoxException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_GLOBAL_BOXES." AS `content_global_boxes`
		WHERE
			`project` = :project
		  AND
			`name` = :name
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT,
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
 * Tests whether given global box belongs to current project. Takes the
 * global box id as first argument. Returns bool.
 *
 * @throws Content_GlobalBoxException
 * @param int Global global box id
 * @return int bool
 */
public function globalBoxBelongsToCurrentProject ($global_box)
{
	// access check
	if (!wcom_check_access('Content', 'GlobalBox', 'Use')) {
		throw new Content_GlobalBoxException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($global_box) || !is_numeric($global_box)) {
		throw new Content_GlobalBoxException('Input for parameter box is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_GLOBAL_BOXES." AS `content_global_boxes`
		WHERE
			`content_global_boxes`.`id` = :box
		  AND
			`content_global_boxes`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'box' => (int)$global_box,
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
 * @throws Content_GlobalBoxException
 * @param int box id
 * @return bool
 */
public function globalBoxBelongsToCurrentUser ($global_box)
{
	// access check
	if (!wcom_check_access('Content', 'GlobalBox', 'Use')) {
		throw new Content_GlobalBoxException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($global_box) || !is_numeric($global_box)) {
		throw new Content_GlobalBoxException('Input for parameter global box is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->globalBoxBelongsToCurrentProject($global_box)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}


// end of class
}

class Content_GlobalBoxException extends Exception { }

?>