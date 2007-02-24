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
	$img = new HtmlTag('img', 'empty', $text);
	$img->appendAttr(new HtmlTagAttr('src', $src));
	$img->appendAttr(new HtmlTagAttr('width', $width));
	$img->appendAttr(new HtmlTagAttr('height', $height));
	$img->appendAttr(new HtmlTagAttr('alt', $alt, false));
	$img->appendAttr(new HtmlTagAttr('title', $title));
	
	$html = $img->getHtml().$text;
	return $this->escapeMultiline($html);
}

public function mmInsertDocument ($text, $href)
{
	$a = new HtmlTag('a', 'inline', $text);
	$a->appendAttr(new HtmlTagAttr('href', $href));
	
	$html = $a->getHtml();
	return $this->escapeMultiline($html);
}

public function mmInsertShockwave ($text, $data, $width, $height, $quality, $scale, $wmode, $bgcolor, $play, $loop)
{
	$object = new HtmlTag('object', 'block');
	$object->appendAttr(new HtmlTagAttr('data', $data));
	$object->appendAttr(new HtmlTagAttr('type', 'application/x-shockwave-flash'));
	$object->appendAttr(new HtmlTagAttr('width', $width));
	$object->appendAttr(new HtmlTagAttr('height', $height));
	
	if (!empty($data)) {
		$param_movie = new HtmlTag('param', 'empty');
		$param_movie->appendAttr(new HtmlTagAttr('name', 'movie'));
		$param_movie->appendAttr(new HtmlTagAttr('value', $data));
		$object->appendTag($param_movie);
	}
	
	if (!empty($quality)) {
		$param_quality = new HtmlTag('param', 'empty');
		$param_quality->appendAttr(new HtmlTagAttr('name', 'quality'));
		$param_quality->appendAttr(new HtmlTagAttr('value', $quality));
		$object->appendTag($param_quality);
	}
	
	if (!empty($scale)) {
		$param_scale = new HtmlTag('param', 'empty');
		$param_scale->appendAttr(new HtmlTagAttr('name', 'scale'));
		$param_scale->appendAttr(new HtmlTagAttr('value', $scale));
		$object->appendTag($param_scale);
	}
	
	if (!empty($wmode)) {
		$param_wmode = new HtmlTag('param', 'empty');
		$param_wmode->appendAttr(new HtmlTagAttr('name', 'wmode'));
		$param_wmode->appendAttr(new HtmlTagAttr('value', $wmode));
		$object->appendTag($param_wmode);
	}
	
	if (!empty($bgcolor)) {
		$param_bgcolor = new HtmlTag('param', 'empty');
		$param_bgcolor->appendAttr(new HtmlTagAttr('name', 'bgcolor'));
		$param_bgcolor->appendAttr(new HtmlTagAttr('value', $bgcolor));
		$object->appendTag($param_bgcolor);
	}
	
	if (!empty($play)) {
		$param_play = new HtmlTag('param', 'empty');
		$param_play->appendAttr(new HtmlTagAttr('name', 'play'));
		$param_play->appendAttr(new HtmlTagAttr('value', ($play == 1 ? 'false' : 'true')));
		$object->appendTag($param_play);
	}
	
	if (!empty($loop)) {
		$param_loop = new HtmlTag('param', 'empty');
		$param_loop->appendAttr(new HtmlTagAttr('name', 'loop'));
		$param_loop->appendAttr(new HtmlTagAttr('value', ($loop == 1 ? 'false' : 'true')));
		$object->appendTag($param_loop);
	}
	
	$html = $object->getHtml().$text;
	return $this->escapeMultiline($html);
}

public function mmInsertInternalLink ($text, $href)
{
	$a = new HtmlTag('a', 'inline', $text);
	$a->appendAttr(new HtmlTagAttr('href', $href));
	
	$html = $a->getHtml();
	return $this->escapeMultiline($html);
}

public function mmInsertInternalReference ($text, $href)
{
	$tag = '%1$s%2$s';
	
	$html = sprintf($tag, $href, $text);
	
	return $html;
}

