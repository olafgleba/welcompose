<?php

/**
 * Project: Welcompose
 * File: abbreviation.class.php
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
 * @author Olaf Gleba
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton for Content_Abbreviation.
 * 
 * @return object
 */
function Content_Abbreviation ()
{
	if (Content_Abbreviation::$instance == null) {
		Content_Abbreviation::$instance = new Content_Abbreviation(); 
	}
	return Content_Abbreviation::$instance;
}

class Content_Abbreviation {
	
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
 * Adds abbreviation to the abbreviations table. Takes a field=>value array with
 * abbreviation data as first argument. Returns insert id. 
 * 
 * @throws Content_AbbreviationException
 * @param array Row data
 * @return int Abbreviation id
 */
public function addAbbreviation ($sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'Abbreviation', 'Manage')) {
		throw new Content_AbbreviationException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Content_AbbreviationException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new abbreviation will be linked to the current project
	$sqlData['project'] = WCOM_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_CONTENT_ABBREVIATIONS, $sqlData);
	
	// test if the abbreviation belongs to current user/project
	if (!$this->abbreviationBelongsToCurrentUser($insert_id)) {
		throw new Content_AbbreviationException('Abbreviation does not belong to current user/project');
	}
	
	return $insert_id;
}

/**
 * Updates abbreviation. Takes the abbreviation id as first argument, a
 * field=>value array with the new abbreviation data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_AbbreviationException
 * @param int Abbreviation id
 * @param array Row data
 * @return int Affected rows
*/
public function updateAbbreviation ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'Abbreviation', 'Manage')) {
		throw new Content_AbbreviationException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_AbbreviationException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_AbbreviationException('Input for parameter sqlData is not an array');	
	}
	
	// test if abbreviation belongs to current user/project
	if (!$this->abbreviationBelongsToCurrentUser($id)) {
		throw new Content_AbbreviationException('Abbreviation does not belong to current user/project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_CONTENT_ABBREVIATIONS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes abbreviation from the abbreviation table. Takes the abbreviation id
 * as first argument. Returns amount of affected rows.
 * 
 * @throws Content_AbbreviationException
 * @param int Abbreviation id
 * @return int Amount of affected rows
 */
public function deleteAbbreviation ($id)
{
	// access check
	if (!wcom_check_access('Content', 'Abbreviation', 'Manage')) {
		throw new Content_AbbreviationException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_AbbreviationException('Input for parameter id is not numeric');
	}
	
	// test if abbreviation belongs to current user/project
	if (!$this->abbreviationBelongsToCurrentUser($id)) {
		throw new Content_AbbreviationException('Abbreviation does not belong to current user/project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_CONTENT_ABBREVIATIONS, $where, $bind_params);
}

/**
 * Selects one abbreviation. Takes the abbreviation id as first argument.
 * Returns array with abbreviation information.
 * 
 * @throws Content_AbbreviationException
 * @param int Abbreviation id
 * @return array
 */
public function selectAbbreviation ($id)
{
	// access check
	if (!wcom_check_access('Content', 'Abbreviation', 'Use')) {
		throw new Content_AbbreviationException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_AbbreviationException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`content_abbreviations`.`id` AS `id`,
			`content_abbreviations`.`project` AS `project`,
			`content_abbreviations`.`name` AS `name`,
			`content_abbreviations`.`first_char` AS `first_char`,
			`content_abbreviations`.`long_form` AS `long_form`,
			`content_abbreviations`.`content` AS `content`,
			`content_abbreviations`.`content_raw` AS `content_raw`,
			`content_abbreviations`.`text_converter` AS `text_converter`,
			`content_abbreviations`.`apply_macros` AS `apply_macros`,
			`content_abbreviations`.`lang` AS `lang`,
			`content_abbreviations`.`date_modified` AS `date_modified`,
			`content_abbreviations`.`date_added` AS `date_added`
		FROM
			".WCOM_DB_CONTENT_ABBREVIATIONS." AS `content_abbreviations`
		WHERE
			`content_abbreviations`.`id` = :id
		  AND
			`content_abbreviations`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => $id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more abbreviations. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>order_marco, string, optional: How to sort the result set.
 * Supported macros:
 *    <ul>
 *        <li>NAME: sort by name</li>
 *        <li>LONG_FORM: sort by long_form</li>
 *		  <li>GLOSSARY_FORM: sort by glossar_form</li>
 *        <li>DATE_MODIFIED: sort by date modified</li>
 *        <li>DATE_ADDED: sort by date added</li>
 *    </ul>
 * </li>
 * <li>long_form, string, optional: compare the occurence of long_form content</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Content_AbbreviationException
 * @param array Select params
 * @return array
 */
public function selectAbbreviations ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'Abbreviation', 'Use')) {
		throw new Content_AbbreviationException("You are not allowed to perform this action");
	}
	
	// define some vars
	$order_macro = null;
	$long_form = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_AbbreviationException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'order_macro':
			case 'long_form':
					$$_key = (string)$_value;
				break;
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_AbbreviationException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'NAME' => '`name`',
		'LONG_FORM' => '`long_form`',
		'GLOSSARY_FORM' => '`glossary_form`',
		'DATE_ADDED' => '`date_added`',
		'DATE_MODIFIED' => '`date_modified`'
	);
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// prepare query
	$sql = "
			SELECT 
			`content_abbreviations`.`id` AS `id`,
			`content_abbreviations`.`project` AS `project`,
			`content_abbreviations`.`name` AS `name`,
			`content_abbreviations`.`first_char` AS `first_char`,
			`content_abbreviations`.`long_form` AS `long_form`,
			`content_abbreviations`.`content` AS `content`,
			`content_abbreviations`.`content_raw` AS `content_raw`,
			`content_abbreviations`.`text_converter` AS `text_converter`,
			`content_abbreviations`.`apply_macros` AS `apply_macros`,
			`content_abbreviations`.`lang` AS `lang`,
			`content_abbreviations`.`date_modified` AS `date_modified`,
			`content_abbreviations`.`date_added` AS `date_added`
		FROM
			".WCOM_DB_CONTENT_ABBREVIATIONS." AS `content_abbreviations`
		WHERE
			`content_abbreviations`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT 
	);
	
	if (!empty($long_form) && is_scalar($long_form)) {
		$sql .= " AND `content_abbreviations`.`long_form` = :long_form ";
		$bind_params['long_form'] = $long_form;
	}
	
	// add sorting
	if (!empty($order_macro)) {
		$HELPER = load('utility:helper');
		$sql .= " ORDER BY ".$HELPER->_sqlForOrderMacro($order_macro, $macros);
	}
	
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
 * Method to count abbreviations. Takes key=>value array with count params as first
 * argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>none</li>
 * </ul>
 * 
 * @throws Content_AbbreviationException
 * @param array Count params
 * @return array
 */
public function countAbbreviations ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'Abbreviation', 'Use')) {
		throw new Content_AbbreviationException("You are not allowed to perform this action");
	}
	
	// define some vars
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_AbbreviationException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			default:
				throw new Content_AbbreviationException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_ABBREVIATIONS." AS `content_abbreviations`
		WHERE
			`content_abbreviations`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Builds abbreviation internal syntax. Takes key=>value array with count params as first
 * argument. Returns string.
 *
 * @throws Content_AbbreviationException
 * @param array Abbreviation args
 * @return string
 */
