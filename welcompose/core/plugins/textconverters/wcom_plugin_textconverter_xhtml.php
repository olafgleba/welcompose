<?php

/**
 * Project: Welcompose_Plugins
 * File: wcom_plugin_textconverter_xhtml.php
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

class TextConverter_XHTML
{

public function mmInsertImage ($text, $src, $width, $height, $alt, $title)
{
	$tag = '<img src="%1$s" width="%2$u" height="%3$u" alt="%4$s" title="%5$s" />%6$s';
	
	$html = sprintf($tag, $src, $width, $height, $alt, $title, $text);
	
	return $html;
}

public function mmInsertDocument ($text, $href)
{
	$tag = '<a href="%1$s">%2$s</a>';
	
	$html = sprintf($tag, $href, $text);
	
	return $html;
}

public function mmInsertShockwave ($text, $data, $width, $height, $quality, $scale, $wmode, $bgcolor, $play, $loop)
{
	$tag = '<object data="%1$s" type="application/x-shockwave-flash" width="%2$u" height="%3$u"><param name="movie" value="%1$s" />%4$s%5$s%6$s%7$s%8$s%9$s</object>%10$s';
	
	$html = sprintf($tag, $data, $width, $height, $quality, $scale, $wmode, $bgcolor, $play, $loop, $text);
	
	return $html;
}

public function mmInsertInternalLink ($text, $href)
{
	$tag = '<a href="%1$s">%2$s</a>';
	
	$html = sprintf($tag, $href, $text);
	
	return $html;
}

public function mmInsertInternalReference ($text, $href)
{
	$tag = '%1$s %2$s';
	
	$html = sprintf($tag, $href, $text);
	
	return $html;
}

public function mmInsertFlickr ($text, $src, $href)
{
	$tag = '<a href="%2$s"><img src="%1$s" /></a>%3$s';
	
	$html = sprintf($tag, $src, $href, $text);
	
	return $html;
}

public function apply ($str)
{
	return $str;
}

}

?>