<?php

/**
 * Project: Oak
 * File: urlgenerator.class.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-3.0.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

class Utility_UrlGenerator {
	
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
		'posting_day_added' => '<posting_day_added>'
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
 * Singleton. Returns instance of the Utility_UrlGenerator object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Utility_UrlGenerator::$instance == null) {
		Utility_UrlGenerator::$instance = new Utility_UrlGenerator(); 
	}
	return Utility_UrlGenerator::$instance;
}

/**
 * Generates internal link using the provieded arguments. The
 * required arguments depend on the page someone likes to link to.
 * Unknown arguments will be treated as user supplied arguments
 * and appended to the generated url.
 * 
 * @throws Utility_UrlGeneratorException
 * @param array Args
 * @return string
 */
public function generateInternalLink ($args = array())
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
		$this->_project = $PROJECT->selectProject(OAK_CURRENT_PROJECT);
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
	
	// set page_name_url
	$project_id = $this->_project['id'];
	$project_name = $this->_project['name_url'];
	$page_id = $current_page['id'];
	$page_name = $current_page['name_url'];
	
	// if the page type of the current page is OAK_BLOG, we need to execute
	// some additional checks because we may have to link to the single
	// blog postings or to the archives.
	if ($current_page['page_type_name'] == 'OAK_BLOG') {
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
		strtolower(preg_replace(OAK_REGEX_ACTION_TO_URL_PATTERN, "_\\1", $current_page['page_type_internal_name'])),
		strtolower(preg_replace(OAK_REGEX_ACTION_TO_URL_PATTERN, "_\\1", $action))
	);
	
	// if the page where the link should point to is the index page, we have to use a different url pattern that
	// ommits the page name
	if ($current_page['index_page']) {
		$url_pattern_name = $url_pattern_name."_start";
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
	require_once('Net/URL.php');
	$output_url = new Net_URL($system_url);
	foreach ($user_supplied_args as $_arg => $_value) {
		$output_url->addQueryString($_arg, $_value);
	}
	
	// get url form Net_URL object
	return $output_url->getURL();
}

// end of class
}

class Utility_UrlGeneratorException extends Exception { }

?>