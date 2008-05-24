<?php

/**
 * Project: Welcompose
 * File: structuraltemplate.class.php
 * 
 * Copyright (c) 2008 creatics media.systems
 * 
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-3.0.php
 * 
 * $Id$
 * 
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

/**
 * Singleton for Content_StructuralTemplate.
 * 
 * @return object
 */
function Content_StructuralTemplate ()
{
	if (Content_StructuralTemplate::$instance == null) {
		Content_StructuralTemplate::$instance = new Content_StructuralTemplate(); 
	}
	return Content_StructuralTemplate::$instance;
}

class Content_StructuralTemplate {
	
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
 * Adds structural template to the structural template table. Takes a
 * field=>value array with structural template data as first argument.
 * Returns insert id. 
 * 
 * @throws Content_StructuralTemplateException
 * @param array Row data
 * @return int Structural template id
 */
public function addStructuralTemplate ($sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'StructuralTemplate', 'Manage')) {
		throw new Content_StructuralTemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Content_StructuralTemplateException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new structural template will be linked to the current project
	$sqlData['project'] = WCOM_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_CONTENT_STRUCTURAL_TEMPLATES, $sqlData);
	
	// test if structural template belongs tu current user/project
	if (!$this->structuralTemplateBelongsToCurrentUser($insert_id)) {
		throw new Content_StructuralTemplateException('Structural template does not belong to current user/project');
	}
	
	return $insert_id;
}

