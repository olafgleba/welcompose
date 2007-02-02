<?php

/**
 * Project: Welcompose_Plugins
 * File: wcom_plugin_textconverter_textile.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-3.0.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Welcompose_Plugins
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

require_once('wcom_plugin_textconverter_xhtml.php');

class TextConverter_Textile extends TextConverter_Xhtml
{

public function mmInsertImage ($text, $src, $width, $height, $alt, $title, $longdesc)
{
	$tag = '![%4$s](%1$s "%5$s")';
	$html = sprintf($tag, $src, $width, $height, $alt, $title, $longdesc, $text);
	
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
		$path = dirname(__FILE__).'/../../third_party/textile.php';
		require(Base_Compat::fixDirectorySeparator($path));
	}
	$TEXTILE = new Textile();
	
	// apply textile
	return $TEXTILE->TextileThis($str);
}

}

?>