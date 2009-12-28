<?php

/**
 * Project: Welcompose
 * File: simpleguestbook.class.php
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
 * Singleton for Content_SimpleGuestbook.
 * 
 * @return object
 */
function Content_SimpleGuestbook ()
{
	if (Content_SimpleGuestbook::$instance == null) {
		Content_SimpleGuestbook::$instance = new Content_SimpleGuestbook(); 
	}
	return Content_SimpleGuestbook::$instance;
}

class Content_SimpleGuestbook {
	
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
 * Adds a simple guestbook to the simple guestbook table. Takes the page id as first argument,
 * a field=>value array with simple guestbook data as second argument. Returns insert
 * id. 
 * 
 * @throws Content_SimpleGuestbookException
 * @param int Page id
 * @param array Row data
 * @return int Simple guestbook id
 */
public function addSimpleGuestbook ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleGuestbook', 'Manage')) {
		throw new Content_SimpleGuestbookException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Content_SimpleGuestbookException('Input for parameter sqlData is not an array');
	}
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimpleGuestbookException('Input for parameter sqlData is not numeric');
	}
	
	// make sure that the simple guestbook will be associated to the correct page
	$sqlData['id'] = $id;
	
	// insert row
	$this->base->db->insert(WCOM_DB_CONTENT_SIMPLE_GUESTBOOKS, $sqlData);
	
	// test if simple guestbook belongs to current user/project
	if (!$this->simpleGuestbookBelongsToCurrentUser($id)) {
		throw new Content_SimpleGuestbookException('Simple guestbook does not belong to current user or project');
	}
	
	return (int)$page;
}

/**
 * Updates simple guestbook. Takes the simple guestbook id as first argument, a
 * field=>value array with the new simple guestbook data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_SimpleGuestbookException
 * @param int Simple guestbook id
 * @param array Row data
 * @return int Affected rows
*/
public function updateSimpleGuestbook ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleGuestbook', 'Manage')) {
		throw new Content_SimpleGuestbookException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimpleGuestbookException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_SimpleGuestbookException('Input for parameter sqlData is not an array');	
	}
	
	// test if simple guestbook belongs to current user/project
	if (!$this->simpleGuestbookBelongsToCurrentUser($id)) {
		throw new Content_SimpleGuestbookException('Simple guestbook does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_CONTENT_SIMPLE_GUESTBOOKS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes simple guestbook from the simple guestbooks table. Takes the
 * simple guestbook id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Content_SimpleGuestbookException
 * @param int Simple guestbook id
 * @return int Amount of affected rows
 */
public function deleteSimpleGuestbook ($id)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleGuestbook', 'Manage')) {
		throw new Content_SimpleGuestbookException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimpleGuestbookException('Input for parameter id is not numeric');
	}
	
	// test if simple book belongs to current user/project
	if (!$this->simpleGuestbookBelongsToCurrentUser($id)) {
		throw new Content_SimpleGuestbookException('Simple guestbook does not belong to current user or project');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_CONTENT_SIMPLE_GUESTBOOKS, $where, $bind_params);
}

/**
 * Selects one simple guestbook. Takes the simple guestbook id as first
 * argument. Returns array with simple guestbook information.
 * 
 * @throws Content_SimpleGuestbookException
 * @param int Simple guestbook id
 * @return array
 */
