<?php

/**
 * Project: Welcompose
 * File: blogcomment.class.php
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

class Community_Blogcomment {
	
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
 * Singleton. Returns instance of the Community_Blogcomment object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Community_Blogcomment::$instance == null) {
		Community_Blogcomment::$instance = new Community_Blogcomment(); 
	}
	return Community_Blogcomment::$instance;
}

/**
 * Adds blog comment. Takes a field=>value array with blog comment data as
 * first argument. Returns insert id. 
 * 
 * @throws Community_BlogcommentException
 * @param array Row data
 * @return int User id
 */
public function addBlogComment ($sqlData)
{
	if (!is_array($sqlData)) {
		throw new Community_BlogcommentException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_COMMUNITY_BLOG_COMMENTS, $sqlData);
	
	// update the comment count of the posting
	$this->updateBlogCommentCount($sqlData['posting']);
	
	return $insert_id;
}

/**
 * Updates blog comment. Takes the blog comment id as first argument, a
 * field=>value array with the new blog comment data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Community_BlogcommentException
 * @param int Blog comment id
 * @param array Row data
 * @return int Affected rows
*/
public function updateBlogComment ($id, $sqlData)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Community_BlogcommentException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Community_BlogcommentException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_COMMUNITY_BLOG_COMMENTS, $sqlData,
		$where, $bind_params);
}

/**
 * Removes blog comment from the blog comment table. Takes the
 * blog comment id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Community_BlogcommentException
 * @param int Blog comment id
 * @return int Amount of affected rows
 */
public function deleteBlogComment ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Community_BlogcommentException('Input for parameter id is not numeric');
	}
	
	// get blog comment (we need it to update the comment count)
	$blog_comment = $this->selectBlogComment($id);
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	$affected_rows = $this->base->db->delete(WCOM_DB_COMMUNITY_BLOG_COMMENTS,	
		$where, $bind_params);
		
	// update comment count
	$this->updateBlogCommentCount($blog_comment['posting']);
	
	return $affected_rows;
}

/**
 * Selects one blog comment. Takes the blog comment id as first
 * argument. Returns array with blog comment information.
 * 
 * @throws Community_BlogcommentException
 * @param int Blog comment id
 * @return array
 */
