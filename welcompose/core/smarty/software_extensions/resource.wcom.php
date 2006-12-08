<?php

/**
 * Project: Welcompose
 * File: resource.wcom.php
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
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

function oakresource_FetchTemplate ($tpl_name, &$tpl_source, &$smarty)
{
	// load template class
	$TEMPLATE = load('templating:template');

	// checks the provided template resource name. the template resource name
	// consists of two parts, the template type name and the page id, separated
	// by a dot. sample: test.12
	if (!preg_match(WCOM_REGEX_TEMPLATE_RESOURCE, $tpl_name)) {
		$smarty->trigger_error("Template resource name $tpl_name is invalid", E_USER_ERROR);
		return false;
	}

	// split template resource name into type name and page id.
	$tpl_name_parts = explode('.', $tpl_name);
	$template_type_name = trim($tpl_name_parts[0]);
	$page_id = trim($tpl_name_parts[1]);

	// get template source
	$tpl_source = $TEMPLATE->smartyFetchTemplate($page_id, $template_type_name);
	
	// when the template source is empty, we cannot distinguish whether
	// the template couldn't be found or the template is empty. So let's
	// throw a more or less meaningful exception.
	if (empty($tpl_source)) {
		throw new Exception('Template not found or empty: '.$tpl_name);
	}
	
	return true;
}

function oakresource_FetchTimestamp ($tpl_name, &$tpl_timestamp, &$smarty)
{
	// load template class
	$TEMPLATE = load('templating:template');

	// checks the provided template resource name. the template resource name
	// consists of two parts, the template type name and the page id, separated
	// by a dot. sample: test.12
	if (!preg_match(WCOM_REGEX_TEMPLATE_RESOURCE, $tpl_name)) {
		$smarty->trigger_error("Template resource name $tpl_name is invalid", E_USER_ERROR);
		return false;
	}

	// split template resource name into type name and page id.
	$tpl_name_parts = explode('.', $tpl_name);
	$template_type_name = trim($tpl_name_parts[0]);
	$page_id = trim($tpl_name_parts[1]);
	
	// get template timestamp
	$tpl_timestamp = $TEMPLATE->smartyFetchTemplateTimestamp($page_id, $template_type_name);
	
	if (!empty($tpl_timestamp)) {
		return true;
	} else {
		return false;
	}
}

function oakresource_isSecure ($tpl_name, &$smarty)
{
    return true;
}

function oakresource_isTrusted($tpl_name, &$smarty)
{
    // not used for templates
}

?>