public function selectSimpleGuestbook ($id)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleGuestbook', 'Use')) {
		throw new Content_SimpleGuestbookException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimpleGuestbookException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`content_simple_guestbooks`.`id` AS `id`,
			`content_simple_guestbooks`.`user` AS `user`,
			`content_simple_guestbooks`.`title` AS `title`,
			`content_simple_guestbooks`.`title_url` AS `title_url`,
			`content_simple_guestbooks`.`content_raw` AS `content_raw`,
			`content_simple_guestbooks`.`content` AS `content`,
			`content_simple_guestbooks`.`text_converter` AS `text_converter`,
			`content_simple_guestbooks`.`apply_macros` AS `apply_macros`,
			`content_simple_guestbooks`.`meta_use` AS `meta_use`,
			`content_simple_guestbooks`.`meta_title_raw` AS `meta_title_raw`,
			`content_simple_guestbooks`.`meta_title` AS `meta_title`,
			`content_simple_guestbooks`.`meta_keywords` AS `meta_keywords`,
			`content_simple_guestbooks`.`meta_description` AS `meta_description`,
			`content_simple_guestbooks`.`use_captcha` AS `use_captcha`,
			`content_simple_guestbooks`.`send_notification` AS `send_notification`,
			`content_simple_guestbooks`.`notification_email_from` AS `notification_email_from`,
			`content_simple_guestbooks`.`notification_email_to` AS `notification_email_to`,
			`content_simple_guestbooks`.`notification_email_subject` AS `notification_email_subject`,
			`content_simple_guestbooks`.`allow_entry` AS `allow_entry`,
			`content_simple_guestbooks`.`date_modified` AS `date_modified`,
			`content_simple_guestbooks`.`date_added` AS `date_added`,
			`content_nodes`.`id` AS `node_id`,
			`content_nodes`.`navigation` AS `node_navigation`,
			`content_nodes`.`root_node` AS `node_root_node`,
			`content_nodes`.`parent` AS `node_parent`,
			`content_nodes`.`lft` AS `node_lft`,
			`content_nodes`.`rgt` AS `node_rgt`,
			`content_nodes`.`level` AS `node_level`,
			`content_nodes`.`sorting` AS `node_sorting`,
			`content_pages`.`id` AS `book_id`,
			`content_pages`.`project` AS `book_project`,
			`content_pages`.`type` AS `book_type`,
			`content_pages`.`template_set` AS `book_template_set`,
			`content_pages`.`name` AS `book_name`,
			`content_pages`.`name_url` AS `book_name_url`,
			`content_pages`.`alternate_name` AS `page_alternate_name`,
			`content_pages`.`description` AS `page_description`,
			`content_pages`.`optional_text` AS `page_optional_text`,
			`content_pages`.`url` AS `book_url`,
			`content_pages`.`protect` AS `book_protect`,
			`content_pages`.`index_page` AS `book_index_page`,
			`content_pages`.`draft` AS `book_draft`,
			`content_pages`.`image_small` AS `book_image_small`,
			`content_pages`.`image_medium` AS `book_image_medium`,
			`content_pages`.`image_big` AS `book_image_big`,
			`content_pages`.`sitemap_changefreq` AS `page_sitemap_changefreq`,
			`content_pages`.`sitemap_priority` AS `page_sitemap_priority`
		FROM
			".WCOM_DB_CONTENT_SIMPLE_GUESTBOOKS." AS `content_simple_guestbooks`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		ON
			`content_simple_guestbooks`.`id` = `content_pages`.`id`
		JOIN
			".WCOM_DB_CONTENT_NODES." AS `content_nodes`
		ON
			`content_pages`.`id` = `content_nodes`.`id`
		WHERE
			`content_simple_guestbooks`.`id` = :id
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
 * Method to select one or more simple guestbooks. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>user, int, optional: User/author id</li>
 * <li>book, int, optional: Book id</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * <li>order_marco, string, otpional: How to sort the result set.
 * Supported macros:
 *    <ul>
 *        <li>BOOK: sorty by book id</li>
 *        <li>DATE_MODIFIED: sorty by date modified</li>
 *        <li>DATE_ADDED: sort by date added</li>
 *    </ul>
 * </li>
 * </ul>
 * 
 * @throws Content_SimpleGuestbookException
 * @param array Select params
 * @return array
 */
