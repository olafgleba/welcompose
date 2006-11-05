<?php

/**
 * Project: Oak
 * File: installer.inc.php
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

$spi = new Setup_PackageExtractor();
$spi->exportFiles();

class Setup_PackageExtractor {
	
	protected $_dir_index = array();
	
	protected $_install_dir = array();

public function exportFiles ()
{
	$this->getDirectoryIndex();
	$this->_install_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'test';
	$this->spawnDirectoryStructure();
	$this->extractFiles();
}

/**
 * Reads directory index from package file and brings it into its
 * original form and saves it to self::_dir_index.
 * 
 * @throws Setup_PackageExtractorException
 */
protected function getDirectoryIndex ()
{
	$dir_index = null;
	$fingerprint = null;
	$compressed = null;
	$line = 0;
	$read = false;
	
	// open pointer to the installer file (yeah, *this* file)
	$fp = fopen(__FILE__, 'rb');
	
	if ($fp) {
		while (!feof($fp)) {
			// read the file line by line
			$buffer = trim(fgets($fp));
			
			/*
			   ok, on the next lines, the code is in reverse order so that
			   we don't read to much lines. that's because we have to execute
			   our checks before we know if have to read.
			*/
			
			// if we reached the end of the directory index, we can stop here
			// with reading and break the while loop.
			if ($buffer == "-----END DIRECTORY INDEX-----") {
				break;
			}
			
			// let's see if reading is enabled. then we're working on the directory
			// index.
			if ($read) {
				// on line 1, we find the fingerprint of the directory index
				if ($line == 1) {
					$fingerprint = preg_replace("=^fingerprint:\s+([a-z0-9]+)$=i", '$1', $buffer);
					
					// increment line number
					$line++;
					
					// go to the next line
					continue;
					
				// on line 2 find the information whether the directory index
				// is compressed or not
				} elseif ($line == 2) {
					if (preg_replace("=^compressed:\s+(true|false)$=i", '$1', $buffer) == "true") {
						$compressed = true;
					} else {
						$compressed = false;
					}
					
					// increment line number
					$line++;
					
					// go to the next line
					continue;
				
				// all other non-empty lines are our directory index
				} elseif (!empty($buffer)) {
					// remove base64 encoding
					$dir_index .= base64_decode($buffer);
				}
			} 
			
			// after we reached the start marker, we can enable reading
			if ($buffer == "-----BEGIN DIRECTORY INDEX-----") {
				$read = true;
				
				// increment the line number
				$line++;
				
				// go to the next line
				continue;
			}
		}
		fclose($fp);
	}
	
	// if the serialized directory index is compressed, uncompress it
	if ($compressed) {
		$dir_index = gzinflate($dir_index);
	}
	
	// compare saved fingerprint with that one generated from the recovered
	// directory index
	if (md5($dir_index) !== $fingerprint) {
		throw new Setup_PackageExtractorException("Directory index broken, cannot continue with the setup");
	}

	// if everything's fine, unserialize the directory index and finish
	$this->_dir_index = unserialize($dir_index);
}

/**
 * Spawns directory structure as defined by self::_dir_index below the
 * defined install dir (self::_install_dir).
 * 
 * @throws Setup_PackageExtractorException
 */
protected function spawnDirectoryStructure ()
{
	// prepare install directory
	$this->prepareInstallDirectory();
	
	foreach ($this->_dir_index as $_dir) {
		// prepare complete path to the parent of the directory to create
		$path_parts = array(
			$this->_install_dir,
			$_dir['directory']
		);
		$parent_path = join(null, $path_parts);
		
		// if the parent directory does not exist, exit here
		if (!is_dir($parent_path)) {
			throw new Setup_PackageExtractorException("Unable to spawn directory stucture; parent directory does not exist");
		}
		
		// if the parent directory is not writable, exit here
		if (!is_writable($parent_path)) {
			throw new Setup_PackageExtractorException("Unable to spawn directory stucture; parent directory is not writable");
		}
		
		// prepare complete path to the directory to create
		$path_parts = array(
			$this->_install_dir,
			$_dir['directory'],
			DIRECTORY_SEPARATOR,
			$_dir['name']
		);
		$path = join(null, $path_parts);
		
		// if the directory already exists, skip here
		if (is_dir($path)) {
			throw new Setup_PackageExtractorException("Unable to spawn directory stucture; directory already exists");
		} else {
			if (@mkdir($path) === false) {
				throw new Setup_PackageExtractorException("Unable to spawn directory stucture; directory creation failed");
			}
		}
	}
}

