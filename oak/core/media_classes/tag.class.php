<?php

/**
 * Project: Oak
 * File: tag.class.php
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

class Media_Tag {
	
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
	 * Tag separator used when converting tag strings to
	 * arrays and vice versa.
	 * @param string 
     */
	public $_tag_separator = ',';

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
 * Singleton. Returns instance of the Media_Tag object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Media_Tag::$instance == null) {
		Media_Tag::$instance = new Media_Tag(); 
	}
	return Media_Tag::$instance;
}

/**
 * Adds tags of a object. Takes the object id as first argument, the tag
 * list as second argument. Returns bool.
 * 
 * @throws Media_TagException
 * @param int Object id
 * @param array Tag array
 * @return bool
 */
public function addTags ($object, $tags)
{
	// input check
	if (empty($object) || !is_numeric($object)) {
		throw new Media_TagException('Input for parameter object is not numeric');	
	}
	if (!is_array($tags)) {
		throw new Media_TagException('Input for parameter tags is not an array');
	}
	
	// loop through the tags and add them
	foreach ($tags as $_tag) {
		if (!empty($_tag)) {
			$this->addTag($object, $_tag);	
		}
	}
	
	return true;
}

/**
 * Update tags of an object. Takes the object id as first argument and
 * the new tag list as second argument. Fetches the old tag list from the
 * database and calculates the differences. Tags that are no longer
 * required will be removed, new ones will be added.
 * 
 * @throws Media_TagException
 * @param int Object id
 * @param array Tag array
 * @return bool
 */
public function updateTags ($object, $tags)
{
	// input check
	if (empty($object) || !is_numeric($object)) {
		throw new Media_TagException('Input for parameter object is not numeric');	
	}
	if (!is_array($tags)) {
		throw new Media_TagException('Input for parameter tags is not an array');
	}	
	
	// prepare old tags
	$old_tags = array();
	$params = array(
		'project' => $project,
		'object' => $object
	);
	foreach ($this->selectTags($params) as $_old_tag) {
		$old_tags[] = $_old_tag['word'];
	}
	
	// discover the tags which we have to delete
	$to_delete = array_diff($old_tags, $tags);
	
	// delete them
	foreach ($to_delete as $_tag) {
		$this->deleteTag($object, $_tag);	
	}
	
	// discover the tags which we have to add
	$to_add = array_diff($tags, $old_tags);
	
	// add them
	foreach ($to_add as $_tag) {
		$this->addTag($object, $_tag);	
	}
	
	return true;
}

/**
 * Deletes all tags of a object. Takes the object id as first argument.
 * Returns bool.
 * 
 * @throws tagException
 * @param int Object id
 * @param array Tag array
 * @return bool
 */
public function deleteTags ($object)
{
	// input check
	if (empty($object) || !is_numeric($object)) {
		throw new Media_TagException('Input for parameter object is not numeric');	
	}
	
	// get the tags and delete them
	$params = array(
		'object' => $object
	);
	foreach ($this->selectTags($params) as $_tag) {
		$this->deleteTag($object, $_tag['word']);
	}
	
	return true;
}

/**
 * Inserts a tag into the tag table. Takes the object which the tag
 * will be linked to as first argument and the tag word as third second.
 * The amount of occurrences will be set to 1. If the tag already
 * exists in the database, updateTag() will be called.
 * 
 * @throws Media_TagException
 * @param int Object id
 * @param string Tag word
 * @return int Tag id
 */
protected function addTag ($object, $tag)
{
	// input check
	if (empty($object) || !is_numeric($object)) {
		throw new Media_TagException('Input for parameter object is not numeric');	
	}
	if (empty($tag) && is_scalar($tag)) {
		throw new Media_TagException('Input for parameter tag is not scalar');	
	}
	
	// load helper class
	$HELPER = load('Utility:Helper');
	
	// check if tag already exists
	if ($this->tag_exists($tag) === false) {
		// create "first char" 
		$word = preg_replace(OAK_REGEX_TAG_FIRST_CHAR_CLEANUP, null, strtolower($tag));
		$first_char = substr($word, 0, 1);
		
		// create tag
		$data = array(
			'project' => OAK_CURRENT_PROJECT,
			'first_char' => $first_char,
			'word' => $tag,
			'word_url' => $HELPER->createMeaningfulString($tag),
			'occurrences' => 1
		);
		$tag_id = $this->base->db->insert(OAK_DB_MEDIA_TAGS, $data);
		
		// create link between object and tag
		$this->addLink($object, $tag_id);
	
		// return tag id
		return $tag_id;
	} else {
		// if the tag already exists, update it
		return $this->updateTag($object, $tag);
	}
}

