<?php

/**
 * Project: Welcompose
 * File: smarty_admin.inc.php
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
 * @author Andreas Ahlenstorf, Olaf Gleba
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
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
require(SMARTY_ADMIN_DIR.DIRECTORY_SEPARATOR.'gettext_plugin'.DIRECTORY_SEPARATOR.'compiler.i18n.php');
$smarty->registerPlugin('compiler', 'i18n', 'smarty_compiler_i18n');

// configure smarty
$smarty->debugging = false;
$smarty->auto_literal = false;
$smarty->muteExpectedErrors();
$smarty->force_compile = false;

$smarty->setTemplateDir(SMARTY_ADMIN_TPL_DIR.DIRECTORY_SEPARATOR.'templates');
$smarty->setCompileDir(SMARTY_ADMIN_TPL_DIR.DIRECTORY_SEPARATOR.'compiled');
$smarty->setPluginsDir(array(
	SMARTY_ADMIN_DIR.DIRECTORY_SEPARATOR.'plugins',
	SMARTY_ADMIN_DIR.DIRECTORY_SEPARATOR.'software_plugins'
	)
);




?>
