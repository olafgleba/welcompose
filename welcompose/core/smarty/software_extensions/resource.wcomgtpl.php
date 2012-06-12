<?php

/**
 * Project: Welcompose
 * File: resource.wcomgtpl.php
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
 * @author Olaf Gleba
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

class Smarty_Resource_Wcomgtpl extends Smarty_Resource_Custom {

	protected function fetch($tpl_name, &$tpl_source, &$mtime)
	{
		// test input
		if (empty($tpl_name) || !is_scalar($tpl_name)) {
			trigger_error("Template resource name is expected to be an non-empty scalar value");
		}
	
		// checks the provided template resource name. The template resource name
		// consists of two parts, the template type name and the page id, separated
		// by a dot. sample: simple_page_index.1
		if (!preg_match(WCOM_REGEX_GLOBAL_TEMPLATE_NAME, $tpl_name)) {
			trigger_error("Template resource name is invalid", E_USER_ERROR);
			return false;
		}
	
		// split template resource name into real name, extension and page id	
		preg_match_all('/([a-z0-9-\.]+?)\.(\w{2,4})\.(\d{1,4})/', $tpl_name, $matches);	
		$template_name = trim($matches[1][0].".".$matches[2][0]);
		$project_id = trim($matches[3][0]);

		// load global template class
		$GLOBALTEMPLATE = load('Templating:GlobalTemplate');
			
		// fetch global template from database
		$template = (array)$GLOBALTEMPLATE->smartyFetchGlobalTemplate($template_name);

		// if there's no template id, we can be sure that
		// the global template wasn't found.
		if (!array_key_exists('id', $template)) {
			throw new Exception('Global template not found or empty: '.$tpl_name);
		} else {			
			// set mime type constant
			define("WCOM_GLOBAL_TEMPLATE_MIME_TYPE", (!empty($template['mime_type']) ? $template['mime_type'] : 'text/plain'));
			
			// assign template source
			$tpl_source = $template['content'];
			$mtime = $template['date_modified'];
		}
	}
}
?>