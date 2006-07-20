<?php

/**
 * Project: Oak
 * File: simpleform.class.php
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

class Content_Simpleform {
	
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
 * Singleton. Returns instance of the Content_Simpleform object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Content_Simpleform::$instance == null) {
		Content_Simpleform::$instance = new Content_Simpleform(); 
	}
	return Content_Simpleform::$instance;
}

/**
 * Adds simple form to the simple form table. Takes a field=>value
 * array with simple form data as first argument. Returns insert id. 
 * 
 * @throws Content_SimpleformException
 * @param array Row data
 * @return int Simple form
 */
public function addSimpleForm ($sqlData)
{
	if (!is_array($sqlData)) {
		throw new Content_SimpleformException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	return $this->base->db->insert(OAK_DB_CONTENT_SIMPLE_FORMS, $sqlData);
}

/**
 * Updates simple form. Takes the simple form id as first argument, a
 * field=>value array with the new simple form data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Content_SimpleformException
 * @param int Simple form id
 * @param array Row data
 * @return int Affected rows
*/
public function updateSimpleForm ($id, $sqlData)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimpleformException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Content_SimpleformException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(OAK_DB_CONTENT_SIMPLE_FORMS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes simple form from the simple forms table. Takes the
 * simple form id as first argument. Returns amount of affected
 * rows.
 * 
 * @throws Content_SimpleformException
 * @param int Simple form id
 * @return int Amount of affected rows
 */
public function deleteSimpleForm ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimpleformException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_CONTENT_SIMPLE_FORMS, $where, $bind_params);
}

/**
 * Selects one simple form. Takes the simple form id as first
 * argument. Returns array with simple form information.
 * 
 * @throws Content_SimpleformException
 * @param int Simple form id
 * @return array
 */
public function selectSimpleForm ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_SimpleformException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`content_simple_forms`.`id` AS `id`,
			`content_simple_forms`.`user` AS `user`,
			`content_simple_forms`.`title` AS `title`,
			`content_simple_forms`.`title_url` AS `title_url`,
			`content_simple_forms`.`content_raw` AS `content_raw`,
			`content_simple_forms`.`content` AS `content`,
			`content_simple_forms`.`text_converter` AS `text_converter`,
			`content_simple_forms`.`apply_macros` AS `apply_macros`,
			`content_simple_forms`.`type` AS `type`,
			`content_simple_forms`.`email_from` AS `email_from`,
			`content_simple_forms`.`email_to` AS `email_to`,
			`content_simple_forms`.`email_subject` AS `email_subject`,
			`content_simple_forms`.`date_modified` AS `date_modified`,
			`content_simple_forms`.`date_added` AS `date_added`,
			`content_nodes`.`id` AS `node_id`,
			`content_nodes`.`navigation` AS `node_navigation`,
			`content_nodes`.`root_node` AS `node_root_node`,
			`content_nodes`.`parent` AS `node_parent`,
			`content_nodes`.`lft` AS `node_lft`,
			`content_nodes`.`rgt` AS `node_rgt`,
			`content_nodes`.`level` AS `node_level`,
			`content_nodes`.`sorting` AS `node_sorting`,
			`content_pages`.`id` AS `form_id`,
			`content_pages`.`project` AS `form_project`,
			`content_pages`.`type` AS `form_type`,
			`content_pages`.`template_set` AS `form_template_set`,
			`content_pages`.`name` AS `form_name`,
			`content_pages`.`name_url` AS `form_name_url`,
			`content_pages`.`url` AS `form_url`,
			`content_pages`.`protect` AS `form_protect`,
			`content_pages`.`index_page` AS `form_index_page`,
			`content_pages`.`image_small` AS `form_image_small`,
			`content_pages`.`image_medium` AS `form_image_medium`,
			`content_pages`.`image_big` AS `form_image_big`
		FROM
			".OAK_DB_CONTENT_SIMPLE_FORMS." AS `content_simple_forms`
		JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		ON
			`content_simple_forms`.`id` = `content_pages`.`id`
		JOIN
			".OAK_DB_CONTENT_NODES." AS `content_nodes`
		ON
			`content_pages`.`id` = `content_nodes`.`id`
		WHERE
			`content_simple_forms`.`id` = :id
		AND
			`content_pages`.`project` = :current_project
		AND
			`content_simple_forms`.`user` = :current_user
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'current_project' => (int)OAK_CURRENT_PROJECT,
		'current_user' => (int)OAK_CURRENT_USER
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more simple forms. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>user, int, optional: User/author id</li>
 * <li>form, int, optional: Form id</li>
 * <li>start, int, optional: row offset</li>
 * <li>limit, int, optional: amount of rows to return</li>
 * <li>order_marco, string, otpional: How to sort the result set.
 * Supported macros:
 *    <ul>
 *        <li>USER_NAME: sort by user name</li>
 *        <li>FORM: sorty by form id</li>
 *        <li>DATE_MODIFIED: sorty by date modified</li>
 *        <li>DATE_ADDED: sort by date added</li>
 *    </ul>
 * </li>
 * </ul>
 * 
 * @throws Content_SimpleformException
 * @param array Select params
 * @return array
 */
