<?php
include 'header.php';

// Retrieve the product

$thisProd = $phpALUGen_ProductLibrary->retrieveProduct($_GET['name'], $_GET['ver']);

?><b>Información del Producto:</b><br />
<br />
<table>
	<tr>
		<td>Nombre</td>
		<td><?=$thisProd->Name?></td>
	</tr>
	<tr>
		<td>Versión</td>
		<td><?=$thisProd->Version?></td>
	</tr>
	<tr>
		<td>VCode</td>
		<td><textarea cols=110 readonly="readonly"><?php echo $thisProd->VCode?></textarea></td>
	</tr>
	<tr>
		<td>GCode</td>
		<td><textarea cols=110 readonly="readonly"><?php echo $thisProd->GCode?></textarea></td>
	</tr>
</table>
<b><a href="prodKeys.php">Volver a Generador de Código de Producto</a></b>
<?php
include 'footer.php';
?>