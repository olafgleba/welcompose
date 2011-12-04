<?php

/**
 * Project: Welcompose
 * File: eventposting.class.php
 * 
 * Copyright (c) 2011 creatics
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * @copyright 2011 creatics, Olaf Gleba
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton for Content_EventPosting.
 * 
 * @return object
 */
function Content_EventPosting ()
{
	if (Content_EventPosting::$instance == null) {
		Content_EventPosting::$instance = new Content_EventPosting(); 
	}
	return Content_EventPosting::$instance;
}

class Content_EventPosting {
	
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
 * Adds event posting to the event posting table. Takes a field=>value
 * array with event posting data as first argument. Returns insert id. 
 * 
 * @throws Content_EventPostingException
 * @param array Row data
 * @return int Insert id
 */
public function addEventPosting ($sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'EventPosting', 'Manage')) {
		throw new Content_EventPostingException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Content_EventPostingException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_CONTENT_EVENT_POSTINGS, $sqlData);
	
	// test if event posting belongs to current user
	if (!$this->eventPostingBelongsToCurrentUser($insert_id)) {
		throw new Content_EventPostingException('Event posting does not belong to current project or user');
	}
	
	return $insert_id;
}

/**
 * Updates event posting. Takes the event posting id as first argument, a
 * field=>value array with the new event posting data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_EventPostingException
 * @param int Event posting id
 * @param array Row data
 * @return int Affected rows
*/
public function updateEventPosting ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'EventPosting', 'Manage')) {
		throw new Content_EventPostingException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_EventPostingException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_EventPostingException('Input for parameter sqlData is not an array');	
	}
	
	// test if event posting belongs to current user
	if (!$this->eventPostingBelongsToCurrentUser($id)) {
		throw new Content_EventPostingException('Event posting does not belong to current project or user');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_CONTENT_EVENT_POSTINGS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes event posting from the event postings table. Takes the
 * event posting id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Content_EventPostingException
 * @param int Event posting id
 * @return int Amount of affected rows
 */
public function deleteEventPosting ($id)
{
	// access check
	if (!wcom_check_access('Content', 'EventPosting', 'Manage')) {
		throw new Content_EventPostingException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_EventPostingException('Input for parameter id is not numeric');
	}
	
	// test if event posting belongs to current user
	if (!$this->eventPostingBelongsToCurrentUser($id)) {
		throw new Content_EventPostingException('Event posting does not belong to current project or user');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_CONTENT_EVENT_POSTINGS, $where, $bind_params);
}

/**
 * Selects one event posting. Takes the event posting id as first
 * argument. Returns array with event posting information.
 * 
 * @throws Content_EventPostingException
 * @param int Event posting id
 * @return array
 */
public function selectEventPosting ($id)
{
	// access check
	if (!wcom_check_access('Content', 'EventPosting', 'Use')) {
		throw new Content_EventPostingException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_EventPostingException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`content_event_postings`.`id` AS `id`,
			`content_event_postings`.`page` AS `page`,
			`content_event_postings`.`user` AS `user`,
			`content_event_postings`.`title` AS `title`,
			`content_event_postings`.`title_url` AS `title_url`,
			`content_event_postings`.`content_raw` AS `content_raw`,
			`content_event_postings`.`content` AS `content`,
			`content_event_postings`.`text_converter` AS `text_converter`,
			`content_event_postings`.`apply_macros` AS `apply_macros`,
			`content_event_postings`.`meta_use` AS `meta_use`,
			`content_event_postings`.`meta_title_raw` AS `meta_title_raw`,
			`content_event_postings`.`meta_title` AS `meta_title`,
			`content_event_postings`.`meta_keywords` AS `meta_keywords`,
			`content_event_postings`.`meta_description` AS `meta_description`,
			`content_event_postings`.`draft` AS `draft`,
			`content_event_postings`.`tag_count` AS `tag_count`,
			`content_event_postings`.`tag_array` AS `tag_array`,
			`content_event_postings`.`date_modified` AS `date_modified`,
			`content_event_postings`.`date_added` AS `date_added`,
			`content_event_postings`.`day_added` AS `day_added`,
			`content_event_postings`.`month_added` AS `month_added`,
			`content_event_postings`.`year_added` AS `year_added`,
			`content_event_postings`.`date_start` AS `date_start`,
			`content_event_postings`.`date_start_time_start` AS `date_start_time_start`,
			`content_event_postings`.`date_start_time_end` AS `date_start_time_end`,
			`content_event_postings`.`date_end` AS `date_end`,
			`content_event_postings`.`date_end_time_start` AS `date_end_time_start`,
			`content_event_postings`.`date_end_time_end` AS `date_end_time_end`,
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
			`content_pages`.`exclude` AS `page_exclude`,
			`content_pages`.`index_page` AS `page_index_page`,
			`content_pages`.`no_follow` AS `page_no_follow`,
			`content_pages`.`draft` AS `page_draft`,
			`content_pages`.`index_page` AS `page_index_page`,
			`content_pages`.`image_small` AS `page_image_small`,
			`content_pages`.`image_medium` AS `page_image_medium`,
			`content_pages`.`image_big` AS `page_image_big`,
			`content_pages`.`sitemap_changefreq` AS `page_sitemap_changefreq`,
			`content_pages`.`sitemap_priority` AS `page_sitemap_priority`,
			`user_users`.`id` AS `user_id`,
			`user_users`.`name` AS `user_name`,
			`user_users`.`email` AS `user_email`,
			`user_users`.`homepage` AS `user_homepage`,
			`user_users`.`secret` AS `user_secret`,
			`user_users`.`editable` AS `user_editable`,
			`user_users`.`date_modified` AS `user_date_modified`,
			`user_users`.`date_added` AS `user_date_added`,
			`user_users`.`_sync` AS `user__sync`
		FROM
			".WCOM_DB_CONTENT_EVENT_POSTINGS." AS `content_event_postings`
		JOIN
			".WCOM_DB_USER_USERS." AS `user_users`
		  ON
			`content_event_postings`.`user` = `user_users`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_event_postings`.`page` = `content_pages`.`id`
		JOIN
			".WCOM_DB_CONTENT_NODES." AS `content_nodes`
		  ON
			`content_pages`.`id` = `content_nodes`.`id`
		WHERE
			`content_event_postings`.`id` = :id
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
 * Method to select one or more event postings. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>user, int, optional: User/author id</li>
 * <li>page, int, optional: Page id</li>
 * <li>draft, int, optional: Draft bit (0/1)</li>
 * <li>year_added, string, optional: four digit year number</li>
 * <li>month_added, string, optional: two digit month number</li>
 * <li>day_added, string, optional: two digit day number</li>
 * <li>current_date, string, optional: return rows based on current date (FORWARD/BACKWARD)</li>
 * <li>tag_word_url, string, optional: Tag word</li>
 * <li>timeframe, string, optional: specific range of rows to return</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * <li>title, string, optional: title.
 * <li>search_name, string, opional: Search string input.
 * <li>order_marco, string, otpional: How to sort the result set.
 * Supported macros:
 *    <ul>
 *		<li>DATE_MODIFIED: sorty by date modified</li>
 *		<li>DATE_ADDED: sort by date added</li>
 *	 	<li>RANDOM: sort by random</li>
 *	 	<li>TITLE: sort by title</li>
 *		<li>DATE_START: sort by start date</li>
 *    </ul>
 * </li>
 * </ul>
 * 
 * @throws Content_EventPostingException
 * @param array Select params
 * @return array
 */
public function selectEventPostings ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'EventPosting', 'Use')) {
		throw new Content_EventPostingException("You are not allowed to perform this action");
	}
	
	// define some vars
	$user = null;
	$page = null;
	$start = null;
	$limit = null;
	$draft = null;
	$year_added = null;
	$month_added = null;
	$day_added = null;
	$tag_word_url = null;
	$order_macro = null;
	$timeframe = null;
	$current_date = null;
	$title = null;
	$date_start = null;
	$search_name = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_EventPostingException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'order_macro':
			case 'year_added':
			case 'month_added':
			case 'day_added':
			case 'tag_word_url':
			case 'timeframe':
			case 'current_date':
			case 'title':
			case 'search_name':
					$$_key = (string)$_value;
				break;
			case 'user':
			case 'page':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			case 'draft':
					$$_key = (is_null($_value) ? null : (string)$_value);
				break;
			default:
				throw new Content_EventPostingException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'DATE_ADDED' => '`content_event_postings`.`date_added`',
		'DATE_MODIFIED' => '`content_event_postings`.`date_modified`',
		'RANDOM' => 'rand()',
		'TITLE' => '`content_event_postings`.`title`',
		'DATE_START' => '`content_event_postings`.`date_start`'
	);
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// prepare query
	$sql = "
		SELECT 
			`content_event_postings`.`id` AS `id`,
			`content_event_postings`.`page` AS `page`,
			`content_event_postings`.`user` AS `user`,
			`content_event_postings`.`title` AS `title`,
			`content_event_postings`.`title_url` AS `title_url`,
			`content_event_postings`.`content_raw` AS `content_raw`,
			`content_event_postings`.`content` AS `content`,
			`content_event_postings`.`text_converter` AS `text_converter`,
			`content_event_postings`.`apply_macros` AS `apply_macros`,
			`content_event_postings`.`meta_use` AS `meta_use`,
			`content_event_postings`.`meta_title_raw` AS `meta_title_raw`,
			`content_event_postings`.`meta_title` AS `meta_title`,
			`content_event_postings`.`meta_keywords` AS `meta_keywords`,
			`content_event_postings`.`meta_description` AS `meta_description`,
			`content_event_postings`.`draft` AS `draft`,
			`content_event_postings`.`tag_count` AS `tag_count`,
			`content_event_postings`.`tag_array` AS `tag_array`,
			`content_event_postings`.`date_modified` AS `date_modified`,
			`content_event_postings`.`date_added` AS `date_added`,
			`content_event_postings`.`day_added` AS `day_added`,
			`content_event_postings`.`month_added` AS `month_added`,
			`content_event_postings`.`year_added` AS `year_added`,
			`content_event_postings`.`date_start` AS `date_start`,
			`content_event_postings`.`date_start_time_start` AS `date_start_time_start`,
			`content_event_postings`.`date_start_time_end` AS `date_start_time_end`,
			`content_event_postings`.`date_end` AS `date_end`,
			`content_event_postings`.`date_end_time_start` AS `date_end_time_start`,
			`content_event_postings`.`date_end_time_end` AS `date_end_time_end`,
			`content_event_tags`.`id` AS `tag_id`,
			`content_event_tags`.`page` AS `tag_page`,
			`content_event_tags`.`first_char` AS `tag_first_char`,
			`content_event_tags`.`word` AS `tag_word`,
			`content_event_tags`.`word_url` AS `tag_word_url`,
			`content_event_tags`.`occurrences` AS `tag_occurrences`,
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
			`content_pages`.`exclude` AS `page_exclude`,
			`content_pages`.`index_page` AS `page_index_page`,
			`content_pages`.`no_follow` AS `page_no_follow`,
			`content_pages`.`draft` AS `page_draft`,
			`content_pages`.`index_page` AS `page_index_page`,
			`content_pages`.`image_small` AS `page_image_small`,
			`content_pages`.`image_medium` AS `page_image_medium`,
			`content_pages`.`image_big` AS `page_image_big`,
			`content_pages`.`sitemap_changefreq` AS `page_sitemap_changefreq`,
			`content_pages`.`sitemap_priority` AS `page_sitemap_priority`,
			`user_users`.`id` AS `user_id`,
			`user_users`.`name` AS `user_name`,
			`user_users`.`email` AS `user_email`,
			`user_users`.`homepage` AS `user_homepage`,
			`user_users`.`secret` AS `user_secret`,
			`user_users`.`editable` AS `user_editable`,
			`user_users`.`date_modified` AS `user_date_modified`,
			`user_users`.`date_added` AS `user_date_added`,
			`user_users`.`_sync` AS `user__sync`
		FROM
			".WCOM_DB_CONTENT_EVENT_POSTINGS." AS `content_event_postings`
		LEFT JOIN
			".WCOM_DB_CONTENT_EVENT_TAGS2CONTENT_EVENT_POSTINGS." AS `content_event_tags2_content_event_postings`
		  ON
			`content_event_postings`.`id` = `content_event_tags2_content_event_postings`.`posting`
		LEFT JOIN
			".WCOM_DB_CONTENT_EVENT_TAGS." AS `content_event_tags`
		  ON
			`content_event_tags2_content_event_postings`.`tag` = `content_event_tags`.`id`
		JOIN
			".WCOM_DB_USER_USERS." AS `user_users`
		  ON
			`content_event_postings`.`user` = `user_users`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_event_postings`.`page` = `content_pages`.`id`
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
		$sql .= " AND `user_users`.`id` = :user ";
		$bind_params['user'] = $user;
	}
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `content_pages`.`id` = :page ";
		$bind_params['page'] = $page;
	}
	if (!empty($tag_word_url) && preg_match(WCOM_REGEX_URL_NAME, $tag_word_url)) {
		$sql .= " AND `content_event_tags`.`word_url` = :tag_word_url ";
		$bind_params['tag_word_url'] = (string)$tag_word_url;
	}
	if (!is_null($draft) && is_numeric($draft)) {
		$sql .= " AND `content_event_postings`.`draft` = :draft ";
		$bind_params['draft'] = (string)$draft;
	}
	if (!is_null($year_added) && is_numeric($year_added)) {
		$sql .= " AND `content_event_postings`.`year_added` = :year_added ";
		$bind_params['year_added'] = (string)$year_added;
	}
	if (!is_null($month_added) && is_numeric($month_added)) {
		$sql .= " AND `content_event_postings`.`month_added` = :month_added ";
		$bind_params['month_added'] = (string)$month_added;
	}
	if (!is_null($day_added) && is_numeric($day_added)) {
		$sql .= " AND `content_event_postings`.`day_added` = :day_added ";
		$bind_params['day_added'] = (string)$day_added;
	}
	if (!empty($date_start) && is_string($date_start)) {
		$sql .= " AND `content_event_postings`.`date_start` = :date_start ";
		$bind_params['date_start'] = (string)$date_start;
	}	
	if (!empty($timeframe)) {
		$sql .= " AND ".$HELPER->_sqlForTimeFrame('`content_event_postings`.`date_added`',
			$timeframe);
	}
	if (!empty($current_date)) {
		$sql .= " AND ".$HELPER->_sqlForCurrentDate('`content_event_postings`.`date_added`',
			$current_date);
	}
	if (!empty($title) && is_string($title)) {
		$sql .= " AND `content_event_postings`.`title` = :title ";
		$bind_params['title'] = (string)$title;
	}
	if (!empty($search_name)) {
		$sql .= " AND ".$HELPER->_searchLikewise('`content_event_postings`.`title`',
			$search_name);
	}
	
	// aggregate result set
	$sql .= " GROUP BY `content_event_postings`.`id` ";
	
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
 * Method to count event postings. Takes key=>value array
 * with select params as first argument. Returns int.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>user, int, optional: User/author id</li>
 * <li>page, int, optional: Page id</li>
 * <li>draft, int, optional: Draft bit (0/1)</li>
 * <li>current_date, string, optional: return rows based on current date (FORWARD/BACKWARD)</li>
 * <li>tag_word_url, string, optional: Tag word</li>
 * <li>timeframe, string, optional: specific range of rows to return</li>
 * <li>year_added, string, optional: four digit year number</li>
 * <li>month_added, string, optional: two digit month number</li>
 * <li>day_added, string, optional: two digit day number</li>
 * </ul>
 * 
 * @throws Content_EventPostingException
 * @param array Select params
 * @return int
 */
public function countEventPostings ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'EventPosting', 'Use')) {
		throw new Content_EventPostingException("You are not allowed to perform this action");
	}
	
	// define some vars
	$user = null;
	$page = null;
	$draft = null;
	$year_added = null;
	$month_added = null;
	$day_added = null;
	$tag_word_url = null;
	$timeframe = null;
	$current_date = null;
	$search_name = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_EventPostingException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'year_added':
			case 'month_added':
			case 'day_added':
			case 'tag_word_url':
			case 'timeframe':
			case 'current_date':
			case 'search_name':
					$$_key = (string)$_value;
				break;
			case 'user':
			case 'page':
					$$_key = (int)$_value;
				break;
			case 'draft':
					$$_key = (is_null($_value) ? null : (string)$_value);
				break;
			default:
				throw new Content_EventPostingException("Unknown parameter $_key");
		}
	}
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(DISTINCT `content_event_postings`.`id`) AS `total`
		FROM
			".WCOM_DB_CONTENT_EVENT_POSTINGS." AS `content_event_postings`
		LEFT JOIN
			".WCOM_DB_CONTENT_EVENT_TAGS2CONTENT_EVENT_POSTINGS." AS `content_event_tags2_content_event_postings`
		  ON
			`content_event_postings`.`id` = `content_event_tags2_content_event_postings`.`posting`
		LEFT JOIN
			".WCOM_DB_CONTENT_EVENT_TAGS." AS `content_event_tags`
		  ON
			`content_event_tags2_content_event_postings`.`tag` = `content_event_tags`.`id`
		JOIN
			".WCOM_DB_USER_USERS." AS `user_users`
		  ON
			`content_event_postings`.`user` = `user_users`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_event_postings`.`page` = `content_pages`.`id`
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
		$sql .= " AND `user_users`.`id` = :user ";
		$bind_params['user'] = $user;
	}
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `content_pages`.`id` = :page ";
		$bind_params['page'] = $page;
	}
	if (!empty($tag_word_url) && preg_match(WCOM_REGEX_URL_NAME, $tag_word_url)) {
		$sql .= " AND `content_event_tags`.`word_url` = :tag_word_url ";
		$bind_params['tag_word_url'] = (string)$tag_word_url;
	}
	if (!is_null($draft) && is_numeric($draft)) {
		$sql .= " AND `content_event_postings`.`draft` = :draft ";
		$bind_params['draft'] = (string)$draft;
	}
	if (!is_null($year_added) && is_numeric($year_added)) {
		$sql .= " AND `content_event_postings`.`year_added` = :year_added ";
		$bind_params['year_added'] = (string)$year_added;
	}
	if (!is_null($month_added) && is_numeric($month_added)) {
		$sql .= " AND `content_event_postings`.`month_added` = :month_added ";
		$bind_params['month_added'] = (string)$month_added;
	}
	if (!is_null($day_added) && is_numeric($day_added)) {
		$sql .= " AND `content_event_postings`.`day_added` = :day_added ";
		$bind_params['day_added'] = (string)$day_added;
	}		
	if (!empty($timeframe)) {
		$sql .= " AND ".$HELPER->_sqlForTimeFrame('`content_event_postings`.`date_added`',
			$timeframe);
	}
	if (!empty($current_date)) {
		$sql .= " AND ".$HELPER->_sqlForCurrentDate('`content_event_postings`.`date_added`',
			$current_date);
	}
	if (!empty($search_name)) {
		$sql .= " AND ".$HELPER->_searchLikewise('`content_event_postings`.`title`',
			$search_name);
	}
	
	return $this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Selects years with event postings. Takes field=>key array with select
 * params as first argument. Returns array with years.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>page, int, optional: Return only event postings assigned to this page</li>
 * <li>order_macro, string, optional: Sorting instructions</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Content_EventPostingException
 * @param array
 * @return array
 */
public function selectDifferentYears ($params)
{
	// access check
	if (!wcom_check_access('Content', 'EventPosting', 'Use')) {
		throw new Content_EventPostingException("You are not allowed to perform this action");
	}
	
	// define some vars
	$page = null;
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_EventPostingException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'page':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			case 'order_macro':
					$$_key = (string)$_value;
				break;
			default:
				throw new Content_EventPostingException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'DATE_ADDED' => '`date_added`',
	);
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// prepare query
	$sql = "
		SELECT
			`date_added` AS `timestamp`,
			`year_added` AS `year`
		FROM
			".WCOM_DB_CONTENT_EVENT_POSTINGS." AS `content_event_postings`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_event_postings`.`page` = `content_pages`.`id`
		WHERE
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	if (!empty($page)) {
		$sql .= sprintf(" AND `page` = :page ");
		$bind_params['page'] = $page;
	}
		
	// aggregate result set
	$sql .=	" GROUP BY `year` ";
	
	// add sorting
	if (!empty($order_macro)) {
		$HELPER = load('utility:helper');
		$sql .= " ORDER BY ".$HELPER->_sqlForOrderMacro($order_macro, $macros);
	}
	
	// add limits etc.
	if (empty($start) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u ", $limit);
	}
	if (!empty($start) && is_numeric($start) && !empty($limit) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u, %u ", $start, $limit);
	}

	// execute query and return result
	return $this->base->db->select($sql, 'multi', $bind_params);
}

