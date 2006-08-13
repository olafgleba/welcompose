<?php

/**
 * Project: Oak
 * File: function.get_url.php
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

function smarty_function_get_url ($params, &$smarty)
{
	// define some vars
	$page = null;
	$action = null;
	$query_params = array();
	
	// check input vars
	if (!is_array($params)) {
		throw new Exception("get_url: Functions params are not in an array");	
	}
	
	// separate function params from the rest
	foreach ($params as $_key => $_value) {
		switch ((string)$_key) {
			case 'page':
					$$_key = (int)$_value;
				break;
			case 'action':
					$$_key = (string)$_value;
				break;
			default:
					$query_params[$_key] = $_value;
				break;
		}
	}
	
	// check input
	if (is_null($page) || !is_numeric($page)) {
		throw new Exception("get_url: Invalid page id supplied");
	}
	if (is_null($action) || !preg_match(OAK_REGEX_ALPHANUMERIC, $action)) {
		throw new Exception("get_url: Invalid action supplied");
	}
	
	// load Net_URL
	require_once 'Net/URL.php';
	$URL = new Net_URL('index.php');
	
	// add page/action to url
	$URL->addQueryString('page', $page);
	$URL->addQueryString('action', $action);
	
	// append query params to url
	foreach ($query_params as $_key => $_value) {
		$URL->addQueryString($_key, $_value);
	}
	
	// return generated url
	return $URL->getUrl();
}

?>