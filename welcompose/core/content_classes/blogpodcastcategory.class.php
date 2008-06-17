<?php

/**
 * Project: Welcompose
 * File: blogpodcastcategory.class.php
 * 
 * Copyright (c) 2008 creatics media.systems
 * 
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id$
 * 
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton for Content_BlogPodcastCategory.
 * 
 * @return object
 */
function Content_BlogPodcastCategory ()
{
	if (Content_BlogPodcastCategory::$instance == null) {
		Content_BlogPodcastCategory::$instance = new Content_BlogPodcastCategory(); 
	}
	return Content_BlogPodcastCategory::$instance;
}

class Content_BlogPodcastCategory {
	
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
 * Adds blog podcast category to the blog podcast category table. Takes a field=>value
 * array with blog podcast category data as first argument. Returns insert id. 
 * 
 * @throws Content_BlogPodcastCategoryException
 * @param array Row data
 * @return int Insert id
 */
public function addBlogPodcastCategory ($sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'BlogPodcastCategory', 'Manage')) {
		throw new Content_BlogPodcastCategoryException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Content_BlogPodcastCategoryException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_CONTENT_BLOG_PODCAST_CATEGORIES, $sqlData);
	
	// test if blog podcast category belongs to current user
	if (!$this->blogPodcastCategoryBelongsToCurrentUser($insert_id)) {
		throw new Content_BlogPodcastCategoryException('Blog podcast does not belong to current project or user');
	}
	
	// update category name
	$this->updateBlogPodcastCategoryName($insert_id);
	
	return $insert_id;
}

/**
 * Updates blog podcast category. Takes the blog podcast category id as first argument, a
 * field=>value array with the new blog podcast category data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_BlogPodcastCategoryException
 * @param int Blog podcast id
 * @param array Row data
 * @return int Affected rows
*/
public function updateBlogPodcastCategory ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Content', 'BlogPodcastCategory', 'Manage')) {
		throw new Content_BlogPodcastCategoryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_BlogPodcastCategoryException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_BlogPodcastCategoryException('Input for parameter sqlData is not an array');	
	}
	
	// test if blog podcast category belongs to current user
	if (!$this->blogPodcastCategoryBelongsToCurrentUser($id)) {
		throw new Content_BlogPodcastCategoryException('Blog podcast does not belong to current project or user');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	$affected_rows = $this->base->db->update(WCOM_DB_CONTENT_BLOG_PODCAST_CATEGORIES, $sqlData,
		$where, $bind_params);
		
	// update category name
	$this->updateBlogPodcastCategoryName($id);
	
	// return amounf of affected rows
	return $affected_rows;
}

/**
 * Removes blog podcast category from the blog podcast categorys table. Takes the
 * blog podcast category id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Content_BlogPodcastCategoryException
 * @param int Blog podcast id
 * @return int Amount of affected rows
 */
public function deleteBlogPodcastCategory ($id)
{
	// access check
	if (!wcom_check_access('Content', 'BlogPodcastCategory', 'Manage')) {
		throw new Content_BlogPodcastCategoryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_BlogPodcastCategoryException('Input for parameter id is not numeric');
	}
	
	// test if blog podcast category belongs to current user
	if (!$this->blogPodcastCategoryBelongsToCurrentUser($id)) {
		throw new Content_BlogPodcastCategoryException('Blog podcast does not belong to current project or user');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_CONTENT_BLOG_PODCAST_CATEGORIES, $where, $bind_params);
}

/**
 * Method to select a blog podcast category. Takes the blog podcast
 * category id as first argument. Returns array.
 * 
 * @throws Content_BlogPodcastCategoryException
 * @param int Podcast category id
 * @return array
 */
public function selectBlogPodcastCategory ($id)
{
	// access check
	if (!wcom_check_access('Content', 'BlogPodcastCategory', 'Use')) {
		throw new Content_BlogPodcastCategoryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_BlogPodcastCategoryException('Input for parameter id is not numeric');
	}
	
	// prepare query
	$sql = "
		SELECT 
			`content_blog_podcast_categories`.`id`,
			`content_blog_podcast_categories`.`project`,
			`content_blog_podcast_categories`.`name`,
			`content_blog_podcast_categories`.`category`,
			`content_blog_podcast_categories`.`subcategory`,
			`content_blog_podcast_categories`.`date_added`,
			`content_blog_podcast_categories`.`date_modified`
		FROM
			".WCOM_DB_CONTENT_BLOG_PODCAST_CATEGORIES." AS `content_blog_podcast_categories`
		WHERE
			`id` = :id
		  AND
			`content_blog_podcast_categories`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more blog podcast categories. Takes key=>value
 * array with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Content_BlogPodcastCategoryException
 * @param array Select params
 * @return array
 */
public function selectBlogPodcastCategories ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'BlogPodcastCategory', 'Use')) {
		throw new Content_BlogPodcastCategoryException("You are not allowed to perform this action");
	}
	
	// define some vars
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_BlogPodcastCategoryException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_BlogPodcastCategoryException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`content_blog_podcast_categories`.`id`,
			`content_blog_podcast_categories`.`project`,
			`content_blog_podcast_categories`.`name`,
			`content_blog_podcast_categories`.`category`,
			`content_blog_podcast_categories`.`subcategory`,
			`content_blog_podcast_categories`.`date_added`,
			`content_blog_podcast_categories`.`date_modified`
		FROM
			".WCOM_DB_CONTENT_BLOG_PODCAST_CATEGORIES." AS `content_blog_podcast_categories`
		WHERE
			`content_blog_podcast_categories`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
		
	// add sorting
	$sql .= " ORDER BY `content_blog_podcast_categories`.`name` ";
	
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
 * Method to count blog podcast categories. Takes key=>value
 * array with count params as first argument. Returns int.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>none</li>
 * </ul>
 * 
 * @throws Content_BlogPodcastCategoryException
 * @param array Count params
 * @return array
 */
