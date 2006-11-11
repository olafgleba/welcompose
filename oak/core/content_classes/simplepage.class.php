<?php

/**
 * Project: Oak
 * File: simplepage.class.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-2.1.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License 3.0
 */

class Content_SimplePage {
	
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
 * Singleton. Returns instance of the Content_SimplePage object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Content_SimplePage::$instance == null) {
		Content_SimplePage::$instance = new Content_SimplePage(); 
	}
	return Content_SimplePage::$instance;
}

/**
 * Adds simple page to the simple page table. Takes the page id as
 * first argument and a field=>value array with simple page data as
 * second argument. Returns insert id. 
 * 
 * @throws Content_SimplePageException
 * @param int Page id
 * @param array Row data
 * @return int Simple page
 */
public function addSimplePage ($id, $sqlData)
{
	// access check
	if (!oak_check_access('Content', 'SimplePage', 'Manage')) {
		throw new Content_SimplePageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimplePageException('Input for parameter id is not numeric');
	}
	if (!is_array($sqlData)) {
		throw new Content_SimplePageException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the simple page will be associated to the correct page
	$sqlData['id'] = $id;
	
	// insert row
	$this->base->db->insert(OAK_DB_CONTENT_SIMPLE_PAGES, $sqlData);
	
	// test if simple page belongs to current user/project
	if (!$this->simplePageBelongsToCurrentUser($id)) {
		throw new Content_SimplePageException('Simple page does not belong to current user or project');
	}
	
	return (int)$id;
}

/**
 * Updates simple page. Takes the simple page id as first argument, a
 * field=>value array with the new simple page data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_SimplePageException
 * @param int Simple page id
 * @param array Row data
 * @return int Affected rows
*/
public function updateSimplePage ($id, $sqlData)
{
	// access check
	if (!oak_check_access('Content', 'SimplePage', 'Manage')) {
		throw new Content_SimplePageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimplePageException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_SimplePageException('Input for parameter sqlData is not an array');	
	}
	
	// test if simple page belongs to current user/project
	if (!$this->simplePageBelongsToCurrentUser($id)) {
		throw new Content_SimplePageException('Simple page does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(OAK_DB_CONTENT_SIMPLE_PAGES, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes simple page from the simple pages table. Takes the
 * simple page id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Content_SimplePageException
 * @param int Simple page id
 * @return int Amount of affected rows
 */
public function deleteSimplePage ($id)
{
	// access check
	if (!oak_check_access('Content', 'SimplePage', 'Manage')) {
		throw new Content_SimplePageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimplePageException('Input for parameter id is not numeric');
	}
	
	// test if simple page belongs to current user/project
	if (!$this->simplePageBelongsToCurrentUser($id)) {
		throw new Content_SimplePageException('Simple page does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_CONTENT_SIMPLE_PAGES, $where, $bind_params);
}

/**
 * Selects one simple page. Takes the simple page id as first
 * argument. Returns array with simple page information.
 * 
 * @throws Content_SimplePageException
 * @param int Simple page id
 * @return array
 */
public function selectSimplePage ($id)
{
	// access check
	if (!oak_check_access('Content', 'SimplePage', 'Use')) {
		throw new Content_SimplePageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimplePageException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`content_simple_pages`.`id` AS `id`,
			`content_simple_pages`.`user` AS `user`,
			`content_simple_pages`.`title` AS `title`,
			`content_simple_pages`.`title_url` AS `title_url`,
			`content_simple_pages`.`content_raw` AS `content_raw`,
			`content_simple_pages`.`content` AS `content`,
			`content_simple_pages`.`text_converter` AS `text_converter`,
			`content_simple_pages`.`apply_macros` AS `apply_macros`,
			`content_simple_pages`.`meta_use` AS `meta_use`,
			`content_simple_pages`.`meta_title_raw` AS `meta_title_raw`,
			`content_simple_pages`.`meta_title` AS `meta_title`,
			`content_simple_pages`.`meta_keywords` AS `meta_keywords`,
			`content_simple_pages`.`meta_description` AS `meta_description`,
			`content_simple_pages`.`date_modified` AS `date_modified`,
			`content_simple_pages`.`date_added` AS `date_added`,
			`content_nodes`.`id` AS `node_id`,
			`content_nodes`.`navigation` AS `node_navigation`,
			`content_nodes`.`root_node` AS `node_root_node`,
			`content_nodes`.`parent` AS `node_parent`,
			`content_nodes`.`lft` AS `node_lft`,
			`content_nodes`.`rgt` AS `node_rgt`,
			`content_nodes`.`level` AS `node_level`,
			`content_nodes`.`sorting` AS `node_sorting`,
			`content_pages`.`id` AS `page_id`,
			`content_pages`.`project` AS `page_project`,
			`content_pages`.`type` AS `page_type`,
			`content_pages`.`template_set` AS `page_template_set`,
			`content_pages`.`name` AS `page_name`,
			`content_pages`.`name_url` AS `page_name_url`,
			`content_pages`.`url` AS `page_url`,
			`content_pages`.`protect` AS `page_protect`,
			`content_pages`.`index_page` AS `page_index_page`,
			`content_pages`.`image_small` AS `page_image_small`,
			`content_pages`.`image_medium` AS `page_image_medium`,
			`content_pages`.`image_big` AS `page_image_big`
		FROM
			".OAK_DB_CONTENT_SIMPLE_PAGES." AS `content_simple_pages`
		JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		ON
			`content_simple_pages`.`id` = `content_pages`.`id`
		JOIN
			".OAK_DB_CONTENT_NODES." AS `content_nodes`
		ON
			`content_pages`.`id` = `content_nodes`.`id`
		WHERE
			`content_simple_pages`.`id` = :id
		AND
			`content_pages`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => (int)OAK_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more simple pages. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>user, int, optional: User/author id</li>
 * <li>page, int, optional: Page id</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * <li>order_marco, string, otpional: How to sort the result set.
 * Supported macros:
 *    <ul>
 *        <li>PAGE: sorty by page id</li>
 *        <li>DATE_MODIFIED: sorty by date modified</li>
 *        <li>DATE_ADDED: sort by date added</li>
 *    </ul>
 * </li>
 * </ul>
 * 
 * @throws Content_SimplePageException
 * @param array Select params
 * @return array
 */
public function selectSimplePages ($params = array())
{
	// access check
	if (!oak_check_access('Content', 'SimplePage', 'Use')) {
		throw new Content_SimplePageException("You are not allowed to perform this action");
	}
	
	// define some vars
	$user = null;
	$page = null;
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_SimplePageException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'order_marco':
					$$_key = (string)$_value;
				break;
			case 'user':
			case 'page':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_SimplePageException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'PAGE' => '`content_simple_pages`.`page`',
		'DATE_ADDED' => '`content_simple_pages`.`date_added` AS `date_added`',
		'DATE_MODIFIED' => '`content_simple_pages`.`date_modified`'
	);
	
	// prepare query
	$sql = "
		SELECT 
			`content_simple_pages`.`id` AS `id`,
			`content_simple_pages`.`user` AS `user`,
			`content_simple_pages`.`title` AS `title`,
			`content_simple_pages`.`title_url` AS `title_url`,
			`content_simple_pages`.`content_raw` AS `content_raw`,
			`content_simple_pages`.`content` AS `content`,
			`content_simple_pages`.`text_converter` AS `text_converter`,
			`content_simple_pages`.`apply_macros` AS `apply_macros`,
			`content_simple_pages`.`meta_use` AS `meta_use`,
			`content_simple_pages`.`meta_title_raw` AS `meta_title_raw`,
			`content_simple_pages`.`meta_title` AS `meta_title`,
			`content_simple_pages`.`meta_keywords` AS `meta_keywords`,
			`content_simple_pages`.`meta_description` AS `meta_description`,
			`content_simple_pages`.`date_modified` AS `date_modified`,
			`content_simple_pages`.`date_added` AS `date_added`,
			`content_nodes`.`id` AS `node_id`,
			`content_nodes`.`navigation` AS `node_navigation`,
			`content_nodes`.`root_node` AS `node_root_node`,
			`content_nodes`.`parent` AS `node_parent`,
			`content_nodes`.`lft` AS `node_lft`,
			`content_nodes`.`rgt` AS `node_rgt`,
			`content_nodes`.`level` AS `node_level`,
			`content_nodes`.`sorting` AS `node_sorting`,
			`content_pages`.`id` AS `page_id`,
			`content_pages`.`project` AS `page_project`,
			`content_pages`.`type` AS `page_type`,
			`content_pages`.`template_set` AS `page_template_set`,
			`content_pages`.`name` AS `page_name`,
			`content_pages`.`name_url` AS `page_name_url`,
			`content_pages`.`url` AS `page_url`,
			`content_pages`.`protect` AS `page_protect`,
			`content_pages`.`index_page` AS `page_index_page`,
			`content_pages`.`image_small` AS `page_image_small`,
			`content_pages`.`image_medium` AS `page_image_medium`,
			`content_pages`.`image_big` AS `page_image_big`
		FROM
			".OAK_DB_CONTENT_SIMPLE_PAGES." AS `content_simple_pages`
		JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		ON
			`content_simple_pages`.`id` = `content_pages`.`id`
		JOIN
			".OAK_DB_CONTENT_NODES." AS `content_nodes`
		ON
			`content_pages`.`id` = `content_nodes`.`id`
		WHERE
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($user) && is_numeric($user)) {
		$sql .= " AND `user_users`.`id` = :user ";
		$bind_params['user'] = $user;
	}
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `content_pages`.`id` = :page ";
		$bind_params['page'] = $page;
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
 * Tests whether given simple page belongs to current project. Takes the
 * simple page id as first argument. Returns bool.
 *
 * @throws Content_SimplePageException
 * @param int Simple page id
 * @return int bool
 */
public function simplePageBelongsToCurrentProject ($simple_page)
{
	// access check
	if (!oak_check_access('Content', 'SimplePage', 'Use')) {
		throw new Content_SimplePageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($simple_page) || !is_numeric($simple_page)) {
		throw new Content_SimplePageException('Input for parameter simple_page is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_CONTENT_SIMPLE_PAGES." AS `content_simple_pages`
		JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_simple_pages`.`id` = `content_pages`.`id`
		WHERE
			`content_simple_pages`.`id` = :simple_page
		  AND
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'simple_page' => (int)$simple_page,
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
 * Test whether simple page belongs to current user or not. Takes
 * the simple page id as first argument. Returns bool.
 *
 * @throws Content_SimplePageException
 * @param int Simple page id
 * @return bool
 */
public function simplePageBelongsToCurrentUser ($simple_page)
{
	// access check
	if (!oak_check_access('Content', 'SimplePage', 'Use')) {
		throw new Content_SimplePageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($simple_page) || !is_numeric($simple_page)) {
		throw new Content_SimplePageException('Input for parameter simple_page is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->simplePageBelongsToCurrentProject($simple_page)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(OAK_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Content_SimplePageException extends Exception { }

?>