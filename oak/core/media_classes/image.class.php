<?php

/**
 * Project: Oak
 * File: image.class.php
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

class Media_Image {
	
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
 * Singleton. Returns instance of the Media_Image object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Media_Image::$instance == null) {
		Media_Image::$instance = new Media_Image(); 
	}
	return Media_Image::$instance;
}

/**
 * Adds image to the image table. Takes a field=>value array with
 * image data as first argument. Returns insert id. 
 * 
 * @throws Media_ImageException
 * @param array Row data
 * @return int Image id
 */
public function addImage ($sqlData)
{
	if (!is_array($sqlData)) {
		throw new Media_ImageException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	return $this->base->db->insert(OAK_DB_MEDIA_IMAGES, $sqlData);
}

/**
 * Updates image. Takes the image id as first argument, a
 * field=>value array with the new image data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Media_ImageException
 * @param int Image id
 * @param array Row data
 * @return int Affected rows
*/
public function updateImage ($id, $sqlData)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_ImageException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Media_ImageException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(OAK_DB_MEDIA_IMAGES, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes image from the image table. Takes the image id
 * as first argument. Returns amount of affected rows.
 * 
 * @throws Media_ImageException
 * @param int Image id
 * @return int Amount of affected rows
 */
public function deleteImage ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_ImageException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_MEDIA_IMAGES, $where, $bind_params);
}

/**
 * Selects one image. Takes the image id as first argument.
 * Returns array with image information.
 * 
 * @throws Media_ImageException
 * @param int Image id
 * @return array
 */
public function selectImage ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_ImageException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`media_images`.`id` AS `id`,
			`media_images`.`name` AS `name`,
			`media_images`.`name_on_disk` AS `name_on_disk`,
			`media_images`.`width` AS `width`,
			`media_images`.`height` AS `height`,
			`media_images`.`size` AS `size`,
			`media_images`.`date_modified` AS `date_modified`,
			`media_images`.`date_added` AS `date_added`,
			`media_image_thumbnails`.`id` AS `thumbnail_id`,
			`media_image_thumbnails`.`name` AS `thumbnail_name`,
			`media_image_thumbnails`.`name_on_disk` AS `thumbnail_name_on_disk`,
			`media_image_thumbnails`.`width` AS `thumbnail_width`,
			`media_image_thumbnails`.`height` AS `thumbnail_height`,
			`media_image_thumbnails`.`size` AS `thumbnail_size`,
			`media_image_thumbnails`.`date_modified` AS `thumbnail_date_modified`,
			`media_image_thumbnails`.`date_added` AS `thumbnail_date_added`
		FROM
			".OAK_DB_MEDIA_IMAGES." AS `media_images`
		LEFT JOIN
			".OAK_DB_MEDIA_THUMBNAILS." AS `media_image_thumbnails`
		  ON
			`media_images`.`id` = `media_image_thumbnails`.`image`
		WHERE 
			1
	";
	
	// prepare where clauses
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `media_images`.`id` = :id ";
		$bind_params['id'] = (int)$id;
	}
	
	// add limits
	$sql .= ' LIMIT 1';
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more images. Takes key=>value array
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
 * @throws Media_ImageException
 * @param array Select params
 * @return array
 */
public function selectImages ($params = array())
{
	// define some vars
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Media_ImageException('Input for parameter params is not an array');	
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
				throw new Media_ImageException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'DATE_ADDED' => '`media_images`.`date_added`',
		'DATE_MODIFIED' => '`media_images`.`date_modified`'
	);
	
	// prepare query
	$sql = "
		SELECT 
			`media_images`.`id` AS `id`,
			`media_images`.`name` AS `name`,
			`media_images`.`name_on_disk` AS `name_on_disk`,
			`media_images`.`width` AS `width`,
			`media_images`.`height` AS `height`,
			`media_images`.`size` AS `size`,
			`media_images`.`date_modified` AS `date_modified`,
			`media_images`.`date_added` AS `date_added`,
			`media_image_thumbnails`.`id` AS `thumbnail_id`,
			`media_image_thumbnails`.`name` AS `thumbnail_name`,
			`media_image_thumbnails`.`name_on_disk` AS `thumbnail_name_on_disk`,
			`media_image_thumbnails`.`width` AS `thumbnail_width`,
			`media_image_thumbnails`.`height` AS `thumbnail_height`,
			`media_image_thumbnails`.`size` AS `thumbnail_size`,
			`media_image_thumbnails`.`date_modified` AS `thumbnail_date_modified`,
			`media_image_thumbnails`.`date_added` AS `thumbnail_date_added`
		FROM
			".OAK_DB_MEDIA_IMAGES." AS `media_images`
		LEFT JOIN
			".OAK_DB_MEDIA_THUMBNAILS." AS `media_image_thumbnails`
		  ON
			`media_images`.`id` = `media_image_thumbnails`.`image`
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
 * Counts images saved in the image table. Returns int.
 * 
 * @return int
 */
public function countImages ()
{
	// define some vars
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_MEDIA_IMAGES." AS `media_images`
		WHERE 
			1
	";
	
	return $this->base->db->select($sql, 'field', $bind_params);
}

// end of class
}

class Media_ImageException extends Exception { }

?>