<?php

/**
 * Project: Welcompose
 * File: textmacro.class.php
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

class Application_Textmacro {
	
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
 * Singleton. Returns instance of the Application_Textmacro object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Application_Textmacro::$instance == null) {
		Application_Textmacro::$instance = new Application_Textmacro(); 
	}
	return Application_Textmacro::$instance;
}

/**
 * Adds text macro to the text macro table. Takes a field=>value
 * array with text macro data as first argument. Returns insert id. 
 * 
 * @throws Application_TextmacroException
 * @param array Row data
 * @return int Insert id
 */
public function addTextMacro ($sqlData)
{
	// access check
	if (!wcom_check_access('Application', 'TextMacro', 'Manage')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Application_TextmacroException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new text macro will be assigned to the current project
	$sqlData['project'] = WCOM_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_APPLICATION_TEXT_MACROS, $sqlData);
	
	// test if macro belongs to current project/user
	if (!$this->textMacroBelongsToCurrentUser($insert_id)) {
		throw new Application_TextmacroException("Created text macro does not belong to current user or project");
	}
	
	// return insert id
	return (int)$insert_id;
}

/**
 * Updates text macro. Takes the text macro id as first argument, a
 * field=>value array with the new text macro data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Application_TextmacroException
 * @param int Text macro id
 * @param array Row data
 * @return int Affected rows
*/
public function updateTextMacro ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Application', 'TextMacro', 'Manage')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_TextmacroException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Application_TextmacroException('Input for parameter sqlData is not an array');	
	}
	
	// test if macro belongs to current project/user
	if (!$this->textMacroBelongsToCurrentUser($id)) {
		throw new Application_TextmacroException("Text macro does not belong to current user or project");
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_APPLICATION_TEXT_MACROS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes text macro from the text macros table. Takes the
 * text macro id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Application_TextmacroException
 * @param int Text macro id
 * @return int Amount of affected rows
 */
public function deleteTextMacro ($id)
{
	// access check
	if (!wcom_check_access('Application', 'TextMacro', 'Manage')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_TextmacroException('Input for parameter id is not numeric');
	}
	
	// test if macro belongs to current project/user
	if (!$this->textMacroBelongsToCurrentUser($id)) {
		throw new Application_TextmacroException("Text macro does not belong to current user or project");
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_APPLICATION_TEXT_MACROS, $where, $bind_params);
}

/**
 * Selects one text macro. Takes the text macro id as first
 * argument. Returns array with text macro information.
 * 
 * @throws Application_TextmacroException
 * @param int Text macro id
 * @return array
 */
public function selectTextMacro ($id)
{
	// access check
	if (!wcom_check_access('Application', 'TextMacro', 'Use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_TextmacroException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`application_text_macros`.`id` AS `id`,
			`application_text_macros`.`project` AS `project`,
			`application_text_macros`.`name` AS `name`,
			`application_text_macros`.`internal_name` AS `internal_name`,
			`application_text_macros`.`type` AS `type`
		FROM
			".WCOM_DB_APPLICATION_TEXT_MACROS." AS `application_text_macros`
		WHERE 
			`application_text_macros`.`id` = :id
		  AND
			`application_text_macros`.`project` = :project
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
 * Method to select one or more text macros. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>type, string, optional: text macro type (pre, post, startup,
 *     shutdown)</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Application_TextmacroException
 * @param array Select params
 * @return array
 */
public function selectTextMacros ($params = array())
{
	// access check
	if (!wcom_check_access('Application', 'TextMacro', 'Use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// define some vars
	$type = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Application_TextmacroException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			case 'type':
					$$_key = (string)$_value;
				break;
			default:
				throw new Application_TextmacroException("Unknown parameter $_key");
		}
	}
	
	// check input for type 
	switch ((string)$type) {
		case "":
		case "pre":
		case "post":
		case "startup":
		case "shutdown":
			break;
		default:
			throw new Application_TextmacroException("Input for parameter type is out of range");
	}
	
	// prepare query
	$sql = "
		SELECT 
			`application_text_macros`.`id` AS `id`,
			`application_text_macros`.`project` AS `project`,
			`application_text_macros`.`name` AS `name`,
			`application_text_macros`.`internal_name` AS `internal_name`,
			`application_text_macros`.`type` AS `type`
		FROM
			".WCOM_DB_APPLICATION_TEXT_MACROS." AS `application_text_macros`
		WHERE 
			`application_text_macros`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($type)) {
		$sql .= " AND `application_text_macros`.`type` = :type ";
		$bind_params['type'] = $type;
	}
	
	// add sorting
	$sql .= " ORDER BY `application_text_macros`.`name` ";
	
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
 * Method to count the existing text macros. Takes key=>value
 * array with count params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * No params supported.
 * 
 * @throws Application_TextmacroException
 * @param array Count params
 * @return array
 */
public function countTextMacros ($params = array())
{
	// access check
	if (!wcom_check_access('Application', 'TextMacro', 'Use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// define some vars
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Application_TextmacroException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			default:
				throw new Application_TextmacroException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_APPLICATION_TEXT_MACROS." AS `application_text_macros`
		WHERE 
			`application_text_macros`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Tests given text macro name for uniqueness. Takes the text macro
 * name as first argument and an optional text macro id as second argument.
 * If the text macro id is given, this text macro won't be considered
 * when checking for uniqueness (useful for updates). Returns boolean true if
 * text macro name is unique.
 *
 * @throws Application_TextmacroException
 * @param string Text macro name
 * @param int Text macro id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!wcom_check_access('Application', 'TextMacro', 'Use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Application_TextmacroException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Application_TextmacroException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Application_TextmacroException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_APPLICATION_TEXT_MACROS." AS `application_text_macros`
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
 * Applies text macro to the given text. Takes the text macro id
 * as first argument, the string with the text to convert as second
 * argument. Returns converted string.
 *
 * @throws Application_TextmacroException
 * @param int Text macro id
 * @param string Text to convert
 * @return string Converted text
 */
public function applyTextMacros ($text, $stage = "pre")
{
	// access check
	if (!wcom_check_access('Application', 'TextMacro', 'Use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_scalar($text)) {
		throw new Application_TextmacroException("Input for parameter text is expected to be scalar");
	}
	if ($stage != "pre" && $stage != "post") {
		throw new Application_TextmacroException("Input for parameter stage is out of range");
	}
	
	// get text macros
	switch ($stage) {
		case 'pre':
				// initialize macro array
				$macros = array(
					'startup' => array(),
					'pre' => array()
				);
				
				// get startup macros
				$startup_macros = $this->selectTextMacros(array(
					'type' => 'startup'
				));
				if (!empty($startup_macros) && is_array($startup_macros)) {
					$macros['startup'] = $startup_macros;
				}
				
				// get pre macros
				$pre_macros = $this->selectTextMacros(array(
					'type' => 'pre'
				));
				if (!empty($pre_macros) && is_array($pre_macros)) {
					$macros['pre'] = $pre_macros;
				}
				
			break;
		case 'post':
				// initialize macro array
				$macros = array(
					'post' => array(),
					'shutdown' => array()
				);
				
				// get post macros
				$post_macros = $this->selectTextMacros(array(
					'type' => 'post'
				));
				if (!empty($post_macros)) {
					$macros['post'] = $post_macros;
				}
				
				// get shutdown macros
				$shutdown_macros = $this->selectTextMacros(array(
					'type' => 'shutdown'
				));
				if (!empty($shutdown_macros)) {
					$macros['shutdown'] = $shutdown_macros;
				}
								
			break;
	}
	
	// let's see if a plug-in path is registred
	if (empty($this->base->_conf['plugins']['textmacro_dir'])) {
		throw new Application_TextmacroException("No text macro plug-in directory configured");
	}
	if (!is_dir($this->base->_conf['plugins']['textmacro_dir'])) {
		throw new Application_TextmacroException("Configured text macro plug-in path is not a directory");
	}
	
	// apply text macros
	foreach ($macros as $_stage => $_macros) {
		foreach ($_macros as $_macro) {
			// test if macro belongs to current project/user
			if (!$this->textMacroBelongsToCurrentUser($_macro['id'])) {
				throw new Application_TextmacroException("Text macro does not belong to current user or project");
			}
			
			// check internal name
			if (empty($_macro['internal_name'])) {
				throw new Application_TextmacroException("No internal text macro name defined");
			}
			if (!preg_match(WCOM_REGEX_TEXT_MACRO_INTERNAL_NAME, $_macro['internal_name'])) {
				throw new Application_TextmacroException("Internal text macro name is invalid");
			}
			
			// prepare path to text macro
			$path = $this->base->_conf['plugins']['textmacro_dir'].DIRECTORY_SEPARATOR.
				"wcom_plugin_textmacro_".$_macro['internal_name'].".php";
			
			if (!file_exists($path)) {
				throw new Application_TextmacroException("Unable to find text macro plug-in");
			}

			// include text macro file
			require_once($path);

			// prepare function name
			$function_name = sprintf("wcom_plugin_textmacro_%s", $_macro['internal_name']);

			// let's see if the text macro function exists
			if (!function_exists($function_name)) {
				throw new Application_TextmacroException("Text macro plug-in function does not exist");
			}
			
			// apply text macro
			$text = call_user_func($function_name, $text);
		}
	}

	return $text; 
}

/**
 * Tests whether given text macro belongs to current project. Takes the
 * text macro id as first argument. Returns bool.
 *
 * @throws Application_TextmacroException
 * @param int Text macro id
 * @return int bool
 */
public function textMacroBelongsToCurrentProject ($text_macro)
{
	// access check
	if (!wcom_check_access('Application', 'TextMacro', 'Use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($text_macro) || !is_numeric($text_macro)) {
		throw new Application_TextmacroException('Input for parameter text_macro is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".WCOM_DB_APPLICATION_TEXT_MACROS." AS `application_text_macros`
		WHERE
			`application_text_macros`.`id` = :text_macro
		AND
			`application_text_macros`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'text_macro' => (int)$text_macro,
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
 * Test whether text macro belongs to current user or not. Takes
 * the text macro id as first argument. Returns bool.
 *
 * @throws Application_TextmacroException
 * @param int Text macro id
 * @return bool
 */
public function textMacroBelongsToCurrentUser ($text_macro)
{
	// access check
	if (!wcom_check_access('Application', 'TextMacro', 'Use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($text_macro) || !is_numeric($text_macro)) {
		throw new Application_TextmacroException('Input for parameter text_macro is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->textMacroBelongsToCurrentProject($text_macro)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Application_TextmacroException extends Exception { }

?>