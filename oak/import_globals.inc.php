<?php

/**
 * Project: Oak
 * File: import_globals.inc.php
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

// default vars
$get = array(
	'action' => Base_Cnc::filterRequest($_GET['action'], OAK_REGEX_ALPHANUMERIC),
	'month' => Base_Cnc::filterRequest($_GET['month'], OAK_REGEX_NUMERIC),
	'page' => Base_Cnc::filterRequest($_GET['page'], OAK_REGEX_NUMERIC),
	'posting' => Base_Cnc::filterRequest($_GET['posting'], OAK_REGEX_NUMERIC),
	'start' => Base_Cnc::filterRequest($_GET['start'], OAK_REGEX_NUMERIC),
	'year' => Base_Cnc::filterRequest($_GET['year'], OAK_REGEX_NUMERIC)
);

$request = array(
	'action' => Base_Cnc::filterRequest($_REQUEST['action'], OAK_REGEX_ALPHANUMERIC),
	'month' => Base_Cnc::filterRequest($_REQUEST['month'], OAK_REGEX_NUMERIC),
	'page' => Base_Cnc::filterRequest($_REQUEST['page'], OAK_REGEX_NUMERIC),
	'posting' => Base_Cnc::filterRequest($_REQUEST['posting'], OAK_REGEX_NUMERIC),
	'start' => Base_Cnc::filterRequest($_REQUEST['start'], OAK_REGEX_NUMERIC),
	'year' => Base_Cnc::filterRequest($_REQUEST['year'], OAK_REGEX_NUMERIC)
);

$session = array(

);

// assign get, session etc.
$BASE->utility->smarty->assign('get', $get);
$BASE->utility->smarty->assign('request', $request);
$BASE->utility->smarty->assign('session', $session);

?>