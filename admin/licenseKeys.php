<?php
include 'header.php';

$allProds = $phpALUGen_ProductLibrary->getAllProducts();
?>
<script language="JavaScript">
function $(eleId) {
	return document.getElementById(eleId);
}

function updateDemoHtml() {
	var prodname = document.getElementById('demo_product').value;
	var thisHtml = '';
	thisHtml += '<form method="POST" action="http://<?=$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])?>/demoKey.php">\n';
	thisHtml += '<input type="text" name="InstallationCode" /><br />\n';
	thisHtml += '<input type="hidden" name="product" value="'+prodname+'" />\n';
	thisHtml += '<input type="submit" value="Get Demo Key" />\n';
	thisHtml += '</form>';
	document.getElementById('demo_html').value = thisHtml;
}

function updateLicenseType() {
	var lictype = document.getElementById('LicenseType').value;
	if (lictype == 1) {
		document.getElementById('licensetype_1').style.display = '';
		document.getElementById('licensetype_3').style.display = 'none';
	} else if (lictype == 2) {
		document.getElementById('licensetype_1').style.display = 'none';
		document.getElementById('licensetype_3').style.display = 'none';
	} else {
		document.getElementById('licensetype_1').style.display = 'none';
		document.getElementById('licensetype_3').style.display = '';
	}
}

var END_OF_INPUT = -1;
var base64Chars = new Array(
    'A','B','C','D','E','F','G','H',
    'I','J','K','L','M','N','O','P',
    'Q','R','S','T','U','V','W','X',
    'Y','Z','a','b','c','d','e','f',
    'g','h','i','j','k','l','m','n',
    'o','p','q','r','s','t','u','v',
    'w','x','y','z','0','1','2','3',
    '4','5','6','7','8','9','+','/'
);

var reverseBase64Chars = new Array();
for (var i=0; i < base64Chars.length; i++){
    reverseBase64Chars[base64Chars[i]] = i;
}

function setBase64Str(str){
    base64Str = str;
    base64Count = 0;
}
function readBase64(){    
    if (!base64Str) return END_OF_INPUT;
    if (base64Count >= base64Str.length) return END_OF_INPUT;
    var c = base64Str.charCodeAt(base64Count) & 0xff;
    base64Count++;
    return c;
}

function readReverseBase64(){   
    if (!base64Str) return END_OF_INPUT;
    while (true){      
        if (base64Count >= base64Str.length) return END_OF_INPUT;
        var nextCharacter = base64Str.charAt(base64Count);
        base64Count++;
        if (reverseBase64Chars[nextCharacter]){
            return reverseBase64Chars[nextCharacter];
        }
        if (nextCharacter == 'A') return 0;
    }
    return END_OF_INPUT;
}

function ntos(n){
    n=n.toString(16);
    if (n.length == 1) n="0"+n;
    n="%"+n;
    return unescape(n);
}

function encodeBase64(str){
    setBase64Str(str);
    var result = '';
    var inBuffer = new Array(3);
    var lineCount = 0;
    var done = false;
    while (!done && (inBuffer[0] = readBase64()) != END_OF_INPUT){
        inBuffer[1] = readBase64();
        inBuffer[2] = readBase64();
        result += (base64Chars[ inBuffer[0] >> 2 ]);
        if (inBuffer[1] != END_OF_INPUT){
            result += (base64Chars [(( inBuffer[0] << 4 ) & 0x30) | (inBuffer[1] >> 4) ]);
            if (inBuffer[2] != END_OF_INPUT){
                result += (base64Chars [((inBuffer[1] << 2) & 0x3c) | (inBuffer[2] >> 6) ]);
                result += (base64Chars [inBuffer[2] & 0x3F]);
            } else {
                result += (base64Chars [((inBuffer[1] << 2) & 0x3c)]);
                result += ('=');
                done = true;
            }
        } else {
            result += (base64Chars [(( inBuffer[0] << 4 ) & 0x30)]);
            result += ('=');
            result += ('=');
            done = true;
        }
    }
    return result;
}

