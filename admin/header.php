<?php
// This makes sure we don't get errors for accessing variables we haven't declared.
// Report all errors except E_NOTICE
// This is the default value set in php.ini
error_reporting(E_ALL & ~E_NOTICE);
//ini_set('display_errors', false);
/*
	Versión modificada por mí, Mauro Javier Montenegro,
	para que funcione con PHP 7.0 y MySQL 5.7.6
	Fecha: 28/05/2017
	Lugar: Villa Ángela, Chaco - Argentina.
*/
$version = '3.4.2';
$tagline = 'for AL3.3 and 3.4';

// This counteracts register_globals = off in php.ini
if (!ini_get('register_globals')) {
	$types_to_register = array('GET','POST','COOKIE','SESSION','SERVER');
	foreach ($types_to_register as $type) {
		if (@count(${'HTTP_' . $type . '_VARS'}) > 0) {
			extract(${'HTTP_' . $type . '_VARS'}, EXTR_OVERWRITE);
		}
	}
}

// This disables the login check. Set this to 1 if you forget your username or password.
//  Otherwise, set it to 0. DO NOT COMMENT OUT THIS LINE.
$noLogin = 0;

// ~~~~~ LIBRARY ~~~~~
$curCwd = getcwd();
chdir('..');
require_once 'phpALUGen.php';
chdir($curCwd);

// ~~~~~ CONFIG ~~~~~
$configstr = $phpALUGen_ProductLibrary->retrieveProduct('phpalugen_admin_config', '1.0');
$configstr = base64_decode($configstr->GCode);
$configlines = explode("\n", $configstr);
foreach ($configlines as $configline) {
	$configparts = explode('&', $configline);
	$admin_config[urldecode($configparts[0])] = unserialize(urldecode($configparts[1]));
}
// Make sure the products set is an array
if (!is_array($admin_config['demo_products'])) { $admin_config['demo_products'][] = $admin_config['demo_products']; }
// If there is no username or password, make login not required
if (($admin_config['user'] == '') && ($admin_config['pass'] == '')) { $noLogin = 1; }

if ($noHeader) { return; }

// ~~~~~ LOGIN ~~~~~
// Our 'not logged in function'.
function notLoggedIn() {
	global $noLogin;
	if ($noLogin != 1) {
		header('WWW-Authenticate: Basic realm="phpALUGen Administration"');
		header('HTTP/1.0 401 Unauthorized');
		echo 'Please Login Correctly. If you have forgotten your password, edit header.php and set $noLogin to 1.';
		exit;
	}
}

// Make sure the user is logged in
if (!isset($_SERVER['PHP_AUTH_USER'])) {
	notLoggedIn();
} else {
	if (($_SERVER['PHP_AUTH_USER'] != $admin_config['user']) || ($_SERVER['PHP_AUTH_PW'] != $admin_config['pass'])) {
		notLoggedIn();
	}
}
?><html>
<head>
<title>ActiveLock Universal GENerator</title>
<style type="text/css">H1 { font-family: Verdana, Arial, sans-serif; font-size: 27px; line-height: 1.5; } H2 { font-family: Verdana, Arial, sans-serif; font-size: 18px; line-height: 1.5; } TD, UL, P, BODY { font-family: Verdana, Arial, sans-serif; font-size: 11px; line-height: 1.5; } TABLE.installForm { border-right: #cccccc 1px solid; border-top: #cccccc 1px solid; border-left: #cccccc 1px solid; border-bottom: #cccccc 1px solid; background-color: #ffffee; }</style>
</head>
<body>
<center>
<?php
// Check for an upgrade
if ($admin_config['noUpdateCheck'] != 'true') {
	if (filemtime('updatecheck.txt') < (time() - 60*60)) {
		//var_dump((filemtime('updatecheck.txt') < (time() - 60*60)));
		$updateText = @file_get_contents('http://lardbucket.org/projects/activelock/updatecheck.php?v='.$version);
		$fp = fopen('updatecheck.txt', 'w');
		fwrite($fp, $updateText);
		fclose($fp);
	} else {
		//var_dump(time()- 60 *60*24*180);
		//var_dump(filemtime('updatecheck.txt'));
		$updateText = file_get_contents('updatecheck.txt');
	}
	if ($updateText && ($updateText != 'NOUP')) {
		echo $updateText;
	}
}
?>
<table width="95%" class="installForm">
	<tr>
		<td width="33%">
			<center><a href="prodKeys.php"><b>Generador de Código de Producto</b></a></center>
		</td>
		<td width="33%">
			<center><a href="licenseKeys.php"><b>Generador de Claves de Licencia</b></a></center>
		</td>
		<td width="33%">
			<center><a href="configuration.php"><b>Configurar</b></a></center>
		</td>
		<td>
			<center><img border="0" src="small-logo.png" /><br /><nobr>phpALUGen <?php echo $version?></nobr><br /><nobr><?php echo $tagline?></nobr></center>
		</td>
	</tr>
</table>
<br />
<table width="95%" class="installForm"><tr><td>
