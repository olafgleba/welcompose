<?php

/**
 * Project: Oak
 * File: flickr.class.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License
 * http://www.opensource.org/licenses/osl-2.1.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

class Media_Flickr {
	
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
	 * Flickr API Key
	 * @var string
	 */
	public $_api_key = null;
	
	/**
	 * XML_RPC_Client instance
	 * @var object
	 */
	public $rpc_client = null;
	
	/**
	 * Request timeout in seconds for flickr request
	 * @var int
	 */
	protected $_timeout = 10;
	
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
		
		// get api key
		$this->_api_key = $this->base->_conf['flickr']['api_key'];
		if (empty($this->_api_key) || !preg_match(OAK_REGEX_ALPHANUMERIC, $this->_api_key)) {
			throw new Media_FlickrException("No Flickr API key found");
		}
		
		// load XML_RPC_Client class
		require_once('XML/RPC.php');
		
		// create new xml rpc client
		$this->rpc_client = new XML_RPC_Client('/services/xmlrpc', 'api.flickr.com');
		if (!($this->rpc_client instanceof XML_RPC_Client)) {
			throw new Media_FlickrException("Unable to start XML-RPC client");
		}
		
	} catch (Exception $e) {
		// trigger error
		printf('%s on Line %u: Unable to start class. Reason: %s.', $e->getFile(),
			$e->getLine(), $e->getMessage());
		exit;
	}
}

/**
 * Singleton. Returns instance of the Media_Flickr object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Media_Flickr::$instance == null) {
		Media_Flickr::$instance = new Media_Flickr(); 
	}
	return Media_Flickr::$instance;
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
 * @return array
 */
