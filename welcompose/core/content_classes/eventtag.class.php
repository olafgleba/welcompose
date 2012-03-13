<?php

/**
 * Project: Welcompose
 * File: eventtag.class.php
 * 
 * Copyright (c) 2008-2012 creatics, Olaf Gleba <og@welcompose.de>
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 *  
 * @author Olaf Gleba
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton for Content_EventTag.
 * 
 * @return object
 */
function Content_EventTag ()
{
	if (Content_EventTag::$instance == null) {
		Content_EventTag::$instance = new Content_EventTag(); 
	}
	return Content_EventTag::$instance;
}

class Content_EventTag {
	
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
	 * Tag separator used when converting tag strings to
	 * arrays and vice versa.
	 * 
	 * @param string 
	 */
	public $_tag_separator = ',';

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
 * Adds tags of a posting. Takes the page id as first argument, the posting
 * id second argument and the tag word list as third argument.
 * 
 * @throws Content_EventTagException
 * @param int Page id
 * @param int Posting id
 * @param array Tag array
 * @return bool
 */
public function addPostingTags ($page, $posting, $tags)
{
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw Content_EventTagException('Input for parameter page is not numeric');
	}
	if (empty($posting) || !is_numeric($posting)) {
		throw new Content_EventTagException('Input for parameter posting is not numeric');	
	}
	if (!is_array($tags)) {
		throw new Content_EventTagException('Input for parameter tags is not an array');
	}
	
	// loop through the tags and add them
	foreach ($tags as $_tag) {
		if (!empty($_tag)) {
			$this->addEventTag($page, $posting, $_tag);	
		}
	}
	
	return true;
}

/**
 * Update tags of a posting. Takes the page id as first argument,
 * the posting id as second argument and the new tag list as third
 * argument. Fetches the old tag list from the database and calculates
 * the differences. Tags that are no longer required will be removed,
 * new ones will be added.
 * 
 * @throws Content_EventTagException
 * @param int Page id
 * @param int Posting id
 * @param array Tag array
 * @return bool
 */
public function updatePostingTags ($page, $posting, $tags)
{
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_EventTagException('Input for parameter page is not numeric');
	}
	if (empty($posting) || !is_numeric($posting)) {
		throw new Content_EventTagException('Input for parameter posting is not numeric');	
	}
	if (!is_array($tags)) {
		throw new Content_EventTagException('Input for parameter tags is not an array');
	}	
	
	// prepare old tags
	$old_tags = array();
	$params = array(
		'page' => $page,
		'posting' => $posting
	);
	foreach ($this->selectEventTags($params) as $_old_tag) {
		$old_tags[] = $_old_tag['word'];
	}
	
	// discover the tags which we have to delete
	$to_delete = array_diff($old_tags, $tags);
	
	// delete them
	foreach ($to_delete as $_tag) {
		$this->deleteEventTag($page, $posting, $_tag);	
	}
	
	// discover the tags which we have to add
	$to_add = array_diff($tags, $old_tags);
	
	// add them
	foreach ($to_add as $_tag) {
		$this->addEventTag($page, $posting, $_tag);	
	}
	
	return true;
}

/**
 * Deletes all tags of a posting. Takes the page id as first argument,
 * the posting id as second argument. Returns bool.
 * 
 * @throws blogtagException
 * @param int Page id
 * @param int Posting id
 * @param array Tag array
 * @return bool
 */
public function deletePostingTags ($page, $posting)
{
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw Content_EventTagException('Input for parameter page is not numeric');
	}
	if (empty($posting) || !is_numeric($posting)) {
		throw new Content_EventTagException('Input for parameter posting is not numeric');	
	}
	
	// get the tags and delete them
	$params = array(
		'page' => $page,
		'posting' => $posting
	);
	foreach ($this->selectEventTags($params) as $_tag) {
		$this->deleteEventTag($page, $posting, $_tag['word']);
	}
	
	return true;
}

/**
 * Inserts a event tag into the event tag database. Takes the page 
 * id as first argument, the event posting that the tag will be
 * linked to as second argument and the tag word as third argument.
 * The amount of occurrences will be set to 1. If the tag already
 * exists in the database, updateEventTag() will be called.
 * 
 * @throws Content_EventTagException
 * @param int Page id
 * @param int Posting id
 * @param string Tag word
 * @return int Tag id
 */