/**
 * Updates structural template. Takes the structural template id as first argument, a
 * field=>value array with the new structural template data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_StructuralTemplateException
 * @param int Structural template id
 * @param array Row data
 * @return int Affected rows
*/
public function updateStructuralTemplate ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'StructuralTemplate', 'Manage')) {
		throw new Content_StructuralTemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_StructuralTemplateException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_StructuralTemplateException('Input for parameter sqlData is not an array');	
	}
	
	// test if structural template belongs tu current user/project
	if (!$this->structuralTemplateBelongsToCurrentUser($id)) {
		throw new Content_StructuralTemplateException('Structural template does not belong to current user/project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_CONTENT_STRUCTURAL_TEMPLATES, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes structural template from the structural template table.
 * Takes the structural template id as first argument. Returns amount
 * of affected rows.
 * 
 * @throws Content_StructuralTemplateException
 * @param int Structural template id
 * @return int Amount of affected rows
 */
public function deleteStructuralTemplate ($id)
{
	// access check
	if (!wcom_check_access('Content', 'StructuralTemplate', 'Manage')) {
		throw new Content_StructuralTemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_StructuralTemplateException('Input for parameter id is not numeric');
	}
	
	// test if structural template belongs tu current user/project
	if (!$this->structuralTemplateBelongsToCurrentUser($id)) {
		throw new Content_StructuralTemplateException('Structural template does not belong to current user/project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_CONTENT_STRUCTURAL_TEMPLATES, $where, $bind_params);
}

/**
 * Selects one structural template. Takes the structural template
 * id as first argument. Returns array with structural template
 * information.
 * 
 * @throws Content_StructuralTemplateException
 * @param int Structural template id
 * @return array
 */
public function selectStructuralTemplate ($id)
{
	// access check
	if (!wcom_check_access('Content', 'StructuralTemplate', 'Use')) {
		throw new Content_StructuralTemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_StructuralTemplateException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`content_structural_templates`.`id` AS `id`,
			`content_structural_templates`.`project` AS `project`,
			`content_structural_templates`.`name` AS `name`,
			`content_structural_templates`.`description` AS `description`,
			`content_structural_templates`.`content` AS `content`,
			`content_structural_templates`.`date_modified` AS `date_modified`,
			`content_structural_templates`.`date_added` AS `date_added`
		FROM
			".WCOM_DB_CONTENT_STRUCTURAL_TEMPLATES." AS `content_structural_templates`
		WHERE
			`content_structural_templates`.`id` = :id
		  AND
			`content_structural_templates`.`project` = :project
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
 * Method to select one or more structural templates. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>order_marco, string, otpional: How to sort the result set.
 * Supported macros:
 *    <ul>
 *        <li>NAME: sort by name</li>
 *        <li>DATE_MODIFIED: sort by date modified</li>
 *        <li>DATE_ADDED: sort by date added</li>
 *    </ul>
 * </li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Content_StructuralTemplateException
 * @param array Select params
 * @return array
 */
public function selectStructuralTemplates ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'StructuralTemplate', 'Use')) {
		throw new Content_StructuralTemplateException("You are not allowed to perform this action");
	}
	
	// define some vars
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_StructuralTemplateException('Input for parameter params is not an array');	
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
				throw new Content_StructuralTemplateException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'NAME' => '`name`',
		'DATE_ADDED' => '`date_added`',
		'DATE_MODIFIED' => '`date_modified`'
	);
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// prepare query
	$sql = "
		SELECT 
			`content_structural_templates`.`id` AS `id`,
			`content_structural_templates`.`project` AS `project`,
			`content_structural_templates`.`name` AS `name`,
			`content_structural_templates`.`description` AS `description`,
			`content_structural_templates`.`content` AS `content`,
			`content_structural_templates`.`date_modified` AS `date_modified`,
			`content_structural_templates`.`date_added` AS `date_added`
		FROM
			".WCOM_DB_CONTENT_STRUCTURAL_TEMPLATES." AS `content_structural_templates`
		WHERE
			`content_structural_templates`.`project` = :project
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
 * Method to count structural templates. Takes key=>value array with count params as first
 * argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>none</li>
 * </ul>
 * 
 * @throws Content_StructuralTemplateException
 * @param array Count params
 * @return array
 */
public function countStructuralTemplates ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'StructuralTemplate', 'Use')) {
		throw new Content_StructuralTemplateException("You are not allowed to perform this action");
	}
	
	// define some vars
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_StructuralTemplateException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			default:
				throw new Content_StructuralTemplateException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_STRUCTURAL_TEMPLATES." AS `content_structural_templates`
		WHERE
			`content_structural_templates`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Tests given structural template name for uniqueness. Takes the structural template
 * name as first argument and an optional structural template id as second
 * argument. If the structural template id is given, this structural template won't be
 * considered when checking for uniqueness (useful for updates).
 * Returns boolean true if structural template name is unique.
 *
 * @throws Content_StructuralTemplateException
 * @param string Structural template name
 * @param int Structural template id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!wcom_check_access('Content', 'StructuralTemplate', 'Use')) {
		throw new Content_StructuralTemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Content_StructuralTemplateException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Content_StructuralTemplateException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Content_StructuralTemplateException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_STRUCTURAL_TEMPLATES." AS `content_structural_templates`
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
 * Tests whether given structural template belongs to current project. Takes the
 * structural template id as first argument. Returns bool.
 *
 * @throws Content_StructuralTemplateException
 * @param int Structural structural template id
 * @return int bool
 */
public function structuralTemplateBelongsToCurrentProject ($structural_template)
{
	// access check
	if (!wcom_check_access('Content', 'StructuralTemplate', 'Use')) {
		throw new Content_StructuralTemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($structural_template) || !is_numeric($structural_template)) {
		throw new Content_StructuralTemplateException('Input for parameter template is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_STRUCTURAL_TEMPLATES." AS `content_structural_templates`
		WHERE
			`content_structural_templates`.`id` = :template
		  AND
			`content_structural_templates`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'template' => (int)$structural_template,
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
 * Test whether template belongs to current user or not. Takes
 * the template id as first argument. Returns bool.
 *
 * @throws Content_StructuralTemplateException
 * @param int template id
 * @return bool
 */
public function structuralTemplateBelongsToCurrentUser ($structural_template)
{
	// access check
	if (!wcom_check_access('Content', 'StructuralTemplate', 'Use')) {
		throw new Content_StructuralTemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($structural_template) || !is_numeric($structural_template)) {
		throw new Content_StructuralTemplateException('Input for parameter structural template is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->structuralTemplateBelongsToCurrentProject($structural_template)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}


// end of class
}

class Content_StructuralTemplateException extends Exception { }

?>