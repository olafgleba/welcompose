<?php

/**
 * Project: Welcompose
 * File: gettext.inc.php
 * 
 * Copyright (c) 2008 creatics media.systems
 * 
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id$
 * 
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */


function gettextInitSoftware ($locale, $domain = 'messages') {
	// prepare path to locales for export
	$path_parts = array(
		dirname(__FILE__),
		'..',
		'locales'
	);
	$locales_dir = implode(DIRECTORY_SEPARATOR, $path_parts);

	// i18n support information here
	putenv("LANG=$locale"); 

	// set the text domain and the direcotry where to get the translation from
	bindtextdomain($domain, $locales_dir); 
	textdomain($domain);
}

?>
