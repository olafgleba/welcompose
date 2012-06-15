<?php

/**
 * Project: Welcompose
 * File: template.class.php
 * 
 * Copyright (c) 2008-2012 creatics, Olaf Gleba <og@welcompose.de>
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 *  
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton. Returns instance of the Templating_Template object.
 * 
 * @return object
 */
function Templating_Template ()
{ 
	if (Templating_Template::$instance == null) {
		Templating_Template::$instance = new Templating_Template(); 
	}
	return Templating_Template::$instance;
}

class Templating_Template {
	
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
 * Singleton. Returns instance of the Templating_Template object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Templating_Template::$instance == null) {
		Templating_Template::$instance = new Templating_Template(); 
	}
	return Templating_Template::$instance;
}

/**
 * Creates new template. Takes field=>value array with template
 * data as first argument. Returns insert id.
 * 
 * @throws Templating_TemplateException
 * @param array Row data
 * @return int Template id
 */
public function addTemplate ($sqlData)
{
	// access check
	if (!wcom_check_access('Templating', 'Template', 'Manage')) {
		throw new Templating_TemplateException("You are not allowed to perform this action");
	}
	
	if (!is_array($sqlData)) {
		throw new Templating_TemplateException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_TEMPLATING_TEMPLATES, $sqlData);
	
	// test if the new template belongs to the current user/project
	if (!$this->templateBelongsToCurrentUser($insert_id)) {
		throw new Templating_TemplateException('Template does not belong to current project or user');
	}
	
	return $insert_id;
}

/**
 * Updates template. Takes the template id as first argument,
 * a field=>value array with the new template data as second
 * argument. Returns amount of affected rows.
 *
 * @throws Templating_TemplateException
 * @param int Template id
 * @param array Row data
 * @return int Affected rows
*/
public function updateTemplate ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Templating', 'Template', 'Manage')) {
		throw new Templating_TemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_TemplateException('Input for parameter id is not numeric');
	}
	if (!is_array($sqlData)) {
		throw new Templating_TemplateException('Input for parameter sqlData is not an array');	
	}
	
	// test if the new template belongs to the current user/project
	if (!$this->templateBelongsToCurrentUser($id)) {
		throw new Templating_TemplateException('Template does not belong to current project or user');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	$affected_rows = $this->base->db->update(WCOM_DB_TEMPLATING_TEMPLATES, $sqlData,
		$where, $bind_params);
	
	return $affected_rows;
}

/**
 * Removes template from the template table. Takes the template 
 * id as first argument. Returns amount of affected rows.
 * 
 * @throws Templating_TemplateException
 * @param int Template id
 * @return int Amount of affected rows
 */
public function deleteTemplate ($id)
{
	// access check
	if (!wcom_check_access('Templating', 'Template', 'Manage')) {
		throw new Templating_TemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_TemplateException('Input for parameter id is not numeric');
	}
	
	// test if the new template belongs to the current user/project
	if (!$this->templateBelongsToCurrentUser($id)) {
		throw new Templating_TemplateException('Template does not belong to current project or user');
	}
	
	// prepare query
	$sql = "
		DELETE `templating_templates` FROM
			".WCOM_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		JOIN
			".WCOM_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		  ON
			`templating_templates`.`type` = `templating_template_types`.`id`
		WHERE
			`templating_templates`.`id` = :id
		  AND
			`templating_template_types`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->execute($sql, $bind_params);
}

/**
 * Selects one template. Takes the template id as first
 * argument. Returns array with template information.
 * 
 * @throws Templating_TemplateException
 * @param int Template id
 * @return array
 */