public function selectSimpleForms ($params = array())
{
	// define some vars
	$user = null;
	$form = null;
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Content_SimpleformException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'order_marco':
					$$_key = (string)$_value;
				break;
			case 'user':
			case 'form':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Content_SimpleformException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'USER_NAME' => '`application_users`.`name`',
		'FORM' => '`content_simple_forms`.`form`',
		'DATE_ADDED' => '`content_simple_forms`.`date_added` AS `date_added`',
		'DATE_MODIFIED' => '`content_simple_forms`.`date_modified`'
	);
	
	// prepare query
	$sql = "
		SELECT 
			`content_simple_forms`.`id` AS `id`,
			`content_simple_forms`.`user` AS `user`,
			`content_simple_forms`.`title` AS `title`,
			`content_simple_forms`.`title_url` AS `title_url`,
			`content_simple_forms`.`content_raw` AS `content_raw`,
			`content_simple_forms`.`content` AS `content`,
			`content_simple_forms`.`text_converter` AS `text_converter`,
			`content_simple_forms`.`apply_macros` AS `apply_macros`,
			`content_simple_forms`.`type` AS `type`,
			`content_simple_forms`.`email_from` AS `email_from`,
			`content_simple_forms`.`email_to` AS `email_to`,
			`content_simple_forms`.`email_subject` AS `email_subject`,
			`content_simple_forms`.`date_modified` AS `date_modified`,
			`content_simple_forms`.`date_added` AS `date_added`,
			`application_users`.`id` AS `user_id`,
			`application_users`.`group` AS `user_group`,
			`application_users`.`name` AS `user_name`,
			`application_users`.`email` AS `user_email`,
			`application_users`.`homeform` AS `user_homeform`,
			`application_users`.`pwd` AS `user_pwd`,
			`application_users`.`public_email` AS `user_public_email`,
			`application_users`.`public_profile` AS `user_public_profile`,
			`application_users`.`author` AS `user_author`,
			`application_users`.`date_modified` AS `user_date_modified`,
			`application_users`.`date_added` AS `user_date_added`,
			`content_pages`.`id` AS `form_id`,
			`content_pages`.`navigation` AS `form_navigation`,
			`content_pages`.`root_node` AS `form_root_node`,
			`content_pages`.`parent` AS `form_parent`,
			`content_pages`.`level` AS `form_level`,
			`content_pages`.`sorting` AS `form_sorting`,
			`content_pages`.`type` AS `form_type`,
			`content_pages`.`template_set` AS `form_template_set`,
			`content_pages`.`name` AS `form_name`,
			`content_pages`.`name_url` AS `form_name_url`,
			`content_pages`.`protect` AS `form_protect`
		FROM
			".OAK_DB_CONTENT_SIMPLE_FORMS." AS `content_simple_forms`
		JOIN
			".OAK_DB_USER_USERS." AS `application_users`
		ON
			`content_simple_forms`.`user` = `application_users`.`id`
		JOIN
			".OAK_DB_CONTENT_PAGES." AS `content_pages`
		ON
			`content_simple_forms`.`form` = `content_pages`.`id`
		WHERE
			1
	";
	
	// add where clauses
	if (!empty($user) && is_numeric($user)) {
		$sql .= " AND `application_users`.`id` = :user ";
		$bind_params['user'] = $user;
	}
	if (!empty($form) && is_numeric($form)) {
		$sql .= " AND `content_pages`.`id` = :form ";
		$bind_params['form'] = $form;
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

// end of class
}

class Content_SimpleformException extends Exception { }

?>