function decodeBase64(str){
    setBase64Str(str);
    var result = "";
    var inBuffer = new Array(4);
    var done = false;
    while (!done && (inBuffer[0] = readReverseBase64()) != END_OF_INPUT
        && (inBuffer[1] = readReverseBase64()) != END_OF_INPUT){
        inBuffer[2] = readReverseBase64();
        inBuffer[3] = readReverseBase64();
        result += ntos((((inBuffer[0] << 2) & 0xff)| inBuffer[1] >> 4));
        if (inBuffer[2] != END_OF_INPUT){
            result +=  ntos((((inBuffer[1] << 4) & 0xff)| inBuffer[2] >> 2));
            if (inBuffer[3] != END_OF_INPUT){
                result +=  ntos((((inBuffer[2] << 6)  & 0xff) | inBuffer[3]));
            } else {
                done = true;
            }
        } else {
            done = true;
        }
    }
    return result;
}

var ICParts = Array();
var noKey = String.fromCharCode(110,111,107,101,121);

function updateUserName() {
	var ICText = decodeBase64(document.getElementById('InstallationCode').value);
	ICParts = ICText.split("\n");
	document.getElementById('UserName').value = ICParts[ICParts.length - 1];
	
	$('lock_mac').checked = false;
	$('lock_name').checked = false;
	$('lock_hd_volume').checked = false;
	$('lock_hd_firm').checked = false;
	$('lock_windows').checked = false;
	$('lock_bios').checked = false;
	$('lock_mobo').checked = false;
	$('lock_ip').checked = false;
	$('lock_mac').disabled = false;
	$('lock_name').disabled = false;
	$('lock_hd_volume').disabled = false;
	$('lock_hd_firm').disabled = false;
	$('lock_windows').disabled = false;
	$('lock_bios').disabled = false;
	$('lock_mobo').disabled = false;
	$('lock_ip').disabled = false;
	$('data_mac').innerHTML = '';
	$('data_name').innerHTML = '';
	$('data_hd_volume').innerHTML = '';
	$('data_hd_firm').innerHTML = '';
	$('data_windows').innerHTML = '';
	$('data_bios').innerHTML = '';
	$('data_mobo').innerHTML = '';
	$('data_ip').innerHTML = '';
	
	// If the code is blank, forget it
	if (ICParts.length < 2) {
		return;
	}
	
	var usedLockNone = false;
	if (ICParts[0].substr(0,1) == '+') {
		ICParts[0] = ICParts[0].substring(1);
		usedLockNone = true;
	}
	
	if (usedLockNone) {
		$('data_mac').innerHTML = ICParts[0];
		$('data_name').innerHTML = ICParts[1];
		$('data_hd_volume').innerHTML = ICParts[2];
		$('data_hd_firm').innerHTML = ICParts[3];
		$('data_windows').innerHTML = ICParts[4];
		$('data_bios').innerHTML = ICParts[5];
		$('data_mobo').innerHTML = ICParts[6];
		$('data_ip').innerHTML = ICParts[7];
	} else {
		if (ICParts[0] != noKey) {
			$('data_mac').innerHTML = ICParts[0];
		} else {
			$('lock_mac').disabled = true;
		}
		if (ICParts[1] != noKey) {
			$('data_name').innerHTML = ICParts[1];
		} else {
			$('lock_name').disabled = true;
		}
		if (ICParts[2] != noKey) {
			$('data_hd_volume').innerHTML = ICParts[2];
		} else {
			$('lock_hd_volume').disabled = true;
		}
		if (ICParts[3] != noKey) {
			$('data_hd_firm').innerHTML = ICParts[3];
		} else {
			$('lock_hd_firm').disabled = true;
		}
		if (ICParts[4] != noKey) {
			$('data_windows').innerHTML = ICParts[4];
		} else {
			$('lock_windows').disabled = true;
		}
		if (ICParts[5] != noKey) {
			$('data_bios').innerHTML = ICParts[5];
		} else {
			$('lock_bios').disabled = true;
		}
		if (ICParts[6] != noKey) {
			$('data_mobo').innerHTML = ICParts[6];
		} else {
			$('lock_mobo').disabled = true;
		}
		if (ICParts[7] != noKey) {
			$('data_ip').innerHTML = ICParts[7];
		} else {
			$('lock_ip').disabled = true;
		}
	}
	
	if ((ICParts[0] == noKey) || (ICParts[0] == '00 00 00 00 00 00') || (ICParts[0] == '00-00-00-00-00-00') || (ICParts[0] == '') || (ICParts[0] == 'Not Available')) { // MAC Address
		$('lock_mac').disabled = true;
	}
	
	if (ICParts[1] == noKey) {
		$('lock_name').disabled = true;
	}
	
	if ((ICParts[2] == noKey) || (ICParts[2] == 'Not Available') || (ICParts[2] == '0000-0000')) { // HDD Volume Serial
		$('lock_hd_volume').disabled = true;
	}
	
	if ((ICParts[3] == noKey) || (ICParts[3] == 'Not Available')) { // HDD Firmware
		$('lock_hd_firm').disabled = true;
	}
	
	if (ICParts[4] == noKey) {
		$('lock_windows').disabled = true;
	}
	
	if ((ICParts[5] == noKey) || (ICParts[5] == 'Not Available')) { // BIOS Serial
		$('lock_bios').disabled = true;
	}
	
	if ((ICParts[6] == noKey) || (ICParts[6] == 'Not Available')) { // Motherboard Serial
		$('lock_mobo').disabled = true;
	}
	
	if ((ICParts[7] == noKey) || (ICParts[7] == 'Not Available')) { // IP Address
		$('lock_ip').disabled = true;
	}
	
	$('lock_mac').checked = !$('lock_mac').disabled;
	$('lock_name').checked = !$('lock_name').disabled;
	$('lock_hd_volume').checked = !$('lock_hd_volume').disabled;
	$('lock_hd_firm').checked = !$('lock_hd_firm').disabled;
	$('lock_windows').checked = !$('lock_windows').disabled;
	$('lock_bios').checked = !$('lock_bios').disabled;
	$('lock_mobo').checked = !$('lock_mobo').disabled;
	$('lock_ip').checked = !$('lock_ip').disabled;
}

