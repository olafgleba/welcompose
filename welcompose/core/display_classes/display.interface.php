<?php

/**
 * Project: Welcompose
 * File: display.interface.php
 * 
 * Copyright (c) 2008 creatics media.systems
 * 
 * Project owner:
 * creatics media.systems, Olaf Gleba
 * 50939 Köln, Germany
 * http://www.creatics.de
 *
 * This file is licensed under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * http://www.opensource.org/licenses/agpl-v3.html
 * 
 * $Id$
 * 
 * @copyright 2008 creatics media.systems, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

interface Display {

/**
 * Creates new instance of display driver. Takes an array
 * with the project information as first argument, an array
 * with the information about the current page as second
 * argumenht.
 * 
 * @param array Project information
 * @param array Page information
 */
public function __construct($project, $page);

/**
 * Default method that will be called from the display script
 * and has to care about the page preparation. Returns boolean
 * true on success.
 * 
 * @return bool
 */ 
public function render ();

/**
 * Returns the cache mode for the current template.
 * 
 * @return int
 */
public function getMainTemplateCacheMode ();

/**
 * Returns the cache lifetime of the current template.
 * 
 * @return int
 */
public function getMainTemplateCacheLifetime ();

/** 
 * Returns the name of the current template.
 * 
 * @return string
 */ 
public function getMainTemplateName ();

/**
 * Returns the redirect location of the the current
 * document (~ $PHP_SELF without it's problems) with the
 * Location: header prepended.
 * 
 * @return string
 */
public function getRedirectLocationSelf ();

/**
 * Returns the redirect location of the the current
 * document (~ $PHP_SELF without it's problems). Already
 * encoded ampersands will be removed if the optional
 * parameter remove_amps is set to true.
 * 
 * @param bool Remove encoded ampersands
 * @return string
 */
public function getLocationSelf ($remove_amps = false);

/**
 * Returns information whether to skip authentication
 * or not.
 * 
 * @return bool
 */
public function skipAuthentication ();

}

?>