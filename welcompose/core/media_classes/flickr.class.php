<?php

/**
 * Project: Welcompose
 * File: flickr.class.php
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
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton. Returns instance of the Media_Flickr object.
 * 
 * @return object
 */
function Media_Flickr ()
{ 
	if (Media_Flickr::$instance == null) {
		Media_Flickr::$instance = new Media_Flickr(); 
	}
	return Media_Flickr::$instance;
}

class Media_Flickr {
	
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
	 * Flickr API Key
	 *
	 * @var string
	 */
	public $_api_key = null;
	
	/**
	 * Flickr client instance
	 *
	 * @var object
	 */
	public $flickr_client = null;
	
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
		
		// get api key
		$this->_api_key = $this->base->_conf['flickr']['api_key'];
		if (empty($this->_api_key) || !preg_match(WCOM_REGEX_ALPHANUMERIC, $this->_api_key)) {
			throw new Media_FlickrException("No Flickr API key found");
		}
		
		
		// prepare flickrClient options
		$options = array(
			'cache_encrypt' => (($this->base->_conf['flickr']['cache_encrypt']) ? true : false),
			'cache_encrypt_passphrase' => $this->base->_conf['environment']['app_key']
		);
		
		// create new flickrClient instance
		$this->flickr_client = new flickrClient($this->base->_conf['flickr']['cache_dir'],
			$options);
	} catch (Exception $e) {
		// trigger error
		printf('%s on Line %u: Unable to start class. Reason: %s.', $e->getFile(),
			$e->getLine(), $e->getMessage());
		exit;
	}
}

/**
 * Implemens flickr.people.findByUsername and returns user's nsid and
 * name. Takes the username as first argument. Returns hash with the
 * following elements:
 * 
 * <ul>
 * <li>user_id: User's nsid</li>
 * <li>username: User's username</li>
 * </ul>
 * 
 * @throws Media_FlickrException
 * @param string Username
 * @return array
 */
public function peopleFindByUsername ($username)
{
	// input check
	if (empty($username) || !preg_match(WCOM_REGEX_FLICKR_SCREENNAME, $username)) {
		throw new Media_FlickrException("Invalid username supplied");
	}
	
	// prepare args
	$args = array(
		'api_key' => $this->_api_key,
		'username' => $username
	);
	
	// send flickr request
	$response = $this->flickr_client->sendFlickrRequest('flickr.people.findByUsername',
		$args);
	
	// get xml from response and pipe it to simplexml
	$sx = simplexml_load_string($response);
	
	// get nsid and username from response
	$user_id = Base_Cnc::filterRequest($this->flickrValue($sx['nsid']),
		WCOM_REGEX_FLICKR_NSID);
	$username = Base_Cnc::filterRequest($this->flickrValue($sx->username),
		WCOM_REGEX_FLICKR_SCREENNAME);
	
	// pack and return array
	return array(
		'user_id' => $user_id,
		'username' => $username
	); 
}

/**
 * Implements flickr.urls.getUserPhotos and returns user's nsid and the url
 * to its photos. Takes the nsid as first argument. Returns hash will the
 * following contents:
 *
 * <ul>
 * <li>user_id: User's nsid</li>
 * <li>url: Url to user's photos</li>
 * </ul>
 * 
 * @throws Media_FlickrException
 * @param string User's nsid
 * @return array
 */
public function urlsGetUserPhotos ($user_id)
{
	// input check
	if (!preg_match(WCOM_REGEX_FLICKR_NSID, $user_id)) {
		throw new Media_FlickrException("Invalid nsid supplied");
	}
	
	// prepare args 
	$args = array(
		'api_key' => $this->_api_key,
		'user_id' => $user_id
	);
	
	// send flickr request
	$response = $this->flickr_client->sendFlickrRequest('flickr.urls.getUserPhotos',
		$args);
	
	// get xml from response and pipe it to simplexml
	$sx = simplexml_load_string($response);
	
	// get nsid and url from response
	$user_id = Base_Cnc::filterRequest($this->flickrValue($sx['nsid']),
		WCOM_REGEX_FLICKR_NSID);
	$url = Base_Cnc::filterRequest($this->flickrValue($sx['url']),
		WCOM_REGEX_FLICKR_URL);
	
	// pack and return array
	return array(
		'user_id' => $user_id,
		'url' => $url
	);
}

