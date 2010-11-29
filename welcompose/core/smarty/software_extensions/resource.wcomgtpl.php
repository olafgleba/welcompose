<?php

/**
 * Project: Welcompose
 * File: resource.wcomgtpl.php
 * 
 * Copyright (c) 2008 creatics
 * 
 * Project owner:
 * creatics, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id$
 * 
 * @copyright 2008 creatics, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

function wcomgtplresource_FetchTemplate ($tpl_name, &$tpl_source, &$smarty)
{
	// test input
	if (empty($tpl_name) || !is_scalar($tpl_name)) {
		$smarty->trigger_error("Template resource name is expected to be an non-empty scalar value");
	}
	
	// check the provided template resource name.
	if (!preg_match(WCOM_REGEX_GLOBAL_TEMPLATE_NAME, $tpl_name)) {
		$smarty->trigger_error("Template resource name is invalid", E_USER_ERROR);
		return false;
	}
		
	// load global template class
	$GLOBALTEMPLATE = load('Templating:GlobalTemplate');
	
	// split template resource name into real name, extension and page id	
	preg_match_all('/([a-z0-9-\.]+?)\.(\w{2,4})\.(\d{1,4})/', $tpl_name, $matches);	
	$template_name = trim($matches[1][0].".".$matches[2][0]);
	$project_id = trim($matches[3][0]);
	
	// fetch global template from database
	$template = (array)$GLOBALTEMPLATE->smartyFetchGlobalTemplate($template_name);

	// if there's no template id, we can be sure that the global template wasn't found.
	if (!array_key_exists('id', $template)) {
		return false;
	}

	// set mime type constant
	define("WCOM_GLOBAL_TEMPLATE_MIME_TYPE",
		(!empty($template['mime_type']) ? $template['mime_type'] : 'text/plain'));
	
	// assign template source
	$tpl_source = $template['content'];
	
	return true;
}

function wcomgtplresource_FetchTimestamp ($tpl_name, &$tpl_timestamp, &$smarty)
{
	// test input
	if (empty($tpl_name) || !is_scalar($tpl_name)) {
		$smarty->trigger_error("Template resource name is expected to be an non-empty scalar value");
	}
	
	// check the provided template resource name.
	if (!preg_match(WCOM_REGEX_GLOBAL_TEMPLATE_NAME, $tpl_name)) {
		$smarty->trigger_error("Template resource name is invalid", E_USER_ERROR);
		return false;
	}
	
	// load global template class
	$GLOBALTEMPLATE = load('Templating:GlobalTemplate');
	
	// split template resource name into real name, extension and page id	
	preg_match_all('/([a-z0-9-\.]+?)\.(\w{2,4})\.(\d{1,4})/', $tpl_name, $matches);	
	$template_name = trim($matches[1][0].".".$matches[2][0]);
	$project_id = trim($matches[3][0]);
	
	// fetch last modification date from database
	$tpl_timestamp = $GLOBALTEMPLATE->smartyFetchGlobalTemplateTimestamp($template_name);
	
	if (!empty($tpl_timestamp)) {
		return true;
	} else {
		return false;
	}
}

function wcomgtplresource_isSecure ($tpl_name, &$smarty)
{
    return true;
}

function wcomgtplresource_isTrusted($tpl_name, &$smarty)
{
    // not used for templates
}

?>