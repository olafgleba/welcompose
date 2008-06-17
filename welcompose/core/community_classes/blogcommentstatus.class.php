<?php

/**
 * Project: Welcompose
 * File: blogcommentstatus.class.php
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
 * Singleton for Community_BlogCommentStatus.
 * 
 * @return object
 */
function Community_BlogCommentStatus ()
{
	if (Community_BlogCommentStatus::$instance == null) {
		Community_BlogCommentStatus::$instance = new Community_BlogCommentStatus(); 
	}
	return Community_BlogCommentStatus::$instance;
}

class Community_BlogCommentStatus {
	
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
 * Adds blog comment status to the blog comment status table. Takes a field=>value
 * array with blog comment status data as first argument. Returns insert id. 
 * 
 * @throws Community_BlogCommentStatusException
 * @param array Row data
 * @return int Insert id
 */
public function addBlogCommentStatus ($sqlData)
{
	// access check
	if (!wcom_check_access('Community', 'BlogCommentStatus', 'Manage')) {
		throw new Community_BlogCommentStatusException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Community_BlogCommentStatusException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new blog comment status will be assigned to the current project
	$sqlData['project'] = WCOM_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_COMMUNITY_BLOG_COMMENT_STATUSES, $sqlData);
	
	// test if blog comment status belongs to current project/user
	if (!$this->blogCommentStatusBelongsToCurrentUser($insert_id)) {
		throw new Community_BlogCommentStatusException("Blog comment status does not belong to current user or project");
	}
}

/**
 * Updates blog comment status. Takes the blog comment status id as first argument, a
 * field=>value array with the new blog comment status data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Community_BlogCommentStatusException
 * @param int Blog comment status id
 * @param array Row data
 * @return int Affected rows
*/
public function updateBlogCommentStatus ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Community', 'BlogCommentStatus', 'Manage')) {
		throw new Community_BlogCommentStatusException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Community_BlogCommentStatusException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Community_BlogCommentStatusException('Input for parameter sqlData is not an array');	
	}
	
	// test if blog comment status belongs to current project/user
	if (!$this->blogCommentStatusBelongsToCurrentUser($id)) {
		throw new Community_BlogCommentStatusException("Blog comment status does not belong to current user or project");
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_COMMUNITY_BLOG_COMMENT_STATUSES, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes blog comment status from the blog comment statuses table. Takes the
 * blog comment status id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Community_BlogCommentStatusException
 * @param int Blog comment status id
 * @return int Amount of affected rows
 */
public function deleteBlogCommentStatus ($id)
{
	// access check
	if (!wcom_check_access('Community', 'BlogCommentStatus', 'Manage')) {
		throw new Community_BlogCommentStatusException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Community_BlogCommentStatusException('Input for parameter id is not numeric');
	}
	
	// test if blog comment status belongs to current project/user
	if (!$this->blogCommentStatusBelongsToCurrentUser($id)) {
		throw new Community_BlogCommentStatusException("Blog comment status does not belong to current user or project");
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_COMMUNITY_BLOG_COMMENT_STATUSES, $where, $bind_params);
}

/**
 * Selects one blog comment status. Takes the blog comment status id as first
 * argument. Returns array with blog comment status information.
 * 
 * @throws Community_BlogCommentStatusException
 * @param int Blog comment status id
 * @return array
 */
public function selectBlogCommentStatus ($id)
{
	// access check
	if (!wcom_check_access('Community', 'BlogCommentStatus', 'Use')) {
		throw new Community_BlogCommentStatusException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Community_BlogCommentStatusException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`community_blog_comment_statuses`.`id` AS `id`,
			`community_blog_comment_statuses`.`project` AS `project`,
			`community_blog_comment_statuses`.`name` AS `name`
		FROM
			".WCOM_DB_COMMUNITY_BLOG_COMMENT_STATUSES." AS `community_blog_comment_statuses`
		WHERE 
			`community_blog_comment_statuses`.`id` = :id
		  AND
			`community_blog_comment_statuses`.`project` = :project
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
 * Method to select one or more blog comment statuses. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Community_BlogCommentStatusException
 * @param array Select params
 * @return array
 */
public function selectBlogCommentStatuses ($params = array())
{
	// access check
	if (!wcom_check_access('Community', 'BlogCommentStatus', 'Use')) {
		throw new Community_BlogCommentStatusException("You are not allowed to perform this action");
	}
	
	// define some vars
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Community_BlogCommentStatusException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Community_BlogCommentStatusException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`community_blog_comment_statuses`.`id` AS `id`,
			`community_blog_comment_statuses`.`project` AS `project`,
			`community_blog_comment_statuses`.`name` AS `name`
		FROM
			".WCOM_DB_COMMUNITY_BLOG_COMMENT_STATUSES." AS `community_blog_comment_statuses`
		WHERE 
			`community_blog_comment_statuses`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add sorting
	$sql .= " ORDER BY `community_blog_comment_statuses`.`name` ";
	
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
 * Method to count the existing blog comment statuses. Takes key=>value
 * array with count params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * No params supported.
 * 
 * @throws Community_BlogCommentStatusException
 * @param array Count params
 * @return array
 */
public function countBlogCommentStatuses ($params = array())
{
	// access check
	if (!wcom_check_access('Community', 'BlogCommentStatus', 'Use')) {
		throw new Community_BlogCommentStatusException("You are not allowed to perform this action");
	}
	
	// define some vars
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Community_BlogCommentStatusException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			default:
				throw new Community_BlogCommentStatusException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_COMMUNITY_BLOG_COMMENT_STATUSES." AS `community_blog_comment_statuses`
		WHERE 
			`community_blog_comment_statuses`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}
/**
 * Tests given blog comment status name for uniqueness. Takes the blog comment status
 * name as first argument and an optional blog comment status id as second argument.
 * If the blog comment status id is given, this blog comment status won't be considered
 * when checking for uniqueness (useful for updates). Returns boolean true if
 * blog comment status name is unique.
 *
 * @throws Community_BlogCommentStatusException
 * @param string Blog comment status name
 * @param int Blog comment status id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!wcom_check_access('Community', 'BlogCommentStatus', 'Use')) {
		throw new Community_BlogCommentStatusException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Community_BlogCommentStatusException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Community_BlogCommentStatusException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Community_BlogCommentStatusException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_COMMUNITY_BLOG_COMMENT_STATUSES." AS `community_blog_comment_statuses`
		WHERE
			`project` = :project
		  AND
			`name` = :name
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT,
		'name' => $name
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
 * Tests whether given blog comment status belongs to current project. Takes
 * the blog comment status id as first argument. Returns bool.
 *
 * @throws Community_BlogCommentStatusException
 * @param int Blog commen status id
 * @return int bool
 */
public function blogCommentStatusBelongsToCurrentProject ($blog_comment_status)
{
	// access check
	if (!wcom_check_access('Community', 'BlogCommentStatus', 'Use')) {
		throw new Community_BlogCommentStatusException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($blog_comment_status) || !is_numeric($blog_comment_status)) {
		throw new Community_BlogCommentStatusException('Input for parameter blog_comment_status is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".WCOM_DB_COMMUNITY_BLOG_COMMENT_STATUSES." AS `community_blog_comment_statuses`
		WHERE
			`community_blog_comment_statuses`.`id` = :blog_comment_status
		AND
			`community_blog_comment_statuses`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'blog_comment_status' => (int)$blog_comment_status,
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
 * Test whether blog comment status belongs to current user or not. Takes
 * the blog comment status id as first argument. Returns bool.
 *
 * @throws Community_Blogcommenstatus
 * @param int Blog comment status id
 * @return bool
 */
public function blogCommentStatusBelongsToCurrentUser ($blog_comment_status)
{
	// access check
	if (!wcom_check_access('Community', 'BlogCommentStatus', 'Use')) {
		throw new Community_BlogCommentStatusException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($blog_comment_status) || !is_numeric($blog_comment_status)) {
		throw new Community_BlogCommentStatusException('Input for parameter blog_comment_status is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->blogCommentStatusBelongsToCurrentProject($blog_comment_status)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Community_BlogCommentStatusException extends Exception { }

?>