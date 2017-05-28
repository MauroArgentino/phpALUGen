<?php
/*
phpALUGen - A PHP script to generate ActiveLock License and Product Keys
Miscellaneous Classes File
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
* The file for miscellaneous classes and definitions.
* 
* @author Andy Schmitz <andy.schmitz@gmail.com>
* @copyright Copyright (C) 2005 Andy Schmitz. Licensed under the GNU Lesser General Public License version 2.1
* @package phpALUGen
*/

/**
* A class for handling license information.
* 
* @package phpALUGen
*/
class phpALUGen_License {	
	/**
	* The name of the product the license is for
	* @var string
	*/
	var $ProductName;
	
	/**
	* The version of the product the license is for
	* @var string
	*/
	var $ProductVer;
	
	/**
	* The user the license is for
	* @var string
	*/
	var $Licensee;
	
	/**
	* The VCode of the product (Public Key)
	* @var string
	*/
	var $ProductKey;
	
	/**
	* The license key (RSA signature)
	* @var string
	*/
	var $LicenseKey;
	
	/**
	* The registration level of the license (0 on up)
	* @var integer
	*/
	var $RegisteredLevel;
	
	/**
	* The date the license was generated (YYYY/M/D)
	* @var string
	*/
	var $RegisteredDate;
	
	/**
	* The expiration date of the license (YYYY/M/D)
	* @var string
	*/
	var $Expiration;
	
	/**
	* The class of the license (Single user or Multiple)
	* @var string
	*/
	var $LicenseClass;
	
	/**
	* The type of the license (Periodic (1), Permanent (2), or Time Locked (3))
	* @var integer
	*/
	var $LicenseType;
	
	/**
	* The maximum number of times the product can be used
	* @var integer
	*/
	var $MaxCount;
	
	/**
	* Generates a copy of the license's information in a string.
	* 
	* @return string The license's information
	*/
	function toString() {
		// An almost exact copy of ToString from the Visual Basic ActiveLock License class
		$crLf = "\r\n";
		$toString = $this->ProductName . $crLf;
		$toString .= $this->ProductVer . $crLf;
		$toString .= $this->LicenseClass . $crLf;
		$toString .= $this->LicenseType . $crLf;
		$toString .= $this->Licensee . $crLf;
		$toString .= $this->RegisteredLevel . $crLf;
		$toString .= $this->RegisteredDate . $crLf;
		$toString .= $this->Expiration . $crLf;
		$toString .= $this->MaxCount;
		return $toString;
	}
	
	/**
	* Generates a copy of this license to use for a liberation key.
	* 
	* This requires LicenseKey to be set.
	* 
	* @return string A string suitable for a liberation key
	*/
	function save() {
		// An almost exact copy of Save from the Visual Basic ActiveLock License Class
		$strOut = $this->toString() . "\r\n" . $this->LicenseKey; //add License Key at the end
		$strOut = str_replace('=', '', base64_encode($strOut));
		return $strOut;
	}
}

/**
* A class for storing product information.
* 
* @package phpALUGen
*/
class phpALUGen_ProductInfo {
	/**
	* The name of the product
	* @var string
	*/
	var $Name;
	
	/**
	* The version of the product
	* @var string
	*/
	var $Version;
	
	/**
	* The GCode for the product (Private Key)
	* @var string
	*/
	var $GCode;
	
	/**
	* The VCode for the product (Public Key)
	* @var string
	*/
	var $VCode;
}
?>