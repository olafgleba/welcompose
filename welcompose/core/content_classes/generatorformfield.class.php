<?php

/**
 * Project: Welcompose
 * File: generatorformfield.class.php
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

/**
 * Singleton for Content_GeneratorFormField.
 * 
 * @return object
 */
function Content_GeneratorFormField ()
{
	if (Content_GeneratorFormField::$instance == null) {
		Content_GeneratorFormField::$instance = new Content_GeneratorFormField(); 
	}
	return Content_GeneratorFormField::$instance;
}

class Content_GeneratorFormField {
	
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
 * Adds form field to the generator form fields table. Takes a field=>value
 * array with form field data as first argument. Returns insert id.
 *
 * @throws Content_GeneratorFormFieldException
 * @param array Row data
 * @return int Form field id
 */
public function addGeneratorFormField ($sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'GeneratorFormField', 'Manage')) {
		throw new Content_GeneratorFormFieldException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Content_GeneratorFormFieldException('Input for parameter sqlData is not an array');
	}
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_CONTENT_GENERATOR_FORM_FIELDS, $sqlData);
	
	// test if generator form field belongs to current user/project
	if (!$this->generatorFormFieldBelongsToCurrentUser($insert_id)) {
		throw new Content_GeneratorFormFieldException('Generator form field does not belong to current user or project');
	}
	
	return (int)$insert_id;
}