/**
 * Selects months with event postings. Takes field=>key array with select
 * params as first argument. Returns array with months and years.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>page, int, optional: Return only event postings assigned to this page</li>
 * <li>year, int, optional: Return only event postings added in that year</li>
 * <li>order_macro, string, optional: Sorting instructions</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Content_EventPostingException
 * @param array Select params
 * @return array
 */
public function selectDifferentMonths ($params)
{
	// access check
	if (!wcom_check_access('Content', 'EventPosting', 'Use')) {
		throw new Content_EventPostingException("You are not allowed to perform this action");
	}
	
	// define some vars
	$page = null;
	$year = null;
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_EventPostingException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ($_key) {
			case 'page':	
			case 'year':	
			case 'start':	
			case 'limit':		
					$$_key = (int)$_value;
				break;
			case 'order_macro':
					$$_key = (string)$_value;
				break;
			default:
				throw new Content_EventPostingException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'DATE_ADDED' => '`date_added`',
	);
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// prepare query
	$sql = "
		SELECT
			`date_added` AS `timestamp`,
			`year_added` AS `year`,
			`month_added` AS `month`
		FROM
			".WCOM_DB_CONTENT_EVENT_POSTINGS." AS `content_event_postings`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_event_postings`.`page` = `content_pages`.`id`
		WHERE
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($page)) {
		$sql .= " AND `page` = :page ";
		$bind_params['page'] = $page;
	}
	if (!empty($year)) {
		$sql .= sprintf(" AND `year_added` = :year ");
		$bind_params['year'] = $year;
	}
	
	// aggregate result set
	$sql .=	" GROUP BY `month_added`, `year_added` ";
	
	// add sorting
	if (!empty($order_macro)) {
		$HELPER = load('utility:helper');
		$sql .= " ORDER BY ".$HELPER->_sqlForOrderMacro($order_macro, $macros);
	}
	
	// add limits etc.
	if (empty($start) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u ", $limit);
	}
	if (!empty($start) && is_numeric($start) && !empty($limit) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u, %u ", $start, $limit);
	}
	
	// execute query and return result
	return $this->base->db->select($sql, 'multi', $bind_params);
}

/**
 * Selects days with event postings. Takes field=>key array with select params
 * as first argument. Returns array with days, months and years.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>page, int, optional: Return only event postings assigned to this page</li>
 * <li>year, int, optional: Return only event postings added in that year</li>
 * <li>month, int, optional: Return only event postings added in that month</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Content_EventPostingException
 * @param array Select params
 * @return array
 */
public function selectDifferentDays ($params)
{
	// access check
	if (!wcom_check_access('Content', 'EventPosting', 'Use')) {
		throw new Content_EventPostingException("You are not allowed to perform this action");
	}
	
	// define some vars
	$page = null;
	$year = null;
	$month = null;
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_EventPostingException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ($_key) {
			case 'page':	
			case 'year':	
			case 'month':	
			case 'start':	
			case 'limit':		
					$$_key = (int)$_value;
				break;
			case 'order_macro':
					$$_key = (string)$_value;
				break;
			default:
				throw new Content_EventPostingException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'DATE_ADDED' => '`date_added`',
	);
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// prepare query
	$sql = "
		SELECT
			date_added AS timestamp,
			date_added_year AS year,
			date_added_month AS month,
			date_added_day AS day
		FROM
			".WCOM_DB_CONTENT_EVENT_POSTINGS." AS `content_event_postings`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_event_postings`.`page` = `content_pages`.`id`
		WHERE
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($page)) {
		$sql .= " AND `page` = :page ";
		$bind_params['page'] = $page;
	}
	if (!empty($year)) {
		$sql .= sprintf(" AND `year_added` = :year ");
		$bind_params['year'] = $year;
	}
	if (!empty($month)) {
		$sql .= sprintf(" AND `month_added` = :month ");
		$bind_params['month'] = $month;
	}
	
	// aggregate result set
	$sql .=	" GROUP BY `day_added`, `month_added`, `year_added` ";
	
	// add sorting
	if (!empty($order_macro)) {
		$HELPER = load('utility:helper');
		$sql .= " ORDER BY ".$HELPER->_sqlForOrderMacro($order_macro, $macros);
	}
		
	// add limits etc.
	if (empty($start) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u ", $limit);
	}
	if (!empty($start) && is_numeric($start) && !empty($limit) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u, %u ", $start, $limit);
	}
	
	// execute query and return result
	return $this->base->db->select($sql, 'multi', $bind_params);
}

