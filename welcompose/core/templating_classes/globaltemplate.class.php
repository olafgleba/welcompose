<?php

/**
 * Project: Welcompose
 * File: globaltemplate.class.php
 * 
 * Copyright (c) 2008 creatics
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id$
 * 
 * @copyright 2008 creatics, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton. Returns instance of the Templating_GlobalTemplate object.
 * 
 * @return object
 */
function Templating_GlobalTemplate ()
{ 
	if (Templating_GlobalTemplate::$instance == null) {
		Templating_GlobalTemplate::$instance = new Templating_GlobalTemplate(); 
	}
	return Templating_GlobalTemplate::$instance;
}

class Templating_GlobalTemplate {
	
	/**
	 * Singleton
	 * 
	 * @var object
	 */
	public static $instance = null;
	
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
public function __construct()
{
	try {
		// get base instance
		$this->base = load('base:base');
		
		// establish database connection
		$this->base->loadClass('database');
		
	} catch (Exception $e) {
		
		// trigger error
		printf('%s on Line %u: Unable to start base class. Reason: %s.', $e->getTemplate(),
			$e->getLine(), $e->getMessage());
		exit;
	}
}

/**
 * Creates new global template. Takes field=>value array with global
 * template data as first argument. Returns insert id.
 * 
 * @throws Templating_GlobalTemplateException
 * @param array Row data
 * @return int Global template id
 */
public function addGlobalTemplate ($sqlData)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalTemplate', 'Manage')) {
		throw new Templating_GlobalTemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Templating_GlobalTemplateException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new global template will be assigned to the current project
	$sqlData['project'] = WCOM_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_TEMPLATING_GLOBAL_TEMPLATES, $sqlData);
	
	// test if global template belongs to current user/project
	if (!$this->globalTemplateBelongsToCurrentUser($insert_id)) {
		throw new Templating_GlobalTemplateException('Global template does not belong to current user or project');
	}
	
	return $insert_id;
}

/**
 * Updates global template. Takes the global template id as first argument,
 * a field=>value array with the new global template data as second
 * argument. Returns amount of affected rows.
 *
 * @throws Templating_GlobalTemplateException
 * @param int Global template id
 * @param array Row data
 * @return int Affected rows
*/
public function updateGlobalTemplate ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalTemplate', 'Manage')) {
		throw new Templating_GlobalTemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_GlobalTemplateException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Templating_GlobalTemplateException('Input for parameter sqlData is not an array');	
	}
	
	// test if global template belongs to current user/project
	if (!$this->globalTemplateBelongsToCurrentUser($id)) {
		throw new Templating_GlobalTemplateException('Global template does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_TEMPLATING_GLOBAL_TEMPLATES, $sqlData,
		$where, $bind_params);
}

/**
 * Removes global template from the global template table. Takes the
 * global template id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Templating_GlobalTemplateException
 * @param int Global template id
 * @return int Amount of affected rows
 */
public function deleteGlobalTemplate ($id)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalTemplate', 'Manage')) {
		throw new Templating_GlobalTemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_GlobalTemplateException('Input for parameter id is not numeric');
	}
	
	// test if global template belongs to current user/project
	if (!$this->globalTemplateBelongsToCurrentUser($id)) {
		throw new Templating_GlobalTemplateException('Global template does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_TEMPLATING_GLOBAL_TEMPLATES,	
		$where, $bind_params);
}

/**
 * Selects one global template. Takes the global template id as first
 * argument. Returns array with global template information.
 * 
 * @throws Templating_GlobalTemplateException
 * @param int Global template id
 * @return array
 */
public function selectGlobalTemplate ($id)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalTemplate', 'Use')) {
		throw new Templating_GlobalTemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_GlobalTemplateException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT
			`templating_global_templates`.`id` AS `id`,
			`templating_global_templates`.`project` AS `project`,
			`templating_global_templates`.`name` AS `name`,
			`templating_global_templates`.`description` AS `description`,
			`templating_global_templates`.`content` AS `content`,
			`templating_global_templates`.`mime_type` AS `mime_type`,
			`templating_global_templates`.`change_delimiter` AS `change_delimiter`,
			`templating_global_templates`.`date_modified` AS `date_modified`,
			`templating_global_templates`.`date_added` AS `date_added`
		FROM
			".WCOM_DB_TEMPLATING_GLOBAL_TEMPLATES." AS `templating_global_templates`
		WHERE 
			`templating_global_templates`.`id` = :id
		  AND
			`templating_global_templates`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more global templates. Takes key=>value
 * array with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Templating_GlobalTemplateException
 * @param array Select params
 * @return array
 */
