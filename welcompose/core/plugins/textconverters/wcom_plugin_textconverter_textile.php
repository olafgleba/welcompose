<?php

/**
 * Project: Welcompose_Plugins
 * File: wcom_plugin_textconverter_textile.php
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
 * @author Andreas Ahlenstorf, Olaf Gleba
 * @package Welcompose_Plugins
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

require_once('wcom_plugin_textconverter_xhtml.php');

class TextConverter_Textile extends TextConverter_Xhtml
{

public function mmInsertImage ($text, $src, $width, $height, $alt, $title)
{
	if (!empty($alt)) {
	  $tag = '!%1$s(%2$s)!%3$s';
	  $html = sprintf($tag, $src, $alt, $text);	
	} else {
	  $tag = '!%1$s!%2$s';
	  $html = sprintf($tag, $src, $text);	
	}	
	return $html;
}

public function mmInsertDocument ($text, $href)
{
	$tag = '"%2$s":%1$s';
	$html = sprintf($tag, $href, $text);
	
	return $html;
}

public function mmInsertInternalLink ($text, $href)
{
	// Omit the Doublequotes if $text is a image reference
	if (preg_match("=^\!(.*)\!$=", $text)) {
		$tag = '%2$s:%1$s';
	} else {
		$tag = '"%2$s":%1$s';
	}
	$html = sprintf($tag, $href, $text);

	if (ini_get('magic_quotes_gpc')) {
		$html = stripslashes($html);
	}	
	return $html;
}

public function mmInsertFlickr ($text, $src, $href)
{
	$tag = '!%1$s!:%2$s %3$s';
	
	$html = sprintf($tag, $src, $href, $text);
	
	return $html;
}

public function apply ($str)
{
	// input check
	if (!is_scalar($str)) {
		throw new TextConverter_MarkdownException('Input for parameter str is expected to be scalar');
	}
	
	// load textile
	if (!class_exists('Textile')) {
		// based on some special needs (s.below), we establish a modified 
		// copy of the original Textile class
		$path = dirname(__FILE__).'/../../third_party/textile-wcom.php';
		require(Base_Compat::fixDirectorySeparator($path));
	}
	
  /**
   * To allow RWD Layouts, we must prevent the default width/height assignment
   * on images by Textile. We establish a new class option ('_set_dimensions') within the
   * Textile class initialisation. See header comments in ./textile-wcom.php also.
   *
   * _set_dimensions	bool	true, false (default)
   *
   * Enable width/height assignment, example
   * $TEXTILE = new Textile('html5', true);
   *
   */
   
	$TEXTILE = new Textile('html5');

  /**
   * Since the original Textile converter class has a problem with supplied
   * with supplied paired ampersands, we cannot use the usual approach. Instead of simply
   * passing the string to the TextileThis method and return it in a single line
   * we must do a string replace before returning the string.
   *
   * Usual approach:
   *
   * return $TEXTILE->TextileThis($str);
   *
   */
	
	// current approach
	$str = $TEXTILE->TextileThis($str);	
	$str = str_replace('&amp;amp;', '&amp;', $str);
	
	return $str;	
}

}

?>