public function selectBlogComment ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Community_BlogcommentException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT
			`community_blog_comments`.`id` AS `id`,
			`community_blog_comments`.`posting` AS `posting`,
			`community_blog_comments`.`user` AS `user`,
			`community_blog_comments`.`status` AS `status`,
			`community_blog_comments`.`name` AS `name`,
			`community_blog_comments`.`email` AS `email`,
			`community_blog_comments`.`homepage` AS `homepage`,
			`community_blog_comments`.`content_raw` AS `content_raw`,
			`community_blog_comments`.`content` AS `content`,
			`community_blog_comments`.`original_raw` AS `original_raw`,
			`community_blog_comments`.`original` AS `original`,
			`community_blog_comments`.`spam_report` AS `spam_report`,
			`community_blog_comments`.`edited` AS `edited`,
			`community_blog_comments`.`date_modified` AS `date_modified`,
			`community_blog_comments`.`date_added` AS `date_added`,
			`content_blog_postings`.`id` AS `blog_posting_id`,
			`content_blog_postings`.`page` AS `blog_posting_page`,
			`content_blog_postings`.`user` AS `blog_posting_user`,
			`content_blog_postings`.`title` AS `blog_posting_title`,
			`content_blog_postings`.`title_url` AS `blog_posting_title_url`,
			`content_blog_postings`.`summary_raw` AS `blog_posting_summary_raw`,
			`content_blog_postings`.`summary` AS `blog_posting_summary`,
			`content_blog_postings`.`content_raw` AS `blog_posting_content_raw`,
			`content_blog_postings`.`content` AS `blog_posting_content`,
			`content_blog_postings`.`draft` AS `blog_posting_draft`,
			`content_blog_postings`.`ping` AS `blog_posting_ping`,
			`content_blog_postings`.`comments_enable` AS `blog_posting_comments_enable`,
			`content_blog_postings`.`comment_count` AS `blog_posting_comment_count`,
			`content_blog_postings`.`trackbacks_enable` AS `blog_posting_trackbacks_enable`,
			`content_blog_postings`.`trackback_count` AS `blog_posting_trackback_count`,
			`content_blog_postings`.`pingbacks_enable` AS `blog_posting_pingbacks_enable`,
			`content_blog_postings`.`pingback_count` AS `blog_posting_pingback_count`,
			`content_blog_postings`.`tag_count` AS `blog_posting_tag_count`,
			`content_blog_postings`.`tag_array` AS `blog_posting_tag_array`,
			`content_blog_postings`.`date_modified` AS `blog_posting_date_modified`,
			`content_blog_postings`.`date_added` AS `blog_posting_date_added`,
			`content_blog_postings`.`day_added` AS `blog_posting_day_added`,
			`content_blog_postings`.`month_added` AS `blog_posting_month_added`,
			`content_blog_postings`.`year_added` AS `blog_posting_year_added`,
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
			".WCOM_DB_COMMUNITY_BLOG_COMMENTS." AS `community_blog_comments`
		JOIN
			".WCOM_DB_CONTENT_BLOG_POSTINGS." AS `content_blog_postings`
		  ON
			`community_blog_comments`.`posting` = `content_blog_postings`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_blog_postings`.`page` = `content_pages`.`id`
		JOIN
			".WCOM_DB_CONTENT_NODES." AS `content_nodes`
		  ON
			`content_pages`.`id` = `content_nodes`.`id`
		WHERE 
			`community_blog_comments`.`id` = :id
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
 * Method to select one or more blog comments. Takes key=>value
 * array with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>page, int, optional: Page id</li>
 * <li>status, int, optional: Blog comment status id</li>
 * <li>posting, int, optional: Posting id</li>
 * <li>order_macro, string, optional: Sorting instruction</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Community_BlogcommentException
 * @param array Select params
 * @return array
 */
public function selectBlogComments ($params = array())
{
	// define some vars
	$page = null;
	$user = null;
	$posting = null;
	$status = null;
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Community_BlogcommentException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'timeframe':
			case 'order_macro':
					$$_key = (string)$_value;
				break;
			case 'page':
			case 'posting':
			case 'status':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Community_BlogcommentException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'DATE_ADDED' => '`community_blog_comments`.`date_added`'
	);
	
	// load helper class
	$HELPER = load('Utility:Helper');
	
	// prepare query
	$sql = "
		SELECT
			`community_blog_comments`.`id` AS `id`,
			`community_blog_comments`.`posting` AS `posting`,
			`community_blog_comments`.`user` AS `user`,
			`community_blog_comments`.`status` AS `status`,
			`community_blog_comments`.`name` AS `name`,
			`community_blog_comments`.`email` AS `email`,
			`community_blog_comments`.`homepage` AS `homepage`,
			`community_blog_comments`.`content_raw` AS `content_raw`,
			`community_blog_comments`.`content` AS `content`,
			`community_blog_comments`.`original_raw` AS `original_raw`,
			`community_blog_comments`.`original` AS `original`,
			`community_blog_comments`.`spam_report` AS `spam_report`,
			`community_blog_comments`.`edited` AS `edited`,
			`community_blog_comments`.`date_modified` AS `date_modified`,
			`community_blog_comments`.`date_added` AS `date_added`,
			`content_blog_postings`.`id` AS `blog_posting_id`,
			`content_blog_postings`.`page` AS `blog_posting_page`,
			`content_blog_postings`.`user` AS `blog_posting_user`,
			`content_blog_postings`.`title` AS `blog_posting_title`,
			`content_blog_postings`.`title_url` AS `blog_posting_title_url`,
			`content_blog_postings`.`summary_raw` AS `blog_posting_summary_raw`,
			`content_blog_postings`.`summary` AS `blog_posting_summary`,
			`content_blog_postings`.`content_raw` AS `blog_posting_content_raw`,
			`content_blog_postings`.`content` AS `blog_posting_content`,
			`content_blog_postings`.`draft` AS `blog_posting_draft`,
			`content_blog_postings`.`ping` AS `blog_posting_ping`,
			`content_blog_postings`.`comments_enable` AS `blog_posting_comments_enable`,
			`content_blog_postings`.`comment_count` AS `blog_posting_comment_count`,
			`content_blog_postings`.`trackbacks_enable` AS `blog_posting_trackbacks_enable`,
			`content_blog_postings`.`trackback_count` AS `blog_posting_trackback_count`,
			`content_blog_postings`.`pingbacks_enable` AS `blog_posting_pingbacks_enable`,
			`content_blog_postings`.`pingback_count` AS `blog_posting_pingback_count`,
			`content_blog_postings`.`tag_count` AS `blog_posting_tag_count`,
			`content_blog_postings`.`tag_array` AS `blog_posting_tag_array`,
			`content_blog_postings`.`date_modified` AS `blog_posting_date_modified`,
			`content_blog_postings`.`date_added` AS `blog_posting_date_added`,
			`content_blog_postings`.`day_added` AS `blog_posting_day_added`,
			`content_blog_postings`.`month_added` AS `blog_posting_month_added`,
			`content_blog_postings`.`year_added` AS `blog_posting_year_added`,
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
			".WCOM_DB_COMMUNITY_BLOG_COMMENTS." AS `community_blog_comments`
		JOIN
			".WCOM_DB_CONTENT_BLOG_POSTINGS." AS `content_blog_postings`
		  ON
			`community_blog_comments`.`posting` = `content_blog_postings`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_blog_postings`.`page` = `content_pages`.`id`
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
		$sql .= " AND `content_blog_postings`.`page` = :page ";
		$bind_params['page'] = $page;
	}
	if (!empty($status) && is_numeric($status)) {
		$sql .= " AND `community_blog_comments`.`status` = :status ";
		$bind_params['status'] = $status;
	}
	if (!empty($posting) && is_numeric($posting)) {
		$sql .= " AND `content_blog_postings`.`id` = :posting ";
		$bind_params['posting'] = $posting;
	}
	if (!empty($timeframe)) {
		$sql .= " AND ".$HELPER->_sqlForTimeFrame('`community_blog_comments`.`date_added`',
			$timeframe);
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
 * Method to count amount of saved blog postings. Takes key=>value
 * array with select params as first argument. Returns int.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>page, int, optional: Page id</li>
 * <li>status, int, optional: Blog comment status id</li>
 * <li>posting, int, optional: Posting id</li>
 * </ul>
 * 
 * @throws Community_BlogcommentException
 * @param array Select params
 * @return int
 */
public function countBlogComments ($params = array())
{
	// define some vars
	$page = null;
	$status = null;
	$posting = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Community_BlogcommentException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'timeframe':
					$$_key = (string)$_value;
				break;
			case 'page':
			case 'status':
			case 'posting':
					$$_key = (int)$_value;
				break;
			default:
				throw new Community_BlogcommentException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_COMMUNITY_BLOG_COMMENTS." AS `community_blog_comments`
		JOIN
			".WCOM_DB_CONTENT_BLOG_POSTINGS." AS `content_blog_postings`
		  ON
			`community_blog_comments`.`posting` = `content_blog_postings`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_blog_postings`.`page` = `content_pages`.`id`
		WHERE
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `content_blog_postings`.`page` = :page ";
		$bind_params['page'] = $page;
	}
	if (!empty($posting) && is_numeric($posting)) {
		$sql .= " AND `content_blog_postings`.`id` = :posting ";
		$bind_params['posting'] = $posting;
	}
	
	return $this->base->db->select($sql, 'field', $bind_params);
}


/**
 * Updates blog comment comment. Takes the posting id as first
 * argument. Returns amount of affected rows.
 * 
 * @throws Community_BlogcommentException
 * @param int Blog posting id
 * @return int Affected rows
 */
public function updateBlogCommentCount ($posting_id)
{
	// input check
	if (empty($posting_id) || !is_numeric($posting_id)) {
		throw new Community_BlogcommentException('Input for parameter posting_id is not numeric');
	}
	
	// count comments
	$comment_count = $this->countBlogComments(array('posting' => $posting_id));
	
	// prepare sql data
	$sqlData = array(
		'comment_count' => (int)$comment_count
	);
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$posting_id
	);
	
	// execute query
	return $this->base->db->update(WCOM_DB_CONTENT_BLOG_POSTINGS,
		$sqlData, $where, $bind_params);
}

// end of class
}

class Community_BlogcommentException extends Exception { }

?>