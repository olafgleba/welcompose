<?php

/**
 * Project: Base Classes
 * File: compat.class.php
 * 
 * Copyright (c) 2004 - 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the GNU Lesser
 * General Public License
 * http://opensource.org/licenses/lgpl-license.php
 * 
 * $Id: compat.class.php 29 2006-02-27 21:35:43Z andreas $
 * 
 * @copyright 2004 - 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Base
 * @version php5.1-1.0
 * @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
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