public function mmInsertFlickr ($text, $src, $href)
{
	$img = new HtmlTag('img', 'empty');
	$img->appendAttr(new HtmlTagAttr('src', $src));
	
	$a = new HtmlTag('a', 'inline', $img->getHtml());
	$a->appendAttr(new HtmlTagAttr('href', $href));
	
	$html = $a->getHtml().$text;
	return $this->escapeMultiline($html);
}

public function apply ($str)
{
	return $str;
}

protected function escapeMultiline ($str)
{
	$str = str_replace("\r\n", "\n", $str);
	$str = str_replace("\r", "\n", $str);
	$str = str_replace("\n", "\\n\\\n", $str);
	
	return $str;
}

// end of class

}

class HtmlTag
{
	
	public $name = null;
	
	public $type = null;
	
	public $childs = array();
	
	public $attrs = array();
	
	public $content = null;
	
	public $omit_if_empty = true;
	
public function __construct ($name, $type, $content = null, $omit_if_empty = true)
{
	$this->name = $name;
	$this->type = $type;
	$this->content = $content;
	$this->omit_if_empty = $omit_if_empty;
}

public function appendTag ($tag)
{
	if (!($tag instanceof HtmlTag)) {
		throw new Exception('No HtmlTag instance');
	}
	if ($this->type == 'empty') {
		throw new Exception('Cannot append new tag to tag declared as empty');
	}
	$this->childs[] = $tag;
}

public function appendAttr ($attribute)
{
	if (!($attribute instanceof HtmlTagAttr)) {
		throw new Exception('No HtmlTagAttr instance');
	}
	
	$this->attrs[] = $attribute;
}

public function getHtml ($indent = null)
{
	if ($this->omit_if_empty) {
		if (!$this->hasAttrs() && $this->hasChilds()) {
			return null;
		}
	}
	
	if ($this->type == 'block') {
		$lines = array();

		if ($this->hasAttrs()) {
			$lines[] = sprintf('%s<%s%s>', $indent, $this->name, " ".$this->inflateAttrs());
		} else {
			$lines[] = sprintf('%s<%s>', $indent, $this->name);
		}
		
		if (!empty($this->content)) {
			$lines[] = $indent . "\t" . $this->content;
		}
	
		foreach ($this->childs as $_child) {
			$lines[] = $_child->getHtml($indent . "\t");
		}
	
		$lines[] = sprintf('%s</%s>', $indent, $this->name);
	
		return implode("\n", $lines);
	} elseif ($this->type == 'inline') {
		if ($this->hasAttrs()) {
			return sprintf('%s<%s%s>%s</%s>', $indent, $this->name, " ".$this->inflateAttrs(), $this->content, $this->name);
		} else {
			return sprintf('%s<%s>%s</%s>', $indent, $this->name, $this->content, $this->name);
		}
	} elseif ($this->type == 'empty') {
		if ($this->hasAttrs()) {
			return sprintf('%s<%s%s/>%s', $indent, $this->name, " ".$this->inflateAttrs(), $this->content);
		} else {
			return sprintf('%s<%s/>%s', $indent, $this->name, $this->content);
		}
	}
}

protected function inflateAttrs ()
{
	sort($this->attrs);
	$strings = array();
	
	foreach ($this->attrs as $_attribute) {
		if (!empty($_attribute->value) || !$_attribute->omit_if_empty) {
			$strings[] = sprintf('%s="%s"', $_attribute->name, $_attribute->value);
		}
	}
	
	return implode(' ', $strings);
}

protected function hasAttrs ()
{
	if (count($this->attrs) == 0) {
		return false;
	} else {
		return true;
	}
}

protected function hasChilds ()
{
	if (count($this->childs) == 0 || $this->type == 'empty') {
		return false;
	} else {
		return true;
	}
}

public function emptyTag ()
{
	$this->childs = array();
}

public function emptyAttrs ()
{
	$this->attrs = array();
}

// end of class
}

class HtmlTagAttr
{
	public $name = null;
	
	public $value = null;
	
	public $omit_if_empty = null;
	
public function __construct ($name, $value, $omit_if_empty = true)
{
	$this->name = $name;
	$this->value = $value;
	$this->omit_if_empty = $omit_if_empty;
}

// end of class
}

?>