<?php

/**
 * Project: Welcompose
 * File: page.class.php
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
 * Singleton for Content_Page.
 * 
 * @return object
 */
function Content_Page ()
{
	if (Content_Page::$instance == null) {
		Content_Page::$instance = new Content_Page(); 
	}
	return Content_Page::$instance;
}

class Content_Page {
	
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
 * Adds page to the page table. Takes a field=>value array with
 * page data as first argument. Returns insert id.
 * 
 * @throws Content_PageException
 * @param array Row data
 * @return int Page id
 */
public function addPage ($sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Manage')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Content_PageException('Input for parameter sqlData is not an array');	
	}
	
	$sqlData['project'] = WCOM_CURRENT_PROJECT;
	
	// insert row
	$this->base->db->insert(WCOM_DB_CONTENT_PAGES, $sqlData);
	
	// test if page belongs to current project/user
	if (!$this->pageBelongsToCurrentUser($sqlData['id'])) {
		throw new Content_PageException('Given page does not belong to current project');
	}
	
	return (int)$sqlData['id'];
}

/**
 * Updates page. Takes the page id as first argument, a
 * field=>value array with the new page data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_PageException
 * @param int Page id
 * @param array Row data
 * @return int Affected rows
*/
public function updatePage ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Manage')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_PageException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_PageException('Input for parameter sqlData is not an array');	
	}
	
	// test if page belongs to current project/user
	if (!$this->pageBelongsToCurrentUser($id)) {
		throw new Content_PageException('Given page does not belong to current project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_CONTENT_PAGES, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes page from the page table. Takes the page id
 * as first argument. Returns amount of affected rows
 * 
 * @throws Content_PageException
 * @param int Page id
 * @return int Amount of affected rows
 */
public function deletePage ($id)
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Manage')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_PageException('Input for parameter id is not numeric');
	}
	
	// test if page belongs to current project/user
	if (!$this->pageBelongsToCurrentUser($id)) {
		throw new Content_PageException('Given page does not belong to current project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_CONTENT_PAGES, $where, $bind_params);
}

/**
 * Selects one page. Takes the page id as first argument.
 * Returns array with page information.
 * 
 * @throws Content_PageException
 * @param int Page id
 * @return array
 */
public function selectPage ($id)
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Use')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_PageException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`content_pages`.`id` AS `id`,
			`content_pages`.`project` AS `project`,
			`content_nodes`.`navigation` AS `navigation`,
			`content_nodes`.`root_node` AS `root_node`,
			`content_nodes`.`parent` AS `parent`,
			`content_nodes`.`lft` AS `lft`,
			`content_nodes`.`rgt` AS `rgt`,
			`content_nodes`.`level` AS `level`,
			`content_nodes`.`sorting` AS `sorting`,
			`content_pages`.`type` AS `type`,
			`content_pages`.`template_set` AS `template_set`,
			`content_pages`.`name` AS `name`,
			`content_pages`.`name_url` AS `name_url`,
			`content_pages`.`alternate_name` AS `alternate_name`,
			`content_pages`.`description` AS `description`,
			`content_pages`.`optional_text` AS `optional_text`,
			`content_pages`.`url` AS `url`,
			`content_pages`.`protect` AS `protect`,
			`content_pages`.`index_page` AS `index_page`,
			`content_pages`.`sitemap_changefreq` AS `sitemap_changefreq`,
			`content_pages`.`sitemap_priority` AS `sitemap_priority`,
			`content_page_types`.`id` AS `page_type_id`,
			`content_page_types`.`name` AS `page_type_name`,
			`content_page_types`.`internal_name` AS `page_type_internal_name`
		FROM
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		JOIN
			".WCOM_DB_CONTENT_NODES." AS `content_nodes`
		  ON
			`content_pages`.`id` = `content_nodes`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGE_TYPES." AS `content_page_types`
		  ON
			`content_pages`.`type` = `content_page_types`.`id`
		WHERE 
			`content_pages`.`id` = :id
		  AND
			`content_pages`.`project` = :project
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
 * Method to select one or more pages. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>navigation, int, optional: Navigation id</li>
 * <li>root_node, int, optional: Root node id</li>
 * <li>parent, int, optional: Parent node id</li>
 * <li>level, int, optional: Level count</li>
 * <li>sorting, int, optional: Sorting count</li>
 * <li>type, int, optional: Page type id</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Content_PageException
 * @param array Select params
 * @return array
 */
public function selectPages ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Use')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
	
	// define some vars
	$navigation = null;
	$root_node = null;
	$parent = null;
	$level = null;
	$sorting = null;
	$type = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_PageException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'navigation':
			case 'root_node':
			case 'parent':
			case 'level':
			case 'sorting':
			case 'type':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_PageException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`content_pages`.`id` AS `id`,
			`content_pages`.`project` AS `project`,
			`content_nodes`.`navigation` AS `navigation`,
			`content_nodes`.`root_node` AS `root_node`,
			`content_nodes`.`parent` AS `parent`,
			`content_nodes`.`lft` AS `lft`,
			`content_nodes`.`rgt` AS `rgt`,
			`content_nodes`.`level` AS `level`,
			`content_nodes`.`sorting` AS `sorting`,
			`content_pages`.`type` AS `type`,
			`content_pages`.`template_set` AS `template_set`,
			`content_pages`.`name` AS `name`,
			`content_pages`.`name_url` AS `name_url`,
			`content_pages`.`alternate_name` AS `alternate_name`,
			`content_pages`.`description` AS `description`,
			`content_pages`.`optional_text` AS `optional_text`,
			`content_pages`.`url` AS `url`,
			`content_pages`.`protect` AS `protect`,
			`content_pages`.`index_page` AS `index_page`,
			`content_pages`.`sitemap_changefreq` AS `sitemap_changefreq`,
			`content_pages`.`sitemap_priority` AS `sitemap_priority`,
			`content_page_types`.`name` AS `page_type_name`,
			`content_page_types`.`internal_name` AS `page_type_internal_name`
		FROM
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		JOIN
			".WCOM_DB_CONTENT_NODES." AS `content_nodes`
		  ON
			`content_pages`.`id` = `content_nodes`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGE_TYPES." AS `content_page_types`
		  ON
			`content_pages`.`type` = `content_page_types`.`id`
		WHERE 
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($navigation) && is_numeric($navigation)) {
		$sql .= " AND `content_nodes`.`navigation` = :navigation ";
		$bind_params['navigation'] = $navigation;
	}
	if (!empty($root_node) && is_numeric($root_node)) {
		$sql .= " AND `content_nodes`.`root_node` = :root_node ";
		$bind_params['root_node'] = $root_node;
	}
	if (!empty($parent) && is_numeric($parent)) {
		$sql .= " AND `content_nodes`.`parent` = :parent ";
		$bind_params['parent'] = $parent;
	}
	if (!empty($level) && is_numeric($level)) {
		$sql .= " AND `content_nodes`.`level` = :level ";
		$bind_params['level'] = $level;
	}
	if (!empty($sorting) && is_numeric($sorting)) {
		$sql .= " AND `content_nodes`.`sorting` = :sorting ";
		$bind_params['sorting'] = $sorting;
	}
	if (!empty($type) && is_numeric($type)) {
		$sql .= " AND `content_pages`.`type` = :type ";
		$bind_params['type'] = $type;
	}
	
	// add sorting
	$sql .= " ORDER BY `content_nodes`.`sorting`, `content_nodes`.`lft` ";
	
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
 * Method to count pages. Takes key=>value array with counting
 * params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>index_page, int, optional: whether to select index pages (0/1)</li>
 * <li>navigation, int, optional: Navigation id</li>
 * <li>root_node, int, optional: Root node id</li>
 * <li>parent, int, optional: Parent node id</li>
 * <li>level, int, optional: Level count</li>
 * <li>sorting, int, optional: Sorting count</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Content_PageException
 * @param array Count params
 * @return array
 */
public function countPages ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Use')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
	
	// define some vars
	$index_page = null;
	$navigation = null;
	$root_node = null;
	$parent = null;
	$level = null;
	$sorting = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_PageException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'index_page':
			case 'navigation':
			case 'root_node':
			case 'parent':
			case 'level':
			case 'sorting':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_PageException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(DISTINCT `content_pages`.`id`) AS `total`
		FROM
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		JOIN
			".WCOM_DB_CONTENT_NODES." AS `content_nodes`
		  ON
			`content_pages`.`id` = `content_nodes`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGE_TYPES." AS `content_page_types`
		  ON
			`content_pages`.`type` = `content_page_types`.`id`
		WHERE 
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($index_page) && is_numeric($index_page)) {
		$sql .= " AND `content_pages`.`index_page` = :index_page ";
		$bind_params['index_page'] = (string)$index_page;
	}
	if (!empty($navigation) && is_numeric($navigation)) {
		$sql .= " AND `content_nodes`.`navigation` = :navigation ";
		$bind_params['navigation'] = $navigation;
	}
	if (!empty($root_node) && is_numeric($root_node)) {
		$sql .= " AND `content_nodes`.`root_node` = :root_node ";
		$bind_params['root_node'] = $root_node;
	}
	if (!empty($parent) && is_numeric($parent)) {
		$sql .= " AND `content_nodes`.`parent` = :parent ";
		$bind_params['parent'] = $parent;
	}
	if (!empty($level) && is_numeric($level)) {
		$sql .= " AND `content_nodes`.`level` = :level ";
		$bind_params['level'] = $level;
	}
	if (!empty($sorting) && is_numeric($sorting)) {
		$sql .= " AND `content_nodes`.`sorting` = :sorting ";
		$bind_params['sorting'] = $sorting;
	}
	
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Selects index page. Returns array with the complete page
 * information.
 *
 * @throws Content_PageException
 * @return array
 */
