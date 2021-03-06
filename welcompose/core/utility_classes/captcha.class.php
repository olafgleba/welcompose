<?php

/**
 * Project: Welcompose
 * File: captcha.class.php
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
 * Singleton. Returns instance of the Utility_Captcha object.
 * 
 * @return object
 */
function Utility_Captcha ()
{ 
	if (Utility_Captcha::$instance == null) {
		Utility_Captcha::$instance = new Utility_Captcha(); 
	}
	return Utility_Captcha::$instance;
}

class Utility_Captcha {
	
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
	 * Session key name where to save the captcha value to
	 * 
	 * @var string
	 */
	protected $_session_key = "_wcom_captcha";

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
		case 'numeral':
			return $this->createNumeralCaptcha();
		default:
			throw new Utility_CaptchaException("Unknown captcha type requested");
	}
	
}

/** 
 * Creates numeral captcha using PEAR's Text_CAPTCHA. Returns the
 * captcha string.
 *
 * @throws Utility_CaptchaException
 * @return string
 */
protected function createNumeralCaptcha  ()
{
	// create new CAPTCHA object using the equation driver
	$captcha = Text_CAPTCHA::factory('Equation');
	
	// generate captcha
	$response = $captcha->init();
	if (PEAR::isError($response)) {
		throw new Utility_CaptchaException("Captcha creation failed");
	}
	
	// save secret passphrase to session
	$_SESSION[$this->_session_key] = $captcha->getPhrase();
	
	// return the captcha string
	return $captcha->getCAPTCHA();
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
		'width' => 200,
		'height' => 60,
		'output' => 'png',
		'imageOptions' => array(
			'font_size' => 23,
			'font_path' => Base_Compat::fixDirectorySeparator(dirname(__FILE__).'/../other/bitstream_vera/'),
			'font_file' => 'Vera.ttf'
		)
	);
	
	// create new CAPTCHA object using the image driver
	$captcha = Text_CAPTCHA::factory('Image');
	
	// generate captcha
	$response = $captcha->init($options);
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