public function countBlogPodcastCategories ($params = array())
{
	// access check
	if (!wcom_check_access('Content', 'BlogPodcastCategory', 'Use')) {
		throw new Content_BlogPodcastCategoryException("You are not allowed to perform this action");
	}
	
	// define some vars
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_BlogPodcastCategoryException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			default:
				throw new Content_BlogPodcastCategoryException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_BLOG_PODCAST_CATEGORIES." AS `content_blog_podcast_categories`
		WHERE
			`content_blog_podcast_categories`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Tests given blog podcast category name for uniqueness. Takes the blog
 * podcast category name as first argument and an array with the subcategory name
 * as first element and the optional blog podcast category id as second element.
 * If the blog podcast category id is given, this blog podcast category won't be
 * considered when checking for uniqueness (useful for updates). Returns boolean
 * true if the combination of blog podcast category and blog podcast category is
 * unique.
 *
 * @throws Content_BlogPodcastException
 * @param string Podcast category name
 * @param array Podcast subcategory name and podcast category id
 * @return bool
 */
public function testForUniqueCategoryName ($name, $params = array())
{
	// access check
	if (!wcom_check_access('Content', 'BlogPodcastCategory', 'Use')) {
		throw new Content_BlogPodcastCategoryException("You are not allowed to perform this action");
	}
	
	// import params
	$subcategory_name = Base_Cnc::ifsetor($params[0], null);
	$id = Base_Cnc::ifsetor($params[1], null);
	
	// input check
	if (empty($name)) {
		throw new Content_BlogPodcastCategoryException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Content_BlogPodcastCategoryException("Input for parameter name is expected to be scalar");
	}
	if (!is_scalar($subcategory_name)) {
		throw new Content_BlogPodcastCategoryException("Input for parameter subcategory_name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Content_BlogPodcastCategoryException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_BLOG_PODCAST_CATEGORIES." AS `content_blog_podcast_categories`
		WHERE
			`project` = :project
		  AND
			`category` = :category
		  AND
			`subcategory` = :subcategory
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT,
		'category' => $name,
		'subcategory' => $subcategory_name
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
 * Updates the blog podcast category name using a combination
 * of category and subcategory name.
 *
 * @throws Content_BlogPodcastCategoryException
 * @param int Blog podcast category id
 * @return int Affected rows
 */
protected function updateBlogPodcastCategoryName ($id)
{
	// access check
	if (!wcom_check_access('Content', 'BlogPodcastCategory', 'Manage')) {
		throw new Content_BlogPodcastCategoryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_BlogPodcastCategoryException('Input for parameter id is not an array');
	}
	
	// test if blog podcast category belongs to current user
	if (!$this->blogPodcastCategoryBelongsToCurrentUser($id)) {
		throw new Content_BlogPodcastCategoryException('Blog podcast does not belong to current project or user');
	}
	
	// get category
	$podcast_category = $this->selectBlogPodcastCategory($id);
	
	// prepare category name
	$name_components = array();
	$category = Base_Cnc::ifsetor($podcast_category['category'], null);
	$subcategory = Base_Cnc::ifsetor($podcast_category['subcategory'], null);
	
	if (!empty($category)) {
		$name_components[] = $category;
	}
	if (!empty($subcategory)) {
		$name_components[] = $subcategory;
	}
	
	// prepare sql data
	$sqlData = array(
		'name' => implode(' > ', $name_components)
	);
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_CONTENT_BLOG_PODCAST_CATEGORIES, $sqlData,
		$where, $bind_params);
}

/**
 * Tests whether given blog podcast category belongs to current project. Takes the
 * blog podcast category id as first argument. Returns bool.
 *
 * @throws Content_BlogPodcastCategoryException
 * @param int Global blog podcast category id
 * @return int bool
 */
public function blogPodcastCategoryBelongsToCurrentProject ($blog_podcast_category)
{
	// access check
	if (!wcom_check_access('Content', 'BlogPodcastCategory', 'Use')) {
		throw new Content_BlogPodcastCategoryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($blog_podcast_category) || !is_numeric($blog_podcast_category)) {
		throw new Content_BlogPodcastCategoryException('Input for parameter box is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_CONTENT_BLOG_PODCAST_CATEGORIES." AS `content_blog_podcast_categories`
		WHERE
			`content_blog_podcast_categories`.`id` = :box
		  AND
			`content_blog_podcast_categories`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'box' => (int)$blog_podcast_category,
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
 * Test whether box belongs to current user or not. Takes
 * the box id as first argument. Returns bool.
 *
 * @throws Content_BlogPodcastCategoryException
 * @param int box id
 * @return bool
 */
public function blogPodcastCategoryBelongsToCurrentUser ($blog_podcast_category)
{
	// access check
	if (!wcom_check_access('Content', 'BlogPodcastCategory', 'Use')) {
		throw new Content_BlogPodcastCategoryException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($blog_podcast_category) || !is_numeric($blog_podcast_category)) {
		throw new Content_BlogPodcastCategoryException('Input for parameter blog podcast category is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->blogPodcastCategoryBelongsToCurrentProject($blog_podcast_category)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Content_BlogPodcastCategoryException extends Exception { }

?>