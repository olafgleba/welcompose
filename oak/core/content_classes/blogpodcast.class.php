<?php

/**
 * Project: Oak
 * File: blogpodcast.class.php
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

class Content_BlogPodcast {
	
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
 * Singleton. Returns instance of the Content_BlogPodcast object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Content_BlogPodcast::$instance == null) {
		Content_BlogPodcast::$instance = new Content_BlogPodcast(); 
	}
	return Content_BlogPodcast::$instance;
}

/**
 * Adds blog podcast to the blog podcast table. Takes a field=>value
 * array with blog podcast data as first argument. Returns insert id. 
 * 
 * @throws Content_BlogPodcastException
 * @param array Row data
 * @return int Insert id
 */
public function addBlogPodcast ($sqlData)
{
	// access check
	if (!oak_check_access('Content', 'BlogPodcast', 'Manage')) {
		throw new Content_BlogPodcastException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Content_BlogPodcastException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	$insert_id = $this->base->db->insert(OAK_DB_CONTENT_BLOG_PODCASTS, $sqlData);
	
	// test if blog podcast belongs to current user
	if (!$this->blogPodcastBelongsToCurrentUser($insert_id)) {
		throw new Content_BlogPodcastException('Blog podcast does not belong to current project or user');
	}
	
	// update metadata
	$this->updateMetadataFromSelectedSources($insert_id);
	
	return $insert_id;
}

/**
 * Updates blog podcast. Takes the blog podcast id as first argument, a
 * field=>value array with the new blog podcast data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_BlogPodcastException
 * @param int Blog podcast id
 * @param array Row data
 * @return int Affected rows
*/
public function updateBlogPodcast ($id, $sqlData)
{
	// access check
	if (!oak_check_access('Content', 'BlogPodcast', 'Manage')) {
		throw new Content_BlogPodcastException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_BlogPodcastException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_BlogPodcastException('Input for parameter sqlData is not an array');	
	}
	
	// test if blog podcast belongs to current user
	if (!$this->blogPodcastBelongsToCurrentUser($id)) {
		throw new Content_BlogPodcastException('Blog podcast does not belong to current project or user');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	$affected_rows = $this->base->db->update(OAK_DB_CONTENT_BLOG_PODCASTS, $sqlData,
		$where, $bind_params);
	
	// update metadata
	$this->updateMetadataFromSelectedSources($id);
	
	// return amount of affected rows
	return $affected_rows;
}

/**
 * Removes blog podcast from the blog podcasts table. Takes the
 * blog podcast id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Content_BlogPodcastException
 * @param int Blog podcast id
 * @return int Amount of affected rows
 */
public function deleteBlogPodcast ($id)
{
	// access check
	if (!oak_check_access('Content', 'BlogPodcast', 'Manage')) {
		throw new Content_BlogPodcastException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_BlogPodcastException('Input for parameter id is not numeric');
	}
	
	// test if blog podcast belongs to current user
	if (!$this->blogPodcastBelongsToCurrentUser($id)) {
		throw new Content_BlogPodcastException('Blog podcast does not belong to current project or user');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_CONTENT_BLOG_PODCASTS, $where, $bind_params);
}

/**
 * Selects one blog podcast. Takes the blog podcast id as first
 * argument. Returns array with blog podcast information.
 * 
 * @throws Content_BlogPodcastException
 * @param int Blog podcast id
 * @return array
 */
public function selectBlogPodcast ($id)
{
	// access check
	if (!oak_check_access('Content', 'BlogPodcast', 'Use')) {
		throw new Content_BlogPodcastException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_BlogPodcastException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`content_blog_podcasts`.`id` AS `id`,
			`content_blog_podcasts`.`blog_posting` AS `blog_posting`,
			`content_blog_podcasts`.`media_object` AS `media_object`,
			`content_blog_podcasts`.`title` AS `title`,
			`content_blog_podcasts`.`description_source` AS `description_source`,
			`content_blog_podcasts`.`description` AS `description`,
			`content_blog_podcasts`.`summary_source` AS `summary_source`,
			`content_blog_podcasts`.`summary` AS `summary`,
			`content_blog_podcasts`.`keywords_source` AS `keywords_source`,
			`content_blog_podcasts`.`keywords` AS `keywords`,
			`content_blog_podcasts`.`category_1` AS `category_1`,
			`content_blog_podcasts`.`category_2` AS `category_2`,
			`content_blog_podcasts`.`category_3` AS `category_3`,
			`content_blog_podcasts`.`pub_date` AS `pub_date`,
			`content_blog_podcasts`.`author` AS `author`,
			`content_blog_podcasts`.`block` AS `block`,
			`content_blog_podcasts`.`duration` AS `duration`,
			`content_blog_podcasts`.`explicit` AS `explicit`,
			`content_blog_podcasts`.`date_added` AS `date_added`,
			`content_blog_podcasts`.`date_modified` AS `date_modified`
		FROM
			".OAK_DB_CONTENT_BLOG_PODCASTS." AS `content_blog_podcasts`
		JOIN
			".OAK_DB_CONTENT_BLOG_POSTINGS." AS `content_blog_postings`
		  ON
			`content_blog_podcasts`.`blog_posting` = `content_blog_postings`.`id`
		JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_blog_postings`.`page` = `content_pages`.`id`
		WHERE
			`content_blog_podcasts`.`id` = :id
		  AND
			`content_pages`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more blog podcasts. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>page, int, optional: Page id</li>
 * <li>blog_posting, int, optional: Blog posting id</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * <li>order_marco, string, otpional: How to sort the result set.
 * Supported macros:
 *    <ul>
 *        <li>DATE_MODIFIED: sorty by date modified</li>
 *        <li>DATE_ADDED: sort by date added</li>
 *    </ul>
 * </li>
 * </ul>
 * 
 * @throws Content_BlogPodcastException
 * @param array Select params
 * @return array
 */
public function selectBlogPodcasts ($params = array())
{
	// access check
	if (!oak_check_access('Content', 'BlogPodcast', 'Use')) {
		throw new Content_BlogPodcastException("You are not allowed to perform this action");
	}
	
	// define some vars
	$page = null;
	$blog_posting = null;
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_BlogPodcastException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'order_macro':
					$$_key = (string)$_value;
				break;
			case 'page':
			case 'blog_posting':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_BlogPodcastException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'DATE_ADDED' => '`content_blog_podcasts`.`date_added`',
		'DATE_MODIFIED' => '`content_blog_podcasts`.`date_modified`'
	);
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// prepare query
	$sql = "
		SELECT 
			`content_blog_podcasts`.`id` AS `id`,
			`content_blog_podcasts`.`blog_posting` AS `blog_posting`,
			`content_blog_podcasts`.`media_object` AS `media_object`,
			`content_blog_podcasts`.`title` AS `title`,
			`content_blog_podcasts`.`description_source` AS `description_source`,
			`content_blog_podcasts`.`description` AS `description`,
			`content_blog_podcasts`.`summary_source` AS `summary_source`,
			`content_blog_podcasts`.`summary` AS `summary`,
			`content_blog_podcasts`.`keywords_source` AS `keywords_source`,
			`content_blog_podcasts`.`keywords` AS `keywords`,
			`content_blog_podcasts`.`category_1` AS `category_1`,
			`content_blog_podcasts`.`category_2` AS `category_2`,
			`content_blog_podcasts`.`category_3` AS `category_3`,
			`content_blog_podcasts`.`pub_date` AS `pub_date`,
			`content_blog_podcasts`.`author` AS `author`,
			`content_blog_podcasts`.`block` AS `block`,
			`content_blog_podcasts`.`duration` AS `duration`,
			`content_blog_podcasts`.`explicit` AS `explicit`,
			`content_blog_podcasts`.`date_added` AS `date_added`,
			`content_blog_podcasts`.`date_modified` AS `date_modified`
		FROM
			".OAK_DB_CONTENT_BLOG_PODCASTS." AS `content_blog_podcasts`
		JOIN
			".OAK_DB_CONTENT_BLOG_POSTINGS." AS `content_blog_postings`
		  ON
			`content_blog_podcasts`.`blog_posting` = `content_blog_postings`.`id`
		JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_blog_postings`.`page` = `content_pages`.`id`
		WHERE
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($blog_posting) && is_numeric($blog_posting)) {
		$sql .= " AND `content_blog_postings`.`id` = :blog_posting ";
		$bind_params['blog_posting'] = $blog_posting;
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
 * Method to count blog podcasts. Takes key=>value array
 * with select params as first argument. Returns int.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>page, int, optional: Page id</li>
 * <li>blog_posting, int, optional: Blog posting id</li>
 * </ul>
 * 
 * @throws Content_BlogPodcastException
 * @param array Select params
 * @return int
 */
public function countBlogPodcasts ($params = array())
{
	// access check
	if (!oak_check_access('Content', 'BlogPodcast', 'Use')) {
		throw new Content_BlogPodcastException("You are not allowed to perform this action");
	}
	
	// define some vars
	$page = null;
	$blog_posting = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_BlogPodcastException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'page':
			case 'blog_posting':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_BlogPodcastException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_CONTENT_BLOG_PODCASTS." AS `content_blog_podcasts`
		JOIN
			".OAK_DB_CONTENT_BLOG_POSTINGS." AS `content_blog_postings`
		  ON
			`content_blog_podcasts`.`blog_posting` = `content_blog_postings`.`id`
		JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_blog_podcasts`.`page` = `content_pages`.`id`
		WHERE
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($blog_posting) && is_numeric($blog_posting)) {
		$sql .= " AND `content_blog_postings`.`id` = :blog_posting ";
		$bind_params['blog_posting'] = $blog_posting;
	}
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `content_pages`.`id` = :page ";
		$bind_params['page'] = $page;
	}
	
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Updates metadata from selected sources. Takes the podcast id
 * as first argument. Returns amount of affected rows.
 *
 * @throws Content_BlogPodcastException
 * @param int Podcast id
 * @return int Affected rows
 */
protected function updateMetadataFromSelectedSources ($podcast_id)
{
	// access check
	if (!oak_check_access('Content', 'BlogPodcast', 'Manage')) {
		throw new Content_BlogPodcastException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($podcast_id) || !is_numeric($podcast_id)) {
		throw new Content_BlogPodcastException('Input for parameter podcast_id is not numeric');
	}
	
	// test if blog podcast belongs to current user
	if (!$this->blogPodcastBelongsToCurrentUser($podcast_id)) {
		throw new Content_BlogPodcastException('Blog podcast does not belong to current project or user');
	}
	
	// load blog posting class
	$BLOGPOSTING = load('Content:BlogPosting');
	
	// get podcast
	$podcast = $this->selectBlogPodcast($podcast_id);
	
	// get blog posting
	$blog_posting = $BLOGPOSTING->selectBlogPosting($podcast['blog_posting']);
	
	// init sql data array
	$sqlData = array();
	
	// update description
	switch ($podcast['description_source']) {
		case 'summary':
				$sqlData['description'] = $blog_posting['summary'];
			break;
		case 'content':
				$sqlData['description'] = $blog_posting['content'];
			break;
		case 'feed_summary':
				$sqlData['description'] = $blog_posting['feed_summary'];
			break;
		case 'empty':
		default:
				$sqlData['description'] = null;
			break;
	}
	
	// update summary
	switch ($podcast['summary_source']) {
		case 'summary':
				$sqlData['summary'] = $blog_posting['summary'];
			break;
		case 'content':
				$sqlData['summary'] = $blog_posting['content'];
			break;
		case 'feed_summary':
				$sqlData['summary'] = $blog_posting['feed_summary'];
			break;
		case 'empty':
		default:
				$sqlData['summary'] = null;
			break;
	}
	
	// update keywords
	switch ($podcast['keywords_source']) {
		case 'tags':
				$sqlData['keywords'] = $blog_posting['tag_array'];
			break;
		case 'empty':
		default:
				$sqlData['keywords'] = serialize(array());
			break;
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$podcast_id
	);
	
	// update row
	return $this->base->db->update(OAK_DB_CONTENT_BLOG_PODCASTS, $sqlData,
		$where, $bind_params);
} 

/**
 * Tests whether given blog podcast belongs to current project. Takes the
 * blog podcast id as first argument. Returns bool.
 *
 * @throws Content_BlogPodcastException
 * @param int Blog podcast id
 * @return int bool
 */
public function blogPodcastBelongsToCurrentProject ($blog_podcast)
{
	// access check
	if (!oak_check_access('Content', 'BlogPodcast', 'Use')) {
		throw new Content_BlogPodcastException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($blog_podcast) || !is_numeric($blog_podcast)) {
		throw new Content_BlogPodcastException('Input for parameter blog_podcast is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_CONTENT_BLOG_PODCASTS." AS `content_blog_podcasts`
		JOIN
			".OAK_DB_CONTENT_BLOG_POSTINGS." AS `content_blog_postings`
		  ON
			`content_blog_podcasts`.`blog_posting` = `content_blog_postings`.`id`
		JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_blog_postings`.`page` = `content_pages`.`id`
		WHERE
			`content_blog_podcasts`.`id` = :blog_podcast
		  AND
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'blog_podcast' => (int)$blog_podcast,
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
 * Test whether blog podcast belongs to current user or not. Takes
 * the blog podcast id as first argument. Returns bool.
 *
 * @throws Content_BlogPodcastException
 * @param int Blog podcast id
 * @return bool
 */
public function blogPodcastBelongsToCurrentUser ($blog_podcast)
{
	// access check
	if (!oak_check_access('Content', 'BlogPodcast', 'Use')) {
		throw new Content_BlogPodcastException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($blog_podcast) || !is_numeric($blog_podcast)) {
		throw new Content_BlogPodcastException('Input for parameter blog_podcast is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->blogPodcastBelongsToCurrentProject($blog_podcast)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(OAK_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Content_BlogPodcastException extends Exception { }

?>