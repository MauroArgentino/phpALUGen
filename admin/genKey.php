<?
include 'header.php';

if ($strictCompat == '3.3') {
	$dateFormat = 'Y/m/d H:i:s';
} else {
	$dateFormat = 'Y/m/d';
}

$prodParts = unserialize(urldecode($product));

$license = new phpALUGen_License;
$license->ProductName = $prodParts[0];
$license->ProductVer = $prodParts[1];
$license->RegisteredLevel = $RegisteredLevel;
$license->RegisteredDate = gmdate($dateFormat);
$license->LicenseClass = 'Single';
$license->LicenseType = $LicenseType;
if ($license->LicenseType == 1) { // Periodic
	$license->Expiration = gmdate($dateFormat, time()+$licensetype1_exp*24*60*60);
} elseif ($license->LicenseType == 3) { // Time Locked
	$license->Expiration = $licensetype3_exp;
}
$license->MaxCount = '1';

// Get a liberation key
$phpALUGen = new phpALUGen;
$liberationKey = $phpALUGen->genKey($license, $InstallationCode);

// Write out the liberation key
echo '<h2>Liberation Key:</h2><pre>'.chunk_split($liberationKey, 64).'</pre><br><br>';
?>
<b><a href="licenseKeys.php">Go back to License Key Generator</a></b>
<?
include 'footer.php';
?>