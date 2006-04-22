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
 * $Id: template.class.php 2 2006-03-20 11:43:20Z andreas $
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
	return $this->base->db->insert(OAK_DB_TEMPLATING_TEMPLATES, $sqlData);
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
		throw new Templating_TemplateException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Templating_TemplateException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(OAK_DB_TEMPLATING_TEMPLATES, $sqlData,
		$where, $bind_params);	
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
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_TEMPLATING_TEMPLATES,	
		$where, $bind_params);
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
			`templating_template_types`.`name` AS `type_name`
		FROM
			".OAK_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		LEFT JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		  ON
			`templating_templates`.`type` = `templating_template_types`.`id`
		WHERE 
			1
	";
	
	// prepare where clauses
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `templating_templates`.`id` = :id ";
		$bind_params['id'] = (int)$id;
	}
	
	// add limits
	$sql .= ' LIMIT 1';
	
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
			`templating_template_types`.`name` AS `type_name`
		FROM
			".OAK_DB_TEMPLATING_TEMPLATES." AS `templating_templates`
		LEFT JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_TYPES." AS `templating_template_types`
		  ON
			`templating_templates`.`type` = `templating_template_types`.`id`
		LEFT JOIN
			".OAK_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES." AS `tts2tt`
		ON
			`templating_templates`.`id` = `tts2tt`.`template`
		WHERE
			1
	";
	
	// add where clauses
	if (!empty($type) && is_numeric($type)) {
		$sql .= " AND `templating_template_types`.`id` = :type ";
		$bind_params['type'] = $type;
	}
	if (!empty($set) && is_numeric($set)) {
		$sql .= " AND `tts2tt`.`set` = :set ";
		$bind_params['set'] = $set;
	}
	
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
	
	// prepare where clause
	$where = " WHERE `template` = :template ";
	
	// prepare bind params
	$bind_params = array(
		'template' => $template
	);
	
	// remove all existing links to the current template
	$this->base->db->delete(OAK_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES,
		$where, $bind_params);
	
	// add new links
	foreach ($sets as $_set) {
		if (!empty($_set) && is_numeric($_set)) {
			$this->base->db->insert(OAK_DB_TEMPLATING_TEMPLATE_SETS2TEMPLATING_TEMPLATES,
				array('template' => $template, 'set' => $_set));
		}
	}
	
	return true;
}

// end of class
}

class Templating_TemplateException extends Exception { }

?>