public function selectTemplate ($id)
{
	// access check
	if (!wcom_check_access('Templating', 'Template', 'Use')) {
		throw new Templating_TemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_TemplateException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT
			`templating_templates`.`id` AS `id`,
			`templating_templates`.`type` AS `type`,
			`templating_templates`.`name` AS `name`,
			`templating_templates`.`description` AS `description`,
			`templating_templates`.`content` AS `content`,
			`templating_template_types`.`id` AS `type_id`,
			`templating_template_types`.`project` AS `type_project`,
			`templating_template_types`.`name` AS `type_name`,
			`templating_template_types`.`description` AS `type_description`,
			`templating_template_types`.`editable` AS `type_editable`
		FROM
			".WCOM_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		JOIN
			".WCOM_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		  ON
			`templating_templates`.`type` = `templating_template_types`.`id`
		WHERE 
			`templating_templates`.`id` = :id
		  AND
			`templating_template_types`.`project` = :project
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
 * Method to select one or more templates. Takes key=>value
 * array with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>type, int, optional: Template type id</li>
 * <li>set, int, optional: Template set id</li>
 * <li>start, int, optional: row off</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Templating_TemplateException
 * @param array Select params
 * @return array
 */
public function selectTemplates ($params = array())
{
	// access check
	if (!wcom_check_access('Templating', 'Template', 'Use')) {
		throw new Templating_TemplateException("You are not allowed to perform this action");
	}
	
	// define some vars
	$type = null;
	$set = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Templating_TemplateException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'type':
			case 'set':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Templating_TemplateException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT
			`templating_templates`.`id` AS `id`,
			`templating_templates`.`type` AS `type`,
			`templating_templates`.`name` AS `name`,
			`templating_templates`.`description` AS `description`,
			`templating_templates`.`content` AS `content`,
			`templating_template_types`.`id` AS `type_id`,
			`templating_template_types`.`project` AS `type_project`,
			`templating_template_types`.`name` AS `type_name`,
			`templating_template_types`.`description` AS `type_description`,
			`templating_template_types`.`editable` AS `type_editable`
		FROM
			".WCOM_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		JOIN
			".WCOM_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		  ON
			`templating_templates`.`type` = `templating_template_types`.`id`
		LEFT JOIN
			".WCOM_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES." AS `tts2tt`
		  ON
			`templating_templates`.`id` = `tts2tt`.`template`
		WHERE
			`templating_template_types`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($type) && is_numeric($type)) {
		$sql .= " AND `templating_template_types`.`id` = :type ";
		$bind_params['type'] = $type;
	}
	if (!empty($set) && is_numeric($set)) {
		$sql .= " AND `tts2tt`.`set` = :set ";
		$bind_params['set'] = $set;
	}
	
	// aggregate result set
	$sql .= " GROUP BY `templating_templates`.`id` ";
	
	// add sorting
	$sql .= " ORDER BY `templating_templates`.`name` ";
	
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
 * Method to count templates. Takes key=>value with count params as first
 * argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>type, int, optional: Template type id</li>
 * <li>set, int, optional: Template set id</li>
 * </ul>
 * 
 * @throws Templating_TemplateException
 * @param array Count params
 * @return array
 */
public function countTemplates ($params = array())
{
	// access check
	if (!wcom_check_access('Templating', 'Template', 'Use')) {
		throw new Templating_TemplateException("You are not allowed to perform this action");
	}
	
	// define some vars
	$type = null;
	$set = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Templating_TemplateException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'type':
			case 'set':
					$$_key = (int)$_value;
				break;
			default:
				throw new Templating_TemplateException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(DISTINCT `templating_templates`.`id`) AS `total`
		FROM
			".WCOM_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		JOIN
			".WCOM_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		  ON
			`templating_templates`.`type` = `templating_template_types`.`id`
		LEFT JOIN
			".WCOM_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES." AS `tts2tt`
		  ON
			`templating_templates`.`id` = `tts2tt`.`template`
		WHERE
			`templating_template_types`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($type) && is_numeric($type)) {
		$sql .= " AND `templating_template_types`.`id` = :type ";
		$bind_params['type'] = $type;
	}
	if (!empty($set) && is_numeric($set)) {
		$sql .= " AND `tts2tt`.`set` = :set ";
		$bind_params['set'] = $set;
	}
	
	return $this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Maps template to template sets. Takes template id as first argument,
 * array with list of set ids as second argument. Returns boolean true.
 * 
 * If an empty array is passed as sets, all existing links will be
 * removed.
 *
 * @throws throw new Templating_TemplateException
 * @param int Template id
 * @param array Template set ids
 * @return bool
 */
public function mapTemplateToSets ($template, $sets = array())
{
	// access check
	if (!wcom_check_access('Templating', 'Template', 'Manage')) {
		throw new Templating_TemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($template) || !is_numeric($template)) {
		throw new Templating_TemplateException('Input for parameter template is expected to be a numeric value');
	}
	if (!is_array($sets)) {
		throw new Templating_TemplateException('Input for parameter sets is expected to be an array');	
	}
	
	// let's see if the given template belongs to the current project
	if (!$this->templateBelongsToCurrentProject($template)) {
		throw new Templating_TemplateException('Given template does not belong to the current project');
	}
	
	// load template set class
	$TEMPLATESET = load('templating:templateset');
	
	// prepare query to remove all existing links to the current template
	$sql = "
		DELETE `tts2tt` FROM
			".WCOM_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES." AS `tts2tt`
		JOIN
			".WCOM_DB_TEMPLATING_TEMPLATE_SETS." AS `templating_template_sets`
		  ON
			`tts2tt`.`set` = `templating_template_sets`.`id`
		WHERE
			`tts2tt`.`template` = :template
		AND
			`templating_template_sets`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'template' => (int)$template,
		'project' => (int)WCOM_CURRENT_PROJECT
	);
	
	// remove all existing links to the current template
	$this->base->db->execute($sql, $bind_params);
	
	// add new links
	foreach ($sets as $_set) {
		if (!empty($_set) && is_numeric($_set) && $TEMPLATESET->templateSetBelongsToCurrentUser($_set)) {
			$this->base->db->insert(WCOM_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES,
				array('template' => $template, 'set' => $_set));
		}
	}
	
	return true;
}

/**
 * Selects links between the given template and its associated template sets. Takes
 * the template id as first argument. Returns array.
 *
 * @throws Templating_TemplateException
 * @param int Group id
 * @return array
 */
public function selectTemplateToSetsMap ($template)
{
	// access check
	if (!wcom_check_access('Templating', 'Template', 'Use')) {
		throw new Templating_TemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($template) || !is_numeric($template)) {
		throw new Templating_TemplateException("Input for parameter template is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT
			`tts2tt`.`id` AS `id`,
			`tts2tt`.`template` AS `template`,
			`tts2tt`.`set` AS `set`
		FROM
			".WCOM_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES." AS `tts2tt`
		JOIN
			".WCOM_DB_TEMPLATING_TEMPLATE_SETS." AS `templating_template_sets`
		  ON
			`tts2tt`.`set` = `templating_template_sets`.`id`
		WHERE
			`tts2tt`.`template` = :template
		  AND
			`templating_template_sets`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'template' => (int)$template,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'multi', $bind_params);
}

/**
 * Checks whether the given template belongs to the current project or not. Takes
 * the id of the template as first argument. Returns bool.
 *
 * @throws Templating_TemplateException
 * @param int Template id
 * @return bool
 */
public function templateBelongsToCurrentProject ($template)
{
	// access check
	if (!wcom_check_access('Templating', 'Template', 'Use')) {
		throw new Templating_TemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($template) || !is_numeric($template)) {
		throw new Templating_TemplateException('Input for parameter template is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".WCOM_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		JOIN
			".WCOM_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		  ON
			`templating_templates`.`type` = `templating_template_types`.`id`
		WHERE
			`templating_templates`.`id` = :template
		AND
			`templating_template_types`.`project` = :project
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
 * Tests whether template belongs to current user or not. Takes
 * the template id as first argument. Returns bool.
 *
 * @throws Templating_TemplateException
 * @param int Template id
 * @return bool
 */
public function templateBelongsToCurrentUser ($template)
{
	// access check
	if (!wcom_check_access('Templating', 'Template', 'Use')) {
		throw new Templating_TemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($template) || !is_numeric($template)) {
		throw new Templating_TemplateException('Input for parameter template is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->templateBelongsToCurrentProject($template)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

/**
 * Tests given template name for uniqueness. Takes the template name as
 * first argument and an optional template id as second argument. If
 * the template id is given, this template won't be considered when checking
 * for uniqueness (useful for updates). Returns boolean true if template
 * name is unique.
 *
 * @throws Templating_TemplateException
 * @param string Template name
 * @param int Template id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!wcom_check_access('Templating', 'Template', 'Use')) {
		throw new Templating_TemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Templating_TemplateException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Templating_TemplateException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Templating_TemplateException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		JOIN
			".WCOM_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		  ON
			`templating_templates`.`type` = `templating_template_types`.`id`
		WHERE
			`templating_templates`.`name` = :name
		AND
			`templating_template_types`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT,
		'name' => $name
	);
	
	// if id isn't empty, add id check
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `templating_templates`.`id` != :id ";
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
 * Tests given template type and set combination for uniqueness. Takes the template type as
 * first argument and an optional template id as second argument. To crosscheck the type and
 * set combination the sets POST are used. If the template id is given, this template won't
 * be considered when checking for uniqueness (useful for updates). Returns boolean true if
 * template type and set(s) combination is unique.
 *
 * @throws Templating_TemplateException
 * @param string Template type
 * @param int Template id
 * @return bool
 */
public function testForUniqueTypeAndSet ($type, $id = null)
{
	// access check
	if (!wcom_check_access('Templating', 'Template', 'Use')) {
		throw new Templating_TemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($type)) {
		throw new Templating_TemplateException("Input for parameter type is not expected to be empty");
	}
	if (!is_scalar($type)) {
		throw new Templating_TemplateException("Input for parameter type is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Templating_TemplateException("Input for parameter id is expected to be numeric");
	}
	if (empty($type)) {
		throw new Templating_TemplateException("Input for parameter type is not expected to be empty");
	}
	if (!empty($_POST['sets']) && !is_array($_POST['sets'])) {
		throw new Templating_TemplateException("Input for parameter sets is expected to be an array");
	}
	
	// Process only if sets are not empty
	// Otherwise get on and exit callback without harm the validation.
	if (empty($_POST['sets'])) {
		return true;
		exit;
	} else {
		$_sets = $_POST['sets'];
	}
	
	// implode sets array and populate var for sql query
	foreach ($_sets as $sets) {
		$set .= "'$sets'";	
	}
	$set = str_replace("''", "', '", $set);
	
		
	// prepare query
	$sql = "
		SELECT
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES." AS `tt2tt`
		JOIN
			".WCOM_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		  ON
			`tt2tt`.`template` = `templating_templates`.`id`
		JOIN
			".WCOM_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		  ON
			`templating_templates`.`type` = `templating_template_types`.`id`
		WHERE
			`templating_templates`.`type` = :type
		AND
			`templating_template_types`.`project` = :project
		AND
			`tt2tt`.`set` IN ($set)
	
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT,
		'type' => $type
	);
	
	// if id isn't empty, add id check
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `templating_templates`.`id` != :id ";
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
 * Fetches template for smarty out of the database. The right template
 * will be chosen using the id of the current page and the name of 
 * the template type.
 *
 * Takes the page id as first argument, the name of the template type
 * as second argument. Returns array.
 *
 * @throws Templating_TemplateException
 * @param int Page id
 * @param string Template type name
 * @return array
 */
public function smartyFetchTemplate ($page_id, $template_type_name)
{
	// access check
	if (!wcom_check_access('Templating', 'Template', 'Use')) {
		throw new Templating_TemplateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($page_id) || !is_numeric($page_id)) {
		throw new Templating_TemplateException("Input for parameter page_id is not numeric");
	}
	if (empty($template_type_name) || !is_scalar($template_type_name)) {
		throw new Templating_TemplateException("Input for parameter template_type_name is not scalar");
	}
	
	// prepare query
	$sql = "
		SELECT
			`templating_templates`.`content` AS `content`,
			UNIX_TIMESTAMP(`templating_templates`.`date_modified`) AS `date_modified`
		FROM
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		JOIN
			".WCOM_DB_TEMPLATING_TEMPLATE_SETS." AS `templating_template_sets`
		  ON
			`content_pages`.`template_set` = `templating_template_sets`.`id`
		JOIN
			".WCOM_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES." AS `templating_template_sets2templating_templates`
		  ON
			`templating_template_sets`.`id` = `templating_template_sets2templating_templates`.`set`
		JOIN
			".WCOM_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		  ON
			`templating_template_sets2templating_templates`.`template` = `templating_templates`.`id`
		JOIN
			".WCOM_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		  ON
			`templating_templates`.`type` = `templating_template_types`.`id`		
		WHERE
			`content_pages`.`id` = :page_id
		AND
			`content_pages`.`project` = :project
		AND
			`templating_template_types`.`name` = :template_type_name
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'page_id' => $page_id,
		'project' => WCOM_CURRENT_PROJECT,
		'template_type_name' => $template_type_name
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'multi', $bind_params);
}

// end of class
}

class Templating_TemplateException extends Exception { }

?>