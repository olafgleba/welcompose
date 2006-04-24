<?php

/**
 * Project: Oak
 * File: resource.oak.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/apache2.0.php Apache License, Version 2.0
 */

function oakresource_FetchTemplate ($tpl_name, &$tpl_source, &$smarty)
{
	// load template class
	$TEMPLATE = load('templating:template');

	// checks the provided template resource name. the template resource name
	// consists of two parts, the template type name and the page id, separated
	// by a dot. sample: test.12
	if (!preg_match(OAK_REGEX_TEMPLATE_RESOURCE, $tpl_name)) {
		$smarty->trigger_error("Template resource name $tpl_name is invalid", E_USER_ERROR);
		return false;
	}

	// split template resource name into type name and page id.
	$tpl_name_parts = explode('.', $tpl_name);
	$template_type_name = trim($tpl_name_parts[0]);
	$page_id = trim($tpl_name_parts[1]);

	// get template source
	try {
		$tpl_source = $TEMPLATE->smartyFetchTemplate($page_id, $template_type_name);
		return true;
	} catch (Exception $e) {
		return false;
	}
}

function oakresource_FetchTimestamp ($tpl_name, &$tpl_timestamp, &$smarty)
{
	// load template class
	$TEMPLATE = load('templating:template');

	// checks the provided template resource name. the template resource name
	// consists of two parts, the template type name and the page id, separated
	// by a dot. sample: test.12
	if (!preg_match(OAK_REGEX_TEMPLATE_RESOURCE, $tpl_name)) {
		$smarty->trigger_error("Template resource name $tpl_name is invalid", E_USER_ERROR);
		return false;
	}

	// split template resource name into type name and page id.
	$tpl_name_parts = explode('.', $tpl_name);
	$template_type_name = trim($tpl_name_parts[0]);
	$page_id = trim($tpl_name_parts[1]);

	// get template source
	try {
		$tpl_timestamp = $TEMPLATE->smartyFetchTemplateTimestamp($page_id, $template_type_name);
		return true;
	} catch (Exception $e) {
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
