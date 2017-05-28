<?php
/*
phpALUGen - A PHP script to generate ActiveLock License and Product Keys
Product Library Class File
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
* The file for product library functions and classes
* 
* @author Andy Schmitz <andy.schmitz@gmail.com>
* @copyright Copyright (C) 2005 Andy Schmitz. Licensed under the GNU Lesser General Public License version 2.1
* @package phpALUGen
*/

/**
* A class for managing a product library
* 
* The base class and functions for manipulating a product library, and
* performing searches, adding products, removing products, etc.
* 
* @package phpALUGen
*/
class phpALUGen_ProductLibrary {
	/**
	* Whether or not the entire product listing has been retrieved from the library. (Internal Use Only)
	* @access private
	* @var boolean
	*/
	var $gotWholeListing;
	
	/**
	* Whether or not the entire product listing has actually been retrieved from the library. (Internal Use Only) (File handler only)
	* @access private
	* @var boolean
	*/
	var $finishedLoading;
	
	/**
	* A cache of the products already requested from the library. (Internal Use Only)
	* @access private
	* @var array
	*/
	var $products;
	
	/**
	* Retrieves a product's information from the library.
	* 
	* Uses name and version to search for a product. If the product has already
	* been loaded, it uses a cached copy if the product's information
	* 
	* @param string $prodName Name of the product to search for
	* @param string $prodVer Version of the product to search for
	* @return phpALUGen_ProductInfo|false The product's information, if found. Otherwise, false.
	*/
	function retrieveProduct($prodName, $prodVer) {
		// If we currently have stored products
		if (is_array($this->products)) {
			// Loop through each currently stored product
			foreach ($this->products as $product) {
				// Check to see if the properties match
				if (($product->Name == $prodName) && ($product->Version == $prodVer)) {
					// This is the correct product, return it
					return $product;
				}
			}
		}
		// No product found. See if we have them all.
		if ($this->gotWholeListing) {
			// We had the whole listing, but there was no product found. Return 0.
			return 0;
		} else {
			// If we don't KNOW that we have them all, call the specialized function to search for the product.
			return $this->searchForProduct($prodName, $prodVer);
		}
	}
	
	/**
	* Retrieves product information for all products in the library.
	* 
	* @return array An array containing phpALUGen_ProductInfo for each product available.
	*/
	function getAllProducts($skipConfig = true) {
		// See if we already got the whole listing (we don't expect it to change over one run of a script).
		if ($this->gotWholeListing) {
			// If we did, just return what we have, but first clear out the admin config.
			$outProducts = array();
			foreach ($this->products as $curProduct) {
				if (!$skipConfig || $curProduct->Name != 'phpalugen_admin_config') {
					$outProducts[] = $curProduct;
				}
			}
			return $outProducts;
		} else {
			// If we didn't, call the specialized function to get it.
			$allProducts = $this->searchForAllProducts();
			// Remove any that have the settings for the admin section
			if ($skipConfig) {
				foreach ($allProducts as $id => $curProduct) {
					if ($curProduct->Name == 'phpalugen_admin_config') {
						unset($allProducts[$id]);
					}
				}
			}
			// Note that we did get all the products available
			$this->gotWholeListing = true;
			// And return the list of products
			return $allProducts;
		}
	}
	
	/**
	* Adds a product to the library.
	* 
	* @param phpALUGen_ProductInfo The information of the product to add
	* @return boolean Success
	*/
	function addProduct($newProduct) {
		// Make sure the product is not available
		if ($this->retrieveProduct($newProduct->Name, $newProduct->Version) != 0) {
			// Silently return 0.
			return 0;
		}
		
		// Add the product
		$this->products[] = $newProduct;
		
		// Call the specialized function to add the product permanently
		return $this->specialAddProduct($newProduct);
	}
	
