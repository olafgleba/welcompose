<?php

/**
 * Project: Oak
 * File: create_install_package.php
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

$spg = new Setup_PackageGenerator();
$spg->createInstallPackage();

class Setup_PackageGenerator {
	
	/**
	 * Name of the output file to write to.
	 *
	 * @var string
	 */
	protected $_output_file = null;
	
	/**
	 * Whether to compress the serialized structures and
	 * file contents.
	 * 
	 * @var bool
	 */
	protected $_compress = null;
	
	/**
	 * Container for the directory list.
	 *
	 * @var array
	 */
	protected $_dir_list = array();
	
	/**
	 * Container for the file list.
	 * 
	 * @var array
	 */
	protected $_file_list = array();
	
	/**
	 * The path to the software directory that should be
	 * packaged.
	 *
	 * @var string
	 */
	protected $_software_directory = null;
	
	/**
	 * Path to the installer script that should be
	 * included on top of the installer package.
	 *
	 * @var string
	 */
	protected $_installer_script = null;
	
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
	 * Options that will be passed to stream_filter_append
	 * when executing the base64 encoding.
	 *
	 * @var array
	 */
	protected $_base64_options = array(
		'line-length' => 76,
		'line-break-chars' => "\r\n"
	);

/**
 * Creates install package.
 */
public function createInstallPackage ()
{
	// check if the environment meets the requirements of the install
	// package generator
	$this->checkRequirements();
	
	// import command line args
	$this->importArgs();
	
	// test if package file is writable
	if ((!file_exists($this->_output_file) && !is_writable(dirname($this->_output_file))) ||
	(file_exists($this->_output_file) && !is_writable($this->_output_file))) {
		$this->triggerError("Package file not writable.\r\n");
	}
	if (!file_exists($this->_installer_script)) {
		$this->triggerError("Installer code file does not exist.\r\n");
	}
	
	// create the file list
	$this->createContentList($this->_software_directory);
	
	// remove old versions of the package file
	unlink($this->_output_file);

	// write the installer code to package
	$this->writeInstallerCodeToPackage();
	
	// now we need the directory index
	$this->writeDirectoryIndexToPackage();
	
	// and last but not least all the files and we're done
	$this->writeFilesToPackage();
}

/**
 * Checks if the environment meets the requirements of the install package
 * generator.
 */
protected function checkRequirements ()
{
	// make sure the script is used through the command line
	if (substr(php_sapi_name(), 0, 3) != 'cli') {
		$this->triggerError("Please run this script only using a php cli binary.\r\n");
	}
	
	// make sure we're running php 5.0.0 or higher
	if (version_compare('5.0.0', phpversion(), '>')) {
		$this->triggerError("PHP 5.0.0. or higher required; 5.1.0 and higher recommended.\r\n");
	}
	
	// make sure the zlib extension is present. we need it for compression.
	if (!extension_loaded('zlib')) {
		$this->triggerError("Zlib extension not installed.\r\n");
	}
	
	// in php versions 5.0.0 up to 5.1.0 the zlib filter package is required
	// see http://de3.php.net/manual/en/filters.compression.php 
	if (version_compare('5.0.0', phpversion(), '<') && version_compare('5.1.0', phpversion(), '>')) {
		if (!extension_loaded('zlib_filters')) {
			$this->triggerError("The PECL extension zlib_filters is required in PHP 5.0.0 up to 5.1.0.\r\n");
		}
	}
}

/**
 * Imports arguments from command line and executes some sanity checks.
 * If the --help argument is supplied, the method will take care of 
 * displaying the usage instructions.
 */
