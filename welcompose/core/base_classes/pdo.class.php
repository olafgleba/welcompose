<?php

/**
 * Project: Welcompose
 * File: pdo.class.php
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
 * $Id: pdo.class.php 50 2007-02-17 21:49:16Z andreas $
 *
 * @author Andreas Ahlenstorf, sopic GmbH
 * @copyright 2008 creatics, Olaf Gleba
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

class Base_Database {

	/**
	*Data Source Name (Connection information)
	*@var string
	*/
	protected $_dsn = null;

	/**
	*Username to use when connecting to the database
	*@var string
	*/
	protected $_username = null;
	
	/**
	*Password to use when connecting to the database
	*@var string
	*/
	protected $_password = null;
	
	/**
	*Array of pdo driver options
	*@var array
	*/
	protected $_driver_options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
	
	/**
	*Whether to import a config file with table aliases and to turn them
	*into constants
	*@param bool
	*/
	protected $_table_alias_constants = false;
	
	/**
	*The pdo database handle
	*@var object
	*/
	protected $pdo = null;
	
	/**
	*Enable/disable backticks
	*If enabled, backticks will automatically be added
	*before and after every field name
	*@var bool
	*/
	protected $_backticks = true;
	
	/**
	*Amount of affected rows
	*@var int
	*/
	public $_affected_rows = -1;

	/**
	*Display debug information or not
	*@var bool
	*/
	protected $_debug = false;
	
	/**
	*Fetch style (associative/numeric array etc) of
	*PDOStatement::fetch() and PDOStatement::fetchAll()
	*/
	protected $_fetch_style = PDO::FETCH_ASSOC;

/**
*Create new database object
*
*<b>Supported params for $params</b>:
*
*<ul>
*<li>dsn, string, required: Contains the information required to
*connect to the database</li>
*<li>username, string, optional: The user name for the DSN string</li>
*<li>password, string, optional: The password for the DSN string</li>
*<li>driver_options, array, optional: A key=>value array of
*driver-specific connection options</li>
*<li>debug, string, optional: Whether to enable the debug mode or
*not</li>
*</ul>
*
*<b>Supported params for $options</b>
*
*<ul>
*<li>debug, bool, optional: Whether to enable/disable the debug
*mode</li>
*</ul>
*
*@throws Base_DatabaseException
*@param array Connection configuration
*@param array Wrapper configuration
*/
public function __construct ($params)
{
	// input check
	if (!is_array($params)) {
		throw new Base_DatabaseException("Input for parameter params is not an array");	
	}
	if (!isset($params['dsn'])) {
		throw new Base_DatabaseException("No DSN supplied");
	}
	if (isset($params['username']) && !is_scalar($params['username'])) {
		throw new Base_DatabaseException("Username supplied but is not scalar");	
	}
	if (isset($params['password']) && !is_scalar($params['password'])) {
		throw new Base_DatabaseException("Password supplied but is not scalar");	
	}
	if (isset($params['driver_options']) && !is_array($params['driver_options'])) {
		throw new Base_DatabaseException("Driver options supplied but is not an array");
	}
	
	// import params
	foreach ($params as $_key => $_value) {
		switch ($_key) {
			case 'dsn':
					$dsn = sprintf((string)$_value, dirname(__FILE__));
					$dsn = Base_Compat::fixDirectorySeparator($dsn);
					$this->{'_'.$_key} = $dsn;
				break;
			case 'username':
			case 'password':
					$this->{'_'.$_key} = (string)$_value;
				break;
			case 'driver_options':
					$this->{'_'.$_key} = (array)$_value;
				break;
			case 'debug':
					$this->_debug = (((int)$_value === 1) ? true : false);
				break;
			case 'table_alias_constants':
					$this->_table_alias_constants = (((int)$_value === 1) ? true : false);
				break;
			case 'backticks':
					$this->_backticks = (((int)$_value === 1) ? true : false);
				break;			
			case 'driver':
				break;
			default:
				trigger_user_error(BASE_ERROR_INFO, "Unknown parameter $_key", __FILE__, __LINE__);
		}
	}
	
	// if _table_alias_constants is enabled, we need to import/create the constants
	if ($this->_table_alias_constants) {
		$this->_importTableAliasConstants();
	}
	
	// connect to database
	$this->connect();
}

/**
*Database destructor
*
*Closes connection to database
*/
public function __destruct ()
{
	unset($this->pdo);
}

