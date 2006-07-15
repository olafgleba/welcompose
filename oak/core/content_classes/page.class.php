<?php

/**
 * Project: Oak
 * File: page.class.php
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

class Content_Page {
	
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
 * Singleton. Returns instance of the Content_Page object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Content_Page::$instance == null) {
		Content_Page::$instance = new Content_Page(); 
	}
	return Content_Page::$instance;
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
	// input check
	if (!is_array($sqlData)) {
		throw new Content_PageException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	return $this->base->db->insert(OAK_DB_CONTENT_PAGES, $sqlData);
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
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_PageException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_PageException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(OAK_DB_CONTENT_PAGES, $sqlData,
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
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_PageException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_CONTENT_PAGES, $where, $bind_params);
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
			`content_pages`.`index_page` AS `index_page`,
			`content_pages`.`protect` AS `protect`,
			`content_page_types`.`id` AS `page_type_id`,
			`content_page_types`.`name` AS `page_type_name`
		FROM
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		JOIN
			".OAK_DB_CONTENT_NODES." AS `content_nodes`
		  ON
			`content_pages`.`id` = `content_nodes`.`id`
		JOIN
			".OAK_DB_CONTENT_PAGE_TYPES." AS `content_page_types`
		  ON
			`content_pages`.`type` = `content_page_types`.`id`
		WHERE 
			1
	";
	
	// prepare where clauses
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `content_pages`.`id` = :id ";
		$bind_params['id'] = (int)$id;
	}
	
	// add limits
	$sql .= ' LIMIT 1';
	
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
	// define some vars
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
			`content_pages`.`id` AS `id`,
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
			`content_pages`.`index_page` AS `index_page`,
			`content_pages`.`protect` AS `protect`,
			`content_page_types`.`id` AS `page_type_id`,
			`content_page_types`.`name` AS `page_type_name`
		FROM
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		JOIN
			".OAK_DB_CONTENT_NODES." AS `content_nodes`
		  ON
			`content_pages`.`id` = `content_nodes`.`id`
		JOIN
			".OAK_DB_CONTENT_PAGE_TYPES." AS `content_page_types`
		  ON
			`content_pages`.`type` = `content_page_types`.`id`
		WHERE 
			1
	";
	
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
 * Selects index page. Returns array with the complete page
 * information.
 *
 * @throws Content_PageException
 * @return array
 */
public function selectIndexPage ()
{
	// get id of the index page
	$sql = "
		SELECT 
			`content_pages`.`id` AS `id`
		FROM
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		WHERE
			`content_pages`.`index_page` = '1'
		LIMIT
			1
	";
	
	// execute query
	$result = (int)$this->base->db->select($sql, 'field');
	
	// make sure that there is some index page
	if ($result < 1) {
		throw new Content_PageException("Unable to find an index page");
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
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_PageException("Input for parameter page is not numeric");
	}
	if (!is_array($groups)) {
		throw new Content_PageException("Input for parameter groups is expected to be an array");
	}
	
	// let's see if the given template belongs to the current project
	if (!$this->pageBelongsToCurrentProject($page)) {
		throw new Templating_TemplateException('Given page does not belong to the current project');
	}
	
	// load group class
	$GROUP = load('user:group');
	
	// prepare query to remove all existing links to the current template
	$sql = "
		DELETE FROM
			".OAK_DB_CONTENT_PAGES2USER_GROUPS." AS `content_pages2user_groups`
		USING
			".OAK_DB_CONTENT_PAGES2USER_GROUPS." AS `content_pages2user_groups`
		JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
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
		'project' => (int)OAK_CURRENT_PROJECT
	);
	
	// remove all existing links to the given page
	$this->base->db->execute($sql, $bind_params);
	
	// add new links
	foreach ($groups as $_group) {
		if (!empty($_group) && is_numeric($_group) && $GROUP->groupBelongsToCurrentProject($_group)) {
			$this->base->db->insert(OAK_DB_CONTENT_PAGES2USER_GROUPS, array('page' => $page, 'group' => $_group));
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
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_PageException("Input for parameter page is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT
			`content_pages2user_groups`.`id`,
			`content_pages2user_groups`.`page`,
			`content_pages2user_groups`.`group`
		FROM
			".OAK_DB_CONTENT_PAGES2USER_GROUPS." AS `content_pages2user_groups`
		JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
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
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'multi', $bind_params);
}

/**
 * Tests whether given page belongs to current project. Takes the page
 * id as first argument. Returns boolean true or false.
 *
 * @throws Content_PageException
 * @param int Page id
 * @return bool
 */
public function pageBelongsToCurrentProject ($page)
{
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_PageException('Input for parameter page is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		WHERE
			`content_pages`.`id` = :page
		AND
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'page' => (int)$page,
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
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_PageException('Input for parameter page  is expected to be numeric');
	}
	
	// test if page belongs to current project
	if (!$this->pageBelongsToCurrentProject($page)) {
		throw new Content_PageException('Given page does not belong to current project');
	}
	
	// get page information
	$page_info = $this->selectPage($page);
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// handle the different page types
	switch((string)$page_info['page_type_name']) {
		case 'OAK_SIMPLE_FORM':
				// prepare sql data
				$sqlData = array(
					'id' => $page_info['id'],
					'user' => OAK_CURRENT_USER,
					'title' => $page_info['name'],
					'title_url' => $HELPER->createMeaningfulString($page_info['name']),
					'date_added' => date('Y-m-d H:i:s')
				);
				
				// create simple form
				$SIMPLEFORM = load('content:simpleform');
				$SIMPLEFORM->addSimpleForm($sqlData);
			break;
		case 'OAK_SIMPLE_PAGE':
				// prepare sql data
				$sqlData = array(
					'id' => $page_info['id'],
					'user' => OAK_CURRENT_USER,
					'title' => $page_info['name'],
					'title_url' => $HELPER->createMeaningfulString($page_info['name']),
					'date_added' => date('Y-m-d H:i:s')
				);
				
				// create simple page
				$SIMPLEPAGE = load('content:simplepage');
				$SIMPLEPAGE->addSimplePage($sqlData);
			break;
		case 'OAK_BLOG':
		case 'OAK_URL':
		default:
			break;
	}
	
	return true;
}


// end of class
}

class Content_PageException extends Exception { }

?>