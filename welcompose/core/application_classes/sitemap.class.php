<?php

/**
 * Project: Welcompose
 * File: sitemap.class.php
 * 
 * Copyright (c) 2008 creatics
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id$
 * 
 * @copyright 2008 creatics, Olaf Gleba
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton for Application_Sitemap.
 * 
 * @return object
 */
function Application_Sitemap ()
{
	if (Application_Sitemap::$instance == null) {
		Application_Sitemap::$instance = new Application_Sitemap(); 
	}
	return Application_Sitemap::$instance;
}

class Application_Sitemap {
	
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
	 * Container to cache the info about all pages.
	 *
	 * @var array
	 */
	protected $_pages = array();
	
	/**
	 * Default xml sitemap file
	 *
	 * @var string
	 */
	protected $_default_output_file = '../../tmp/sitemaps/sitemap.xml';
	
	/**
	 * Default gzip flatten sitemap file
	 *
	 * @var string
	 */
	protected $_default_gzip_output_file = '../../tmp/sitemaps/sitemap.xml.gz';

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
 * Generates the sitemap. Write file to file system. 
 * If param compress is set we also write a gunzip compressed
 * sitemap file. At least we apply some system rights to the 
 * generated files. 
 * 
 * @throws Application_SitemapException
 * @param int Compress true or false  
 */
public function generateSitemap ($compress)
{
	
	// input check
	if (!is_int($compress)) {
		throw new Application_SitemapException("Input for paramter compress is expected to be 0 or 1");
	}
	
	// check if file is writable when exits
	if(file_exists($this->_default_output_file) && !is_writable($this->_default_output_file)) {
		throw new Application_SitemapException('We have no write access to the sitemap file');
	}
	
	// provide params
	// with draft is set to null we only include public pages
	// with protect is set we spare protected pages out
	$select_params = array(
		'draft' => null,
		'protect' => 1,
		'exclude' => 1
	);	
	
	// get all pages without status protect and draft and cache them
	if (empty($this->_pages)) {
		// get pages
		$PAGE = load('Content:Page');
		$this->_pages = $PAGE->selectPages($select_params);
	}

	// load url generator
	$URLGENERATOR = load('Utility:UrlGenerator');
	
	// load blogposting class
	$BLOGPOSTING = load('content:blogposting');	
	
	// prepare file data
	$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
	$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\r\n";
	
	// build url node for common pages
	foreach ($this->_pages as $_page) {
		// spare external pages out completely
		if ($_page['page_type_name'] != 'WCOM_URL') {
			$xml .= '  <url>'."\r\n";
			$xml .= '    <loc>'.
							$URLGENERATOR->generateExternalLink(array('page_id' => $_page['id'])
							)
						.'</loc>'."\r\n";
			$xml .= '    <changefreq>'.$_page['sitemap_changefreq'].'</changefreq>'."\r\n";
			$xml .= '    <priority>'.$_page['sitemap_priority'].'</priority>'."\r\n";
			$xml .= '  </url>'."\r\n";				

			// we have to differ here because we need 
			// additional params (posting_id, action)
			// to be able building blog posting urls
			if ($_page['page_type_name'] == 'WCOM_BLOG') {
					
				// get single blog posting
				 $posting = $BLOGPOSTING->selectBlogPostings(array('page' => $_page['id'], 'draft' => '0'));
				
				// build url node for blog postings
				foreach ($posting as $_posting) {
					$xml .= '  <url>'."\r\n";
					$xml .= '    <loc>'.
									$URLGENERATOR->generateExternalLink(array('page_id' => $_page['id'],
										'posting_id' => $_posting['id'],
										'action' => 'Item')
									)
								.'</loc>'."\r\n";
					$xml .= '    <changefreq>'.$_page['sitemap_changefreq'].'</changefreq>'."\r\n";
					$xml .= '    <priority>'.$_page['sitemap_priority'].'</priority>'."\r\n";
					$xml .= '  </url>'."\r\n";
				}
			}
		}
	}
	$xml .= '</urlset>';
	
	// set appropriate header
	header('content-type: text/xml; charset= utf-8');
		 
	// write file to disk
	file_put_contents($this->_default_output_file, $xml);
	
	// load chmod class
	$CHMOD = load('Utility:Chmod');
	
	// set proper system rights at least 
	$CHMOD->chmodFileDefault($this->_default_output_file);
		
	// we want a gunzip compressed file also
	if ($compress > 0) {		
		// gz encode and write file to filesystem
		$this->writeGzip($xml);
	}
}

/**
 * Writes the gunzip encoded sitemap to the file system
 * 
 * @throws Application_TextConverterException
 * @param string Build sitemap data
 */
protected function writeGzip ($data)
{	
	// encode the target content
	$gzdata = gzencode($data, 9);
	
	// open a pointer to the target file
	$fp = fopen($this->_default_gzip_output_file, 'w+');
	
	fwrite($fp, $gzdata);
	fclose($fp);
		
	// write gzip file to filesystem
	file_put_contents($this->_default_gzip_output_file, $fp);

	// load chmod class
	$CHMOD = load('Utility:Chmod');
		
	// set proper system rights at least 
	$CHMOD->chmodFileDefault($this->_default_gzip_output_file);
}

// end of class
}

class Application_SitemapException extends Exception { }

?>