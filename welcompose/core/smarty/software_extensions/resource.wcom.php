<?php

/**
 * Project: Welcompose
 * File: resource.wcom.php
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

class Smarty_Resource_Wcom extends Smarty_Resource_Custom {

	protected function fetch($tpl_name, &$tpl_source, &$mtime)
	{
		// test input
		if (empty($tpl_name) || !is_scalar($tpl_name)) {
			trigger_error("Template resource name is expected to be an non-empty scalar value");
		}
		
		// checks the provided template resource name. The template resource name
		// consists of two parts, the template type name and the page id, separated
		// by a dot. sample: test.1
		if (!preg_match(WCOM_REGEX_TEMPLATE_RESOURCE, $tpl_name)) {
			trigger_error("Template resource name is invalid", E_USER_ERROR);
			return false;
		}

		// split template resource name into type name and page id.
		$tpl_name_parts = explode('.', $tpl_name);
		$template_type_name = trim($tpl_name_parts[0]);
		$page_id = trim($tpl_name_parts[1]);

		// load template class
		$TEMPLATE = load('templating:template');
		
		// fetch template from database
		$template = (array)$TEMPLATE->smartyFetchTemplate($page_id, $template_type_name);
	
		// when the template source is empty, we cannot distinguish whether
		// the template couldn't be found or the template is empty. So let's
		// throw a more or less meaningful exception.
		if (empty($template[0]['content'])) {
			throw new Exception('Template not found or empty: '.$tpl_name);
		}	else {
			$tpl_source = $template[0]['content'];
			$mtime = $template[0]['date_modified'];
		}
	}
}
?>
