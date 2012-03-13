<?php

/**
 * Project: Welcompose
 * File: installer.inc.php
 * 
 * Copyright (c) 2006 sopic GmbH
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

class Setup_PackageExtractor {
	
	/**
	 * Container where the recovered directory index will
	 * be stored 
	 *
	 * @param array
	 */
	protected $_dir_index = array();
	
	/**
	 * Directory where the software should be installed to.
	 *
	 * @param string 
	 */
	protected $_install_dir = array();
	
	/**
	 * Default chmod value that will be applied to files.
	 *
	 * @param int
	 */
	protected $_default_file_mask = 0644;
	
	/**
	 * Default chmod value that will be applied to directories.
	 *
	 * @param int
	 */
	protected $_default_dir_mask = 0755;
	
	/**
	 * Chmod value that will be applied to files that are supposed
	 * to be writable.
	 * 
	 * @param int
	 */
	protected $_writable_file_mask = 0666;
	
	/**
	 * Chmod value that will be applied to directories that are
	 * supposed to be writable.
	 * 
	 * @param int
	 */
	protected $_writable_dir_mask = 0777;
	
	/**
	 * List of files that are supposed to be writable after extraction
	 *
	 * @param array
	 */
	protected $_writable_files = array(
		'/core/conf/sys.inc.php'
	);
	
	/**
	 * List of directories that are supposed to be writable after extraction
	 *
	 * @param array
	 */
	protected $_writable_dirs = array(
		'/admin/smarty/compiled',
		'/smarty/compiled',
		'/smarty/cache',
		'/files/media',
		'/files/global_files',
		'/tmp',
		'/tmp/captchas',
		'/tmp/flickr_cache',
		'/tmp/installer',
		'/tmp/mail_attachments',
		'/tmp/updater',
		'/tmp/log',
		'/tmp/sitemaps'
	);
	
	/**
	 * Strings that mark the start and end of the
	 * directory index in the installer package.
	 * 
	 * @var array
	 */
	protected $_dir_index_markers = array(
		'start' => '-----BEGIN DIRECTORY INDEX-----',
		'end' => '-----END DIRECTORY INDEX-----'
	);
	
	/**
	 * Strings that mark the start and end of a
	 * file in the installer package.
	 * 
	 * @var array
	 */	
	protected $_file_markers = array(
		'start' => '-----BEGIN FILE-----',
		'end' => '-----END FILE-----'
	);

/**
 * Sets default chmod mode for files. Please note that you
 * have to supply a four digit long octal mode value.
 *
 * @throws Setup_PackageExtractorException
 * @param int 
 */
public function setDefaultFileMask ($mode)
{
	// input check
	if (!is_numeric($mode) || strlen($mode) != 4) {
		throw new Setup_PackageExtractorException("Invalid mode supplied");
	}
	
	$this->_default_file_mask = (int)$mode;
}

/**
 * Sets default chmod mode for directories. Please note that you
 * have to supply a four digit long octal mode value.
 *
 * @throws Setup_PackageExtractorException
 * @param int 
 */
public function setDefaultDirMask ($mode)
{
	// input check
	if (!is_numeric($mode) || strlen($mode) != 4) {
		throw new Setup_PackageExtractorException("Invalid mode supplied");
	}
	
	$this->_default_dir_mask = (int)$mode;
}

/**
 * Sets chmod mode for files that are supposed to be writable.
 * Please note that you have to supply a four digit long octal
 * mode value.
 *
 * @throws Setup_PackageExtractorException
 * @param int 
 */
public function setWritableFileMask ($mode)
{
	// input check
	if (!is_numeric($mode) || strlen($mode) != 4) {
		throw new Setup_PackageExtractorException("Invalid mode supplied");
	}
	
	$this->_writable_file_mask = (int)$mode;
}

/**
 * Sets chmod mode for directories that are supposed to be writable.
 * Please note that you have to supply a four digit long octal
 * mode value.
 *
 * @throws Setup_PackageExtractorException
 * @param int 
 */
public function setWritableDirMask ($mode)
{
	// input check
	if (!is_numeric($mode) || strlen($mode) != 4) {
		throw new Setup_PackageExtractorException("Invalid mode supplied");
	}
	
	$this->_writable_dir_mask = (int)$mode;
}

/**
 * Controller function that extracts the install package. It takes care of
 * the directory structur spawning, the file extraction and the required
 * chmod adjustments. Takes the path to the install dir as first argument.
 * 
 * Attention: The path will be evaluated relative to the installer package.
 *
 * @throws Setup_PackageExtractorException
 * @param string Install dir
 */
public function exportPackage ($install_dir)
{
	// set install dir
	if (empty($install_dir) || !is_scalar($install_dir)) {
		throw new Setup_PackageExtractorException("Install dir must be a non empty scalar value");
	}
	$this->_install_dir = $install_dir;
	
	// spawn directory structure
	$this->spawnDirectoryStructure();
	
	// extract the files
	$this->extractFiles();
	
	// execute chmod adjustments
	$this->chmodFiles();
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
			if ($buffer == $this->_dir_index_markers['end']) {
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
			if ($buffer == $this->_dir_index_markers['start']) {
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
	
	// get directory index
	$this->getDirectoryIndex();
	
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
			
			// change rights
			chmod($path, octdec($this->_default_dir_mask));
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
			
			if ($buffer == $this->_file_markers['end']) {
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
			
			if ($buffer == $this->_file_markers['start']) {
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
	
	// change rights
	chmod($path, octdec($this->_default_file_mask));
}

/**
 * Prepares install directory which means that it tries to create the install
 * directory if it does not exist.
 *
 * @throws Setup_PackageExtractorException
 */
protected function prepareInstallDirectory ()
{
	// make sure that the install dir doesn't start and end with a slash
	if (substr($this->_install_dir, 0, 1) == '/') {
		$this->_install_dir = substr($this->_install_dir, 1);
	}
	if (substr($this->_install_dir, -1, 1) == '/') {
		$this->_install_dir = substr($this->_install_dir, 0, -1);
	}
	if (empty($this->_install_dir)) {
		$this->_install_dir = ".";
	}
	
	foreach (explode('/', $this->_install_dir) as $_dir) {
		$path .= $_dir.'/';
		if (!empty($_dir) && !is_dir($path)) {
			if (@mkdir($path) === false) {
				throw new Setup_PackageExtractorException("Failed to create not existing install directory");
			}
			
			// change rights
			chmod($path, octdec($this->_default_dir_mask));
		} 
	}
}

/**
 * Changes rights of dirs and files listed in self::_writable_dirs and
 * self::_writable_files.
 */
protected function chmodFiles ()
{
	// chmod dirs and files
	foreach ($this->_writable_dirs as $_dir) {
		if (@chmod($this->_install_dir.$_dir, octdec($this->_writable_dir_mask)) === false) {
			throw new Setup_PackageExtractorException("Failed to chmod $_dir");
		}
	}
	foreach ($this->_writable_files as $_file) {
		if (@chmod($this->_install_dir.$_file, octdec($this->_writable_file_mask)) === false) {
			throw new Setup_PackageExtractorException("Failed to chmod $_file");
		}
	}
}

// end of class
}

class Setup_PackageExtractorException extends Exception { }

// don't remove this exit here. otherwise the whole installer file will
// be piped through php and that's not what we like to see...
exit;

?>