public function selectIndexPage ()
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Use')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
	
	// get id of the index page
	$sql = "
		SELECT 
			`content_pages`.`id` AS `id`
		FROM
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		WHERE
			`content_pages`.`index_page` = '1'
		  AND
			`content_pages`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query
	$result = (int)$this->base->db->select($sql, 'field', $bind_params);
	
	// make sure that there is some index page
	if ($result < 1) {
		throw new Content_PageException("Unable to find an index page");
	}
	
	// test if found page belongs to current user/project
	if (!$this->pageBelongsToCurrentUser($result)) {
		throw new Templating_TemplateException('Page does not belong to the current project');
	}
	
	// return complete page information
	return $this->selectPage($result);
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
public function mapPageToGroups ($page, $groups = array())
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Manage')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_PageException("Input for parameter page is not numeric");
	}
	if (!is_array($groups)) {
		throw new Content_PageException("Input for parameter groups is expected to be an array");
	}
	
	// let's see if the given template belongs to the current project
	if (!$this->pageBelongsToCurrentUser($page)) {
		throw new Templating_TemplateException('Given page does not belong to the current project');
	}
	
	// load group class
	$GROUP = load('user:group');
	
	// prepare query to remove all existing links to the current template
	$sql = "
		DELETE FROM
			".WCOM_DB_CONTENT_PAGES2USER_GROUPS." AS `content_pages2user_groups`
		USING
			".WCOM_DB_CONTENT_PAGES2USER_GROUPS." AS `content_pages2user_groups`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_pages2user_groups`.`page` = `content_pages`.`id`
		WHERE
			`content_pages2user_groups`.`page` = :page
		AND
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'page' => (int)$page,
		'project' => (int)WCOM_CURRENT_PROJECT
	);
	
	// remove all existing links to the given page
	$this->base->db->execute($sql, $bind_params);
	
	// add new links
	foreach ($groups as $_group) {
		if (!empty($_group) && is_numeric($_group) && $GROUP->groupBelongsToCurrentUser($_group)) {
			$this->base->db->insert(WCOM_DB_CONTENT_PAGES2USER_GROUPS, array('page' => $page, 'group' => $_group));
		}
	}
	
	return true;
}

/**
 * Selects page to groups map. Takes the page id as first
 * argument. Returns array.
 * 
 * @throws Content_PageException
 * @param int Page id 
 * @return array
 */
public function selectPageToGroupsMap ($page)
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Use')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_PageException("Input for parameter page is expected to be numeric");
	}
	
	// test if page belongs to current project/user
	if (!$this->pageBelongsToCurrentUser($page)) {
		throw new Content_PageException('Given page does not belong to current project');
	}
	
	// prepare query
	$sql = "
		SELECT
			`content_pages2user_groups`.`id`,
			`content_pages2user_groups`.`page`,
			`content_pages2user_groups`.`group`
		FROM
			".WCOM_DB_CONTENT_PAGES2USER_GROUPS." AS `content_pages2user_groups`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
		 	`content_pages2user_groups`.`page` = `content_pages`.`id`
		WHERE
			`content_pages2user_groups`.`page` = :page
		AND
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'page' => (int)$page,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'multi', $bind_params);
}

/**
 * Prepares table structure for the selected page types. This task has to
 * be executed directly after the page creation. Takes the id of the just
 * created page as first argument. Returns boolean true.
 * 
 * @throws Content_PageException
 * @param int Page id
 * @return bool
 */
public function initPageContents ($page)
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Manage')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_PageException('Input for parameter page  is expected to be numeric');
	}
	
	// test if page belongs to current project/user
	if (!$this->pageBelongsToCurrentUser($page)) {
		throw new Content_PageException('Given page does not belong to current project');
	}
	
	// get page information
	$page_info = $this->selectPage($page);
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// handle the different page types
	switch((string)$page_info['page_type_name']) {
		case 'WCOM_GENERATOR_FORM':
				// prepare sql data
				$sqlData = array(
					'id' => $page_info['id'],
					'user' => WCOM_CURRENT_USER,
					'title' => $page_info['name'],
					'title_url' => $HELPER->createMeaningfulString($page_info['name']),
					'date_added' => date('Y-m-d H:i:s')
				);
				
				// create generator form
				$GENERATORFORM = load('Content:GeneratorForm');
				$GENERATORFORM->addGeneratorForm($page_info['id'], $sqlData);
			break;
		case 'WCOM_SIMPLE_FORM':
				// prepare sql data
				$sqlData = array(
					'id' => $page_info['id'],
					'user' => WCOM_CURRENT_USER,
					'title' => $page_info['name'],
					'title_url' => $HELPER->createMeaningfulString($page_info['name']),
					'date_added' => date('Y-m-d H:i:s')
				);
				
				// create simple form
				$SIMPLEFORM = load('content:simpleform');
				$SIMPLEFORM->addSimpleForm($page_info['id'], $sqlData);
			break;
		case 'WCOM_SIMPLE_PAGE':
				// prepare sql data
				$sqlData = array(
					'id' => $page_info['id'],
					'user' => WCOM_CURRENT_USER,
					'title' => $page_info['name'],
					'title_url' => $HELPER->createMeaningfulString($page_info['name']),
					'date_added' => date('Y-m-d H:i:s')
				);
				
				// create simple page
				$SIMPLEPAGE = load('content:simplepage');
				$SIMPLEPAGE->addSimplePage($page_info['id'], $sqlData);
			break;
		case 'WCOM_BLOG':
		case 'WCOM_URL':
		default:
			break;
	}
	
	return true;
}

/**
 * Tests given url name of a page for uniqueness. Takes the page's url
 * name as first argument and an optional page id as second argument. If
 * the page id is given, this page type won't be considered when checking
 * for uniqueness (useful for updates). Returns boolean true if page name
 * is unique.
 *
 * @throws Content_PagetypeException
 * @param string Page name
 * @param int Page id
 * @return bool
 */
public function testForUniqueUrlName ($name, $id = null)
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Use')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Content_PageException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Content_PageException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Content_PageException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		WHERE
			`project` = :project
		  AND
			`name_url` = :name
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
 * Tests whether given page belongs to current project. Takes the
 * page id as first argument. Returns bool.
 *
 * @throws Content_PageException
 * @param int Page id
 * @return int bool
 */
public function pageBelongsToCurrentProject ($page)
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Use')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
		
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_PageException('Input for parameter page is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		WHERE
			`content_pages`.`id` = :page
		  AND
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'page' => (int)$page,
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
 * Test whether page belongs to current user or not. Takes
 * the page id as first argument. Returns bool.
 *
 * @throws Content_PageException
 * @param int page id
 * @return bool
 */
public function pageBelongsToCurrentUser ($page)
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Use')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_PageException('Input for parameter page is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->pageBelongsToCurrentProject($page)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

/**
 * Sets index page to given page id. Takes the page id as
 * first argument. Returns amount of affected rows. 
 * 
 * @throws Content_PageException
 * @param int Page id
 * @return int Affected rows
 */
public function setIndexPage ($page)
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Manage')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_PageException('Input for parameter page is expected to be a numeric value');
	}
	
	// unset all existing index pages.
	$sql = "
		UPDATE
			".WCOM_DB_CONTENT_PAGES." 
		SET
			`index_page` = '0'
		WHERE
			`index_page` = '1'
		  AND
			`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute update
	$this->base->db->execute($sql, $bind_params);
	
	// set given page as index page
	return $this->updatePage($page, array('index_page' => '1'));
}