/**
 * Implements flickr.photos.search and returns an array with the found
 * photos. Takes an array with search options as first argument. The
 * function supports the following official params:
 * 
 * <ul>
 * <li>user_id</li>
 * <li>tags</li>
 * <li>text</li>
 * <li>extra</li>
 * <li>per_page</li>
 * <li>page</li>
 * <li>tag_mode</li>
 * <li>sort</li>
 * <li>privacy_filter</li>
 * </ul>
 *
 * The options {min,max}_{upload,taken}_date can be used through the
 * options timeframe_{upload,taken} that acceppt the timeframe keywords
 * as returned by Utility_Helper::getTimeframes().
 * 
 * @throws Media_FlickrException
 * @param array Search params
 * @return array Photos
 */
public function photosSearch ($search_params)
{
	// input check
	if (!is_array($search_params)) {
		throw new Media_FlickrException("Input for parameter search_params is expected to be an array");
	}
	
	// load helper class
	$HELPER = load('Utility:Helper');
	
	// prepare flickr params using the supplied search params
	$flickr_params = array();
	foreach ($search_params as $_key => $_value) {
		switch ((string)$_key) {
			case 'user_id':
					if (empty($_value) || !preg_match(WCOM_REGEX_FLICKR_NSID, $_value)) {
						throw new Media_FlickrException("Invalid nsid supplied");
					}
					$flickr_params[$_key] = (string)$_value;
				break;
			case 'tags':
			case 'text':
			case 'extra':
					$flickr_params[$_key] = strip_tags((string)$_value);
				break;
			case 'per_page':
			case 'page':
					$flickr_params[$_key] = (int)$_value;
				break;
			case 'tag_mode':
					$flickr_params[$_key] = (($_value == 'all') ? 'all' : 'any');
				break;
			case 'timeframe_upload':
					$dates = $HELPER->datesForTimeframe($_value);
					$flickr_params['min_upload_date'] = $dates['timeframe_start'];
					$flickr_params['max_upload_date'] = $dates['timeframe_end'];
				break;
			case 'timeframe_taken':
					$dates = $HELPER->datesForTimeframe($_value);
					$flickr_params['min_taken_date'] = $dates['timeframe_start'];
					$flickr_params['max_taken_date'] = $dates['timeframe_end'];
				break;
			case 'sort':
					switch ((string)$_value) {
						case 'date-posted-desc':
						case 'date-posted-asc':
						case 'date-posted-desc':
						case 'date-taken-asc':
						case 'date-taken-desc':
						case 'interestingness-desc':
						case 'interestingness-asc':
						case 'relevance':
								$flickr_params[$_key] = (string)$_value;
							break;
					}
				break;
			case 'privacy_filter':
					switch ((int)$_value) {
						case 1:
						case 2:
						case 3:
						case 4:
						case 5:
								$flickr_params[$_key] = (int)$_value;
							break;
					}
				break;
		}
	}
	
	// append api key to list of flickr params
	$flickr_params['api_key'] = $this->_api_key;
	
	// send flickr request
	$response = $this->flickr_client->sendFlickrRequest('flickr.photos.search',
		$flickr_params);
	
	// get xml from response and pipe it to simplexml
	$sx = simplexml_load_string($response);
	
	// get metadata from response
	$page = Base_Cnc::filterRequest($this->flickrValue($sx['page']), WCOM_REGEX_FLICKR_NSID);
	$pages = Base_Cnc::filterRequest($this->flickrValue($sx['pages']), WCOM_REGEX_NUMERIC);
	$perpage = Base_Cnc::filterRequest($this->flickrValue($sx['perpage']), WCOM_REGEX_NUMERIC);
	$total = Base_Cnc::filterRequest($this->flickrValue($sx['total']), WCOM_REGEX_NUMERIC);
	
	// get pictures from response
	$photos = array();
	foreach ($sx->xpath('/photos/photo') as $_photo) {
		$photos[] = array(
			'id' => Base_Cnc::filterRequest($this->flickrValue($_photo['id']), WCOM_REGEX_NUMERIC),
			'owner' => Base_Cnc::filterRequest($this->flickrValue($_photo['owner']), WCOM_REGEX_FLICKR_NSID),
			'secret' => Base_Cnc::filterRequest($this->flickrValue($_photo['secret']), WCOM_REGEX_ALPHANUMERIC),
			'server' => Base_Cnc::filterRequest($this->flickrValue($_photo['server']), WCOM_REGEX_NUMERIC),
			'title' => $this->flickrValue($_photo['title']),
			'ispublic' => Base_Cnc::filterRequest($this->flickrValue($_photo['ispublic']), WCOM_REGEX_ZERO_OR_ONE),
			'isfriend' => Base_Cnc::filterRequest($this->flickrValue($_photo['isfriend']), WCOM_REGEX_ZERO_OR_ONE),
			'isfamily' => Base_Cnc::filterRequest($this->flickrValue($_photo['isfamily']), WCOM_REGEX_ZERO_OR_ONE)
		);
	}
	
	// pack everything in an array and return it
	return array(
		'page' => $page,
		'pages' => $pages,
		'perpage' => $perpage,
		'total' => $total,
		'photos' => $photos
	);
}

