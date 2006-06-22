<?php

/**
 * Project: Oak
 * File: pingserviceconfiguration.class.php
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

class Application_Pingserviceconfiguration {
	
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
 * Singleton. Returns instance of the
 * Application_Pingserviceconfiguration object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Application_Pingserviceconfiguration::$instance == null) {
		Application_Pingserviceconfiguration::$instance = new Application_Pingserviceconfiguration(); 
	}
	return Application_Pingserviceconfiguration::$instance;
}

/**
 * Adds ping service configuration to the ping service configuration
 * table. Takes a field=>value array with ping service configuration
 * data as first argument. Returns insert id. 
 * 
 * @throws Application_PingserviceconfigurationException
 * @param array Row data
 * @return int Ping service configuration id
 */
public function addPingServiceConfiguration ($sqlData)
{
	if (!is_array($sqlData)) {
		throw new Application_PingserviceconfigurationException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	return $this->base->db->insert(OAK_DB_APPLICATION_PING_SERVICE_CONFIGURATION,
		$sqlData);
}

/**
 * Updates ping service configuration. Takes the ping service
 * configuration id as first argument, a field=>value array with the
 * new ping service configuration data as second argument. Returns
 * amount of affected rows.
 *
 * @throws Application_PingserviceconfigurationException
 * @param int Ping service configuration id
 * @param array Row data
 * @return int Affected rows
*/
public function updatePingServiceConfiguration ($id, $sqlData)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_PingserviceconfigurationException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Application_PingserviceconfigurationException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(OAK_DB_APPLICATION_PING_SERVICE_CONFIGURATION,
		$sqlData, $where, $bind_params);	
}

/**
 * Removes ping service configuration from the ping service configuration
 * table. Takes the ping service configuration id as first argument. Returns
 * amount of affected rows.
 * 
 * @throws Application_PingserviceconfigurationException
 * @param int Ping service configuration id
 * @return int Amount of affected rows
 */
public function deletePingServiceConfiguration ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_PingserviceconfigurationException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_APPLICATION_PING_SERVICE_CONFIGURATION,
			$where, $bind_params);
}

/**
 * Selects one ping service configuration. Takes the ping service
 * configuration id as first argument. Returns array with ping service
 * configuration information.
 * 
 * @throws Application_PingserviceconfigurationException
 * @param int Ping service configuration id
 * @return array
 */
public function selectPingServiceConfiguration ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Application_PingserviceconfigurationException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`application_ping_services`.`id` AS `ping_service_id`,
			`application_ping_services`.`name` AS `ping_service_name`,
			`application_ping_services`.`host` AS `ping_service_host`,
			`application_ping_services`.`port` AS `ping_service_port`,
			`application_ping_services`.`http_version` AS `ping_service_http_version`,
			`application_ping_services`.`path` AS `ping_service_path`,
			`application_ping_service_configurations`.`id` AS `id`,
			`application_ping_service_configurations`.`site_name` AS `site_name`,
			`application_ping_service_configurations`.`site_url` AS `site_url`,
			`application_ping_service_configurations`.`site_index` AS `site_index`,
			`application_ping_service_configurations`.`site_feed` AS `site_feed`
		FROM
			".OAK_DB_APPLICATION_PING_SERVICE_CONFIGURATION." AS `application_ping_service_configurations`
		LEFT JOIN
			".OAK_DB_APPLICATION_PING_SERVICES." AS `application_ping_services`
		  ON
			`application_ping_service_configurations`.`ping_service` = `application_ping_services`.`id`
		WHERE 
			1
	";
	
	// prepare where clauses
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `application_ping_service_configurations`.`id` = :id ";
		$bind_params['id'] = (int)$id;
	}
	
	// add limits
	$sql .= ' LIMIT 1';
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more ping service configurations. Takes
 * key=>value array with select params as first argument. Returns
 * array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>page, int, optional: Page id</li>
 * <li>ping_service, int, optional: Ping service id</li> 
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * </ul>
 * 
 * @throws Application_PingserviceconfigurationException
 * @param array Select params
 * @return array
 */
public function selectPingServiceConfigurations ($params = array())
{
	// define some vars
	$page = null;
	$ping_service = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Application_PingserviceconfigurationException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'page':
			case 'ping_service':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Application_PingserviceconfigurationException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			`application_ping_services`.`id` AS `ping_service_id`,
			`application_ping_services`.`name` AS `ping_service_name`,
			`application_ping_services`.`host` AS `ping_service_host`,
			`application_ping_services`.`port` AS `ping_service_port`,
			`application_ping_services`.`http_version` AS `ping_service_http_version`,
			`application_ping_services`.`path` AS `ping_service_path`,
			`application_ping_service_configurations`.`id` AS `id`,
			`application_ping_service_configurations`.`page` AS `page`,
			`application_ping_service_configurations`.`ping_service` AS `ping_service`,
			`application_ping_service_configurations`.`site_name` AS `site_name`,
			`application_ping_service_configurations`.`site_url` AS `site_url`,
			`application_ping_service_configurations`.`site_index` AS `site_index`,
			`application_ping_service_configurations`.`site_feed` AS `site_feed`
		FROM
			".OAK_DB_APPLICATION_PING_SERVICE_CONFIGURATION." AS `application_ping_service_configurations`
		LEFT JOIN
			".OAK_DB_APPLICATION_PING_SERVICES." AS `application_ping_services`
		  ON
			`application_ping_service_configurations`.`ping_service` = `application_ping_services`.`id`
		WHERE 
			1
	";
	
	// add where clauses
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `application_ping_service_configurations`.`page` = :page ";
		$bind_params['page'] = $page;
	}
	if (!empty($ping_service) && is_numeric($ping_service)) {
		$sql .= " AND `application_ping_service_configurations`.`ping_service` = :ping_service ";
		$bind_params['ping_service'] = $ping_service;
	}
	
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

// end of class
}

class Application_PingserviceconfigurationException extends Exception { }

?>