function updateIC() {
	var newIC = '+';
	
	if ($('lock_mac').checked) {
		newIC += ICParts[0] + "\n";
	} else {
		newIC += noKey + "\n";
	}
	
	if ($('lock_name').checked) {
		newIC += ICParts[1] + "\n";
	} else {
		newIC += noKey + "\n";
	}
	
	if ($('lock_hd_volume').checked) {
		newIC += ICParts[2] + "\n";
	} else {
		newIC += noKey + "\n";
	}
	
	if ($('lock_hd_firm').checked) {
		newIC += ICParts[3] + "\n";
	} else {
		newIC += noKey + "\n";
	}
	
	if ($('lock_windows').checked) {
		newIC += ICParts[4] + "\n";
	} else {
		newIC += noKey + "\n";
	}
	
	if ($('lock_bios').checked) {
		newIC += ICParts[5] + "\n";
	} else {
		newIC += noKey + "\n";
	}
	
	if ($('lock_mobo').checked) {
		newIC += ICParts[6] + "\n";
	} else {
		newIC += noKey + "\n";
	}
	
	if ($('lock_ip').checked) {
		newIC += ICParts[7] + "\n";
	} else {
		newIC += noKey + "\n";
	}
	
	newIC += ICParts[ICParts.length - 1];
	
	$('InstallationCode').value = encodeBase64(newIC);
}
</script>
<b>Create License Key:</b>
<form method="POST" action="genKey.php">
<table>
	<tr>
		<td>
			Product:
		</td>
		<td>
			<select name="product">
			<?
