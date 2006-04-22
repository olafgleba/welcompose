<?php

/**
 * Project: Oak
 * File: document.class.php
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
 * $Id: document.class.php 2 2006-03-20 11:43:20Z andreas $
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/apache2.0.php Apache License, Version 2.0
 */

class Media_Document {
	
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
 * Singleton. Returns instance of the Media_Document object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Media_Document::$instance == null) {
		Media_Document::$instance = new Media_Document(); 
	}
	return Media_Document::$instance;
}

/**
 * Adds document to the document table. Takes a field=>value array with
 * document data as first argument. Returns insert id. 
 * 
 * @throws Media_DocumentException
 * @param array Row data
 * @return int Document id
 */
public function addDocument ($sqlData)
{
	if (!is_array($sqlData)) {
		throw new Media_DocumentException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	return $this->base->db->insert(OAK_DB_MEDIA_DOCUMENTS, $sqlData);
}

/**
 * Updates document. Takes the document id as first argument, a
 * field=>value array with the new document data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Media_DocumentException
 * @param int Document id
 * @param array Row data
 * @return int Affected rows
*/
public function updateDocument ($id, $sqlData)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_DocumentException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Media_DocumentException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(OAK_DB_MEDIA_DOCUMENTS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes document from the document table. Takes the document id
 * as first argument. Returns amount of affected rows.
 * 
 * @throws Media_DocumentException
 * @param int Document id
 * @return int Amount of affected rows
 */
public function deleteDocument ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_DocumentException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_MEDIA_DOCUMENTS, $where, $bind_params);
}

/**
 * Selects one document. Takes the document id as first argument.
 * Returns array with document information.
 * 
 * @throws Media_DocumentException
 * @param int Document id
 * @return array
 */
public function selectDocument ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_DocumentException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`media_documents`.`id` AS `id`,
			`media_documents`.`name` AS `name`,
			`media_documents`.`name_on_disk` AS `name_on_disk`,
			`media_documents`.`size` AS `size`,
			`media_documents`.`date_modified` AS `date_modified`,
			`media_documents`.`date_added` AS `date_added`
		FROM
			".OAK_DB_MEDIA_DOCUMENTS." AS `media_documents`
		WHERE 
			1
	";
	
	// prepare where clauses
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `media_documents`.`id` = :id ";
		$bind_params['id'] = (int)$id;
	}
	
	// add limits
	$sql .= ' LIMIT 1';
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more documents. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
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
 * </ul>
 * 
 * @throws Media_DocumentException
 * @param array Select params
 * @return array
 */
public function selectDocuments ($params = array())
{
	// define some vars
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Media_DocumentException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'order_macro':
					$$_key = (int)$_value;
				break;
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Media_DocumentException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'DATE_ADDED' => '`media_documents`.`date_added`',
		'DATE_MODIFIED' => '`media_documents`.`date_modified`'
	);
	
	// prepare query
	$sql = "
		SELECT 
			`media_documents`.`id` AS `id`,
			`media_documents`.`name` AS `name`,
			`media_documents`.`name_on_disk` AS `name_on_disk`,
			`media_documents`.`size` AS `size`,
			`media_documents`.`date_modified` AS `date_modified`,
			`media_documents`.`date_added` AS `date_added`
		FROM
			".OAK_DB_MEDIA_DOCUMENTS." AS `media_documents`
		WHERE 
			1
	";
	
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
 * Counts documents saved in the document table. Returns int.
 * 
 * @return int
 */
public function countDocuments ()
{
	// define some vars
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_MEDIA_DOCUMENTS." AS `media_documents`
		WHERE 
			1
	";
	
	return $this->base->db->select($sql, 'field', $bind_params);
}

// end of class
}

class Media_DocumentException extends Exception { }

?>