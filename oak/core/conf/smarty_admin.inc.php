<?php

/**
 * Project: Oak
 * File: smarty_admin.inc.php
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

// define constants
if (!defined('SMARTY_ADMIN_DIR')) {
	$path_parts = array(
		dirname(__FILE__),
		'..',
		'smarty'
	);
	define('SMARTY_ADMIN_DIR', implode(DIRECTORY_SEPARATOR, $path_parts));
}
if (!defined('SMARTY_ADMIN_TPL_DIR')) {
	$path_parts = array(
		dirname(__FILE__),
		'..',
		'..',
		'admin',
		'smarty'
	);
	define('SMARTY_ADMIN_TPL_DIR', implode(DIRECTORY_SEPARATOR, $path_parts));
}

// configure i18n
require(SMARTY_ADMIN_DIR.DIRECTORY_SEPARATOR.'gettext_plugin'.DIRECTORY_SEPARATOR.'Smarty_GettextHelper.class.php');
require(SMARTY_ADMIN_DIR.DIRECTORY_SEPARATOR.'gettext_plugin'.DIRECTORY_SEPARATOR.'compiler.i18n.php');
$smarty->register_compiler_function('i18n', 'smarty_compiler_i18n');

// configure smarty
$smarty->debug = false;
$smarty->template_dir = SMARTY_ADMIN_TPL_DIR.DIRECTORY_SEPARATOR.'templates';
$smarty->compile_dir = SMARTY_ADMIN_TPL_DIR.DIRECTORY_SEPARATOR.'compiled';
$smarty->cache_dir = SMARTY_ADMIN_TPL_DIR.DIRECTORY_SEPARATOR.'cache';
$smarty->plugins_dir = array(
	SMARTY_ADMIN_DIR.DIRECTORY_SEPARATOR.'my_plugins',
	SMARTY_ADMIN_DIR.DIRECTORY_SEPARATOR.'plugins',
	SMARTY_ADMIN_DIR.DIRECTORY_SEPARATOR.'software_plugins'
);

?>