/**
*Connect to a database
*
*Connects to a database server using the information
*provided by the params array passed to the constructor.
*
*@throws Base_DatabaseException
*/
private function connect ()
{
	try {
		// try to connect to database
		$this->pdo = new PDO($this->_dsn, $this->_username, $this->_password,
			$this->_driver_options);
		
		// let's pdo throw exceptions
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true); 
	} catch (PDOException $e) {
		// we have a problem...
		throw new Base_DatabaseException($e->getMessage());	
	}
}

/**
*Insert new row
*
*Inserts new row into table. First argument takes
*the table name, the second argument takes a
*key=>value array, where the key is the column
*name and the value is the data to insert into
*the column.
*
*@throws Base_DatabaseException
*@param string Table name
*@param array Row data
*@return int Insert id 
*/
public function insert($table, $data)
{
	try {
		// reset affected rows
		$this->resetAffectedRows();
	
		// input check
		if (empty($table) || !is_scalar($table)) {
			throw new Base_DatabaseException('Input for parameter table is not scalar');	
		}
		if (!is_array($data)) {
			throw new Base_DatabaseException('Input for parameter data is not an array');
		}
		$this->_checkDataFormat($data);
	
		// prepare data for prepared statement
		$prepared_data = $this->prepareInsertData($data);
		
		// prepare query
		$sql = sprintf("INSERT INTO %s ( %s ) VALUES ( %s )", $table,
			$prepared_data['field_names'], $prepared_data['bind_params']);
		
		// debug info
		if ($this->_debug) {
			$message = sprintf('%s, %s(): %s', __CLASS__, __FUNCTION__, $sql);
			trigger_user_error(BASE_ERROR_DEBUG, $message, __FILE__, __LINE__);
		}
		
		// prepare statement
		$stmt = $this->pdo->prepare($sql);
		
		// import bind params
		foreach ($prepared_data['bind_data'] as $_param => $_field) {
			if (!is_array($_field['value'])) {
				// bind param
				$stmt->bindParam($_param, ${$_field['key']});
			} else {
				// rearrange params
				$params = array($_param);
				foreach ($_field['value'] as $_var) {
					$params[] = $_var;
				}
				// bind param with optimal arguments
				call_user_func_array(array($stmt, 'bindParam'), $params);
			}
			// assign data
			${$_field['key']} = $_field['value'];
		}
		
		// execute prepared statement
		$stmt->execute();
	
		// set Base_Database::_affected_rows to new value
		$this->_affected_rows = $stmt->rowCount();
		
		// return last insert id
		return $this->pdo->lastInsertId();
	} catch (Exception $e) {
		throw new Base_DatabaseException($e->getMessage());	
	}
}

/**
*Update row
*
*Updates existing row. First argument takes
*the table name, the second argument takes a
*key=>value array, where the key is the column
*name and the value is the data to insert into
*the column. Third argument is the optional
*where clause. The fourth argument is a key=>value
*array of bind params for the where clause.
*
*@throws Base_DatabaseException
*@param string Table name
*@param array Row data
*@param string Where clause
*@param array Bind params
*@return int Affected rows
*/
public function update ($table, $data, $where = null, $bind_params = array())
{
	try {
		// reset affected rows
		$this->resetAffectedRows();
	
		// input check
		if (empty($table) || !is_scalar($table)) {
			throw new Base_DatabaseException('Input for parameter table is not scalar');	
		}
		if (!is_array($data)) {
			throw new Base_DatabaseException('Input for parameter data is not an array');
		}
		if (!empty($where) && !is_scalar($where)) {
			throw new Base_DatabaseException('Input for parameter where is not scalar');
		}
		if (!empty($bind_params) && !is_array($bind_params)) {
			throw new Base_DatabaseException('Input for parameter bind_params is not an array');
		}
		$this->_checkDataFormat($data);
		$this->_checkBindParamsFormat($bind_params);
	
		// prepare data for prepared statement
		$prepared_data = $this->prepareUpdateData($data);
		
		// prepare query
		$sql = sprintf("UPDATE %s SET %s %s", $table,
			$prepared_data['update_string'], $where);
		
		// debug info
		if ($this->_debug) {
			$message = sprintf('%s, %s(): %s', __CLASS__, __FUNCTION__, $sql);
			trigger_user_error(BASE_ERROR_DEBUG, $message, __FILE__, __LINE__);
		}
		
		// prepare statement
		$stmt = $this->pdo->prepare($sql);
		
		// import bind params
		foreach ($prepared_data['bind_data'] as $_param => $_field) {
			if (!is_array($_field['value'])) {
				// bind param
				$stmt->bindParam($_param, ${$_field['key']});
			} else {
				// rearrange params
				$params = array($_param);
				foreach ($_field['value'] as $_var) {
					$params[] = $_var;
				}
				// bind param with optimal arguments
				call_user_func_array(array($stmt, 'bindParam'), $params);
			}
			// assign data
			${$_field['key']} = $_field['value'];
		}
		
		// import bind params for where clause
		foreach ($this->prepareBindParams($bind_params) as $_param => $_field) {
			if (!is_array($_field['value'])) {
				// bind param
				$stmt->bindParam($_param, ${$_field['key']});
			} else {
				// rearrange params
				$params = array($_param);
				foreach ($_field['value'] as $_var) {
					$params[] = $_var;
				}
				// bind param with optimal arguments
				call_user_func_array(array($stmt, 'bindParam'), $params);
			}
			// assign data
			${$_field['key']} = $_field['value'];
		}
	
		// execute prepared statement
		$stmt->execute();
	
		// set Base_Database::_affected_rows to new value
		$this->_affected_rows = $stmt->rowCount();
		
		// return amount of affected rows
		return $stmt->rowCount();
	} catch (Exception $e) {
		throw new Base_DatabaseException($e->getMessage());	
	}
}

