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

class HTML_QuickForm_Rule_Is_Equal extends HTML_QuickForm_Rule
{
	/**
	 * Tests whether input for parameter user_input matches the supplied value.
	 * Returns boolean true if user_input and value are the same.
	 *
	 * @param mixed User input
	 * @param array Required input
	 * @access public
	 * @return bool
	 */
    function validate($user_input, $value)
    {
		return $user_input == $value;
    }
}
?>