protected function importArgs ()
{
	// filter our arguments out of the list of command line arguments
	foreach ($_SERVER['argv'] as $_arg) {
		// handle --help argument
		if (preg_match('=^-{2}help=', $_arg, $matches)) {
			$this->_help = true;
		}
		// handle --compress=<true|false> argument
		if (preg_match('=^-{2}compress\=\"?\'?(true|false)\"?\'?$=', $_arg, $matches)) {
			if ($matches[1] == 'true') {
				$this->_compress = true;
			} else {
				$this->_compress = false;
			}
		}
		// handle --installer_script=<file> argument
		if (preg_match('=^-{2}installer_script\=\"?\'?(.*?)\"?\'?$=', $_arg, $matches)) {
			if (!file_exists($matches[1])) {
				$this->triggerError("Installer script does not exist.\r\n");
			} else {
				$this->_installer_script = $matches[1];
			}
		}
		// handle --software_directory=<directory> argument
		if (preg_match('=^-{2}software_directory\=\"?\'?(.*?)\"?\'?$=', $_arg, $matches)) {
			if (!is_dir($matches[1])) {
				$this->triggerError("Software directory is a file or does not exist.\r\n");
			} else {
				$this->_software_directory = $matches[1];
			}
		}
		// handle --output_file=<file> argument
		if (preg_match('=^-{2}output_file\=\"?\'?(.*?)\"?\'?$=', $_arg, $matches)) {
			if (is_dir($matches[1])) {
				$this->triggerError("Output file already exists and is a directory.\r\n");
			} else {
				$this->_output_file = $matches[1];
			}
		}
		
	}
	
	// if $this->_help is true, we have to output the usage instructions
	if ($this->_help) {
		$this->printHelp();
		exit(0);
	}
	
	// check arguments
	if (is_null($this->_compress)) {
		$this->triggerError("Invalid or no value for argument --compress supplied.\r\n");
	}
	if (empty($this->_installer_script)) {
		$this->triggerError("No value for argument --installer_script supplied.\r\n");
	}
	if (empty($this->_software_directory)) {
		$this->triggerError("No value for argument --software_directory supplied.\r\n");
	}
	if (empty($this->_output_file)) {
		$this->triggerError("No value for argument --output_file supplied.\r\n");
	}
}

/**
 * Creates list of contents of a directory and writes the found contents
 * to self::_dir_list and self::_file_list. Takes path to the directory to
 * scan as first argument.
 *  
 * @param string Path
 */
protected function createContentList ($path)
{
	$dir = dir($path);
	while (false !== ($file = $dir->read())) {
		if ($file != '.' && $file != '..' && !preg_match("=^\.=", $file)) {
			// compose full path
			$full_path = $dir->path.DIRECTORY_SEPARATOR.$file;
			
			// put directories in the dir list so that we can create a separate directory
			// index. that's handy when we have to spawn the directory structure during
			// installation
			if (is_dir($full_path)) {
				$this->_dir_list[] = array(
					'name' => $file,
					'directory' => str_replace($this->_software_directory, null, $dir->path)
				);
				
				$this->createContentList($full_path);
			// put files to it's own index with some additional information like the full
			// (system dependent) path to the file so that we don't always have to recompose
			// it.
			} elseif (is_file($full_path)) {
				$this->_file_list[] = array(
					'name' => $file,
					'directory' => str_replace($this->_software_directory, null, $dir->path),
					'full_path' => $full_path
				);
			}
		}
	}
	$dir->close();
}

/**
 * Writes installer code taken from installer_code.inc.php to package.
 */
protected function writeInstallerCodeToPackage ()
{
	// write installer code to package
	file_put_contents($this->_output_file, file_get_contents($this->_installer_script)."\r\n",
		FILE_APPEND);
}

/**
 * Writes directory index to install package. Takes the array structure created
 * by this script in self::createContentList() and serializes it. If self::_compress
 * is true, the serialized array will be compressed using zlib. Afterwards, the
 * serialized array structure will be encoded as base64, chunked into 76 chars
 * long lines and saved to the package file.
 */
protected function writeDirectoryIndexToPackage ()
{
	// serialize the directory list...
	$directory_index = serialize($this->_dir_list);
	
	// write start marker to file
	file_put_contents($this->_output_file, $this->_dir_index_markers['start']."\r\n",
		FILE_APPEND);
	
	// insert fingerprint of directory index
	file_put_contents($this->_output_file, sprintf("Fingerprint: %s\r\n",
		md5($directory_index)), FILE_APPEND);
	
	// write down if the directory index is compressed or not
	file_put_contents($this->_output_file, sprintf("Compressed: %s\r\n\r\n",
		($this->_compress ? "true" : "false")), FILE_APPEND);
	
	// ... and open a pointer to the package file...
	$fp = fopen($this->_output_file, 'ab');
	
	// ... enable zlib stream filter only if self::_compress is true ...
	if ($this->_compress) {
		stream_filter_append($fp, 'zlib.deflate', STREAM_FILTER_WRITE);
	}
	
	// ... enable the base64 encoding filter and write the whole thing
	// to the package file.
	stream_filter_append($fp, 'convert.base64-encode', STREAM_FILTER_WRITE,
		$this->_base64_options);
	fwrite($fp, $directory_index);
	fclose($fp);
	
	// write start marker to file
	file_put_contents($this->_output_file, "\r\n".$this->_dir_index_markers['end']."\r\n",
		FILE_APPEND);
}