/**
*Delete row
*
*Deletes existing row(s). First argument takes
*the table name, the second argument is the
*optional where clause. The fourth argument is
*a key=>value array of bind params for the where
*clause.
*
*@throws Base_DatabaseException
*@param string Table name
*@param string Where clause
*@param array Bind params
*@return int Affected rows
*/
public function delete ($table, $where = null, $bind_params = array())
{
	try {
		// reset affected rows
		$this->resetAffectedRows();
	
		// input check
		if (empty($table) || !is_scalar($table)) {
			throw new Base_DatabaseException('Input for parameter table is not scalar');	
		}
		if (!empty($where) && !is_scalar($where)) {
			throw new Base_DatabaseException('Input for parameter where is not scalar');
		}
		if (!empty($bind_params) && !is_array($bind_params)) {
			throw new Base_DatabaseException('Input for parameter bind_params is not an array');
		}
		$this->_checkBindParamsFormat($bind_params);
		
		// prepare query
		$sql = sprintf("DELETE FROM %s %s", $table, $where);
		
		// debug info
		if ($this->_debug) {
			$message = sprintf('%s, %s(): %s', __CLASS__, __FUNCTION__, $sql);
			trigger_user_error(BASE_ERROR_DEBUG, $message, __FILE__, __LINE__);
		}

		// prepare statement
		$stmt = $this->pdo->prepare($sql);
		
		// import bind params for where clause
		foreach ($this->prepareBindParams($bind_params) as $_param => $_field) {
			if (!is_array($_field['value'])) {
				// bind param
				$stmt->bindParam($_param, ${$_field['key']});
			} else {
				// rearrange params
				$params = array($_param);
				foreach ($_field['value'] as $_var) {
					$params[] = $_var;
				}
				// bind param with optimal arguments
				call_user_func_array(array($stmt, 'bindParam'), $params);
			}
			// assign data
			${$_field['key']} = $_field['value'];
		}
	
		// execute prepared statement
		$stmt->execute();
	
		// set Base_Database::_affected_rows to new value
		$this->_affected_rows = $stmt->rowCount();
		
		// return amount of affected rows
		return $stmt->rowCount();
	} catch (Exception $e) {
		throw new Base_DatabaseException($e->getMessage());	
	}
}

