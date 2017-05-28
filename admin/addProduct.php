<?php
include 'header.php';

// Create the product
$newProduct = new phpALUGen_ProductInfo;

// Generate encryption keys for the product
$generator = new phpALUGen;
$generator->genProductKeys(1024, $VCode, $GCode);
$newProduct->VCode = $VCode;
$newProduct->GCode = $GCode;

// Set the product's name and version
$newProduct->Name = $prodName;
$newProduct->Version = $prodVer;

// Add the product
$phpALUGen_ProductLibrary->addProduct($newProduct);
?><h1>Product Has Been Created</h1>
<b><a href="prodKeys.php">Go back to Product Code Generator</a></b>
<?
include 'footer.php';
?>
