<?php

/**
 * Project: Welcompose
 * File: regex.inc.php
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

// Defines regular expression for numbers/numeric strings
define("WCOM_REGEX_NUMERIC", "=^([0-9]+)$=D");

// Defines regular expression for alphanumeric strings
define("WCOM_REGEX_ALPHANUMERIC", "=^([0-9a-z]+)$=Di");

// Defines regular expression for the article number
define("WCOM_REGEX_ARTICLE_NUMBER", "=^([-_,;\.:#+*\\/\w\s]+)$=Di");

// Defines regular expression for URLs
define("WCOM_REGEX_URL", "=^((ht|f)tp(s?))\://([0-9a-z\-]+\.)+[a-z]{2,6}(\:[0-9]+)?(/\S*)?$=Di");

// Defines regular expression for the template type
// names
define("WCOM_REGEX_TEMPLATE_TYPE_NAME", "=^([a-z0-9_]+)$=Di");

// Defines regular expression for the template set
// names
define("WCOM_REGEX_TEMPLATE_SET_NAME", "=^([a-z0-9_]+)$=Di");

// Defines regular expression for the template
// resource names
define("WCOM_REGEX_TEMPLATE_RESOURCE", "=^([a-z0-9-_]+).([0-9]+)$=Di");

// Defines regular expression for strings containing
// the name of a Smarty variable (only for usage
// in Smarty plugins).
define("WCOM_REGEX_SMARTY_VAR_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=Di");

// Defines regular expression for strings containing
// the name of a namespace (only for usage in Smarty plugins).
define("WCOM_REGEX_SMARTY_NS_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=Di");

// Defines regular expression for strings containing
// the name of a class (only for usage in Smarty plugins).
define("WCOM_REGEX_SMARTY_CLASS_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=Di");

// Defines regular expression for strings containing
// the name of a class' method (only for usage in Smarty plugins).
define("WCOM_REGEX_SMARTY_METHOD_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=Di");

// Defines regular expression for strings containing
// one or more order macros.
define("WCOM_REGEX_ORDER_MACRO", "=^((([A-Z-_]+)(:DESC|:ASC|)(;(?!$)|$))+)$=D");

// Defines regular expression to be used to remove 
// undesired chars when determining the first char of a tag
define("WCOM_REGEX_TAG_FIRST_CHAR_CLEANUP", "=[^a-z0-9]=");

// Defines regular expression for strings containing a
// page type
define("WCOM_REGEX_PAGE_TYPE", "=^([A-Z0-9]+)_([A-Z0-9_]+)$=D");

// Defines regular expression for strings containing a
// page type name
define("WCOM_REGEX_PAGE_TYPE_NAME", "=^(?!WCOM)([A-Z0-9]+)_([A-Z0-9_]+)$=D");

// Defines regular expression for strings containing an
// internal page type name
define("WCOM_REGEX_PAGE_TYPE_INTERNAL_NAME", "=^(([A-Z]{1}[a-z]{1,})+)$=D");

// Defines regular expression for strings containing a
// field id name
define("WCOM_REGEX_FORM_FIELD_ID", "=^([a-z0-9-_]+)$=Di");

// Defines regular expression for non empty strings
define("WCOM_REGEX_NON_EMPTY", "=^.+$=D");

// Defines regular expression for object id search pattern
// within a mediamanager tag search
define("WCOM_REGEX_TAG_SEARCH_ID", "=^(wcom+)\:([0-9]+)$=D");

// Defines regular expression for the help template
// names
define("WCOM_REGEX_HELP", "=^([a-z0-9-_]+)$=Di");

// Defines regular expression for javascript file
// names (exclude thirdparty javascript files)
define("WCOM_REGEX_JS", '=^(wcom+)\.([a-z_]+)\.js$=Di');

// Defines regular expression for setup javascript file
// names (exclude thirdparty javascript files)
define("WCOM_REGEX_SETUP_JS", '=^(wcom.setup+)\.([a-z_]+)\.js$=Di');

// Defines regular expression for setup javascript file
// names (exclude thirdparty javascript files)
define("WCOM_REGEX_UPDATE_JS", '=^(wcom.update+)\.([a-z_]+)\.js$=Di');

// Defines regular expression for strings containing
// a file name of a php script
define("WCOM_REGEX_FILE_NAME_PHP", '=^([a-z0-9-_]+)\.php$=Di');

// Defines regular expression for strings containing
// a group name
define("WCOM_REGEX_GROUP_NAME", "=^(?!WCOM)([A-Z0-9]+)_([A-Z0-9_]+)$=D");

// Defines regular expression for strings containing 
// only zero or one.
define("WCOM_REGEX_ZERO_OR_ONE", "=^[0-1]$=D");

// Defines regular expression for strings containing
// a user password
define("WCOM_REGEX_PASSWORD", "=^\S{5,}$=D");

// Defines regular expression for strings containing
// an email address
define("WCOM_REGEX_EMAIL", "=^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$=D");

// Defines regular expression for strings containing a
// right name
define("WCOM_REGEX_RIGHT_NAME", "=^(?!WCOM)([A-Z0-9]+)_([A-Z0-9_]+)$=D");

// Defines regular expression for strings containing a
// ping service host
define("WCOM_REGEX_PING_SERVICE_HOST", "=^([a-z0-9.-]+)$=Di");

// Defines regular expression for strings containing a
// ping service path
define("WCOM_REGEX_PING_SERVICE_PATH", "=^/([a-z0-9.-\/]*)$=Di");

// Defines regular expression for strings containing an
// internal text converter name
define("WCOM_REGEX_TEXT_CONVERTER_INTERNAL_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=Di");

// Defines regular expression for strings containing an
// internal text macro name
define("WCOM_REGEX_TEXT_MACRO_INTERNAL_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=Di");

// Defines regular expression for strings containing a
// blog comment status name
define("WCOM_REGEX_BLOG_COMMENT_STATUS_NAME", "=^([A-Z0-9_]+)$=D");

// Defines regular expression for strings containing a
// timeframe
define("WCOM_REGEX_TIMEFRAME", "=^([a-z0-9_]+)$=D");

// Defines regular expression for strings containing a
// project's url name
define("WCOM_REGEX_PROJECT_NAME_URL", "=^([0-9a-z-]+)$=Di");

// Defines regular expression for strings containing a phone
// number.
define("WCOM_REGEX_PHONE_NUMBER", "=^([0-9-\/\(\)+\s]+)$=Di");

// Defines regular expression for strings containing an
// internal anti spam plugin name
define("WCOM_REGEX_ANTI_SPAM_PLUGIN_INTERNAL_NAME", "=^([a-z]|_){1}([0-9a-z_]+)$=Di");

// Defines regular expression for strings containing a
// hex number
define("WCOM_REGEX_HEX", "=^([a-f0-9]+)$=Di");

// Defines regular expression for strings containing
// hex syntax 
define("WCOM_REGEX_HEXADEZIMAL", "=^(^[\#]{1}[a-f0-9]{3,6}+)$=Di");

// Defines regular expression for strings containing a
// url-friendly representation of a string
define("WCOM_REGEX_MEANINGFUL_STRING", "=^([0-9a-z-]+)$=Di");

// Defines regular expression to turn action names into
// url pattern names.
define("WCOM_REGEX_ACTION_TO_URL_PATTERN", "~(?<=[a-z])([A-Z0-9])~");

// Defines regular expression for css Identifier.
define("WCOM_REGEX_CSS_IDENTIFIER", "=^([a-z_]+)$=Di");

// Defines regular expression for object file names.
define("WCOM_REGEX_OBJECT_FILENAME", "=^([a-z0-9-_\. ]+)$=Di");

// Defines regular expression for flickr's nsids
define("WCOM_REGEX_FLICKR_NSID", "=^([a-z0-9\@]+)$=Di");

// Defines regular expression for flickr URLs
define("WCOM_REGEX_FLICKR_URL", "=^http\:\/\/www.flickr.com(/\S*)?$=Di");

// Defines regular expression for flickr screennames
define("WCOM_REGEX_FLICKR_SCREENNAME", "=^([a-z0-9\s]+)$=Di");

// Defines regular expression for strings containing a date
// in datetime format
define("WCOM_REGEX_DATETIME", "=^[0-9]{4}-[0-9]{2}-[0-9]{2}\s[0-9]{2}\:[0-9]{2}\:[0-9]{2}$=Di");

// Defines regular expression for strings containing a mime type
define("WCOM_REGEX_MIME_TYPE", "=^([a-z0-9-_]+)\/([a-z0-9-_\.]+)$=Di");

// Defines regular expression for strings containing a
// database name
define("WCOM_REGEX_DATABASE_NAME", "=^[^_]([a-z0-9_\-])+$=Di");

// Defines regular expression for strings containing a
// locale name
define("WCOM_REGEX_LOCALE_NAME", "=(^POSIX$|^C$|^[a-z]{2}_[A-Z]{2})=");

// Defines regular expression for strings containing a global
// template name
define("WCOM_REGEX_GLOBAL_TEMPLATE_NAME", "=^([a-z0-9-,_\.\s]+)$=Di");

// Defines regular expression for strings containing the path
// to MySQL's unix socket
define("WCOM_REGEX_DATABASE_SOCKET", "=^/[a-z0-9-_\.\/]+$=D");

// Defines regular expression for strings containing a custom
// form type
define("WCOM_REGEX_CUSTOM_FORM_TYPE", "=^([A-Z]{1}[a-z]+)+$=D");

// Defines regular expression for strings containing a
// sitemap priority
define('WCOM_REGEX_SITEMAP_PRIORITY', '=^(0\.[1-9]{1}|1\.0)$=D');

// Defines generic regular expression for strings containing an
// URL name
define('WCOM_REGEX_URL_NAME', '=^[a-z0-9\-]+$=D');

// Defines regular expression for text converter callback names
define('WCOM_REGEX_TEXT_CONVERTER_CALLBACK', '=^[a-z0-9\-]+$=Di');

// Defines regular expression for strings containing a function
// or variable name
define('WCOM_REGEX_OPERATOR_NAME', '=^[a-z]{1}[a-z0-9_]*$=Di');

// Defines regular expression for callback strings
define("WCOM_REGEX_CALLBACK_STRING", "=^([a-z_]+)$=Di");

// Defines regular expression for server http host string
define("WCOM_REGEX_SERVER_HTTP_HOST", "=^([0-9a-z-\.]+?)$=Di");

// Defines regular expression for guestbook search name strings
define("WCOM_REGEX_SEARCH_NAME", "=^([aüößa-z-\.]+)$=Di");

// Defines regular expression for bayes values
define("WCOM_REGEX_BAYES", "=^([0-9\.]+)$=D");

?>