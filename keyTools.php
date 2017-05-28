<?php
/*
phpALUGen - A PHP script to generate ActiveLock License and Product Keys
Key Tools File
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
* The file for key generation and manipulation functions and classes.
* 
* @author Andy Schmitz <andy.schmitz@gmail.com>
* @copyright Copyright (C) 2005 Andy Schmitz. Licensed under the GNU Lesser General Public License version 2.1
* @package phpALUGen
*/

/**
* The main phpALUGen class.
* 
* @package phpALUGen
*/
class phpALUGen {
	
	/**
	* A function to generate liberation keys.
	* 
	* Takes an input license and install code and returns a liberation key.
	* This is probably the slowest function in the entire library. Avoid calling
	* it unnecessarily.
	* 
	* @param phpALUGen_License $lic The license to generate the liberation key for. Must have ProductName and ProductVer set.
	* @param string $installCode The install code to generate the liberation key for. Should be provided by the user.
	* @return string The liberation key generated from the license and install code.
	*/
	function genKey(&$lic, $installCode) {
		global $phpALUGen_ProductLibrary;
		
		// Decode the Install Code
		$strReq = base64_decode($installCode);
		
		// Get the lock and user information
		$this->getLockAndUserFromInstallCode($strReq, $strLock, $strUser);
		
		// Set the licensee to the user
		$lic->Licensee = $strUser;
		
		// Set the registered date to the license's registered date (doesn't seem necessary)
		//$strRegDate = $lic->RegisteredDate;
		// Set the encrypted information to the registered date (doesn't seem necessary)
		//$strEncrypted = $strRegDate;
		
		// Search for the product in our registry
		$prodInfo = $phpALUGen_ProductLibrary->retrieveProduct($lic->ProductName, $lic->ProductVer);
		
		if (!$prodInfo) {
			// Couldn't find the product. Return 0.
			return 0;
		}
		
		// Set the product key to the public key
		$lic->ProductKey = $prodInfo->VCode;
		
		// Get the RSA keys from the VCode and GCode ($privMod, $privExp, and $pubExp are passed by reference)
		$this->getRSAKeys($prodInfo->VCode, $prodInfo->GCode, $privMod, $privExp, $pubExp);
		
		// Set the license we need to sign
		$strLic = $lic->toString()."\n".$strLock;
		
		// Sign the license
		$strSig = $this->RSASign($strLic, $privMod, $privExp);
		
		// Add the signature to the license
		$strLicKey = str_replace('=', '', base64_encode($strSig));
		$lic->LicenseKey = $strLicKey;
		
		// Get the license formatted nicely
		$strLibKey = $lic->save();
		
		// Add the aLck and Installation Code to the end
		$strLibKey = $strLibKey . 'aLck' . $installCode;
		
		// Return the liberation key (calling function should wrap this with chunk_split() or wordwrap())
		return $strLibKey;
	}
	
	/**
	* Gets the lockcode and user from and installation code.
	* 
	* @access private
	* @param string $strReq The installation code
	* @param string $strLock The Lockcode (pass by reference, will be set by this function)
	* @param string $strUser The user (pass by reference, will be set by this function)
	*/
	function getLockAndUserFromInstallCode($strReq, &$strLock, &$strUser) {
		// Break up by newlines
		$parts = explode("\n", $strReq);
		
		// User is the last item after linefeed
		$strUser = array_pop($parts);
		
		// Lockcode is the rest of it
		$strLock = join("\n", $parts);
	}
	