protected function addEventTag ($page, $posting, $tag)
{
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_EventTagException('Input for parameter page is not numeric');
	}
	if (empty($posting) || !is_numeric($posting)) {
		throw new Content_EventTagException('Input for parameter posting is not numeric');	
	}
	if (empty($tag) && is_scalar($tag)) {
		throw new Content_EventTagException('Input for parameter tag is not scalar');	
	}
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// check if tag already exists
	if ($this->tag_exists($page, $tag) === false) {
		// create "first char" 
		$word = preg_replace(WCOM_REGEX_TAG_FIRST_CHAR_CLEANUP, null, strtolower($tag));
		$first_char = substr($word, 0, 1);
		
		// create tag
		$data = array(
			'page' => $page,
			'first_char' => $first_char,
			'word' => $tag,
			'word_url' => $HELPER->createMeaningfulString($tag),
			'occurrences' => 1
		);
		$tag_id = $this->base->db->insert(WCOM_DB_CONTENT_EVENT_TAGS, $data);
		
		// create link between posting and tag
		$this->addLink($posting, $tag_id);
	
		// return tag id
		return $tag_id;
	} else {
		// if the tag already exists, update it
		return $this->updateEventTag($page, $posting, $tag);
	}
}

/**
 * Function to increase/decrease occurrences of tag words
 * and to add/remove links between tag words. Takes the page id
 * as first argument, the posting id as second argument, the tag
 * word as third argument and the instruction how to modify the
 * occurrences as fourth argument. modify_amount can be either
 * <samp>increase</samp> or <samp>decrease</samp>.
 * 
 * @throws Content_EventTagException
 * @param int Page id
 * @param int Posting id
 * @param string Tag word
 * @param string Modify amount
 */
protected function updateEventTag ($page, $posting, $tag, $modify_amount = 'increase')
{
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_EventTagException('Input for parameter page is not numeric');
	}
	if (empty($posting) || !is_numeric($posting)) {
		throw new Content_EventTagException('Input for parameter posting is not numeric');	
	}
	if (empty($tag) && is_scalar($tag)) {
		throw new Content_EventTagException('Input for parameter tag is not scalar');	
	}	

	// get the tag id
	$tag_id = $this->tag_exists($page, $tag);
	
	// throw exception if the tag wasn't found
	if ($tag_id === false) {
		throw new Content_EventTagException('Unable to find tag that I have to update');	
	}
	
	// update
	switch ($modify_amount) {
		case 'increase':
				// increment the occurrences
				$sql = "
					UPDATE
						".WCOM_DB_CONTENT_EVENT_TAGS."
					SET
						`occurrences` = `occurrences` + 1
					WHERE
						`id` = :id
				";
				// pepare bind params
				$bind_params = array(
					'id' => $tag_id
				);
				// execute query				
				$this->base->db->execute($sql, $bind_params);
				
				// check if a link between tag and posting already exits
				if (!$this->link_exists($posting, $tag_id)) {
					// add link
					$this->addLink($posting, $tag_id);
				}
			break;
		case 'decrease':
				// decrement the occurrences
				$sql = "
					UPDATE
						".WCOM_DB_CONTENT_EVENT_TAGS."
					SET
						`occurrences` = `occurrences` - 1
					WHERE
						`id` = :id
				";
				// pepare bind params
				$bind_params = array(
					'id' => $tag_id
				);
				// execute query
				$this->base->db->execute($sql, $bind_params);
				// check if we've to remove the link
				if ($this->link_exists($posting, $tag_id)) {
					// remove link
					$this->deleteLink($posting, $tag_id);	
				}
			break;
	}
	
	// return the tag id
	return $tag_id;
}

/**
 * Removes tag.Takes the page id as first argument, the posting
 * id as second argument and the tag word as third argument.
 * First, the function will decrease the occurrences (-1)
 * and then remove every tag where the amount of occurrences
 * is 0 (null). Then it will remove the link between the tag
 * an the posting.
 * 
 * @throws Content_EventTagException
 * @param int Page id
 * @param int Posting id
 * @param string Tag word
 * @return bool
 */
