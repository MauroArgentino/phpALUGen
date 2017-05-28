<?php
include 'header.php';

echo '<pre>';
// Assemble the configuration string
$cStr = 'user'.'&'.urlencode(serialize($user))."\n";
$cStr .= 'pass'.'&'.urlencode(serialize($pass))."\n";
$cStr .= 'demo_enable'.'&'.urlencode(serialize($demo_enable))."\n";
$cStr .= 'demo_days'.'&'.urlencode(serialize($demo_days))."\n";
$cStr .= 'demo_products'.'&'.urlencode(serialize($demo_products))."\n";
$cStr .= 'demo_log'.'&'.urlencode(serialize($demo_log))."\n";
$cStr .= 'demo_log_days'.'&'.urlencode(serialize($demo_log_days))."\n";
$cStr .= 'demo_block'.'&'.urlencode(serialize($demo_block))."\n";
$cStr .= 'demo_block_days'.'&'.urlencode(serialize($demo_block_days))."\n";
$cStr .= 'noUpdateCheck'.'&'.urlencode(serialize($noUpdateCheck))."\n";

// Check to see if we already have a configuration saved
if ($phpALUGen_ProductLibrary->retrieveProduct('phpalugen_admin_config', '1.0') != 0) {
	// If we do, remove it so we can write the new one.
	$phpALUGen_ProductLibrary->removeProduct('phpalugen_admin_config', '1.0');
}

// Add our configuration
$config = new phpALUGen_ProductInfo;
$config->Name = 'phpalugen_admin_config';
$config->Version = '1.0';
$config->GCode = base64_encode($cStr);
$phpALUGen_ProductLibrary->addProduct($config);
?>
<h1>Configuration Saved!</h1>
<?php
include 'footer.php';
?>
