<?php

/**
 * Project: Welcompose
 * File: simpleguestbookentry.class.php
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
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton for Content_SimpleGuestbookEntry.
 * 
 * @return object
 */
function Content_SimpleGuestbookEntry ()
{
	if (Content_SimpleGuestbookEntry::$instance == null) {
		Content_SimpleGuestbookEntry::$instance = new Content_SimpleGuestbookEntry(); 
	}
	return Content_SimpleGuestbookEntry::$instance;
}

class Content_SimpleGuestbookEntry {
	
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
 * Adds book field to the simple guestbook entrys table. Takes a field=>value
 * array with book field data as first argument. Returns insert id.
 *
 * @throws Content_SimpleGuestbookEntryException
 * @param array Row data
 * @return int Form field id
 */
public function addSimpleGuestbookEntry ($sqlData)
{	
	// input check
	if (!is_array($sqlData)) {
		throw new Content_SimpleGuestbookEntryException('Input for parameter sqlData is not an array');
	}
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_CONTENT_SIMPLE_GUESTBOOK_ENTRIES, $sqlData);
	
	// test if simple guestbook entry belongs to current user/project
	if (!$this->simpleGuestbookEntryBelongsToCurrentUser($insert_id)) {
		throw new Content_SimpleGuestbookEntryException('The Simple Guestbook Entry does not belong to current user or project');
	}
	
	return (int)$insert_id;
}

