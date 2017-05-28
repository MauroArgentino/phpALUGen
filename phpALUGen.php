<?php
/*
phpALUGen - A PHP script to generate ActiveLock License and Product Keys
Main File
Copyright (C) 2005 Andy Schmitz

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation, version 2.1.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

A human-readable summary of the LGPL is available at the following address:
http://creativecommons.org/licenses/LGPL/2.1/
*/

/**
* The main include file for the phpALUGen library.
* 
* Include this file, and none of the others in your program files
* This file will create a product library object in $phpALUGen_ProductLibrary
* for ease of use and minimum load on databases and disks. Please use this
* object instead of creating your own.
* 
* @author Andy Schmitz <andy.schmitz@gmail.com>
* @copyright Copyright (C) 2005 Andy Schmitz. Licensed under the GNU Lesser General Public License version 2.1
* @package phpALUGen
*/

// Change this to where the configuration file is, if you have moved it.
$configfile = 'config.inc.php';

/*
--------------------------------------------------------------------------------
         DO NOT EDIT BELOW THIS LINE UNLESS YOU KNOW WHAT YOU ARE DOING
--------------------------------------------------------------------------------
*/

//----------------------------------------------------------------------------//
// Startup Procedures                                                         //
//----------------------------------------------------------------------------//

// Load the configuration file
if (is_file($configfile)) {
	include $configfile;
} else {
	die("Please run install.php first! (If you have, please make sure you have a config file. We could not find one.)");
}

// Auto-detect the directory
if ($phpALUGen_dir == '') {
	$phpALUGen_dir = dirname(__FILE__);
}

// Load our include files
require_once $phpALUGen_dir.'/productLibrary.php';
require_once $phpALUGen_dir.'/keyTools.php';
require_once $phpALUGen_dir.'/miscClasses.php';

// Include the PEAR RSA Library (we use the bignum libraries and key generation routines)
require_once 'Crypt/RSA.php';

// Create a ProductLibrary object
switch ($phpALUGen_config['product_handler']) {
	case 'mysql':
		$phpALUGen_ProductLibrary = new phpALUGen_ProductLibrary_mysql($phpALUGen_config['ph_mysql_dbserver'], $phpALUGen_config['ph_mysql_dbuser'], $phpALUGen_config['ph_mysql_dbpass'], $phpALUGen_config['ph_mysql_dbname'], $phpALUGen_config['ph_mysql_dbtable']);
		break;
	case 'file':
		$phpALUGen_ProductLibrary = new phpALUGen_ProductLibrary_file($phpALUGen_config['ph_file_filename']);
		break;
	default:
		die('The Product Library Handler you have selected is not available. Please check to see that you spelled it correctly.');
}
?>