	/**
	* Gets the RSA keys for signing from a VCode and GCode.
	* 
	* The modulus and exponents will be set by the function, and should be
	* passed by reference.
	* 
	* @access private
	* @param string $VCode The VCode to be decoded (contains public key)
	* @param string $GCode The GCode to be decoded (contains private key)
	* @param mixed $privMod The modulus of the keys
	* @param mixed $privExp The exponent of the private key
	* @param mixed $pubExp The exponent of the public key
	*/
	function getRSAKeys($VCode, $GCode, &$privMod, &$privExp, &$pubExp) {
		// Disassemble VCode (Public Key)
		$VCode = base64_decode($VCode);
		$pos = 0;
		//  First section of VCode - Magic Token
		$len = $this->get32Bit(substr($VCode, $pos, 4)); $pos += 4;
		$VCodeHeader = substr($VCode, $pos, $len); $pos += $len;
		if ($VCodeHeader != 'ssh-rsa') {
			// Something is wrong. Return 0
			return 0;
		}
		//  Second section of VCode - Public Key Exponent
		$len = $this->get32Bit(substr($VCode, $pos, 4)); $pos += 4;
		$pubExp = substr($VCode, $pos, $len); $pos += $len;
		$pubExp = strrev($pubExp);
		//  Third section of VCode - Key Modulus (Public AND Private)
		$len = $this->get32Bit(substr($VCode, $pos, 4)); $pos += 4;
		$keyMod = substr($VCode, $pos, $len); $pos += $len;
		$keyMod = strrev($keyMod);
		
		// Disassemble GCode (Private Key)
		$GCode = base64_decode($GCode);
		$pos = 0;
		//  First section of GCode - Private Key Exponent
		$len = $this->get32Bit(substr($GCode, $pos, 4)); $pos += 4;
		$privExp = substr($GCode, $pos, $len); $pos += $len;
		$privExp = strrev($privExp);
		//  Ignore the rest of GCode (we don't need it)
		
		// Load a math wrapper (defined by PEAR RSA classes)
		$math_wrapper = &Crypt_RSA_MathLoader::loadWrapper();
		
		// Make the keys (constructor order is Modulus, Exponent, Type)
		$privMod = $math_wrapper->bin2int($keyMod);
		$privExp = $math_wrapper->bin2int($privExp);
		$pubExp = $math_wrapper->bin2int($pubExp);
	}
	
	/**
	* Creates an RSA signature of the data.
	* 
	* @access private
	* @param string $data The data to be signed
	* @param mixed $privMod The modulus of the key used for signing
	* @param mixed $privExp The exponent of the key used for signing
	* @return string The signature
	*/
	function RSASign($data, $privMod, $privExp) {
		
		// Load a math wrapper
		$math_wrapper = &Crypt_RSA_MathLoader::loadWrapper();
		
		// The sha_simple hash from ALCrypto is a sha1 hash in binary (not hex)
		$hash = pack('H*', sha1($data));
		// The number of bytes is the length of the private key Modulus minus 1
		$nbytes = strlen($math_wrapper->int2bin($privMod))-1;
		// This is just some stuff, retained from ALCrypto Lib (perhaps a magic number sequence)
		$asn1_weird_stuff = array(chr(0x00), chr(0x30), chr(0x21), chr(0x30), chr(0x09), chr(0x06), chr(0x05), chr(0x2B), chr(0x0E), chr(0x03), chr(0x02), chr(0x1A), chr(0x05), chr(0x00), chr(0x04), chr(0x14));
		$ASN1_LEN = count($asn1_weird_stuff);
		
		// Assemble the bytes string
		//  It starts as a single byte 0x01
		$bytes = chr(1);
		//  Most of the bytes are 0xFF
		for ($i = 1; $i < $nbytes - 20 - $ASN1_LEN; $i++) { $bytes .= chr(0xFF); }
		//  Add in the $asn1_weird_stuff
		for ($i = $nbytes - 20 - $ASN1_LEN, $j = 0; $i < $nbytes - 20; $i++, $j++) { $bytes .= $asn1_weird_stuff[$j]; }
		//  End with the hash
		for ($i = $nbytes - 20, $j = 0; $i < $nbytes; $i++, $j++) { $bytes .= $hash[$j]; }
		// Turn the bytes into a format usable for the encryption
		$bytes = $math_wrapper->bin2int(strrev($bytes));
		
		// Do the actual encryption of the signature
		$out = $math_wrapper->powmod($bytes, $privExp, $privMod);
		// Manipulate the signature to be the way it is needed for the formatted signature
		$out = strrev($math_wrapper->int2bin($out));
		
		// Assemble the output formatted signature
		//  Length of the magic number
		$outstr = $this->put32Bit(7,$math_wrapper);
		//  Add the magic number
		$outstr .= 'ssh-rsa';
		//  Length of the signature
		$outstr .= $this->put32Bit($nbytes+1,$math_wrapper);
		//  Add the signature
		$outstr .= $out;
		
		// Return the formatted signature
		return $outstr;
	}
	
