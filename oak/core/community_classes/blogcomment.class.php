<?php

/**
 * Project: Oak
 * File: blogcomment.class.php
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
	return $this->base->db->insert(OAK_DB_COMMUNITY_BLOG_COMMENTS, $sqlData);
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
	return $this->base->db->update(OAK_DB_COMMUNITY_BLOG_COMMENTS, $sqlData,
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
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_COMMUNITY_BLOG_COMMENTS,	
		$where, $bind_params);
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
			`application_users`.`id` AS `user_id`,
			`application_users`.`group` AS `user_group`,
			`application_users`.`name` AS `user_name`,
			`application_users`.`email` AS `user_email`,
			`application_users`.`pwd` AS `user_pwd`,
			`application_users`.`public_email` AS `user_public_email`,
			`application_users`.`public_profile` AS `user_public_profile`,
			`application_users`.`author` AS `user_author`,
			`application_users`.`date_modified` AS `user_date_modified`,
			`application_users`.`date_added` AS `user_date_added`,
			`content_blog_postings`.`id` AS `posting_id`,
			`content_blog_postings`.`page` AS `posting_page`,
			`content_blog_postings`.`user` AS `posting_user`,
			`content_blog_postings`.`title` AS `posting_title`,
			`content_blog_postings`.`title_url` AS `posting_title_url`,
			`content_blog_postings`.`summary_raw` AS `posting_summary_raw`,
			`content_blog_postings`.`summary` AS `posting_summary`,
			`content_blog_postings`.`content_raw` AS `posting_content_raw`,
			`content_blog_postings`.`content` AS `posting_content`,
			`content_blog_postings`.`draft` AS `posting_draft`,
			`content_blog_postings`.`ping` AS `posting_ping`,
			`content_blog_postings`.`comments_enable` AS `posting_comments_enable`,
			`content_blog_postings`.`date_modified` AS `posting_date_modified`,
			`content_blog_postings`.`date_added` AS `posting_date_added`,
			`community_blog_comments`.`id` AS `id`,
			`community_blog_comments`.`posting` AS `posting`,
			`community_blog_comments`.`user` AS `user`,
			`community_blog_comments`.`content_raw` AS `content_raw`,
			`community_blog_comments`.`content` AS `content`,
			`community_blog_comments`.`edited` AS `edited`,
			`community_blog_comments`.`date_modified` AS `date_modified`,
			`community_blog_comments`.`date_added` AS `date_added`
		FROM
			".OAK_DB_COMMUNITY_BLOG_COMMENTS." AS `community_blog_comments`
		LEFT JOIN
			".OAK_DB_CONTENT_BLOG_POSTINGS." AS `content_blog_postings`
		  ON
			`community_blog_comments`.`posting` = `content_blog_postings`.`id`
		LEFT JOIN
			".OAK_DB_USER_USERS." AS `application_users`
		  ON
			`community_blog_comments`.`user` = `application_users`.`id`
		WHERE 
			1
	";
	
	// prepare where clauses
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `community_blog_comments`.`id` = :id ";
		$bind_params['id'] = (int)$id;
	}
	
	// add limits
	$sql .= ' LIMIT 1';
	
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
 * <li>user, int, optional: User id</li>
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
			case 'order_macro':
					$$_key = (string)$_value;
				break;
			case 'page':
			case 'user':
			case 'posting':
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
		'DATE_ADDED' => '`content_blog_postings`.`date_added`'
	);
	
	// prepare query
	$sql = "
		SELECT
			`application_users`.`id` AS `user_id`,
			`application_users`.`group` AS `user_group`,
			`application_users`.`name` AS `user_name`,
			`application_users`.`email` AS `user_email`,
			`application_users`.`pwd` AS `user_pwd`,
			`application_users`.`public_email` AS `user_public_email`,
			`application_users`.`public_profile` AS `user_public_profile`,
			`application_users`.`author` AS `user_author`,
			`application_users`.`date_modified` AS `user_date_modified`,
			`application_users`.`date_added` AS `user_date_added`,
			`content_blog_postings`.`id` AS `posting_id`,
			`content_blog_postings`.`page` AS `posting_page`,
			`content_blog_postings`.`user` AS `posting_user`,
			`content_blog_postings`.`title` AS `posting_title`,
			`content_blog_postings`.`title_url` AS `posting_title_url`,
			`content_blog_postings`.`summary_raw` AS `posting_summary_raw`,
			`content_blog_postings`.`summary` AS `posting_summary`,
			`content_blog_postings`.`content_raw` AS `posting_content_raw`,
			`content_blog_postings`.`content` AS `posting_content`,
			`content_blog_postings`.`draft` AS `posting_draft`,
			`content_blog_postings`.`ping` AS `posting_ping`,
			`content_blog_postings`.`comments_enable` AS `posting_comments_enable`,
			`content_blog_postings`.`date_modified` AS `posting_date_modified`,
			`content_blog_postings`.`date_added` AS `posting_date_added`,
			`community_blog_comments`.`id` AS `id`,
			`community_blog_comments`.`posting` AS `posting`,
			`community_blog_comments`.`user` AS `user`,
			`community_blog_comments`.`content_raw` AS `content_raw`,
			`community_blog_comments`.`content` AS `content`,
			`community_blog_comments`.`edited` AS `edited`,
			`community_blog_comments`.`date_modified` AS `date_modified`,
			`community_blog_comments`.`date_added` AS `date_added`
		FROM
			".OAK_DB_COMMUNITY_BLOG_COMMENTS." AS `community_blog_comments`
		LEFT JOIN
			".OAK_DB_CONTENT_BLOG_POSTINGS." AS `content_blog_postings`
		  ON
			`community_blog_comments`.`posting` = `content_blog_postings`.`id`
		LEFT JOIN
			".OAK_DB_USER_USERS." AS `application_users`
		  ON
			`community_blog_comments`.`user` = `application_users`.`id`
		WHERE 
			1
	";
	
	// add where clauses
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `content_blog_postings`.`page` = :page ";
		$bind_params['page'] = $page;
	}
	if (!empty($user) && is_numeric($user)) {
		$sql .= " AND `application_users`.`id` = :user ";
		$bind_params['user'] = $user;
	}
	if (!empty($posting) && is_numeric($posting)) {
		$sql .= " AND `content_blog_postings`.`id` = :posting ";
		$bind_params['posting'] = $posting;
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
 * <li>user, int, optional: User id</li>
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
	$user = null;
	$posting = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Community_BlogcommentException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'page':
			case 'user':
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
			COUNT (*) AS `total`
		FROM
			".OAK_DB_COMMUNITY_BLOG_COMMENTS." AS `community_blog_comments`
		LEFT JOIN
			".OAK_DB_CONTENT_BLOG_POSTINGS." AS `content_blog_postings`
		  ON
			`community_blog_comments`.`posting` = `content_blog_postings`.`id`
		LEFT JOIN
			".OAK_DB_USER_USERS." AS `application_users`
		  ON
			`community_blog_comments`.`user` = `application_users`.`id`
		WHERE 
			1
	";
	
	// add where clauses
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `content_blog_postings`.`page` = :page ";
		$bind_params['page'] = $page;
	}
	if (!empty($user) && is_numeric($user)) {
		$sql .= " AND `application_users`.`id` = :user ";
		$bind_params['user'] = $user;
	}
	if (!empty($posting) && is_numeric($posting)) {
		$sql .= " AND `content_blog_postings`.`id` = :posting ";
		$bind_params['posting'] = $posting;
	}
	
	return $this->base->db->select($sql, 'field', $bind_params);
}

// end of class
}

class Community_BlogcommentException extends Exception { }

?>