/**
 * Function to increase/decrease occurrences of tag words
 * and to add/remove links between tag words. Takes the object
 * id as first argument, the tag word as second argument and the
 * instruction how to modify the occurrences as third argument.
 * modify_amount can be either <samp>increase</samp> or
 * <samp>decrease</samp>.
 * 
 * @throws Media_TagException
 * @param int Object id
 * @param string Tag word
 * @param string Modify amount
 * @return int Tag id
 */
protected function updateTag ($object, $tag, $modify_amount = 'increase')
{
	// input check
	if (empty($object) || !is_numeric($object)) {
		throw new Media_TagException('Input for parameter object is not numeric');	
	}
	if (empty($tag) && is_scalar($tag)) {
		throw new Media_TagException('Input for parameter tag is not scalar');	
	}	

	// get the tag id
	$tag_id = $this->tag_exists($tag);
	
	// throw exception if the tag wasn't found
	if ($tag_id === false) {
		throw new Media_TagException('Unable to find tag that I have to update');	
	}
	
	// update
	switch ($modify_amount) {
		case 'increase':
				// increment the occurrences
				$sql = "
					UPDATE
						".OAK_DB_MEDIA_TAGS."
					SET
						`occurrences` = `occurrences` + 1
					WHERE
						`id` = :id
					  AND
						`project` = :project
				";
				
				// pepare bind params
				$bind_params = array(
					'id' => $tag_id,
					'project' => OAK_CURRENT_PROJECT
				);
				
				// execute query				
				$this->base->db->execute($sql, $bind_params);
				
				// check if a link between tag and object already exits
				if (!$this->link_exists($object, $tag_id)) {
					// add link
					$this->addLink($object, $tag_id);
				}
			break;
		case 'decrease':
				// decrement the occurrences
				$sql = "
					UPDATE
						".OAK_DB_MEDIA_TAGS."
					SET
						`occurrences` = `occurrences` - 1
					WHERE
						`id` = :id
					  AND
						`project` = :project
				";
				
				// pepare bind params
				$bind_params = array(
					'id' => $tag_id,
					'project' => OAK_CURRENT_PROJECT
				);
				
				// execute query
				$this->base->db->execute($sql, $bind_params);
				
				// check if we've to remove the link
				if ($this->link_exists($object, $tag_id)) {
					// remove link
					$this->deleteLink($object, $tag_id);	
				}
			break;
	}
	
	// return the tag id
	return $tag_id;
}

/**
 * Removes tag. Takes the object id as first argument and the
 * tag word as second argument. First, the function will
 * decrease the occurrences (-1) and then remove every tag
 * where the amount of occurrences is 0 (null). Then it will
 * remove the link between the tag and the object.
 * 
 * @throws Media_TagException
 * @param int Object id
 * @param string Tag word
 * @return bool
 */
protected function deleteTag ($object, $tag)
{
	// input check
	if (empty($object) || !is_numeric($object)) {
		throw new Media_TagException('Input for parameter object is not numeric');	
	}
	if (empty($tag) && is_scalar($tag)) {
		throw new Media_TagException('Input for parameter tag is not scalar');	
	}
	
	// get the tag id
	$tag_id = $this->tag_exists($tag);
	
	if ($tag_id !== false) {
		// decrease occurrences if tag was found
		$this->updateTag($object, $tag, 'decrease');
		
		// run cleanup to remove tags with occurrences = 0
		$where = " WHERE `occurrences` < 1 AND `project` = :project ";
		$this->base->db->delete('media_tags', $where, array('project' => OAK_CURRENT_PROJECT));
		
		// remove link between tag and object
		$this->deleteLink($object, $tag_id);
	}

	return true;
}

/**
 * Selects tag. Takes tag word id as first argument. Returns tag
 * information.
 * 
 * @throws Media_TagException
 * @param int Tag word id
 * @return array
 */
public function selectTag ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Media_TagException('Input for parameter id is not numeric');	
	}
	
	// compose query
	$sql = "
		SELECT
			`media_tags`.`id` AS `id`,
			`media_tags`.`project` AS `project`,
			`media_tags`.`first_char` AS `first_char`,
			`media_tags`.`word` AS `word`,
			`media_tags`.`word_url` AS `word_url`,
			`media_tags`.`occurrences` AS `occurrences`
		FROM
			".OAK_DB_MEDIA_TAGS." AS `media_tags`
		WHERE
			`media_tags`.`id` = :id
		  AND
			`media_tags`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => $id,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Selects  tags. Takes array with select params as first
 * argument. Returns array with tags. 
 *
 * <b>List of supported parameters</b>
 * 
 * <ul>
 * <li>project: int, project id</li>
 * <li>object: int, object id</li>
 * <li>start: int, article offset</li>
 * <li>limit: int, amount of articles to return</li>
 * <li>order_marco, string, otpional: How to sort the result set.
 * Supported macros:
 *    <ul>
 *        <li>FIRST_CHAR: sort by first char</li>
 *        <li>WORD: sorty by tag word</li>
 *        <li>OCCURRENCES: sorty by occurrences</li>
 *    </ul>
 * </li>
 * </ul>
 * </ul>
 * 
 * @throws Media_TagException
 * @param array Select params
 * @return array
 */