/**
 * Updates simple guestbook entry. Takes the simple guestbook entry id as first
 * argument, a field=>value array with the new generator form data as second
 * argument. Returns amount of affected rows.
 *
 * @throws Content_SimpleGuestbookEntryException
 * @param int Simple Guestbook Entry id
 * @param array Row data
 * @return int Affected rows
*/
public function updateSimpleGuestbookEntry ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleGuestbookEntry', 'Manage')) {
		throw new Content_SimpleGuestbookEntryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimpleGuestbookEntryException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_SimpleGuestbookEntryException('Input for parameter sqlData is not an array');	
	}
	
	// test if simple guestbook entry belongs to current user/project
	if (!$this->simpleGuestbookEntryBelongsToCurrentUser($id)) {
		throw new Content_SimpleGuestbookEntryException('Simple Guestbook Entry does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_CONTENT_SIMPLE_GUESTBOOK_ENTRIES, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes simple guestbook entry from the simple guestbook entry table. Takes
 * the simple guestbook entry id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Content_SimpleGuestbookEntryException
 * @param int Simple Guestbook Entry id
 * @return int Amount of affected rows
 */
public function deleteSimpleGuestbookEntry ($id)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleGuestbookEntry', 'Manage')) {
		throw new Content_SimpleGuestbookEntryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimpleGuestbookEntryException('Input for parameter id is not numeric');
	}
	
	// test if simple form belongs to current user/project
	if (!$this->simpleGuestbookEntryBelongsToCurrentUser($id)) {
		throw new Content_SimpleGuestbookEntryException('Simple Guestbook Entry does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_CONTENT_SIMPLE_GUESTBOOK_ENTRIES, $where, $bind_params);
}

/**
 * Selects a simple guestbook entry. Takes the simple guestbook entry id
 * as first argument. Returns array with simple guestbook entry information.
 * 
 * @throws Content_SimpleGuestbookEntryException
 * @param int Simple Guestbook Entry id
 * @return array
 */
public function selectSimpleGuestbookEntry ($id)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleGuestbookEntry', 'Use')) {
		throw new Content_SimpleGuestbookEntryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimpleGuestbookEntryException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`content_simple_guestbook_entries`.`id` AS `id`,
			`content_simple_guestbook_entries`.`book` AS `book`,
			`content_simple_guestbook_entries`.`user` AS `user`,
			`content_simple_guestbook_entries`.`name` AS `name`,
			`content_simple_guestbook_entries`.`email` AS `email`,
			`content_simple_guestbook_entries`.`subject` AS `subject`,
			`content_simple_guestbook_entries`.`content` AS `content`,
			`content_simple_guestbook_entries`.`content_raw` AS `content_raw`,
			`content_simple_guestbook_entries`.`text_converter` AS `text_converter`,
			`content_simple_guestbook_entries`.`date_modified` AS `date_modified`,
			`content_simple_guestbook_entries`.`date_added` AS `date_added`,
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
			".WCOM_DB_CONTENT_SIMPLE_GUESTBOOK_ENTRIES." AS `content_simple_guestbook_entries`
		JOIN
			".WCOM_DB_CONTENT_SIMPLE_GUESTBOOKS." AS `content_simple_guestbooks`
		  ON
			`content_simple_guestbook_entries`.`book` = `content_simple_guestbooks`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_simple_guestbooks`.`id` = `content_pages`.`id`
		JOIN
			".WCOM_DB_CONTENT_NODES." AS `content_nodes`
		  ON
			`content_pages`.`id` = `content_nodes`.`id`
		WHERE
			`content_simple_guestbook_entries`.`id` = :id
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
 * Method to select one or more simple guestbook entrys. Takes
 * key=>value array with select params as first argument. 
 * Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>book, int, optional: Form id</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * <li>timeframe, string, optional: specific range of rows to return</li>
 * <li>order_marco, string, otpional: How to sort the result set.
 * Supported macros:
 *    <ul>
 *		<li>DATE_MODIFIED: sorty by date modified</li>
 *		<li>DATE_ADDED: sort by date added</li>
 *	 	<li>RANDOM: sort by random</li>
 *	 	<li>NAME: sort by name</li>
 *    </ul>
 * </li>
 * </ul>
 * 
 * @throws Content_SimpleGuestbookEntryException
 * @param array Select params
 * @return array
 */
public function selectSimpleGuestbookEntries ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'SimpleGuestbookEntry', 'Use')) {
		throw new Content_SimpleGuestbookEntryException("You are not allowed to perform this action");
	}
	
	// define some vars
	$book = null;
	$start = null;
	$limit = null;
	$timeframe = null;
	$search_name = null;
	$order_macro = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_SimpleGuestbookEntryException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'book':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			case 'order_macro':
			case 'timeframe':
			case 'search_name':
					$$_key = (string)$_value;
				break;
			default:
				throw new Content_SimpleGuestbookEntryException("Unknown parameter $_key");
		}
	}
		
	// define order macros
	$macros = array(
		'DATE_ADDED' => '`content_simple_guestbook_entries`.`date_added`',
		'DATE_MODIFIED' => '`content_simple_guestbook_entries`.`date_modified`',
		'RANDOM' => 'rand()',
		'NAME' => '`content_simple_guestbook_entries`.`name`',
	);

	// load helper class
	$HELPER = load('utility:helper');
	
	// prepare query
	$sql = "
		SELECT 
			`content_simple_guestbook_entries`.`id` AS `id`,
			`content_simple_guestbook_entries`.`book` AS `book`,
			`content_simple_guestbook_entries`.`user` AS `user`,
			`content_simple_guestbook_entries`.`name` AS `name`,
			`content_simple_guestbook_entries`.`email` AS `email`,
			`content_simple_guestbook_entries`.`subject` AS `subject`,
			`content_simple_guestbook_entries`.`content` AS `content`,
			`content_simple_guestbook_entries`.`content_raw` AS `content_raw`,
			`content_simple_guestbook_entries`.`text_converter` AS `text_converter`,
			`content_simple_guestbook_entries`.`date_modified` AS `date_modified`,
			`content_simple_guestbook_entries`.`date_added` AS `date_added`,
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
			".WCOM_DB_CONTENT_SIMPLE_GUESTBOOK_ENTRIES." AS `content_simple_guestbook_entries`
		JOIN
			".WCOM_DB_CONTENT_SIMPLE_GUESTBOOKS." AS `content_simple_guestbooks`
		  ON
			`content_simple_guestbook_entries`.`book` = `content_simple_guestbooks`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_simple_guestbooks`.`id` = `content_pages`.`id`
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
	if (!empty($book) && is_numeric($book)) {
		$sql .= " AND `content_simple_guestbook_entries`.`book` = :book ";
		$bind_params['book'] = $book;
	}
	if (!empty($timeframe)) {
		$sql .= " AND ".$HELPER->_sqlForTimeFrame('`content_simple_guestbook_entries`.`date_added`',
			$timeframe);
	}
	if (!empty($search_name)) {
		$sql .= " AND ".$HELPER->_searchLikewise('`content_simple_guestbook_entries`.`name`',
			$search_name);
	}
	
	// add sorting
	if (!empty($order_macro)) {
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
 * Method to count simple guestbook entrys. Takes key=>value array
 * with select count as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>book, int, optional: Form id</li>
 * <li>timeframe, string, optional: specific range of rows to return</li>
 * </ul>
 * 
 * @throws Content_SimpleGuestbookEntryException
 * @param array Count params
 * @return array
 */
public function countSimpleGuestbookEntries ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'SimpleGuestbookEntry', 'Use')) {
		throw new Content_SimpleGuestbookEntryException("You are not allowed to perform this action");
	}
	
	// define some vars
	$book = null;
	$timeframe = null;
	$search_name = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_SimpleGuestbookEntryException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'book':
					$$_key = (int)$_value;
				break;
			case 'timeframe':
			case 'search_name':
					$$_key = (string)$_value;
			break;
			default:
				throw new Content_SimpleGuestbookEntryException("Unknown parameter $_key");
		}
	}
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(DISTINCT `content_simple_guestbook_entries`.`id`) AS `total`
		FROM
			".WCOM_DB_CONTENT_SIMPLE_GUESTBOOK_ENTRIES." AS `content_simple_guestbook_entries`
		JOIN
			".WCOM_DB_CONTENT_SIMPLE_GUESTBOOKS." AS `content_simple_guestbooks`
		  ON
			`content_simple_guestbook_entries`.`book` = `content_simple_guestbooks`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_simple_guestbook_entries`.`book` = `content_pages`.`id`
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
	if (!empty($book) && is_numeric($book)) {
		$sql .= " AND `content_simple_guestbook_entries`.`book` = :book ";
		$bind_params['book'] = $book;
	}	
	if (!empty($search_name)) {
		$sql .= " AND ".$HELPER->_searchLikewise('`content_simple_guestbook_entries`.`name`',
			$search_name);
	}
	if (!empty($timeframe)) {
		$sql .= " AND ".$HELPER->_sqlForTimeFrame('`content_simple_guestbook_entries`.`date_added`',
			$timeframe);
	}
	
	return $this->base->db->select($sql, 'field', $bind_params);
}


/**
 * Tests whether given simple guestbook entry belongs to current
 * project. Takes the generator book id as first argument.
 * Returns bool.
 *
 * @throws Content_SimpleGuestbookEntryException
 * @param int Simple Guestbook Entry id
 * @return int bool
 */
public function simpleGuestbookEntryBelongsToCurrentProject ($simple_guestbook_entry)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleGuestbookEntry', 'Use')) {
		throw new Content_SimpleGuestbookEntryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($simple_guestbook_entry) || !is_numeric($simple_guestbook_entry)) {
		throw new Content_SimpleGuestbookEntryException('Input for parameter simple_guestbook_entry is expected to be numeric');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_SIMPLE_GUESTBOOK_ENTRIES." AS `content_simple_guestbook_entries`
		JOIN
			".WCOM_DB_CONTENT_SIMPLE_GUESTBOOKS." AS `content_simple_guestbooks`
		  ON
			`content_simple_guestbook_entries`.`book` = `content_simple_guestbooks`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_simple_guestbooks`.`id` = `content_pages`.`id`
		WHERE
			`content_simple_guestbook_entries`.`id` = :simple_guestbook_entry
		  AND
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'simple_guestbook_entry' => (int)$simple_guestbook_entry,
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
 * Test whether simple guestbook entry belongs to current user or not.
 * Takes the generator book id as first argument. Returns bool.
 *
 * @throws Content_SimpleGuestbookEntryException
 * @param int Simple Guestbook Entry id
 * @return bool
 */
public function simpleGuestbookEntryBelongsToCurrentUser ($simple_guestbook_entry)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleGuestbookEntry', 'Use')) {
		throw new Content_SimpleGuestbookEntryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($simple_guestbook_entry) || !is_numeric($simple_guestbook_entry)) {
		throw new Content_SimpleGuestbookEntryException('Input for parameter simple_guestbook_entry is expected to be numeric');
	}
	
	// load user class
	$USER = load('User:User');
	
	if (!$this->simpleGuestbookEntryBelongsToCurrentProject($simple_guestbook_entry)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Content_SimpleGuestbookEntryException extends Exception { }

?>