<?php

/**
 * Project: Oak
 * File: textconverter.class.php
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

class Application_Textconverter {
	
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
 * Singleton. Returns instance of the Application_Textconverter object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Application_Textconverter::$instance == null) {
		Application_Textconverter::$instance = new Application_Textconverter(); 
	}
	return Application_Textconverter::$instance;
}

/**
 * Adds text converter to the text converter table. Takes a field=>value
 * array with text converter data as first argument. Returns insert id. 
 * 
 * @throws Application_TextconverterException
 * @param array Row data
 * @return int Insert id
 */
public function addTextConverter ($sqlData)
{
	if (!is_array($sqlData)) {
		throw new Application_TextconverterException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new text converter will be assigned to the current project
	$sqlData['project'] = OAK_CURRENT_PROJECT;
	
	// insert row
	return $this->base->db->insert(OAK_DB_APPLICATION_TEXT_CONVERTERS, $sqlData);
}

/**
 * Updates text converter. Takes the text converter id as first argument, a
 * field=>value array with the new text converter data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Application_TextconverterException
 * @param int Text converter id
 * @param array Row data
 * @return int Affected rows
*/
public function updateTextConverter ($id, $sqlData)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_TextconverterException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Application_TextconverterException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(OAK_DB_APPLICATION_TEXT_CONVERTERS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes text converter from the text converters table. Takes the
 * text converter id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Application_TextconverterException
 * @param int Text converter id
 * @return int Amount of affected rows
 */
public function deleteTextConverter ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_TextconverterException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_APPLICATION_TEXT_CONVERTERS, $where, $bind_params);
}

/**
 * Selects one text converter. Takes the text converter id as first
 * argument. Returns array with text converter information.
 * 
 * @throws Application_TextconverterException
 * @param int Text converter id
 * @return array
 */
public function selectTextConverter ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_TextconverterException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`application_text_converters`.`id` AS `id`,
			`application_text_converters`.`project` AS `project`,
			`application_text_converters`.`internal_name` AS `internal_name`,
			`application_text_converters`.`name` AS `name`
		FROM
			".OAK_DB_APPLICATION_TEXT_CONVERTERS." AS `application_text_converters`
		WHERE 
			`application_text_converters`.`id` = :id
		  AND
			`application_text_converters`.`project` = :project
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
 * Method to select one or more text converters. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Application_TextconverterException
 * @param array Select params
 * @return array
 */
public function selectTextConverters ($params = array())
{
	// define some vars
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Application_TextconverterException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Application_TextconverterException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`application_text_converters`.`id` AS `id`,
			`application_text_converters`.`project` AS `project`,
			`application_text_converters`.`internal_name` AS `internal_name`,
			`application_text_converters`.`name` AS `name`
		FROM
			".OAK_DB_APPLICATION_TEXT_CONVERTERS." AS `application_text_converters`
		WHERE 
			`application_text_converters`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	// add sorting
	$sql .= " ORDER BY `application_text_converters`.`name` ";
	
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
 * Method to count the existing text converters. Takes key=>value
 * array with count params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * No params supported.
 * 
 * @throws Application_TextconverterException
 * @param array Count params
 * @return array
 */
public function countTextConverters ($params = array())
{
	// define some vars
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Application_TextconverterException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			default:
				throw new Application_TextconverterException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_APPLICATION_TEXT_CONVERTERS." AS `application_text_converters`
		WHERE 
			`application_text_converters`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query and return result
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}
/**
 * Tests given text converter name for uniqueness. Takes the text converter
 * name as first argument and an optional text converter id as second argument.
 * If the text converter id is given, this text converter won't be considered
 * when checking for uniqueness (useful for updates). Returns boolean true if
 * text converter name is unique.
 *
 * @throws Application_TextconverterException
 * @param string Text converter name
 * @param int Text converter id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// input check
	if (empty($name)) {
		throw new Application_TextconverterException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Application_TextconverterException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Application_TextconverterException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_APPLICATION_TEXT_CONVERTERS." AS `application_text_converters`
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
 * Tests given text converter internal name for uniqueness. Takes the text
 * converter internal name as first argument and an optional text converter
 * id as second argument. If the text converter id is given, this text converter
 * won't be considered when checking for uniqueness (useful for updates).
 * Returns boolean true if text converter name is unique.
 *
 * @throws Application_TextconverterException
 * @param string Text converter internal name
 * @param int Text converter id
 * @return bool
 */
public function testForUniqueInternalName ($name, $id = null)
{
	// input check
	if (empty($name)) {
		throw new Application_TextconverterException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Application_TextconverterException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Application_TextconverterException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_APPLICATION_TEXT_CONVERTERS." AS `application_text_converters`
		WHERE
			`project` = :project
		  AND
			`internal_name` = :name
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

class Application_TextconverterException extends Exception { }

?>