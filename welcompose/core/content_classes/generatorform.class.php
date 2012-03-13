<?php

/**
 * Project: Welcompose
 * File: generatorform.class.php
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
 * Singleton for Content_GeneratorForm.
 * 
 * @return object
 */
function Content_GeneratorForm ()
{
	if (Content_GeneratorForm::$instance == null) {
		Content_GeneratorForm::$instance = new Content_GeneratorForm(); 
	}
	return Content_GeneratorForm::$instance;
}

class Content_GeneratorForm {
	
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
 * Adds generator form to the generator form table. Takes the page id as first argument,
 * a field=>value array with generator form data as second argument. Returns insert
 * id. 
 * 
 * @throws Content_GeneratorFormException
 * @param int Page id
 * @param array Row data
 * @return int Generator form id
 */
public function addGeneratorForm ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'GeneratorForm', 'Manage')) {
		throw new Content_GeneratorFormException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Content_GeneratorFormException('Input for parameter sqlData is not an array');
	}
	if (empty($id) || !is_numeric($id)) {
		throw new Content_GeneratorFormException('Input for parameter sqlData is not numeric');
	}
	
	// make sure that the generator form will be associated to the correct page
	$sqlData['id'] = $id;
	
	// insert row
	$this->base->db->insert(WCOM_DB_CONTENT_GENERATOR_FORMS, $sqlData);
	
	// test if generator form belongs to current user/project
	if (!$this->generatorFormBelongsToCurrentUser($id)) {
		throw new Content_GeneratorFormException('Generator form does not belong to current user or project');
	}
	
	return (int)$page;
}