/**
*Execute query
*
*Executes query without a return value. First
*argument takes the query string, the second
*argument takes a key=>value array of bind
*params.
*
*@throws Base_DatabaseException
*@param string Query string
*@param array Bind params
*@return int Affected rows
*/
public function execute ($sql, $bind_params = array())
{
	try {
		// reset affected rows
		$this->resetAffectedRows();
	
		// input check
		if (empty($sql) || !is_scalar($sql)) {
			throw new Base_DatabaseException('Input for parameter sql is not scalar');	
		}
		if (!empty($bind_params) && !is_array($bind_params)) {
			throw new Base_DatabaseException('Input for parameter bind_params is not an array');
		}
		$this->_checkBindParamsFormat($bind_params);
		
		// debug info
		if ($this->_debug) {
			$message = sprintf('%s, %s(): %s', __CLASS__, __FUNCTION__, $sql);
			trigger_user_error(BASE_ERROR_DEBUG, $message, __FILE__, __LINE__);
		}
		
		// prepare query
		$stmt = $this->pdo->prepare($sql);
		
		// import bind params for where clause etc.
		foreach ($this->prepareBindParams($bind_params) as $_param => $_field) {
			if (!is_array($_field['value'])) {
				// bind param
				$stmt->bindParam($_param, ${$_field['key']});
			} else {
				// rearrange params
				$params = array($_param);
				foreach ($_field['value'] as $_var) {
					$params[] = $_var;
				}
				// bind param with optimal arguments
				call_user_func_array(array($stmt, 'bindParam'), $params);
			}
			// assign data
			${$_field['key']} = $_field['value'];
		}
	
		// execute prepared statement
		$stmt->execute();
		
		// set Base_Database::_affected_rows to new value
		$this->_affected_rows = $stmt->rowCount();
		
		// return amount of affected rows
		return $stmt->rowCount();
	} catch (Exception $e) {
		throw new Base_DatabaseException($e->getMessage());
	}
}

/**
*Run a select
*
*Runs a select against a database. The first
*argument takes the query string, the second
*argument the select mode. The third argument
*is an a key=>value array of bind params.
*
*<b>Valid select modes:</b>
*
*<ul>
*<li>field: Returns only the value of a field</li>
*<li>row: Returns only one row</li>
*<li>multi: Returns an array containing multiple
*rows</li>
*</ul>
*
*@throws Base_DatabaseException
*@param string Query string
*@param string Select mode
*@param array Bind params
*@return array
*/
public function select ($sql, $mode, $bind_params = array())
{
	try {
		// reset affected rows
		$this->resetAffectedRows();
		
		// input check
		if (empty($sql) || !is_scalar($sql)) {
			throw new Base_DatabaseException('Input for parameter sql is not scalar');	
		}
		if (empty($mode) || !is_scalar($mode)) {
			throw new Base_DatabaseException('Input for parameter mode is not scalar');	
		}
		if (!empty($bind_params) && !is_array($bind_params)) {
			throw new Base_DatabaseException('Input for parameter bind_params is not an array');
		}
		$this->_checkBindParamsFormat($bind_params);
		
		// debug info
		if ($this->_debug) {
			$message = sprintf('%s, %s(): %s', __CLASS__, __FUNCTION__, $sql);
			trigger_user_error(BASE_ERROR_DEBUG, $message, __FILE__, __LINE__);
		}
		
		switch ($mode) {
			case 'field':
					// prepare query
					$stmt = $this->pdo->prepare($sql);
					// import bind params for where clause etc.
					foreach ($this->prepareBindParams($bind_params) as $_param => $_field) {
						if (!is_array($_field['value'])) {
							// bind param
							$stmt->bindParam($_param, ${$_field['key']});
						} else {
							// rearrange params
							$params = array($_param);
							foreach ($_field['value'] as $_var) {
								$params[] = $_var;
							}
							// bind param with optimal arguments
							call_user_func_array(array($stmt, 'bindParam'), $params);
						}
						// assign data
						${$_field['key']} = $_field['value'];
					}
					// execute prepared statement
					$stmt->execute();
					// make sure that we only select one column and not more
					if ($stmt->columnCount() > 1) {
						throw new Base_DatabaseException("Unable to select multiple columns with ".
							"select mode field");
					}
					// create string out of result array and return it
					return implode(null, (array)$stmt->fetch($this->_fetch_style));
				break;
			case 'row':
					// prepare query
					$stmt = $this->pdo->prepare($sql);
					// import bind params for where clause etc.
					foreach ($this->prepareBindParams($bind_params) as $_param => $_field) {
						// bind params
						$stmt->bindParam($_param, ${$_field['key']});
						// assign data
						${$_field['key']} = $_field['value'];
					}
					// execute prepared statement
					$stmt->execute();
					// fetch one row and return array
					return $stmt->fetch($this->_fetch_style);
				break;
			case 'multi':
					// prepare query
					$stmt = $this->pdo->prepare($sql);
					// import bind params for where clause etc.
					foreach ($this->prepareBindParams($bind_params) as $_param => $_field) {
						// bind params
						$stmt->bindParam($_param, ${$_field['key']});
						// assign data
						${$_field['key']} = $_field['value'];
					}
					// execute prepared statement
					$stmt->execute();
					// fetch all rows and return them in an array
					return $stmt->fetchAll($this->_fetch_style);
				break;
			default:
				throw new Base_DatabaseException("Unknown select mode $mode");
		}
	} catch (Exception $e) {
		throw new Base_DatabaseException($e->getMessage());	
	}
}