public function selectSimpleGuestbooks ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'SimpleGuestbook', 'Use')) {
		throw new Content_SimpleGuestbookException("You are not allowed to perform this action");
	}
	
	// define some vars
	$user = null;
	$book = null;
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_SimpleGuestbookException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'order_marco':
					$$_key = (string)$_value;
				break;
			case 'user':
			case 'book':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_SimpleGuestbookException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'BOOK' => '`content_simple_guestbooks`.`id`',
		'DATE_ADDED' => '`content_simple_guestbooks`.`date_added`',
		'DATE_MODIFIED' => '`content_simple_guestbooks`.`date_modified`'
	);
	
	// prepare query
	$sql = "
		SELECT 
			`content_simple_guestbooks`.`id` AS `id`,
			`content_simple_guestbooks`.`user` AS `user`,
			`content_simple_guestbooks`.`title` AS `title`,
			`content_simple_guestbooks`.`title_url` AS `title_url`,
			`content_simple_guestbooks`.`content_raw` AS `content_raw`,
			`content_simple_guestbooks`.`content` AS `content`,
			`content_simple_guestbooks`.`text_converter` AS `text_converter`,
			`content_simple_guestbooks`.`apply_macros` AS `apply_macros`,
			`content_simple_guestbooks`.`meta_use` AS `meta_use`,
			`content_simple_guestbooks`.`meta_title_raw` AS `meta_title_raw`,
			`content_simple_guestbooks`.`meta_title` AS `meta_title`,
			`content_simple_guestbooks`.`meta_keywords` AS `meta_keywords`,
			`content_simple_guestbooks`.`meta_description` AS `meta_description`,
			`content_simple_guestbooks`.`use_captcha` AS `use_captcha`,
			`content_simple_guestbooks`.`send_notification` AS `send_notification`,
			`content_simple_guestbooks`.`notification_email_from` AS `notification_email_from`,
			`content_simple_guestbooks`.`notification_email_to` AS `notification_email_to`,
			`content_simple_guestbooks`.`notification_email_subject` AS `notification_email_subject`,
			`content_simple_guestbooks`.`allow_entry` AS `allow_entry`,
			`content_simple_guestbooks`.`flood_protection` AS `flood_protection`,
			`content_simple_guestbooks`.`date_modified` AS `date_modified`,
			`content_simple_guestbooks`.`date_added` AS `date_added`,
			`content_nodes`.`id` AS `node_id`,
			`content_nodes`.`navigation` AS `node_navigation`,
			`content_nodes`.`root_node` AS `node_root_node`,
			`content_nodes`.`parent` AS `node_parent`,
			`content_nodes`.`lft` AS `node_lft`,
			`content_nodes`.`rgt` AS `node_rgt`,
			`content_nodes`.`level` AS `node_level`,
			`content_nodes`.`sorting` AS `node_sorting`,
			`content_pages`.`id` AS `book_id`,
			`content_pages`.`project` AS `book_project`,
			`content_pages`.`type` AS `book_type`,
			`content_pages`.`template_set` AS `book_template_set`,
			`content_pages`.`name` AS `book_name`,
			`content_pages`.`name_url` AS `book_name_url`,
			`content_pages`.`alternate_name` AS `page_alternate_name`,
			`content_pages`.`description` AS `page_description`,
			`content_pages`.`optional_text` AS `page_optional_text`,
			`content_pages`.`url` AS `book_url`,
			`content_pages`.`protect` AS `book_protect`,
			`content_pages`.`index_page` AS `book_index_page`,
			`content_pages`.`draft` AS `book_draft`,
			`content_pages`.`image_small` AS `book_image_small`,
			`content_pages`.`image_medium` AS `book_image_medium`,
			`content_pages`.`image_big` AS `book_image_big`,
			`content_pages`.`sitemap_changefreq` AS `page_sitemap_changefreq`,
			`content_pages`.`sitemap_priority` AS `page_sitemap_priority`
		FROM
			".WCOM_DB_CONTENT_SIMPLE_GUESTBOOKS." AS `content_simple_guestbooks`
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
	if (!empty($user) && is_numeric($user)) {
		$sql .= " AND `content_simple_guestbooks`.`id` = :user ";
		$bind_params['user'] = $user;
	}
	if (!empty($book) && is_numeric($book)) {
		$sql .= " AND `content_simple_guestbooks`.`id` = :book ";
		$bind_params['book'] = $book;
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
 * Tests whether given simple guestbook belongs to current project. Takes the
 * simple guestbook id as first argument. Returns bool.
 *
 * @throws Content_SimpleGuestbookException
 * @param int Simple guestbook id
 * @return int bool
 */
public function simpleGuestbookBelongsToCurrentProject ($simple_guestbook)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleGuestbook', 'Use')) {
		throw new Content_SimpleGuestbookException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($simple_guestbook) || !is_numeric($simple_guestbook)) {
		throw new Content_SimpleGuestbookException('Input for parameter simple_book is expected to be numeric');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_SIMPLE_GUESTBOOKS." AS `content_simple_guestbooks`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_simple_guestbooks`.`id` = `content_pages`.`id`
		WHERE
			`content_simple_guestbooks`.`id` = :simple_guestbook
		  AND
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'simple_guestbook' => (int)$simple_guestbook,
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
 * Test whether simple guestbook belongs to current user or not. Takes
 * the simple guestbook id as first argument. Returns bool.
 *
 * @throws Content_SimpleGuestbookException
 * @param int Simple guestbook id
 * @return bool
 */
public function simpleGuestbookBelongsToCurrentUser ($simple_guestbook)
{
	// access check
	if (!wcom_check_access('Content', 'SimpleGuestbook', 'Use')) {
		throw new Content_SimpleGuestbookException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($simple_guestbook) || !is_numeric($simple_guestbook)) {
		throw new Content_SimpleGuestbookException('Input for parameter simple_guestbook is expected to be numeric');
	}
	
	// load user class
	$USER = load('User:User');
	
	if (!$this->simpleGuestbookBelongsToCurrentProject($simple_guestbook)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Content_SimpleGuestbookException extends Exception { }

?>