/**
 * Checks if current user has access to given page. Takes the page id
 * as first argument, the flag, if the page has to be protected or not,
 * as second argument. Returns bool.
 *
 * @throws Content_PageException
 * @param int Page id
 * @param bool Protect flag
 * @return bool
 */
public function checkAccess ($page, $protect_flag)
{
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_PageException('Input for parameter page is expected to be a numeric value');
	}
	
	// if the protect flag is false, we don't have to protect this page
	if (!$protect_flag) {
		return true;
	}
	
	// get the user group of the current user
	$group = Base_Cnc::filterRequest($_SESSION['public_area'][WCOM_CURRENT_PROJECT]['group'],
		WCOM_REGEX_NUMERIC);
	
	// if there's not group, we can't grant access
	if (is_null($group)) {
		return false;
	}
	
	// get the page-to-groups map so that we can see if the group of
	// the user may access this page. 
	$map = $this->selectPageToGroupsMap($page);
	foreach ($map as $_node) {
		if ($_node['group'] == $group) {
			return true;
		}
	}
	
	return false;
}

/**
 * Resolves page using the available url params. Returns the page id on
 * success or throws an exception on failure.
 * 
 * The function either expects the plain page id (~ $_REQUEST['page']) or
 * the url name of a page (~ $_REQUEST['page_name]).
 * 
 * @throws Content_BlogPostingException
 * @return int
 */