/**
 * Updates generator form. Takes the generator form id as first argument, a
 * field=>value array with the new generator form data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_GeneratorFormException
 * @param int Generator form id
 * @param array Row data
 * @return int Affected rows
*/
public function updateGeneratorForm ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'GeneratorForm', 'Manage')) {
		throw new Content_GeneratorFormException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_GeneratorFormException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_GeneratorFormException('Input for parameter sqlData is not an array');	
	}
	
	// test if generator form belongs to current user/project
	if (!$this->generatorFormBelongsToCurrentUser($id)) {
		throw new Content_GeneratorFormException('Generator form does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_CONTENT_GENERATOR_FORMS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes generator form from the generator forms table. Takes the
 * generator form id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Content_GeneratorFormException
 * @param int Generator form id
 * @return int Amount of affected rows
 */
public function deleteGeneratorForm ($id)
{
	// access check
	if (!wcom_check_access('Content', 'GeneratorForm', 'Manage')) {
		throw new Content_GeneratorFormException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_GeneratorFormException('Input for parameter id is not numeric');
	}
	
	// test if simple form belongs to current user/project
	if (!$this->generatorFormBelongsToCurrentUser($id)) {
		throw new Content_GeneratorFormException('Generator form does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_CONTENT_GENERATOR_FORMS, $where, $bind_params);
}

/**
 * Selects one generator form. Takes the generator form id as first
 * argument. Returns array with generator form information.
 * 
 * @throws Content_GeneratorFormException
 * @param int Generator form id
 * @return array
 */
public function selectGeneratorForm ($id)
{
	// access check
	if (!wcom_check_access('Content', 'GeneratorForm', 'Use')) {
		throw new Content_GeneratorFormException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_GeneratorFormException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`content_generator_forms`.`id` AS `id`,
			`content_generator_forms`.`user` AS `user`,
			`content_generator_forms`.`title` AS `title`,
			`content_generator_forms`.`title_url` AS `title_url`,
			`content_generator_forms`.`content_raw` AS `content_raw`,
			`content_generator_forms`.`content` AS `content`,
			`content_generator_forms`.`text_converter` AS `text_converter`,
			`content_generator_forms`.`apply_macros` AS `apply_macros`,
			`content_generator_forms`.`meta_use` AS `meta_use`,
			`content_generator_forms`.`meta_title_raw` AS `meta_title_raw`,
			`content_generator_forms`.`meta_title` AS `meta_title`,
			`content_generator_forms`.`meta_keywords` AS `meta_keywords`,
			`content_generator_forms`.`meta_description` AS `meta_description`,
			`content_generator_forms`.`email_from` AS `email_from`,
			`content_generator_forms`.`email_to` AS `email_to`,
			`content_generator_forms`.`email_subject` AS `email_subject`,
			`content_generator_forms`.`use_captcha` AS `use_captcha`,
			`content_generator_forms`.`date_modified` AS `date_modified`,
			`content_generator_forms`.`date_added` AS `date_added`,
			`content_nodes`.`id` AS `node_id`,
			`content_nodes`.`navigation` AS `node_navigation`,
			`content_nodes`.`root_node` AS `node_root_node`,
			`content_nodes`.`parent` AS `node_parent`,
			`content_nodes`.`lft` AS `node_lft`,
			`content_nodes`.`rgt` AS `node_rgt`,
			`content_nodes`.`level` AS `node_level`,
			`content_nodes`.`sorting` AS `node_sorting`,
			`content_pages`.`id` AS `form_id`,
			`content_pages`.`project` AS `form_project`,
			`content_pages`.`type` AS `form_type`,
			`content_pages`.`template_set` AS `form_template_set`,
			`content_pages`.`name` AS `form_name`,
			`content_pages`.`name_url` AS `form_name_url`,
			`content_pages`.`alternate_name` AS `page_alternate_name`,
			`content_pages`.`description` AS `page_description`,
			`content_pages`.`optional_text` AS `page_optional_text`,
			`content_pages`.`url` AS `form_url`,
			`content_pages`.`protect` AS `form_protect`,
			`content_pages`.`index_page` AS `form_index_page`,
			`content_pages`.`draft` AS `form_draft`,
			`content_pages`.`image_small` AS `form_image_small`,
			`content_pages`.`image_medium` AS `form_image_medium`,
			`content_pages`.`image_big` AS `form_image_big`,
			`content_pages`.`sitemap_changefreq` AS `page_sitemap_changefreq`,
			`content_pages`.`sitemap_priority` AS `page_sitemap_priority`
		FROM
			".WCOM_DB_CONTENT_GENERATOR_FORMS." AS `content_generator_forms`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		ON
			`content_generator_forms`.`id` = `content_pages`.`id`
		JOIN
			".WCOM_DB_CONTENT_NODES." AS `content_nodes`
		ON
			`content_pages`.`id` = `content_nodes`.`id`
		WHERE
			`content_generator_forms`.`id` = :id
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
 * Method to select one or more generator forms. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>user, int, optional: User/author id</li>
 * <li>form, int, optional: Form id</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * <li>order_marco, string, otpional: How to sort the result set.
 * Supported macros:
 *    <ul>
 *        <li>FORM: sorty by form id</li>
 *        <li>DATE_MODIFIED: sorty by date modified</li>
 *        <li>DATE_ADDED: sort by date added</li>
 *    </ul>
 * </li>
 * </ul>
 * 
 * @throws Content_GeneratorFormException
 * @param array Select params
 * @return array
 */
public function selectGeneratorForms ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'GeneratorForm', 'Use')) {
		throw new Content_GeneratorFormException("You are not allowed to perform this action");
	}
	
	// define some vars
	$user = null;
	$form = null;
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_GeneratorFormException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'order_marco':
					$$_key = (string)$_value;
				break;
			case 'user':
			case 'form':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_GeneratorFormException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'FORM' => '`content_generator_forms`.`id`',
		'DATE_ADDED' => '`content_generator_forms`.`date_added`',
		'DATE_MODIFIED' => '`content_generator_forms`.`date_modified`'
	);
	
	// prepare query
	$sql = "
		SELECT 
			`content_generator_forms`.`id` AS `id`,
			`content_generator_forms`.`user` AS `user`,
			`content_generator_forms`.`title` AS `title`,
			`content_generator_forms`.`title_url` AS `title_url`,
			`content_generator_forms`.`content_raw` AS `content_raw`,
			`content_generator_forms`.`content` AS `content`,
			`content_generator_forms`.`text_converter` AS `text_converter`,
			`content_generator_forms`.`apply_macros` AS `apply_macros`,
			`content_generator_forms`.`meta_use` AS `meta_use`,
			`content_generator_forms`.`meta_title_raw` AS `meta_title_raw`,
			`content_generator_forms`.`meta_title` AS `meta_title`,
			`content_generator_forms`.`meta_keywords` AS `meta_keywords`,
			`content_generator_forms`.`meta_description` AS `meta_description`,
			`content_generator_forms`.`email_from` AS `email_from`,
			`content_generator_forms`.`email_to` AS `email_to`,
			`content_generator_forms`.`email_subject` AS `email_subject`,
			`content_generator_forms`.`use_captcha` AS `use_captcha`,
			`content_generator_forms`.`date_modified` AS `date_modified`,
			`content_generator_forms`.`date_added` AS `date_added`,
			`content_nodes`.`id` AS `node_id`,
			`content_nodes`.`navigation` AS `node_navigation`,
			`content_nodes`.`root_node` AS `node_root_node`,
			`content_nodes`.`parent` AS `node_parent`,
			`content_nodes`.`lft` AS `node_lft`,
			`content_nodes`.`rgt` AS `node_rgt`,
			`content_nodes`.`level` AS `node_level`,
			`content_nodes`.`sorting` AS `node_sorting`,
			`content_pages`.`id` AS `form_id`,
			`content_pages`.`project` AS `form_project`,
			`content_pages`.`type` AS `form_type`,
			`content_pages`.`template_set` AS `form_template_set`,
			`content_pages`.`name` AS `form_name`,
			`content_pages`.`name_url` AS `form_name_url`,
			`content_pages`.`alternate_name` AS `page_alternate_name`,
			`content_pages`.`description` AS `page_description`,
			`content_pages`.`optional_text` AS `page_optional_text`,
			`content_pages`.`url` AS `form_url`,
			`content_pages`.`protect` AS `form_protect`,
			`content_pages`.`index_page` AS `form_index_page`,
			`content_pages`.`draft` AS `form_draft`,
			`content_pages`.`image_small` AS `form_image_small`,
			`content_pages`.`image_medium` AS `form_image_medium`,
			`content_pages`.`image_big` AS `form_image_big`,
			`content_pages`.`sitemap_changefreq` AS `page_sitemap_changefreq`,
			`content_pages`.`sitemap_priority` AS `page_sitemap_priority`
		FROM
			".WCOM_DB_CONTENT_GENERATOR_FORMS." AS `content_generator_forms`
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
	if (!empty($user) && is_numeric($user)) {
		$sql .= " AND `content_generator_forms`.`id` = :user ";
		$bind_params['user'] = $user;
	}
	if (!empty($form) && is_numeric($form)) {
		$sql .= " AND `content_generator_forms`.`id` = :form ";
		$bind_params['form'] = $form;
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
 * Tests whether given generator form belongs to current project. Takes the
 * generator form id as first argument. Returns bool.
 *
 * @throws Content_GeneratorFormException
 * @param int Generator form id
 * @return int bool
 */
public function generatorFormBelongsToCurrentProject ($generator_form)
{
	// access check
	if (!wcom_check_access('Content', 'GeneratorForm', 'Use')) {
		throw new Content_GeneratorFormException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($generator_form) || !is_numeric($generator_form)) {
		throw new Content_GeneratorFormException('Input for parameter simple_form is expected to be numeric');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_GENERATOR_FORMS." AS `content_generator_forms`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_generator_forms`.`id` = `content_pages`.`id`
		WHERE
			`content_generator_forms`.`id` = :generator_form
		  AND
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'generator_form' => (int)$generator_form,
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
 * Test whether generator form belongs to current user or not. Takes
 * the generator form id as first argument. Returns bool.
 *
 * @throws Content_GeneratorFormException
 * @param int Generator form id
 * @return bool
 */
public function generatorFormBelongsToCurrentUser ($generator_form)
{
	// access check
	if (!wcom_check_access('Content', 'GeneratorForm', 'Use')) {
		throw new Content_GeneratorFormException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($generator_form) || !is_numeric($generator_form)) {
		throw new Content_GeneratorFormException('Input for parameter generator_form is expected to be numeric');
	}
	
	// load user class
	$USER = load('User:User');
	
	if (!$this->generatorFormBelongsToCurrentProject($generator_form)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Content_GeneratorFormException extends Exception { }

?>