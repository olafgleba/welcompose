<?php

/**
 * Project: Welcompose
 * File: simpledate.class.php
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
 * @copyright 2009 creatics, Olaf Gleba
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton for Content_SimpleDate.
 * 
 * @return object
 */
function Content_SimpleDate ()
{
	if (Content_SimpleDate::$instance == null) {
		Content_SimpleDate::$instance = new Content_SimpleDate(); 
	}
	return Content_SimpleDate::$instance;
}

class Content_SimpleDate {
	
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
 * Adds simple date to the simple date table. Takes a field=>value
 * array with simple date data as first argument. Returns insert id. 
 * 
 * @throws Content_SimpleDateException
 * @param array Row data
 * @return int Insert id
 */
public function addSimpleDate ($sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleDate', 'Manage')) {
		throw new Content_SimpleDateException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Content_SimpleDateException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_CONTENT_SIMPLE_DATES, $sqlData);
	
	// test if simple date belongs to current user
	if (!$this->simpleDateBelongsToCurrentUser($insert_id)) {
		throw new Content_SimpleDateException('Simple Date does not belong to current project or user');
	}
	
	return $insert_id;
}

/**
 * Updates simple date. Takes the simple date id as first argument, a
 * field=>value array with the new simple date data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_SimpleDateException
 * @param int Simple Date id
 * @param array Row data
 * @return int Affected rows
*/
public function updateSimpleDate ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleDate', 'Manage')) {
		throw new Content_SimpleDateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimpleDateException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_SimpleDateException('Input for parameter sqlData is not an array');	
	}
	
	// test if simple date belongs to current user
	if (!$this->simpleDateBelongsToCurrentUser($id)) {
		throw new Content_SimpleDateException('Simple Date does not belong to current project or user');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_CONTENT_SIMPLE_DATES, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes simple date from the simple dates table. Takes the
 * simple date id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Content_SimpleDateException
 * @param int Simple Date id
 * @return int Amount of affected rows
 */
public function deleteSimpleDate ($id)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleDate', 'Manage')) {
		throw new Content_SimpleDateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimpleDateException('Input for parameter id is not numeric');
	}
	
	// test if simple date belongs to current user
	if (!$this->simpleDateBelongsToCurrentUser($id)) {
		throw new Content_SimpleDateException('Simple Date does not belong to current project or user');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_CONTENT_SIMPLE_DATES, $where, $bind_params);
}

/**
 * Selects one simple date. Takes the simple date id as first
 * argument. Returns array with simple date information.
 * 
 * @throws Content_SimpleDateException
 * @param int Simple Date id
 * @return array
 */
