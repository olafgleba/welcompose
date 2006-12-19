<?php

/**
 * Project: Welcompose
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
 * http://www.opensource.org/licenses/osl-3.0.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/osl-3.0.php Open Software License 3.0
 */

// default vars
$get = array(
	'action' => Base_Cnc::filterRequest($_GET['action'], WCOM_REGEX_ALPHANUMERIC),
	'page' => Base_Cnc::filterRequest($_GET['page'], WCOM_REGEX_NUMERIC),
	'posting' => Base_Cnc::filterRequest($_GET['posting'], WCOM_REGEX_NUMERIC),
	'posting_month_added' => Base_Cnc::filterRequest($_GET['posting_month_added'], WCOM_REGEX_NUMERIC),
	'posting_day_added' => Base_Cnc::filterRequest($_GET['posting_day_added'], WCOM_REGEX_NUMERIC),
	'posting_year_added' => Base_Cnc::filterRequest($_GET['posting_year_added'], WCOM_REGEX_NUMERIC),
	'start' => Base_Cnc::filterRequest($_GET['start'], WCOM_REGEX_NUMERIC)
);

$request = array(
	'action' => Base_Cnc::filterRequest($_REQUEST['action'], WCOM_REGEX_ALPHANUMERIC),
	'posting_year_added' => Base_Cnc::filterRequest($_REQUEST['posting_year_added'], WCOM_REGEX_NUMERIC),
	'posting_month_added' => Base_Cnc::filterRequest($_REQUEST['posting_month_added'], WCOM_REGEX_NUMERIC),
	'posting_day_added' => Base_Cnc::filterRequest($_REQUEST['posting_day_added'], WCOM_REGEX_NUMERIC),
	'page' => Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC),
	'posting' => Base_Cnc::filterRequest($_REQUEST['posting'], WCOM_REGEX_NUMERIC),
	'start' => Base_Cnc::filterRequest($_REQUEST['start'], WCOM_REGEX_NUMERIC)
);

$session = array(
	'form_submitted' => Base_Cnc::filterRequest($_SESSION['form_submitted'], WCOM_REGEX_ZERO_OR_ONE)
);

// assign get, session etc.
$BASE->utility->smarty->assign('get', $get);
$BASE->utility->smarty->assign('request', $request);
$BASE->utility->smarty->assign('session', $session);

?>