/**
 * Resolves event posting using the available url params. Returns the
 * event posting id on success or throws an exception on failure.
 * 
 * The function either expects the plain event posting id
 * (~ $_REQUEST['posting']) or a  combination consisting of the
 * following parameters:
 *
 * <ul>
 * <li>year: Four digit year number when the posting was added</li>
 * <li>month: Two digit month number when the posting was added</li>
 * <li>day: Two digit day number when the posting was added</li>
 * <li>title: Url title of the event posting</li>
 * </ul>
 * 
 * @throws Content_EventPostingException
 * @return int
 */
public function resolveEventPosting ()
{
	// access check
	if (!wcom_check_access('Content', 'EventPosting', 'Use')) {
		throw new Content_EventPostingException("You are not allowed to perform this action");
	}
	
	// let's see if there's a posting id in the request url
	$posting_id = Base_Cnc::filterRequest($_REQUEST['posting_id'], WCOM_REGEX_NUMERIC);
	
	if (!is_null($posting_id)) {
		if ($this->eventPostingExists($posting_id)) {
			return $posting_id;
		} else {
			throw new Content_EventPostingException("Event posting could not be found");
		}
	}
	
	// if there's no event posting id in the url, we have to look for it
	// using date and title_url
	
	// prepare date added
	$date_added = sprintf("%s-%s-%s%%",
		Base_Cnc::filterRequest($_REQUEST['posting_year_added'], WCOM_REGEX_NUMERIC),
		Base_Cnc::filterRequest($_REQUEST['posting_month_added'], WCOM_REGEX_NUMERIC),
		Base_Cnc::filterRequest($_REQUEST['posting_day_added'], WCOM_REGEX_NUMERIC)
	);
	
	// prepare query
	$sql = "
		SELECT
		 	`id`
		FROM
			".WCOM_DB_CONTENT_EVENT_POSTINGS."
		WHERE
			`title_url` = :title_url
		  AND
			`date_added` LIKE :date_added
		  AND
			`page` = :page
		LIMIT 1
	";
	
	// prepare bind params
	$bind_params = array(
		'title_url' => Base_Cnc::filterRequest($_REQUEST['posting_title'],
			WCOM_REGEX_MEANINGFUL_STRING),
		'date_added' => $date_added,
		'page' => WCOM_CURRENT_PAGE
	);
	
	// execute query and evaluate result
	$result = intval($this->base->db->select($sql, 'field', $bind_params));
	if ($result >= 1) {
		return $result;
	} else {
		throw new Content_EventPostingException("Event posting could not be found");
	}
}