foreach ($allProds as $curProduct) {
	?>
				<option value="<?=rawurlencode(serialize(array($curProduct->Name, $curProduct->Version)))?>"><?=$curProduct->Name?> - <?=$curProduct->Version?></option>
<? } ?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			License Type:
		</td>
		<td>
			<select name="LicenseType" id="LicenseType" onChange="updateLicenseType();">
				<option value="1">Periodic</option>
				<option value="2">Permanent</option>
				<option value="3">Time Locked</option>
			</select>
		</td>
	</tr>
	<tr id="licensetype_1">
		<td>
			Expires After:
		</td>
		<td>
			<input type="text" name="licensetype1_exp" value="30"> Day(s)
		</td>
	</tr>
	<tr id="licensetype_3" style="display:none;">
		<td>
			Expires On Date:
		</td>
		<td>
			<input type="text" name="licensetype3_exp" value="<?=gmdate('Y/n/j', time()+2*24*60*60)?>"> YYYY/(M)M/(D)D
		</td>
	</tr>
	<tr>
		<td>
			Registration Level:
		</td>
		<td>
			<input type="text" name="RegisteredLevel" value="" />
		</td>
	</tr>
	<tr>
		<td>
			Installation Code:
		</td>
		<td>
			<input type="text" name="InstallationCode" id="InstallationCode" size=75 onchange="updateUserName();">
			&nbsp;(Click off to update username and lock to)
		</td>
	</tr>
	<tr>
		<td>
			Username:
		</td>
		<td>
			<input type="text" name="UserName" id="UserName" size=75 disabled="disabled">
		</td>
	</tr>
	<tr>
		<td>
			Lock To:
		</td>
		<td>
			<table>
				<tr>
					<td>
						<input type="checkbox" name="lock_mac" id="lock_mac" value="1" onclick="updateIC();" />
						MAC Address:
					</td>
					<td id="data_mac">
						
					</td>
				</tr>
				<tr>
					<td>
						<input type="checkbox" name="lock_name" id="lock_name" value="1" onclick="updateIC();" />
						Computer Name:
					</td>
					<td id="data_name">
						
					</td>
				</tr>
				<tr>
					<td>
						<input type="checkbox" name="lock_hd_volume" id="lock_hd_volume" value="1" onclick="updateIC();" />
						HDD Volume Serial Number:
					</td>
					<td id="data_hd_volume">
						
					</td>
				</tr>
				<tr>
					<td>
						<input type="checkbox" name="lock_hd_firm" id="lock_hd_firm" value="1" onclick="updateIC();" />
						HDD Firmware Serial Number:
					</td>
					<td id="data_hd_firm">
						
					</td>
				</tr>
				<tr>
					<td>
						<input type="checkbox" name="lock_windows" id="lock_windows" value="1" onclick="updateIC();" />
						Windows Serial Number:
					</td>
					<td id="data_windows">
						
					</td>
				</tr>
				<tr>
					<td>
						<input type="checkbox" name="lock_bios" id="lock_bios" value="1" onclick="updateIC();" />
						BIOS Serial Number:
					</td>
					<td id="data_bios">
						
					</td>
				</tr>
				<tr>
					<td>
						<input type="checkbox" name="lock_mobo" id="lock_mobo" value="1" onclick="updateIC();" />
						Motherboard Serial Number:
					</td>
					<td id="data_mobo">
						
					</td>
				</tr>
				<tr>
					<td>
						<input type="checkbox" name="lock_ip" id="lock_ip" value="1" onclick="updateIC();" />
						IP Address:
					</td>
					<td id="data_ip">
						
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			Strict Compatability:
		</td>
		<td>
			<select name="strictCompat">
				<option value="3.4">3.4 (Works with 3.3 as well)</option>
				<option value="3.3">3.3 (Not necessary, works with 3.4)</option>
			</select>
		</td>
	</tr>
</table>
<input type="submit" value="Generate Liberation Key">
</form>
</td></tr></table>
<br />

<table width="95%" class="installForm"><tr><td>
<b>Demo Code:</b>
<? if (count($admin_config['demo_products']) == 0) { ?>
You haven't selected any products for demos. Go to the configuration section to select some.
<? } else { ?>
(Select a product to generate HTML code for requesting demo keys)
<form>
<table>
	<tr>
		<td>
			Product:
		</td>
		<td>
			<select name="demo_product" id="demo_product" onChange="updateDemoHtml();">
			<?
foreach ($admin_config['demo_products'] as $curProduct) {
	$curProductInfo = unserialize(urldecode($curProduct));
	?>
				<option value="<?=$curProduct?>"><?=$curProductInfo[0]?> - <?=$curProductInfo[1]?></option>
<? } ?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			HTML Code:
		</td>
		<td>
			<textarea id="demo_html" rows="5" cols="80" readonly="readonly"></textarea>
		</td>
		<script language="JavaScript">
				updateDemoHtml();
		</script>
	</tr>
</table>
</form>
<? } ?>
</td></tr></table>
<?
include 'footer.php';
?>