public function resolvePage ()
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Use')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
	
	// get page id and url name from url
	$page_id = Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC);
	$page_name = Base_Cnc::filterRequest($_REQUEST['page_name'], WCOM_REGEX_MEANINGFUL_STRING);
	
	if (!is_null($page_id)) {
		if ($this->pageExists($page_id)) {
			return (int)$page_id;
		} else {
			throw new Content_PageException("Requested page could not be found");
		}
	} elseif (!is_null($page_name)) {
		// resolve page by name
		$sql = "
			SELECT
			 	`id`
			FROM
				".WCOM_DB_CONTENT_PAGES."
			WHERE
				`name_url` = :name_url
			  AND
				`project` = :project
			LIMIT 1
		";
	
		// prepare bind params
		$bind_params = array(
			'name_url' => (string)$page_name,
			'project' => WCOM_CURRENT_PROJECT
		);
	
		// execute query and evaluate result
		$result = intval($this->base->db->select($sql, 'field', $bind_params));
		if ($result > 1) {
			return (int)$result;
		} else {
			throw new Content_PageException("Requested page could not be found");
		}
	} else {
		// get index get
		$page = $this->selectIndexPage();
		
		// if there's an index page, return it's id. if not, throw an
		// exception
		if (!is_null(Base_Cnc::filterRequest($page['id'], WCOM_REGEX_NUMERIC))) {
			return (int)$page['id'];
		} else {
			throw new Content_PageException("Requested page could not be found");
		}
	}
}