public function selectGlobalTemplates ($params = array())
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalTemplate', 'Use')) {
		throw new Templating_GlobalTemplateException("You are not allowed to perform this action");
	}
	
	// define some vars
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Templating_GlobalTemplateException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Templating_GlobalTemplateException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT
			`templating_global_templates`.`id` AS `id`,
			`templating_global_templates`.`project` AS `project`,
			`templating_global_templates`.`name` AS `name`,
			`templating_global_templates`.`description` AS `description`,
			`templating_global_templates`.`content` AS `content`,
			`templating_global_templates`.`mime_type` AS `mime_type`,
			`templating_global_templates`.`change_delimiter` AS `change_delimiter`,
			`templating_global_templates`.`date_modified` AS `date_modified`,
			`templating_global_templates`.`date_added` AS `date_added`
		FROM
			".WCOM_DB_TEMPLATING_GLOBAL_TEMPLATES." AS `templating_global_templates`
		WHERE
			`templating_global_templates`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add sorting
	$sql .= " ORDER BY `templating_global_templates`.`name` ";
	
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
 * Fetches global template from database for usage in the smarty resource plugin.
 * Takes the global template name as first argument. Returns array.
 *
 * @throws Templating_GlobalTemplateException
 * @param string Name
 * @return array
 */
public function smartyFetchGlobalTemplate ($name)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalTemplate', 'Use')) {
		throw new Templating_GlobalTemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name) || !is_scalar($name)) {
		throw new Templating_GlobalTemplateException('Input for parameter name is not scalar');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT
			`templating_global_templates`.`id` AS `id`,
			`templating_global_templates`.`project` AS `project`,
			`templating_global_templates`.`name` AS `name`,
			`templating_global_templates`.`description` AS `description`,
			`templating_global_templates`.`content` AS `content`,
			`templating_global_templates`.`mime_type` AS `mime_type`,
			`templating_global_templates`.`change_delimiter` AS `change_delimiter`,
			`templating_global_templates`.`date_modified` AS `date_modified`,
			`templating_global_templates`.`date_added` AS `date_added`
		FROM
			".WCOM_DB_TEMPLATING_GLOBAL_TEMPLATES." AS `templating_global_templates`
		WHERE 
			`templating_global_templates`.`name` = :name
		  AND
			`templating_global_templates`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'name' => (string)$name,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Fetches last modification date of  global template from database for usage in the
 * smarty resource plugin. Takes the global template name as first argument.
 * Returns UNIX timestamp.
 *
 * @throws Templating_GlobalTemplateException
 * @param string Name
 * @return int
 */
public function smartyFetchGlobalTemplateTimestamp ($name)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalTemplate', 'Use')) {
		throw new Templating_GlobalTemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name) || !is_scalar($name)) {
		throw new Templating_GlobalTemplateException('Input for parameter name is not scalar');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT
			UNIX_TIMESTAMP(`templating_global_templates`.`date_modified`) AS `date_modified`
		FROM
			".WCOM_DB_TEMPLATING_GLOBAL_TEMPLATES." AS `templating_global_templates`
		WHERE 
			`templating_global_templates`.`name` = :name
		  AND
			`templating_global_templates`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'name' => (string)$name,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Method to count global templates. Takes key=>value array with count params as
 * first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>n/a</li>
 * </ul>
 * 
 * @throws Templating_GlobalTemplateException
 * @param array Count params
 * @return array
 */
public function countGlobalTemplates ($params = array())
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalTemplate', 'Use')) {
		throw new Templating_GlobalTemplateException("You are not allowed to perform this action");
	}
	
	// define some vars
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Templating_GlobalTemplateException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			default:
				throw new Templating_GlobalTemplateException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_TEMPLATING_GLOBAL_TEMPLATES." AS `templating_global_templates`
		WHERE
			`templating_global_templates`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Checks whether the given global template belongs to the current project or not.
 * Takes the id of the global template as first argument. Returns bool.
 *
 * @throws Templating_GlobalTemplateException
 * @param int Global template id
 * @return bool
 */
public function globalTemplateBelongsToCurrentProject ($template)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalTemplate', 'Use')) {
		throw new Templating_GlobalTemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($template) || !is_numeric($template)) {
		throw new Templating_GlobalTemplateException('Input for parameter template is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".WCOM_DB_TEMPLATING_GLOBAL_TEMPLATES." AS `templating_global_templates`
		WHERE
			`templating_global_templates`.`id` = :template
		  AND
			`templating_global_templates`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'template' => (int)$template,
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
 * Tests whether global template belongs to current user or not. Takes
 * the global template id as first argument. Returns bool.
 *
 * @throws Templating_GlobalTemplateException
 * @param int Global template id
 * @return bool
 */
public function globalTemplateBelongsToCurrentUser ($global_template)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalTemplate', 'Use')) {
		throw new Templating_GlobalTemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($global_template) || !is_numeric($global_template)) {
		throw new Templating_GlobalTemplateException('Input for parameter global is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->globalTemplateBelongsToCurrentProject($global_template)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

/**
 * Tests given global template name for uniqueness. Takes the global template
 * name as first argument and an optional global template id as second argument.
 * If the global template id is given, this global template won't be considered
 * when checking for uniqueness (useful for updates). Returns boolean true if
 * global template name is unique.
 *
 * @throws Templating_GlobalTemplateException
 * @param string Global template name
 * @param int Global template id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!wcom_check_access('Templating', 'GlobalTemplate', 'Use')) {
		throw new Templating_GlobalTemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Templating_GlobalTemplateException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Templating_GlobalTemplateException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Templating_GlobalTemplateException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_TEMPLATING_GLOBAL_TEMPLATES." AS `templating_global_templates`
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

// end of class
}

class Templating_GlobalTemplateException extends Exception { }

?>