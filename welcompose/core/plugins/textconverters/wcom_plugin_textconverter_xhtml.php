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

public function mmInsertImage ($text, $src, $width, $height, $alt, $title, $longdesc)
{
	$tag = '<img src="%1$s" width="%2$u" height="%3$u" alt="%4$s" title="%5$s" longdesc="%6$s"/>%7$s';
	
	$html = sprintf($tag, $src, $width, $height, $alt, $title, $longdesc, $text);
	
	return htmlentities($html);
}

public function apply ($str)
{
	return $str;
}

}

?>