/**
 * Implements flickr.photosets.getList and returns list of photosets.
 * Takes the user's nsid as first argument. Returns array with list of
 * photosets.
 *
 * @throws Media_FlickrException
 * @param string User's nsid
 * @return array
 */
public function photosetsGetList ($user_id)
{
	// input check
	if (!preg_match(WCOM_REGEX_FLICKR_NSID, $user_id)) {
		throw new Media_FlickrException("Invalid nsid supplied");
	}
	
	// prepare args
	$args = array(
		'api_key' => $this->_api_key,
		'user_id' => $user_id
	);
	
	// send flickr request
	$response = $this->flickr_client->sendFlickrRequest('flickr.photosets.getList',
		$args);
	
	// get xml from response and pipe it to simplexml
	$sx = simplexml_load_string($response);
	
	// get metadata from response
	$cancreate = Base_Cnc::filterRequest($this->flickrValue($sx['cancreate']), WCOM_REGEX_ZERO_OR_ONE);
	
	// get photosets from response
	$photosets = array();
	foreach ($sx->xpath('/photosets/photoset') as $_photoset) {
		$photosets[] = array(
			'id' => Base_Cnc::filterRequest($this->flickrValue($_photoset['id']), WCOM_REGEX_NUMERIC),
			'primary' => Base_Cnc::filterRequest($this->flickrValue($_photoset['primary']), WCOM_REGEX_NUMERIC),
			'secret' => Base_Cnc::filterRequest($this->flickrValue($_photoset['secret']), WCOM_REGEX_ALPHANUMERIC),
			'server' => Base_Cnc::filterRequest($this->flickrValue($_photoset['server']), WCOM_REGEX_NUMERIC),
			'photos' => Base_Cnc::filterRequest($this->flickrValue($_photoset['photos']), WCOM_REGEX_NUMERIC),
			'title' => $this->flickrValue($_photoset->title),
			'description' => $this->flickrValue($_photoset->description)
		);
	}
	
	// return photoset array
	return $photosets;
}

/**
 * Implements flickr.photosets.getPhotos. Takes the photoset id as first
 * argument, a comma-delimited list of extra information to fetch for each
 * returned record as second argument, the  privacy_filter value as third
 * argument, the amount of photos to return per page as fourth argument and
 * the page to return as fifth argument. Returns array.
 *
 * @throws Media_FlickrException
 * @param int Photoset id
 * @param string List of extra fields
 * @param int Privacy filter setting
 * @param int Photos per page
 * @param itn Page id
 * @return array
 */
