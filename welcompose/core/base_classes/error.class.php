<?php

/**
 * Project: Base Classes
 * File: error.class.php
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
 * $Id: error.class.php 51 2007-02-21 21:22:54Z andreas $
 * 
 * @copyright 2004 - 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Base
 * @version php5.1-1.0
 * @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 */

// load PEAR::Log
require_once('Log.php');

// define constants
define('BASE_ERROR_ERROR', PEAR_LOG_ERROR);
define('BASE_ERROR_WARNING', PEAR_LOG_WARNING);
define('BASE_ERROR_NOTICE', PEAR_LOG_NOTICE);
define('BASE_ERROR_DEBUG', PEAR_LOG_DEBUG);

/**
 * Singleton for Base_Error.
 * 
 * @return object
 */
function Base_Error ($handler, $name, $level)
{
	if (Base_Error::$instance == null) {
		Base_Error::$instance = new Base_Error($handler, $name, $level); 
	}
	return Base_Error::$instance;
}

class Base_Error {
	
	/**
	 * Singleton
	 *
	 * @var object
	 */
	public static $instance = null;

/**
 * Creates new Base_Error object. Takes the log handler, the log name
 * and the log level for PEAR::Log as first, second and third argument.
 *
 * @throws Base_ErrorException
 * @param string Log handler name
 * @param string Log name
 * @param string Log level name (PEAR_LOG*)
 */
public function __construct ($handler, $name, $level)
{
	// input check
	if (empty($handler)) {
		throw new Base_ErrorException('Empty log handler configured');
	}
	switch ($level) {
		case 'PEAR_LOG_EMERG':
		case 'PEAR_LOG_ALERT':
		case 'PEAR_LOG_CRIT':
		case 'PEAR_LOG_ERR':
		case 'PEAR_LOG_WARNING':
		case 'PEAR_LOG_NOTICE':
		case 'PEAR_LOG_INFO':
		case 'PEAR_LOG_DEBUG':
			break;
		default:
			throw new Base_ErrorException('Unknown log level configured');
	}
	
	// create new PEAR::Log instance
	$this->log = Log::singleton($handler, $name);
	
	// set log level
	$this->log->setMask(Log::UPTO(constant($level)));
}

/**
 * Triggers user error. Takes the error code/level as first argument,
 * the error message as second argument and the file and line where
 * the error occurred (use the constants __FILE__ and __LINE__) as
 * third and fourth argument. 
 * 
 * Valid values for the error code/level are BASE_ERROR_ERROR,
 * BASE_ERROR_WARNING, BASE_ERROR_INFO and BASE_ERROR_DEBUG.
 *
 * Stops script execution on BASE_ERROR_ERROR.
 *
 * @param int Error code/level
 * @param string Error message
 * @param string File where the error occurred
 * @param int Line number where the error occurred
 */
public function triggerUserError ($code, $message, $file, $line)
{
	// make sure that a valid log level will be used
	switch ($code) {
		case PEAR_LOG_EMERG:
		case PEAR_LOG_ALERT:
		case PEAR_LOG_CRIT:
		case PEAR_LOG_ERR:
		case PEAR_LOG_WARNING:
		case PEAR_LOG_NOTICE:
		case PEAR_LOG_INFO:
		case PEAR_LOG_DEBUG:
			break;
		default:
			$code = PEAR_LOG_INFO;
	}
	
	// prepare log message
	$log_message = sprintf('%s in %s at line %u', strip_tags($message), $file, $line);
	
	// log event
	$this->log->log($log_message, $code);
	
	// exit if error is of type BASE_ERROR_ERROR
	if ($code == BASE_ERROR_ERROR) {
		exit(1);
	} else {
		return true;
	}
}

 /**
 * Will display an exception error message using smarty. Takes the
 * exception object as first argument, the smarty object as second
 * argument and the name of the error template as third argument.
 * Please note that displayException() does not stop script execution.
 * 
 * @param object error object (thrown exception)
 * @param object smarty object
 * @param string Template name
 */
public function displayException (&$error_object, &$smarty, $template = 'error.html')
{
	// pass exception information to smarty
	$smarty->assign('file', $error_object->getFile());
	$smarty->assign('message', $error_object->getMessage());
	$smarty->assign('code', $error_object->getCode());
	$smarty->assign('line', $error_object->getLine());
	$smarty->assign('trace', $error_object->getTrace());
	$smarty->assign('trace_string', $error_object->getTraceAsString());
	
	// fetch template & display
	$smarty->display($template, md5($_SERVER['REQUEST_URI']));
}

/**
 * Logs exception as PEAR_LOG_ERR and stops script execution.
 * Takes the error object as first argument. 
 *
 * @param object Exception object
 */
public function triggerException (&$error_object)
{
	// prepare log message
	$log_message = sprintf(
		"%s in %s at line %u\r\n%s",
		$error_object->getMessage(),
		$error_object->getFile(),
		$error_object->getLine(),
		$error_object->getTraceAsString()
	);
	
	// log event
	$this->log->log($log_message, PEAR_LOG_ERR);
	
	// stop script execution
	exit(1);
}

/**
 * Error handler for php to be used through set_error_handler(). Takes
 * the error code/level as first argument, the error message as second
 * argument and the file and line where the error occurred as third and
 * fourth argument. 
 *
 * Stops script execution on E_USER_ERROR and E_ERROR.
 *
 * @param int Error code
 * @param string Error message
 * @param string File name
 * @param int Line number
 */
public function phpErrorHandler ($code, $message, $file, $line)
{
	// map the php error to a PEAR::Log priority
	switch ($code) {
		case E_WARNING:
		case E_USER_WARNING:
				$priority = PEAR_LOG_WARNING;
			break;
		case E_NOTICE:
		case E_USER_NOTICE:
				$priority = PEAR_LOG_NOTICE;
			break;
		case E_ERROR:
		case E_USER_ERROR:
				$priority = PEAR_LOG_ERR;
			break;
		case E_STRICT:
				$priority = PEAR_LOG_DEBUG;
			break;
		default:
				$priority = PEAR_LOG_INFO;
	}
	
	// prepare log message
	$log_message = sprintf('%s in %s at line %u', strip_tags($message), $file, $line);
	
	// log event
	$this->log->log($log_message, $priority);
	
	if ($code === E_ERROR || $code === E_USER_ERROR) {
		exit(1);
	} else {
		return true;
	}
}

// end of class
}

class Base_ErrorException extends Exception { }

/**
 * Triggers user error. Takes the error code/level as first argument,
 * the error message as second argument and the file and line where
 * the error occurred (use the constants __FILE__ and __LINE__) as
 * third and fourth argument. 
 * 
 * Valid values for the error code/level are BASE_ERROR_ERROR,
 * BASE_ERROR_WARNING, BASE_ERROR_INFO and BASE_ERROR_DEBUG. 
 *
 * @param int Error code/level
 * @param string Error message
 * @param string File where the error occurred
 * @param int Line number where the error occurred
 */
function trigger_user_error ($code, $message, $file, $line)
{
	// get instance of Base_Error
	$Base_Error = Base_Error::$instance;
	if (!($Base_Error instanceof Base_Error)) {
		trigger_error('Create Base_Error instance before using trigger_user_error', E_USER_ERROR);
	}
	
	// handle event using Base_Error::triggerUserError()
	$Base_Error->triggerUserError($code, $message, $file, $line);
}

?>