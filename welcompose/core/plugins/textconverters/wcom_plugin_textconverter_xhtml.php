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
	$tag = '<img src="%1$s" width="%2$u" height="%3$u" alt="%4$s" title="%5$s" />';
	
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
	$tag = '<object data="%1$s" type="application/x-shockwave-flash" width="%2$u" height="%3$u"><param name="movie" value="%1$s" /><param name="quality" value="%4$s" /><param name="scale" value="%5$s" /><param name="wmode" value="%6$s" /><param name="bgcolor" value="%7$s" /><param name="play" value="%8$s" /><param name="loop" value="%9$s" /></object>';
	
	$html = sprintf($tag, $data, $width, $height, $quality, $scale, $wmode, $bgcolor, $play, $loop, $text);
	
	return $html;
}

public function apply ($str)
{
	return $str;
}

}

?>