/**
*Begin transaction
*
*Disables autocommit mode and starts a new transaction.
*To re-enable the autocommit mode, call either
*Base_Database::commit() or Base_Database::rollback().
*
*@return bool
*/
public function begin ()
{
	// debug info
	if ($this->_debug) {
		trigger_user_error(BASE_ERROR_DEBUG, "Transaction started", __FILE__, __LINE__);
	}
	
	return $this->pdo->beginTransaction();
}

/**
*Commit transaction
*
*Commits a transaction and re-enables the autocommit
*mode.
*
*@return bool
*/
public function commit ()
{
	// debug info
	if ($this->_debug) {
		trigger_user_error(BASE_ERROR_DEBUG, "Commit issued", __FILE__, __LINE__);
	}
	
	return $this->pdo->commit();
}

/**
*Rollback transaction
*
*Rolls back any work in progress and re-enables the
*autocommit mode.
*
*@return bool
*/
public function rollback ()
{
	// debug info
	if ($this->_debug) {
		trigger_user_error(BASE_ERROR_DEBUG, "Rollback issued", __FILE__, __LINE__);
	}
	
	return $this->pdo->rollBack();
}

/**
 * Quotes string for usage in query. Wrapps around PDO::quote. See
 * the PHP manual for usage instructions.
 * 
 * @param string
 * @param int
 * @return string
 */
public function quote ($string, $type = PDO::PARAM_STR)
{
	return $this->pdo->quote($string, $type);
}

/**
*Prepare data for insert
*
*Prepares the query string and the bind params for
*an insert. The first argument is a key=>value array,
*where the key is the column name and the value is
*the data to insert into the column.
*
*@throws Base_DatabaseException
*@param array Row data
*@return array
*/
protected function prepareInsertData ($data)
{
	// define some vars
	$field_names = array();
	$bind_params = array();
	$bind_data = array();

	// input check
	if (!is_array($data)) {
		throw new Base_DatabaseException("Input for parameter data is not an array");	
	}

	// prepare field names and bind params
	foreach ($data as $_key => $_value) {
		if ($this->_backticks) {
			$field_names[] = '`'.$_key.'`';
		} else {
			$field_names[] = $_key;
		}
		$bind_params[] = ":".$_key;
		$bind_data[":".$_key] = array(
			'key' => $_key,
			'value' => $_value
		);
	}

	// return data
	return array(
		'field_names' => trim(implode(', ', $field_names)),
		'bind_params' => trim(implode(', ', $bind_params)),
		'bind_data' => $bind_data
	);
}

/**
*Prepare data for update
*
*Prepares the query string and the bind params for
*an update. The first argument is a key=>value array,
*where the key is the column name and the value is
*the data to insert into the column.
*
*@throws Base_DatabaseException
*@param array Row data
*@return array
*/
protected function prepareUpdateData ($data)
{
	// define some vars
	$update_strings = array();
	$bind_data = array();

	// input check
	if (!is_array($data)) {
		throw new Base_DatabaseException("Input for parameter data is not an array");	
	}
	
	// add backticks around field names?
	if ($this->_backticks) {
		$token = "`%s` = %s";
	} else {
		$token = "%s = %s";
	}

	// prepare field names and bind params
	foreach ($data as $_key => $_value) {
		$update_strings[] = sprintf($token, $_key, ":".$_key);
		$bind_data[":".$_key] = array(
			'key' => $_key,
			'value' => $_value
		);
	}

	// return data
	return array(
		'update_string' => trim(implode(', ', $update_strings)),
		'bind_data' => $bind_data
	);
}

/**
*Prepare bind params for where clause
*
*Prepares the bind params for a where clause.
*The first argument is a key=>value array, where
*the key is the column name and the value is
*the data to insert into the column.
*
*@throws Base_DatabaseException
*@param array Row data
*@return array
*/
protected function prepareBindParams ($data)
{
	// define some vars
	$bind_data = array();

	// input check
	if (!is_array($data)) {
		throw new Base_DatabaseException("Input for parameter data is not an array");	
	}

	foreach ($data as $_key => $_value) {
		$bind_data[":".$_key] = array(
			'key' => $_key,
			'value' => $_value
		);
	}
	
	return $bind_data;
}

