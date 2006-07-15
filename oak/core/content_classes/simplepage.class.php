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

class Content_Simplepage {
	
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
 * Singleton. Returns instance of the Content_Simplepage object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Content_Simplepage::$instance == null) {
		Content_Simplepage::$instance = new Content_Simplepage(); 
	}
	return Content_Simplepage::$instance;
}

/**
 * Adds simple page to the simple page table. Takes a field=>value
 * array with simple page data as first argument. Returns insert id. 
 * 
 * @throws Content_SimplepageException
 * @param array Row data
 * @return int Simple page
 */
public function addSimplePage ($sqlData)
{
	if (!is_array($sqlData)) {
		throw new Content_SimplepageException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	return $this->base->db->insert(OAK_DB_CONTENT_SIMPLE_PAGES, $sqlData);
}

/**
 * Updates simple page. Takes the simple page id as first argument, a
 * field=>value array with the new simple page data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_SimplepageException
 * @param int Simple page id
 * @param array Row data
 * @return int Affected rows
*/
public function updateSimplePage ($id, $sqlData)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimplepageException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_SimplepageException('Input for parameter sqlData is not an array');	
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
 * @throws Content_SimplepageException
 * @param int Simple page id
 * @return int Amount of affected rows
 */
public function deleteSimplePage ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimplepageException('Input for parameter id is not numeric');
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
 * @throws Content_SimplepageException
 * @param int Simple page id
 * @return array
 */
public function selectSimplePage ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimplepageException('Input for parameter id is not numeric');
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
			`content_pages`.`project` = :current_project
		AND
			`content_simple_pages`.`user` = :current_user
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'current_project' => (int)OAK_CURRENT_PROJECT,
		'current_user' => (int)OAK_CURRENT_USER
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
 *        <li>USER_NAME: sort by user name</li>
 *        <li>PAGE: sorty by page id</li>
 *        <li>DATE_MODIFIED: sorty by date modified</li>
 *        <li>DATE_ADDED: sort by date added</li>
 *    </ul>
 * </li>
 * </ul>
 * 
 * @throws Content_SimplepageException
 * @param array Select params
 * @return array
 */
public function selectSimplePages ($params = array())
{
	// define some vars
	$user = null;
	$page = null;
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_SimplepageException('Input for parameter params is not an array');	
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
				throw new Content_SimplepageException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'USER_NAME' => '`application_users`.`name`',
		'PAGE' => '`content_simple_pages`.`page`',
		'DATE_ADDED' => '`content_simple_pages`.`date_added` AS `date_added`',
		'DATE_MODIFIED' => '`content_simple_pages`.`date_modified`'
	);
	
	// prepare query
	$sql = "
		SELECT 
			`content_simple_pages`.`id` AS `id`,
			`content_simple_pages`.`user` AS `user`,
			`content_simple_pages`.`page` AS `page`,
			`content_simple_pages`.`title` AS `title`,
			`content_simple_pages`.`title_url` AS `title_url`,
			`content_simple_pages`.`content_raw` AS `content_raw`,
			`content_simple_pages`.`content` AS `content`,
			`content_simple_pages`.`date_modified` AS `date_modified`,
			`content_simple_pages`.`date_added` AS `date_added`,
			`application_users`.`id` AS `user_id`,
			`application_users`.`group` AS `user_group`,
			`application_users`.`name` AS `user_name`,
			`application_users`.`email` AS `user_email`,
			`application_users`.`homepage` AS `user_homepage`,
			`application_users`.`pwd` AS `user_pwd`,
			`application_users`.`public_email` AS `user_public_email`,
			`application_users`.`public_profile` AS `user_public_profile`,
			`application_users`.`author` AS `user_author`,
			`application_users`.`date_modified` AS `user_date_modified`,
			`application_users`.`date_added` AS `user_date_added`,
			`content_pages`.`id` AS `page_id`,
			`content_pages`.`navigation` AS `page_navigation`,
			`content_pages`.`root_node` AS `page_root_node`,
			`content_pages`.`parent` AS `page_parent`,
			`content_pages`.`level` AS `page_level`,
			`content_pages`.`sorting` AS `page_sorting`,
			`content_pages`.`type` AS `page_type`,
			`content_pages`.`template_set` AS `page_template_set`,
			`content_pages`.`name` AS `page_name`,
			`content_pages`.`name_url` AS `page_name_url`,
			`content_pages`.`protect` AS `page_protect`
		FROM
			".OAK_DB_CONTENT_SIMPLE_PAGES." AS `content_simple_pages`
		LEFT JOIN
			".OAK_DB_USER_USERS." AS `application_users`
		ON
			`content_simple_pages`.`user` = `application_users`.`id`
		LEFT JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		ON
			`content_simple_pages`.`page` = `content_pages`.`id`
		WHERE
			1
	";
	
	// add where clauses
	if (!empty($user) && is_numeric($user)) {
		$sql .= " AND `application_users`.`id` = :user ";
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

// end of class
}

class Content_SimplepageException extends Exception { }

?>