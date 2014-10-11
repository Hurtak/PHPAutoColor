<!doctype html>
<meta charset="utf-8">
<link rel="stylesheet" href="demo.css">
<title>PHPAutoColor examples</title>

<?php 

	include "../src/PHPAutoColor.php";

	// create dummy database data
	$dbData = array();

	$numberOfUsers = 3;
	$numberOfRows = 10;

	for ($i = 0; $i < $numberOfRows; $i++) { 
		$dbData[$i] = array(
			"id" => $i + 1,
			"user_id" => mt_rand(1, $numberOfUsers),
			"amount" => mt_rand(10, 100),
			"date" => date("Y-m-d H:i:s", strtotime(mt_rand(-1000000, 0) . " second"))
		);
	}

?>
<div class="wrapper">
	<table>
		<tr>
			<th colspan="4"><span>PHPAutoColor</span></th>
		</tr>
		<tr>
			<th>author:</th>
			<td>Petr Huřťák</td>
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
		$color->setColorPickingMethod("static");
		$color->setLightnessLimit("min", 0.2);
		$color->setLightnessLimit("max", 0.8);
		// $color->enableDebugging();
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
		$color->setColorPickingMethod("dynamic");
		$color->setLightnessLimit("min", 0.2);
		$color->setLightnessLimit("max", 0.8);
		// $color->enableDebugging();
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

	<br>

	<?php 
		$color = new PHPAutoColor();
		$color->setColorType("rgba");
		$color->setColorPickingMethod("dynamic-random");
		$color->setLightnessLimit("min", 0.2);
		$color->setLightnessLimit("max", 0.8);
		// $color->enableDebugging();
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

	<?php 
		$color = new PHPAutoColor();
		$color->setColorType("rgba");
		$color->setColorPickingMethod("random");
		$color->setLightnessLimit("min", 0.2);
		$color->setLightnessLimit("max", 0.8);
		// $color->enableDebugging();
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