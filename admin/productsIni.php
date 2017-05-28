<?php
$curCwd = getcwd();
chdir('..');
require_once 'phpALUGen.php';
chdir($curCwd);

// Get the products.ini file data
$productsIni = $phpALUGen_ProductLibrary->getProductsIni();

if (isset($download)) {
	// If a download was requested, send it as a file
	header('Content-type: text/plain');
	
	// It will be called products.ini
	header('Content-Disposition: attachment; filename="products.ini"');
	echo $productsIni;
	
	die();
}
include 'header.php';
?><b>Export products.ini file:</b><br />
<textarea rows="10" cols="50">
<?=$productsIni?>
</textarea><br />
<br />
You can also <a href="productsIni.php?download=1">download this as a products.ini file</a><br />
<br />
<b><a href="prodKeys.php">Go back to Product Code Generator</a></b>
<?php
include 'footer.php';
?>
