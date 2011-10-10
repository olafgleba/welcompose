<?php

/**
 * Project: Welcompose_Plugins
 * File: wcom_plugin_textmacro_embed_video.php
 * 
 * Copyright (c) 2008 creatics media.systems
 * 
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Olaf Gleba
 * @package Welcompose_Plugins
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/** 
 * Embed video files provided by video sharing service like youtube etc.
 * Return HTML5/XHMTL compliant Flash insertion.
 * 
 * Example:
 * 
 * {embed_video service="youtube" vid="4wr3AD8mZy0" width="500" height="408"}
 *
 * @param string String process
 * @return string
 */
function wcom_plugin_textmacro_embed_video ($str)
{
	// input check
	if (!is_scalar($str)) {
		trigger_error("Input for parameter str is expected to be scalar", E_USER_ERROR);
	}
	
	// parse ...
	return preg_replace_callback("={embed_video\s*service\=\"(\w+)\"\s*vid\=\"([a-zA-Z0-9\-\/\_\=]+)\"\s*width\=\"(\w+)\"\s*height\=\"(\w+)\"}=",
		'wcom_plugin_textmacro_embed_video_callback', $str);
}

/**
 * Callback function for the embed_video macro.
 *
 * @param array
 * @return string
 */
function wcom_plugin_textmacro_embed_video_callback ($args)
{
	// input check
	if (!is_array($args)) {
		trigger_error("Input for parameter args is expected to be an array", E_USER_ERROR);
	}
	
	// init vars
	$pre_url = '';
	$post_url = '';
	
	// define url parts
	switch ($args[1]) {
		case 'youtube':
			$pre_url = 'http://www.youtube.com/embed/';
			$post_url = '';
			return sprintf("<iframe src=\"%s%s%s\" width=\"%s\" height=\"%s\" frameborder=\"0\"></iframe>", $pre_url, $args[2], $post_url, $args[3], $args[4]);
			break;
		case 'vimeo': 
			$pre_url = 'http://player.vimeo.com/video/';
			$post_url = '?title=0&amp;byline=0&amp;portrait=0" ';
			return sprintf("<iframe src=\"%s%s%s\" width=\"%s\" height=\"%s\" frameborder=\"0\" webkitAllowFullScreen allowFullScreen></iframe>", $pre_url, $args[2], $post_url, $args[3], $args[4]);
			break;
		case 'dailymotion': 
			$pre_url = 'http://www.dailymotion.com/embed/video/';
			$post_url = '';
			return sprintf("<iframe src=\"%s%s%s\" width=\"%s\" height=\"%s\" frameborder=\"0\"></iframe>", $pre_url, $args[2], $post_url, $args[3], $args[4]);
			break;
		case 'clipfish': 
			$pre_url = 'http://www.clipfish.de/videoplayer.swf?as=0&amp;videoid=';
			$post_url = '&amp;r=1';
			return sprintf("<object type=\"application/x-shockwave-flash\" data=\"%s%s%s\" width=\"%s\" height=\"%s\"><param name=\"movie\" value=\"%s%s%s\" /><param name=\"allowFullScreen\" value=\"true\" /><param name=\"allowScriptAccess\" value=\"always\" /></object>", $pre_url, $args[2], $post_url, $args[3], $args[4], $pre_url, $args[2], $post_url);
			break;
		case 'sevenload': 
			$pre_url = 'http://en.sevenload.com/pl/';
			$post_url = '/'.$args[3].'x'.$args[4].'/swf';
			return sprintf("<object type=\"application/x-shockwave-flash\" data=\"%s%s%s\" width=\"%s\" height=\"%s\"><param name=\"movie\" value=\"%s%s%s\" /><param name=\"allowFullScreen\" value=\"true\" /><param name=\"allowScriptAccess\" value=\"always\" /></object>", $pre_url, $args[2], $post_url, $args[3], $args[4], $pre_url, $args[2], $post_url);
			break;
		case 'myvideo': 
			$pre_url = 'http://www.myvideo.de/movie/';
			$post_url = '';
			return sprintf("<object type=\"application/x-shockwave-flash\" data=\"%s%s%s\" width=\"%s\" height=\"%s\"><param name=\"movie\" value=\"%s%s%s\" /><param name=\"allowFullScreen\" value=\"true\" /><param name=\"allowScriptAccess\" value=\"always\" /></object>", $pre_url, $args[2], $post_url, $args[3], $args[4], $pre_url, $args[2], $post_url);
			break;
	}	
}
?>