public function getAbbreviation ($args = array())
{
	// access check
	if (!wcom_check_access('Content', 'Abbreviation', 'Use')) {
		throw new Content_AbbreviationException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($args)) {
		throw new Content_AbbreviationException('Input for parameter item is not an array');	
	}

	// check existence of param value
	if (empty($args['value'])) {
		throw new Content_AbbreviationException('Parameter value has to be within the array result set');
	}
		
	// get Abbreviation
	$_abbreviation = $this->selectAbbreviation($args['id']);
	
	switch ($args['value']) {
		case 'long_form' :
			$str = $_abbreviation['long_form'];
			break;
		case 'name' :
			$str = $_abbreviation['name'];
			break;
		case 'lang' : 
			$str = $_abbreviation['lang'];
			break;
		default:
			$str = $_abbreviation['name'];
		break;
	}

	// return string
	return $str;		
}


/**
 * Tests given abbreviation long_form for uniqueness. Takes the abbreviation
 * long_form as first argument and an optional abbreviation id as second
 * argument. If the abbreviation id is given, this abbreviation won't be
 * considered when checking for uniqueness (useful for updates).
 * Returns boolean true if abbreviation long_form is unique.
 *
 * @throws Content_AbbreviationException
 * @param string Abbreviation long_form
 * @param int Abbreviation id
 * @return bool
 */
public function testForUniqueLongForm ($long_form, $id = null)
{
	// access check
	if (!wcom_check_access('Content', 'Abbreviation', 'Use')) {
		throw new Content_AbbreviationException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($long_form)) {
		throw new Content_AbbreviationException("Input for parameter long_form is not expected to be empty");
	}
	if (!is_scalar($long_form)) {
		throw new Content_AbbreviationException("Input for parameter long_form is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Content_AbbreviationException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_ABBREVIATIONS." AS `content_abbreviations`
		WHERE
			`project` = :project
		  AND
			`long_form` = :long_form
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT,
		'long_form' => $long_form
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
 * Tests whether given abbreviation belongs to current project. Takes the
 * abbreviation id as first argument. Returns bool.
 *
 * @throws Content_AbbreviationException
 * @param int Abbreviation id
 * @return int bool
 */
public function abbreviationBelongsToCurrentProject ($abbreviation)
{
	// access check
	if (!wcom_check_access('Content', 'Abbreviation', 'Use')) {
		throw new Content_AbbreviationException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($abbreviation) || !is_numeric($abbreviation)) {
		throw new Content_AbbreviationException('Input for parameter abbreviation is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_ABBREVIATIONS." AS `content_abbreviations`
		WHERE
			`content_abbreviations`.`id` = :abbreviation
		  AND
			`content_abbreviations`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'abbreviation' => (int)$abbreviation,
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
 * Test whether abbreviation belongs to current user or not. Takes
 * the abbreviation id as first argument. Returns bool.
 *
 * @throws Content_AbbreviationException
 * @param int Abbreviation id
 * @return bool
 */
public function abbreviationBelongsToCurrentUser ($abbreviation)
{
	// access check
	if (!wcom_check_access('Content', 'Abbreviation', 'Use')) {
		throw new Content_AbbreviationException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($abbreviation) || !is_numeric($abbreviation)) {
		throw new Content_AbbreviationException('Input for parameter abbreviation is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->abbreviationBelongsToCurrentProject($abbreviation)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}


// end of class
}

class Content_AbbreviationException extends Exception { }

?>