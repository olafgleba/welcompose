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
	// access check
	if (!oak_check_access('textconverter', 'manage')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Application_TextconverterException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new text converter will be assigned to the current project
	$sqlData['project'] = OAK_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(OAK_DB_APPLICATION_TEXT_CONVERTERS, $sqlData);
	
	// test if created text converter belongs to current user/project
	if (!$this->textConverterBelongsToCurrentUser($insert_id)) {
		throw new Application_TextconverterException("Text converter does not belong to current user or project");
	}
	
	// return insert id
	return (int)$insert_id;
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
	// access check
	if (!oak_check_access('textconverter', 'manage')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_TextconverterException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Application_TextconverterException('Input for parameter sqlData is not an array');	
	}
	
	// test if text converter belongs to current user/project
	if (!$this->textConverterBelongsToCurrentUser($id)) {
		throw new Application_TextconverterException("Text converter does not belong to current user or project");
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
	// access check
	if (!oak_check_access('textconverter', 'manage')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_TextconverterException('Input for parameter id is not numeric');
	}
	
	// test if text converter belongs to current user/project
	if (!$this->textConverterBelongsToCurrentUser($id)) {
		throw new Application_TextconverterException("Text converter does not belong to current user or project");
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
	// access check
	if (!oak_check_access('textconverter', 'use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
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
	// access check
	if (!oak_check_access('textconverter', 'use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
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
	// access check
	if (!oak_check_access('textconverter', 'use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
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
	// access check
	if (!oak_check_access('textconverter', 'use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
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
	// access check
	if (!oak_check_access('textconverter', 'use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
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

/**
 * Applies text converter to the given text. Takes the text converter id
 * as first argument, the string with the text to convert as second
 * argument. Returns converted string.
 *
 * @throws Application_TextconverterException
 * @param int Text converter id
 * @param string Text to convert
 * @return string Converted text
 */
public function applyTextConverter ($id, $text)
{
	// access check
	if (!oak_check_access('textconverter', 'use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_TextconverterException('Input for parameter id is not numeric');
	}
	if (!is_scalar($text)) {
		throw new Application_TextconverterException("Input for parameter text is expected to be scalar");
	}
	
	// test if text converter belongs to current user/project
	if (!$this->textConverterBelongsToCurrentUser($id)) {
		throw new Application_TextconverterException("Text converter does not belong to current user or project");
	}
	
	// get text converter
	$text_converter = $this->selectTextConverter($id);
	
	// let's see if a plug-in path is registred
	if (empty($this->base->_conf['plugins']['textconverter_dir'])) {
		throw new Application_TextconverterException("No text converter plug-in directory configured");
	}
	if (!is_dir($this->base->_conf['plugins']['textconverter_dir'])) {
		throw new Application_TextconverterException("Configured text converter plug-in path is not a directory");
	}
		
	// check text converter
	if (empty($text_converter)) {
		throw new Applicatin_TextconverterException("Requested text converter is not registred");
	}
	if (empty($text_converter['internal_name'])) {
		throw new Application_TextconverterException("No internal text converter name defined");
	}
	if (!preg_match(OAK_REGEX_TEXT_CONVERTER_INTERNAL_NAME, $text_converter['internal_name'])) {
		throw new Application_TextconverterException("Internal text converter name is invalid");
	}
	
	// prepare path to text converter
	$path = $this->base->_conf['plugins']['textconverter_dir'].DIRECTORY_SEPARATOR.
		"oak_plugin_textconverter_".$text_converter['internal_name'].".php";
	if (!file_exists($path)) {
		throw new Application_TextconverterException("Unable to find text converter plug-in");
	}
	
	// include text converter file
	require($path);
	
	// prepare function name
	$function_name = sprintf("oak_plugin_textconverter_%s", $text_converter['internal_name']);
	
	// let's see if the text converter function exists
	if (!function_exists($function_name)) {
		throw new Application_TextconverterException("Text converter plug-in function does not exist");
	}

	// apply text converter
	return call_user_func($function_name, $text);
}

/**
 * Tests whether given text converter belongs to current project. Takes the
 * text converter id as first argument. Returns bool.
 *
 * @throws Application_TextconverterException
 * @param int Text converter id
 * @return int bool
 */
public function textConverterBelongsToCurrentProject ($text_converter)
{
	// access check
	if (!oak_check_access('textconverter', 'use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($text_converter) || !is_numeric($text_converter)) {
		throw new Application_TextconverterException('Input for parameter text_converter is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".OAK_DB_APPLICATION_TEXT_CONVERTERS." AS `application_text_converters`
		WHERE
			`application_text_converters`.`id` = :text_converter
		AND
			`application_text_converters`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'text_converter' => (int)$text_converter,
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
 * Test whether text converter belongs to current user or not. Takes
 * the text converter id as first argument. Returns bool.
 *
 * @throws Application_TextconverterException
 * @param int Text converter id
 * @return bool
 */
public function textConverterBelongsToCurrentUser ($text_converter)
{
	// access check
	if (!oak_check_access('textconverter', 'use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($text_converter) || !is_numeric($text_converter)) {
		throw new Application_TextconverterException('Input for parameter text_converter is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->textConverterBelongsToCurrentProject($text_converter)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(OAK_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Application_TextconverterException extends Exception { }

?>