/**
 * Writes files to install package. Every file block will be enclosed with a
 * start and an end marker (see self::_file_markers). The file block itself
 * consists of some metadata (file name, directory name, md5 fingerprint) and
 * the file contents, encoded using base64 and compressed using zlib if
 * self::_compress is enabled.
 */
protected function writeFilesToPackage ()
{
	// loop through file list and write all the files including some metadata
	// (name, directory, fingerprint) to the package.
	foreach ($this->_file_list as $_file) {
		// write start marker to file
		file_put_contents($this->_output_file, $this->_file_markers['start']."\r\n",
			FILE_APPEND);
		
		// write metadata to file
		file_put_contents($this->_output_file, sprintf("File: %s\r\n",
			$_file['name']), FILE_APPEND);
		file_put_contents($this->_output_file, sprintf("Directory: %s\r\n",
			$_file['directory']), FILE_APPEND);
		file_put_contents($this->_output_file, sprintf("Fingerprint: %s\r\n",
			md5_file($_file['full_path'])), FILE_APPEND);
		file_put_contents($this->_output_file, sprintf("Compressed: %s\r\n\r\n",
			($this->_compress ? "true" : "false")), FILE_APPEND);
		
		// open file for reading...
		$fp_source = fopen($_file['full_path'], "rb");
		
		// ... create pointer to the end of the package file... 
		$fp_dest = fopen($this->_output_file, 'ab');
		
		// ... enable zlib stream filter only if self::_compress is true ...
		if ($this->_compress) {
			stream_filter_append($fp_dest, 'zlib.deflate', STREAM_FILTER_WRITE);
		}
		
		// ... enable the base64 encoding filter...
		stream_filter_append($fp_dest, 'convert.base64-encode', STREAM_FILTER_WRITE,
			$this->_base64_options);
			
		// and write the whole thing in chunks to the package file.
		if ($fp_source) {
		   while (!feof($fp_source)) {
		       $buffer = fgets($fp_source, 4096);
		       fwrite($fp_dest, $buffer);
		   }
		}

		fclose($fp_source);
		fclose($fp_dest);
		
		// write end marker to file
		file_put_contents($this->_output_file, "\r\n".$this->_file_markers['end']."\r\n",
			FILE_APPEND);
		
		$this->printStderr($_file['full_path']." packaged...\r\n");
	}
}

/**
 * Prints help message and usage instructions to stdout.
 */
protected function printHelp ()
{
	$this->printStderr("Oak Setup Generator 0.1 ($Id$)\r\n");
	$this->printStderr("(c) 2006 sopic GmbH\r\n");
	$this->printStderr("Licensed below the terms of the Open Software License 2.1.\r\n");
	$this->printStderr("\r\n");
	$this->printStderr("Usage: php create_install_package.php \\\r\n");
	$this->printStderr("           --compress=<true|false> \\\r\n");
	$this->printStderr("           --installer_script=<install script file> \\\r\n");
	$this->printStderr("           --software_directory=<software dir to package> \\\r\n");
	$this->printStderr("           --output_file=<file to write the install package to>\r\n");
	$this->printStderr("\r\n");
	$this->printStderr("Arguments:\r\n");
	$this->printStderr("    --compress:\r\n");
	$this->printStderr("        Deflate file contents and serialized structures to create a smaller\r\n");
	$this->printStderr("        install package. Use true to enable and false to disable.\r\n");
	$this->printStderr("        compression.\r\n\r\n");
	$this->printStderr("    --installer_script:\r\n");
	$this->printStderr("        Path to the file that contains the php install script that will\r\n");
	$this->printStderr("        be included at top of the install package.\r\n\r\n");
	$this->printStderr("    --software_directory:\r\n");
	$this->printStderr("        Full path to the root directory of the software to package.\r\n\r\n");
	$this->printStderr("    --output_file:\r\n");
	$this->printStderr("        Path to the file which the install package will be written to. If\r\n");
	$this->printStderr("        the file already exists, it will be overwritten.\r\n\r\n");
}

/**
 * Writes given error message to stderr and terminates the script
 * execution.
 * 
 * @param string Error message 
 */
protected function triggerError ($error_string)
{
	// append instructions to get help to the error string
	$error_string = $error_string."Type 'php create_install_package.php --help' for usage instructions.\r\n";
	
	// write error message to stderr
	$this->printStderr($error_string);
	
	// exit
	exit(1);
}

/**
 * Prints message to stdout
 */
protected function printStdout ($message)
{
	// write message to stdout
	file_put_contents('php://stdout', $message);
}

/**
 * Writes message to stderr.
 */
protected function printStderr ($message)
{
	// write error message to stderr
	file_put_contents('php://stderr', $message);
}

// end of class
}

?>