/**
 * Updates generator form field. Takes the generator form field id as first
 * argument, a field=>value array with the new generator form data as second
 * argument. Returns amount of affected rows.
 *
 * @throws Content_GeneratorFormFieldException
 * @param int Generator form field id
 * @param array Row data
 * @return int Affected rows
*/
public function updateGeneratorFormField ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'GeneratorFormField', 'Manage')) {
		throw new Content_GeneratorFormFieldException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_GeneratorFormFieldException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_GeneratorFormFieldException('Input for parameter sqlData is not an array');	
	}
	
	// test if generator form field belongs to current user/project
	if (!$this->generatorFormFieldBelongsToCurrentUser($id)) {
		throw new Content_GeneratorFormFieldException('Generator form field does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_CONTENT_GENERATOR_FORM_FIELDS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes generator form field from the generator form field table. Takes
 * the generator form field id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Content_GeneratorFormFieldException
 * @param int Generator form field id
 * @return int Amount of affected rows
 */
public function deleteGeneratorFormField ($id)
{
	// access check
	if (!wcom_check_access('Content', 'GeneratorFormField', 'Manage')) {
		throw new Content_GeneratorFormFieldException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_GeneratorFormFieldException('Input for parameter id is not numeric');
	}
	
	// test if simple form belongs to current user/project
	if (!$this->generatorFormFieldBelongsToCurrentUser($id)) {
		throw new Content_GeneratorFormFieldException('Generator form field does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_CONTENT_GENERATOR_FORM_FIELDS, $where, $bind_params);
}

/**
 * Selects a generator form field. Takes the generator form field id
 * as first argument. Returns array with generator form field information.
 * 
 * @throws Content_GeneratorFormFieldException
 * @param int Generator form field id
 * @return array
 */
public function selectGeneratorFormField ($id)
{
	// access check
	if (!wcom_check_access('Content', 'GeneratorFormField', 'Use')) {
		throw new Content_GeneratorFormFieldException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_GeneratorFormFieldException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`content_generator_form_fields`.`id` AS `id`,
			`content_generator_form_fields`.`form` AS `form`,
			`content_generator_form_fields`.`type` AS `type`,
			`content_generator_form_fields`.`name` AS `name`,
			`content_generator_form_fields`.`label` AS `label`,
			`content_generator_form_fields`.`value` AS `value`,
			`content_generator_form_fields`.`required` AS `required`,
			`content_generator_form_fields`.`required_message` AS `required_message`,
			`content_generator_form_fields`.`validator_regex` AS `validator_regex`,
			`content_generator_form_fields`.`validator_message` AS `validator_message`,
			`content_generator_form_fields`.`sorting` AS `sorting`
		FROM
			".WCOM_DB_CONTENT_GENERATOR_FORM_FIELDS." AS `content_generator_form_fields`
		JOIN
			".WCOM_DB_CONTENT_GENERATOR_FORMS." AS `content_generator_forms`
		  ON
			`content_generator_form_fields`.`form` = `content_generator_forms`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_generator_forms`.`id` = `content_pages`.`id`
		JOIN
			".WCOM_DB_CONTENT_NODES." AS `content_nodes`
		  ON
			`content_pages`.`id` = `content_nodes`.`id`
		WHERE
			`content_generator_form_fields`.`id` = :id
		  AND
			`content_pages`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => (int)WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more generator form fields. Takes
 * key=>value array with select params as first argument. 
 * Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>form, int, optional: Form id</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Content_GeneratorFormFieldException
 * @param array Select params
 * @return array
 */
public function selectGeneratorFormFields ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'GeneratorFormField', 'Use')) {
		throw new Content_GeneratorFormFieldException("You are not allowed to perform this action");
	}
	
	// define some vars
	$form = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_GeneratorFormFieldException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'form':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_GeneratorFormFieldException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`content_generator_form_fields`.`id` AS `id`,
			`content_generator_form_fields`.`form` AS `form`,
			`content_generator_form_fields`.`type` AS `type`,
			`content_generator_form_fields`.`name` AS `name`,
			`content_generator_form_fields`.`label` AS `label`,
			`content_generator_form_fields`.`value` AS `value`,
			`content_generator_form_fields`.`required` AS `required`,
			`content_generator_form_fields`.`required_message` AS `required_message`,
			`content_generator_form_fields`.`validator_regex` AS `validator_regex`,
			`content_generator_form_fields`.`validator_message` AS `validator_message`,
			`content_generator_form_fields`.`sorting` AS `sorting`
		FROM
			".WCOM_DB_CONTENT_GENERATOR_FORM_FIELDS." AS `content_generator_form_fields`
		JOIN
			".WCOM_DB_CONTENT_GENERATOR_FORMS." AS `content_generator_forms`
		  ON
			`content_generator_form_fields`.`form` = `content_generator_forms`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_generator_forms`.`id` = `content_pages`.`id`
		JOIN
			".WCOM_DB_CONTENT_NODES." AS `content_nodes`
		  ON
			`content_pages`.`id` = `content_nodes`.`id`
		WHERE
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($form) && is_numeric($form)) {
		$sql .= " AND `content_generator_form_fields`.`form` = :form ";
		$bind_params['form'] = $form;
	}
	
	$sql .= ' ORDER BY `content_generator_form_fields`.`sorting` ';
	
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
 * Method to count generator form fields. Takes key=>value array
 * with select count as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>form, int, optional: Form id</li>
 * </ul>
 * 
 * @throws Content_GeneratorFormFieldException
 * @param array Count params
 * @return array
 */
public function countGeneratorFormFields ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'GeneratorFormField', 'Use')) {
		throw new Content_GeneratorFormFieldException("You are not allowed to perform this action");
	}
	
	// define some vars
	$form = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_GeneratorFormFieldException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'form':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_GeneratorFormFieldException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_GENERATOR_FORM_FIELDS." AS `content_generator_form_fields`
		JOIN
			".WCOM_DB_CONTENT_GENERATOR_FORMS." AS `content_generator_forms`
		  ON
			`content_generator_form_fields`.`form` = `content_generator_forms`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_generator_forms`.`id` = `content_pages`.`id`
		JOIN
			".WCOM_DB_CONTENT_NODES." AS `content_nodes`
		  ON
			`content_pages`.`id` = `content_nodes`.`id`
		WHERE
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($form) && is_numeric($form)) {
		$sql .= " AND `content_generator_form_fields`.`form` = :form ";
		$bind_params['form'] = $form;
	}
	
	return $this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Returns list of available from field types for form definition.
 *
 * @return array
 */
public function getTypeListForForm ()
{
	// type list definition
	$types = array(
		'hidden' => 'hidden',
		'text' => 'text',
		'textarea' => 'textarea',
		'submit' => 'submit',
		'reset' => 'reset',
		'radio' => 'radio',
		'checkbox' => 'checkbox',
		'select' => 'select'
	);
	
	// sort types
	asort($types);
	
	// return type list
	return $types;
}

/**
 * Tests whether given generator form field belongs to current
 * project. Takes the generator form id as first argument.
 * Returns bool.
 *
 * @throws Content_GeneratorFormFieldException
 * @param int Generator form field id
 * @return int bool
 */
public function generatorFormFieldBelongsToCurrentProject ($generator_form_field)
{
	// access check
	if (!wcom_check_access('Content', 'GeneratorFormField', 'Use')) {
		throw new Content_GeneratorFormFieldException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($generator_form_field) || !is_numeric($generator_form_field)) {
		throw new Content_GeneratorFormFieldException('Input for parameter generator_form_field is expected to be numeric');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_GENERATOR_FORM_FIELDS." AS `content_generator_form_fields`
		JOIN
			".WCOM_DB_CONTENT_GENERATOR_FORMS." AS `content_generator_forms`
		  ON
			`content_generator_form_fields`.`form` = `content_generator_forms`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_generator_forms`.`id` = `content_pages`.`id`
		WHERE
			`content_generator_form_fields`.`id` = :generator_form_field
		  AND
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'generator_form_field' => (int)$generator_form_field,
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
 * Test whether generator form field belongs to current user or not.
 * Takes the generator form id as first argument. Returns bool.
 *
 * @throws Content_GeneratorFormFieldException
 * @param int Generator form field id
 * @return bool
 */
public function generatorFormFieldBelongsToCurrentUser ($generator_form_field)
{
	// access check
	if (!wcom_check_access('Content', 'GeneratorFormField', 'Use')) {
		throw new Content_GeneratorFormFieldException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($generator_form_field) || !is_numeric($generator_form_field)) {
		throw new Content_GeneratorFormFieldException('Input for parameter generator_form_field is expected to be numeric');
	}
	
	// load user class
	$USER = load('User:User');
	
	if (!$this->generatorFormFieldBelongsToCurrentProject($generator_form_field)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Content_GeneratorFormFieldException extends Exception { }

?>