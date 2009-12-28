<?php

/**
 * Project: Welcompose
 * File: textconverter.class.php
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
 * Singleton for Application_TextConverter.
 * 
 * @return object
 */
function Application_TextConverter ()
{
	if (Application_TextConverter::$instance == null) {
		Application_TextConverter::$instance = new Application_TextConverter(); 
	}
	return Application_TextConverter::$instance;
}

class Application_TextConverter {
	
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
	 * Text converter list for forms
	 * 
	 * @var array
	 */
	protected $_text_converter_list = null;

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
 * Adds text converter to the text converter table. Takes a field=>value
 * array with text converter data as first argument. Returns insert id. 
 * 
 * @throws Application_TextConverterException
 * @param array Row data
 * @return int Insert id
 */
public function addTextConverter ($sqlData)
{
	// access check
	if (!wcom_check_access('Application', 'TextConverter', 'Manage')) {
		throw new Application_TextConverterException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Application_TextConverterException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new text converter will be assigned to the current project
	$sqlData['project'] = WCOM_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_APPLICATION_TEXT_CONVERTERS, $sqlData);
	
	// test if created text converter belongs to current user/project
	if (!$this->textConverterBelongsToCurrentUser($insert_id)) {
		throw new Application_TextConverterException("Text converter does not belong to current user or project");
	}
	
	// return insert id
	return (int)$insert_id;
}

/**
 * Updates text converter. Takes the text converter id as first argument, a
 * field=>value array with the new text converter data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Application_TextConverterException
 * @param int Text converter id
 * @param array Row data
 * @return int Affected rows
*/
public function updateTextConverter ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Application', 'TextConverter', 'Manage')) {
		throw new Application_TextConverterException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_TextConverterException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Application_TextConverterException('Input for parameter sqlData is not an array');	
	}
	
	// test if text converter belongs to current user/project
	if (!$this->textConverterBelongsToCurrentUser($id)) {
		throw new Application_TextConverterException("Text converter does not belong to current user or project");
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_APPLICATION_TEXT_CONVERTERS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes text converter from the text converters table. Takes the
 * text converter id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Application_TextConverterException
 * @param int Text converter id
 * @return int Amount of affected rows
 */
public function deleteTextConverter ($id)
{
	// access check
	if (!wcom_check_access('Application', 'TextConverter', 'Manage')) {
		throw new Application_TextConverterException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_TextConverterException('Input for parameter id is not numeric');
	}
	
	// test if text converter belongs to current user/project
	if (!$this->textConverterBelongsToCurrentUser($id)) {
		throw new Application_TextConverterException("Text converter does not belong to current user or project");
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_APPLICATION_TEXT_CONVERTERS, $where, $bind_params);
}

/**
 * Selects one text converter. Takes the text converter id as first
 * argument. Returns array with text converter information.
 * 
 * @throws Application_TextConverterException
 * @param int Text converter id
 * @return array
 */
public function selectTextConverter ($id)
{
	// access check
	if (!wcom_check_access('Application', 'TextConverter', 'Use')) {
		throw new Application_TextConverterException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_TextConverterException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`application_text_converters`.`id` AS `id`,
			`application_text_converters`.`project` AS `project`,
			`application_text_converters`.`internal_name` AS `internal_name`,
			`application_text_converters`.`name` AS `name`,
			`application_text_converters`.`default` AS `default`
		FROM
			".WCOM_DB_APPLICATION_TEXT_CONVERTERS." AS `application_text_converters`
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
		'project' => WCOM_CURRENT_PROJECT
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
 * @throws Application_TextConverterException
 * @param array Select params
 * @return array
 */
public function selectTextConverters ($params = array())
{
	// access check
	if (!wcom_check_access('Application', 'TextConverter', 'Use')) {
		throw new Application_TextConverterException("You are not allowed to perform this action");
	}
	
	// define some vars
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Application_TextConverterException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Application_TextConverterException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`application_text_converters`.`id` AS `id`,
			`application_text_converters`.`project` AS `project`,
			`application_text_converters`.`internal_name` AS `internal_name`,
			`application_text_converters`.`name` AS `name`,
			`application_text_converters`.`default` AS `default`
		FROM
			".WCOM_DB_APPLICATION_TEXT_CONVERTERS." AS `application_text_converters`
		WHERE 
			`application_text_converters`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
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
 * @throws Application_TextConverterException
 * @param array Count params
 * @return array
 */
public function countTextConverters ($params = array())
{
	// access check
	if (!wcom_check_access('Application', 'TextConverter', 'Use')) {
		throw new Application_TextConverterException("You are not allowed to perform this action");
	}
	
	// define some vars
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Application_TextConverterException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			default:
				throw new Application_TextConverterException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_APPLICATION_TEXT_CONVERTERS." AS `application_text_converters`
		WHERE 
			`application_text_converters`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Selects default text converter. Returns array with the complete text converter
 * information.
 *
 * @throws Application_TextConverterException
 * @return array
 */
public function selectDefaultTextConverter ()
{
	// access check
	if (!wcom_check_access('Application', 'TextConverter', 'Use')) {
		throw new Application_TextConverterException("You are not allowed to perform this action");
	}
	
	// get id of the default text converter
	$sql = "
		SELECT 
			`application_text_converters`.`id` AS `id`
		FROM
			".WCOM_DB_APPLICATION_TEXT_CONVERTERS." AS `application_text_converters`
		WHERE
			`application_text_converters`.`default` = '1'
		  AND
			`application_text_converters`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query
	$result = (int)$this->base->db->select($sql, 'field', $bind_params);
	
	// if no result set is found return false otherwise
	// return complete text converter information
	if ($result < 1) {
		return 0;
	} else {
		return $this->selectTextConverter($result);
	}
}


/**
 * Sets the default text converter to given converter id. Takes the converter id as
 * first argument. Returns amount of affected rows. 
 * 
 * @throws Application_TextConverterException
 * @param int Text converter id
 * @return int Affected rows
 */
public function setDefaultTextConverter ($textconverter)
{
	// access check
	if (!wcom_check_access('Application', 'TextConverter', 'Use')) {
		throw new Application_TextConverterException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($textconverter) || !is_numeric($textconverter)) {
		throw new Application_TextConverterException('Input for parameter textconverter is expected to be a numeric value');
	}
	
	// unset all existing index pages.
	$sql = "
		UPDATE
			".WCOM_DB_APPLICATION_TEXT_CONVERTERS." 
		SET
			`default` = '0'
		WHERE
			`default` = '1'
		  AND
			`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute update
	$this->base->db->execute($sql, $bind_params);
	
	// set given textconverter as default
	return $this->updateTextConverter($textconverter, array('default' => '1'));
}

/**
 * Tests given text converter name for uniqueness. Takes the text converter
 * name as first argument and an optional text converter id as second argument.
 * If the text converter id is given, this text converter won't be considered
 * when checking for uniqueness (useful for updates). Returns boolean true if
 * text converter name is unique.
 *
 * @throws Application_TextConverterException
 * @param string Text converter name
 * @param int Text converter id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!wcom_check_access('Application', 'TextConverter', 'Use')) {
		throw new Application_TextConverterException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Application_TextConverterException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Application_TextConverterException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Application_TextConverterException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_APPLICATION_TEXT_CONVERTERS." AS `application_text_converters`
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
 * Tests given text converter internal name for uniqueness. Takes the text
 * converter internal name as first argument and an optional text converter
 * id as second argument. If the text converter id is given, this text converter
 * won't be considered when checking for uniqueness (useful for updates).
 * Returns boolean true if text converter name is unique.
 *
 * @throws Application_TextConverterException
 * @param string Text converter internal name
 * @param int Text converter id
 * @return bool
 */
public function testForUniqueInternalName ($name, $id = null)
{
	// access check
	if (!wcom_check_access('Application', 'TextConverter', 'Use')) {
		throw new Application_TextConverterException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Application_TextConverterException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Application_TextConverterException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Application_TextConverterException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_APPLICATION_TEXT_CONVERTERS." AS `application_text_converters`
		WHERE
			`project` = :project
		  AND
			`internal_name` = :name
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
 * Applies text converter to the given text. Takes the text converter id
 * as first argument, the string with the text to convert as second
 * argument. Returns converted string.
 *
 * @throws Application_TextConverterException
 * @param int Text converter id
 * @param string Text to convert
 * @return string Converted text
 */
public function applyTextConverter ($id, $text)
{
	// access check
	if (!wcom_check_access('Application', 'TextConverter', 'Use')) {
		throw new Application_TextConverterException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_TextConverterException('Input for parameter id is not numeric');
	}
	if (!is_scalar($text)) {
		throw new Application_TextConverterException("Input for parameter text is expected to be scalar");
	}
	
	// test if text converter belongs to current user/project
	if (!$this->textConverterBelongsToCurrentUser($id)) {
		throw new Application_TextConverterException("Text converter does not belong to current user or project");
	}
	
	// get text converter
	$text_converter = $this->selectTextConverter($id);
	
	// let's see if a plug-in path is registred
	if (empty($this->base->_conf['plugins']['textconverter_dir'])) {
		throw new Application_TextConverterException("No text converter plug-in directory configured");
	}
	if (!is_dir($this->base->_conf['plugins']['textconverter_dir'])) {
		throw new Application_TextConverterException("Configured text converter plug-in path is not a directory");
	}
		
	// check text converter
	if (empty($text_converter)) {
		throw new Applicatin_TextconverterException("Requested text converter is not registred");
	}
	if (empty($text_converter['internal_name'])) {
		throw new Application_TextConverterException("No internal text converter name defined");
	}
	if (!preg_match(WCOM_REGEX_TEXT_CONVERTER_INTERNAL_NAME, $text_converter['internal_name'])) {
		throw new Application_TextConverterException("Internal text converter name is invalid");
	}
	
	// prepare class name
	$class_name = "TextConverter_".$text_converter['internal_name'];
	
	// load text converter plugin
	if (!class_exists($class_name)) {
		$path = $this->base->_conf['plugins']['textconverter_dir'].DIRECTORY_SEPARATOR.
			"wcom_plugin_textconverter_".strtolower($text_converter['internal_name']).".php";
		require($path);
	}
	
	// let's see if the text converter class exists
	if (!class_exists($class_name)) {
		throw new Application_TextConverterException("Text converter plug-in class does not exist");
	}
	
	// load class
	$t = new $class_name();
	
	// apply text converter
	return $t->apply($text);
}

/**
 * Provides interface between media manager and insert callbacks provided
 * by the text converter plugins. Is required to insert media objects into
 * the pages using the text converter specific syntax. 
 *
 * Takes the text converter id as first argument, the name of the callback
 * function (without the prefix mmInsert) as second argument and the arguments
 * for the callback function in an array as third argument. Returns string.
 * 
 * @throws Application_TextConverterException
 * @param int Text converter id
 * @param string Callback name
 * @param array Callback args
 * @return string 
 */
public function insertCallback ($id, $callback, $args)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_TextConverterException('Input for parameter id is expected to be numeric');
	}
	if (empty($callback) || !preg_match(WCOM_REGEX_TEXT_CONVERTER_CALLBACK, $callback)) {
		throw new Application_TextConverterException('No or invalid text converter callback supplied');
	}
	if (!is_array($args)) {
		throw new Application_TextConverterException('Input for parameter args is expected to be an array');
	}
	
	// get text converter
	$text_converter = $this->selectTextConverter($id);
	if (empty($text_converter)) {
		throw new Application_TextConverterException('Selected text converter does not exist');
	}
	
	// load text converter file
	$path_parts = array(
		$this->base->_conf['plugins']['textconverter_dir'],
		sprintf('wcom_plugin_textconverter_%s.php', strtolower($text_converter['internal_name']))
	);
	require_once(implode(DIRECTORY_SEPARATOR, $path_parts));
	
	// prepare text converter name
	$text_converter_name = sprintf('TextConverter_%s', $text_converter['internal_name']);
	if (!class_exists($text_converter_name)) {
		throw new ApplicationTextConverterException('Text converter class does not exist');
	}
	
	// load text converter and prepare callback
	$t = new $text_converter_name();
	$callback = sprintf('mmInsert%s', $callback);
	
	// execute callback
	return call_user_func_array(array($t, $callback), $args);
}

/**
 * Returns list of text converters for usage in quickform selects.
 *
 * @return array
 */
public function getTextConverterListForForm ()
{
	if (is_array($this->_text_converter_list)) {
		return $this->_text_converter_list;
	}
	
	// initialize text converter array
	$this->_text_converter_list = array();
	
	// build text converter list
	foreach ($this->selectTextConverters() as $_converter) {
		$this->_text_converter_list[(int)$_converter['id']] = htmlspecialchars($_converter['name']);
	}
	
	// get default (xhtml) on top
	ksort($this->_text_converter_list);
	
	// return converter list
	return $this->_text_converter_list;
}

/**
 * Tests whether given text converter belongs to current project. Takes the
 * text converter id as first argument. Returns bool.
 *
 * @throws Application_TextConverterException
 * @param int Text converter id
 * @return int bool
 */
public function textConverterBelongsToCurrentProject ($text_converter)
{
	// access check
	if (!wcom_check_access('Application', 'TextConverter', 'Use')) {
		throw new Application_TextConverterException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($text_converter) || !is_numeric($text_converter)) {
		throw new Application_TextConverterException('Input for parameter text_converter is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".WCOM_DB_APPLICATION_TEXT_CONVERTERS." AS `application_text_converters`
		WHERE
			`application_text_converters`.`id` = :text_converter
		AND
			`application_text_converters`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'text_converter' => (int)$text_converter,
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
 * Test whether text converter belongs to current user or not. Takes
 * the text converter id as first argument. Returns bool.
 *
 * @throws Application_TextConverterException
 * @param int Text converter id
 * @return bool
 */
public function textConverterBelongsToCurrentUser ($text_converter)
{
	// access check
	if (!wcom_check_access('Application', 'TextConverter', 'Use')) {
		throw new Application_TextConverterException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($text_converter) || !is_numeric($text_converter)) {
		throw new Application_TextConverterException('Input for parameter text_converter is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->textConverterBelongsToCurrentProject($text_converter)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Application_TextConverterException extends Exception { }

?>