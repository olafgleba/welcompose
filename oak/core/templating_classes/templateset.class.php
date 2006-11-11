<?php

/**
 * Project: Oak
 * File: templateset.class.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-2.1.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License 3.0
 */

class Templating_Templateset {
	
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
 * Singleton. Returns instance of the Templating_Templateset object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Templating_Templateset::$instance == null) {
		Templating_Templateset::$instance = new Templating_Templateset(); 
	}
	return Templating_Templateset::$instance;
}

/**
 * Creates new template set. Takes field=>value array with template
 * set data as first argument. Returns insert id.
 * 
 * @throws Templating_TemplatesetException
 * @param array Row data
 * @return int Template set id
 */
public function addTemplateSet ($sqlData)
{
	// access check
	if (!oak_check_access('Templating', 'TemplateSet', 'Manage')) {
		throw new Templating_TemplateSetException("You are not allowed to perform this action");
	}
	
	if (!is_array($sqlData)) {
		throw new Templating_TemplatesetException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new template set will be assigned to the current project
	$sqlData['project'] = OAK_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(OAK_DB_TEMPLATING_TEMPLATE_SETS, $sqlData);
	
	// test if template set belongs to current user/project
	if (!$this->templateSetBelongsToCurrentUser($insert_id)) {
		throw new Templating_TemplatesetException('Template set does not belong to current user or project');
	}
	
	return $insert_id;
}

/**
 * Updates template set. Takes the template set id as first argument,
 * a field=>value array with the new template set data as second
 * argument. Returns amount of affected rows.
 *
 * @throws Templating_TemplatesetException
 * @param int Template set id
 * @param array Row data
 * @return int Affected rows
*/
public function updateTemplateSet ($id, $sqlData)
{
	// access check
	if (!oak_check_access('Templating', 'TemplateSet', 'Manage')) {
		throw new Templating_TemplateSetException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_TemplatesetException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Templating_TemplatesetException('Input for parameter sqlData is not an array');	
	}
	
	// test if template set belongs to current user/project
	if (!$this->templateSetBelongsToCurrentUser($id)) {
		throw new Templating_TemplatesetException('Template set does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(OAK_DB_TEMPLATING_TEMPLATE_SETS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes template set from the template set table. Takes the
 * template set id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Templating_TemplatesetException
 * @param int Template set id
 * @return int Amount of affected rows
 */
public function deleteTemplateSet ($id)
{
	// access check
	if (!oak_check_access('Templating', 'TemplateSet', 'Manage')) {
		throw new Templating_TemplateSetException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_TemplatesetException('Input for parameter id is not numeric');
	}
	
	// test if template set belongs to current user/project
	if (!$this->templateSetBelongsToCurrentUser($id)) {
		throw new Templating_TemplatesetException('Template set does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_TEMPLATING_TEMPLATE_SETS,	
		$where, $bind_params);
}

/**
 * Selects one template set. Takes the template set id as first
 * argument. Returns array with template set information.
 * 
 * @throws Templating_TemplatesetException
 * @param int Template set id
 * @return array
 */
public function selectTemplateSet ($id)
{
	// access check
	if (!oak_check_access('Templating', 'TemplateSet', 'Use')) {
		throw new Templating_TemplateSetException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_TemplatesetException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT
			`templating_template_sets`.`id` AS `id`,
			`templating_template_sets`.`project` AS `project`,
			`templating_template_sets`.`name` AS `name`,
			`templating_template_sets`.`description` AS `description`
		FROM
			".OAK_DB_TEMPLATING_TEMPLATE_SETS." AS `templating_template_sets`
		WHERE 
			`templating_template_sets`.`id` = :id
		  AND
			`templating_template_sets`.`project` = :project
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
 * Method to select one or more template sets. Takes key=>value
 * array with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Templating_TemplatesetException
 * @param array Select params
 * @return array
 */
public function selectTemplateSets ($params = array())
{
	// access check
	if (!oak_check_access('Templating', 'TemplateSet', 'Use')) {
		throw new Templating_TemplateSetException("You are not allowed to perform this action");
	}
	
	// define some vars
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Templating_TemplatesetException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Templating_TemplatesetException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT
			`templating_template_sets`.`id` AS `id`,
			`templating_template_sets`.`project` AS `project`,
			`templating_template_sets`.`name` AS `name`,
			`templating_template_sets`.`description` AS `description`
		FROM
			".OAK_DB_TEMPLATING_TEMPLATE_SETS." AS `templating_template_sets`
		WHERE
			`templating_template_sets`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	// add sorting
	$sql .= " ORDER BY `templating_template_sets`.`name` ";
	
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
 * Checks whether the given template set belongs to the current project or not. Takes
 * the id of the template set as first argument. Returns bool.
 *
 * @throws Templating_TemplatesetException
 * @param int Template set id
 * @return bool
 */
public function templateSetBelongsToCurrentProject ($set)
{
	// access check
	if (!oak_check_access('Templating', 'TemplateSet', 'Use')) {
		throw new Templating_TemplateSetException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($set) || !is_numeric($set)) {
		throw new Templating_TemplatesetException('Input for parameter set is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".OAK_DB_TEMPLATING_TEMPLATE_SETS." AS `templating_template_sets`
		WHERE
			`templating_template_sets`.`id` = :set
		  AND
			`templating_template_sets`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'set' => (int)$set,
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
 * Tests whether template set belongs to current user or not. Takes
 * the template set id as first argument. Returns bool.
 *
 * @throws Templating_TemplateSetException
 * @param int Template set id
 * @return bool
 */
public function templateSetBelongsToCurrentUser ($template_set)
{
	// access check
	if (!oak_check_access('Templating', 'TemplateSet', 'Use')) {
		throw new Templating_TemplateSetException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($template_set) || !is_numeric($template_set)) {
		throw new Templating_TemplateSetException('Input for parameter template is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->templateSetBelongsToCurrentProject($template_set)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(OAK_CURRENT_USER)) {
		return false;
	}
	
	return true;
}


/**
 * Tests given template set name for uniqueness. Takes the template set
 * name as first argument and an optional template set id as second argument.
 * If the template set id is given, this template set won't be considered
 * when checking for uniqueness (useful for updates). Returns boolean true if
 * template set name is unique.
 *
 * @throws Templating_TemplatesetException
 * @param string Template set name
 * @param int Template set id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!oak_check_access('Templating', 'TemplateSet', 'Use')) {
		throw new Templating_TemplateSetException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Templating_TemplatesetException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Templating_TemplatesetException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Templating_TemplatesetException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_TEMPLATING_TEMPLATE_SETS." AS `templating_template_sets`
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

class Templating_TemplatesetException extends Exception { }

?>