<?php

/**
 * Project: Oak
 * File: helper.class.php
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

class Utility_Helper {
	
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
	 * Textile object
	 * @var object
	 */
	protected $textile = null;

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
 * Singleton. Returns instance of the Utility_Helper object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Utility_Helper::$instance == null) {
		Utility_Helper::$instance = new Utility_Helper(); 
	}
	return Utility_Helper::$instance;
}

/**
 * Translate order definition into sql statements
 * 
 * <b>Order definition</b>
 * 
 * <code>
 * DATE;MANUFACTURER:ASC;DATE:DESC;ID;STATUS:ASC
 * </code>
 * 
 * <b>Macro definition</b>
 * 
 * <code>
 * $macros = array(
 *     'DATE' => '`catalogue_articles`.`date_added`',
 *     'MANUFACTURER' => '`catalogue_manufacturer`.`id`'
 * );
 * </code>
 * 
 * @throws Utility_HelperException
 * @param string Order definition
 * @param array Macro definition
 * @return string
 */
public function _sqlForOrderMacro ($definition, $macros)
{
	// input check
	if (!is_array($macros)) {
		throw new Utility_HelperException('Input for parameter macros is expected to be an array');	
	}

	// check definition syntax
	if (!preg_match(OAK_REGEX_ORDER_MACRO, $definition)) {
		throw new Utility_HelperException('Input for parameter definition is not well-formed');
	}
	
	$orders = array();
	foreach (explode(';', $definition) as $definition) {
		$parts = explode(':', $definition);
		if (isset($macros[$parts[0]])) {
			$parts[0] = $macros[$parts[0]];
			$orders[] = implode(' ', $parts);
		} else {
			throw new Exception("Unknown macro $parts[0]");	
		}
	}

	return implode(', ', $orders);
}

/**
 * Applies markdown on given string. Takes the string to convert as
 * first argument, the information if HTML should be stripped before
 * using markdown as second argument. Returns the converted string.
 *
 * @throws Utility_HelperException
 * @param string String to convert
 * @param bool Stip html
 * @return string
 */
public function applyMarkdown ($str, $strip_html = true)
{
	// input check
	if (!is_scalar($str)) {
		throw new Utility_HelperException("Input for parameter str is expected to be scalar");
	}
	if (!is_bool($strip_html)) {
		throw new Utility_HelperException("Input for parameter strip_html is expected to be bool");
	}
	
	// strip html if needed
	if ($strip_html !== false) {
		$str = strip_tags($str);
	}
	
	// load markdown
	if (!function_exists('Markdown')) {
		$path = dirname(__FILE__).'/../third_party/markdown.php';
		require(Base_Compat::fixDirectorySeparator($path));
	}
	
	// apply markdown
	return Markdown($str);
}

/** 
 * Applies Textile on given string. Takes the string to convert as
 * first argument, the information if HTML should be stripped before
 * using markdown as second argument. Returns the converted string.
 *
 * @throws Utility_HelperException
 * @param string String to convert
 * @param bool Stip html
 * @return string
 */
public function applyTextile ($str, $strip_html = true)
{
	// input check
	if (!is_scalar($str)) {
		throw new Utility_HelperException("Input for parameter str is expected to be scalar");
	}
	if (!is_bool($strip_html)) {
		throw new Utility_HelperException("Input for parameter strip_html is expected to be bool");
	}
	
	// strip html if needed
	if ($strip_html !== false) {
		$str = strip_tags($str);
	}
	
	// load textile
	if (!is_a($this->textile, 'Textile')) {
		if (!class_exists('Textile')) {
			$path = dirname(__FILE__).'/../third_party/textile.php';
			require(Base_Compat::fixDirectorySeparator($path));
		}
		$this->textile = new Textile();
	}
	
	// apply textile
	return $this->textile->TextileThis($str);
}

/**
 * Replaces non-url-friendly characters like whitespaces etc. with something
 * more url friendly to create the 'meaningful urls'. Takes the string to
 * convert as first argument. Returns the converted string.
 * 
 * See helper::_urlTranslationTable() for the whole character translation
 * table.
 * 
 * @param string
 * @return string
 */
public function createMeaningfulString ($str)
{
	// get translation table
	$table = $this->_urlTranslationTable();
	
	// initialize search/replace arrays
	$search = array();
	$replace = array();
	
	// prepare replacement arrays
	foreach ($table as $_key => $_value) {
		$search[] = $_key;
		$replace[] = $_value;
	}
	
	// lower string
	$str = strtolower($str);
	
	// remove whitespaces from the beginning and the end
	$str = trim($str);
	
	// translate the special characters like umlauts
	// to us ascii
	$str = str_replace($search, $replace, $str);
	
	// replace everything but allowed characters by dashes
	$str = preg_replace('=[^a-z0-9-]=', '-', $str);
	
	// remove unnecessary dashes
	$str = preg_replace('=(-+)=', '-', $str);
	
	// return meaningful url
	return $str;
}

/**
 * Url translation table
 * 
 * Creates and returns the url translation table for
 * helper::createMeaningfulString().
 *
 * @return array
 */
protected function _urlTranslationTable ()
{
	return array (
				// german
				'ä' => 'ae',
				'ü' => 'ue',
				'ö' => 'oe',
				'ß' => 'ss',
				// french
				'è' => 'e',
				'é' => 'e',
				'à' => 'a',
				// spanish
				'ñ' => 'n',
				'á' => 'a',
				'é' => 'e',		
				'í' => 'i',		
				'ó' => 'o',		
				'ú' => 'u',
			);
}

/**
 * Tests array of sql data for pear errors. Takes array with sql data
 * as first argument, returns bool.
 *
 * @throws Utility_HelperException
 * @param array Sql data
 * @return bool
 */
public function testSqlDataForPearErrors (&$sqlData)
{
	if (!is_array($sqlData)) {
		throw new Utility_HelperException('Input for parameter sqlData is not an array');
	}
	foreach ($sqlData as $_key => $_value) {
		if (!is_scalar($_key)) {
			throw new Utility_HelperException("Some key in sql data array is not scalar");
		} 
		if ($_value instanceof PEAR_Error) {
			throw new Utility_HelperException(sprintf("Element %s's value is of type PEAR_Error: %s",
				$_key, $_value->getMessage()));
		}
		if (!is_scalar($_value)) {
			throw new Utility_HelperException("Element $_key in bind params array is not scalar");
		}
	}
	reset($sqlData);
	
	return true;
}

/**
 * Calculates a page index on basis of the total item count and the number
 * of items per page. Taks the total item count as first argument, the number
 * of items per page as second argument. Returns array.
 * 
 * @throws Utility_HelperException
 * @param int Total item count
 * @param int Number of items per page
 * @return array
 */
public function calculatePageIndex ($total_items, $interval)
{
	// input check
	if (!is_numeric($total_items)) {
		throw new Utility_HelperException('Input for parameter total_items is not numeric');
	}
	if (!is_numeric($interval)) {
		throw new Utility_HelperException('Input for parameter interval is not numeric');
	}
	
	if ($total_items == 0) {
		$index = array();
	} else {
		$pages = ceil($total_items / $interval);
		
		$index = array();
		for ($i=1;$i<$pages+1;$i++) {
			$index[] = array(
							'page' => $i,
							'last' => ($i - 2) * $interval,
							'self' => ($i - 1) * $interval,
							'next' => $i * $interval
						);
		}
		foreach ($index as $_key => $_value) {
			if ($_value['last'] < 0) {
				$index[$_key]['last'] = null;
			}
		}
	}
	
	return $index;
}

// end of class
}

class Utility_HelperException extends Exception { }

?>