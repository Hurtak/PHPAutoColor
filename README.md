# PHPAutoColor

##### Simple PHP class for automated coloring with visually distinct colors.

### 1. Use case
* coloring peoples messages in chat by their user name or their user id
* coloring list of latest users actions, on admin dashboard, by user id or username

<img src="http://i.imgur.com/gWcqw2c.png">

### 2. Usage

##### 2.1 Basic setup 
```php
	include "PHPAutoColor.php";
	$color = new PHPAutoColor();
```

##### 2.2 Settings (optional)
* customize PHPAutoColor settings or don't do anything and default settings will be used

##### 2.3 Send user id or username, color will be returned
* integers, numbers with decimal points and strings (case sensitive) are accepted
```php
	$color->getColor($userID); // returns "#000"
```

### 3. Features
* fast and lightweight
* lots of customization options
* advance algorithms like <a href="http://en.wikipedia.org/wiki/Color_difference#CIEDE2000">CIEDE2000</a> or <a href="http://alienryderflex.com/hsp.html">HSP</a> color model
* easy debugging

<img src="http://i.imgur.com/ReCpGGC.png">

### 4. Code example
```php
	<?php

	// data from DB
	$sql = $pdo->prepare("SELECT * FROM actions");
	$sql->execute();
	$userActions = $sql->fetchAll();
	
	// initial setup
	include "PHPAutoColor.php";
	$color = new PHPAutoColor();

	// PHPAutoColor settings (optional)
	$color->setColorType("rgb");
	$color->setColorPickingMethod("dynamic");
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

### 5. Settings

##### 5.1 getColor($input, $opacity = 1)

The $opacity settings is optional, with default value `1`.

| Parameter | Description |
| --------- | ----------- |
| $input    | Input you are basing the coloring around, eg.: user id or username. Both strings and numbers are accepted. |
| $opacity  | Opacity value, only used if color type is set to `rgba` |

* returns string of color in hex format (can be changed, see 5.3)
* on error returns empty string (to display errors list, enable debugging, see 5.6)

##### 5.2 setColorPickingMethod($colorPickingMethod)

This setting is optional, if you won't call this function, default value will be used.

| $colorPickingMethod  | Description |
| -------------------- | ----------- |
| `static` (default)   | Colors are assigned from pregenerated colors list. Same color is always assigned to gived number or string |
| `dynamic`            | Colors are assigned gradually from pregenerated colors list (first color will always be black, second one white...) |
| `dynamic-random`     | Colors are assigned randomly from pregenerated colors list |
| `random`             | Colors are assigned randomly |

##### 5.3 setColorType($colorType)

This setting is optional, if you won't call this function, default value will be used.

| $colorType      | Description |
| --------------- | ----------- |
| `hex` (default) | Color in hexadecimal format will be returned. If possible, color will be shortened to 3 digit format. (eg.: `#968AE8`, `#FFF`) |
| `rgb`           | Color in rgb format (eg.: `rgb(255,255,255)`) |
| `rgba`          | Color in rgba format (eg.: `rgba(255,255,255,0.5)`) |

##### 5.4 setLightnessLimit($type, $lightness)

This setting is optional, if you won't call this function, default value will be used.

| $type | $lightness default | Accepted values | Description |
| ----- | ------------------ | --------------- | ----------- |
| `max` | `1`                | <`0.2`;`1`>     | Limits maximum perceived lightness of returned colors |
| `min` | `0`                | <`0`;`0.8`>     | Limits minimum perceived lightness of returned colors |

setLightnessLimit() can be called twice if you want to set `max` and `min` limit at the same time (difference between `max` and `min` must be bigger or equal to `0.2`).

##### 5.5 setMaximumColors($maximumColors)

This setting is optional, if you won't call this function, number of used colors won't be limited.

| $maximumColors accepted values | Description                              |
| ------------------------------ | ---------------------------------------- |
| bigger or equal to `2`         | Limits the maximum number of used colors |

##### 5.6 enableDebugging()

* debugging is turned off and errors are not displayed by default
* if errors occur, empty string is returned from getColor() method
* calling this method will display detected errors

### 6. List of pregenerated colors

List of 65 visually most distinct colors generated using <a href="http://en.wikipedia.org/wiki/Color_difference#CIEDE2000">CIEDE2000</a> algorithm.
Colors from this list are used if you use `setColorPickingMethod()` with `dynamic`, `dynamic-random` or `static` parameter.

<img src="http://i.imgur.com/40Dwl8U.png">


