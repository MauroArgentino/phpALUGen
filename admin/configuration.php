<?php
include 'header.php';

$allProds = $phpALUGen_ProductLibrary->getAllProducts();
?>
<b>Configure phpALUGen:</b>
<form method="POST" action="configurationSave.php">
<table><tr><td width="7"></td><td><table width="100%">
	<tr>
		<td colspan="2">
			<b>Administration Authentication (leave both fields blank to not require authentication):</b>
		</td>
	</tr>
	<tr>
		<td>
			<nobr>Username to login:</nobr>
		</td>
		<td width="100%">
			<input type="text" name="user" value="<?php echo $admin_config['user']?>">
		</td>
	</tr>
	<tr>
		<td>
			<nobr>Password to login:</nobr>
		</td>
		<td width="100%">
			<input type="password" name="pass" value="<?php echo $admin_config['pass']?>">
		</td>
	</tr>
	<tr>
		<td colspan="2">
			&nbsp;
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<b>Demo Settings:</b>
		</td>
	</tr>
	<tr>
		<td>
			<nobr>Enable:</nobr>
		</td>
		<td width="100%">
			<input type="checkbox" name="demo_enable"<?php echo $admin_config['demo_enable'] ? ' checked="checked"': ''?>>
		</td>
	</tr>
	<tr>
		<td>
			<nobr>Number of days demo keys last:</nobr>
		</td>
		<td width="100%">
			<input type="text" name="demo_days" size="5" value="<?php echo $admin_config['demo_days'] ? $admin_config['demo_days'] : '30'?>">
		</td>
	</tr>
	<tr>
		<td>
			<nobr>Demo keys are available for:</nobr>
		</td>
		<td width="100%">
			<select size=4 multiple name="demo_products[]">
<?
foreach ($allProds as $curProduct) {
	$prodString = rawurlencode(serialize(array($curProduct->Name, $curProduct->Version)));
	$thisSelected = in_array($prodString, $admin_config['demo_products']) ? ' selected="selected"' : '';
	?>
				<option value="<?php echo $prodString?>"<?php echo $thisSelected?>><?=$curProduct->Name?> - <?=$curProduct->Version?></option>
<? } ?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			<nobr>Log Used Keys:</nobr>
		</td>
		<td width="100%">
			<input type="checkbox" name="demo_log"<?php echo $admin_config['demo_log'] ? ' checked="checked"': ''?>>
		</td>
	</tr>
	<tr>
		<td>
			<nobr>Number of days to keep logged keys:</nobr><br /><nobr>(0 = infinity)</nobr>
		</td>
		<td width="100%">
			<input type="text" name="demo_log_days" size="5" value="<?php echo $admin_config['demo_log_days'] ? $admin_config['demo_log_days'] : '45'?>">
		</td>
	</tr>
	<tr>
		<td>
			<nobr>Block used keys from getting new demo licenses:</nobr>
		</td>
		<td width="100%">
			<input type="checkbox" name="demo_block"<?php echo $admin_config['demo_block'] ? ' checked="checked"': ''?>>
		</td>
	</tr>
	<tr>
		<td>
			<nobr>Number of days to do so:</nobr><br /><nobr>(0 = infinity)</nobr>
		</td>
		<td width="100%">
			<input type="text" name="demo_block_days" size="5" value="<?php echo $admin_config['demo_block_days'] ? $admin_config['demo_block_days'] : '45'?>">
		</td>
	</tr>
	<tr>
		<td colspan="2">
			&nbsp;
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<b>Other Settings:</b>
		</td>
	</tr>
	<tr>
		<td>
			<nobr>Disable automatic version checking:</nobr>
		</td>
		<td>
			<select name="noUpdateCheck">
				<option value="false"<?php echo $admin_config['noUpdateCheck']=='true' ? '' : ' selected="selected"'?>>No</option>
				<option value="true"<?php echo $admin_config['noUpdateCheck']=='true' ? ' selected="selected"' : ''?>>Yes</option>
			</select>
		</td>
	</tr>
</table></td></tr></table>
<input type="submit" value="Guardar configuración" />
</form>
<?
include 'footer.php';
?>