public function photosetsGetPhotos ($photoset_id, $extras = null, $privacy_filter = null,
	$per_page = 500, $page = 1)
{
	// input check
	if (empty($photoset_id) || !preg_match(WCOM_REGEX_NUMERIC, $photoset_id)) {
		throw new Media_FlickrException("Invalid photoset_id supplied");
	}
	if (!is_null($privacy_filter) && ($privacy_filter < 1 || $privacy_filter > 5)) {
		throw new Media_FlickrException("Invalid privacy_filter supplied");
	}
	if (!preg_match(WCOM_REGEX_NUMERIC, $per_page)) {
		throw new Media_FlickrException("Invalid input for parameter per_page supplied");
	}
	if (!preg_match(WCOM_REGEX_NUMERIC, $page)) {
		throw new Media_FlickrException("Invalid input for parameter page supplied");
	}
	
	// prepare args
	$args = array();
	$args['api_key'] = $this->_api_key;
	
	// append option args
	if (!is_null($photoset_id)) {
		$args['photoset_id'] = $photoset_id;
	}
	if (!is_null($extras)) {
		$args['extras'] = $extras;
	}
	if (!is_null($privacy_filter)) {
		$args['privacy_filter'] = $privacy_filter;
	}
	if (!is_null($per_page)) {
		$args['per_page'] = $per_page;
	}
	if (!is_null($page)) {
		$args['page'] = $page;
	}
	
	// send flickr request
	$response = $this->flickr_client->sendFlickrRequest('flickr.photosets.getPhotos',
		$args);
	
	// get xml from response and pipe it to simplexml
	$sx = simplexml_load_string($response);
	
	// get metadata from response
	$id = Base_Cnc::filterRequest($this->flickrValue($sx['id']), WCOM_REGEX_NUMERIC);
	$primary = Base_Cnc::filterRequest($this->flickrValue($sx['primary']), WCOM_REGEX_NUMERIC);
	$owner = Base_Cnc::filterRequest($this->flickrValue($sx['owner']), WCOM_REGEX_FLICKR_NSID);
	$ownername = Base_Cnc::filterRequest($this->flickrValue($sx['ownername']), WCOM_REGEX_FLICKR_SCREENNAME);
	$page = Base_Cnc::filterRequest($this->flickrValue($sx['page']), WCOM_REGEX_NUMERIC);
	$per_page = Base_Cnc::filterRequest($this->flickrValue($sx['per_page']), WCOM_REGEX_NUMERIC);
	$pages = Base_Cnc::filterRequest($this->flickrValue($sx['pages']), WCOM_REGEX_NUMERIC);
	$total = Base_Cnc::filterRequest($this->flickrValue($sx['total']), WCOM_REGEX_NUMERIC);
	
	// get photos from response
	$photos = array();
	foreach ($sx->xpath('/photoset/photo') as $_photo) {
		$photos[] = array(
			'id' => Base_Cnc::filterRequest($this->flickrValue($_photo['id']), WCOM_REGEX_NUMERIC),
			'secret' => Base_Cnc::filterRequest($this->flickrValue($_photo['secret']), WCOM_REGEX_ALPHANUMERIC),
			'server' => Base_Cnc::filterRequest($this->flickrValue($_photo['server']), WCOM_REGEX_NUMERIC),
			'title' => $this->flickrValue($_photo['title']),
			'isprimary' => Base_Cnc::filterRequest($this->flickrValue($_photo['isprimary']), WCOM_REGEX_NUMERIC),
			'license' => Base_Cnc::filterRequest($this->flickrValue($_photo['license']), WCOM_REGEX_NUMERIC),
			'dateupload' => Base_Cnc::filterRequest($this->flickrValue($_photo['dateupload']), WCOM_REGEX_NUMERIC),
			'datetaken' => Base_Cnc::filterRequest($this->flickrValue($_photo['datetaken']), WCOM_REGEX_DATETIME),
			'datetakengranularity' =>
				Base_Cnc::filterRequest($this->flickrValue($_photo['datetakengranularity']), WCOM_REGEX_NUMERIC),
			'ownername' => Base_Cnc::filterRequest($this->flickrValue($_photo['ownername']), WCOM_REGEX_ALPHANUMERIC),
			'iconserver' => Base_Cnc::filterRequest($this->flickrValue($_photo['iconserver']), WCOM_REGEX_NUMERIC),
			'originalformat' => Base_Cnc::filterRequest($this->flickrValue($_photo['originalformat']), WCOM_REGEX_ALPHANUMERIC),
			'lastupdate' => Base_Cnc::filterRequest($this->flickrValue($_photo['lastupdate']), WCOM_REGEX_NUMERIC)
		);
	}
	
	// pack everythin into an array and return it
	return array(
		'id' => $id,
		'primary' => $primary,
		'owner' => $owner,
		'ownername' => $ownername,
		'page' => $page,
		'per_page' => $per_page,
		'pages' => $pages,
		'total' => $total,
		'photos' => $photos
	);
}

/**
 * Creates page index from the value of total existing pages provieded
 * by Flickr. Takes the total page count as first argument. Returns array.
 * 
 * @throws Media_FlickrException
 * @param int Page count
 * @return array
 */
