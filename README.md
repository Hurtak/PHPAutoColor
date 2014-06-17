# PHPAutoColor

##### Simple PHP class for automated coloring with visually distinct colors.

### 1. Use case
* coloring peoples messages in chat by their user name
* coloring list of latest users actions on admin dashboard by user id

<img src="http://i.imgur.com/gWcqw2c.png">

### 2. Features
* fast and lightweight
* lots of customization options
* advance algorithms like <a href="http://en.wikipedia.org/wiki/Color_difference#CIEDE2000">CIEDE2000</a> or <a href="http://alienryderflex.com/hsp.html">HSP</a> color model
* easy debugging

<img src="http://i.imgur.com/GSei33D.png">

### 3. Code example
```php
	<?php

	// data from DB
	$sql = $pdo->prepare("SELECT * FROM actions ORDER BY id DESC LIMIT 10");
	$sql->execute();
	$userActions = $sql->fetchAll();
	
	// PHPAutoColor settings
	$color = new PHPAutoColor();
	$color->setColorType("hex");
	$color->setColorPickingMethod("static");
	$color->setLightnessLimit("min", 0.3);

	?>
	
	<table>
		<tr>
			<td>id</td>
			<td>user_id</td>
			<td>amount</td>
			<td>date</td>
		</tr>

		<?php foreach ($userActions as $action): ?>
		<tr style="background-color: <?= $color->getColor($action['user_id']) ?>">
			<td><?= $action["id"] ?></td>
			<td><?= $action["user_id"] ?></td>
			<td><?= $action["amount"] ?></td>
			<td><?= $action["date"] ?></td>
		</tr>
		<?php endforeach ?>
	
	</table>
```

### 4. Functions

##### 4.1 setColorPickingMethod($colorPickingMethod)

This setting is optional, if you won't call this function default value will be used.

| $colorPickingMethod  | Description |
| -------------------- | ----------- |
| `dynamic` (default)  | Colors are assigned gradually from pregenerated colors list (first color will always be black, second one white...) |
| `dynamic-random`     | Colors are assigned randomly from pregenerated colors list |
| `static`             | Colors are assigned from pregenerated colors list in a way that same color ise always asigned to entered number (100 will always be red no matter if its first or last) |
| `random`             | Colors are assigned randomly |

##### 4.2 setColorType($colorType)

This setting is optional, if you won't call this function default value will be used.

| $colorType      | Description |
| --------------- | ----------- |
| `hex` (default) | Color in hexadecimal format will be returned. If possible, color will be shortened to 3 digit format. (eg.: `#968AE8`, `#FFF`) |
| `rgb`           | Color in rgb format (eg.: `rgb(255,0,0)`) |
| `rgba`          | Color in rgba format (eg.: `rgba(255,0,0,0.5)`) |

##### 4.3 setLightnessLimit($type, $lightness)

This setting is optional, if you won't call this function default value will be used.

| $type | $lightness default | Accepted values | Description |
| ----- | ------------------ | --------------- | ----------- |
| `max` | `1`                | <`0.5`;`1`>     | Limits maximum perceived lightness of returned colors |
| `min` | `0`                | <`0`;`0.5`>     | Limits minimum perceived lightness of returned colors |

setLightnessLimit() can be called twice if you want to set `max` and `min` limit at the same time (difference between `max` and `min` must be bigger or equal to `0.5`)

##### 4.4 setMaximumColors($maximumColors)

This setting is optional, if you won't call this function number of used colors wont be limited.

| $maximumColors accepted values | Description                              |
| ------------------------------ | ---------------------------------------- |
|  <`6`;`32`>                    | Limits the maximum number of used colors |

##### 4.5 getColor($number, $opacity = 1)

| Parameter | Description |
| --------- | ----------- |
| $number   | Number you are basing the coloring around, eg.: user id, user action type |
| $opacity  | Opacity value, only used if color type is set to `rgba` |

### 5. List of pregenerated list of colors

65 visually most distinct colors generated using <a href="http://en.wikipedia.org/wiki/Color_difference#CIEDE2000">CIEDE2000</a> algorithm

<img src="http://i.imgur.com/40Dwl8U.png">