/**
 * Extracts files from install package and prepares them for copying
 * on the disk. 
 *
 * @throws Setup_PackageExtractorException
 */
protected function extractFiles ()
{
	// initialize vars
	$file = null;
	$file_name = null;
	$directory = null;
	$fingerprint = null;
	$compressed = null;
	$read = false;
	$line = 0;
	
	// open pointer to the installer file (yeah, *this* file)
	$fp = fopen(__FILE__, 'rb');
	
	if ($fp) {
		while (!feof($fp)) {
			// read the file line by line
			$buffer = trim(fgets($fp));
			
			if ($buffer == "-----END FILE-----") {
				// if the file is compressed, uncompress it
				if ($compressed) {
					$file = gzinflate($file);
				}
				
				// compare saved fingerprint with that one generated from
				// the recovered file
				if (md5($file) !== $fingerprint) {
					throw new Setup_PackageExtractorException("File $file_name is broken, cannot continue with the setup");
				}
				
				// save the extracted file to disk
				$this->saveExtractedFileToDisk($directory, $file_name, $file);
				
				// disable reading
				$read = false;
				
				// go to the next line
				continue;
			}
			
			// let's see if reading is enabled. then we're working on a file.
			if ($read && !empty($buffer)) {
				switch ($line) {
					// on line 1, we find the name of the file
					case 1:
							$file_name = preg_replace("=^file:\s+(.*?)$=i", '$1', $buffer);
						break;
					// on line 2, we find the name of the directory where the file should
					// be saved to
					case 2:
							$directory = preg_replace("=^directory:\s*(.*?)$=i", '$1', $buffer);
						break;
					// line 3 contains the fingerprint
					case 3:
							$fingerprint = preg_replace("=^fingerprint:\s+([a-z0-9]+)$=i", '$1', $buffer);
						break;
					// and line 4 the information whether the file is compressed or not
					case 4:
							if (preg_replace("=^compressed:\s+(true|false)$=i", '$1', $buffer) == "true") {
								$compressed = true;
							} else {
								$compressed = false;
							}
						break;
					// ok, the rest of non empty lines is our file
					default:
							$file .= base64_decode($buffer);
						break;
				}
				
				// increment line number
				$line++;
				
				// go to the next line
				continue;
			}
			
			if ($buffer == "-----BEGIN FILE-----") {
				// reset vars for the file
				$file = null;
				$file_name = null;
				$directory = null;
				$fingerprint = null;
				$compressed = null;
				
				// enable reading
				$read = true;
				
				// set line count to one
				$line = 1;
			}
		}
		fclose($fp);
	}
}

/** 
 * Writes extracted file to disk. Takes the directory where it should be saved to as
 * first argument, its file name as second argument and the file contents as third
 * argument.
 * 
 * @throws Setup_PackageExtractorException
 * @param string
 * @param string
 * @param string
 */
protected function saveExtractedFileToDisk ($directory, $file_name, $file_contents)
{
	// prepare path to directory where the file should be saved to
	$path_parts = array(
		$this->_install_dir,
		$directory
	);
	$directory_path = join(null, $path_parts);
	
	// test if directory is writable
	if (!is_writable($directory_path)) {
		throw new Setup_PackageExtractorException("Directory where file should be saved to is not writable");
	}
	
	// prepare path to file that should be written to disk
	$path_parts = array(
		$this->_install_dir,
		$directory,
		DIRECTORY_SEPARATOR,
		$file_name
	);
	$path = join(null, $path_parts);
	
	// test if file is writable
	if (file_exists($path) && !is_writable($path)) {
		throw new Setup_PackageExtractorException("File that should be written to disk exists and is not writable"); 
	}
	
	// write file to disk
	file_put_contents($path, $file_contents);
}

protected function prepareInstallDirectory ()
{
	// try to create the install dir if it does not exist
	if (!is_dir($this->_install_dir)) {
		if (@mkdir($this->_install_dir) === false) {
			throw new Setup_PackageExtractorException("Failed to create not existing install directory");
		}
	}
}

protected function chmodFiles ()
{
	
}

// end of class
}

class Setup_PackageExtractorException extends Exception { }

// don't remove this exit here. otherwise the whole installer file will
// be piped through php and that's not what we like to see...
exit;

?>