<?php

/**
 * Project: Oak
 * File: object.class.php
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

class Media_Object {
	
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
 * Singleton. Returns instance of the Media_Object object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Media_Object::$instance == null) {
		Media_Object::$instance = new Media_Object(); 
	}
	return Media_Object::$instance;
}

/**
 * Adds object to the object table. Takes a field=>value array with
 * object data as first argument. Returns insert id. 
 * 
 * @throws Media_ObjectException
 * @param array Row data
 * @return int Object id
 */
public function addObject ($sqlData)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Manage')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($sqlData)) {
		throw new Media_ObjectException('Input for parameter sqlData is not an array');	
	}
	
	// make sure that the new object will be assigned to the current project
	$sqlData['project'] = OAK_CURRENT_PROJECT;
	
	// insert row
	return $this->base->db->insert(OAK_DB_MEDIA_OBJECTS, $sqlData);
}

/**
 * Updates object. Takes the object id as first argument, a
 * field=>value array with the new object data as second argument.
 * Returns amount of affected rows.
 *
 * @throws Media_ObjectException
 * @param int Object id
 * @param array Row data
 * @return int Affected rows
*/
public function updateObject ($id, $sqlData)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Manage')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_ObjectException('Input for parameter id is not an array');
	}
	if (!is_array($sqlData)) {
		throw new Media_ObjectException('Input for parameter sqlData is not an array');	
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// update row
	return $this->base->db->update(OAK_DB_MEDIA_OBJECTS, $sqlData,
		$where, $bind_params);	
}

/**
 * Removes object from the object table. Takes the object id
 * as first argument. Returns amount of affected rows.
 * 
 * @throws Media_ObjectException
 * @param int Object id
 * @return int Amount of affected rows
 */
public function deleteObject ($id)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Manage')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_ObjectException('Input for parameter id is not numeric');
	}
	
	// prepare where clause
	$where = " WHERE `id` = :id AND `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'id' => (int)$id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query
	return $this->base->db->delete(OAK_DB_MEDIA_OBJECTS, $where, $bind_params);
}

/**
 * Selects one object. Takes the object id as first argument.
 * Returns array with object information.
 * 
 * @throws Media_ObjectException
 * @param int Object id
 * @return array
 */
public function selectObject ($id)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_ObjectException('Input for parameter id is not numeric');
	}
	
	// initialize bind params
	$bind_params = array();
	
	// prepare query
	$sql = "
		SELECT 
			`media_objects`.`id` AS `id`,
			`media_objects`.`project` AS `project`,
			`media_objects`.`description` AS `description`,
			`media_objects`.`tags` AS `tags`,
			`media_objects`.`file_name` AS `file_name`,
			`media_objects`.`file_name_on_disk` AS `file_name_on_disk`,
			`media_objects`.`file_mime_type` AS `file_mime_type`,
			`media_objects`.`file_width` AS `file_width`,
			`media_objects`.`file_height` AS `file_height`,
			`media_objects`.`file_size` AS `file_size`,
			`media_objects`.`preview_name_on_disk` AS `preview_name_on_disk`,
			`media_objects`.`preview_mime_type` AS `preview_mime_type`,
			`media_objects`.`preview_width` AS `preview_width`,
			`media_objects`.`preview_height` AS `preview_height`,
			`media_objects`.`preview_size` AS `preview_size`,
			`media_objects`.`date_modified` AS `date_modified`,
			`media_objects`.`date_added` AS `date_added`
		FROM
			".OAK_DB_MEDIA_OBJECTS." AS `media_objects`
		WHERE
			`media_objects`.`id` = :id
		AND
			`media_objects`.`project` = :project
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
 * Method to select one or more objects. Takes key=>value array
 * with select params as first argument. Returns array.
 * 
 * <b>List of supported params:</b>
 * 
 * <ul>
 * <li>types, array, optional: Return only objects that belong to the given
 * generic types</li>
 * <li>tags, array, optional: Returns objects with given tags</li>
 * <li>timeframe, string, optional: Returns objects from given timeframe</li>
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
 * @throws Media_ObjectException
 * @param array Select params
 * @return array
 */
