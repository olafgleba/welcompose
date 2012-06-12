<?php

/**
 * Project: Smarty::Gettext
 * File: Smarty_GettextHelper.class.php
 *
 * Copyright (c) 2008-2012 creatics, Olaf Gleba <og@welcompose.de>
 *
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author 	Andreas Ahlenstorf, Olaf Gleba
 * @package SmartyGettext
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
 * Regex constant for Smarty::Gettext defining the match regex for plural forms
 *
 * @var string
 */
define('SMARTY_GETTEXT_REGEX_PLURAL_MATCH',
	'=^((.*?)[^\x5c])\|((.*?)[^\x5c])\|\W*([0-9]+|\$[a-z0-9_.\x7c]+)$=im');

/**
 * Regex constant for Smarty::Gettext defining the search pattern for template
 * variables in singular phrases. 
 *
 * @var string
 */
define('SMARTY_GETTEXT_REGEX_SINGULAR_SUBSTITUTION', '=`%([a-z0-9_.\x3a]+?)`=i');

/**
 * Regex constant for Smarty::Gettext defining the search pattern for template
 * variables in plural phrases. 
 *
 * @var string
 */
define('SMARTY_GETTEXT_REGEX_PLURAL_SUBSTITUTION', '=`%([a-z0-9_.\x3a]+?)`=i');

class Smarty_GettextHelper
{
	/**
	 * Smarty_Compiler instance
	 *
	 * @var object
 	 */
	public static $compiler = null;

	/**
	 * Temporary map for template vars
	 * 
	 * @var array
	 */
	public static $map = array();

	/**
	 * Template var counter
	 *
	 * @var int
	 */
	public static $counter = 1;

	/**
	 * Callback for preg_replace_callback(). Searches preg matches for template vars.
	 * Found vars will be added to the template var map. Takes array with preg matches
	 * as first argument.
	 * 
	 * @param array Preg matches
	 */
	public static function preg_collector_callback ($matches)
	{
		// make sure we add a var only once
		if (!array_key_exists($matches[0], Smarty_GettextHelper::$map)) {
			$matches[1] = str_replace(':', '|', $matches[1]);
			Smarty_GettextHelper::$map[$matches[0]] = array(
				'counter' => Smarty_GettextHelper::$counter,
				'value' => Smarty_GettextHelper::$matches[1]
			);

			Smarty_GettextHelper::$counter++;
		}
	}

	/**
	 * Callback for preg_replace_callback(). Searches preg matches for template vars
	 * and replaces them with sprintf() placeholders. Takes array with preg matches
	 * as first argument, returns string.
	 *
	 * @param array Preg matches
	 * @return string 
	 */
	public static function preg_translator_callback ($matches)
	{	
		return ' %'.Smarty_GettextHelper::$map[$matches[0]]['counter'].'$s';
	}
	
}

?>