protected function deleteEventTag ($page, $posting, $tag)
{
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_EventTagException('Input for parameter page is not numeric');
	}
	if (empty($posting) || !is_numeric($posting)) {
		throw new Content_EventTagException('Input for parameter posting is not numeric');	
	}
	if (empty($tag) && is_scalar($tag)) {
		throw new Content_EventTagException('Input for parameter tag is not scalar');	
	}
	
	// get the tag id
	$tag_id = $this->tag_exists($page, $tag);
	
	if ($tag_id !== false) {
		// decrease occurrences if tag was found
		$this->updateEventTag($page, $posting, $tag, 'decrease');
		
		// run cleanup to remove tags with occurrences = 0
		$where = " WHERE `occurrences` < 1 ";
		$this->base->db->delete('content_event_tags', $where);
		
		// remove link between tag and posting
		$this->deleteLink($posting, $tag_id);
	}

	return true;
}

/**
 * Select tag
 * 
 * Takes tag word id as first argument. Returns tag
 * information.
 * 
 * @throws Content_EventTagException
 * @param int Tag word id
 * @return array
 */
public function selectEventTag ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_EventTagException('Input for parameter id is not numeric');	
	}
	
	// compose query
	$sql = "
		SELECT
			`content_event_tags`.`id` AS `id`,
			`content_event_tags`.`page` AS `page`,
			`content_event_tags`.`first_char` AS `first_char`,
			`content_event_tags`.`word` AS `word`,
			`content_event_tags`.`word_url` AS `word_url`,
			`content_event_tags`.`occurrences` AS `occurrences`
		FROM
			".WCOM_DB_CONTENT_EVENT_TAGS." AS `content_event_tags`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_event_tags`.`page` = `content_pages`.`id`
		WHERE
			`content_event_tags`.`id` = :id
		  AND
			`content_pages`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => $id,
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Selects event tags. Takes array with select params as first
 * argument. Returns array with tags. 
 *
 * <b>List of supported parameters</b>
 * 
 * <ul>
 * <li>page: int, page id</li>
 * <li>posting: int, posting id</li>
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
 * @throws Content_EventTagException
 * @param array Select params
 * @return array
 */
public function selectEventTags ($params = array())
{
	// define some vars
	$page = null;
	$posting = null;
	$order_macro = null;
	$start = null;
	$limit = null;
	$bind_params = array();

	// input check
	if (!is_array($params)) {
		throw new Content_EventTagException('Input for parameter params is not an array');	
	}

	// import params
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'posting':
			case 'page':
			case 'start':
			case 'limit':
					$$_key = (int)$_value;
				break;
			case 'order_macro':
					$$_key = (string)$_value;
				break;
			default:
				throw new Content_EventTagException("Unknow parameter $_key");
		}
	}

	// define ordner macros
	$macros = array(
		'FIRST_CHAR' => '`content_event_tags`.`first_char`',
		'WORD' => '`content_event_tags`.`word`',
		'OCCURRENCES' => '`content_event_tags`.`occurrences`'
	);
	
	// load helper class
	$HELPER = load('utility:helper');
	
	// compose sql query
	$sql = "
		SELECT
			`content_event_tags`.`id` AS `id`,
			`content_event_tags`.`page` AS `page`,
			`content_event_tags`.`first_char` AS `first_char`,
			`content_event_tags`.`word` AS `word`,
			`content_event_tags`.`word_url` AS `word_url`,
			`content_event_tags`.`occurrences` AS `occurrences`
		FROM
			".WCOM_DB_CONTENT_EVENT_TAGS." AS `content_event_tags`
		JOIN
			".WCOM_DB_CONTENT_EVENT_TAGS2CONTENT_EVENT_POSTINGS." AS `content_event_tags2content_event_postings`
		  ON
			`content_event_tags`.`id` = `content_event_tags2content_event_postings`.`tag`
		JOIN
			".WCOM_DB_CONTENT_EVENT_POSTINGS." AS `content_event_postings`
		  ON
			`content_event_tags2content_event_postings`.`posting` = `content_event_postings`.`id`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_event_postings`.`page` = `content_pages`.`id`
		WHERE
			`content_pages`.`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// add where clauses
	if (!empty($posting) && is_numeric($posting)) {
		$sql .= " AND `content_event_postings`.`id` = :posting ";
		$bind_params['posting'] = $posting;
	}
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `content_event_postings`.`page` = :page ";
		$bind_params['page'] = $page;
	}
	// exclude draft postings
	//$sql .= " AND `content_event_postings`.`draft` = '0' ";
	
	// aggregate result set
	$sql .= " GROUP BY `content_event_tags`.`id` ";
	
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
 * Select tag by word_url
 * 
 * Takes tag word_url as first argument. Returns tag
 * information.
 * 
 * @throws Content_EventTagException
 * @param string Tag word url
 * @return array
 */
public function selectEventTagByWordUrl ($word_url)
{
	// input check
	if (empty($word_url) || !is_string($word_url)) {
		throw new Content_EventTagException('Input for parameter word_url is not a string');	
	}
	
	// compose query
	$sql = "
		SELECT
			`content_event_tags`.`id` AS `id`,
			`content_event_tags`.`page` AS `page`,
			`content_event_tags`.`first_char` AS `first_char`,
			`content_event_tags`.`word` AS `word`,
			`content_event_tags`.`word_url` AS `word_url`,
			`content_event_tags`.`occurrences` AS `occurrences`
		FROM
			".WCOM_DB_CONTENT_EVENT_TAGS." AS `content_event_tags`
		WHERE
			`content_event_tags`.`word_url` = :word_url
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'word_url' => (string)$word_url
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Function to look up if the tag is already in the database. Takes
 * the page id as first argument, the tag word (some scalar value) as
 * second argument. Returns the tag id in the database (int) or, if
 * the tag wasn't found, boolean false.
 * 
 * @throws Content_EventTagException
 * @param int Page id
 * @param string Tag word
 * @return mixed
 */
public function tag_exists ($page, $tag)
{
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_EventTagException('Input for parameter page is not numeric');
	}
	if (!is_scalar($tag)) {
		throw new Content_EventTagException('Input for parameter tag is not scalar');	
	}
	
	// pepare query
	$sql = "
		SELECT
			`content_event_tags`.`id`
		FROM
			".WCOM_DB_CONTENT_EVENT_TAGS." AS `content_event_tags`
		JOIN
			".WCOM_DB_CONTENT_PAGES." AS `content_pages`
		  ON
			`content_event_tags`.`page` = `content_pages`.`id`
		WHERE
			`content_event_tags`.`word` = :word
		  AND
			`content_event_tags`.`page` = :page
		  AND
			`content_pages`.`project` = :project
		LIMIT 1
	";
	
	// prepare bind params
	$bind_params = array(
		'word' => $tag,
		'page' => $page,
		'project' => WCOM_CURRENT_PROJECT
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
 * Check for link between posting id and tag word. Takes posting
 * id as first argument, tag word id as second argument. Return
 * bool on false and int (Link id) on true.
 *
 * @throws Content_EventTagException
 * @param int Posting id
 * @param int Tag id
 * @return mixed
 */
public function link_exists ($posting, $tag)
{
	// input check
	if (empty($posting) || !is_numeric($posting)) {
		throw new Content_EventTagException('Input for parameter posting is not numeric');	
	}
	if (empty($tag) && is_scalar($tag)) {
		throw new Content_EventTagException('Input for parameter tag is not scalar');	
	}
	
	// prepare query
	$sql = "
		SELECT
			`id`
		FROM
			".WCOM_DB_CONTENT_EVENT_TAGS2CONTENT_EVENT_POSTINGS." AS `content_event_tags2content_event_postings`
		WHERE
			`posting` = :posting
		  AND
			`tag` = :tag
		LIMIT 1
	";
	
	// prepare bind params
	$bind_params = array(
		'posting' => $posting,
		'tag' => $tag
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
 * Adds link between posting and tag. Takes the posting id as first
 * argument, the tag word id as second argument. First, the function
 * will look up, if the link exists. If it doesn't exists, the function
 * will add one. If it already exist, nothing happens. The function
 * always returns boolean true.
 * 
 * @throws Content_EventTagException
 * @param int Posting id
 * @param int Tag id
 * @return int Link id
 */
protected function addLink ($posting, $tag)
{
	// input check
	if (empty($posting) || !is_numeric($posting)) {
		throw new Content_EventTagException('Input for parameter posting is not numeric');	
	}
	if (empty($tag) || !is_numeric($tag)) {
		throw new Content_EventTagException('Input for parameter tag is not numeric');
	}
	
	// check if link exists. if it doesn't exist, add id
	$link = $this->link_exists($posting, $tag);
	if ($link === false) {
		// prepare sql data
		$data = array (
			'posting' => (int)$posting,
			'tag' => (int)$tag
		);
		// add link
		$link = $this->base->db->insert('`content_event_tags2content_event_postings`', $data);
	}
	
	// return link id
	return (int)$link;
}

/**
 * Removes link between posting and tag. Takes the posting id as first
 * argument, the tag word id as second argument. First, the function will
 * look up, if the link exists. If it exists, it will be deleted. If it
 * doesn't exist, nothing happens. The function always returns boolean true.
 * 
 * @throws Content_EventTagException
 * @param int Posting id
 * @param int Tag id
 * @return bool
 */
protected function deleteLink ($posting, $tag)
{
	// input check
	if (empty($posting) || !is_numeric($posting)) {
		throw new Content_EventTagException('Input for parameter posting is not numeric');	
	}
	if (empty($tag) || !is_numeric($tag)) {
		throw new Content_EventTagException('Input for parameter tag is not numeric');
	}
	
	// check if link exists
	$link = $this->link_exists($posting, $tag);
	if ($link !== false) {
		// prepare where clause
		$where = " WHERE id = :id ";
		
		// pepare bind params
		$bind_params = array(
			'id' => $link
		);
			
		// delete entry
		$this->base->db->delete('`content_event_tags2content_event_postings`',
			$where, $bind_params);
	}
	
	return true;
}

/**
 * Prepares tags for tag cloud generation. Calculates relevance for every tag.
 * Relevance is a number between 0 and the given higher range delimiter.
 * 
 * It's recommend to pass an array of tags that contains the n most relevant
 * tags (so these one with the highest occurrences) in descending order
 * (highest number of occurrences comes first). Alphabetical sorting will be
 * done automatically.
 * 
 * @param array Tag array
 * @param int Higher range delimiter
 */
public function prepareTagsForCloud ($tags, $range)
{
	// input check
	if (!is_array($tags)) {
		throw new Content_EventTagException('Input for parameter tags is expected to be an array');
	}
	if (empty($range) || !is_numeric($range)) {
		throw new Content_EventTagException('Input for parameter tags is expected to be numeric');
	}
	if ((int)$range === 0) {
		throw new Content_EventTagException('Range must not be equal zero.');
	}
	
	// if tag array is empty, skip here.
	if (empty($tags)) {
		return array();
	}
	
	// find highest/lowest amount of occurrences
	$numbers = array();
	foreach ($tags as $_tag) {
		$numbers[] = (int)Base_Cnc::ifsetor($_tag['occurrences'], 0);
	}
	$max = max($numbers);
	$min = min($numbers);
	
	// calculate divisor to bring occurrences into range
	$divisor = ($max - $min) / (int)$range; 
	$divisor = ($divisor == 0) ? 1 : $divisor;
	
	// append relevance to every tag
	$new_tag_array = array();
	foreach ($tags as $_tag) {
		$new_tag_array[$_tag['word']] = $_tag;
		
		$occurrences = (float)Base_Cnc::ifsetor($_tag['occurrences'], 0);
		$new_tag_array[$_tag['word']]['relevance'] = round(($occurrences - $min) / $divisor);
	}
	
	// shuffle tags
	ksort($new_tag_array);
	
	return $new_tag_array;
}

/**
 * Helper function to convert a string of tags, each separated by a comma,
 * into an array. Useful when receiving data from a an user input field.
 * 
 * @throws Content_EventTagException
 * @param string Tag string
 * @return array Tag array
 */
public function _tagStringToArray ($string)
{
	// input check
	if (!is_scalar($string)) {
		throw new Content_EventTagException('Input for parameter string is not scalar');
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
 * @throws Content_EventTagException
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

public function getSerializedTagArrayFromTagArray ($array)
{
	// unset occurrences key
	foreach ($array as $_key => $_tag) {
		unset($array[$_key]['occurrences']);
	}
	
	return serialize($array);
}

public function getTagStringFromSerializedArray ($str)
{
	$array = unserialize($str);
	$tags = array();
	foreach ($array as $_tag) {
		$tags[] = $_tag['word'];
	}
	
	return $this->_tagArrayToString($tags);
}

public function getTagWordUrlArrayFromSerializedArray ($str)
{
	$array = unserialize($str);
	$tags = array();
	foreach ($array as $_tag) {
		$tags[] = $_tag['word_url'];
	}
	
	return $tags;
}

// End of class
}

class Content_EventTagException extends Exception { }

?>