public function selectTags ($params = array())
{
	// define some vars
	$object = null;
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();

	// input check
	if (!is_array($params)) {
		throw new Media_TagException('Input for parameter params is not an array');	
	}

	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'object':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			case 'order_macro':
					$$_key = (string)$_value;
				break;
			default:
				throw new Media_TagException("Unknow parameter $_key");
		}
	}

	// define ordner macros
	$macros = array(
		'FIRST_CHAR' => '`media_tags`.`first_char`',
		'WORD' => '`media_tags`.`word`',
		'OCCURRENCES' => '`media_tags`.`occurrences`'
	);
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// compose sql query
	$sql = "
		SELECT
			`media_tags`.`id` AS `id`,
			`media_tags`.`project` AS `project`,
			`media_tags`.`first_char` AS `first_char`,
			`media_tags`.`word` AS `word`,
			`media_tags`.`word_url` AS `word_url`,
			`media_tags`.`occurrences` AS `occurrences`
		FROM
			".OAK_DB_MEDIA_TAGS." AS `media_tags`
		JOIN
			".OAK_DB_MEDIA_OBJECTS2MEDIA_TAGS." AS `media_tags2media_objects`
		  ON
			`media_tags`.`id` = `media_tags2media_objects`.`tag`
		JOIN
			".OAK_DB_MEDIA_OBJECTS." AS `media_objects`
		  ON
			`media_tags2media_objects`.`object` = `media_objects`.`id`
		WHERE
			`media_tags`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($object) && is_numeric($object)) {
		$sql .= " AND `media_objects`.`id` = :object ";
		$bind_params['object'] = $object;
	}
	
	// aggregate result set
	$sql .= " GROUP BY `media_tags`.`id` ";
	
	// re-order result set
	if (!empty($order_macro)) {
		$HELPER = load('utility:helper');
		$sql .= " ORDER BY ".$HELPER->_sqlForOrderMacro($order_macro, $macros);
	}
	
	// add limits etc
	if (empty($start) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u", $limit);
	}
	if (!empty($start) && is_numeric($start) && !empty($limit) && is_numeric($limit)) {
		$sql .= sprintf(" LIMIT %u, %u", $start, $limit);
	}
	
	// execute query and return result
	return $this->base->db->select($sql, 'multi', $bind_params);
}

/**
 * Function to look up if the tag is already in the database. Takes
 * the tag word (some scalar value) as first argument. Returns the
 * tag id in the database (int) or, if the tag wasn't found, boolean
 * false.
 * 
 * @throws Media_TagException
 * @param string Tag word
 * @return mixed
 */
