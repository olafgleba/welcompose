<?php

/**
 * Project: Welcompose
 * File: pingservice.class.php
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

class Application_Pingservice {
	
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
 * Singleton. Returns instance of the Application_Pingservice object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Application_Pingservice::$instance == null) {
		Application_Pingservice::$instance = new Application_Pingservice(); 
	}
	return Application_Pingservice::$instance;
}

/**
 * Adds ping service to the ping service table. Takes a field=>value
 * array with ping service data as first argument. Returns insert id. 
 * 
 * @throws Application_PingserviceException
 * @param array Row data
 * @return int Insert id
 */
public function addPingService ($sqlData)
{
	// access check
	if (!wcom_check_access('Application', 'PingService', 'Manage')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	if (!is_array($sqlData)) {
		throw new Application_PingserviceException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new ping service will be assigned to the current project
	$sqlData['project'] = WCOM_CURRENT_PROJECT;
	
	// insert row
	$insert_id = $this->base->db->insert(WCOM_DB_APPLICATION_PING_SERVICES, $sqlData);

	// test if ping service belongs to current user/project
	if (!$this->pingServiceBelongsToCurrentUser($insert_id)) {
		throw new Application_PingserviceException("Ping service does not belong to current user or project");
	}
	
	return $insert_id;
}

/**
 * Updates ping service. Takes the ping service id as first argument, a
 * field=>value array with the new ping service data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Application_PingserviceException
 * @param int Ping service id
 * @param array Row data
 * @return int Affected rows
*/
public function updatePingService ($id, $sqlData)
{
	// access check
	if (!wcom_check_access('Application', 'PingService', 'Manage')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_PingserviceException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Application_PingserviceException('Input for parameter sqlData is not an array');	
	}
	
	// test if ping service belongs to current user/project
	if (!$this->pingServiceBelongsToCurrentUser($id)) {
		throw new Application_PingserviceException("Ping service does not belong to current user or project");
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(WCOM_DB_APPLICATION_PING_SERVICES, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes ping service from the ping services table. Takes the
 * ping service id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Application_PingserviceException
 * @param int Ping service id
 * @return int Amount of affected rows
 */
public function deletePingService ($id)
{
	// access check
	if (!wcom_check_access('Application', 'PingService', 'Manage')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_PingserviceException('Input for parameter id is not numeric');
	}
	
	// test if ping service belongs to current user/project
	if (!$this->pingServiceBelongsToCurrentUser($id)) {
		throw new Application_PingserviceException("Ping service does not belong to current user or project");
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(WCOM_DB_APPLICATION_PING_SERVICES, $where, $bind_params);
}

/**
 * Selects one ping service. Takes the ping service id as first
 * argument. Returns array with ping service information.
 * 
 * @throws Application_PingserviceException
 * @param int Ping service id
 * @return array
 */
public function selectPingService ($id)
{
	// access check
	if (!wcom_check_access('Application', 'PingService', 'Use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_PingserviceException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`application_ping_services`.`id` AS `id`,
			`application_ping_services`.`project` AS `project`,
			`application_ping_services`.`name` AS `name`,
			`application_ping_services`.`host` AS `host`,
			`application_ping_services`.`port` AS `port`,
			`application_ping_services`.`path` AS `path`
		FROM
			".WCOM_DB_APPLICATION_PING_SERVICES." AS `application_ping_services`
		WHERE 
			`application_ping_services`.`id` = :id
		  AND
			`application_ping_services`.`project` = :project
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
 * Method to select one or more ping services. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Application_PingserviceException
 * @param array Select params
 * @return array
 */
public function selectPingServices ($params = array())
{
	// access check
	if (!wcom_check_access('Application', 'PingService', 'Use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// define some vars
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Application_PingserviceException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Application_PingserviceException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`application_ping_services`.`id` AS `id`,
			`application_ping_services`.`name` AS `name`,
			`application_ping_services`.`host` AS `host`,
			`application_ping_services`.`port` AS `port`,
			`application_ping_services`.`path` AS `path`
		FROM
			".WCOM_DB_APPLICATION_PING_SERVICES." AS `application_ping_services`
		WHERE 
			`application_ping_services`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add sorting
	$sql .= " ORDER BY `application_ping_services`.`name` ";
	
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
 * Method to count the available ping services. Takes key=>value
 * array with count params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * No params supported.
 * 
 * @throws Application_PingserviceException
 * @param array Count params
 * @return array
 */
public function countPingServices ($params = array())
{
	// access check
	if (!wcom_check_access('Application', 'PingService', 'Use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// define some vars
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Application_PingserviceException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			default:
				throw new Application_PingserviceException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_APPLICATION_PING_SERVICES." AS `application_ping_services`
		WHERE 
			`application_ping_services`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return (int)$this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Tests given ping service name for uniqueness. Takes the ping service
 * name as first argument and an optional ping service id as second argument.
 * If the ping service id is given, this ping service won't be considered
 * when checking for uniqueness (useful for updates). Returns boolean true if
 * ping service name is unique.
 *
 * @throws Application_PingserviceException
 * @param string Ping service name
 * @param int Ping service id
 * @return bool
 */
public function testForUniqueName ($name, $id = null)
{
	// access check
	if (!wcom_check_access('Application', 'PingService', 'Use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name)) {
		throw new Application_PingserviceException("Input for parameter name is not expected to be empty");
	}
	if (!is_scalar($name)) {
		throw new Application_PingserviceException("Input for parameter name is expected to be scalar");
	}
	if (!is_null($id) && ((int)$id < 1 || !is_numeric($id))) {
		throw new Application_PingserviceException("Input for parameter id is expected to be numeric");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_APPLICATION_PING_SERVICES." AS `application_ping_services`
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
 * Notifies ping service. Takes the id of the ping service configuration
 * to use as first argument. Returns true.
 * 
 * @throws Application_PingserviceException
 * @param int Ping service configuration id
 * @return bool
 */
public function pingService ($configuration_id)
{
	// access check
	if (!wcom_check_access('Application', 'PingService', 'Use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
		
	// input check
	if (empty($configuration_id) || !is_numeric($configuration_id)) {
		throw new Application_PingserviceException("Input for parameter configuration_id is not numeric");
	}
	
	// load ping service configuration class
	$PINGSERVICECONFIGURATION = load('application:pingserviceconfiguration');
	
	// test if ping service belongs to current user/project 
	if (!$PINGSERVICECONFIGURATION->pingServiceConfigurationBelongsToCurrentUser($configuration_id)) {
		throw new Application_PingserviceException("Ping service does not belong to current user or project");
	}
	
	// get service configuration
	$configuration = $PINGSERVICECONFIGURATION->selectPingServiceConfiguration($configuration_id);
	
	// make sure that we got the configuration
	if (empty($configuration) || !is_array($configuration)) {
		throw new Application_PingserviceException("Requested ping service configuration does not exist");
	}
	
	// get service
	$service = $this->selectPingService($configuration['ping_service_id']);
	
	// test if ping service belongs to current user/project
	if (!$this->pingServiceBelongsToCurrentUser($service['id'])) {
		throw new Application_PingserviceException("Ping service does not belong to current user or project");
	}
	
	// make sure that we got the service
	if (empty($service) || !is_array($service)) {
		throw new Application_PingserviceException("Requested ping service does not exist");
	}
		
	// load PEAR::XML_RPC
	require_once "XML/RPC.php";
	
	// initialize new XML-RPC client
	$client = new XML_RPC_Client($service['path'], $service['host'], $service['port']);
	
	// prepare the ping message using weblogs.com's extendedPing interface 
	$message = new XML_RPC_MESSAGE('weblogUpdates.extendedPing');
	$message->addParam(new XML_RPC_VALUE($configuration['site_name'], 'string'));
	$message->addParam(new XML_RPC_VALUE($configuration['site_url'], 'string'));
	$message->addParam(new XML_RPC_VALUE($configuration['site_index'], 'string'));
	$message->addParam(new XML_RPC_VALUE($configuration['site_feed'], 'string'));
	
	// send notification
	$response = $client->send($message, 5);
	
	// convert response object into struct
	$response_struct = $response->value();
	
	// get response error
	$error = $response_struct->structmem('flerror')->getVal();
		
	// get response message
	$message = $response_struct->structmem('message')->getVal();	
	
	// evaluate error code
	if ($error > 0)  {
		throw new Application_PingserviceException("Ping failed, reason: ".strip_tags($message));
	} else {
		return true;
	}
}

/**
 * Tests whether given ping service belongs to current project. Takes the
 * ping service id as first argument. Returns bool.
 *
 * @throws Application_PingserviceException
 * @param int Ping service id
 * @return int bool
 */
public function pingServiceBelongsToCurrentProject ($ping_service)
{
	// access check
	if (!wcom_check_access('Application', 'PingService', 'Use')) {
		throw new Application_TextmacroException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($ping_service) || !is_numeric($ping_service)) {
		throw new Application_TextmacroException('Input for parameter ping_service is expected to be a numeric value');
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			".WCOM_DB_APPLICATION_PING_SERVICES." AS `application_ping_services`
		WHERE
			`application_ping_services`.`id` = :ping_service
		AND
			`application_ping_services`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'ping_service' => (int)$ping_service,
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
 * Test whether ping service belongs to current user or not. Takes
 * the ping service id as first argument. Returns bool.
 *
 * @throws Application_PingserviceException
 * @param int Ping service id
 * @return bool
 */
public function pingServiceBelongsToCurrentUser ($ping_service)
{
	// access check
	if (!wcom_check_access('Application', 'PingService', 'Use')) {
		throw new Application_PingserviceException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($ping_service) || !is_numeric($ping_service)) {
		throw new Application_PingserviceException('Input for parameter ping_service is expected to be a numeric value');
	}
	
	// load user class
	$USER = load('user:user');
	
	if (!$this->pingServiceBelongsToCurrentProject($ping_service)) {
		return false;
	}
	if (!$USER->userBelongsToCurrentProject(WCOM_CURRENT_USER)) {
		return false;
	}
	
	return true;
}

// end of class
}

class Application_PingserviceException extends Exception { }

?>