public function selectSimpleDate ($id)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleDate', 'Use')) {
		throw new Content_SimpleDateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimpleDateException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`content_simple_dates`.`id` AS `id`,
			`content_simple_dates`.`page` AS `page`,
			`content_simple_dates`.`user` AS `user`,
			`content_simple_dates`.`date_start` AS `date_start`,
			`content_simple_dates`.`date_end` AS `date_end`,
			`content_simple_dates`.`location_raw` AS `location_raw`,
			`content_simple_dates`.`location` AS `location`,
			`content_simple_dates`.`link_1` AS `link_1`,
			`content_simple_dates`.`link_2` AS `link_2`,
			`content_simple_dates`.`link_3` AS `link_3`,
			`content_simple_dates`.`sold_out_1` AS `sold_out_1`,
			`content_simple_dates`.`sold_out_2` AS `sold_out_2`,
			`content_simple_dates`.`sold_out_3` AS `sold_out_3`,
			`content_simple_dates`.`text_converter` AS `text_converter`,
			`content_simple_dates`.`apply_macros` AS `apply_macros`,
			`content_simple_dates`.`draft` AS `draft`,
			`content_simple_dates`.`ping` AS `ping`,
			`content_simple_dates`.`pingbacks_enable` AS `pingbacks_enable`,
			`content_simple_dates`.`pingback_count` AS `pingback_count`,
			`content_simple_dates`.`date_modified` AS `date_modified`,
			`content_simple_dates`.`date_added` AS `date_added`,
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
			`content_pages`.`alternate_name` AS `page_alternate_name`,
			`content_pages`.`description` AS `page_description`,
			`content_pages`.`optional_text` AS `page_optional_text`,
			`content_pages`.`url` AS `page_url`,
			`content_pages`.`protect` AS `page_protect`,
			`content_pages`.`index_page` AS `page_index_page`,
			`content_pages`.`image_small` AS `page_image_small`,
			`content_pages`.`image_medium` AS `page_image_medium`,
			`content_pages`.`image_big` AS `page_image_big`,
			`content_pages`.`sitemap_changefreq` AS `page_sitemap_changefreq`,
			`content_pages`.`sitemap_priority` AS `page_sitemap_priority`
		FROM
			".WCOM_DB_CONTENT_SIMPLE_DATES." AS `content_simple_dates`
		JOIN
			".WCOM_DB_USER_USERS." AS `user_users`
		  ON
			`content_simple_dates`.`user` = `user_users`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_simple_dates`.`page` = `content_pages`.`id`
		JOIN
			".WCOM_DB_CONTENT_NODES." AS `content_nodes`
		  ON
			`content_pages`.`id` = `content_nodes`.`id`
		WHERE
			`content_simple_dates`.`id` = :id
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
 * Method to select one or more simple dates. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>page, int, optional: Page id</li>
 * <li>draft, int, optional: Draft bit (0/1)</li>
 * <li>timeframe, string, optional: specific range of rows to return</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * <li>order_marco, string, otpional: How to sort the result set.
 * Supported macros:
 *    <ul>
 *		<li>DATE_MODIFIED: sorty by date modified</li>
 *		<li>DATE_ADDED: sort by date added</li>
 *		<li>DATE_START: sort by start date</li>
 *		<li>DATE_END: sort by end date</li>
 *	 	<li>RANDOM: sort by random</li>
 *    </ul>
 * </li>
 * </ul>
 * 
 * @throws Content_SimpleDateException
 * @param array Select params
 * @return array
 */
public function selectSimpleDates ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'SimpleDate', 'Use')) {
		throw new Content_SimpleDateException("You are not allowed to perform this action");
	}
	
	// define some vars
	$page = null;
	$draft = null;
	$timeframe = null;
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_SimpleDateException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'timeframe':
			case 'order_macro':
					$$_key = (string)$_value;
				break;
			case 'page':
			case 'user':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			case 'draft':
					$$_key = (is_null($_value) ? null : (string)$_value);
				break;
			default:
				throw new Content_SimpleDateException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'DATE_ADDED' => '`content_simple_dates`.`date_added`',
		'DATE_MODIFIED' => '`content_simple_dates`.`date_modified`',
		'DATE_START' => '`content_simple_dates`.`date_start`',
		'DATE_END' => '`content_simple_dates`.`date_end`',
		'RANDOM' => 'rand()'
	);
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// prepare query
	$sql = "
		SELECT 
			`content_simple_dates`.`id` AS `id`,
			`content_simple_dates`.`page` AS `page`,
			`content_simple_dates`.`user` AS `user`,
			`content_simple_dates`.`date_start` AS `date_start`,
			`content_simple_dates`.`date_end` AS `date_end`,
			`content_simple_dates`.`location_raw` AS `location_raw`,
			`content_simple_dates`.`location` AS `location`,
			`content_simple_dates`.`link_1` AS `link_1`,
			`content_simple_dates`.`link_2` AS `link_2`,
			`content_simple_dates`.`link_3` AS `link_3`,
			`content_simple_dates`.`sold_out_1` AS `sold_out_1`,
			`content_simple_dates`.`sold_out_2` AS `sold_out_2`,
			`content_simple_dates`.`sold_out_3` AS `sold_out_3`,
			`content_simple_dates`.`text_converter` AS `text_converter`,
			`content_simple_dates`.`apply_macros` AS `apply_macros`,
			`content_simple_dates`.`draft` AS `draft`,
			`content_simple_dates`.`ping` AS `ping`,
			`content_simple_dates`.`pingbacks_enable` AS `pingbacks_enable`,
			`content_simple_dates`.`pingback_count` AS `pingback_count`,
			`content_simple_dates`.`date_modified` AS `date_modified`,
			`content_simple_dates`.`date_added` AS `date_added`,
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
			`content_pages`.`alternate_name` AS `page_alternate_name`,
			`content_pages`.`description` AS `page_description`,
			`content_pages`.`optional_text` AS `page_optional_text`,
			`content_pages`.`url` AS `page_url`,
			`content_pages`.`protect` AS `page_protect`,
			`content_pages`.`index_page` AS `page_index_page`,
			`content_pages`.`image_small` AS `page_image_small`,
			`content_pages`.`image_medium` AS `page_image_medium`,
			`content_pages`.`image_big` AS `page_image_big`,
			`content_pages`.`sitemap_changefreq` AS `page_sitemap_changefreq`,
			`content_pages`.`sitemap_priority` AS `page_sitemap_priority`
		FROM
			".WCOM_DB_CONTENT_SIMPLE_DATES." AS `content_simple_dates`
		JOIN
			".WCOM_DB_USER_USERS." AS `user_users`
		  ON
			`content_simple_dates`.`user` = `user_users`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_simple_dates`.`page` = `content_pages`.`id`
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
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `content_pages`.`id` = :page ";
		$bind_params['page'] = $page;
	}
	if (!is_null($draft) && is_numeric($draft)) {
		$sql .= " AND `content_simple_dates`.`draft` = :draft ";
		$bind_params['draft'] = (string)$draft;
	}
	if (!empty($timeframe)) {
		$sql .= " AND ".$HELPER->_sqlForTimeFrame('`content_simple_dates`.`date_added`',
			$timeframe);
	}
	
	// aggregate result set
	$sql .= " GROUP BY `content_simple_dates`.`id` ";
	
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
 * Method to count simple dates. Takes key=>value array
 * with select params as first argument. Returns int.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>page, int, optional: Page id</li>
 * <li>draft, int, optional: Draft bit (0/1)</li>
 * <li>draft, int, optional: Draft bit (0/1)</li>
 * <li>timeframe, string, optional: specific range of rows to return</li>
 * </ul>
 * 
 * @throws Content_SimpleDateException
 * @param array Select params
 * @return int
 */
