<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4.0                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Bertrand Mansion <bmansion@mamasam.com>                     |
// +----------------------------------------------------------------------+
//
// $Id: Email.php,v 1.4 2003/12/18 14:21:57 mansion Exp $

require_once('HTML/QuickForm/Rule.php');

class HTML_QuickForm_Rule_In_Array_Keys extends HTML_QuickForm_Rule
{
	/**
	 * Tests whether input for parameter selected_key is a key in the supplied
	 * array. Useful when dealing with selects and you'll make sure that the
	 * user selects only one of the available options and not a custom one.
	 * Returns boolean true if selection is valid.
	 *
	 * @param mixed Selected array key
	 * @param array Array with available options
	 * @access public
	 * @return bool
	 */
    function validate($selected_key, $array)
    {
		// if input for paramater array is not an array, simply return false.
		if (!is_array($array)) {
			return false;
		}
		
		// we can either receive an array, a scalar value or something
		// else as selected key
		if (is_array($selected_key)) {
			foreach ($selected_key as $_key) {
				if (!is_scalar($_key) || !array_key_exists($_key, $array)) {
					return false;
				}
			}
			
			return true;
		} elseif (is_scalar($selected_key)) {
			if (array_key_exists($selected_key, $array)) {
				return true;
			} else {
				return false;
			}			
		} else {
			return false;
		}
    }
}
?>