/**
 * Tests if event posting exists. Takes the id of the event posting
 * as first argument. Returns bool.
 *
 * @throws Content_EventPostingException
 * @param int Event posting id
 * @return bool
 */
public function eventPostingExists ($id)
{
	// access check
	if (!wcom_check_access('Content', 'EventPosting', 'Use')) {
		throw new Content_EventPostingException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_EventPostingException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_EVENT_POSTINGS." AS `content_event_postings`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_event_postings`.`page` = `content_pages`.`id`
		WHERE
			`content_event_postings`.`id` = :id
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
 * Tests whether given event posting belongs to current project. Takes the
 * event posting id as first argument. Returns bool.
 *
 * @throws Content_EventPostingException
 * @param int Event posting id
 * @return int bool
 */
public function eventPostingBelongsToCurrentProject ($event_posting)
{
	// access check
	if (!wcom_check_access('Content', 'EventPosting', 'Use')) {
		throw new Content_EventPostingException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($event_posting) || !is_numeric($event_posting)) {
		throw new Content_EventPostingException('Input for parameter event_posting is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_EVENT_POSTINGS." AS `content_event_postings`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_event_postings`.`page` = `content_pages`.`id`
		WHERE
			`content_event_postings`.`id` = :event_posting
		  AND
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'event_posting' => (int)$event_posting,
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
 * Test whether event posting belongs to current user or not. Takes
 * the event posting id as first argument. Returns bool.
 *
 * @throws Content_EventPostingException
 * @param int Event posting id
 * @return bool
 */
public function eventPostingBelongsToCurrentUser ($event_posting)
{
	// access check
	if (!wcom_check_access('Content', 'EventPosting', 'Use')) {
		throw new Content_EventPostingException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($event_posting) || !is_numeric($event_posting)) {
		throw new Content_EventPostingException('Input for parameter event_posting is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->eventPostingBelongsToCurrentProject($event_posting)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Content_EventPostingException extends Exception { }

?>