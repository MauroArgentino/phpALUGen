<!--DOCTYPE html-->
<?php
$conexionRoot = @mysqli_connect($_POST['ph_mysql_dbserver'], 'root', $_POST['ph_mysql_dbroot']);
if (get_magic_quotes_gpc()) {
	// Strip the added slashes
	$_REQUEST = array_map('stripslashes', $_REQUEST);
	$_GET = array_map('stripslashes', $_GET);
	$_POST = array_map('stripslashes', $_POST);
	$_COOKIE = array_map('stripslashes', $_COOKIE);
}

// This counteracts register_globals = off in php.ini
if (!ini_get('register_globals')) {
	$types_to_register = array('GET','POST','COOKIE','SESSION','SERVER');
	foreach ($types_to_register as $type) {
		if (@count(${'HTTP_' . $type . '_VARS'}) > 0) {
			extract(${'HTTP_' . $type . '_VARS'}, EXTR_OVERWRITE);
		}
	}
}

function failTest($text) {
	?>
	</td>
	</tr>
	<tr>
		<td colspan=2>
			<h2>There was an error!</h2>
			<?php echo $text?><br /><br />
			<a href="javascript:history.go(-1)">Please go back and fix the error</a>
		</td>
	</tr></table></center></body></html>
	<?php
	die();
}

function useRoot($rootPw) {
	echo 'Attempting connection with root password: ';
	if (@mysqli_connect($_POST['ph_mysql_dbserver'], 'root', $rootPw)) {
		echo '<font color="green">Connected</font><br />';
		$usedRoot = true;
	} else {
		echo '<font color="red">Could Not Connect</font><br />';
		failTest('We could not connect to the database with the user supplied nor the root password. Please check that your settings are correct.');
	}
}

$css = '<style type="text/css">h1 { font-family: Verdana, Arial, sans-serif; font-size: 27px; line-height: 1.5; } H2 { font-family: Verdana, Arial, sans-serif; font-size: 18px; line-height: 1.5; } TD, UL, P, BODY { font-family: Verdana, Arial, sans-serif; font-size: 11px; line-height: 1.5; } TABLE.installForm { border-right: #cccccc 1px solid; border-top: #cccccc 1px solid; border-left: #cccccc 1px solid; border-bottom: #cccccc 1px solid; background-color: #ffffee; }</style>';

