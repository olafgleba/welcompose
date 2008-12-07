<?php

/**
 * Project: Welcompose
 * File: urlgenerator.class.php
 * 
 * Copyright (c) 2008 creatics media.systems
 * 
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id$
 * 
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton. Returns instance of the Utility_UrlGenerator object.
 * 
 * @return object
 */
function Utility_UrlGenerator ()
{ 
	if (Utility_UrlGenerator::$instance == null) {
		Utility_UrlGenerator::$instance = new Utility_UrlGenerator(); 
	}
	return Utility_UrlGenerator::$instance;
}

class Utility_UrlGenerator {
	
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
	 * List of url arguments and their search patterns
	 * for url generation.
	 *
	 * @var array
	 */
	protected $_args_and_patterns = array(
		'project_id' => '<project_id>',
		'project_name' => '<project_name>',
		'page_id' => '<page_id>',
		'page_name' => '<page_name>',
		'action' => '<action>',
		'posting_id' => '<posting_id>',
		'posting_title' => '<posting_title>',
		'posting_year_added' => '<posting_year_added>',
		'posting_month_added' => '<posting_month_added>',
		'posting_day_added' => '<posting_day_added>',
		'tag_word' => '<tag_word>',
		'start' => '<start>'
	);
	
	/**
	 * Container to cache the current project info.
	 *
	 * @var array
	 */
	protected $_project = array();
	