	/**
	* Removes a product's information from the library.
	* 
	* Uses name and version to search for a product. If the product exists, it
	* removes the product from the cache and library permanently.
	* 
	* @param string $prodName Name of the product to search for
	* @param string $prodVer Version of the product to search for
	* @return boolean Success
	*/
	function removeProduct($prodName, $prodVer) {
		// If we currently have stored products
		if (is_array($this->products)) {
			// Loop through each currently stored product
			foreach ($this->products as $prodId => $product) {
				// Check to see if the properties match
				if (($product->Name == $prodName) && ($product->Version == $prodVer)) {
					// This is the correct product, delete it
					unset($this->products[$prodId]);
				}
			}
		}
		
		// Call the specialized function to delete the product
		return $this->specialRemoveProduct($prodName, $prodVer);
	}
	
	/**
	* Returns a suitable products.ini file from the products in the library.
	* 
	* @return string A products.ini-formatted string
	*/
	function getProductsIni($skipConfig = true) {
		// Get all the products
		$allProducts = $this->getAllProducts($skipConfig);
		
		// Initialize the Output String
		$outStr = '';
		
		// Write out a products.ini-format file
		foreach ($allProducts as $thisProduct) {
			$outStr .= '['.$thisProduct->Name.' '.$thisProduct->Version."]\n";
			$outStr .= 'Name='.$thisProduct->Name."\n";
			$outStr .= 'Version='.$thisProduct->Version."\n";
			$outStr .= 'VCode='.$thisProduct->VCode."\n";
			$outStr .= 'GCode='.$thisProduct->GCode."\n\n";
		}
		
		// Return the products.ini data
		return $outStr;
	}
	
	/**
	* Loads products from a products.ini-formatted string.
	* 
	* @param string $data The products.ini data
	* @param boolean $remove Whether or not to remove all existing products first (true=remove all products)
	* @param boolean $overwrite Whether or not to overwrite an existing product with the same name and version (true=overwrite)
	*/
	function loadProductsIni($data, $remove, $overwrite) {
		$commentchar = ';';
		$data = str_replace("\r", "\n", $data);
		$array1 = explode("\n", $data);
		$section = '';
		foreach ($array1 as $filedata) {
			$dataline = trim($filedata);
			$firstchar = substr($dataline, 0, 1);
			if ($firstchar!=$commentchar && $dataline!='') {
				//It's an entry (not a comment and not a blank line)
				if ($firstchar == '[' && substr($dataline, -1, 1) == ']') {
					//It's a section
					$section = strtolower(substr($dataline, 1, -1));
				}else{
					//It's a key...
					$delimiter = strpos($dataline, '=');
					if ($delimiter > 0) {
						//...with a value
						$key = strtolower(trim(substr($dataline, 0, $delimiter)));
						$value = trim(substr($dataline, $delimiter + 1));
						if (substr($value, 1, 1) == '"' && substr($value, -1, 1) == '"') { $value = substr($value, 1, -1); }
						$array2[$section][$key] = stripcslashes($value);
					}else{
						//...without a value (ignore)
					}
				}
			}else{
				//It's a comment or blank line.  Ignore.
			}
		}
		
		// If we are supposed to remove all existing products
		if ($remove) {
			$curProducts = $this->getAllProducts();
			foreach ($curProducts as $thisProduct) {
				$this->removeProduct($thisProduct->Name, $thisProduct->Version);
			}
		}
		
		// Loop through the products and add them
		foreach ($array2 as $thisProduct) {
			// Start assembling the product
			$newProduct = new phpALUGen_ProductInfo;
			// Each new key overwrites an old one, if it has the same name
			foreach ($thisProduct as $key => $value) {
				switch (strtoupper($key)) {
					case 'NAME':
						$newProduct->Name = $value;
						break;
					case 'VERSION':
						$newProduct->Version = $value;
						break;
					case 'GCODE':
						$newProduct->GCode = $value;
						break;
					case 'VCODE':
						$newProduct->VCode = $value;
						break;
				}
			}
			
			// Check to see if the product exists
			if ($this->retrieveProduct($newProduct->Name, $newProduct->Version) != 0) {
				// It does exist
				if ($overwrite) {
					// We can overwrite it
					$this->removeProduct($newProduct->Name, $newProduct->Version);
					$this->addProduct($newProduct);
				}
			} else {
				$this->addProduct($newProduct);
			}
		}
	}
}