public function selectObjects ($params = array())
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// define some vars
	$timeframe = null;
	$tags = null;
	$order_macro = null;
	$types = array();
	$start = null;
	$limit = null;
	$bind_params = array();
	
	// input check
	if (!is_array($params)) {
		throw new Media_ObjectException('Input for parameter params is not an array');	
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'tags':
			case 'order_macro':
			case 'timeframe':
					$$_key = (string)$_value;
				break;
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			case 'types':
					$$_key = (array)$_value;
				break;
			default:
				throw new Media_ObjectException("Unknown parameter $_key");
		}
	}
	
	// define order macros
	$macros = array(
		'NAME' => '`media_objects`.`name`',
		'DATE_ADDED' => '`media_objects`.`date_added`',
		'DATE_MODIFIED' => '`media_objects`.`date_modified`'
	);
	
	// load Utility_Helper
	$HELPER = load('Utility:Helper');
	
	// load Media_Tag
	$TAG = load('Media:Tag');
	
	// prepare query
	$sql = "
		SELECT
			`media_objects`.`id` AS `id`,
			`media_objects`.`project` AS `project`,
			`media_objects`.`description` AS `description`,
			`media_objects`.`tags` AS `tags`,
			`media_objects`.`file_name` AS `file_name`,
			`media_objects`.`file_name_on_disk` AS `file_name_on_disk`,
			`media_objects`.`file_mime_type` AS `file_mime_type`,
			`media_objects`.`file_width` AS `file_width`,
			`media_objects`.`file_height` AS `file_height`,
			`media_objects`.`file_size` AS `file_size`,
			`media_objects`.`preview_name_on_disk` AS `preview_name_on_disk`,
			`media_objects`.`preview_width` AS `preview_width`,
			`media_objects`.`preview_height` AS `preview_height`,
			`media_objects`.`preview_size` AS `preview_size`,
			`media_objects`.`date_modified` AS `date_modified`,
			`media_objects`.`date_added` AS `date_added`,
			`media_tags`.`id` AS `tag_id`,
			`media_tags`.`word` AS `tag_word`,
			`media_tags`.`occurrences` AS `occurrences`
		FROM
			".OAK_DB_MEDIA_OBJECTS." AS `media_objects`
		LEFT JOIN
			".OAK_DB_MEDIA_OBJECTS2MEDIA_TAGS." AS `media_objects2media_tags`
		  ON
			`media_objects`.`id` = `media_objects2media_tags`.`object`
		LEFT JOIN
			".OAK_DB_MEDIA_TAGS." AS `media_tags`
		  ON
			`media_objects2media_tags`.`tag` = `media_tags`.`id`
		WHERE
			`media_objects`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($timeframe)) {
		$sql .= " AND ".$HELPER->_sqlForTimeFrame('`media_objects`.`date_added`',
			$timeframe);
	}
	if (!empty($tags)) {
		$sql .= " AND ".$HELPER->_sqlLikeFromArray('`media_tags`.`word`',
			$TAG->_prepareTagStringForQuery($tags));
	}
	if (!empty($types) && is_array($types)) {
		$sql .= " AND ".$this->sqlForGenericTypes('`media_objects`.`file_mime_type`', $types);
	}
	
	// aggregate result set
	$sql .= " GROUP BY `media_objects`.`id` ";
	
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
 * Counts objects saved in the object table. Returns int.
 * 
 * @return int
 */
