<?php

/**
 * Project: Oak
 * File: template.class.php
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

class Templating_Template {
	
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
	if (!is_array($sqlData)) {
		throw new Templating_TemplateException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	$insert_id = $this->base->db->insert(OAK_DB_TEMPLATING_TEMPLATES, $sqlData);
	
	// test if the new template belongs to the current project
	if (!$this->templateBelongsToCurrentProject($insert_id)) {
		throw new Templating_TemplateException('Created template does not belong to current project');
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
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_TemplateException('Input for parameter id is not numeric');
	}
	if (!is_array($sqlData)) {
		throw new Templating_TemplateException('Input for parameter sqlData is not an array');	
	}
	
	// let's see if the given template belongs to the current project
	if (!$this->templateBelongsToCurrentProject($id)) {
		throw new Templating_TemplateException('Given template does not belong to the current project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	$affected_rows = $this->base->db->update(OAK_DB_TEMPLATING_TEMPLATES, $sqlData,
		$where, $bind_params);
	
	// test if the new template belongs to the current project
	if (!$this->templateBelongsToCurrentProject($id)) {
		throw new Templating_TemplateException('Created template does not belong to current project');
	}
	
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
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Templating_TemplateException('Input for parameter id is not numeric');
	}
	
	// prepare query
	$sql = "
		DELETE FROM
			".OAK_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		USING
			".OAK_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
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
		'project' => OAK_CURRENT_PROJECT
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
			".OAK_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
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
		'project' => OAK_CURRENT_PROJECT
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
			".OAK_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		  ON
			`templating_templates`.`type` = `templating_template_types`.`id`
		LEFT JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES." AS `tts2tt`
		  ON
			`templating_templates`.`id` = `tts2tt`.`template`
		WHERE
			`templating_template_types`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
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
			".OAK_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		  ON
			`templating_templates`.`type` = `templating_template_types`.`id`
		LEFT JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES." AS `tts2tt`
		  ON
			`templating_templates`.`id` = `tts2tt`.`template`
		WHERE
			`templating_template_types`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
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
		DELETE FROM
			".OAK_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES." AS `tts2tt`
		USING
			".OAK_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES." AS `tts2tt`
		JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_SETS." AS `templating_template_sets`
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
		'project' => (int)OAK_CURRENT_PROJECT
	);
	
	// remove all existing links to the current template
	$this->base->db->execute($sql, $bind_params);
	
	// add new links
	foreach ($sets as $_set) {
		if (!empty($_set) && is_numeric($_set) && $TEMPLATESET->templateSetBelongsToCurrentProject($_set)) {
			$this->base->db->insert(OAK_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES,
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
			".OAK_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES." AS `tts2tt`
		JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_SETS." AS `templating_template_sets`
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
		'project' => OAK_CURRENT_PROJECT
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
	// input check
	if (empty($template) || !is_numeric($template)) {
		throw new Templating_TemplateException('Input for parameter template is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".OAK_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
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
 * Tests given template name for uniqueness. Takes the template name as
 * first argument and an optional template id as second argument. If
 * the template id is given, this template won't be considered when checking
 * for uniqueness (useful for updates). Returns boolean true if template
 * name is unique.
 *
 * @throws Templating_TemplateException
 * @param string Template name
 * @param int Template id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
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
			".OAK_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		  ON
			`templating_templates`.`type` = `templating_template_types`.`id`
		WHERE
			`templating_templates`.`name` = :name
		AND
			`templating_template_types`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT,
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
 * Fetches template for smarty out of the database. The right template
 * will be chosen using the id of the current page and the name of 
 * the template type.
 *
 * Takes the page id as first argument, the name of the template type
 * as second argument. Returns string.
 *
 * @throws Templating_TemplateException
 * @param int Page id
 * @param string Template type name
 * @return string Template
 */
public function smartyFetchTemplate ($page_id, $template_type_name)
{
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
			`templating_templates`.`content` AS `template`
		FROM
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		LEFT JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_SETS." AS `templating_template_sets`
		  ON
			`content_pages`.`template_set` = `templating_template_sets`.`id`
		LEFT JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES." AS `templating_template_sets2templating_templates`
		  ON
			`templating_template_sets`.`id` = `templating_template_sets2templating_templates`.`set`
		LEFT JOIN
			".OAK_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		  ON
			`templating_template_sets2templating_templates`.`template` = `templating_templates`.`id`
		LEFT JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		  ON
			`templating_templates`.`type` = `templating_template_types`.`id`		
		WHERE
			`content_pages`.`id` = :page_id
		AND
			`templating_template_types`.`name` = :template_type_name
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'page_id' => $page_id,
		'template_name' => $template_type_name
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Fetches last modification timestamp of a template. The right template
 * will be chosen using the id of the current page and the name of 
 * the template type.
 *
 * Takes the page id as first argument, the name of the template type
 * as second argument. Returns int.
 *
 * @throws Templating_TemplateException
 * @param int Page id
 * @param string Template type name
 * @return int Unix timestamp
 */
public function smartyFetchTemplateTimestamp ($page_id, $template_type_name)
{
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
			UNIX_TIMESTAMP(`templating_templates`.`date_modified`) AS `timestamp`
		FROM
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		LEFT JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_SETS." AS `templating_template_sets`
		  ON
			`content_pages`.`template_set` = `templating_template_sets`.`id`
		LEFT JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES." AS `templating_template_sets2templating_templates`
		  ON
			`templating_template_sets`.`id` = `templating_template_sets2templating_templates`.`set`
		LEFT JOIN
			".OAK_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		  ON
			`templating_template_sets2templating_templates`.`template` = `templating_templates`.`id`
		LEFT JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		  ON
			`templating_templates`.`type` = `templating_template_types`.`id`		
		WHERE
			`content_pages`.`id` = :page_id
		AND
			`templating_template_types`.`name` = :template_type_name
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'page_id' => $page_id,
		'template_name' => $template_type_name
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'field', $bind_params);
}

// end of class
}

class Templating_TemplateException extends Exception { }

?>