	/**
	* Generates new product keys (to be used in a new product) of a given number of bits.
	* 
	* @param integer $bits The key length (in bits)
	* @param string $VCode The public key data (pass by reference, will be set by this function)
	* @param string $GCode The private key data (pass by reference, will be set by this function)
	*/
	function genProductKeys($bits, &$VCode, &$GCode) {
		// Load a math wrapper
		$math_wrapper = &Crypt_RSA_MathLoader::loadWrapper();
		
		// Generate the key pair (Taken from RSA PEAR Module, modified for a special public exponent)
		$prng = create_function('', '$a=explode(" ",microtime());return(int)($a[0]*1000000);');
		$p_len = (int) ($bits / 2) + 1;
		$q_len = $bits - $p_len;
		$p = $math_wrapper->getRand($p_len, $prng, true);
		$p = $math_wrapper->nextPrime($p);
		do {
			do {
				$q = $math_wrapper->getRand($q_len, $prng, true);
				$tmp_len = $math_wrapper->bitLen($math_wrapper->mul($p, $q));
				if ($tmp_len < $bits) $q_len++;
				elseif ($tmp_len > $bits) $q_len--;
			} while ($tmp_len != $bits);
			$q = $math_wrapper->nextPrime($q);
			$tmp = $math_wrapper->mul($p, $q);
		} while ($math_wrapper->bitLen($tmp) != $bits);
		// $n - is shared modulus
		$n = $math_wrapper->mul($p, $q);
		// generate public ($e) and private ($d) keys
		$pq = $math_wrapper->mul($math_wrapper->dec($p), $math_wrapper->dec($q));
		// The ActiveLock Keys have a standard public exponent, the prime number 37.
		$e = 37;
		$d = $math_wrapper->invmod($e, $pq);
	
		$keyMod = $math_wrapper->int2bin($n);
		$pubExp = $math_wrapper->int2bin($e);
		$privExp = $math_wrapper->int2bin($d);
		$p = $math_wrapper->int2bin($p);
		$q = $math_wrapper->int2bin($q);
		$iqmp = $math_wrapper->int2bin($math_wrapper->invmod($q, $p));
		
		// Generate the VCode
		$VCode = $this->put32Bit(7, $math_wrapper);
		$VCode .= 'ssh-rsa';
		$VCode .= $this->put32Bit(strlen($pubExp), $math_wrapper);
		$VCode .= strrev($pubExp);
		$VCode .= $this->put32Bit(strlen($keyMod), $math_wrapper);
		$VCode .= strrev($keyMod);
		$VCode = base64_encode($VCode);
		
		// Generate the GCode
		$GCode = $this->put32Bit(strlen($privExp), $math_wrapper);
		$GCode .= strrev($privExp);
		//  These aren't necessary, but we'll include them for completeness
		$GCode .= $this->put32Bit(strlen($p), $math_wrapper);
		$GCode .= strrev($p);
		$GCode .= $this->put32Bit(strlen($q), $math_wrapper);
		$GCode .= strrev($q);
		$GCode .= $this->put32Bit(strlen($iqmp), $math_wrapper);
		$GCode .= strrev($iqmp);
		$GCode = base64_encode($GCode);
	}
	
	/**
	* A function to get a 32-bit number from 4 characters.
	* 
	* @access private
	* @param string $num Input characters
	* @return integer Output 32-bit number 
	*/
	function get32Bit($num) {
		// A translation of the function from the ALCrypto lib
		//  Returns a number from a 32 Bit string
		$numParts[0] = substr($num,0,1);
		$numParts[1] = substr($num,1,1);
		$numParts[2] = substr($num,2,1);
		$numParts[3] = substr($num,3,1);
		$outNum = ((ord($numParts[0]) << 24) | (ord($numParts[1]) << 16) | (ord($numParts[2]) << 8) | (ord($numParts[3])));
		return $outNum;
	}
	
	/**
	* A function to create a 4 character string from a 32-bit number.
	* 
	* @access private
	* @param integer $num The number to be converted
	* @param mixed $math_wrapper A Crypt/RSA Math Wrapper
	* @return string Output 4 character string
	*/
	function put32Bit($num, $math_wrapper) {
		// A rewrite of the function from the ALCrypto lib
		//  Returns a 4 character long string from a 32 bit (or less) number
		$outStr = $math_wrapper->int2bin($num);
		while (strlen($outStr) < 4) {
			$outStr = chr(0).$outStr;
		}
		return $outStr;
	}
}
?>