/**
 * Tests whether page exists or not. Takes the page id as first
 * argument. Returns bool.
 *
 * @throws Content_PageException
 * @param int Page id
 * @return bool
 */ 
public function pageExists ($id)
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Use')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_PageException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		WHERE
			`content_pages`.`id` = :id
		  AND
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
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
 * Returns path from root to target node. Takes the id of the target
 * node as first argument. Returns id.
 *
 * @throws Content_PageException
 * @param int Page id
 * @return array
 */
public function selectPath ($target)
{
	// access check
	if (!wcom_check_access('Content', 'Page', 'Use')) {
		throw new Content_PageException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($target) || !is_numeric($target)) {
		throw new Content_PageException('Input for parameter target is not numeric');
	}
	
	// prepare query
	$sql = "
		SELECT
			`content_pages`.`id` AS `id`,
			`content_pages`.`project` AS `project`,
			`content_nodes`.`navigation` AS `navigation`,
			`content_nodes`.`root_node` AS `root_node`,
			`content_nodes`.`parent` AS `parent`,
			`content_nodes`.`lft` AS `lft`,
			`content_nodes`.`rgt` AS `rgt`,
			`content_nodes`.`level` AS `level`,
			`content_nodes`.`sorting` AS `sorting`,
			`content_pages`.`type` AS `type`,
			`content_pages`.`template_set` AS `template_set`,
			`content_pages`.`name` AS `name`,
			`content_pages`.`name_url` AS `name_url`,
			`content_pages`.`alternate_name` AS `alternate_name`,
			`content_pages`.`description` AS `description`,
			`content_pages`.`optional_text` AS `optional_text`,
			`content_pages`.`url` AS `url`,
			`content_pages`.`protect` AS `protect`,
			`content_pages`.`index_page` AS `index_page`,
			`content_pages`.`sitemap_changefreq` AS `sitemap_changefreq`,
			`content_pages`.`sitemap_priority` AS `sitemap_priority`,
			`content_page_types`.`name` AS `page_type_name`,
			`content_page_types`.`internal_name` AS `page_type_internal_name`
		FROM
			".WCOM_DB_CONTENT_NODES." `content_nodes`,
			".WCOM_DB_CONTENT_NODES." `content_nodes_alias`
		JOIN
			`content_pages`
		  ON
			`content_nodes_alias`.`id` = `content_pages`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGE_TYPES." AS `content_page_types`
		  ON
			`content_pages`.`type` = `content_page_types`.`id`
		WHERE
			`content_pages`.`project` = :project
		  AND
			`content_nodes`.`lft` BETWEEN `content_nodes_alias`.`lft` AND `content_nodes_alias`.`rgt`
		  AND
			`content_nodes`.`id` = :id
		  AND
			`content_nodes_alias`.`navigation` = `content_nodes`.`navigation`
		  AND
			`content_nodes_alias`.`root_node` = `content_nodes`.`root_node`
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT,
		'id' => (int)$target
	);
	
	// add sorting
	$sql .= " ORDER BY `content_nodes`.`sorting`, `content_nodes`.`lft` ";
	
	return $this->base->db->select($sql, 'multi', $bind_params);
}

// end of class
}

class Content_PageException extends Exception { }

?>
