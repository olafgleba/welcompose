<?php

/**
 * Project: Oak
 * File: antispam.interface.php
 * 
 * Copyright (c) 2006 sopic GmbH
 * 
 * Project owner:
 * sopic GmbH
 * 8472 Seuzach, Switzerland
 * http://www.sopic.com/
 *
 * This file is licensed under the terms of the Open Software License
 * http://www.opensource.org/licenses/osl-2.1.php
 * 
 * $Id$
 * 
 * @copyright 2006 sopic GmbH
 * @author Andreas Ahlenstorf
 * @package Oak
 * @license http://www.opensource.org/licenses/osl-2.1.php Open Software License
 */

interface Oak_AntiSpam_Comment {

/**
 * Creates new instance of anti spam plugin. Takes the user supplied
 * name as first argument, the user supplied email address (if any) as
 * second argument, the user supplied homepage url as third argument and
 * the user supplied message as fourth and last argument.
 *
 * If any of the arguments is empty, simply pass an empty string.
 *
 * @throws Oak_AntiSpam_CommentException
 * @param string Name
 * @param string Email address
 * @param string Homepage URL
 * @param string Message
 */
public function __construct($name, $email, $homepage, $message);

/**
 * Executes spam check. Returns boolean true if the spam check
 * was successfully executed. Note that this says nothing about
 * the comment being spam or not.
 */
public function check ();

/**
 * Returns the api version used by the anti spam plugin.
 *
 * @return int
 */
public function getApiVersion ();

/**
 * Returns the plugin name.
 *
 * @return string
 */
public function getPluginName ();

/**
 * Sets wheter the posting user is registered or not.
 *
 * @throws Oak_AntiSpam_CommentException
 * @param bool
 */
public function setRegisteredUser ($flag);

/**
 * Returns current state of registered user flag.
 *
 * @throws Oak_AntiSpam_CommentException
 * @return bool
 */
public function getRegisteredUser ();

/**
 * Returns result of previously executed spam check.
 * True indicates ham, false indicates spam. 
 * 
 * @return bool
 */
public function getResult ();

/**
 * Returns string with detailed spam report.
 *
 * @return string
 */
public function getReport ();

}

?>