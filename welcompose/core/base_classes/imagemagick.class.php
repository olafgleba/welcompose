<?php

/**
 * Project: Welcompose
 * File: imagemagick.class.php
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
 * $Id: imagemagick.class.php 32 2006-02-28 21:09:35Z andreas $
 * 
 * @copyright 2008 creatics, Olaf Gleba
 * @author Andreas Ahlenstorf
 * @package Welcompose
 * @license http://www.opensource.org/licenses/agpl-v3.html GNU AFFERO GENERAL PUBLIC LICENSE v3
 */

class Base_Imagemagick {
	
	/**
	*Singleton
	*@var object
	*/
	static $instance = null;
	
	/**
	*Path to convert binary
	*@var string
	*/
	private $_convert_bin = '/usr/bin/convert';
	
/**
 * Constructor
 * 
 * Please use Base_Imagemagick::instance() to get an
 * instance of this class.
 * 
 * @throws Base_ImagemagickException
 * @param string Path to convert binary
 */
protected function __construct ($convert_bin)
{
	// input check
	if (empty($convert_bin)) {
		throw new Base_ImagemagickException("Input for configuration setting convert_bin is empty");
	}
	if (!file_exists($convert_bin)) {
		throw new Base_ImagemagickException("Unable to find ImageMagick's convert binary");
	}
	if (!is_executable($convert_bin)) {
		throw new Base_ImagemagickException("ImageMagick's convert binary is not executable");
	}
	
	$this->_convert_bin = $convert_bin;
}

/**
 * Singleton
 * 
 * @param string path to convert binary
 * @return object
 */
public function instance($convert_bin)
{ 
	if (Base_Imagemagick::$instance == NULL) {
		Base_Imagemagick::$instance = new Base_Imagemagick($convert_bin); 
	}
	return Base_Imagemagick::$instance; 
} 

/**
 * Convert image
 * 
 * Converts image old_file from one format to another and saves the result
 * to new_file. Takes the source file name as first argument and the target
 * file name as second argument. If the target file already exists it will
 * be overwritten unless overwrite is set to false.
 * 
 * <b>Used ImageMagick command</b>
 * <code>
 * $ /usr/bin/convert source.tiff image.jpg
 * </code>
 * 
 * @throws Base_ImagemagickException
 * @param string Source file
 * @param string Target file
 * @param bool Overwrite target file yes/no
 * @return string Path to target file
*/
public function convert ($old_file, $new_file, $overwrite = true)
{
	// check if $old_file is ok
	$old_file = $this->sourceFileCheck($old_file);
	
	// check if $new_file is ok
	$new_file = $this->targetFileCheck($new_file, $overwrite);
	
	// /usr/bin/convert source.tiff image.jpg
	$cmd = sprintf(
					'"%s" "%s" "%s"',
					escapeshellarg($this->_convert_bin),
					escapeshellarg($old_file),
					escapeshellarg($new_file)
	);
	
	$output = 100;
	
	// execute command
	passthru($cmd, $output);

	if  (!is_null($output) && $output != 0) {
		throw new Base_ImagemagickException('Operation failed');
	}
	
	return $new_file;
}

/**
*Scale image
*
*Scales $old_file to the desired $size and saves the result
*to $new_file.
*
*<b>CLI command</b>
*<code>
*$ /usr/bin/convert -scale <size> source.tiff image.jpg
*</code>
*
*For further details regarding the possible values for $size see 
*http://www.imagemagick.org/www/ImageMagick.html#details-scale
*
*@throws Base_ImagemagickException
*@param string file to scale
*@param string file to save result to
*@param string desired size
*@param bool overwrite $new_file if $new_file already exists
*@return string returns $new_file
*/
public function scale ($old_file, $new_file, $size, $overwrite = true)
{
	// input check
	if (empty($old_file)) {
		throw new Base_ImagemagickException("No source file supplied");	
	}
	if (empty($new_file)) {
		throw new Base_ImagemagickException("No target file supplied");	
	}
	if (empty($size)) {
		throw new Base_ImagemagickException("No file size supplied");	
	}
	
	// check if $old_file is ok
	$file = $this->sourceFileCheck($old_file);
	
	// check if $new_file is ok
	$file = $this->targetFileCheck($new_file, $overwrite);
	
	// /usr/bin/convert -scale <size> source.tiff image.jpg
	$cmd = sprintf(
					'"%s" -scale "%s" "%s" "%s"',
					escapeshellarg($this->_convert_bin),
					escapeshellarg($size),
					escapeshellarg($old_file),
					escapeshellarg($new_file)
	);
	
	$output = 100;
	
	// execute command
	passthru($cmd, $output);

	if  (!is_null($output) && $output != 0) {
		throw new Base_ImagemagickException('Operation failed');
	}
	
	return $new_file;
}

/**
*Rotate image
*
*Rotate image $old_file into a new position and save the
*result top $new_file. $degrees could be either a negative
*or a positive number.
*
*<b>CLI command</b>
*<code>
*$ /usr/bin/convert -rotate <size> source.tiff image.jpg
*</code>
*
*@throws Base_ImagemagickException
*@param string image to rotate
*@param string file to save result to
*@param string degrees to rotate
*@param bool overwrite $new_file if $new_file already exists
*@return string returns $new_file
*/
public function rotate ($old_file, $new_file, $degrees, $overwrite = true)
{
	// check if $old_file is ok
	$old_file = $this->sourceFileCheck($old_file);
	
	// check if $new_file is ok
	$new_file = $this->targetFileCheck($new_file, $overwrite);
	
	// check if $degrees is ok
	if (!is_numeric($degrees)) {
		throw new Base_ImagemagickException('$degress is not numeric');
	}
	
	// /usr/bin/convert -rotate <size> source.tiff image.jpg
	$cmd = sprintf(
					'"%s" -rotate "%d" "%s" "%s"',
					escapeshellarg($this->_convert_bin),
					escapeshellarg($degrees),
					escapeshellarg($old_file),
					escapeshellarg($new_file)
	);
	
	$output = 100;
	
	// execute command
	passthru($cmd, $output);

	if  (!is_null($output) && $output != 0) {
		throw new Base_ImagemagickException('Operation failed');
	}
	
	return $new_file;
}

/**
*Create watermarks
*
*Creates a watermark on $old_files and saves the result
*to $new_file. $conf is an array of configuration settings.
*
*Contents of $conf:
*<ul>
*<li>font: Path to the TTF file</li>
*<li>pointsize: Font size in points</li>
*<li>color: Font color</li>
*<li>position: Start position of the text (x, y)</li>
*<li>text: Text to draw</li>
*</ul>
*
*A list of allowed values for color could be found on
*http://imagemagick.org/www/ImageMagick.html#details-fill.
*
*<b>CLI command</b>
*<code>
*$ /usr/bin/convert -font euron.ttf -fill white -pointsize 16 \
*> -draw 'text 10,160 "This is a watermark"' \
*> keira_knightley.jpg keira_watermark.jpg
*</code>
*
*@throws Base_ImagemagickException
*@param string file to manipulate
*@param string file to safe result to
*@param array config array
*@param bool overwrite $new_file if $new_file already exists
*@return string returns $new_file
*/
public function watermark ($old_file, $new_file, $conf, $overwrite = true)
{
	// check if $old_file is ok
	$old_file = $this->sourceFileCheck($old_file);
	
	// check if $new_file is ok
	$new_file = $this->targetFileCheck($new_file, $overwrite);
	
	// check conf
	$this->watermarkConfCheck($conf);

	/*
	$ /usr/bin/convert -font euron.ttf -fill white -pointsize 16 \
	> -draw 'text 10,160 "This is a watermark"' \
	> keira_knightley.jpg keira_watermark.jpg
	*/
	$cmd = sprintf(
					'"%s" -font "%s" -fill "%s" -pointsize "%d" -draw \'text %s "%s"\' "%s" "%s"',
					escapeshellarg($this->_convert_bin),
					escapeshellarg($conf['font']),
					escapeshellarg($conf['color']),
					escapeshellarg($conf['pointsize']),
					escapeshellarg($conf['position']),
					escapeshellarg($conf['text']),
					escapeshellarg($old_file),
					escapeshellarg($new_file)
	);
	
	$output = 100;
	
	// execute command
	passthru($cmd, $output);

	if  (!is_null($output) && $output != 0) {
		throw new Base_ImagemagickException('Operation failed');
	}
	
	return $new_file;
}

/**
*Check source files
*
*Verifies that $file does exist and is an image. If
*one of these conditions isn't true, an exception will
*be thrown.
*
*@throws Base_ImagemagickException
*@param string path to source file
*@return string returns $file
*/
protected function sourceFileCheck ($file)
{
	// make sure $file isn't empty
	if (empty($file)) {
		throw new Base_ImagemagickException('$file is empty');	
	}
	
	// input check
	$file = @realpath($file);
	
	if ($file === false || !is_file($file)) {
		throw new Base_ImagemagickException('File does not exist');
	}
	
	// make sure that $file is an image
	if (!preg_match("=^image\/(.*)$=i", mime_content_type($file))) {
		throw new Base_ImagemagickException('File is not an image');
	}
	
	// make sure the source file is readable
	if (!is_readable($file)) {
		throw new Base_ImagemagickException('Source file is not readable');
	}
	
	return $file;
}

/**
*Check target files
*
*Verifies that $file doesn't exist (if $overwrite = false)
*and that the target directory is writeable by the webserver.
*
*@throws Base_ImagemagickException
*@param string target file
*@param bool overwrite $file if $file already exists
*@return $file
*/
protected function targetFileCheck ($file, $overwrite = true)
{
	// make sure $file isn't empty
	if (empty($file)) {
		throw new Base_ImagemagickException('$file is empty');	
	}
	
	// if $overwrite is false, make sure that target file does not exist
	if ($overwrite === false) {
		if (file_exists($file)) {
			throw new Base_ImagemagickException('File already exists ($overwrite = false)');	
		}
	}
	
	if ($overwrite === true && file_exists($file) && !is_writeable($file)) {
		throw new Base_ImagemagickException('Target file exists and is not writeable');
	}
	
	// make sure that the target directory is writeable
	if (!is_writeable(dirname($file))) {
		throw new Base_ImagemagickException('Target directory is not writeable');
	}
	
	return $file;
}

/**
*Check font file
*
*Verifies that $file does exist and is readable by
*the webserver.
*
*@throws Base_ImagemagickException
*@param string target file
*@return $file
*/
protected function fontFileCheck ($file)
{
	// make sure $file isn't empty
	if (empty($file)) {
		throw new Base_ImagemagickException('$file is empty');	
	}
	
	// make sure the font file exists
	$file = @realpath($file);
	if ($file === false) {
		throw new Base_ImagemagickException('Font file not found');	
	}
	
	// check if it's readable
	if (!is_readable($file)) {
		throw new Base_ImagemagickException('Font file not readable by the webserver');	
	}
	
	return $file;
}

/**
*Check watermark configuration
*
*See watermark() for allowed values
*
*@throws Base_ImagemagickException
*@param array array of configuration values
*/
protected function watermarkConfCheck (&$conf)
{
	// check $conf
	if (!is_array($conf)) {
		throw new Base_ImagemagickException('$conf is not an array');	
	}
	
	// check font file
	if (!isset($conf['font'])) {
		throw new Base_ImagemagickException('$conf["font"] is not defined');	
	} else {
		$conf['file'] = $this->fontFileCheck($conf['font']);
	}
	
	// check pointsize
	if (!isset($conf['pointsize'])) {
		throw new Base_ImagemagickException('$conf["pointsize"] is not defined');
	}
	if (!is_numeric($conf['pointsize'])) {
		throw new Base_ImagemagickException('$conf["pointsize"] is not numeric');	
	}
	
	// check color
	if (!isset($conf['color'])) {
		throw new Base_ImagemagickException('$conf["color"] is not defined');
	}
	if (
			!preg_match("=^#([a-f0-9]){3,16}$=i", $conf['color']) &&
			!preg_match("=^rgb\(([0-9]){1,5},(\W|)([0-9]){1,5},(\W|)([0-9]){1,5}\)$=i", $conf['color']) &&
			!preg_match("=^rgba\(([0-9]){1,5},(\W|)([0-9]){1,5},(\W|)([0-9]){1,5},(\W|)([0-9]){1,5}\)$=i", $conf['color']) &&
			!preg_match("=^([a-z0-9]+)$=i", $conf['color'])
		) {
		
			throw new Base_ImagemagickException('$conf["color"] is not a valid color scheme');
	}
	
	// check position
	if (!isset($conf['position'])) {
		throw new Base_ImagemagickException('$conf["position"] is not defined');
	}
	if (!preg_match("=^(-|\+|)([0-9]+),( |)(-|\+|)([0-9]+)$=", $conf['position'])) {
		throw new Base_ImagemagickException();	
	}
	
	// check text
	if (!isset($conf['text']) && !empty($conf['text'])) {
		throw new Base_ImagemagickException('$conf["text"] is not defined');
	}
}

// end of class
}

/**
 * Get MIME type
 * 
 * Handcoded alternative for mime_content_type, if
 * PHP doesn't provide the function. The required 
 * information will be gathered using the command
 * line:
 * 
 * <code>
 * $ file -bi file.php
 * </code>
 * 
 * For further information see
 * http://de3.php.net/manual/en/function.mime-content-type.php
 * 
 * @param string file name to verify
 * @return string mime type
 */
if (!function_exists('mime_content_type')) {
	function mime_content_type ($file) {
		$f = escapeshellarg($file);
		return trim(exec(sprintf('file -bi %s', $file)));
	}
}

class Base_ImagemagickException extends Exception {}

?>