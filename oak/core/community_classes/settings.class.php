<?php

/**
 * Project: Oak
 * File: settings.class.php
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

class Community_Settings {
	
	/**
	 * Singleton
	 * @var object
	 */
	private static $instance = null;
	
	/**
	 * Reference to base class
	 * @var object
	 */
	public $base = null;

/**
 * Start instance of base class, load configuration and
 * establish database connection. Please don't call the
 * constructor direcly, use the singleton pattern instead.
 */
protected function __construct()
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
 * Singleton. Returns instance of the Community_Settings object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Community_Settings::$instance == null) {
		Community_Settings::$instance = new Community_Settings(); 
	}
	return Community_Settings::$instance;
}

/**
 * Returns array with community settings of the current project.
 *
 * @return array
 */
public function getSettings ()
{
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
			".OAK_DB_COMMUNITY_SETTINGS." AS `community_settings`
		WHERE
			`community_settings`.`project` = :project
		LIMIT
			1
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
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
		'project' => OAK_CURRENT_PROJECT
	);
	
	// update configuration
	return $this->base->db->update(OAK_DB_COMMUNITY_SETTINGS, $settings, $where, $bind_params);
}

/**
 * Tests whether community settings of the current project
 * exist. Returns bool.
 * 
 * @return bool
 */
protected function settingsExist ()
{
	// prepare query
	$sql = "
		SELECT
			COUNT(*) AS `total`
		FROM
			".OAK_DB_COMMUNITY_SETTINGS." AS `community_settings`
		WHERE
			`project` = :project
	";
	
	// prepare bind params
	$bind_params = array(
		'project' => OAK_CURRENT_PROJECT
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
	// make sure that there are no orphaned rows with settings
	$where = " WHERE `project` = :project ";
	$bind_params = array('project' => OAK_CURRENT_PROJECT);
	$this->base->db->delete(OAK_DB_COMMUNITY_SETTINGS, $where, $bind_params);
	
	// prepare sql data
	$sqlData = array(
		'project' => OAK_CURRENT_PROJECT,
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
	return $this->base->db->insert(OAK_DB_COMMUNITY_SETTINGS, $sqlData);
}

// end of class
}

class Community_SettingsException extends Exception { }

?>