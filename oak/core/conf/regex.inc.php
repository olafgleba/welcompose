<?php

/**
 * Project: Oak
 * File: regex.inc.php
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
 * $Id: regex.inc.php 2 2006-03-20 11:43:20Z andreas $
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/apache2.0.php Apache License, Version 2.0
 */

// Defines regular expression for numbers/numeric strings
define("OAK_REGEX_NUMERIC", "=^([0-9]+)$=");

// Defines regular expression for alphanumeric strings
define("OAK_REGEX_ALPHANUMERIC", "=^([0-9a-z]+)$=i");

// Defines regular expression for the article number
define("OAK_REGEX_ARTICLE_NUMBER", "=^([-_,;\.:#+*\\/\w\s]+)$=i");

// Defines regular expression for URLs
define("OAK_REGEX_URL", "=^((ht|f)tp(s?))\://([0-9a-z\-]+\.)+[a-z]{2,6}(\:[0-9]+)?(/\S*)?$=i");

// Defines regular expression for the template type
// names
define("OAK_REGEX_TEMPLATE_TYPE", "=^([a-z0-9-_]+)$=i");

// Defines regular expression for the template set
// names
define("OAK_REGEX_TEMPLATE_SET", "=^([a-z0-9-_]+)$=i");

// Defines regular expression for the template
// resource names
define("OAK_REGEX_TEMPLATE_RESOURCE", "=^([a-z0-9-_]+).([0-9]+)$=i");

// Defines regular expression for strings containing
// the name of a Smarty variable (only for usage
// in Smarty plugins).
define("OAK_REGEX_SMARTY_VAR_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=i");

// Defines regular expression for strings containing
// the name of a shop class (only for usage
// in Smarty plugins).
define("OAK_REGEX_SMARTY_CLASS_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=i");

// Defines regular expression for strings containing
// the name of a shop class' method (only for usage
// in Smarty plugins).
define("OAK_REGEX_SMARTY_METHOD_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=i");

// Defines regular expression for strings containing
// one or more order macros.
define("OAK_REGEX_ORDER_MACRO", "=^((([A-Z-_]+)(:DESC|:ASC|)(;(?!$)|$))+)$=");

// Defines regular expression to be used to remove 
// undesired chars when determining the first char of a tag
define("OAK_REGEX_TAG_FIRST_CHAR_CLEANUP", "=[^a-z0-9]=");

?>