<?php

/**
 * Project: Welcompose
 * File: captcha.class.php
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

class Utility_Captcha {
	
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
	 * Session key name where to save the captcha value to
	 * @var string
	 */
	protected $_session_key = "_wcom_captcha";

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
		
		// load PEAR Text_CAPTCHA
		require_once 'Text/CAPTCHA.php';
		
	} catch (Exception $e) {
		
		// trigger error
		printf('%s on Line %u: Unable to start base class. Reason: %s.', $e->getFile(),
			$e->getLine(), $e->getMessage());
		exit;
	}
}

/**
 * Singleton. Returns instance of the Utility_Captcha object.
 * 
 * @return object
 */
public function instance()
{ 
	if (Utility_Captcha::$instance == null) {
		Utility_Captcha::$instance = new Utility_Captcha(); 
	}
	return Utility_Captcha::$instance;
}

/**
 * Wrapper for the different captcha generators. Takes the captcha type as
 * first argument. Available captcha types:
 * 
 * <ul>
 * <li>image: Creates image captcha. Returns the www path from the doc root
 * to the image.</li>
 * </ul>
 * 
 * @throws Utility_CaptchaException
 * @param string Captcha type
 * @return string 
 */
public function createCaptcha ($type)
{
	switch ((string)$type) {
		case 'image':
			return $this->createImageCaptcha();
		default:
			throw new Utility_CaptchaException("Unknown captcha type requested");
	}
	
}

/** 
 * Creates image captcha using PEAR's Text_CAPTCHA. Returns the www path from
 * the doc root to the image.
 *
 * @throws Utility_CaptchaException
 * @return string
 */
protected function createImageCaptcha  ()
{
	// remove captcha images older than one hour
	$this->imageCaptchaCleanup();
	
	// preppare CAPTCHA options
	$options = array(
		'font_size' => 23,
		'font_path' => Base_Compat::fixDirectorySeparator(dirname(__FILE__).'/../other/bitstream_vera/'),
		'font_file' => 'Vera.ttf'
	);
	
	// create new CAPTCHA object using the image driver
	$captcha = Text_CAPTCHA::factory('Image');
	
	// generate captcha
	$response = $captcha->init(200, 60, null, $options);
	if (PEAR::isError($response)) {
		throw new Utility_CaptchaException("Captcha creation failed");
	}
	
	// save secret passphrase to session
	$_SESSION[$this->_session_key] = $captcha->getPhrase();
	
	// get image
	$image = $captcha->getCAPTCHAAsPNG();
	if (PEAR::isError($image)) {
		throw new Utility_CaptchaException("Captcha creation failed");
	}
	
	// prepare image save path
	$image_save_path = $this->getImageCaptchaSavePath().DIRECTORY_SEPARATOR.$this->getImageCaptchaFilename();
	
	// test if image save path is writable
	if (!is_writable($this->getImageCaptchaSavePath())) {
		throw new Utility_CaptchaException("Captcha save path is not writable");
	}
	
	// save image to disk
	file_put_contents($image_save_path, $image);
	
	// return web path to the captcha
	return $this->getImageCaptchaWwwPath();
}

/**
 * Returns the current captcha value that the user input should match.
 *
 * @return string
 */
public function captchaValue ()
{
	if (!array_key_exists($this->_session_key, $_SESSION)) {
		$_SESSION[$this->_session_key] = null;
	}
	return $_SESSION[$this->_session_key];
}

/**
 * Returns full path to the directory where the image captchas will be
 * saved.
 *
 * @return string
 */
protected function getImageCaptchaSavePath ()
{
	// prepare image save path
	$path_parts = array(
		dirname(__FILE__),
		'..',
		'..',
		'tmp',
		'captchas'
	);
	return implode(DIRECTORY_SEPARATOR, $path_parts);
}

/**
 * Returns full www path from the doc root to the image captcha.
 *
 * @return string
 */
protected function getImageCaptchaWwwPath ()
{
	return $this->base->_conf['path']['wcom_public_root_www'].'/tmp/captchas/'.
		$this->getImageCaptchaFilename();
}

/**
 * Returns file name of the current image captcha.
 *
 * @return string
 */
protected function getImageCaptchaFilename ()
{
	return md5(session_id()).".png";
}

/**
 * Cleanup function to remove captcha images older than one hour.
 */
protected function imageCaptchaCleanup ()
{
	$dir = dir($this->getImageCaptchaSavePath());
	while (false !== ($file = $dir->read())) {
		$path = $dir->path.DIRECTORY_SEPARATOR.$file;
		if ($file != '.' && $file != '..' && filemtime($path) > 3600) {
			unlink($path);
		}
	}
	$dir->close();
}

// end of class
}

class Utility_CaptchaException extends Exception { }

?>