/**
* A MySQL interface to the Product Library
* 
* @package phpALUGen
*/
class phpALUGen_ProductLibrary_mysql extends phpALUGen_ProductLibrary {
	/**
	* The resource of the link to the database
	* @access private
	* @var resource
	*/
	var $dbLink;
	
	/**
	* The table the library is in
	* @access private
	* @var string
	*/
	var $dbTable;
	
	/**
	* Constructor Function.
	* 
	* Connects to and selects the database, and keeps the connection available
	* 
	* @param string $db_server Database Server
	* @param string $db_user Database Username
	* @param string $db_pass Database Password
	* @param string $db_name Database Name (the name of the database)
	* @param string $db_table Table to be used inside the database
	*/
	//function __phpALUGen_ProductLibrary_mysql($db_server, $db_user, $db_pass, $db_name, $db_table) {
		function __construct($db_server, $db_user, $db_pass, $db_name, $db_table) {
		// Constructor for the database version
		// Try to connect to the database
		$db_link = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
		if (!$db_link) {
			// We coulnd't connect to the server.
			// Tell the user
			echo 'Could not connect to database server, error:<br>'.mysqli_error($db_link);
			// and return 0.
			return 0;
		}
		
		// Try to select the correct database
		if (!mysqli_select_db($db_link, $db_name)) {
			// We couldn't load the database.
			// Tell the user
			echo 'Could not load database, error:<br>'.mysqli_error($db_link);
			// and return 0.
			return 0;
		}
		//var_dump($db_link);
		
		$this->dbLink = $db_link;
		$this->dbTable = $db_table;
		//var_dump($this->dbLink);
	}
	
	/**
	* Specialized product-searching routine
	* 
	* Searches the database for the product, and assembles a phpALUGen_ProductInfo
	* class if it is found
	* 
	* @access private
	* @param string $prodName The product name to search for
	* @param string $prodVer The version of the product to search for
	* @return phpALUGen_ProductInfo|false The product's information, if found. Otherwise, false.
	*/
	function searchForProduct($prodName, $prodVer) {
		$sql = 'SELECT * FROM '.mysqli_real_escape_string($this->dbLink, $this->dbTable).' WHERE name = \''.mysqli_real_escape_string($this->dbLink, $prodName).'\' AND version = \''.mysqli_real_escape_string($this->dbLink, $prodVer).'\'';
		$result = mysqli_query($this->dbLink, $sql);
		
		$product = mysqli_fetch_assoc($result);
		if ($product == 0) {
			// We didn't find anything, return 0.
			return 0;
		}
		
		// Otherwise, make a new product and add this info.
		$outProduct = new phpALUGen_ProductInfo;
		$outProduct->Name = $product['name'];
		$outProduct->Version = $product['version'];
		$outProduct->GCode = $product['gcode'];
		$outProduct->VCode = $product['vcode'];
		
		// Add this product to the cache
		$this->products[] = $outProduct;
		
		// Return the product
		return $outProduct;
	}
	
	/**
	* Specialized product-searching routine
	* 
	* Searches the database for all products, and assembles an array of
	* phpALUGen_ProductInfo classes
	* 
	* @access private
	* @return array The array of all products found (may be empty)
	*/
	function searchForAllProducts() {
		$sql = 'SELECT * FROM '.mysqli_real_escape_string($this->dbLink, $this->dbTable);
		$result = mysqli_query( $this->dbLink, $sql);
		
		// Initialize the products array
		$outProducts = array();
		
		// Loop through all the products
		while ($product = mysqli_fetch_assoc($result)) {
			// Otherwise, make a new product and add this info.
			$newProduct = new phpALUGen_ProductInfo;
			$newProduct->Name = $product['name'];
			$newProduct->Version = $product['version'];
			$newProduct->GCode = $product['gcode'];
			$newProduct->VCode = $product['vcode'];
			
			// Add this product to the full listing
			$outProducts[] = $newProduct;
		}
		
		// Return the products
		return $outProducts;
	}
	
