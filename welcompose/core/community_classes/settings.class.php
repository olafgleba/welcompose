<?php

/**
 * Project: Welcompose
 * File: settings.class.php
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

/**
 * Singleton for Community_Settings.
 * 
 * @return object
 */
function Community_Settings ()
{
	if (Community_Settings::$instance == null) {
		Community_Settings::$instance = new Community_Settings(); 
	}
	return Community_Settings::$instance;
}

class Community_Settings {
	
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
 * Returns array with community settings of the current project.
 *
 * @return array
 */
public function getSettings ()
{
	// access check
	if (!wcom_check_access('Community', 'Settings', 'Use')) {
		throw new Community_BlogCommentStatusException("You are not allowed to perform this action");
	}
	
	// make sure that the settings exist. otherwise initialise
	// a configuration for the current project.
	if (!$this->settingsExist()) {
		$this->settingsInit();
	}
	
	// prepare query
	$sql = "
		SELECT
			`community_settings`.`id`,
			`community_settings`.`project`,
			`community_settings`.`blog_comment_display_status`,
			`community_settings`.`blog_comment_default_status`,
			`community_settings`.`blog_comment_spam_status`,
			`community_settings`.`blog_comment_ham_status`,
			`community_settings`.`blog_comment_use_captcha`,
			`community_settings`.`blog_comment_timeframe_threshold`,
			`community_settings`.`blog_comment_bayes_autolearn`,
			`community_settings`.`blog_comment_bayes_autolearn_threshold`,
			`community_settings`.`blog_comment_bayes_spam_threshold`,
			`community_settings`.`blog_comment_text_converter`,
			`community_settings`.`blog_trackback_display_status`,
			`community_settings`.`blog_trackback_default_status`,
			`community_settings`.`blog_trackback_spam_status`,
			`community_settings`.`blog_trackback_ham_status`,
			`community_settings`.`blog_trackback_timeframe_threshold`,
			`community_settings`.`blog_trackback_bayes_autolearn`,
			`community_settings`.`blog_trackback_bayes_autolearn_threshold`,
			`community_settings`.`blog_trackback_bayes_spam_threshold`
		FROM
			".WCOM_DB_COMMUNITY_SETTINGS." AS `community_settings`
		WHERE
			`community_settings`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and return array
	return $this->base->db->select($sql, 'row', $bind_params);
}

/**
 * Saves community settings. Takes an array with the new settings
 * as first argument. Returns amount of affected rows.
 *
 * @throws Community_SettingsException
 * @param array Settings
 * @return int Amount of affected rows 
 */
public function saveSettings ($settings)
{
	// access check
	if (!wcom_check_access('Community', 'Settings', 'Manage')) {
		throw new Community_BlogCommentStatusException("You are not allowed to perform this action");
	}
	
	// input check
	if (!is_array($settings)) {
		throw new Community_SettingsException("Input for parameter settings is not an array");
	}
	
	// make sure that the settings exist. otherwise initialise
	// a configuration for the current project.
	if (!$this->settingsExist()) {
		$this->settingsInit();
	}
	
	// turn empty strings into null
	foreach ($settings as $_key => $_value) {
		if ($_value == "") {
			$settings[$_key] = null;
		}
	}
	
	// prepare where clause
	$where = " WHERE `project` = :project ";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// update configuration
	return $this->base->db->update(WCOM_DB_COMMUNITY_SETTINGS, $settings, $where, $bind_params);
}

/**
 * Tests whether community settings of the current project
 * exist. Returns bool.
 * 
 * @return bool
 */
protected function settingsExist ()
{
	// access check
	if (!wcom_check_access('Community', 'BlogCommentStatus', 'Use')) {
		throw new Community_BlogCommentStatusException("You are not allowed to perform this action");
	}
	
	// prepare query
	$sql = "
		SELECT
			COUNT(*) AS `total`
		FROM
			".WCOM_DB_COMMUNITY_SETTINGS." AS `community_settings`
		WHERE
			`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => WCOM_CURRENT_PROJECT
	);
	
	// execute query and evaluate result
	if (intval($this->base->db->select($sql, 'field', $bind_params)) === 1) {
		return true;
	} else {
		return false;
	}
}

/**
 * Intializes row with community settings for the
 * current project. Returns insert id.
 * 
 * @return int
 */
protected function settingsInit ()
{
	// access check
	if (!wcom_check_access('Community', 'Settings', 'Manage')) {
		throw new Community_SettingsException("You are not allowed to perform this action");
	}
	
	// make sure that there are no orphaned rows with settings
	$where = " WHERE `project` = :project ";
	$bind_params = array('project' => WCOM_CURRENT_PROJECT);
	$this->base->db->delete(WCOM_DB_COMMUNITY_SETTINGS, $where, $bind_params);
	
	// prepare sql data
	$sqlData = array(
		'project' => WCOM_CURRENT_PROJECT,
		'blog_comment_use_captcha' => "0",
		'blog_comment_timeframe_threshold' => 0,
		'blog_comment_bayes_autolearn' => "0",
		'blog_comment_bayes_autolearn_threshold' => 0,
		'blog_comment_bayes_spam_threshold' => 0,
		'blog_trackback_timeframe_threshold' => 0,
		'blog_trackback_bayes_autolearn' => "0",
		'blog_trackback_bayes_autolearn_threshold' => 0,
		'blog_trackback_bayes_spam_threshold' => 0	
	);
	
	// insert row
	return $this->base->db->insert(WCOM_DB_COMMUNITY_SETTINGS, $sqlData);
}

// end of class
}

class Community_SettingsException extends Exception { }

?>