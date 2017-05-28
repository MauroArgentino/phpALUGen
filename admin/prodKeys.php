<?php
include 'header.php';
?>

<?php
$allProds = $phpALUGen_ProductLibrary->getAllProducts();
?>
<b>Crear Nuevo Producto:</b>
<form method="POST" action="addProduct.php">
<table>
	<tr>
		<td>
			Nombre:
		</td>
		<td>
			<input type="text" name="prodName">
		</td>
	</tr>
	<tr>
		<td>
			Versión:
		</td>
		<td>
			<input type="text" name="prodVer">
		</td>
	</tr>
</table>
<input type="submit" value="Crear Claves y Agregar Productos">
</form>

</td></tr></table><br /><table width="95%" class="installForm"><tr><td>

<b>Lista de Productos:</b>
<table cellspacing=0 cellpadding=0 border=0>
	<tr><td colspan="13" bgcolor="black" height="1"></tr>
	<tr>
		<td width="1" bgcolor="black"></td>
		<td>
			Nombre
		</td>
		<td width="1" bgcolor="black"></td>
		<td>
			Versión
		</td>
		<td width="1" bgcolor="black"></td>
		<td>
			VCode
		</td>
		<td width="1" bgcolor="black"></td>
		<td>
			GCode
		</td>
		<td width="1" bgcolor="black"></td>
		<td>
			&nbsp;Ver información&nbsp;
		</td>
		<td width="1" bgcolor="black"></td>
		<td>
			&nbsp;Suprimir&nbsp;
		</td>
		<td width="1" bgcolor="black"></td>
	</tr>
	<tr><td colspan="13" bgcolor="black" height="1"></tr>
	<?php
foreach ($allProds as $curProduct) {
	?>
	<tr>
		<td width="1" bgcolor="black"></td>
		<td>
			<?php echo $curProduct->Name?>
		</td>
		<td width="1" bgcolor="black"></td>
		<td>
			<?php echo $curProduct->Version?>
		</td>
		<td width="1" bgcolor="black"></td>
		<td>
			<?php echo substr($curProduct->VCode,0,20)?>...
		</td>
		<td width="1" bgcolor="black"></td>
		<td>
			<?php echo substr($curProduct->GCode,0,20)?>...
		</td>
		<td width="1" bgcolor="black"></td>
		<td>
			<center><a href="viewInfo.php?name=<?php echo urlencode($curProduct->Name)?>&ver=<?php echo urlencode($curProduct->Version)?>">Ver</a></center>
		</td>
		<td width="1" bgcolor="black"></td>
		<td>
			<center><a href="remove.php?name=<?php echo urlencode($curProduct->Name)?>&ver=<?php echo urlencode($curProduct->Version)?>">Suprimir</a></center>
		</td>
		<td width="1" bgcolor="black"></td>
	</tr>
	<tr><td colspan="13" bgcolor="black" height="1"></tr>
<?php } ?>
</table>
<br />
<b><a href="productsIni.php">Exportar a formato productos.ini</a></b><br />
<b><a href="importIni.php">Importar desde formato productos.ini</a></b>
<?php
include 'footer.php';
?>