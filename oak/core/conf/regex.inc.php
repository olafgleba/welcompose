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
 * $Id$
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
define("OAK_REGEX_TEMPLATE_TYPE_NAME", "=^([a-z0-9_]+)$=i");

// Defines regular expression for the template set
// names
define("OAK_REGEX_TEMPLATE_SET_NAME", "=^([a-z0-9_]+)$=i");

// Defines regular expression for the template
// resource names
define("OAK_REGEX_TEMPLATE_RESOURCE", "=^([a-z0-9-_]+).([0-9]+)$=i");

// Defines regular expression for strings containing
// the name of a Smarty variable (only for usage
// in Smarty plugins).
define("OAK_REGEX_SMARTY_VAR_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=i");

// Defines regular expression for strings containing
// the name of a namespace (only for usage in Smarty plugins).
define("OAK_REGEX_SMARTY_NS_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=i");

// Defines regular expression for strings containing
// the name of a class (only for usage in Smarty plugins).
define("OAK_REGEX_SMARTY_CLASS_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=i");

// Defines regular expression for strings containing
// the name of a class' method (only for usage in Smarty plugins).
define("OAK_REGEX_SMARTY_METHOD_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=i");

// Defines regular expression for strings containing
// one or more order macros.
define("OAK_REGEX_ORDER_MACRO", "=^((([A-Z-_]+)(:DESC|:ASC|)(;(?!$)|$))+)$=");

// Defines regular expression to be used to remove 
// undesired chars when determining the first char of a tag
define("OAK_REGEX_TAG_FIRST_CHAR_CLEANUP", "=[^a-z0-9]=");

// Defines regular expression for strings containing a
// page type
define("OAK_REGEX_PAGE_TYPE", "=^([A-Z0-9]+)_([A-Z0-9_]+)$=");

// Defines regular expression for strings containing a
// page type name
define("OAK_REGEX_PAGE_TYPE_NAME", "=^(?!OAK)([A-Z0-9]+)_([A-Z0-9_]+)$=");

// Defines regular expression for strings containing an
// internal page type name
define("OAK_REGEX_PAGE_TYPE_INTERNAL_NAME", "=^(([A-Z]{1}[a-z]{1,})+)$=");

// Defines regular expression for strings containing a
// field id name
define("OAK_REGEX_FORM_FIELD_ID", "=^([a-z0-9-_]+)$=i");

// Defines regular expression for non empty strings
define("OAK_REGEX_NON_EMPTY", "=^.+$=");

// Defines regular expression for the help template
// names
define("OAK_REGEX_HELP", "=^([a-z0-9-_]+)$=i");

// Defines regular expression for javascript file
// names (exclude thirdparty javascript files)
define("OAK_REGEX_JS", '=^(oak+)\.([a-z_]+)\.js$=i');

// Defines regular expression for strings containing
// a file name of a php script
define("OAK_REGEX_FILE_NAME_PHP", '=^([a-z0-9-_]+)\.php$=i');

// Defines regular expression for strings containing
// a group name
define("OAK_REGEX_GROUP_NAME", "=^(?!OAK)([A-Z0-9]+)_([A-Z0-9_]+)$=");

// Defines regular expression for strings containing 
// only zero or one.
define("OAK_REGEX_ZERO_OR_ONE", "=^[0-1]$=");

// Defines regular expression for strings containing
// a user password
define("OAK_REGEX_PASSWORD", "=^\S{5,}$=");

// Defines regular expression for strings containing
// an email address
define("OAK_REGEX_EMAIL", "=^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$=");

// Defines regular expression for strings containing a
// right name
define("OAK_REGEX_RIGHT_NAME", "=^(?!OAK)([A-Z0-9]+)_([A-Z0-9_]+)$=");

// Defines regular expression for strings containing a
// ping service host
define("OAK_REGEX_PING_SERVICE_HOST", "=^([a-z0-9.-]+)$=i");

// Defines regular expression for strings containing a
// ping service path
define("OAK_REGEX_PING_SERVICE_PATH", "=^/([a-z0-9.-\/]*)$=i");

// Defines regular expression for strings containing an
// internal text converter name
define("OAK_REGEX_TEXT_CONVERTER_INTERNAL_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=i");

// Defines regular expression for strings containing an
// internal text macro name
define("OAK_REGEX_TEXT_MACRO_INTERNAL_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=i");

// Defines regular expression for strings containing a
// blog comment status name
define("OAK_REGEX_BLOG_COMMENT_STATUS_NAME", "=^([A-Z0-9_]+)$=");

// Defines regular expression for strings containing a
// timeframe
define("OAK_REGEX_TIMEFRAME", "=^([a-z0-9_]+)$=");

// Defines regular expression for strings containing a
// project's url name
define("OAK_REGEX_PROJECT_NAME_URL", "=^([0-9a-z-]+)$=i");

// Defines regular expression for strings containing a phone
// number.
define("OAK_REGEX_PHONE_NUMBER", "=^([0-9-\/\(\)+\s]+)$=i");

// Defines regular expression for strings containing an
// internal anti spam plugin name
define("OAK_REGEX_ANTI_SPAM_PLUGIN_INTERNAL_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=i");

// Defines regular expression for strings containing a
// hex number
define("OAK_REGEX_HEX", "=^([a-f0-9]+)$=i");

// Defines regular expression for strings containing a
// url-friendly representation of a string
define("OAK_REGEX_MEANINGFUL_STRING", "=^([0-9a-z-]+)$=i");

// Defines regular expression to turn action names into
// url pattern names.
define("OAK_REGEX_ACTION_TO_URL_PATTERN", "~(?<=[a-z])([A-Z0-9])~");

// Defines regular expression for css Identifier.
define("OAK_REGEX_CSS_IDENTIFIER", "=^([a-z_]+)$=i");

// Defines regular expression for object file names.
define("OAK_REGEX_OBJECT_FILENAME", "=^([a-z0-9-_\. ]+)$=i");

// Defines regular expression for flickr's nsids
define("OAK_REGEX_FLICKR_NSID", "=^([a-z0-9\@]+)$=i");

// Defines regular expression for flickr URLs
define("OAK_REGEX_FLICKR_URL", "=^http\:\/\/www.flickr.com(/\S*)?$=i");

// Defines regular expression for flickr screennames
define("OAK_REGEX_FLICKR_SCREENNAME", "=^([a-z0-9\s]+)$=i");

// Defines regular expression for strings containing a date
// in datetime format
define("OAK_REGEX_DATETIME", "=^[0-9]{4}-[0-9]{2}-[0-9]{2}\s[0-9]{2}\:[0-9]{2}\:[0-9]{2}$=i");

// Defines regular expression for strings containing a mime type
define("OAK_REGEX_MIME_TYPE", "=^([a-z0-9-_]+)\/([a-z0-9-_\.]+)$=i");

?>