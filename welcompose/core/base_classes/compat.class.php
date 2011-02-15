<?php

/**
 * Project: Welcompose
 * File: compat.class.php
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
 * $Id: compat.class.php 29 2006-02-27 21:35:43Z andreas $
 * 
 * @copyright 2008 creatics, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

class Base_Compat {

public static function fixDirectorySeparator ($path)
{
	// let's fix the problem with the different directory
	// separators. simply replace everything that can be
	// a valid directory separator on any system with the
	// directory separator now used by php.
	$possible_separators = array(
		'/',
		'\\'
	);
	
	return str_replace($possible_separators, DIRECTORY_SEPARATOR, $path);
}

// End of class
}

?>