	/**
	 * Container to cache the info about all pages.
	 *
	 * @var array
	 */
	protected $_pages = array();
	
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
 * Generates internal link using the provided arguments. The
 * required arguments depend on the page someone likes to link to.
 * Unknown arguments will be treated as user supplied arguments
 * and appended to the generated url. If the parameter remove_amps
 * is set to true, encoded ampersands will be converted to "clear
 * text" ampersands. If the parameter prepend_host ist set to true,
 * the server http host will be prepend the build url. If the parameter
 * encode_system_url is set to true the url will urlencoded. This takes
 * account of the possibly former converted ampersand.
 * 
 * @throws Utility_UrlGeneratorException
 * @param array Args
 * @param bool Remove encoded ampersands
 * @param bool Prepend http host
 * @param bool Encode output url string
 * @return string
 */
public function generateInternalLink ($args = array(), $remove_amps = false)
{
	// input check
	if (!is_array($args)) {
		throw new Utility_UrlGeneratorException("Input for paramter args is expected to be an array");
	}
	
	// initialize internal arguments
	foreach ($this->_args_and_patterns as $_arg => $_replacement) {
		$$_arg = null;
	}
	
	// import args
	$user_supplied_args = array();
	foreach ($args as $_arg => $_value) {
		if (array_key_exists($_arg, $this->_args_and_patterns)) {
			$$_arg = $_value;
		} else {
			$user_supplied_args[$_arg] = $_value;
		}
	}
	
	// get project and all pages and cache them for reuse
	if (empty($this->_project)) {
		// get current project
		$PROJECT = load('Application:Project');
		$this->_project = $PROJECT->selectProject(WCOM_CURRENT_PROJECT);
	}
	if (empty($this->_pages)) {
		// get pages
		$PAGE = load('Content:Page');
		$this->_pages = $PAGE->selectPages();
	}
	
	// ok, the first step is to look for the page we'll link to
	$current_page = array();
	foreach ($this->_pages as $_page) {
		if ($_page['id'] == $page_id) {
			$current_page = $_page;
			break;
		}
	}
	
	// if we haven't found a current page yet, the user may have selected a page that
	// doesn't exist or has not defined any page at all to link to. if the page doesnt
	// exist (!empty($page)), we should skip here. if he has not defined any page at
	// all, we use the index page.
	if (empty($current_page) && !empty($page_id)) {
	} elseif (empty($current_page)) {
		foreach ($this->_pages as $_page) {
			if ($_page['index_page']) {
				$current_page = $_page;
			}
		}
	}
	
	// if there's not even a index page, we have so skip here
	if (empty($current_page)) {
		return false;
	}
	
	// if the current page is a WCOM_URL page, we can simply return the url saved in
	// the database.
	if ($_page['page_type_name'] == 'WCOM_URL') {
		return $_page['url'];
	}
	
	// set page_name_url
	$project_id = $this->_project['id'];
	$project_name = $this->_project['name_url'];
	$page_id = $current_page['id'];
	$page_name = $current_page['name_url'];
	
	// if the page type of the current page is WCOM_BLOG, we need to execute
	// some additional checks because we may have to link to the single
	// blog postings or to the archives.
	if ($current_page['page_type_name'] == 'WCOM_BLOG') {
		// if posting_id is set, we need to get the blog posting so that we've something
		// to fill the variables like posting_title with
		if (!empty($posting_id) && is_numeric($posting_id)) {
			$BLOGPOSTING = load('Content:BlogPosting');
			$blog_posting = $BLOGPOSTING->selectBlogPosting($posting_id);
			
			// if there's no blog posting, the url cannot be valid and we have to
			// return false
			if (empty($blog_posting)) {
				return false;
			}
			
			// fill the variables with the blog posting info
			$posting_title = $blog_posting['title_url'];
			$posting_year_added = $blog_posting['year_added'];
			$posting_month_added = $blog_posting['month_added'];
			$posting_day_added = $blog_posting['day_added'];
		}
	}
	
	// the next step to get an url is to look at the page type and the action so that we
	// can fetch the right url pattern from the sys.inc.php.
	$action = (empty($action) ? 'Index' : $action);
	$url_pattern_name = sprintf("%s_%s",
		strtolower(preg_replace(WCOM_REGEX_ACTION_TO_URL_PATTERN, "_\\1", $current_page['page_type_internal_name'])),
		strtolower(preg_replace(WCOM_REGEX_ACTION_TO_URL_PATTERN, "_\\1", $action))
	);
	
	// if the page where the link should point to is the index page, we have to use a different url pattern that
	// ommits the page name
	if ($current_page['index_page']) {
		$url_pattern_name = $url_pattern_name."_start";
	}
	
	// the same applies on link requests for a tag page
	if (array_key_exists('tag_word', $args) && !empty($args['tag_word'])) {
		$url_pattern_name = $url_pattern_name."_tag";
	}
	
	// the same applies on link requests for a pager
	if (array_key_exists('start', $args) && is_numeric($args['start'])) {
		$url_pattern_name = $url_pattern_name."_pager";
	}
	
	// if the url pattern does not exist, we can return false
	if (!array_key_exists($url_pattern_name, $this->base->_conf['urls'])) {
		return false;
	}
	
	// prepare patterns and replacements
	$patterns = array();
	$replacements = array();
	foreach ($this->_args_and_patterns as $_arg => $_pattern) {
		$patterns[] = $_pattern;
		$replacements[] = $$_arg; 
	}
	ksort($patterns);
	ksort($replacements);
	
	// generate the url to the page from the configured url pattern in the sys.inc.php
	$system_url = str_replace($patterns, $replacements,
		$this->base->_conf['urls'][$url_pattern_name]);
	
	// append user supplied arguments to the system url
	if (count($user_supplied_args) > 1) {
		$system_url = $system_url.'?'.http_build_query($user_supplied_args, null, '&amp;');
	}
		
	// remove encoded ampersands from url if we're supposed to.
	// it's required if we're passing URLs to HTML_QuickForm because
	// there they will be encoded once again.
	if ($remove_amps) {
		$system_url = str_replace('&amp;', '&', $system_url);
	}
	
	// get url form Net_URL object
	return $system_url;
}

/**
 * Generates internal sitemap links using the provided arguments.
 * The required arguments depend on the page someone likes to link to.
 * Unknown arguments will be treated as user supplied arguments
 * and appended to the generated url. If the parameter remove_amps
 * is set to true, encoded ampersands will be converted to "clear
 * text" ampersands. If the parameter encode_system_url is set to
 * true the url will urlencoded. This takes account of the possibly
 * former converted ampersand.
 * 
 * @throws Utility_UrlGeneratorException
 * @param array Args
 * @param bool Remove encoded ampersands
 * @param bool Encode output url string
 * @return string
 */
public function generateSitemapLinks ($args = array())
{
	// input check
	if (!is_array($args)) {
		throw new Utility_UrlGeneratorException("Input for paramter args is expected to be an array");
	}
	
	// initialize internal arguments
	foreach ($this->_args_and_patterns as $_arg => $_replacement) {
		$$_arg = null;
	}
	
	// import args
	$user_supplied_args = array();
	foreach ($args as $_arg => $_value) {
		if (array_key_exists($_arg, $this->_args_and_patterns)) {
			$$_arg = $_value;
		} else {
			$user_supplied_args[$_arg] = $_value;
		}
	}
	
	// get project and all pages and cache them for reuse
	if (empty($this->_project)) {
		// get current project
		$PROJECT = load('Application:Project');
		$this->_project = $PROJECT->selectProject(WCOM_CURRENT_PROJECT);
	}
	if (empty($this->_pages)) {
		// get pages
		$PAGE = load('Content:Page');
		$this->_pages = $PAGE->selectPages();
	}
	
	// ok, the first step is to look for the page we'll link to
	$current_page = array();
	foreach ($this->_pages as $_page) {
		if ($_page['id'] == $page_id) {
			$current_page = $_page;
			break;
		}
	}
	
	// if we haven't found a current page yet, the user may have selected a page that
	// doesn't exist or has not defined any page at all to link to. if the page doesnt
	// exist (!empty($page)), we should skip here. if he has not defined any page at
	// all, we use the index page.
	if (empty($current_page) && !empty($page_id)) {
	} elseif (empty($current_page)) {
		foreach ($this->_pages as $_page) {
			if ($_page['index_page']) {
				$current_page = $_page;
			}
		}
	}
	
	// if there's not even a index page, we have so skip here
	if (empty($current_page)) {
		return false;
	}
	
	// if the current page is a WCOM_URL page, we can simply return the url saved in
	// the database.
	// if ($_page['page_type_name'] == 'WCOM_URL') {
	// 	return false;
	// }
	
	// set page_name_url
	$project_id = $this->_project['id'];
	$project_name = $this->_project['name_url'];
	$page_id = $current_page['id'];
	$page_name = $current_page['name_url'];
	
	// if the page type of the current page is WCOM_BLOG, we need to execute
	// some additional checks because we may have to link to the single
	// blog postings or to the archives.
	if ($current_page['page_type_name'] == 'WCOM_BLOG') {
		// if posting_id is set, we need to get the blog posting so that we've something
		// to fill the variables like posting_title with
		if (!empty($posting_id) && is_numeric($posting_id)) {
			$BLOGPOSTING = load('Content:BlogPosting');
			$blog_posting = $BLOGPOSTING->selectBlogPosting($posting_id);
			
			// if there's no blog posting, the url cannot be valid and we have to
			// return false
			if (empty($blog_posting)) {
				return false;
			}
			
			// fill the variables with the blog posting info
			$posting_title = $blog_posting['title_url'];
			$posting_year_added = $blog_posting['year_added'];
			$posting_month_added = $blog_posting['month_added'];
			$posting_day_added = $blog_posting['day_added'];
		}
	}
	
	// the next step to get an url is to look at the page type and the action so that we
	// can fetch the right url pattern from the sys.inc.php.
	$action = (empty($action) ? 'Index' : $action);
	$url_pattern_name = sprintf("%s_%s",
		strtolower(preg_replace(WCOM_REGEX_ACTION_TO_URL_PATTERN, "_\\1", $current_page['page_type_internal_name'])),
		strtolower(preg_replace(WCOM_REGEX_ACTION_TO_URL_PATTERN, "_\\1", $action))
	);
	
	// if the page where the link should point to is the index page, we have to use a different url pattern that
	// ommits the page name
	if ($current_page['index_page']) {
		$url_pattern_name = $url_pattern_name."_start";
	}
	
	// the same applies on link requests for a tag page
	if (array_key_exists('tag_word', $args) && !empty($args['tag_word'])) {
		$url_pattern_name = $url_pattern_name."_tag";
	}
	
	// the same applies on link requests for a pager
	if (array_key_exists('start', $args) && is_numeric($args['start'])) {
		$url_pattern_name = $url_pattern_name."_pager";
	}
	
	// if the url pattern does not exist, we can return false
	if (!array_key_exists($url_pattern_name, $this->base->_conf['urls'])) {
		return false;
	}
	
	// prepare patterns and replacements
	$patterns = array();
	$replacements = array();
	foreach ($this->_args_and_patterns as $_arg => $_pattern) {
		$patterns[] = $_pattern;
		$replacements[] = $$_arg; 
	}
	ksort($patterns);
	ksort($replacements);
	
	// generate the url to the page from the configured url pattern in the sys.inc.php
	$system_url = str_replace($patterns, $replacements,
		$this->base->_conf['urls'][$url_pattern_name]);
	
	// append user supplied arguments to the system url
	if (count($user_supplied_args) > 1) {
		$system_url = $system_url.'?'.http_build_query($user_supplied_args, null, '&amp;');
	}

	// filter http_host var
	$http_host = Base_Cnc::filterRequest($_SERVER['HTTP_HOST'], WCOM_REGEX_SERVER_HTTP_HOST);	

	// get the delivered host from the server globals and
	// prepend it to the system_url validate server response
	$system_url = preg_replace('=(^\/)=', 'http://'.$http_host.'${1}', $system_url);
	
	// get url form Net_URL object
	return $system_url;
}

// end of class
}

class Utility_UrlGeneratorException extends Exception { }

?>