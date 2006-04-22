<?php

/**
 * Project: Oak
 * File: blogtag.class.php
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
 * $Id: blogtag.class.php 2 2006-03-20 11:43:20Z andreas $
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/apache2.0.php Apache License, Version 2.0
 */

class Content_Blogtag {
	
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
 * Singleton. Returns instance of the Content_Blogtag object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Content_Blogtag::$instance == null) {
		Content_Blogtag::$instance = new Content_Blogtag(); 
	}
	return Content_Blogtag::$instance;
}

/**
 * Inserts a blog tag into the blog tag database. Takes the page 
 * id as first argument, the blog posting that the tag will be
 * linked to as second argument and the tag word as third argument.
 * The amount of occurrences will be set to 1. If the tag already
 * exists in the database, updateBlogTag() will be called.
 * 
 * @throws Content_BlogtagException
 * @param int Page id
 * @param int Posting id
 * @param string Tag word
 * @return int Tag id
 */
protected function addBlogTag ($page, $posting, $tag)
{
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_BlogtagException('Input for parameter page is not numeric');
	}
	if (empty($posting) || !is_numeric($posting)) {
		throw new Content_BlogtagException('Input for parameter posting is not numeric');	
	}
	if (empty($tag) && is_scalar($tag)) {
		throw new Content_BlogtagException('Input for parameter tag is not scalar');	
	}
	
	// load helper class
	require_once(dirname(__FILE__).'/../loader.php');
	$HELPER = load('helper');
	
	// check if tag already exists
	if ($this->tag_exists($page, $tag) === false) {
		// create "first char" 
		$word = preg_replace(OAK_REGEX_TAG_FIRST_CHAR_CLEANUP, null, strtolower($tag));
		$first_char = substr($word, 0, 1);
		
		// create tag
		$data = array(
			'page' => $page,
			'first_char' => $first_char,
			'word' => $tag,
			'word_url' => $HELPER->createMeaningfulUrl($tag),
			'occurrences' => 1
		);
		$tag_id = $this->base->db->insert('`content_blog_tags`', $data);
		
		// create link between posting and tag
		$this->addLink($posting, $tag_id);
	
		// return tag id
		return $tag_id;
	} else {
		// if the tag already exists, update it
		return $this->updateBlogTag($page, $posting, $tag);
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
 * @throws Content_BlogtagException
 * @param int Page id
 * @param int Posting id
 * @param string Tag word
 * @param string Modify amount
 */
protected function updateBlogTag ($page, $posting, $tag, $modify_amount = 'increase')
{
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_BlogtagException('Input for parameter page is not numeric');
	}
	if (empty($posting) || !is_numeric($posting)) {
		throw new Content_BlogtagException('Input for parameter posting is not numeric');	
	}
	if (empty($tag) && is_scalar($tag)) {
		throw new Content_BlogtagException('Input for parameter tag is not scalar');	
	}	

	// get the tag id
	$tag_id = $this->tag_exists($page, $tag);
	
	// throw exception if the tag wasn't found
	if ($tag_id === false) {
		throw new Content_BlogtagException('Unable to find tag that I have to update');	
	}
	
	// update
	switch ($modify_amount) {
		case 'increase':
				// increment the occurrences
				$sql = "
					UPDATE
						`content_blog_tags`
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
						`content_blog_tags`
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
 * @throws Content_BlogtagException
 * @param int Page id
 * @param int Posting id
 * @param string Tag word
 * @return bool
 */
protected function deleteBlogTag ($page, $posting, $tag)
{
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_BlogtagException('Input for parameter page is not numeric');
	}
	if (empty($posting) || !is_numeric($posting)) {
		throw new Content_BlogtagException('Input for parameter posting is not numeric');	
	}
	if (empty($tag) && is_scalar($tag)) {
		throw new Content_BlogtagException('Input for parameter tag is not scalar');	
	}
	
	// get the tag id
	$tag_id = $this->tag_exists($page, $tag);
	
	if ($tag_id !== false) {
		// decrease occurrences if tag was found
		$this->updateBlogTag($page, $posting, $tag, 'decrease');
		
		// run cleanup to remove tags with occurrences = 0
		$where = " WHERE `occurrences` < 1 ";
		$this->base->db->delete('content_blog_tags', $where);
		
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
 * @throws Content_BlogtagException
 * @param int Tag word id
 * @return array
 */
public function selectTag ($id)
{
	// input check
	if (empty($id) || !is_numeric($id)) {
		throw new Content_BlogtagException('Input for parameter id is not numeric');	
	}
	
	// compose query
	$sql = "
		SELECT
			`content_blog_tags`.`id` AS `id`,
			`content_blog_tags`.`page` AS `page`,
			`content_blog_tags`.`word` AS `word`,
			`content_blog_tags`.`word_url` AS `word_url`,
			`content_blog_tags`.`occurrences` AS `occurrences`
		FROM
			`content_blog_tags`
		WHERE
			`content_blog_tags`.`id` = :id
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'id' => $id
	);
	
	// execute query and return result
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Selects blog tags. Takes array with select params as first
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
 * @throws Content_BlogtagException
 * @param array Select params
 * @return array
 */
public function selectBlogTags ($params = array())
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
		throw new Content_BlogtagException('Input for parameter params is not an array');	
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
				throw new Content_BlogtagException("Unknow parameter $_key");
		}
	}

	// define ordner macros
	$macros = array(
		'FIRST_CHAR' => '`content_blog_tags`.`first_char`',
		'WORD' => '`content_blog_tags`.`word`',
		'OCCURRENCES' => '`content_blog_tags`.`occurrences`'
	);
	
	// compose sql query
	$sql = "
		SELECT
			`content_blog_tags`.`id` AS `id`,
			`content_blog_tags`.`page` AS `page`,
			`content_blog_tags`.`word` AS `word`,
			`content_blog_tags`.`word_url` AS `word_url`,
			`content_blog_tags`.`occurrences` AS `occurrences`,
			`content_blog_postings`.`id` AS `posting_id`
		FROM
			`content_blog_tags`
		LEFT JOIN
			`content_blog_tags2content_blog_postings`
		  ON
			`content_blog_tags`.`id` = `content_blog_tags2content_blog_postings`.`tag`
		LEFT JOIN
			`blog_postings`
		  ON
			`content_blog_tags2content_blog_postings`.`posting` = `blog_postings`.`id`
		LEFT JOIN
			`pages`
		  ON
			`blog_postings`.`page` = `pages`.`id`
		WHERE
			1
	";
	
	// add where clauses
	if (!empty($posting) && is_numeric($posting)) {
		$sql .= " AND `content_blog_postings`.`id` = :posting ";
		$bind_params['posting'] = $posting;
	}
	if (!empty($page) && is_numeric($page)) {
		$sql .= " AND `content_blog_postings`.`page` = :page ";
		$bind_params['page'] = $page;
	}
	
	// aggregate result set
	$sql .= " GROUP BY `content_blog_tags`.`id` ";
	
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
 * the page id as first argument, the tag word (some scalar value) as
 * second argument. Returns the tag id in the database (int) or, if
 * the tag wasn't found, boolean false.
 * 
 * @throws Content_BlogtagException
 * @param int Page id
 * @param string Tag word
 * @return mixed
 */
public function tag_exists ($page, $tag)
{
	// input check
	if (empty($page) || !is_numeric($page)) {
		throw new Content_BlogtagException('Input for parameter page is not numeric');
	}
	if (!is_scalar($tag)) {
		throw new Content_BlogtagException('Input for parameter tag is not scalar');	
	}
	
	// pepare query
	$sql = "
		SELECT
			`id`
		FROM
			`content_blog_tags`
		WHERE
			`word` = :word
		  AND
			`page` = :page
		LIMIT 1
	";
	
	// prepare bind params
	$bind_params = array(
		'word' => $tag,
		'page' => $page
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
 * @throws Content_BlogtagException
 * @param int Posting id
 * @param int Tag id
 * @return mixed
 */
public function link_exists ($posting, $tag)
{
	// input check
	if (empty($posting) || !is_numeric($posting)) {
		throw new Content_BlogtagException('Input for parameter posting is not numeric');	
	}
	if (empty($tag) && is_scalar($tag)) {
		throw new Content_BlogtagException('Input for parameter tag is not scalar');	
	}
	
	// prepare query
	$sql = "
		SELECT
			`id`
		FROM
			`content_blog_tags2content_blog_postings`
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
 * @throws Content_BlogtagException
 * @param int Posting id
 * @param int Tag id
 * @return int Link id
 */
protected function addLink ($posting, $tag)
{
	// input check
	if (empty($posting) || !is_numeric($posting)) {
		throw new Content_BlogtagException('Input for parameter posting is not numeric');	
	}
	if (empty($tag) || !is_numeric($tag)) {
		throw new Content_BlogtagException('Input for parameter tag is not numeric');
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
		$link = $this->base->db->insert('`content_blog_tags2content_blog_postings`', $data);
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
 * @throws Content_BlogtagException
 * @param int Posting id
 * @param int Tag id
 * @return bool
 */
protected function deleteLink ($posting, $tag)
{
	// input check
	if (empty($posting) || !is_numeric($posting)) {
		throw new Content_BlogtagException('Input for parameter posting is not numeric');	
	}
	if (empty($tag) || !is_numeric($tag)) {
		throw new Content_BlogtagException('Input for parameter tag is not numeric');
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
		$this->base->db->delete('`content_blog_tags2content_blog_postings`',
			$where, $bind_params);
	}
	
	return true;
}

/**
 * Helper function to convert a string of tags, each separated by a comma,
 * into an array. Useful when receiving data from a an user input field.
 * 
 * @throws Content_BlogtagException
 * @param string Tag string
 * @return array Tag array
 */
public function _tagStringToArray ($string)
{
	// input check
	if (!is_scalar($string)) {
		throw new Content_BlogtagException('Input for parameter string is not scalar');	
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
 * @throws Content_BlogtagException
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

// End of class
}

class Content_BlogtagException extends Exception { }

?>