public function countSimpleDates ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'SimpleDate', 'Use')) {
		throw new Content_SimpleDateException("You are not allowed to perform this action");
	}
	
	// define some vars
	$page = null;
	$draft = null;
	$timeframe = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_SimpleDateException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'timeframe':
					$$_key = (string)$_value;
				break;
			case 'page':
					$$_key = (int)$_value;
				break;
			case 'draft':
					$$_key = (is_null($_value) ? null : (string)$_value);
				break;
			default:
				throw new Content_SimpleDateException("Unknown parameter $_key");
		}
	}
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(DISTINCT `content_simple_dates`.`id`) AS `total`
		FROM
			".WCOM_DB_CONTENT_SIMPLE_DATES." AS `content_simple_dates`
		JOIN
			".WCOM_DB_USER_USERS." AS `user_users`
		  ON
			`content_simple_dates`.`user` = `user_users`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_simple_dates`.`page` = `content_pages`.`id`
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
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `content_pages`.`id` = :page ";
		$bind_params['page'] = $page;
	}
	if (!is_null($draft) && is_numeric($draft)) {
		$sql .= " AND `content_simple_dates`.`draft` = :draft ";
		$bind_params['draft'] = (string)$draft;
	}
	if (!empty($timeframe)) {
		$sql .= " AND ".$HELPER->_sqlForTimeFrame('`content_simple_dates`.`date_added`',
			$timeframe);
	}
	
	return $this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Tests if simple date exists. Takes the id of the simple date
 * as first argument. Returns bool.
 *
 * @throws Content_SimpleDateException
 * @param int Simple Date id
 * @return bool
 */
public function simpleDateExists ($id)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleDate', 'Use')) {
		throw new Content_SimpleDateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimpleDateException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_SIMPLE_DATES." AS `content_simple_dates`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_simple_dates`.`page` = `content_pages`.`id`
		WHERE
			`content_simple_dates`.`id` = :id
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
 * Tests whether given simple date belongs to current project. Takes the
 * simple date id as first argument. Returns bool.
 *
 * @throws Content_SimpleDateException
 * @param int Simple Date id
 * @return int bool
 */
public function simpleDateBelongsToCurrentProject ($simple_date)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleDate', 'Use')) {
		throw new Content_SimpleDateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($simple_date) || !is_numeric($simple_date)) {
		throw new Content_SimpleDateException('Input for parameter simple_date is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_SIMPLE_DATES." AS `content_simple_dates`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_simple_dates`.`page` = `content_pages`.`id`
		WHERE
			`content_simple_dates`.`id` = :simple_date
		  AND
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'simple_date' => (int)$simple_date,
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
 * Test whether simple date belongs to current user or not. Takes
 * the simple date id as first argument. Returns bool.
 *
 * @throws Content_SimpleDateException
 * @param int Simple Date id
 * @return bool
 */
public function simpleDateBelongsToCurrentUser ($simple_date)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleDate', 'Use')) {
		throw new Content_SimpleDateException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($simple_date) || !is_numeric($simple_date)) {
		throw new Content_SimpleDateException('Input for parameter simple_date is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->simpleDateBelongsToCurrentProject($simple_date)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}


/**
 * Tests given date array wether the values are not null and in workable format. 
 * Returns boolean true if month, day and year values are in a workable format. 
 *
 * @var array
 * @return bool
 */
public function checkDateOnEmpty ($dates) 
{
	// access check
	if (!wcom_check_access('Content', 'SimpleDate', 'Use')) {
		throw new Content_SimpleDateException("You are not allowed to perform this action");
	}
	
	// test the array
	if (checkdate($dates['m'], $dates['d'], $dates['Y'])) {
		return true;
	} else {
		return false;
	}
}


// end of class
}

class Content_SimpleDateException extends Exception { }

?>