public function _flickrPageIndex ($pages)
{
	// input check
	if (!is_numeric($pages)) {
		throw new Media_FlickrException("Input for parameter pages is not numeric");
	}
	
	// initialize index
	$index = array();
	
	if (empty($pages)) {
		return array();
	} else {
		for ($i=1;$i<$pages+1;$i++) {
			$index[] = array(
				'page' => $i,
				'last' => $i - 1,
				'self' => $i,
				'next' => $i + 1,
				'total_pages' => $pages
			);
		}
		
		foreach ($index as $_key => $_value) {
			if ($_value['last'] < 1) {
				$index[$_key]['last'] = null;
			}
			if ($_value['page'] == $_value['total_pages']) {
				$index[$_key]['next'] = null;
			}
		}
	}
	
	return $index;
}

/**
 * Prepares data received from flickr for internal processing. The input
 * will be casted to string, converted from utf8 to iso and all tags
 * will be stripped.
 *
 * OBSOLET WITH 0.9.0 (utf-8)
 *
 * @param mixed
 * @return string
 */
protected function flickrValue ($value)
{
	// cast to string
	$value = (string)$value;
	
	// decode utf8
	//$value = utf8_decode($value);
	
	// strip tags
	$value = strip_tags($value);
	
	return trim($value);
}

// end of class
}

class Media_FlickrException extends Exception { }

class flickrClient {
	
	/**
	 * Container for the PEAR Cache_Lite object
	 * 
	 * @var object
	 */
	protected $cache = null;
	
	/**
	 * Container for the PEAR Crypt_RC4 object
	 * 
	 * @var object
	 */
	protected $rc4 = null;
	
	/**
	 * Whether the args and other input is encoded using
	 * ISO-8859-1 or not. Then it will be automatically
	 * converted to UTF-8.
	 * 
	 * @var bool
	 */
	protected $_iso_input = false;
	
	/**
	 * Flickr API endpoint URL
	 * 
	 * @var string
	 */
	protected $_endpoint = 'http://api.flickr.com/services/rest/';
	
	/**
	 * Options array passed to the constructor of HTTP_Request
	 *
	 * @var array 
	 */
	protected $_http_request_options = array(
		'method' => 'GET',
		'http' => '1.1'
	);
	
	/**
	 * Whether to cache the flickr responses or not
	 *
	 * @var bool
	 */
	protected $_cache = true;
	
	/**
	 * Directory where to cache the flickr responses
	 *
	 * @var string
	 */
	protected $_cache_dir = null;
	
	/**
	 * Lifetime of the flickr cache in seconds
	 *
	 * @var int
	 */
	protected $_cache_lifetime = 1800;
	
	/**
	 * Whether to encrypt the cache files or not
	 *
	 * @var bool
	 */
	protected $_cache_encrypt = false;
	