public function countObjects ()
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// prepare query
	$sql = "
		SELECT 
			COUNT(*) AS `total`
		FROM
			".OAK_DB_MEDIA_OBJECTS." AS `media_objects`
		WHERE 
			`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	return $this->base->db->select($sql, 'field', $bind_params);
}

/**
 * Moves object to store. Takes the real name (~ file name on user's disk)
 * as first argument, the path to the uploaded file as second argument. Returns
 * the new name on disk (uniqid + real name).
 *
 * @throws Media_ObjectException
 * @param string File name
 * @param string Path to uploaded file
 * @return string File name on disk
 */
public function moveObjectToStore ($name, $path)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Manage')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($name) || !is_scalar($name)) {
		throw new Media_ObjectException("Input for parameter name is expected to be a non-empty scalar value");
	}
	if (empty($path) || !is_scalar($path)) {
		throw new Media_ObjectException("Input for parameter path is expected to be a non-empty scalar value");
	}
	if (!$this->imageStoreIsReady()) {
		throw new Media_ObjectException("Image store is not ready");
	}
	
	// get unique id
	$uniqid = Base_Cnc::uniqueId();
	
	// prepare file name
	$file_name = $uniqid.'_'.$name;
	
	// prepare target path
	$target_path = $this->getPathToObject($file_name);
	
	// move file
	move_uploaded_file($path, $target_path);
	
	// return file name
	return $file_name;
}

/**
 * Removes object form store. Takes the id of the object as
 * first argument. Returns bool.
 *
 * @throws Media_ObjectException
 * @param int Global file id
 * @return bool
 */
public function removeObjectFromStore ($object)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Manage')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($object) || !is_numeric($object)) {
		throw new Media_ObjectException("Input for parameter object is expected to be numeric");
	}
	if (!$this->imageStoreIsReady()) {
		throw new Media_ObjectException("Image store is not ready");
	}
	
	// get object
	$file = $this->selectObject($object);
	
	// if the object is empty, we can skip here
	if (empty($file)) {
		return false;
	}
	
	// prepare path to file on disk
	$path = $this->getPathToObject($file['file_name_on_disk']);
	
	// unlink object
	if (file_exists($path)) {
		if (unlink($path)) {
			// update object in database
			$sqlData = array(
				'file_name' => null,
				'file_name_on_disk' => null,
				'file_mime_type' => null,
				'file_width' => null,
				'file_height' => null,
				'file_size' => null
			);
			$this->updateObject($file['id'], $sqlData);
			
			return true;
		}
	}
	
	return false;
}

/**
 * Creates thumbnail of given image. Takes the original name (as saved on
 * user's disk) of the object as first argument and the filename of the object
 * on the server as second argument. The arguments three and four define the
 * maximum width and height of the thumbnail. The image will be scaled keeping
 * the aspect ratio of the original image.
 * 
 * If the image is smaller than $width or $height and you don't like that, the
 * image will be placed on the middle of an empty canvas with the size of
 * $width x $height if you pass boolean true as fifth argument. The color of the
 * canvas can be defined using a hexadecimal color code as sixth argument
 * (e.g. ffffff for white or ff0000 for red).
 *
 * Returns array with complete array information:
 * 
 * <ul>
 * <li>name: Name of the thumbnail on the server's disk</li>
 * <li>width: Width of the thumbnail</li>
 * <li>height: Height of the thumbnail</li>
 * <li>type: MIME type the thumbnail</li>
 * <li>size: Filesize of the thumbnail in bytes</li>
 * </ul> 
 *
 * @throws Media_ObjectException
 * @param string Original image name
 * @param string Image name on server
 * @param int Maximal thumbnail width
 * @param int Maximal thumbnail height
 * @param bool Fill the image up
 * @param bool Canvas color
 * @return array
 */
public function createImageThumbnail ($orig_name, $object_name, $width, $height, $fill_up = false, $hex = null)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Manage')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($orig_name) || !is_scalar($orig_name)) {
		throw new Media_ObjectException("orig_name is supposed to be a non-empty scalar value");
	}
	if (empty($object_name) || !is_scalar($object_name)) {
		throw new Media_ObjectException("object_name is supposed to be a non-empty scalar value");
	}
	if (empty($width) || !is_numeric($width)) {
		throw new Media_ObjectException("width is supposed to be a non-empty numeric value");
	}
	if (empty($height) || !is_numeric($height)) {
		throw new Media_ObjectException("height is supposed to be a non-empty numeric value");	
	}
	if (!is_bool($fill_up)) {
		throw new Media_ObjectException("fill_up is supposed to be a boolean value");
	}
	if ($fill_up === true && empty($hex)) {
		throw new Media_ObjectException("to fill up an image a canvas color is required");
	}
	if (!is_null($hex) && !Base_Cnc::filterRequest($hex, OAK_REGEX_HEX)) {
		throw new Media_ObjectException("hex is supposed to be empty or a hexadecimal value");
	}
	if (!$this->imageStoreIsReady()) {
		throw new Media_ObjectException("Image store is not ready");
	}
	
	$path = $this->getPathToObject($object_name);
	
	// get image size
	list($width_orig, $height_orig, $type) = @getimagesize($path);
	
	// let's look at the type. if it's 1, 2 or three, go ahead. if not, we kan skip here.
	if ($type != 1 && $type != 2 && $type != 3) {
		return false;
	}
	
	// create new image size
	$width_resized = $width;
	$height_resized = $height;
	$ratio_orig = $width_orig / $height_orig;
	if ($width / $height > $ratio_orig) {
		$width_resized = $height * $ratio_orig;
	} else {
		$height_resized = $width / $ratio_orig;
	}
	
	// create new canvas
	$image_p = imagecreatetruecolor($width_resized, $height_resized);
	
	// import iamge
	switch ($type) {
		case 1:
				$image = imagecreatefromgif($path);
			break;
		case 2:
				$image = imagecreatefromjpeg($path);
			break;
		case 3:
				$image = imagecreatefrompng($path);
			break;
	}
	
	// resize image
	imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width_resized, $height_resized,
		$width_orig, $height_orig);
	
	// fill the image up to the maximum size if desired
	if ($fill_up === true) {
		// create canvas with the maximum size
		$filled_image = imagecreatetruecolor($width, $height);
		
		// calculate the position where the resized image should be placed on the canvas
		$dest_x = ($width_resized / 2) - ($width / 2);
		$dest_y = ($height_resized / 2) - ($height / 2);
		
		// place the resized image on the created canvas
		imagecopy($filled_image, $image_p, 0, 0, $dest_x, $dest_y, $width, $height);
		
		// allocate the color to fill the canvas
		$color = imagecolorallocate($filled_image, hexdec(substr($hex, 0 ,2)),
			hexdec(substr($hex, 2 ,2)), hexdec(substr($hex, 4 ,2)));
		
		// fill the canvas with the background color
		imagefill($filled_image, 0, 0, $color);
		
		// fill the canvas with the background color
		imagefill($filled_image, $width - 1, $height - 1, $color);
		
		// reassign variables
		$image_p = $filled_image;
	}
	
	// prepare save name
	$parts = explode('.', $orig_name);
	$suffix = $parts[count($parts) - 1];
	if ($suffix == 'jpg' || $suffix == 'png' || $suffix == 'gif') {
		unset($parts[count($parts) - 1]);
	}
	$parts[] = 'png';
	$save_name = sprintf("%s_%ux%u_%s", Base_Cnc::uniqueId(), imagesx($image_p),
		imagesy($image_p), implode('.', $parts));
	
	// save image as png
	imagepng($image_p, $this->getPathToThumbnail($save_name));
	
	// return thumbnail information
	return array(
		'name' => $save_name,
		'width' => imagesx($image_p),
		'height' => imagesy($image_p),
		'type' => 'image/png',
		'size' => filesize($this->getPathToThumbnail($save_name))
	);
}

/**
 * Remove thumbnail. Takes the object id as first argument.
 * Returns bool.
 *
 * @throws Media_ObjectException
 * @param int Object id
 * @return bool
 */
public function removeImageThumbnail ($object)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Manage')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($object) || !is_numeric($object)) {
		throw new Media_ObjectException("Input for parameter object is expected to be numeric");
	}
	if (!$this->imageStoreIsReady()) {
		throw new Media_ObjectException("Image store is not ready");
	}
	
	// get object
	$file = $this->selectObject($object);
	
	// if the object is empty, we can skip here
	if (empty($file)) {
		return false;
	}
	
	// if there's no thumbnail, we can skip here too
	if (empty($file['preview_name_on_disk'])) {
		return false;
	}
	
	// prepare path to file on disk
	$path = $this->getPathToThumbnail($file['preview_name_on_disk']);
	
	// unlink object
	if (file_exists($path)) {
		if (unlink($path)) {
			// update object in database
			$sqlData = array(
				'preview_name_on_disk' => null,
				'preview_mime_type' => null,
				'preview_height' => null,
				'preview_width' => null,
				'preview_size' => null
			);
			$this->updateObject($file['id'], $sqlData);
			
			return true;
		}
	}
	
	return false;
}

/**
 * Returns full path to media object. Takes media object name on disk
 * as frist argument. Please note that the object doesn't have to exist
 * to get the path to a object.
 *
 * @param string Object name
 * @return mixed
 */
public function getPathToObject ($object_name)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($object_name) || !is_scalar($object_name)) {
		throw new Media_ObjectException("Object name is supposed to be a non-empty scalar value");
	}
	
	return $this->base->_conf['image']['store_disk'].DIR_SEP.$object_name;
}

/**
 * Returns full www path to media object. Takes media object name on disk
 * as frist argument. Please note that the object doesn't have to exist
 * to get the path to a object.
 *
 * @param string Object name
 * @return mixed
 */
public function getWwwPathToObject ($object_name)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($object_name) || !is_scalar($object_name)) {
		throw new Media_ObjectException("Object name is supposed to be a non-empty scalar value");
	}
	
	return $this->base->_conf['image']['store_www'].DIR_SEP.$object_name;
}

/**
 * Takes media object id  as frist argument. Returns full www path to
 * media object.
 *
 * @param string Object id
 * @return mixed
 */
public function getWwwPathToObjectUsingId ($object_id)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($object_id) || !is_scalar($object_id)) {
		throw new Media_ObjectException("Object id is supposed to be a non-empty numeric value");
	}
	
	// get object
	$object = $this->selectObject($object_id);
	
	// if there's no file name on disk, return an empty url
	if (empty($object['file_name_on_disk'])) {
		return "";
	} else {
		return $this->base->_conf['image']['store_www'].DIR_SEP.$object['file_name_on_disk'];
	}
}

/**
 * Returns full path to media thumbnail. Takes the media object name on
 * disk as frist argument. Please note that the thumbnail doesn't have to
 * exist to get the path to the thumbnail.
 *
 * @param string Thumbnail name
 * @return mixed
 */
public function getPathToThumbnail ($object_name)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($object_name) || !is_scalar($object_name)) {
		throw new Media_ObjectException("Object name is supposed to be a non-empty scalar value");
	}
	
	return $this->base->_conf['image']['store_disk'].DIR_SEP.$object_name;
}

/**
 * Tests if the image store is ready to save some files there.
 *
 * @return bool
 */
public function imageStoreIsReady ()
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// get configured path
	$path = $this->base->_conf['image']['store_disk'];
	
	// clear stat cache
	clearstatcache();
	
	// execute some checks on the path
	if (empty($path)) {
		return false;
	}
	if (!is_dir($path)) {
		return false;
	}
	if (!is_readable($path)) {
		return false;
	}
	if (!is_writeable($path)) {
		return false;
	}
	if (!file_exists($path.DIR_SEP.'.')) {
		return false;
	}
	
	return true;
}

/**
 * Flips keys and values in type array.
 * 
 * @throws Media_ObjectException
 * @param array
 * @return array
 */
protected function _flipTypes ($types)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($types)) {
		throw new Media_ObjectException("types is supposed to be an array");
	}
	
	$tmp_types = array();
	foreach ($types as $_key => $_value) {
		if ($_value) {
			$tmp_types[] = $_key;
		}
	}
	return $tmp_types;
}

/**
 * Returns list of generic image types.
 * 
 * @throws Media_ObjectException
 * @return array
 */
public function getGenericTypes ()
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	return array(
		'image',
		'document',
		'audio',
		'video',
		'other'
	);
}

/**
 * Returns list of mime types that belong to the given generic
 * type. Valid generic types:
 *
 * <ul>
 * <li>image</li>
 * <li>document</li>
 * <li>audio</li>
 * <li>video</li>
 * <li>other</li>
 * </ul>
 *
 * @throws Media_ObjectException
 * @param string
 * @return array
 */
public function genericTypesToMimeTypes ($generic_type)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	switch ((string)$generic_type) {
		case 'image':
			return array(
				'gif' => 'image/gif',
				'jpg' => 'image/jpeg',
				'png' => 'image/png',
				'tiff' => 'image/tif'
			);
		case 'document':
			return array(
				'pdf' => 'application/pdf',
				'doc' => 'application/msword',
				'rtf' => 'application/rtf',
				'xls' => 'application/vnd.ms-excel',
				'ppt' => 'application/vnd.ms-powerpoint',
				'zip' => 'application/zip'
			);
		case 'audio':
			return array(
				'mp3' => 'audio/mpeg',
				'm4a' => 'audio/x-m4a'
			);
		case 'video':
			return array(
				'mp4' => 'video/mp4',
				'm4v' => 'video/x-m4v',
				'mov' => 'video/quicktime'
			);
		case 'other':
			return array();
		default:
			throw new Media_ObjectException("Unknown generic type supplied");
	}
}

/**
 * Tests if object with given mime type can be used for a podcast.
 * Returns bool.
 * 
 * @throws Media_ObjectException
 * @param string Object's mime type
 * @return bool
 */
public function isPodcastFormat ($mime_type)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	switch ((string)$mime_type) {
		case 'application/pdf':
		case 'audio/mpeg':
		case 'audio/x-m4a':
		case 'video/mp4':
		case 'video/quicktime':
		case 'video/x-m4v':
			return true;
		default:
			return false;
	}
}

/**
 * Generates sql fragment to select objects that belong to the
 * different generic types. Takes the name of the mime type as
 * first argument, the list of generic types that should be
 * queried as second argument. Returns string.
 *
 * @throws Media_ObjectException
 * @param string
 * @param array
 * @return string
 */
protected function sqlForGenericTypes ($field, $types)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($field) || !is_scalar($field)) {
		throw new Media_ObjectException("Input for parameter field is expected to be a non-empty scalar value");
	}
	if (!is_array($types)) {
		throw new Media_ObjectException("Input for parameter types is not an array");
	}
	
	// load helper class
	$HELPER = load('Utility:Helper');
	
	// generate list of mime types that belong to the given generic types
	$in_types = array();
	foreach ($types as $_type) {
		if ($_type != 'other') {
			$in_types = array_merge($in_types, $this->genericTypesToMimeTypes($_type));
		}
	}
	
	// generate list of mime types that match the generic "other" type
	$not_in_types = array();
	foreach ($types as $_type) {
		if ($_type == 'other') {
			foreach ($this->getGenericTypes() as $_generic_type) {
				if ($_generic_type != 'other') {
					$not_in_types = array_merge($not_in_types,
						$this->genericTypesToMimeTypes($_generic_type));
				}
			}
		}
	}
	// prepare sql fragment
	$sql = array();
	if (!empty($in_types)) {
		$sql[] = $HELPER->_sqlInFromArray($field, $in_types);
	}
	if (!empty($not_in_types)) {
		$sql[] = $HELPER->_sqlNotInFromArray($field, $not_in_types);
	}
	
	// generate and return sql fragment
	return implode(' OR ', $sql);
}

/**
 * Returns name of a fancy icon for the given mime type. Takes the
 * mime type name as first argument. If the given mime type could not
 * be found, the name of a generic icon will be returned.
 * 
 * @throws Media_ObjectExpception
 * @param string
 * @return string
 */
public function mimeTypeToIcon ($mime_type)
{
	// access check
	if (!oak_check_access('Media', 'Object', 'Use')) {
		throw new Media_ObjectException("You are not allowed to perform this action");
	}
	
	// input check
	if (empty($mime_type) || !preg_match(OAK_REGEX_MIME_TYPE, $mime_type)) {
		throw new Media_ObjectException("Invalid mime type supplied");
	}
	
	// icon list
	$icons = array(
		'application/pdf' => 'pdf.jpg',
		'application/msword' => 'doc.jpg',
		'application/rtf' => 'rtf.jpg',
		'application/vnd.ms-excel' => 'xls.jpg',
		'application/vnd.ms-powerpoint' => 'ppt.jpg',
		'application/zip' => 'zip.jpg',
		'audio/x-m4a' => 'audio.jpg',
		'audio/mpeg' => 'audio.jpg',
		'video/mp4' => 'video.jpg',
		'video/x-m4v' => 'video.jpg',
		'video/quicktime' => 'video.jpg'
	);
	
	// search icon array and return the right icon
	foreach ($icons as $_mime_type => $_icon) {
		if ($mime_type == $_mime_type) {
			return $_icon;
		}
	}
	
	// return default icon
	return 'generic.jpg';
}

// end of class
}

class Media_ObjectException extends Exception { }

?>