public function peopleFindByUsername ($username)
{
	// input check
	if (empty($username) || !preg_match(OAK_REGEX_FLICKR_SCREENNAME, $username)) {
		throw new Media_FlickrException("Invalid username supplied");
	}
	
	// prepare message struct
	$struct = new XML_RPC_Value(array(
		'api_key' => new XML_RPC_Value($this->_api_key),
		'username' => new XML_RPC_Value($username)
	), 'struct');
	
	// create message
	$message = new XML_RPC_Message('flickr.people.findByUsername', array($struct));
	
	// send the message
	$response = $this->rpc_client->send($message, $this->_timeout);
	
	// test response
	if (!($response instanceof XML_RPC_Response)) {
		throw new Media_FlickrException(strip_tags($this->rpc_client->errstring));
	}
	
	// test if flickr request was successful
	if ($response->faultCode() != 0) {
		$fault_string = strip_tags(utf8_decode($response->faultString()));
		throw new Media_FlickrException("Flickr request failed, reason: ".$fault_string);
	}
	
	// get xml from response and pipe it to simplexml
	$sx = simplexml_load_string(XML_RPC_decode($response->value()));
	
	// get nsid and username from response
	$user_id = Base_Cnc::filterRequest($this->flickrValue($sx['nsid']),
		OAK_REGEX_FLICKR_NSID);
	$username = Base_Cnc::filterRequest($this->flickrValue($sx->username),
		OAK_REGEX_FLICKR_SCREENNAME);
	
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
 * @return array
 */
public function urlsGetUserPhotos ($user_id)
{
	// input check
	if (!preg_match(OAK_REGEX_FLICKR_NSID, $user_id)) {
		throw new Media_FlickrException("Invalid nsid supplied");
	}
	
	// prepare message struct
	$struct = new XML_RPC_Value(array(
		'api_key' => new XML_RPC_Value($this->_api_key),
		'user_id' => new XML_RPC_Value($user_id)
	), 'struct');
	
	// create message
	$message = new XML_RPC_Message('flickr.urls.getUserPhotos', array($struct));
	
	// send the message
	$response = $this->rpc_client->send($message, $this->_timeout);
	
	// test response
	if (!($response instanceof XML_RPC_Response)) {
		throw new Media_FlickrException(strip_tags($this->rpc_client->errstring));
	}
	
	// test if flickr request was successful
	if ($response->faultCode() != 0) {
		$fault_string = strip_tags(utf8_decode($response->faultString()));
		throw new Media_FlickrException("Flickr request failed, reason: ".$fault_string);
	}
	
	// get xml from response and pipe it to simplexml
	$sx = simplexml_load_string(XML_RPC_decode($response->value()));
	
	// get nsid and url from response
	$user_id = Base_Cnc::filterRequest($this->flickrValue($sx['nsid']),
		OAK_REGEX_FLICKR_NSID);
	$url = Base_Cnc::filterRequest($this->flickrValue($sx['url']),
		OAK_REGEX_FLICKR_URL);
	
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
					if (empty($_value) || !preg_match(OAK_REGEX_FLICKR_NSID, $_value)) {
						throw new Media_FlickrException("Invalid nsid supplied");
					}
					$flickr_params[$_key] = new XML_RPC_Value((string)$_value);
				break;
			case 'tags':
			case 'text':
			case 'extra':
					$flickr_params[$_key] = new XML_RPC_Value(strip_tags((string)$_value));
				break;
			case 'per_page':
			case 'page':
					$flickr_params[$_key] = new XML_RPC_Value((int)$_value);
				break;
			case 'tag_mode':
					$flickr_params[$_key] = new XML_RPC_Value((($_value == 'all') ? 'all' : 'any'));
				break;
			case 'timeframe_upload':
					$dates = $HELPER->datesForTimeframe($_value);
					$flickr_params['min_upload_date'] = new XML_RPC_Value($dates['timeframe_start']);
					$flickr_params['max_upload_date'] = new XML_RPC_Value($dates['timeframe_end']);
				break;
			case 'timeframe_taken':
					$dates = $HELPER->datesForTimeframe($_value);
					$flickr_params['min_taken_date'] = new XML_RPC_Value($dates['timeframe_start']);
					$flickr_params['max_taken_date'] = new XML_RPC_Value($dates['timeframe_end']);
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
								$flickr_params[$_key] = new XML_RPC_Value((string)$_value);
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
								$flickr_params[$_key] = new XML_RPC_Value((int)$_value);
							break;
					}
				break;
		}
	}
	
	// prepare message struct
	$flickr_params['api_key'] = new XML_RPC_Value($this->_api_key);
	$struct = new XML_RPC_Value($flickr_params, 'struct');
	
	// create message
	$message = new XML_RPC_Message('flickr.photos.search', array($struct));
	
	// send the message
	$response = $this->rpc_client->send($message, $this->_timeout);
	
	// test response
	if (!($response instanceof XML_RPC_Response)) {
		throw new Media_FlickrException(strip_tags($this->rpc_client->errstring));
	}
	
	// test if flickr request was successful
	if ($response->faultCode() != 0) {
		$fault_string = strip_tags(utf8_decode($response->faultString()));
		throw new Media_FlickrException("Flickr request failed, reason: ".$fault_string);
	}
	
	// get xml from response and pipe it to simplexml
	$sx = simplexml_load_string(XML_RPC_decode($response->value()));
	
	// get metadata from response
	$page = Base_Cnc::filterRequest($this->flickrValue($sx['page']), OAK_REGEX_FLICKR_NSID);
	$pages = Base_Cnc::filterRequest($this->flickrValue($sx['pages']), OAK_REGEX_NUMERIC);
	$perpage = Base_Cnc::filterRequest($this->flickrValue($sx['perpage']), OAK_REGEX_NUMERIC);
	$total = Base_Cnc::filterRequest($this->flickrValue($sx['total']), OAK_REGEX_NUMERIC);
	
	// get pictures from response
	$photos = array();
	foreach ($sx->xpath('/photos/photo') as $_photo) {
		$photos[] = array(
			'id' => Base_Cnc::filterRequest($this->flickrValue($_photo['id']), OAK_REGEX_NUMERIC),
			'owner' => Base_Cnc::filterRequest($this->flickrValue($_photo['owner']), OAK_REGEX_FLICKR_NSID),
			'secret' => Base_Cnc::filterRequest($this->flickrValue($_photo['secret']), OAK_REGEX_ALPHANUMERIC),
			'server' => Base_Cnc::filterRequest($this->flickrValue($_photo['server']), OAK_REGEX_NUMERIC),
			'title' => $this->flickrValue($_photo['title']),
			'ispublic' => Base_Cnc::filterRequest($this->flickrValue($_photo['ispublic']), OAK_REGEX_ZERO_OR_ONE),
			'isfriend' => Base_Cnc::filterRequest($this->flickrValue($_photo['isfriend']), OAK_REGEX_ZERO_OR_ONE),
			'isfamily' => Base_Cnc::filterRequest($this->flickrValue($_photo['isfamily']), OAK_REGEX_ZERO_OR_ONE)
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
	if (!preg_match(OAK_REGEX_FLICKR_NSID, $user_id)) {
		throw new Media_FlickrException("Invalid nsid supplied");
	}
	
	// prepare message struct
	$struct = new XML_RPC_Value(array(
		'api_key' => new XML_RPC_Value($this->_api_key),
		'user_id' => new XML_RPC_Value($user_id)
	), 'struct');
	
	// create message
	$message = new XML_RPC_Message('flickr.photosets.getList', array($struct));
	
	// send the message
	$response = $this->rpc_client->send($message, $this->_timeout);
	
	// test response
	if (!($response instanceof XML_RPC_Response)) {
		throw new Media_FlickrException(strip_tags($this->rpc_client->errstring));
	}
	
	// test if flickr request was successful
	if ($response->faultCode() != 0) {
		$fault_string = strip_tags(utf8_decode($response->faultString()));
		throw new Media_FlickrException("Flickr request failed, reason: ".$fault_string);
	}
	
	// get xml from response and pipe it to simplexml
	$sx = simplexml_load_string(XML_RPC_decode($response->value()));
	
	// get metadata from response
	$cancreate = Base_Cnc::filterRequest($this->flickrValue($sx['cancreate']), OAK_REGEX_ZERO_OR_ONE);
	
	// get photosets from response
	$photosets = array();
	foreach ($sx->xpath('/photosets/photoset') as $_photoset) {
		$photosets[] = array(
			'id' => Base_Cnc::filterRequest($this->flickrValue($_photoset['id']), OAK_REGEX_NUMERIC),
			'primary' => Base_Cnc::filterRequest($this->flickrValue($_photoset['primary']), OAK_REGEX_NUMERIC),
			'secret' => Base_Cnc::filterRequest($this->flickrValue($_photoset['secret']), OAK_REGEX_ALPHANUMERIC),
			'server' => Base_Cnc::filterRequest($this->flickrValue($_photoset['server']), OAK_REGEX_NUMERIC),
			'photos' => Base_Cnc::filterRequest($this->flickrValue($_photoset['photos']), OAK_REGEX_NUMERIC),
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
	if (empty($photoset_id) || !preg_match(OAK_REGEX_NUMERIC, $photoset_id)) {
		throw new Media_FlickrException("Invalid photoset_id supplied");
	}
	if (!is_null($privacy_filter) && ($privacy_filter < 1 || $privacy_filter > 5)) {
		throw new Media_FlickrException("Invalid privacy_filter supplied");
	}
	if (!preg_match(OAK_REGEX_NUMERIC, $per_page)) {
		throw new Media_FlickrException("Invalid input for parameter per_page supplied");
	}
	if (!preg_match(OAK_REGEX_NUMERIC, $page)) {
		throw new Media_FlickrException("Invalid input for parameter page supplied");
	}
	
	// prepare message struct
	$options = array();
	$options['api_key'] = new XML_RPC_Value($this->_api_key);
	if (!is_null($photoset_id)) {
		$options['photoset_id'] = new XML_RPC_Value($photoset_id);
	}
	if (!is_null($extras)) {
		$options['extras'] = new XML_RPC_Value($extras);
	}
	if (!is_null($privacy_filter)) {
		$options['privacy_filter'] = new XML_RPC_Value($privacy_filter);
	}
	if (!is_null($per_page)) {
		$options['per_page'] = new XML_RPC_Value($per_page);
	}
	if (!is_null($page)) {
		$options['page'] = new XML_RPC_Value($page);
	}
	$struct = new XML_RPC_Value($options, 'struct');
	
	// create message
	$message = new XML_RPC_Message('flickr.photosets.getPhotos', array($struct));
	
	// send the message
	$response = $this->rpc_client->send($message, $this->_timeout);
	
	// test response
	if (!($response instanceof XML_RPC_Response)) {
		throw new Media_FlickrException(strip_tags($this->rpc_client->errstring));
	}
	
	// test if flickr request was successful
	if ($response->faultCode() != 0) {
		$fault_string = strip_tags(utf8_decode($response->faultString()));
		throw new Media_FlickrException("Flickr request failed, reason: ".$fault_string);
	}
	
	// get xml from response and pipe it to simplexml
	$sx = simplexml_load_string(XML_RPC_decode($response->value()));
	
	// get metadata from response
	$id = Base_Cnc::filterRequest($this->flickrValue($sx['id']), OAK_REGEX_NUMERIC);
	$primary = Base_Cnc::filterRequest($this->flickrValue($sx['primary']), OAK_REGEX_NUMERIC);
	$owner = Base_Cnc::filterRequest($this->flickrValue($sx['owner']), OAK_REGEX_FLICKR_NSID);
	$ownername = Base_Cnc::filterRequest($this->flickrValue($sx['ownername']), OAK_REGEX_FLICKR_SCREENNAME);
	$page = Base_Cnc::filterRequest($this->flickrValue($sx['page']), OAK_REGEX_NUMERIC);
	$per_page = Base_Cnc::filterRequest($this->flickrValue($sx['per_page']), OAK_REGEX_NUMERIC);
	$pages = Base_Cnc::filterRequest($this->flickrValue($sx['pages']), OAK_REGEX_NUMERIC);
	$total = Base_Cnc::filterRequest($this->flickrValue($sx['total']), OAK_REGEX_NUMERIC);
	
	// get photos from response
	$photos = array();
	foreach ($sx->xpath('/photoset/photo') as $_photo) {
		$photos[] = array(
			'id' => Base_Cnc::filterRequest($this->flickrValue($_photo['id']), OAK_REGEX_NUMERIC),
			'secret' => Base_Cnc::filterRequest($this->flickrValue($_photo['secret']), OAK_REGEX_ALPHANUMERIC),
			'server' => Base_Cnc::filterRequest($this->flickrValue($_photo['server']), OAK_REGEX_NUMERIC),
			'title' => $this->flickrValue($_photo['title']),
			'isprimary' => Base_Cnc::filterRequest($this->flickrValue($_photo['isprimary']), OAK_REGEX_NUMERIC),
			'license' => Base_Cnc::filterRequest($this->flickrValue($_photo['license']), OAK_REGEX_NUMERIC),
			'dateupload' => Base_Cnc::filterRequest($this->flickrValue($_photo['dateupload']), OAK_REGEX_NUMERIC),
			'datetaken' => Base_Cnc::filterRequest($this->flickrValue($_photo['datetaken']), OAK_REGEX_DATETIME),
			'datetakengranularity' =>
				Base_Cnc::filterRequest($this->flickrValue($_photo['datetakengranularity']), OAK_REGEX_NUMERIC),
			'ownername' => Base_Cnc::filterRequest($this->flickrValue($_photo['ownername']), OAK_REGEX_ALPHANUMERIC),
			'iconserver' => Base_Cnc::filterRequest($this->flickrValue($_photo['iconserver']), OAK_REGEX_NUMERIC),
			'originalformat' => Base_Cnc::filterRequest($this->flickrValue($_photo['originalformat']), OAK_REGEX_ALPHANUMERIC),
			'lastupdate' => Base_Cnc::filterRequest($this->flickrValue($_photo['lastupdate']), OAK_REGEX_NUMERIC)
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
 * Prepares data received from flickr for internal processing. The input
 * will be casted to string, converted from utf8 to iso and all tags
 * will be stripped.
 *
 * @param mixed
 * @return string
 */
protected function flickrValue ($value)
{
	// cast to string
	$value = (string)$value;
	
	// decode utf8
	$value = utf8_decode($value);
	
	// strip tags
	$value = strip_tags($value);
	
	return trim($value);
}

// end of class
}

class Media_FlickrException extends Exception { }

?>