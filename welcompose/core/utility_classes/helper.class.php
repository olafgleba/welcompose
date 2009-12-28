<?php

/**
 * Project: Welcompose
 * File: helper.class.php
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
 * $Id$
 * 
 * @copyright 2008 creatics, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

/**
 * Singleton. Returns instance of the Utility_Helper object.
 * 
 * @return object
 */
function Utility_Helper ()
{ 
	if (Utility_Helper::$instance == null) {
		Utility_Helper::$instance = new Utility_Helper(); 
	}
	return Utility_Helper::$instance;
}

class Utility_Helper {
	
	/**
	 * Singleton
	 * 
	 * @var object
	 */
	public static $instance = null;
	
	/**
	 * Reference to base class
	 * 
	 * @var object
	 */
	public $base = null;
	
	/**
	 * Textile object
	 * 
	 * @var object
	 */
	protected $textile = null;

/**
 * Start instance of base class, load configuration and
 * establish database connection. Please don't call the
 * constructor direcly, use the singleton pattern instead.
 */
public function __construct()
{
	try {
		// get base instance
		$this->base = load('base:base');
		
		// establish database connection
		$this->base->loadClass('database');
		
	} catch (Exception $e) {
		
		// trigger error
		printf('%s on Line %u: Unable to start base class. Reason: %s.', $e->getFile(),
			$e->getLine(), $e->getMessage());
		exit;
	}
}

/**
 * Translate order definition into sql statements
 * 
 * <b>Order definition</b>
 * 
 * <code>
 * DATE;MANUFACTURER:ASC;DATE:DESC;ID;STATUS:ASC
 * </code>
 * 
 * <b>Macro definition</b>
 * 
 * <code>
 * $macros = array(
 *     'DATE' => '`catalogue_articles`.`date_added`',
 *     'MANUFACTURER' => '`catalogue_manufacturer`.`id`'
 * );
 * </code>
 * 
 * @throws Utility_HelperException
 * @param string Order definition
 * @param array Macro definition
 * @return string
 */
public function _sqlForOrderMacro ($definition, $macros)
{
	// input check
	if (!is_array($macros)) {
		throw new Utility_HelperException('Input for parameter macros is expected to be an array');	
	}

	// check definition syntax
	if (!preg_match(WCOM_REGEX_ORDER_MACRO, $definition)) {
		throw new Utility_HelperException('Input for parameter definition is not well-formed');
	}
	
	$orders = array();
	foreach (explode(';', $definition) as $definition) {
		$parts = explode(':', $definition);
		if (isset($macros[$parts[0]])) {
			$parts[0] = $macros[$parts[0]];
			$orders[] = implode(' ', $parts);
		} else {
			throw new Exception("Unknown macro $parts[0]");	
		}
	}

	return implode(', ', $orders);
}

/**
 * Generates IN clause for database queries. Takes the field to search as
 * first argument, the values for the IN clause as second argument. Returns
 * string with sql query fragment. Sample:
 * 
 * <code>
 * echo $helper->_sqlInFromArray('id', array(1, 2));
 * // result:
 * // id IN (1, 2)
 * </code>
 * 
 * @throws Utility_HelperException
 * @param string Field name
 * @param array Search values
 * @return string
 */
public function _sqlInFromArray ($field, $values)
{
	// input check
	if (empty($field) || !is_scalar($field)) {
		throw new Utility_HelperException("field is expected to be a non empty scalar value");
	}
	if (!is_array($values)) {
		throw new Utility_HelperException("values is expected to be an array");
	}
	
	// if the value array is empty, return 1
	if (count($values) < 1) {
		return " 1 ";
	}
	
	// quote values
	foreach ($values as $_key => $_value) {
		$values[$_key] = $this->base->db->quote($_value, PDO::PARAM_STR);
	}
	
	return sprintf(' %s IN ( %s ) ', $field, implode(', ', $values));
}

/**
 * Generates NOT IN clause for database queries. Takes the field to search as
 * first argument, the values for the NOT IN clause as second argument. Returns
 * string with sql query fragment. Sample:
 * 
 * <code>
 * echo $helper->_sqlNotInFromArray('id', array(1, 2));
 * // result:
 * // id NOT IN (1, 2)
 * </code>
 * 
 * @throws Utility_HelperException
 * @param string Field name
 * @param array Search values
 * @return string
 */
public function _sqlNotInFromArray ($field, $values)
{
	// input check
	if (empty($field) || !is_scalar($field)) {
		throw new Utility_HelperException("field is expected to be a non empty scalar value");
	}
	if (!is_array($values)) {
		throw new Utility_HelperException("values is expected to be an array");
	}
	
	// if the value array is empty, return 1
	if (count($values) < 1) {
		return " 1 ";
	}
	
	// quote values
	foreach ($values as $_key => $_value) {
		$values[$_key] = $this->base->db->quote($_value, PDO::PARAM_STR);
	}
	
	return sprintf(' %s NOT IN ( %s ) ', $field, implode(', ', $values));
}


/**
 * Generates multiple LIKE clauses for database queries from array of
 * search values. Takes the field to search as first argument, the values
 * for the LIKE clauses as second argument and an optional concatenation
 * operator as third argument. Returns string with sql query fragment. Sample:
 *
 * <code>
 * echo $helper->_sqlLikeFromArray('id', array(1, 2), 'AND');
 * // result:
 * // id LIKE 1 AND LIKE 2
 *
 * echo $helper->_sqlLikeFromArray('id', array(1, 2), 'OR');
 * // result:
 * // id LIKE 1 OR LIKE 2
 * </code>
 * 
 * @throws Utility_HelperException
 * @param string Field name
 * @param array Search values
 * @param string Concatenation operator
 * @return string
 */
public function _sqlLikeFromArray ($field, $values, $concat = 'OR')
{
	// input check
	if (empty($field) || !is_scalar($field)) {
		throw new Utility_HelperException("field is expected to be a non empty scalar value");
	}
	if (!is_array($values)) {
		throw new Utility_HelperException("values is expected to be an array");
	}
	if ($concat != 'AND' && $concat != 'OR') {
		throw new Utility_HelperException("concat can be either AND or OR");
	}
	
	// if the value array is empty, return 1
	if (count($values) < 1) {
		return " 1 ";
	}
	
	// quote values
	$fragments = array();
	foreach ($values as $_key => $_value) {
		 $fragments[] = sprintf(" %s LIKE %s ", $field,
			$this->base->db->quote($_value, PDO::PARAM_STR));
	}
	
	if ($concat == 'AND') {
		return implode(' AND ', $fragments);
	} else {
		return implode(' OR ', $fragments);
	}
}

/**
 * Returns array with available timeframes.
 * 
 * @return array
 */
public function getTimeframes ()
{
	return array(
		'today' => gettext('Today'),
		'yesterday' => gettext('Yesterday'),
		'two_days_ago' => gettext('Two days ago'),
		'three_days_ago' => gettext('Three days ago'),
		'four_days_ago' => gettext('Four days ago'),
		'five_days_ago' => gettext('Five days ago'),
		'six_days_ago' => gettext('Six days ago'),
		'seven_days_ago' => gettext('Seven days ago'),
		'this_week' => gettext('This week'),
		'last_week' => gettext('Last week'),
		'two_weeks_ago' => gettext('Two weeks ago'),
		'three_weeks_ago' => gettext('Three weeks ago'),
		'four_weeks_ago' => gettext('Four weeks ago'),
		'this_month' => gettext('This month'),
		'last_month' => gettext('Last month'),
		'two_months_ago' => gettext('Two months ago'),
		'three_months_ago' => gettext('Three months ago'),
		'four_months_ago' => gettext('Four months ago'),
		'five_months_ago' => gettext('Five months ago'),
		'six_months_ago' => gettext('Six months ago'),
		'half_year_ago' => gettext('Half year ago'),
		'one_year_ago' => gettext('One year ago'),
		'two_years_ago' => gettext('Two years ago'),
		'three_years_ago' => gettext('Three years ago'),
		'four_years_ago' => gettext('Four years ago'),
		'five_years_ago' => gettext('Five years ago')
	);
}

/**
 * Creates sql fragment for given timeframe. Takes the name of the
 * date field as first argument, the name of the timeframe as second
 * argument. Returns string.
 * 
 * See Utility_Helper::getTimeframes() for a list of supported 
 * timeframes. 
 * 
 * @throws Utility_HelperException
 * @param string Date field name
 * @param string Timeframe name
 * @return string Sql fragment
 */
public function _sqlForTimeframe ($field, $timeframe)
{
	// input check
	if (empty($field) || !is_scalar($field)) {
		throw new Utility_HelperException("Input for parameter field is expected to be a non-empty string");
	}
	if (empty($timeframe) || !is_scalar($timeframe)) {
		throw new Utility_HelperException("Input for parameter timeframe is expected to be a non-empty string");
	}
	if (!preg_match(WCOM_REGEX_TIMEFRAME, $timeframe)) {
		throw new Utility_HelperException("Invalid timeframe supplied");
	}
	
	// let's define now as a var, so we can play around with different dates
	$now = strtotime('now');
	
	// let's fix date('w') 'cos weeks of normal people start with monday
	$w = (date('w', $now) == 0) ? 7 : date('w', $now);
	
	switch ((string)$timeframe) {
		case 'today':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-d 00:00:00", strtotime('today', $now));
			
			// compose and return sql fragment
			return sprintf("%s > '%s'", $field, $timeframe_start);
		case 'yesterday':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-d 00:00:00", strtotime('yesterday', $now));
			$timeframe_end = date("Y-m-d 23:59:59", strtotime('yesterday', $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'two_days_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-d 00:00:00", strtotime('2 days ago', $now));
			$timeframe_end = date("Y-m-d 23:59:59", strtotime('2 days ago', $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'three_days_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-d 00:00:00", strtotime('3 days ago', $now));
			$timeframe_end = date("Y-m-d 23:59:59", strtotime('3 days ago', $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'four_days_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-d 00:00:00", strtotime('4 days ago', $now));
			$timeframe_end = date("Y-m-d 23:59:59", strtotime('4 days ago', $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'five_days_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-d 00:00:00", strtotime('5 days ago', $now));
			$timeframe_end = date("Y-m-d 23:59:59", strtotime('5 days ago', $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'six_days_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-d 00:00:00", strtotime('6 days ago', $now));
			$timeframe_end = date("Y-m-d 23:59:59", strtotime('6 days ago', $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'seven_days_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-d 00:00:00", strtotime('7 days ago', $now));
			$timeframe_end = date("Y-m-d 23:59:59", strtotime('7 days ago', $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'this_week':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-d 00:00:00", $now - (86400 * ($w - 1)));
			
			// compose and return sql fragment
			return sprintf("%s > '%s'", $field, $timeframe_start);
		case 'last_week':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-d 00:00:00", strtotime(sprintf("%s -1 week",
				date('Y-m-d', $now - (86400 * ($w - 1))))));
			$timeframe_end = date("Y-m-d 23:59:59", strtotime(date('Y-m-d',
				$now - (86400 * ($w - 1)) - 86400)));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'two_weeks_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-d 00:00:00", strtotime(sprintf("%s -2 weeks",
				date('Y-m-d', $now - (86400 * ($w - 1))))));
			$timeframe_end = date("Y-m-d 23:59:59", strtotime(sprintf("%s -1 week",
				date('Y-m-d', $now - (86400 * ($w - 1)) - 86400))));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'three_weeks_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-d 00:00:00", strtotime(sprintf("%s -3 weeks",
				date('Y-m-d', $now - (86400 * ($w - 1))))));
			$timeframe_end = date("Y-m-d 23:59:59", strtotime(sprintf("%s -2 weeks",
				date('Y-m-d', $now - (86400 * ($w - 1)) - 86400))));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'four_weeks_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-d 00:00:00", strtotime(sprintf("%s -4 weeks",
				date('Y-m-d', $now - (86400 * ($w - 1))))));
			$timeframe_end = date("Y-m-d 23:59:59", strtotime(sprintf("%s -3 weeks",
				date('Y-m-d', $now - (86400 * ($w - 1)) - 86400))));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'this_month':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-01 00:00:00", $now);

			// compose and return sql fragment
			return sprintf("%s > '%s'", $field, $timeframe_start);
		case 'last_month':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-01 00:00:00", strtotime(sprintf('%s -1 month', date("Y-m-15", $now)), $now));
			$timeframe_end = date("Y-m-t 23:59:59", strtotime(sprintf('%s -1 month', date("Y-m-15", $now)), $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'two_months_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-01 00:00:00", strtotime(sprintf('%s -2 months', date("Y-m-15", $now)), $now));
			$timeframe_end = date("Y-m-t 23:59:59", strtotime(sprintf('%s -2 months', date("Y-m-15", $now)), $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'three_months_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-01 00:00:00", strtotime(sprintf('%s -3 months', date("Y-m-15", $now)), $now));
			$timeframe_end = date("Y-m-t 23:59:59", strtotime(sprintf('%s -3 months', date("Y-m-15", $now)), $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'four_months_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-01 00:00:00", strtotime(sprintf('%s -4 months', date("Y-m-15", $now)), $now));
			$timeframe_end = date("Y-m-t 23:59:59", strtotime(sprintf('%s -4 months', date("Y-m-15", $now)), $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'five_months_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-01 00:00:00", strtotime(sprintf('%s -5 months', date("Y-m-15", $now)), $now));
			$timeframe_end = date("Y-m-t 23:59:59", strtotime(sprintf('%s -5 months', date("Y-m-15", $now)), $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'six_months_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-01 00:00:00", strtotime(sprintf('%s -6 months', date("Y-m-15", $now)), $now));
			$timeframe_end = date("Y-m-t 23:59:59", strtotime(sprintf('%s -6 months', date("Y-m-15", $now)), $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'half_year_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-01 00:00:00", strtotime(sprintf('%s -12 months', date("Y-m-15", $now)), $now));
			$timeframe_end = date("Y-m-t 23:59:59", strtotime(sprintf('%s -6 months', date("Y-m-15", $now)), $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'one_year_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-01 00:00:00", strtotime(sprintf('%s -2 years', date("Y-m-15", $now)), $now));
			$timeframe_end = date("Y-m-t 23:59:59", strtotime(sprintf('%s -1 year', date("Y-m-15", $now)), $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'two_years_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-01 00:00:00", strtotime(sprintf('%s -3 years', date("Y-m-15", $now)), $now));
			$timeframe_end = date("Y-m-t 23:59:59", strtotime(sprintf('%s -2 years', date("Y-m-15", $now)), $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'three_years_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-01 00:00:00", strtotime(sprintf('%s -4 years', date("Y-m-15", $now)), $now));
			$timeframe_end = date("Y-m-t 23:59:59", strtotime(sprintf('%s -3 years', date("Y-m-15", $now)), $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'four_years_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-01 00:00:00", strtotime(sprintf('%s -5 years', date("Y-m-15", $now)), $now));
			$timeframe_end = date("Y-m-t 23:59:59", strtotime(sprintf('%s -4 years', date("Y-m-15", $now)), $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		case 'five_years_ago':
			// calculate timeframe start/end
			$timeframe_start = date("Y-m-01 00:00:00", strtotime(sprintf('%s -6 years', date("Y-m-15", $now)), $now));
			$timeframe_end = date("Y-m-t 23:59:59", strtotime(sprintf('%s -5 years', date("Y-m-15", $now)), $now));

			// compose and return sql fragment
			return sprintf("%s BETWEEN '%s' AND '%s'", $field, $timeframe_start,
				$timeframe_end);
		default:
			throw new Utility_HelperException("Unknown timeframe supplied");
	}
	
}

/**
 * Calculates start/end dates for given timeframe. Takes the name
 * of the timeframe as first argument. Returns array with to values:
 * timeframe_start and timeframe end.
 * 
 * See Utility_Helper::getTimeframes() for a list of supported 
 * timeframes. 
 * 
 * @throws Utility_HelperException
 * @param string Timeframe name
 * @return array
 */
public function datesForTimeframe ($timeframe)
{
	if (empty($timeframe) || !is_scalar($timeframe)) {
		throw new Utility_HelperException("Input for parameter timeframe is expected to be a non-empty string");
	}
	if (!preg_match(WCOM_REGEX_TIMEFRAME, $timeframe)) {
		throw new Utility_HelperException("Invalid timeframe supplied");
	}
	
	// let's define now as a var, so we can play around with different dates
	$now = strtotime('now');
	
	// let's fix date('w') 'cos weeks of normal people start with monday
	$w = (date('w', $now) == 0) ? 7 : date('w', $now);
	
	switch ((string)$timeframe) {
		case 'today':
			return array(
				'timeframe_start' => date("Y-m-d 00:00:00", strtotime('today', $now)),
				'timeframe_end' => date("Y-m-d H:i:s", strtotime('today', $now))
			);
		case 'yesterday':
			return array(
				'timeframe_start' => date("Y-m-d 00:00:00", strtotime('yesterday', $now)),
				'timeframe_end' => date("Y-m-d 23:59:59", strtotime('yesterday', $now))
			);
		case 'two_days_ago':
			return array(
				'timeframe_start' => date("Y-m-d 00:00:00", strtotime('2 days ago', $now)),
				'timeframe_end' => date("Y-m-d 23:59:59", strtotime('2 days ago', $now))
			);
		case 'three_days_ago':
			return array(
				'timeframe_start' => date("Y-m-d 00:00:00", strtotime('3 days ago', $now)),
				'timeframe_end' => date("Y-m-d 23:59:59", strtotime('3 days ago', $now))
			);
		case 'four_days_ago':
			return array(
				'timeframe_start' => date("Y-m-d 00:00:00", strtotime('4 days ago', $now)),
				'timeframe_end' => date("Y-m-d 23:59:59", strtotime('4 days ago', $now))
			);
		case 'five_days_ago':
			return array(
				'timeframe_start' => date("Y-m-d 00:00:00", strtotime('5 days ago', $now)),
				'timeframe_end' => date("Y-m-d 23:59:59", strtotime('5 days ago', $now))
			);
		case 'six_days_ago':
			return array(
				'timeframe_start' => date("Y-m-d 00:00:00", strtotime('6 days ago', $now)),
				'timeframe_end' => date("Y-m-d 23:59:59", strtotime('6 days ago', $now))
			);
		case 'seven_days_ago':
			return array(
				'timeframe_start' => date("Y-m-d 00:00:00", strtotime('7 days ago', $now)),
				'timeframe_end' => date("Y-m-d 23:59:59", strtotime('7 days ago', $now))
			);
		case 'this_week':
			return array(
				'timeframe_start' => date("Y-m-d 00:00:00", $now - (86400 * ($w - 1))),
				'timeframe_end' => date("Y-m-d H:i:s", $now)
			);
		case 'last_week':
			return array(
				'timeframe_start' => date("Y-m-d 00:00:00", strtotime(sprintf("%s -1 week",
					date('Y-m-d', $now - (86400 * ($w - 1)))))),
				'timeframe_end' => date("Y-m-d 23:59:59", strtotime(date('Y-m-d',
					$now - (86400 * ($w - 1)) - 86400)))
			);
		case 'two_weeks_ago':
			return array(
				'timeframe_start' => date("Y-m-d 00:00:00", strtotime(sprintf("%s -2 weeks",
					date('Y-m-d', $now - (86400 * ($w - 1)))))),
				'timeframe_end' => date("Y-m-d 23:59:59", strtotime(sprintf("%s -1 week",
					date('Y-m-d', $now - (86400 * ($w - 1)) - 86400))))
			);
		case 'three_weeks_ago':
			return array(
				'timeframe_start' => date("Y-m-d 00:00:00", strtotime(sprintf("%s -3 weeks",
					date('Y-m-d', $now - (86400 * ($w - 1)))))),
				'timeframe_end' => date("Y-m-d 23:59:59", strtotime(sprintf("%s -2 weeks",
					date('Y-m-d', $now - (86400 * ($w - 1)) - 86400))))
			);
		case 'four_weeks_ago':
			return array(
				'timeframe_start' => date("Y-m-d 00:00:00", strtotime(sprintf("%s -4 weeks",
					date('Y-m-d', $now - (86400 * ($w - 1)))))),
					'timeframe_end' => date("Y-m-d 23:59:59", strtotime(sprintf("%s -3 weeks",
					date('Y-m-d', $now - (86400 * ($w - 1)) - 86400))))
				);
		case 'this_month':
			return array(
				'timeframe_start' => date("Y-m-01 00:00:00", $now),
				'timeframe_end' => date("Y-m-d 00:00:00", $now),
			);
		case 'last_month':
			return array(
				'timeframe_start' => date("Y-m-01 00:00:00", strtotime(sprintf('%s -1 month', date("Y-m-15", $now)), $now)),
				'timeframe_end' => date("Y-m-t 23:59:59", strtotime(sprintf('%s -1 month', date("Y-m-15", $now)), $now))
			);
		case 'two_months_ago':
			return array(
				'timeframe_start' => date("Y-m-01 00:00:00", strtotime(sprintf('%s -2 months', date("Y-m-15", $now)), $now)),
				'timeframe_end' => date("Y-m-t 23:59:59", strtotime(sprintf('%s -2 months', date("Y-m-15", $now)), $now))
			);
		case 'three_months_ago':
			return array(
				'timeframe_start' => date("Y-m-01 00:00:00", strtotime(sprintf('%s -3 months', date("Y-m-15", $now)), $now)),
				'timeframe_end' => date("Y-m-t 23:59:59", strtotime(sprintf('%s -3 months', date("Y-m-15", $now)), $now))
			);
		case 'four_months_ago':
			return array(
				'timeframe_start' => date("Y-m-01 00:00:00", strtotime(sprintf('%s -4 months', date("Y-m-15", $now)), $now)),
				'timeframe_end' => date("Y-m-t 23:59:59", strtotime(sprintf('%s -4 months', date("Y-m-15", $now)), $now))
			);
		case 'five_months_ago':
			return array(
				'timeframe_start' => date("Y-m-01 00:00:00", strtotime(sprintf('%s -5 months', date("Y-m-15", $now)), $now)),
				'timeframe_end' => date("Y-m-t 23:59:59", strtotime(sprintf('%s -5 months', date("Y-m-15", $now)), $now))
			);
		case 'six_months_ago':
			return array(
				'timeframe_start' => date("Y-m-01 00:00:00", strtotime(sprintf('%s -6 months', date("Y-m-15", $now)), $now)),
				'timeframe_end' => date("Y-m-t 23:59:59", strtotime(sprintf('%s -6 months', date("Y-m-15", $now)), $now))
			);
		case 'half_year_ago':
			return array(
				'timeframe_start' => date("Y-m-01 00:00:00", strtotime(sprintf('%s -12 months', date("Y-m-15", $now)), $now)),
				'timeframe_end' => date("Y-m-t 23:59:59", strtotime(sprintf('%s -6 months', date("Y-m-15", $now)), $now))
			);
		case 'one_year_ago':
			return array(
				'timeframe_start' => date("Y-m-01 00:00:00", strtotime(sprintf('%s -2 years', date("Y-m-15", $now)), $now)),
				'timeframe_end' => date("Y-m-t 23:59:59", strtotime(sprintf('%s -1 year', date("Y-m-15", $now)), $now))
			);
		case 'two_years_ago':
			return array(
				'timeframe_start' => date("Y-m-01 00:00:00", strtotime(sprintf('%s -3 years', date("Y-m-15", $now)), $now)),
				'timeframe_end' => date("Y-m-t 23:59:59", strtotime(sprintf('%s -2 years', date("Y-m-15", $now)), $now))
			);
		case 'three_years_ago':
			return array(
				'timeframe_start' => date("Y-m-01 00:00:00", strtotime(sprintf('%s -4 years', date("Y-m-15", $now)), $now)),
				'timeframe_end' => date("Y-m-t 23:59:59", strtotime(sprintf('%s -3 years', date("Y-m-15", $now)), $now))
			);
		case 'four_years_ago':
			return array(
				'timeframe_start' => date("Y-m-01 00:00:00", strtotime(sprintf('%s -5 years', date("Y-m-15", $now)), $now)),
				'timeframe_end' => date("Y-m-t 23:59:59", strtotime(sprintf('%s -4 years', date("Y-m-15", $now)), $now))
			);
		case 'five_years_ago':
			return array(
				'timeframe_start' => date("Y-m-01 00:00:00", strtotime(sprintf('%s -6 years', date("Y-m-15", $now)), $now)),
				'timeframe_end' => date("Y-m-t 23:59:59", strtotime(sprintf('%s -5 years', date("Y-m-15", $now)), $now))
			);
		default:
			throw new Utility_HelperException("Unknown timeframe supplied");
	}
}


/**
 * Creates sql fragment to search for given string
 * Take the table field name as first argument and
 * the search string as second. Returns sql fragment string.
 * 
 * @throws Utility_HelperException
 * @param string Table field name
 * @param string Search fragment
 * @return string
 */
public function _searchLikewise ($field, $str)
{
	if (empty($field) || !is_scalar($field)) {
		throw new Utility_HelperException("Input for parameter field is expected to be a non-empty string");
	}
	if (empty($str) || !is_scalar($str)) {
		throw new Utility_HelperException("Input for parameter str is expected to be a non-empty string");
	}
	
	// return sql fragment
	return sprintf("%s LIKE '%%%s%%'", $field, $str);
}

/**
 * Applies markdown on given string. Takes the string to convert as
 * first argument, the information if HTML should be stripped before
 * using markdown as second argument. Returns the converted string.
 *
 * @throws Utility_HelperException
 * @param string String to convert
 * @param bool Stip html
 * @return string
 */
public function applyMarkdown ($str, $strip_html = true)
{
	// input check
	if (!is_scalar($str)) {
		throw new Utility_HelperException("Input for parameter str is expected to be scalar");
	}
	if (!is_bool($strip_html)) {
		throw new Utility_HelperException("Input for parameter strip_html is expected to be bool");
	}
	
	// strip html if needed
	if ($strip_html !== false) {
		$str = strip_tags($str);
	}
	
	// load markdown
	if (!function_exists('Markdown')) {
		$path = dirname(__FILE__).'/../third_party/markdown.php';
		require(Base_Compat::fixDirectorySeparator($path));
	}
	
	// apply markdown
	return Markdown($str);
}

/** 
 * Applies Textile on given string. Takes the string to convert as
 * first argument, the information if HTML should be stripped before
 * using markdown as second argument. Returns the converted string.
 *
 * @throws Utility_HelperException
 * @param string String to convert
 * @param bool Stip html
 * @return string
 */
public function applyTextile ($str, $strip_html = true)
{
	// input check
	if (!is_scalar($str)) {
		throw new Utility_HelperException("Input for parameter str is expected to be scalar");
	}
	if (!is_bool($strip_html)) {
		throw new Utility_HelperException("Input for parameter strip_html is expected to be bool");
	}
	
	// strip html if needed
	if ($strip_html !== false) {
		$str = strip_tags($str);
	}
	
	// load textile
	if (!is_a($this->textile, 'Textile')) {
		if (!class_exists('Textile')) {
			$path = dirname(__FILE__).'/../third_party/textile.php';
			require(Base_Compat::fixDirectorySeparator($path));
		}
		$this->textile = new Textile();
	}
	
	// apply textile
	return $this->textile->TextileThis($str);
}

/**
 * Replaces non-url-friendly characters like whitespaces etc. with something
 * more url friendly to create the 'meaningful urls'. Takes the string to
 * convert as first argument. Returns the converted string.
 * 
 * See helper::_urlTranslationTable() for the whole character translation
 * table.
 * 
 * @param string
 * @return string
 */
public function createMeaningfulString ($str)
{
	// get translation table
	$table = $this->_urlTranslationTable();
	
	// initialize search/replace arrays
	$search = array();
	$replace = array();
	
	// prepare replacement arrays
	foreach ($table as $_key => $_value) {
		$search[] = $_key;
		$replace[] = $_value;
	}
	
	// lower string
	$str = strtolower($str);
	
	// remove whitespaces from the beginning and the end
	$str = trim($str);
	
	// translate the special characters like umlauts
	// to us ascii
	$str = str_replace($search, $replace, $str);
	
	// replace everything but allowed characters by dashes
	$str = preg_replace('=[^a-z0-9-]=', '-', $str);
	
	// remove unnecessary dashes
	$str = preg_replace('=(-+)=', '-', $str);
	
	// remove dashes at the beginning and at the end of the string
	$str = preg_replace('=^(-+)=', null, $str);
	$str = preg_replace('=(-+)$=', null, $str);
	
	// return meaningful url
	return $str;
}

/**
 * Url translation table
 * 
 * Creates and returns the url translation table for
 * helper::createMeaningfulString().
 *
 * @return array
 */
protected function _urlTranslationTable ()
{
	return array (
				// german
				'ä' => 'ae',
				'ü' => 'ue',
				'ö' => 'oe',
				'ß' => 'ss',
				// french
				'è' => 'e',
				'é' => 'e',
				'à' => 'a',
				// spanish
				'ñ' => 'n',
				'á' => 'a',
				'é' => 'e',		
				'í' => 'i',		
				'ó' => 'o',		
				'ú' => 'u',
			);
}

/**
 * Tests array of sql data for pear errors. Takes array with sql data
 * as first argument, returns bool.
 *
 * @throws Utility_HelperException
 * @param array Sql data
 * @return bool
 */
public function testSqlDataForPearErrors (&$sqlData)
{
	if (!is_array($sqlData)) {
		throw new Utility_HelperException('Input for parameter sqlData is not an array');
	}
	foreach ($sqlData as $_key => $_value) {
		if (!is_scalar($_key)) {
			throw new Utility_HelperException("Some key in sql data array is not scalar");
		} 
		if ($_value instanceof PEAR_Error) {
			throw new Utility_HelperException(sprintf("Element %s's value is of type PEAR_Error: %s",
				$_key, $_value->getMessage()));
		}
		if (!is_null($_value) && !is_scalar($_value)) {
			throw new Utility_HelperException("Element $_key in bind params array is not scalar");
		}
	}
	reset($sqlData);
	
	return true;
}

/**
 * Calculates a page index on basis of the total item count and the number
 * of items per page. Tasks the total item count as first argument, the number
 * of items per page as second argument. Returns array.
 * 
 * @throws Utility_HelperException
 * @param int Total item count
 * @param int Number of items per page
 * @return array
 */
public function calculatePageIndex ($total_items, $interval)
{
	// input check
	if (!is_numeric($total_items)) {
		throw new Utility_HelperException('Input for parameter total_items is not numeric');
	}
	if (!is_numeric($interval)) {
		throw new Utility_HelperException('Input for parameter interval is not numeric');
	}
	
	if ($total_items == 0) {
		$index = array();
	} else {
		$pages = ceil($total_items / $interval);
		
		$index = array();
		for ($i=1;$i<$pages+1;$i++) {
			$index[] = array(
							'page' => $i,
							'last' => ($i - 2) * $interval,
							'self' => ($i - 1) * $interval,
							'next' => $i * $interval,
							'total_pages' => $pages
						);
		}
		foreach ($index as $_key => $_value) {
			if ($_value['last'] < 0) {
				$index[$_key]['last'] = null;
			}
			if ($_value['page'] == $_value['total_pages']) {
				$index[$_key]['next'] = null;
			}
		}
	}
	
	return $index;
}

/**
 * Creates date string as expected by MySQL's datetime fields from
 * QuickForm's date element.
 *
 * @var array
 * @return string
 */
public function datetimeFromQuickFormDate ($values)
{
	// import date elements from value array
	$day = (int)Base_Cnc::ifsetor($values['d'], null);
	$month = (int)Base_Cnc::ifsetor($values['m'], null);
	$year = (int)Base_Cnc::ifsetor($values['Y'], null);
	$hour = (int)Base_Cnc::ifsetor($values['H'], null);
	$minute = (int)Base_Cnc::ifsetor($values['i'], null);
	
	// if any of the first three date elements is null, return "now"
	if (empty($day) || empty($month) || empty($year)) {
		return date('Y-m-d H:i:s');
	}
	
	// prepare date string
	$day = str_pad($day, 2, '0', STR_PAD_LEFT);
	$month = str_pad($month, 2, '0', STR_PAD_LEFT);
	$year = str_pad($year, 4, '0', STR_PAD_LEFT);
	$hour = str_pad($hour, 2, '0', STR_PAD_LEFT);
	$minute = str_pad($minute, 2, '0', STR_PAD_LEFT);
	
	return sprintf('%s-%s-%s %s:%s:00', $year, $month, $day, $hour, $minute);
}

/**
 * Creates date string as expected by MySQL's datetime fields from
 * QuickForm's date element. Return null when empty.
 *
 * @var array
 * @return string
 */
public function datetimeFromQuickFormDateWithNull ($values)
{
	// import date elements from value array
	$day = (int)Base_Cnc::ifsetor($values['d'], null);
	$month = (int)Base_Cnc::ifsetor($values['m'], null);
	$year = (int)Base_Cnc::ifsetor($values['Y'], null);
	$hour = (int)Base_Cnc::ifsetor($values['H'], null);
	$minute = (int)Base_Cnc::ifsetor($values['i'], null);
	
	// if any of the first three date elements is null, return "null"
	if (empty($day) || empty($month) || empty($year)) {
		return null;
	}
	
	// Otherwise prepare date string and return it
	$day = str_pad($day, 2, '0', STR_PAD_LEFT);
	$month = str_pad($month, 2, '0', STR_PAD_LEFT);
	$year = str_pad($year, 4, '0', STR_PAD_LEFT);
	$hour = str_pad($hour, 2, '0', STR_PAD_LEFT);
	$minute = str_pad($minute, 2, '0', STR_PAD_LEFT);
	
	return sprintf('%s-%s-%s %s:%s:00', $year, $month, $day, $hour, $minute);
}

// end of class
}

class Utility_HelperException extends Exception { }

?>