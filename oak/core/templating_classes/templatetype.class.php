<?php

/**
 * Project: Oak
 * File: templatetype.class.php
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

class Templating_Templatetype {
	
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
 * Singleton. Returns instance of the Templating_Templatetype object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Templating_Templatetype::$instance == null) {
		Templating_Templatetype::$instance = new Templating_Templatetype(); 
	}
	return Templating_Templatetype::$instance;
}

/**
 * Creates new template type. Takes field=>value array with template
 * type data as first argument. Returns insert id.
 * 
 * @throws Templating_TemplatetypeException
 * @param array Row data
 * @return int Template type id
 */
public function addTemplateType ($sqlData)
{
	// access check
	if (!oak_check_access('Templating', 'TemplateType', 'Manage')) {
		throw new Templating_TemplateTypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Templating_TemplatetypeException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new template type will be assigned to the current project
	$sqlData['project'] = OAK_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(OAK_DB_TEMPLATING_TEMPLATE_TYPES, $sqlData);
	
	// test if template type belongs to current user/project
	if (!$this->templateTypeBelongsToCurrentUser($insert_id)) {
		throw new Templating_TemplatetypeException('Template type does not belong to current user or project');
	}
	
	return $insert_id;
}

/**
 * Updates template type. Takes the template type id as first argument,
 * a field=>value array with the new template type data as second
 * argument. Returns amount of affected rows.
 *
 * @throws Templating_TemplatetypeException
 * @param int Template type id
 * @param array Row data
 * @return int Affected rows
*/
public function updateTemplateType ($id, $sqlData)
{
	// access check
	if (!oak_check_access('Templating', 'TemplateType', 'Manage')) {
		throw new Templating_TemplateTypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_TemplatetypeException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Templating_TemplatetypeException('Input for parameter sqlData is not an array');	
	}
	
	// test if template type belongs to current user/project
	if (!$this->templateTypeBelongsToCurrentUser($id)) {
		throw new Templating_TemplatetypeException('Template type does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project AND `editable` = '1' ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(OAK_DB_TEMPLATING_TEMPLATE_TYPES, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes template type from the template type table. Takes the
 * template type id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Templating_TemplatetypeException
 * @param int Template type id
 * @return int Amount of affected rows
 */
public function deleteTemplateType ($id)
{
	// access check
	if (!oak_check_access('Templating', 'TemplateType', 'Manage')) {
		throw new Templating_TemplateTypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_TemplatetypeException('Input for parameter id is not numeric');
	}
	
	// test if template type belongs to current user/project
	if (!$this->templateTypeBelongsToCurrentUser($id)) {
		throw new Templating_TemplatetypeException('Template type does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project AND `editable` = '1' ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_TEMPLATING_TEMPLATE_TYPES,	
		$where, $bind_params);
}

/**
 * Selects one template type. Takes the template type id as first
 * argument. Returns array with template type information.
 * 
 * @throws Templating_TemplatetypeException
 * @param int Template type id
 * @return array
 */
public function selectTemplateType ($id)
{
	// access check
	if (!oak_check_access('Templating', 'TemplateType', 'Use')) {
		throw new Templating_TemplateTypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_TemplatetypeException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT
			`templating_template_types`.`id` AS `id`,
			`templating_template_types`.`project` AS `project`,
			`templating_template_types`.`name` AS `name`,
			`templating_template_types`.`description` AS `description`,
			`templating_template_types`.`editable` AS `editable`
		FROM
			".OAK_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		WHERE 
			`templating_template_types`.`id` = :id
		  AND
			`templating_template_types`.`project` = :project
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
 * Method to select one or more template types. Takes key=>value
 * array with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>start, int, optional: row offtype</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Templating_TemplatetypeException
 * @param array Select params
 * @return array
 */
public function selectTemplateTypes ($params = array())
{
	// access check
	if (!oak_check_access('Templating', 'TemplateType', 'Use')) {
		throw new Templating_TemplateTypeException("You are not allowed to perform this action");
	}
	
	// define some vars
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Templating_TemplatetypeException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Templating_TemplatetypeException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT
			`templating_template_types`.`id` AS `id`,
			`templating_template_types`.`project` AS `project`,
			`templating_template_types`.`name` AS `name`,
			`templating_template_types`.`description` AS `description`,
			`templating_template_types`.`editable` AS `editable`
		FROM
			".OAK_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		WHERE 
			`templating_template_types`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	// add sorting
	$sql .= " ORDER BY `templating_template_types`.`name` ";
	
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
 * Checks whether the given template type belongs to the current project or not. Takes
 * the id of the template type as first argument. Returns bool.
 *
 * @throws Templating_TemplatetypeException
 * @param int Template type id
 * @return bool
 */
public function templateTypeBelongsToCurrentProject ($type)
{
	// access check
	if (!oak_check_access('Templating', 'TemplateType', 'Use')) {
		throw new Templating_TemplateTypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($type) || !is_numeric($type)) {
		throw new Templating_TemplatetypeException('Input for parameter type is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".OAK_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		WHERE
			`templating_template_types`.`id` = :type
		  AND
			`templating_template_types`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'type' => (int)$type,
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
 * Tests whether template type belongs to current user or not. Takes
 * the template type id as first argument. Returns bool.
 *
 * @throws Templating_TemplateTypeException
 * @param int Template type id
 * @return bool
 */
public function templateTypeBelongsToCurrentUser ($template_type)
{
	// access check
	if (!oak_check_access('Templating', 'TemplateType', 'Use')) {
		throw new Templating_TemplateTypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($template_type) || !is_numeric($template_type)) {
		throw new Templating_TemplateTypeException('Input for parameter template tyoe is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->templateTypeBelongsToCurrentProject($template_type)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(OAK_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

/**
 * Tests given template type name for uniqueness. Takes the template type
 * name as first argument and an optional template type id as second argument.
 * If the template type id is given, this template type won't be considered
 * when checking for uniqueness (useful for updates). Returns boolean true if
 * template type name is unique.
 *
 * @throws Templating_TemplatetypeException
 * @param string Template type name
 * @param int Template type id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!oak_check_access('Templating', 'TemplateType', 'Use')) {
		throw new Templating_TemplateTypeException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Templating_TemplatetypeException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Templating_TemplatetypeException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Templating_TemplatetypeException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
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

class Templating_TemplatetypeException extends Exception { }

?>