<?php

/**
 * Project: Welcompose
 * File: import_globals.inc.php
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
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @link http://welcompose.de
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

// default vars
$get = array(
	'action' => Base_Cnc::filterRequest($_GET['action'], WCOM_REGEX_ALPHANUMERIC),
	'page' => Base_Cnc::filterRequest($_GET['page'], WCOM_REGEX_NUMERIC),
	'posting' => Base_Cnc::filterRequest($_GET['posting'], WCOM_REGEX_NUMERIC),
	'posting_month_added' => Base_Cnc::filterRequest($_GET['posting_month_added'], WCOM_REGEX_NUMERIC),
	'posting_day_added' => Base_Cnc::filterRequest($_GET['posting_day_added'], WCOM_REGEX_NUMERIC),
	'posting_year_added' => Base_Cnc::filterRequest($_GET['posting_year_added'], WCOM_REGEX_NUMERIC),
	'start' => Base_Cnc::filterRequest($_GET['start'], WCOM_REGEX_NUMERIC),
	'tag' => Base_Cnc::filterRequest($_GET['tag'], WCOM_REGEX_URL_NAME)
);

$request = array(
	'action' => Base_Cnc::filterRequest($_REQUEST['action'], WCOM_REGEX_ALPHANUMERIC),
	'posting_year_added' => Base_Cnc::filterRequest($_REQUEST['posting_year_added'], WCOM_REGEX_NUMERIC),
	'posting_month_added' => Base_Cnc::filterRequest($_REQUEST['posting_month_added'], WCOM_REGEX_NUMERIC),
	'posting_day_added' => Base_Cnc::filterRequest($_REQUEST['posting_day_added'], WCOM_REGEX_NUMERIC),
	'page' => Base_Cnc::filterRequest($_REQUEST['page'], WCOM_REGEX_NUMERIC),
	'posting' => Base_Cnc::filterRequest($_REQUEST['posting'], WCOM_REGEX_NUMERIC),
	'start' => Base_Cnc::filterRequest($_REQUEST['start'], WCOM_REGEX_NUMERIC),
	'tag' => Base_Cnc::filterRequest($_GET['tag'], WCOM_REGEX_URL_NAME)
);

$session = array(
	'form_submitted' => Base_Cnc::filterRequest($_SESSION['form_submitted'], WCOM_REGEX_ZERO_OR_ONE)
);

// assign get, session etc.
$BASE->utility->smarty->assign('get', $get);
$BASE->utility->smarty->assign('request', $request);
$BASE->utility->smarty->assign('session', $session);

?>