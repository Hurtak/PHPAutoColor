<?php 

	include "PHPAutoColor.php";

	// create dummy database data
	$dbData = [];

	$numberOfUsers = 3;
	$numberOfRows = 10;

	for ($i = 0; $i < $numberOfRows; $i++) { 
		$dbData[$i] = [
			"id" => $i + 1,
			"user_id" => mt_rand(1, $numberOfUsers),
			"amount" => mt_rand(10, 100),
			"date" => date("Y-m-d H:i:s", strtotime(mt_rand(-1000000, 0) . " second"))
		];
	}

?>

<style>
	.wrapper {text-align: center;}
	body {
		background: #808080;
		background: radial-gradient(circle closest-corner at center,#858585 0,#474747 120%);
	}
	table {
		color: #fff;
		font-family: "cambria", "arial";
		display: inline-block;
		margin: .5em;
		width: 400px;
	}
	table:first-child {margin-bottom: 1em;}
	th, td {padding: .2em .5em;}
	th {text-align: center;	background-color: rgba(0,0,0,.2);}
	th:first-child {padding-bottom: 1em; padding-top: 1em;}
	span {font-style: italic; font-size: 130%;}
	a {color: #fff;}
</style>

<div class="wrapper">
	<table>
		<tr>
			<th colspan="4"><span>PHPAutoColor</span></th>
		</tr>
		<tr>
			<th>author:</th>
			<td>Petr Hurtak</td>
			<th>licence:</th>
			<td>The MIT License (MIT)</td>
		</tr>
		<tr>
			<th>e-mail:</th>
			<td>petr.hurtak@gmail.com</td>
			<th>readme:</th>
			<td><a href="https://github.com/Hurtak/PHPAutoColor" target="_blank">github.com/Hurtak/PHPAutoColor</a></td>
		</tr>
	</table>

	<br>

	<?php 
		$color = new PHPAutoColor();
		$color->setColorType("rgba");
		$color->setColorPickingMethod("dynamic");
		$color->setLightnessLimit("min", 0.2);
		$color->setLightnessLimit("max", 0.8);
	?>
	<table>
		<tr>
			<th colspan="4">setColorPickingMethod("dynamic");</th>
		</tr>
		<tr>
			<th>id</th>
			<th>user_id</th>
			<th>amount</th>
			<th>date</th>
		</tr>
		<?php foreach ($dbData as $value): ?>
		<tr style="background-color: <?= $color->getColor($value['user_id'], 0.5) ?>">
			<td><?= $value["id"] ?></td>
			<td><?= $value["user_id"] ?></td>
			<td><?= $value["amount"] ?></td>
			<td><?= $value["date"] ?></td>
		</tr>
		<?php endforeach ?>
	</table>

	<?php 
		$color = new PHPAutoColor();
		$color->setColorType("rgba");
		$color->setColorPickingMethod("dynamic-random");
		$color->setLightnessLimit("min", 0.2);
		$color->setLightnessLimit("max", 0.8);
	?>
	<table>
		<tr>
			<th colspan="4">setColorPickingMethod("dynamic-random");</th>
		</tr>
		<tr>
			<th>id</th>
			<th>user_id</th>
			<th>amount</th>
			<th>date</th>
		</tr>
		<?php foreach ($dbData as $value): ?>
		<tr style="background-color: <?= $color->getColor($value['user_id'], 0.5) ?>">
			<td><?= $value["id"] ?></td>
			<td><?= $value["user_id"] ?></td>
			<td><?= $value["amount"] ?></td>
			<td><?= $value["date"] ?></td>
		</tr>
		<?php endforeach ?>
	</table>

	<br>

	<?php 
		$color = new PHPAutoColor();
		$color->setColorType("rgba");
		$color->setColorPickingMethod("static");
		$color->setLightnessLimit("min", 0.2);
		$color->setLightnessLimit("max", 0.8);
	?>
	<table>
		<tr>
			<th colspan="4">setColorPickingMethod("static");</th>
		</tr>
		<tr>
			<th>id</th>
			<th>user_id</th>
			<th>amount</th>
			<th>date</th>
		</tr>
		<?php foreach ($dbData as $value): ?>
		<tr style="background-color: <?= $color->getColor($value['user_id'], 0.5) ?>">
			<td><?= $value["id"] ?></td>
			<td><?= $value["user_id"] ?></td>
			<td><?= $value["amount"] ?></td>
			<td><?= $value["date"] ?></td>
		</tr>
		<?php endforeach ?>
	</table>

	<?php 
		$color = new PHPAutoColor();
		$color->setColorType("rgba");
		$color->setColorPickingMethod("random");
		$color->setLightnessLimit("min", 0.2);
		$color->setLightnessLimit("max", 0.8);
	?>
	<table>
		<tr>
			<th colspan="4">setColorPickingMethod("random");</th>
		</tr>
		<tr>
			<th>id</th>
			<th>user_id</th>
			<th>amount</th>
			<th>date</th>
		</tr>
		<?php foreach ($dbData as $value): ?>
		<tr style="background-color: <?= $color->getColor($value['user_id'], 0.5) ?>">
			<td><?= $value["id"] ?></td>
			<td><?= $value["user_id"] ?></td>
			<td><?= $value["amount"] ?></td>
			<td><?= $value["date"] ?></td>
		</tr>
		<?php endforeach ?>
	</table>
</div>