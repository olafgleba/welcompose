<?php /* Smarty version 2.6.14, created on 2007-02-05 12:30:54
         compiled from wcom.setup.strings.js */ ?>
/**
 * Project: Welcompose
 * File: wcom.setup.strings.js
 *
 * Copyright (c) 2004-2005 sopic GmbH
 *
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License
 * http://www.opensource.org/licenses/osl-2.1.php
 *
 * $Id$
 *
 * @copyright 2004-2005 sopic GmbH
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */


/**
 * debug output strings (used with debug state 2)
 */
var e_msg_str_prefix = '<?php 
			$map = 'a:0:{}';
			Smarty_GettextHelper::$map = unserialize(stripslashes($map));
			$printf_args = array();
		
			$translated = gettext(stripslashes('An javascript error occured:'));
			$prepared = preg_replace_callback('=`%([a-z0-9_.\x3a]+?)`=i',
			    array('Smarty_GettextHelper', 'preg_translator_callback'), $translated);
			ksort($printf_args);
			vprintf($prepared, $printf_args);
		 ?>';
var e_msg_str_suffix = '<?php 
			$map = 'a:0:{}';
			Smarty_GettextHelper::$map = unserialize(stripslashes($map));
			$printf_args = array();
		
			$translated = gettext(stripslashes('If the error remains, please get in touch with our support.'));
			$prepared = preg_replace_callback('=`%([a-z0-9_.\x3a]+?)`=i',
			    array('Smarty_GettextHelper', 'preg_translator_callback'), $translated);
			ksort($printf_args);
			vprintf($prepared, $printf_args);
		 ?>';

/**
 * used in func getHelp(), removeHelp()
 */
var hideHelp = '<?php 
			$map = 'a:0:{}';
			Smarty_GettextHelper::$map = unserialize(stripslashes($map));
			$printf_args = array();
		
			$translated = gettext(stripslashes('Hide help on this topic'));
			$prepared = preg_replace_callback('=`%([a-z0-9_.\x3a]+?)`=i',
			    array('Smarty_GettextHelper', 'preg_translator_callback'), $translated);
			ksort($printf_args);
			vprintf($prepared, $printf_args);
		 ?>';
var showHelp = '<?php 
			$map = 'a:0:{}';
			Smarty_GettextHelper::$map = unserialize(stripslashes($map));
			$printf_args = array();
		
			$translated = gettext(stripslashes('Show help on this topic'));
			$prepared = preg_replace_callback('=`%([a-z0-9_.\x3a]+?)`=i',
			    array('Smarty_GettextHelper', 'preg_translator_callback'), $translated);
			ksort($printf_args);
			vprintf($prepared, $printf_args);
		 ?>';