	/**
	 * Passphrase to use for cache encryption
	 *
	 * @var string
	 */
	protected $_cache_encrypt_passphrase = null;

/**
 * Creates new flickrClient instance. Takes the cache dir
 * to use as first argument and an options array as second
 * argument. Available options:
 *
 * <ul>
 * <li>cache_encrypt, bool: Whether to encrypt the cache files or not</li>
 * <li>cache_encrypt_passphrase, string: Passphrase to use for encryption.
 * Required when using encryption.</li>
 * </ul>
 *
 * @throws flickrClientException
 * @param string Path to cache dir
 * @param array Options array
 */
public function __construct ($cache_dir, $options = array())
{
	// input check
	if (empty($cache_dir) || !is_scalar($cache_dir)) {
		throw new flickrClientException("cache_dir must be a non-empty scalar value");
	}
	if (!is_array($options)) {
		throw new flickrClientException("Parameter options must be an array");
	}
	
	// set new cache dir
	if (!is_dir($cache_dir)) {
		throw new flickrClientException("Flickr cache dir does not exist");
	}
	if (!is_writable($cache_dir)) {
		throw new flickrClientException("Flickr cache dir is not writable");
	}
	$this->_cache_dir = $cache_dir;
	
	// load PEAR's HTTP_Request
	require('HTTP/Request2.php');
	
	// import options
	if (array_key_exists('cache_encrypt', $options) && is_bool($options['cache_encrypt'])) {
		$this->_cache_encrypt = $options['cache_encrypt'];
	}
	if (array_key_exists('cache_encrypt_passphrase', $options) && is_scalar($options['cache_encrypt_passphrase'])) {
		$this->_cache_encrypt_passphrase = $options['cache_encrypt_passphrase'];
	}
}

/** 
 * Sends flickr request. Takes the API method as first argument, a
 * key=>value with args as second argument. Returns the message in REST
 * format.
 *
 * @throws flickrClientException
 * @param string Method name
 * @param array Method args
 * @return string
 */
public function sendFlickrRequest ($method, $args)
{
	// input check
	if (empty($method) || !is_scalar($method)) {
		throw new flickrClientException("Method must be a non-empty scalar value");
	}
	if (!is_array($args)) {
		throw new flickrClientException("Args must be an array");
	}
	
	// add method name to the list of args 
	$args['method'] = $method;
	
	// sort the args so that we don't get a cache miss because of different
	// parameter ordering
	ksort($args);
	
	// create new HTTP_Request object
	$flickr_request = new HTTP_Request($this->_endpoint, $this->_http_request_options);
	
	// add args to query string
	foreach ($args as $_key => $_value) {
		// encode key and value if required
		if ($this->_iso_input) {
			$_key = utf8_encode($_key);
			$_value = utf8_encode($_value);
		}
		
		// add arg to query string
		$flickr_request->addQueryString($_key, $_value);
	}
	
	// if we don't cache or if there's no cached response we have to send
	// a new request
	if (!$this->_cache || !$this->flickrRequestIsCached($flickr_request)) {
		
		// send request
		$response = $flickr_request->sendRequest();
		if ($response instanceof PEAR_Error) {
			throw new flickrClientException("Unable to send flickr request");
		}
		
		// get the response body
		$flickr_response = $flickr_request->getResponseBody();
		
		// test the response body for errors
		if ($flickr_request->getResponseCode() != '200' || !$this->flickrRequestWasSuccessfull($flickr_response)) {
			$error_msg = $this->getFlickrRequestError($flickr_response);
			throw new flickrClientException("Flickr request failed ".$error_msg);
		}
		
		// cache the response body if required
		if ($this->_cache) {
			$this->cacheFlickrResponse($flickr_request, $flickr_response);
		}
	} else {
		// get cached flickr response
		$flickr_response = $this->getCachedFlickrRequest($flickr_request);
	}
	
	//  extract xml payload
	$sx = simplexml_load_string($flickr_response);
	$payload = $sx->xpath('/rsp/*');
	
	// turn the payload to xml again and return it
	return $payload[0]->asXML();
}

/**
 * Tests if flickr request was successfull. Takes the
 * response body as first argument. Returns bool.
 * 
 * @throws flickrClientException
 * @return 
 */
protected function flickrRequestWasSuccessfull ($response)
{
	if (empty($response) || !is_scalar($response)) {
		throw new flickrClientException("Response must be a non-empty scalar value");
	}
	
	// create simplexml object from flickr response
	$sx = simplexml_load_string($response);
	
	// evaluate result
	if ((string)$sx['stat'] == 'ok') {
		return true;
	} else {
		return false;
	}
}

/**
 * Extracts error message from flickr response. Returns string.
 *
 * @throws flickrClientException
 * @param string Response body
 * @return string
 */
protected function getFlickrRequestError ($response)
{
	if (empty($response) || !is_scalar($response)) {
		throw new flickrClientException("Response must be a non-empty scalar value");
	}
	
	// create simplexml object from flickr response
	$sx = simplexml_load_string($response);
	
	// extract error message
	$error_msg = htmlspecialchars((string)$sx->err['msg']);
	
	// return error message
	return $error_msg;
}

/**
 * Tests if there's a cached result for the current request.
 * Takes the HTTP_Request object (where the cache id will be
 * extracted from) as first argument. Returns bool.
 *
 * @throws flickrClientException
 * @param object HTTP_Request object
 * @return bool
 */
protected function flickrRequestIsCached ($http_request_object)
{
	// input check
	if (!($http_request_object instanceof HTTP_Request)) {
		throw new flickrClientException("http_request_object is not an HTTP_Client instance");
	}
	
	// init PEAR's Cache_Lite
	$this->loadCacheLite();
	
	// get cached flickr request
	$result = $this->getCachedFlickrRequest($http_request_object);
	
	// evaluate the result
	if ($result === false) {
		return false;
	} else {
		return true;
	}
}

/**
 * Returns cached response body from cache. Taktes the HTTP_Request
 * object (where the cache id will be extracted from) as first
 * argument. Returns string on success or false on failure.
 * 
 * Test the availability of the cache file using
 * flickrClient::flickrRequestIsCached before using this method.  
 *
 * @throws flickrClientException
 * @param object HTTP_Request object
 * @return mixed 
 */
protected function getCachedFlickrRequest ($http_request_object)
{
	// input check
	if (!($http_request_object instanceof HTTP_Request)) {
		throw new flickrClientException("http_request_object is not an HTTP_Client instance");
	}
	
	// init PEAR's Cache_Lite
	$this->loadCacheLite();
	
	// get the cached response body
	$cached_response_body = $this->cache->get($this->flickrResponseCacheId($http_request_object));
	
	// if the response body is empty, return false
	if (empty($cached_response_body)) {
		return false;
	}
	
	// decrypt the cached response body if required
	if ($this->_cache_encrypt) {
		$cached_response_body = base64_decode($cached_response_body);
		$this->rc4->decrypt($cached_response_body);
	}
	
	return $cached_response_body;
}

/**
 * Caches the Flickr response as supplied by the http_response body.
 * Takes the HTTP_Request object as first argument, the response body
 * as second argument. Returns true on success.
 * 
 * @throws flickrClientException
 * @param object HTTP_Requst object
 * @param string Response body
 * @return bool
 */
protected function cacheFlickrResponse ($http_request_object, $http_response_body)
{
	// input check
	if (!($http_request_object instanceof HTTP_Request)) {
		throw new flickrClientException("http_request_object is not an HTTP_Client instance");
	}
	if (!is_scalar($http_response_body)) {
		throw new flickrClientException("http_response_body is not scalar");
	}
	
	// init PEAR's Cache_Lite
	$this->loadCacheLite();
	
	// encrypt the response body if required
	if ($this->_cache_encrypt) {
		$this->rc4->crypt($http_response_body);
		$http_response_body = base64_encode($http_response_body);
	}
	
	// save response body to cache
	if ($this->cache->save($http_response_body, $this->flickrResponseCacheId($http_request_object)) === false) {
		throw new flickrClientException("Failed to cache the response body");
	}
	
	return true;
}

/**
 * Extracts the cache id from the HTTP_Request object.
 *
 * @return string
 */
protected function flickrResponseCacheId ($http_request_object)
{
	// input check
	if (!($http_request_object instanceof HTTP_Request)) {
		throw new flickrClientException("http_request_object is not an HTTP_Client instance");
	}
	
	// prepend the prefix "encrypted" so that the whole flickr thing doesn't crash
	// if somebody changes the flickrClient::_cache_encrypt setting during operation
	// without purging the cache 
	if ($this->_cache_encrypt) {
		return "encrypted_".$http_request_object->getUrl(null);
	} else {
		return $http_request_object->getUrl(null);
	}
}

/**
 * Creates new Cache_Lite instance if there isn't one yet.
 */
private function loadCacheLite ()
{
	if (!is_a($this->cache, 'Cache_Lite')) {
		// load Cache_Lite package
		require ("Cache/Lite.php");
		
		// prepare cache dir
		if (substr($this->_cache_dir, -1, 1) != '/') {
			$this->_cache_dir = $this->_cache_dir.'/';
		}
		
		// prepare options array
		$options = array(
			'cacheDir' => $this->_cache_dir,
			'lifeTime' => $this->_cache_lifetime,
			'automaticCleaningFactor' => 200
		);
		
		// create new Cache_Lite instance
		$this->cache = new Cache_Lite($options);
		
		// load PEAR::Crypt_RC4 if encryption is enabled
		if ($this->_cache_encrypt) {
			// load PEAR::Crypt_RC42
			require('Crypt/Rc42.php');
			
			// input check
			if (empty($this->_cache_encrypt_passphrase) || !is_scalar($this->_cache_encrypt_passphrase)) {
				throw new flickrClientException("No useable passphrase for cache encryption found");
			}
			
			// create instance of PEAR::Crypt_RC4
			$this->rc4 = new Crypt_RC4($this->_cache_encrypt_passphrase);
		}
	}
}

}

class flickrClientException extends Exception { }

?>