if (isset($_POST['installing'])) {
	$requiredFiles = array('productLibrary.php', 'keyTools.php', 'phpALUGen.php', 'miscClasses.php', 'admin/index.php');
	?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Installing phpALUGen</title>
	<?php echo $css;?>
</head>
<body>
	<h1>Installing phpALUGen</h1>
	<center>
		<table width="95%" border="0" cellpadding="0" class="installForm">
			<tr>
				<td colspan=2>
					<h2>Tests:</h2>
				</td>
			</tr>
			<tr>
				<td width="30%" valign="top"><b>phpALUGen Files:</b></td>
				<td width="70%">
				<?php
				foreach ($requiredFiles as $fileOn) {
					echo $fileOn.': ';
					if (is_file($_POST['phpalugen_dir'].'/'.$fileOn)) {
						echo '<font color="green">Exists</font>';
					} else {
						echo '<font color="red">Does Not Exist</font>';
						failTest('We could not find a required file! Please ensure you have extracted the entire package and that you downloaded it correctly.');
					}
					echo '<br />';
				}
				?>
				</td>
			</tr>
			<tr>
				<td width="30%" valign="top"><b>PEAR Files:</b></td>
				<td width="70%">
				<?php
				echo 'Crypt/RSA.php: ';
				if (is_file($_POST['pear_dir'].'/Crypt/RSA.php')) {
					echo '<font color="green">Exists</font>';
				} else {
					echo '<font color="red">Does Not Exist</font>';
					failTest('We could not find a required file! Please ensure you have extracted the entire package and that you downloaded it correctly.');
				}
				?>
				</td>
			</tr>
			<tr>
				<td width="30%" valign="top"><b>Product Library Handler:</b></td>
				<td width="70%">
				<?php
				echo 'Checking handler: ';
				if (in_array($_POST['product_handler'], array('mysql', 'file'))) {
					echo '<font color="green">Valid</font>';
				} else {
					echo '<font color="red">Invalid</font>';
					failTest('You selected an unsupported product library handler.');
				}
				?>
				</td>
			</tr>
			<?php if ($_POST['product_handler'] == 'mysql') { ?>
			<tr>
				<td width="30%" valign="top"><b>MySQL Information:</b></td>
				<td width="70%">
				<?php
				$createdUser = false;
				$usedRoot = false;
				echo 'Connecting with user supplied: ';
				
				if (@mysqli_connect($_POST['ph_mysql_dbserver'], $_POST['ph_mysql_dbuser'], $_POST['ph_mysql_dbpass'])) {
					echo '<font color="green">Connected</font><br />';
				} else {
					
					echo '<font color="red">Could Not Connect, will try with root user</font><br />';
					useRoot($_POST['ph_mysql_dbroot']);
					echo 'Creating user: ';
					$hosts = array('%', 'localhost', 'localhost.localdomain');
					foreach ($hosts as $host) {
						$sql = "GRANT USAGE ON * . * TO '".mysqli_real_escape_string($conexionRoot, $_POST['ph_mysql_dbuser'])."'@'$host' IDENTIFIED BY '".mysqli_real_escape_string($conexionRoot, $_POST['ph_mysql_dbpass'])."' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 ;";
						if (!@mysqli_query($conexionRoot, $sql)) {
							echo '<font color="red">Failed</font><br />';
							failTest('We could not create the user with the username you entered. Please check that you specified a valid name.');
						}
					}
					echo '<font color="green">Succeeded</font><br />';
					$createdUser = true;
				}
				
				echo 'Connecting to the supplied database ('.$_POST['ph_mysql_dbname'].'): ';
				if (@mysqli_select_db($conexionRoot, $_POST['ph_mysql_dbname'])) {
					echo '<font color="green">Succeeded</font><br />';
				} else {
					echo '<font color="red">Could Not Connect, Attempting to create it</font><br />';
					echo 'Creating the database: ';
					$sql = 'CREATE DATABASE '.mysqli_real_escape_string($conexionRoot, $_POST['ph_mysql_dbname']).';';
					if (@mysqli_query($conexionRoot, $sql)) {
						echo '<font color="green">Succeeded</font><br />';
					} else {
						if ($usedRoot) {
							failTest('We could not create the database with the root account. Please check that your settings are correct.');
						} else {
							echo '<font color="red">Could Not Create It, Attempting With root Password</font><br />';
							useRoot($_POST['ph_mysql_dbroot']);
							echo 'Creating the database: ';
							if (@mysqli_query($conexionRoot, $sql)) {
								echo '<font color="green">Succeeded</font><br />';
							} else {
								echo '<font color="red">Failed</font><br />';
								failTest('We could not create the database with the root account nor the user you supplied. Please check that your settings are correct.');
							}
						}
					}
					echo 'Connecting to the supplied database (second try): ';
					if (@mysqli_select_db($conexionRoot, $_POST['ph_mysql_dbname'])) {
						echo '<font color="green">Succeeded</font><br />';
					} else {
						echo '<font color="red">Failed</font><br />';
						failTest('We could not connect to the database, even though it seems we created it. Check that your copy of MySQL is working properly.');
					}
				}
				
				if ($createdUser) {
					echo 'Giving created user permissions to modify the database: ';
					foreach ($hosts as $host) {
						$sql = "GRANT ALL PRIVILEGES ON `".mysqli_real_escape_string($conexionRoot, $_POST['ph_mysql_dbname'])."` . * TO '".mysqli_real_escape_string($conexionRoot, $_POST['ph_mysql_dbuser'])."'@'$host' WITH GRANT OPTION ;";
						if (!@mysqli_query($conexionRoot, $sql)) {
							echo '<font color="red">Failed</font><br />';
							failTest('We could give permissions to the user with the username you entered. Please check that you specified a valid name and that your MySQL server is working properly.');
						}
					}
					echo '<font color="green">Succeeded</font><br />';
				}
				
				echo 'Checking for existing products table: ';
				$sql = 'SHOW TABLES';
				if (!($result = @mysqli_query($conexionRoot, $sql))) {
					failTest('A critical MySQL query failed. Please check that your MySQL server is operational');
				}
				$foundTable = false;
				while (($thisTable = mysqli_fetch_row($result)) && (!$foundTable)) {
					if ($thisTable[0] == $$_POST['ph_mysql_dbtable']) {
						$foundTable = true;
					}
				}
				if ($foundTable) {
					echo '<font color="green">Exists</font><br />';
					echo 'Checking structure: ';
					$sql = 'SHOW COLUMNS FROM '.mysqli_real_escape_string($conexionRoot, $_POST['ph_mysql_dbtable']);
					if (!($result = @mysqli_query($conexionRoot, $sql))) {
						failTest('A critical MySQL query failed. Please check that your MySQL server is operational');
					}
					$reqFields = Array();
					$reqFields[] = Array('Field' => 'name', 'Type' => 'tinytext');
					$reqFields[] = Array('Field' => 'version', 'Type' => 'tinytext');
					$reqFields[] = Array('Field' => 'gcode', 'Type' => 'longtext');
					$reqFields[] = Array('Field' => 'vcode', 'Type' => 'longtext');
					$wrongTable = false;
					while (($thisField = mysqli_fetch_assoc($result)) && (!$wrongTable)) {
						$validField = false;
						foreach ($reqFields as $reqField) {
							$seemsRight = true;
							foreach ($reqField as $tag => $value) {
								if ($thisField[$tag] != $value) {
									$seemsRight = false;
								}
							}
							if ($seemsRight) {
								$validField = true;
							}
						}
						if (!$validField) {
							$wrongTable = true;
						}
					}
					
					if ($wrongTable) {
						echo '<font color="red">Incorrect</font><br />';
						failTest('The table name you provided already exists, but it is in the wrong format. Please remove the existing table or choose a different name for the phpALUGen table.');
					}
					echo '<font color="green">Correct</font><br />';
				} else {
					echo '<font color="green">Does Not Exist</font><br />';
					echo 'Creating table: ';
					$sql = 'CREATE TABLE '.mysqli_real_escape_string($conexionRoot, $_POST['ph_mysql_dbtable']).' (name TINYTEXT NOT NULL, version TINYTEXT NOT NULL, gcode LONGTEXT NOT NULL, vcode LONGTEXT NOT NULL);';
					if (!($result = @mysqli_query($conexionRoot, $sql))) {
						echo '<font color="red">Failed</font><br />';
						failTest('We could not create the necessary table. Please check that you have permissions to do so and that your MySQL server is functioning properly.');
					}
					echo '<font color="green">Succeeded</font><br />';
				}
				?>
				</td>
			</tr>
			<?php } elseif ($_POST['product_handler'] == 'file') { ?>
			<tr>
				<td width="30%" valign="top"><b>File Information:</b></td>
				<td width="70%">
				<?php
				echo 'Checking for product library file: ';
				if (@is_file($_POST['ph_file_filename'])) {
					echo '<font color="green">Exists</font><blockquote><font color="red">WARNING: Ensure this file is a valid products.ini-format file. Otherwise, phpALUGen may have trouble handling products. If you are unsure, go back and change to a different filename.</font></blockquote>';
				} else {
					echo '<font color="green">Does Not Exist</font><br />';
					echo 'Attempting to create it: ';
					if (!@touch($_POST['ph_file_filename'])) {
						echo '<font color="red">Could Not Create File</font><br />';
						failTest('We could not create your product library file. Please create it manually and ensure that the server can write to it.');
					}
					echo '<font color="green">Succeeded</font><br />';
				}
				
				echo 'Checking to see that it is writable: ';
				if (@is_writable($_POST['ph_file_filename'])) {
					echo '<font color="green">S'?>&iacute;<?php echo '</font><br />';
				} else {
					echo '<font color="red">No</font><br />';
					failTest('The file you specified was not writable. Please ensure that your server can write to it (change the file permissions).');
				}
				?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td width="30%" valign="top"><b>Configuration File Information:</b></td>
				<td width="70%">
				<?php
				echo 'Checking to see if config.inc.php is writable: ';
				if (!@is_writable($_POST['phpalugen_dir'].'/config.inc.php')) {
					echo '<font color="red">No</font><br />';
					failTest('The configuration file was not writable. Please ensure that your server can write to it (change the file permissions).');
				}
				echo '<font color="green">Yes</font><br />';
				
				echo 'Writing configuration file: ';
				if (!($fp = @fopen($_POST['phpalugen_dir'].'/config.inc.php', 'w'))) {
					echo '<font color="red">Failed</font><br />';
					failTest('We failed to write the configuration file. Ensure that the file is writable by the server and try again. If this problem continues, you may need to edit the configuration file by hand.');
				}
				$configFile[] = '<?php';
				$configFile[] = '$phpALUGen_dir = \''.addslashes($_POST['phpalugen_dir']).'\';';
				$configFile[] = '$phpALUGen_config[\'product_handler\'] = \''.addslashes($_POST['product_handler']).'\';';
				$configFile[] = '$phpALUGen_config[\'ph_mysql_dbserver\'] = \''.addslashes($_POST['ph_mysql_dbserver']).'\';';
				$configFile[] = '$phpALUGen_config[\'ph_mysql_dbuser\'] = \''.addslashes($_POST['ph_mysql_dbuser']).'\';';
				$configFile[] = '$phpALUGen_config[\'ph_mysql_dbpass\'] = \''.addslashes($_POST['ph_mysql_dbpass']).'\';';
				$configFile[] = '$phpALUGen_config[\'ph_mysql_dbname\'] = \''.addslashes($_POST['ph_mysql_dbname']).'\';';
				$configFile[] = '$phpALUGen_config[\'ph_mysql_dbtable\'] = \''.addslashes($_POST['ph_mysql_dbtable']).'\';';
				$configFile[] = '$phpALUGen_config[\'ph_file_filename\'] = \''.addslashes($_POST['ph_file_filename']).'\';';
				$configFile[] = '?>';
				foreach ($configFile as $configLine) {
					if (!@fwrite($fp, $configLine."\n")) {
						echo '<font color="red">Failed</font><br />';
						failTest('We failed to write the configuration file. Ensure that the file is writable by the server and try again. If this problem continues, you may need to edit the configuration file by hand.');
					}
				}
				echo '<font color="green">Succeeded!</font><br />';
				?>
				</td>
			</tr>
			<tr>
				<td colspan=2>
					<center><h2>You are now done with installation!</h2></center>
				</td>
			</tr>
		</table>
	</center>
</body>
</html>
	<?php
} else {
	?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Installing phpALUGen</title>
	<?php echo $css;?>
	<script language="JavaScript">
		var hidChoose = false;
		
		function showHide() {
			var mySel = document.getElementById('product_handler');//document.forms['configform'].product_handler;
			var selection = mySel.selectedIndex;
			var valsel = mySel.value;
			if (valsel != 'none' && !hidChoose) {
				selection -= 1;
				hidChoose = true;
			}
			mySel.options.length = 0;
			mySel.options[0] = new Option('MySQL', 'mysql');
			mySel.options[1] = new Option('Archivo', 'file');
			mySel.selectedIndex = selection;
			
			if (valsel == 'mysql') {
				visibId('mysql', 'block');
				visibId('file', 'none');
			} else if (valsel == 'file') {
				visibId('mysql', 'none');
				visibId('file', 'block');
			}
			
			if (valsel != 'none') {
				visibId('submit', 'block');
			}
		}
		
		function visibId(id, state) {
			document.getElementById(id).style.display = state;
		}
		
		function jsOnLoad() {
			if (document.getElementById('product_handler').selectedIndex) {
				showHide();
			} else {
				document.getElementById('product_handler').selectedIndex = 0;
			}
		}
	</script>
</head>
<body onload="jsOnLoad();">
	<h1>Instalando phpALUGen</h1>
	<center>
		<form action="install.php" method="post" name="configform">
			<table width="95%" border="0" cellpadding="0" class="installForm">
				<tr>
					<td colspan=2><h2>Opciones Generales:</h2></td>
				</tr>
				<tr>
					<td width="30%" valign="top">Directorio phpALUGen:</td>
					<td width="70%">
						<input type="text" name="phpalugen_dir" value="<?php echo dirname(__FILE__)?>" size="50"><br>
						Set this to the directory in which you have placed the phpALUGen files. It has been automatically detected. If the directory is incorrect, change it here.
						Do not include a trailing slash.
					</td>
				</tr>
				<tr>
					<td width="30%" valign="top">PEAR Directory:</td>
					<td width="70%">
						<input type="text" name="pear_dir" value="<?php echo dirname(__FILE__)?>" size="50"><br>
						The location of the PEAR Crypt/RSA library. If you have no idea what this is, the default (the same setting as your phpALUGen Directory) should be correct.
						If you have already installed PEAR, you should leave this blank. If you moved the Crypt/RSA folder, change this to the location in which you placed it.
						Do not include the Crypt/RSA itself, and leave off a trailing slash.
					</td>
				</tr>
				<tr>
					<td width="30%" valign="top">Product Library Handler:</td>
					<td width="70%">
						<select name="product_handler" id="product_handler" onchange="showHide();">
							<option selected value="none">(Elija uno)</option>
							<option value="mysql">MySQL</option>
							<option value="file">Archivo</option>
						</select><br>
					</td>
				</tr>
			</table>
			
			<div id="mysql" style="display: none"><br />
				<table width="95%" border="0" cellpadding="0" class="installForm">
					<tr>
						<td colspan=2><h2>Opciones de MySQL:</h2></td>
					</tr>
					<tr>
						<td width="30%" valign="top">Ubicación del Servidor MySQL:</td>
						<td width="70%">
							<input type="text" name="ph_mysql_dbserver" value="192.168.1.120" size="50"><br>
							<!--The location of your MySQL database server. 99% of the time, 'localhost' is the correct setting.-->
							La ubicación de su servidor de base de datos MySQL. 99% de las veces, 'localhost' es el ajuste correcto.
						</td>
					</tr>
					<tr>
						<td width="30%" valign="top">Usuario de MySQL:</td>
						<td width="70%">
							<input type="text" name="ph_mysql_dbuser" value="phpalugen" size="50"><br>
							The username you use to login to the MySQL server.
						</td>
					</tr>
					<tr>
						<td width="30%" valign="top">Contraseña de MySQL:</td>
						<td width="70%">
							<input type="password" name="ph_mysql_dbpass" value="phpalugen" size="50"><br>
							The password you use to login to the MySQL server.
						</td>
					</tr>
					<tr>
						<td width="30%" valign="top">Nombre de base de datos MySQL:</td>
						<td width="70%">
							<input type="text" name="ph_mysql_dbname" value="phpalugen" size="50"><br>
							The name of the database you would like phpALUGen to use to store its data.
						</td>
					</tr>
					<tr>
						<td width="30%" valign="top">MySQL Table:</td>
						<td width="70%">
							<input type="text" name="ph_mysql_dbtable" value="productos" size="50"><br>
							The name of the table in the database you would like phpALUGen to use to store its data.
						</td>
					</tr>
					<tr>
						<td width="30%" valign="top">MySQL Root Password:</td>
						<td width="70%">
							<input type="password" name="ph_mysql_dbroot" value="" size="50"><br>
							<b>If the account or username you would like to use for phpALUGen does not exist</b>, you can enter your MySQL root password here,
							and phpALUGen setup will automatically create the user and give it access to the necessary database.
							If the account and database both exist, do not enter anything here. <b>phpALUGen setup will not store this password.</b>
						</td>
					</tr>
				</table>
			</div>
			
			<div id="file" style="display: none"><br />
				<table width="95%" border="0" cellpadding="0" class="installForm">
					<tr>
						<td colspan=2><h2>File Handler Options:</h2></td>
					</tr>
					<tr>
						<td width="30%" valign="top">Product Library Location:</td>
						<td width="70%">
							<input type="text" name="ph_file_filename" value="<?php echo dirname(__FILE__)?>\products.ini" size="50"><br>
							Set this to the location where you want your product library information to be stored. If you already have a products.ini file, enter it's location here.
						</td>
					</tr>
				</table>
			</div>
			
			<div id="submit" style="display: none"><br />
				<input type="hidden" name="installing" value="1" />
				<center><input type="submit" value="Continuar con la Instalación" /></center>
			</div>
		</form>
	</center>
</body>
</html>
<?php } ?>