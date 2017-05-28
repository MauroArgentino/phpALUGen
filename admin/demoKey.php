<?
/* Use <?=$libKey?> or <?=$libKeySplit?> in demo_template.html
//  to display the generated liberation key.
*/

$noHeader = true;
include 'header.php';

// Make sure demo keys are ENABLED
if (!$admin_config['demo_enable']) {
	die('Sorry, Demo keys aren\'t enabled right now.');
}

// Make sure that the product can have demo keys
if (!in_array($product, $admin_config['demo_products'])) {
	die('Sorry, Demo keys aren\'t available for that product right now.');
}

// See if we should check for already-requested keys
if ($admin_config['demo_log']) {
	// Make sure the user hasn't requested a key yet
	$keyListFile = dirname(__FILE__).'/demo_key_requests';
	if (is_file($keyListFile)) {
		$keyList = file($keyListFile);
		foreach($keyList as $keyId => $key) {
			// Split the information
			$keyParts = split(';', trim($key));
			
			// Remove the old keys
			if ($admin_config['demo_log'] && ($admin_config['demo_log_days'] != 0) && ($keyParts[1] < (time() - $admin_config['demo_log_days']*24*60*60))) {
				unset($keyList[$keyId]);
			} else {
				// Check to see if they have already used the installation code for this product (within the time limit)
				if ($admin_config['demo_block'] && ($keyParts[0] == $InstallationCode) && ($keyParts[2] == $product) && !($keyParts[1] < (time() - $admin_config['demo_block_days']*24*60*60))) {
					die('Sorry, You can\'t get more that one demo key for this product.');
				}
			}
		}
	}
	$keyList[] = $InstallationCode.';'.time().';'.$product;
	
	// Write out the key listing
	$fp = fopen($keyListFile, 'w');
	foreach($keyList as $key) {
		fwrite($fp, trim($key)."\n");
	}
	fclose($fp);
}

$prodParts = unserialize(urldecode($product));

$license = new phpALUGen_License;
$license->ProductName = $prodParts[0];
$license->ProductVer = $prodParts[1];
$license->RegisteredDate = gmdate('Y/m/d');
$license->LicenseClass = 'Single';
$license->LicenseType = 1;
$license->Expiration = gmdate('Y/m/d', time()+$admin_config['demo_days']*24*60*60);
$license->MaxCount = '0';

// Get a liberation key
$phpALUGen = new phpALUGen;
$libKey = $phpALUGen->genKey($license, $InstallationCode);
//echo '<pre>'.$InstallationCode;
//print_r($GLOBALS);
$libKeySplit = chunk_split($libKey, 64);

include 'demo_template.html';
?>