<?php

/**
 * Project: Oak
 * File: resource.oakgtpl.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License 3.0
 * http://www.opensource.org/licenses/osl-2.1.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License 3.0
 */

function oakgtplresource_FetchTemplate ($tpl_name, &$tpl_source, &$smarty)
{
	// test input
	if (empty($tpl_name) || !is_scalar($tpl_name)) {
		$smarty->trigger_error("Template resource name is expected to be an non-empty scalar value");
	}
	
	// sanitize tpl_name
	$tpl_name = trim(strip_tags($tpl_name));
	
	// load global template class
	$GLOBALTEMPLATE = load('Templating:GlobalTemplate');
	
	// fetch global template from database
	$template = (array)$GLOBALTEMPLATE->smartyFetchGlobalTemplate($tpl_name);

	// if there's no template id, we can be sure that the global template wasn't found.
	if (!array_key_exists('id', $template)) {
		return false;
	}

	// set mime type constant
	define("OAK_GLOBAL_TEMPLATE_MIME_TYPE",
		(!empty($content['mime_type']) ? $content['mime_type'] : 'text/plain'));
	
	// assign template source
	$tpl_source = $template['content'];
	
	return true;
}

function oakgtplresource_FetchTimestamp ($tpl_name, &$tpl_timestamp, &$smarty)
{
	// test input
	if (empty($tpl_name) || !is_scalar($tpl_name)) {
		$smarty->trigger_error("Template resource name is expected to be an non-empty scalar value");
	}
	
	// sanitize tpl_name
	$tpl_name = trim(strip_tags($tpl_name));
	
	// load global template class
	$GLOBALTEMPLATE = load('Templating:GlobalTemplate');
	
	// fetch last modification date from database
	$tpl_timestamp = $GLOBALTEMPLATE->smartyFetchGlobalTemplateTimestamp($tpl_name);
	
	if (!empty($tpl_timestamp)) {
		return true;
	} else {
		return false;
	}
}

function oakgtplresource_isSecure ($tpl_name, &$smarty)
{
    return true;
}

function oakgtplresource_isTrusted($tpl_name, &$smarty)
{
    // not used for templates
}

?>