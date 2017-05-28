<?
include 'header.php';

if (isset($_FILES['userfile']['tmp_name'])) {
	// There was an upload, process it
	$prodIni = implode("\n", file($_FILES['userfile']['tmp_name']));
	$phpALUGen_ProductLibrary->loadProductsIni($prodIni, $remove, $overwrite);
}

// Retrieve the product
$thisProd = $phpALUGen_ProductLibrary->retrieveProduct($name, $ver);
?><b>Import products.ini file:</b><br />
<br />
<form enctype="multipart/form-data" method="post" action="importIni.php">
Upload your products.ini file: <input name="userfile" type="file" /><br />
Remove all existing products: <input type="checkbox" name="remove" /><br />
Overwrite existing products (with identical names): <input type="checkbox" name="overwrite" /><br />
<input type="submit" value="Upload file" /><br />
<br />
<b><a href="prodKeys.php">Go back to Product Code Generator</a></b>
</form>
<?
include 'footer.php';
?>