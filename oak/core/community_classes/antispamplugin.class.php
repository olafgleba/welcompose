<?php

/**
 * Project: Oak
 * File: antispamplugin.class.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License
 * http://www.opensource.org/licenses/osl-2.1.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

class Community_AntiSpamPlugin {
	
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
 * Singleton. Returns instance of the Community_AntiSpamPlugin object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Community_AntiSpamPlugin::$instance == null) {
		Community_AntiSpamPlugin::$instance = new Community_AntiSpamPlugin(); 
	}
	return Community_AntiSpamPlugin::$instance;
}

/**
 * Adds anti spam plugin to the anti spam plugin table. Takes a field=>value
 * array with anti spam plugin data as first argument. Returns insert id. 
 * 
 * @throws Community_AntiSpamPluginException
 * @param array Row data
 * @return int Insert id
 */
public function addAntiSpamPlugin ($sqlData)
{
	// access check
	if (!oak_check_access('Community', 'AntiSpamPlugin', 'Manage')) {
		throw new Community_AntiSpamPluginException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Community_AntiSpamPluginException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new anti spam plugin will be assigned to the current project
	$sqlData['project'] = OAK_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(OAK_DB_COMMUNITY_ANTI_SPAM_PLUGINS, $sqlData);
	
	// test if anti spam plugin belongs to current project/user
	if (!$this->antiSpamPluginBelongsToCurrentUser($insert_id)) {
		throw new Community_AntiSpamPluginException("Anti spam plugin does not belong to current user or project");
	}
}

/**
 * Updates anti spam plugin. Takes the anti spam plugin id as first argument, a
 * field=>value array with the new anti spam plugin data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Community_AntiSpamPluginException
 * @param int Anti spam plugin id
 * @param array Row data
 * @return int Affected rows
*/
public function updateAntiSpamPlugin ($id, $sqlData)
{
	// access check
	if (!oak_check_access('Community', 'AntiSpamPlugin', 'Manage')) {
		throw new Community_AntiSpamPluginException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Community_AntiSpamPluginException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Community_AntiSpamPluginException('Input for parameter sqlData is not an array');	
	}
	
	// test if anti spam plugin belongs to current project/user
	if (!$this->antiSpamPluginBelongsToCurrentUser($id)) {
		throw new Community_AntiSpamPluginException("Anti spam plugin does not belong to current user or project");
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(OAK_DB_COMMUNITY_ANTI_SPAM_PLUGINS, $sqlData,
		$where, $bind_params);
}

/**
 * Removes anti spam plugin from the anti spam plugins table. Takes the
 * anti spam plugin id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Community_AntiSpamPluginException
 * @param int Anti spam plugin id
 * @return int Amount of affected rows
 */
public function deleteAntiSpamPlugin ($id)
{
	// access check
	if (!oak_check_access('Community', 'AntiSpamPlugin', 'Manage')) {
		throw new Community_AntiSpamPluginException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Community_AntiSpamPluginException('Input for parameter id is not numeric');
	}
	
	// test if anti spam plugin belongs to current project/user
	if (!$this->antiSpamPluginBelongsToCurrentUser($id)) {
		throw new Community_AntiSpamPluginException("Anti spam plugin does not belong to current user or project");
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_COMMUNITY_ANTI_SPAM_PLUGINS, $where, $bind_params);
}

/**
 * Selects one anti spam plugin. Takes the anti spam plugin id as first
 * argument. Returns array with anti spam plugin information.
 * 
 * @throws Community_AntiSpamPluginException
 * @param int Anti spam plugin id
 * @return array
 */
public function selectAntiSpamPlugin ($id)
{
	// access check
	if (!oak_check_access('Community', 'AntiSpamPlugin', 'Use')) {
		throw new Community_AntiSpamPluginException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Community_AntiSpamPluginException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`community_anti_spam_plugins`.`id` AS `id`,
			`community_anti_spam_plugins`.`project` AS `project`,
			`community_anti_spam_plugins`.`type` AS `type`,
			`community_anti_spam_plugins`.`name` AS `name`,
			`community_anti_spam_plugins`.`internal_name` AS `internal_name`,
			`community_anti_spam_plugins`.`priority` AS `priority`,
			`community_anti_spam_plugins`.`active` AS `active`
		FROM
			".OAK_DB_COMMUNITY_ANTI_SPAM_PLUGINS." AS `community_anti_spam_plugins`
		WHERE 
			`community_anti_spam_plugins`.`id` = :id
		  AND
			`community_anti_spam_plugins`.`project` = :project
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
 * Method to select one or more anti spam plugins. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>order_macro, string, optional: Sorting instructions</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Community_AntiSpamPluginException
 * @param array Select params
 * @return array
 */
public function selectAntiSpamPlugins ($params = array())
{
	// access check
	if (!oak_check_access('Community', 'AntiSpamPlugin', 'Use')) {
		throw new Community_AntiSpamPluginException("You are not allowed to perform this action");
	}
	
	// define some vars
	$order_macros = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Community_AntiSpamPluginException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			case 'order_macro':
					$$_key = (string)$_value;
				break;
			default:
				throw new Community_AntiSpamPluginException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'NAME' => '`community_anti_spam_plugins`.`name`',
		'PRIORITY' => '`community_anti_spam_plugins`.`priority`'
	);
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// prepare query
	$sql = "
		SELECT 
			`community_anti_spam_plugins`.`id` AS `id`,
			`community_anti_spam_plugins`.`project` AS `project`,
			`community_anti_spam_plugins`.`type` AS `type`,
			`community_anti_spam_plugins`.`name` AS `name`,
			`community_anti_spam_plugins`.`internal_name` AS `internal_name`,
			`community_anti_spam_plugins`.`priority` AS `priority`,
			`community_anti_spam_plugins`.`active` AS `active`
		FROM
			".OAK_DB_COMMUNITY_ANTI_SPAM_PLUGINS." AS `community_anti_spam_plugins`
		WHERE 
			`community_anti_spam_plugins`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
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
 * Method to count the existing anti spam plugins. Takes key=>value
 * array with count params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * No params supported.
 * 
 * @throws Community_AntiSpamPluginException
 * @param array Count params
 * @return array
 */
public function countAntiSpamPlugins ($params = array())
{
	// access check
	if (!oak_check_access('Community', 'AntiSpamPlugin', 'Use')) {
		throw new Community_AntiSpamPluginException("You are not allowed to perform this action");
	}
	
	// define some vars
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Community_AntiSpamPluginException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			default:
				throw new Community_AntiSpamPluginException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_COMMUNITY_ANTI_SPAM_PLUGINS." AS `community_anti_spam_plugins`
		WHERE 
			`community_anti_spam_plugins`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query and return result
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Tests given anti spam plugin name for uniqueness. Takes the anti spam plugin
 * name as first argument and an optional anti spam plugin id as second argument.
 * If the anti spam plugin id is given, this anti spam plugin won't be considered
 * when checking for uniqueness (useful for updates). Returns boolean true if
 * anti spam plugin name is unique.
 *
 * @throws Community_AntiSpamPluginException
 * @param string Anti spam plugin name
 * @param int Anti spam plugin id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!oak_check_access('Community', 'AntiSpamPlugin', 'Use')) {
		throw new Community_AntiSpamPluginException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Community_AntiSpamPluginException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Community_AntiSpamPluginException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Community_AntiSpamPluginException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_COMMUNITY_ANTI_SPAM_PLUGINS." AS `community_anti_spam_plugins`
		WHERE
			`project` = :project
		  AND
			`name` = :name
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT,
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
 * Tests whether given anti spam plugin belongs to current project. Takes
 * the anti spam plugin id as first argument. Returns bool.
 *
 * @throws Community_AntiSpamPluginException
 * @param int Blog commen status id
 * @return int bool
 */
public function antiSpamPluginBelongsToCurrentProject ($anti_spam_plugin)
{
	// access check
	if (!oak_check_access('Community', 'AntiSpamPlugin', 'Use')) {
		throw new Community_AntiSpamPluginException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($anti_spam_plugin) || !is_numeric($anti_spam_plugin)) {
		throw new Community_AntiSpamPluginException('Input for parameter anti_spam_plugin is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".OAK_DB_COMMUNITY_ANTI_SPAM_PLUGINS." AS `community_anti_spam_plugins`
		WHERE
			`community_anti_spam_plugins`.`id` = :anti_spam_plugin
		AND
			`community_anti_spam_plugins`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'anti_spam_plugin' => (int)$anti_spam_plugin,
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
 * Test whether anti spam plugin belongs to current user or not. Takes
 * the anti spam plugin id as first argument. Returns bool.
 *
 * @throws Community_Blogcommenstatus
 * @param int Anti spam plugin id
 * @return bool
 */
public function antiSpamPluginBelongsToCurrentUser ($anti_spam_plugin)
{
	// access check
	if (!oak_check_access('Community', 'AntiSpamPlugin', 'Use')) {
		throw new Community_AntiSpamPluginException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($anti_spam_plugin) || !is_numeric($anti_spam_plugin)) {
		throw new Community_AntiSpamPluginException('Input for parameter anti_spam_plugin is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->antiSpamPluginBelongsToCurrentProject($anti_spam_plugin)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(OAK_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Community_AntiSpamPluginException extends Exception { }

?>