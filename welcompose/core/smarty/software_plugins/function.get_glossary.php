<?php

/**
 * Project: Welcompose
 * File: smarty_function_get_glossary.php
 * 
 * Copyright (c) 2009 creatics
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 * 
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id$
 * 
 * @copyright 2009 creatics, Olaf Gleba
 * @author Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

function smarty_function_get_glossary ($params, &$smarty)
{
	// check params
	if (!is_array($params)) {
		throw new Exception("page_index: Functions params are not in an array");	
	}
	
	// check input
	if (is_null($params['var']) || !preg_match(WCOM_REGEX_SMARTY_VAR_NAME, $params['var'])) {
		throw new Exception("Invalid var name supplied");
	}
	
	if (is_null($params['action']) || !preg_match(WCOM_REGEX_SMARTY_VAR_NAME, $params['action'])) {
		throw new Exception("Invalid action name supplied");
	}
	
	// define some vars
	$var = $params['var'];
	$action = $params['action'];
	
	// load abbreviation class
	$ABBREVIATION = load('Content:Abbreviation');

	// define some params 
	$params = array(
		'order_macro' => 'NAME'
	);	
	$abbreviations = $ABBREVIATION->selectAbbreviations($params);
		
	if ($action == 'pager') {
	
		// init chars array
		$first_chars = array();
	
		// get first char rows
		foreach ($abbreviations as $_key => $char) {
			if (!empty($char['content_raw'])) {
				$first_chars[] = $char['first_char'];
			}
		}
	
		// straightly build the alphabet array
		$pager_chars = array(
			'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'
			);	

		// get the intersection of both arrays. 
		$intersection = array_intersect($pager_chars, $first_chars);	

		// build the array for the alphabetic link bar
		foreach ($pager_chars as $char) {
			if (in_array($char, $intersection)) {
				$glossary_pager[] = array(
					'char' => $char,
					'has_link' => 1
					);
			} else {
				$glossary_pager[] = array(
					'char' => $char,
					'has_link' => ''
					);
			}
		}
	
		// prepare result 
		$result = $glossary_pager;
	
	}
	
	if ($action == 'content') {
	
		// get all sets with glossary_form content
		foreach ($abbreviations as $key => $val) {
			if (!empty($val['content_raw'])) {				
				$content[] = array(
					'name' => $val['name'],
					'long_form' => $val['long_form'],
					'content' => $val['content'],
					'anchor' => $val['first_char']
				);
			}
		}

		foreach ($content as $key => $val) {			
			// extract anchor values
			$anchor[] = $val['anchor'];			
		
			// prepare new anchor array
			$new_anchor = array();
			
			// set identical pairs to 'null' except the first occurrence.
			// we need this to set unique anchor targets on only the first
			// occurrence of a letter assuming it could have multiple entries
			foreach($anchor as $_key => $_val) {
				$_val = in_array($_val, $new_anchor) ? null : $_val;
				$new_anchor[$_key] = $_val;	
			}
		}
		
		// replace original pairs with processed
		foreach ($content as $key => $val) {
			$val['anchor'] = current($new_anchor);
			next($new_anchor);
			$content[$key] = $val;
		}
		
		// prepare result
		$result = $content;	
	}
	
	// assign result to smarty
	$smarty->assign($var, $result);
}
?>