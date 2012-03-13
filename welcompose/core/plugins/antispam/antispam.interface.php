<?php

/**
 * Project: Welcompose
 * File: antispam.interface.php
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

interface Welcompose_AntiSpam_Comment {

/**
 * Creates new instance of anti spam plugin. Takes the user supplied
 * name as first argument, the user supplied email address (if any) as
 * second argument, the user supplied homepage url as third argument and
 * the user supplied message as fourth and last argument.
 *
 * If any of the arguments is empty, simply pass an empty string.
 *
 * @throws Welcompose_AntiSpam_CommentException
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
 * @throws Welcompose_AntiSpam_CommentException
 * @param bool
 */
public function setRegisteredUser ($flag);

/**
 * Returns current state of registered user flag.
 *
 * @throws Welcompose_AntiSpam_CommentException
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