public function tag_exists ($tag)
{
	// input check
	if (!is_scalar($tag)) {
		throw new Media_TagException('Input for parameter tag is not scalar');	
	}
	
	// pepare query
	$sql = "
		SELECT
			`media_tags`.`id`
		FROM
			".OAK_DB_MEDIA_TAGS." AS `media_tags`
		WHERE
			`media_tags`.`word` = :word
		  AND
			`media_tags`.`project` = :project
		LIMIT 1
	";
	
	// prepare bind params
	$bind_params = array(
		'word' => $tag,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// execute query an return tag id
	$tag_id = $this->base->db->select($sql, 'field', $bind_params);
	
	// return tag id or boolean false
	if ((int)$tag_id < 1) {
		return false;
	} else {
		return (int)$tag_id;	
	}
}

/**
 * Check for link between object id and tag word. Takes object
 * id as first argument, tag word id as second argument. Return
 * bool on false and int (Link id) on true.
 *
 * @throws Media_TagException
 * @param int Object id
 * @param int Tag id
 * @return mixed
 */
public function link_exists ($object, $tag)
{
	// input check
	if (empty($object) || !is_numeric($object)) {
		throw new Media_TagException('Input for parameter object is not numeric');	
	}
	if (empty($tag) && is_scalar($tag)) {
		throw new Media_TagException('Input for parameter tag is not scalar');	
	}
	
	// prepare query
	$sql = "
		SELECT
			`media_tags2media_objects`.`id` AS `id`
		FROM
			".OAK_DB_MEDIA_OBJECTS2MEDIA_TAGS." AS `media_tags2media_objects`
		JOIN
			".OAK_DB_MEDIA_OBJECTS." AS `media_objects`
		  ON
			`media_tags2media_objects`.`object` = `media_objects`.`id`
		WHERE
			`media_tags2media_objects`.`object` = :object
		  AND
			`media_tags2media_objects`.`tag` = :tag
		  AND
			`media_objects`.`project` = :project
		LIMIT 1
	";
	
	// prepare bind params
	$bind_params = array(
		'object' => $object,
		'tag' => $tag,
		'project' => OAK_CURRENT_PROJECT
	);
	
	// get the entry
	$link_id = $this->base->db->select($sql, 'field', $bind_params);

	// return link id or boolean false
	if ((int)$link_id < 1) {
		return false;
	} else {
		return (int)$link_id;	
	}
}

/**
 * Adds link between object and tag. Takes the object id as first
 * argument, the tag word id as second argument. First, the function
 * will look up, if the link exists. If it doesn't exists, the function
 * will add one. If it already exist, nothing happens. The function
 * always returns boolean true.
 * 
 * @throws Media_TagException
 * @param int Object id
 * @param int Tag id
 * @return int Link id
 */
protected function addLink ($object, $tag)
{
	// input check
	if (empty($object) || !is_numeric($object)) {
		throw new Media_TagException('Input for parameter object is not numeric');	
	}
	if (empty($tag) || !is_numeric($tag)) {
		throw new Media_TagException('Input for parameter tag is not numeric');
	}
	
	// check if link exists. if it doesn't exist, add id
	$link = $this->link_exists($object, $tag);
	if ($link === false) {
		// prepare sql data
		$data = array (
			'object' => (int)$object,
			'tag' => (int)$tag
		);
		// add link
		$link = $this->base->db->insert(OAK_DB_MEDIA_OBJECTS2MEDIA_TAGS, $data);
	}
	
	// return link id
	return (int)$link;
}

/**
 * Removes link between object and tag. Takes the object id as first
 * argument, the tag word id as second argument. First, the function will
 * look up, if the link exists. If it exists, it will be deleted. If it
 * doesn't exist, nothing happens. The function always returns boolean true.
 * 
 * @throws Media_TagException
 * @param int Object id
 * @param int Tag id
 * @return bool
 */
protected function deleteLink ($object, $tag)
{
	// input check
	if (empty($object) || !is_numeric($object)) {
		throw new Media_TagException('Input for parameter object is not numeric');	
	}
	if (empty($tag) || !is_numeric($tag)) {
		throw new Media_TagException('Input for parameter tag is not numeric');
	}
	
	// check if link exists
	$link = $this->link_exists($object, $tag);
	if ($link !== false) {
		// prepare where clause
		$where = " WHERE id = :id ";
		
		// pepare bind params
		$bind_params = array(
			'id' => $link
		);
			
		// delete entry
		$this->base->db->delete(OAK_DB_MEDIA_OBJECTS2MEDIA_TAGS,
			$where, $bind_params);
	}
	
	return true;
}

/**
 * Helper function to convert a string of tags, each separated by a comma,
 * into an array. Useful when receiving data from a an user input field.
 * 
 * @throws Media_TagException
 * @param string Tag string
 * @return array Tag array
 */
public function _tagStringToArray ($string)
{
	// input check
	if (!is_scalar($string)) {
		throw new Media_TagException('Input for parameter string is not scalar');	
	}

	// explode string and clean the parts
	$tags = array();
	foreach (explode($this->_tag_separator, $string) as $_part) {
		if (!empty($_part)) {
			$tags[] = trim($_part);
		}
	}
	
	return $tags;
}

/**
 * Helper function to convert a array of tags into a string
 * of tags, each separated by a comma. Useful when it's
 * required to prepare data for a user input field.
 * 
 * @throws Media_TagException
 * @param string Tag string
 * @return array Tag array
*/
public function _tagArrayToString ($array)
{
	// input check
	if (empty($array) || !is_array($array)) {
		return '';
	} else {
		// put every tag within the array into a string, every tag separated
		// by a the configured tag separator, follwed by a whitespace and return
		// the string
		return (string)trim(implode($this->_tag_separator.' ', $array));
	}
}

/**
 * Prepares tag string for tag query. Takes a string of tags, each
 * separated by Tag::_tag_separator, as first argument. Returns array.
 *
 * @throws Media_TagException
 * @param string Tag string
 * @return array Tag array
 */
public function _prepareTagStringForQuery ($string)
{
	// input check
	if (!is_scalar($string)) {
		throw new Media_TagException('Input for parameter string is not scalar');	
	}

	// explode string and clean the parts
	$tags = array();
	foreach (explode($this->_tag_separator, $string) as $_part) {
		if (!empty($_part)) {
			$tags[] = trim($_part)."%";
		}
	}
	
	return $tags;
}

// End of class
}

class Media_TagException extends Exception { }

?>