	/**
	* Specialized product-adding routine
	* 
	* @access private
	* @param phpALUGen_ProductInfo $newProduct Product info to add
	* @return boolean Success
	*/
	function specialAddProduct($newProduct) {
		$sql = 'INSERT INTO `products` (`name`, `version`, `gcode`, `vcode`) VALUES (\''.mysql_escape_string($newProduct->Name).'\', \''.mysql_escape_string($newProduct->Version).'\', \''.mysql_escape_string($newProduct->GCode).'\', \''.mysql_escape_string($newProduct->VCode).'\')';
		$result = mysqli_query($sql, $this->dbLink);
		if (!$result) {
			// Something went wrong. Return 0.
			return 0;
		}
		// It looks like we succeeded. Return 1.
		return 1;
	}
	
	/**
	* Specialized product-removing routine
	* 
	* @access private
	* @param string $prodName Name of product to remove
	* @param string $prodVer Version of product to remove
	* @return boolean Success
	*/
	function specialRemoveProduct($prodName, $prodVer) {
		$sql = 'DELETE FROM `products` WHERE name = \''.mysql_escape_string($prodName).'\' AND version = \''.mysql_escape_string($prodVer).'\'';
		$result = mysqli_query($sql, $this->dbLink);
		echo mysql_error();
		if (!result) {
			// Something went wrong. Return 0.
			return 0;
		}
		// It looks like we succeeded. Return 1.
		return 1;
	}
}

/**
* A file-based interface to the Product Library
* 
* @package phpALUGen
*/
class phpALUGen_ProductLibrary_file extends phpALUGen_ProductLibrary {
	/**
	* The filename of the library file
	* @access private
	* @var string
	*/
	var $fileName;
	
	/**
	* Constructor Function.
	* 
	* Loads the .ini file and parses it
	* 
	* @param string $file_name The name of the file (in products.ini format)
	*/
	//function phpALUGen_ProductLibrary_file($file_name) {
		function __construct($file_name) {
		// Constructor for the file version
		// Say we loaded the whole file (to pass through checks on loaded files)
		$this->gotWholeListing = true;
		// Load the file
		$this->loadProductsIni(implode("\n", file($file_name)), $remove, $overwrite);
		// Save the file location
		$this->fileName = $file_name;
		// Note that we are done loading
		$this->finishedLoading = true;
	}
	
	/**
	* Specialized product-searching routine
	* 
	* Searches the database for the product, and assembles a phpALUGen_ProductInfo
	* class if it is found
	* 
	* @access private
	* @param string $prodName The product name to search for
	* @param string $prodVer The version of the product to search for
	* @return phpALUGen_ProductInfo|false The product's information, if found. Otherwise, false.
	*/
	function searchForProduct($prodName, $prodVer) {
		// If it wasn't found from the loaded .ini file, it doesn't exist
		return 0;
	}
	
	/**
	* Specialized product-searching routine
	* 
	* Searches the database for all products, and assembles an array of
	* phpALUGen_ProductInfo classes
	* 
	* @access private
	* @return array The array of all products found (may be empty)
	*/
	function searchForAllProducts() {
		// BEAR DRIVING TRUCK! (This should never happen)
		// But just in case, return an empty array (no products)
		return Array();
	}
	
	/**
	* Specialized product-adding routine
	* 
	* @access private
	* @param phpALUGen_ProductInfo $newProduct Product info to add
	* @return boolean Success
	*/
	function specialAddProduct($newProduct) {
		// The product was already added to the current product array.
		// This means we can just write out the .ini file the generic ProductLibrary creates
		// But first make sure we aren't still doing the first import.
		if ($this->finishedLoading) {
			$fp = fopen($this->fileName, 'w');
			fwrite($fp, $this->getProductsIni(false));
			fclose($fp);
			return 1;
		}
	}
	
	/**
	* Specialized product-removing routine
	* 
	* @access private
	* @param string $prodName Name of product to remove
	* @param string $prodVer Version of product to remove
	* @return boolean Success
	*/
	function specialRemoveProduct($prodName, $prodVer) {
		// All we have to do is write this out, the add product routine does this already
		//  so we call that.
		$this->specialAddProduct('');
		return 1;
	}
}
?>
