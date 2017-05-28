<?php
include 'header.php';

// Remove the product
$phpALUGen_ProductLibrary->removeProduct($name, $ver);
?><h1>Product Has Been Removed</h1>
<b><a href="prodKeys.php">Go back to Product Code Generator</a></b>
<?php
include 'footer.php';
?>