/**
*Reset affected rows
*
*Function to reset the value of Base_Database::_affected_rows.
*/
protected function resetAffectedRows ()
{
	$this->_affected_rows = -1;	
}

/**
 * SQLite Table Exists
 * 
 * Utility function that checks if a table existis
 * within a sqlite database. Takes the table name
 * as first argument, returns bool.
 *
 * @param string Table name
 * @return bool
 */
public function _sqliteTableExists ($table_name)
{
	// input check
	if (empty($table_name) || !is_scalar($table_name)) {
		throw new Base_DatabaseException("Input for parameter table_name is not scalar");
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*)
		FROM
			sqlite_master
		WHERE
			type = 'table'
		  AND
			name= :table_name
	";
	
	// prepare bind params
	$bind_params = array(
		'table_name' => $table_name
	);
	
	// execute query and get result
	$result = $this->select($sql, 'field', $bind_params);
	
	if ((int)$result === 1) {
		return true;
	} else {
		return false;
	}
}

/**
 * Auto generate params
 * 
 * Auto generates params for prepared statements.
 * Takes list of values as first argument. Returns
 * array with three elements:
 * 
 * <ul>
 * <li>__param_names: List of bind param names</li>
 * <li>__param_list: List of bind param names for
 * insert in query</li>
 * <li>__params: Array to append to $bind_params</li>
 * </ul>
 *
 * @param array Value list
 * @return array
 */
public function _stmtAutoGenParams ($values)
{
	// set value count
	$value_count = count($values);

	// initialize array for param storage
	$params = array(
		'__param_names' => array(),
		'__param_list' => array(),
		'__params' => array()
	);
	
	// create lists of bind param names
	while (count($params['__param_names']) < $value_count) {
		$name = Base_Cnc::randomString(5);
		$params['__param_names'][] = $name;
		$params['__param_list'][] = ':'.$name;
		array_unique($params['__param_names']);
		array_unique($params['__param_list']);
	}
	
	// prepare array that can be appended to $bind_params
	foreach ($params['__param_names'] as $_value) {
		$params['__params'][$_value] = array_shift($values);
	}
	
	return $params;
}

/**
 * Imports table aliases.
 *
 * @return bool
 */
protected function _importTableAliasConstants ()
{
	// load regex constants
	$file = dirname(__FILE__).'/../conf/table_aliases.inc.php';
	$file = Base_Compat::fixDirectorySeparator($file);
	if (file_exists($file) && is_readable($file)) {
		require_once($file);
	} else {
		trigger_user_error(BASE_ERROR_ERROR, "Constant file table_aliases.inc.php not found", __FILE__, __LINE__);
	}
	
	return true;
}

/** 
 * Checks if provided sql data is in valid format. Takes array
 * with sql data array as first argument. Returns boolean true.
 *
 * @throws Base_DatabaseException
 * @param array Sql Data
 * @return bool
 */
protected function _checkDataFormat (&$data)
{
	if (!is_array($data)) {
		throw new Base_DatabaseException("Input for parameter data is not an array");
	}
	foreach ($data as $_key => $_value) {
		if (!is_scalar($_key)) {
			throw new Base_DatabaseException("Some key in sql data array is not scalar");
		}
		if (!is_null($_value) && !is_scalar($_value)) {
			throw new Base_DatabaseException("Element $_key in sql data array is not scalar");
		}
	}
	
	return true;
}

/** 
 * Checks if provided array with bind params is in valid format. Takes
 * array with bind params array as first argument. Returns boolean true.
 *
 * @throws Base_DatabaseException
 * @param array Bind params array
 * @return bool
 */
protected function _checkBindParamsFormat (&$bind_params)
{
	if (!is_array($bind_params)) {
		throw new Base_DatabaseException("Input for parameter bind_params is not an array");
	}
	foreach ($bind_params as $_key => $_value) {
		if (!is_scalar($_key)) {
			throw new Base_DatabaseException("Some key in bind params array is not scalar");
		}
		if (!is_scalar($_value)) {
			throw new Base_DatabaseException("Element $_key in bind params array is not scalar");
		}
	}
	
	return true;
}

// end of class
}

class Base_DatabaseException extends Exception {}

?>
