<?php

/**
 * Project: Oak
 * File: imagethumbnail.class.php
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
 * $Id: imagethumbnail.class.php 2 2006-03-20 11:43:20Z andreas $
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/apache2.0.php Apache License, Version 2.0
 */

class Media_Imagethumbnail {
	
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
	if (Media_Imagethumbnail::$instance == null) {
		Media_Imagethumbnail::$instance = new Media_Imagethumbnail(); 
	}
	return Media_Imagethumbnail::$instance;
}

/**
 * Adds thumbnail to the image thumbnail table. Takes a field=>value array
 * with thumbnail data as first argument. Returns insert id. 
 * 
 * @throws Media_ImagethumbnailException
 * @param array Row data
 * @return int Insert id
 */
public function addImageThumbnail ($sqlData)
{
	if (!is_array($sqlData)) {
		throw new Media_ImagethumbnailException('Input for parameter sqlData is not an array');	
	}
	
	// insert row
	return $this->base->db->insert(OAK_DB_MEDIA_IMAGE_THUMBNAILS, $sqlData);
}

/**
 * Updates image thumbnail. Takes the image thumbnail id as first argument,
 * a field=>value array with the new thumbnail data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Media_ImagethumbnailException
 * @param int Thumbnail id
 * @param array Row data
 * @return int Affected rows
*/
public function updateImageThumbnail ($id, $sqlData)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_ImagethumbnailException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Media_ImagethumbnailException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// update row
	return $this->base->db->update(OAK_DB_MEDIA_IMAGE_THUMBNAILS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes thumbnail from the image thumbnail table. Takes the thumbnail
 * id as first argument. Returns amount of affected rows.
 * 
 * @throws Media_ImagethumbnailException
 * @param int Thumbnail id
 * @return int Amount of affected rows
 */
public function deleteImageThumbnail ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_ImagethumbnailException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_MEDIA_IMAGE_THUMBNAILS,
		$where, $bind_params);
}

/**
 * Selects one image thumbnail. Takes the thumbnail id as first
 * argument. Returns array with thumbnail information.
 * 
 * @throws Media_ImagethumbnailException
 * @param int Thumbnail id
 * @return array
 */
public function selectImageThumbnail ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_ImagethumbnailException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`media_image_thumbnails`.`id` AS `id`,
			`media_image_thumbnails`.`image` AS `image`,
			`media_image_thumbnails`.`name` AS `name`,
			`media_image_thumbnails`.`name_on_disk` AS `name_on_disk`,
			`media_image_thumbnails`.`width` AS `width`,
			`media_image_thumbnails`.`height` AS `height`,
			`media_image_thumbnails`.`size` AS `size`,
			`media_image_thumbnails`.`date_modified` AS `date_modified`,
			`media_image_thumbnails`.`date_added` AS `date_added,`
			`media_images`.`id` AS `image_id`,
			`media_images`.`name` AS `image_name`,
			`media_images`.`name_on_disk` AS `image_name_on_disk`,
			`media_images`.`width` AS `image_width`,
			`media_images`.`height` AS `image_height`,
			`media_images`.`size` AS `image_size`,
			`media_images`.`date_modified` AS `image_date_modified`,
			`media_images`.`date_added` AS `image_date_added`
		FROM
			".OAK_DB_MEDIA_IMAGE_THUMBNAILS." AS `media_image_thumbnails`
		LEFT JOIN
			".OAK_DB_MEDIA_IMAGES." AS `media_images`
		  ON
			`media_image_thumbnails`.`image` = `media_images`.`id`
		WHERE 
			1
	";
	
	// prepare where clauses
	if (!empty($id) && is_numeric($id)) {
		$sql .= " AND `media_image_thumbnails`.`id` = :id ";
		$bind_params['id'] = (int)$id;
	}
	
	// add limits
	$sql .= ' LIMIT 1';
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Method to select one or more image thumbnails. Takes key=>value
 * array with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>image, int, optional: Image id</li>
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
 * @throws Media_ImagethumbnailException
 * @param array Select params
 * @return array
 */
public function selectImageThumbnails ($params = array())
{
	// define some vars
	$image = null;
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Media_ImagethumbnailException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'order_macro':
					$$_key = (int)$_value;
				break;
			case 'image':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			default:
				throw new Media_ImagethumbnailException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'DATE_ADDED' => '`media_image_thumbnails`.`date_added`',
		'DATE_MODIFIED' => '`media_image_thumbnails`.`date_modified`'
	);
	
	// prepare query
	$sql = "
		SELECT 
			`media_image_thumbnails`.`id` AS `id`,
			`media_image_thumbnails`.`image` AS `image`,
			`media_image_thumbnails`.`name` AS `name`,
			`media_image_thumbnails`.`name_on_disk` AS `name_on_disk`,
			`media_image_thumbnails`.`width` AS `width`,
			`media_image_thumbnails`.`height` AS `height`,
			`media_image_thumbnails`.`size` AS `size`,
			`media_image_thumbnails`.`date_modified` AS `date_modified`,
			`media_image_thumbnails`.`date_added` AS `date_added,`
			`media_images`.`id` AS `image_id`,
			`media_images`.`name` AS `image_name`,
			`media_images`.`name_on_disk` AS `image_name_on_disk`,
			`media_images`.`width` AS `image_width`,
			`media_images`.`height` AS `image_height`,
			`media_images`.`size` AS `image_size`,
			`media_images`.`date_modified` AS `image_date_modified`,
			`media_images`.`date_added` AS `image_date_added`
		FROM
			".OAK_DB_MEDIA_IMAGE_THUMBNAILS." AS `media_image_thumbnails`
		LEFT JOIN
			".OAK_DB_MEDIA_IMAGES." AS `media_images`
		  ON
			`media_image_thumbnails`.`image` = `media_images`.`id`
		WHERE 
			1
	";
	
	// add where clauses
	if (!empty($image) && is_numeric($image)) {
		$sql .= " AND `media_image_thumbnails`.`image` = :image ";
		$bind_params['image'] = $image;
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
 * Counts image thumbnails saved in the thumbnail table. Takes field=>value
 * array with select params as first argument. Returns int.
 *
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>image, int, optional: Image id</li>
 * </ul>
 * </ul> 
 * 
 * @throws Media_ImagethumbnailException
 * @param array Select params
 * @return int
 */
public function countImageThumbnails ($params = array())
{
	// define some vars
	$image = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Media_ImagethumbnailException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'image':
					$$_key = (int)$_value;
				break;
			default:
				throw new Media_ImagethumbnailException("Unknown parameter $_key");
		}
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_MEDIA_IMAGE_THUMBNAILS." AS `media_image_thumbnails`
		WHERE 
			1
	";
	
	if (!empty($image) && is_numeric($image)) {
		$sql .= " AND `media_image_thumbnails` = :image ";
		$bind_params['image'] = $image;
	}
	
	return $this->base->db->select($sql, 'field', $bind_params);
}

// end of class
}

class Media_ImagethumbnailException extends Exception { }

?>