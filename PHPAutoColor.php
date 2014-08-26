<?php 

/*

	PHPAutoColor

	author: Petr Huřťák
	e-mail: petr.hurtak@gmail.com
	licence: The MIT License (MIT)

	readme: https://github.com/Hurtak/PHPAutoColor

 */

class PHPAutoColor {
	private $lightnessMax = 1;
	private $lightnessMin = 0;
	
	private $colorPickingMethod = "dynamic";
	private $colorPickingMethods = array(
		"random", "dynamic", "dynamic-random", "static"
		);

	private $colorType = "hex";
	private $colorTypes = array("hex", "rgb", "rgba");

	private $usedNumbersAndColors = array();
	private $error = array();

	private $usedColors = 0;
	private $maximumColors = 0;
	private $numberOfColors;

	private $inicializationCompleted = false;

	// pregenerated colors with CIEDE2000 algorithm 
	// http://en.wikipedia.org/wiki/Color_difference#CIEDE2000
	private $CIEDE2000 = array(
		"000000", "FFFFFF", "00FF00", "0000FF", "FF0000", "01FFFE", "FFA6FE",
		"FFDB66", "006401", "010067", "95003A", "007DB5", "FF00F6", "FFEEE8",
		"774D00", "90FB92", "0076FF", "D5FF00", "FF937E", "6A826C", "FF029D",
		"FE8900", "7A4782", "7E2DD2", "85A900", "FF0056", "A42400", "00AE7E",
		"683D3B", "BDC6FF", "263400", "BDD393", "00B917", "9E008E", "001544",
		"C28C9F", "FF74A3", "01D0FF", "004754", "E56FFE", "788231", "0E4CA1",
		"91D0CB", "BE9970", "968AE8", "BB8800", "43002C", "DEFF74", "00FFC6",
		"FFE502", "620E00", "008F9C", "98FF52", "7544B1", "B500FF", "00FF78",
		"FF6E41", "005F39", "6B6882", "5FAD4E", "A75740", "A5FFD2", "FFB167",
		"009BFF", "E85EBE"
		);

	/**
	 * Sets method which will be used to pick colors when getColor() is called
	 * @param [string] [$colorPickingMethod] method choosen to pick colors
	 */
	public function setColorPickingMethod($colorPickingMethod) {
		$colorPickingMethod = strtolower(trim($colorPickingMethod));
		if (in_array($colorPickingMethod, $this->colorPickingMethods)) {
			$this->colorPickingMethod = $colorPickingMethod;
		} else {
			$this->error[] = "Computation type value not specified properly, '" . implode("', '", $this->colorPickingMethods) . "' are the only accepted values. Entered value '" . $colorPickingMethod . "'.";
		}
	}	

	/**
	 * Sets color type which should getColor() function return
	 * @param [string] [$colorType] "rgb", "rgba" or "hex" (default)
	 */
	public function setColorType($colorType) {
		$colorType = strtolower(trim($colorType));

		if (in_array($colorType, $this->colorTypes)) {
			$this->colorType = $colorType;
		} else {
			$this->error[] = "Color type value not specified properly, '" . implode("', '", $this->colorTypes) . "'' are the only accepted values. Entered value '" . $colorType . "'.";
		}
	}

	/**
	 * Sets either the maximum or the minimum lightness of colors which should 
	 * getColor() function return. Deletes colors which does not meet the
	 * $lightness criteria from CIEDE2000 colors array.
	 * @param [string] [$type]      type of limitation, either "max" which 
	 *                              limits maximum lightness or "min" which sets
	 *                              minimum lightness
	 * @param [float]  [$lightness] maximum or minimum lightness of color in 
	 *                              range from 0 (black) to 1 (white)
	 */
	public function setLightnessLimit($type, $lightness) {
		$type = strtolower(trim($type));

		if ($type != "max" && $type != "min") {
			$this->error[] = "Lightness limitation type not specified properly, only 'max' and 'min' are accepted values. Entered value '" . $type . "'.";
		} elseif (!is_numeric($lightness)) {
			$this->error[] = "Lightness value not specified properly, only numbers are accepted. Entered value '" . $lightness . "'.";
		} elseif ($type == "max" && ($lightness < 0.5 || $lightness > 1)) {
			$this->error[] = "Maximum lightness value must be in <0.5;1> range. Entered value '" . $lightness . "'.";
		} elseif ($type == "min" && ($lightness > 0.5 || $lightness < 0)) {
			$this->error[] = "Minimum lightness value must be in <0;0.5> range. Entered value '" . $lightness . "'.";
		} else {
			if ($type == "max") {
				$this->lightnessMax = $lightness;
			} else {
				$this->lightnessMin = $lightness;
			}
		}
	}

	/**
	 * Limits the maximum number of used colors, when we run ot of colors we
	 * start reusing previpously generated ones.
	 * @param [int] [$maximumColors] maximum number of colors 
	 */
	public function setMaximumColors($maximumColors) {
		$min = 6;
		$max = 32;
		if (!is_numeric($maximumColors)) {
			$this->error[] = "Maximum number of colors value not specified properly, only numbers are accepted. Entered value '" . $maximumColors . "'.";
		} elseif ($maximumColors > $max || $maximumColors < $min) {
			$this->error[] = "Maximum number of colors must be in <$min;$max> range. Entered value '" . $maximumColors . "'.";
		} else {
			$this->maximumColors = $maximumColors;
		}
	}

	/**
	 * Assigns color to entered number and returns that color accordingly to
	 * selected setColorPickingMethod() and setColorType()
	 * @param  [number] [$number]  number to which we want to assign color
	 * @param  [number] [$opacity] opracity used for rgba
	 * @return [string]            visually most distinct color 
	 */
	public function getColor($number, $opacity = 1) {
		if (!is_numeric($opacity)) {
			$this->error[] = "Opacity value is not a number. Entered value '" . $opacity . "'.";
		} elseif ($opacity > 1 || $opacity < 0) {
			$this->error[] = "Opacity must be in <0;1> range. Entered value '" . $opacity . "'.";
		} elseif (!is_numeric($number)) {
			$this->error[] = "Number is not specified properly. Entered value '" . $number . "'.";
		}

		$number = (string)$number;

		// initialization
		if (!$this->inicializationCompleted) {
			$this->inicializationCompleted = true;

			if ($this->lightnessMax - $this->lightnessMin < 0.5) {
				$this->error[] = "Difference between maximum and minimum lightness must be at least 0.5. Entered values: max '" . $this->lightnessMax . "', min  '" . $this->lightnessMin . "', difference " . ($this->lightnessMax - $this->lightnessMin) . ".";
			}

			if ($this->lightnessMin != 0 || $this->lightnessMax != 1) {
				$this->CIEDE2000 = $this->removeColorsOutsideLightnessSettings($this->lightnessMin, $this->lightnessMax, $this->CIEDE2000);
			}

			if ($this->maximumColors > 0) {
				$this->CIEDE2000 = $this->limitNumberOfUsedColors($this->maximumColors, $this->CIEDE2000);
			}

			$this->numberOfColors = count($this->CIEDE2000);
		}
		
		// assigning color to entered number
		if (isset($this->usedNumbersAndColors[$number])) {
			$color = $this->usedNumbersAndColors[$number];
		} else {
			switch ($this->colorPickingMethod) {
				case 'random':
					$color = dechex(mt_rand(0, 0xFFFFFF));
					$color = $this->addLeadingZeros($color);
					$this->usedNumbersAndColors[$number] = $color;
					break;
				case "dynamic":
					$color = $this->CIEDE2000[$this->usedColors%$this->numberOfColors];
					break;
				case "dynamic-random":
					$color = $this->CIEDE2000[mt_rand(0, $this->numberOfColors - 1)];
					break;
				case "static":
					// removes decimal point from $number so it becomes whole number
					$numberAdjusted = preg_replace("~(\d*)(\.)(\d*)~", "\\1\\3", $number);

					$color = $this->CIEDE2000[$numberAdjusted%$this->numberOfColors];
					break;
			}
			$this->usedColors++;
			$this->usedNumbersAndColors[$number] = $color;
		}

		// transforming color to desired color type
		switch ($this->colorType) {
			case 'hex':
				$color = "#" . $this->shortenHex($color);
				break;
			case 'rgb':
			case 'rgba':
				$colorRGB = array();
				for ($i = 0; $i < 3; $i++) { 
					$colorRGB[] = hexdec(substr($color, $i * 2, 2));
				}

				if ($this->colorType == "rgba") {
					$opacity = "," . $opacity;
				} else {
					$opacity = "";
				}
				$color = $this->colorType . "(" . implode(",", $colorRGB) . $opacity . ")";
				break;						
		}

		$this->error = $this->handleErrors($this->error);

		return $color;
	}

	/**
	 * Returns lightness of color in range from 0 (black) to 1 (white). Based on
	 * the HSP color model
	 * @link http://alienryderflex.com/hsp.html
	 * @param  [string] [$color] hex color (format type "ffffff")
	 * @return [float]           lightness of color
	 */
	private function getPerceivedLightness($color) {
		$color = $this->hextorgb($color);

		return sqrt(
			$color[0] * $color[0] * .299 +  
			$color[1] * $color[1] * .587 + 
			$color[2] * $color[2] * .114
		) / 255;
	}

	/**
	 * When maximum or minimum lightness is set, we remove colors which does not
	 * meet lightness criteria
	 * @param  [int]   [$lightnessMax] lightness max in <0;1> range
	 * @param  [int]   [$lightnessMin] lightness min in <0;1> range
	 * @param  [array] [$colors]       array with colors
	 * @return [type]                  array with removed colors
	 */
	private function removeColorsOutsideLightnessSettings($lightnessMin, $lightnessMax, $colors) {
		$adjustedColors = array();
		foreach ($colors as $color) {
			$colorLightness = $this->getPerceivedLightness($color); 
			if ($colorLightness <= $lightnessMax && $colorLightness >= $lightnessMin) {
				$adjustedColors[] = $color; 
			}
		}

		return $adjustedColors;
	}

	/**
	 * When maximum number of colors is set, we remove the ones we will not be using
	 * @param  [int]   [$numberOfColors] maximum number of colors in array
	 * @param  [array] [$colors]         array with colors
	 * @return [type]                    array with removed colors
	 */
	private function limitNumberOfUsedColors($numberOfColors, $colors) {
		$adjustedColors = array();
		foreach ($colors as $colorsAdded => $color) {
			$adjustedColors[] = $color; 
			if ($colorsAdded == $numberOfColors - 1) {
				break;
			}
		}

		return $adjustedColors;
	}	

	/**
	 * Converts color from hexadecimal format to RGB format  
	 * @param  [string] [$hex] hex color in six character format, leading hash 
	 *                         mark is optional
	 * @return [array]         array with int values of RGB format (0 => red,
	 *                         1 => green, 2=> blue)
	 */
	private function hexToRGB($hex) {
		if (substr($hex, 0, 1) == "#") {
			$hex = substr($hex, 1, strlen($hex) - 1);
		}

		$rgb = array();
		for ($i = 0; $i < 3; $i++) { 
			$rgb[] = hexdec(substr($hex, $i * 2, 2));
		}

		return $rgb;
	}

	/**
	 * Adds leading zeroes before inputed string until it has 6 characters
	 * @param  [string] [$hexNumber] hexadecimal number
	 * @return [string] [$hexNumber] hexadecimal number in 6 characters format
	 */
	private function addLeadingZeros($hexNumber) {
		while (strlen($hexNumber) < 6) {
			$hexNumber = "0" . $hexNumber;
		}

		return $hexNumber;
	}

	/**
	 * If possible, shortens hexadecimal color from 6 characters to 3 characters
	 * @param  [string] [$hexNumber] hexadecimal color
	 * @return [string]              original hex color or shortened hex color
	 */
	private function shortenHex($hexNumber) {
		if ($hexNumber[0] == $hexNumber[1] && 
			$hexNumber[2] == $hexNumber[3] && 
			$hexNumber[4] == $hexNumber[5]) {
			return ($hexNumber[0] . $hexNumber[2] . $hexNumber[4]);
		}
		return $hexNumber;
	}

	/**
	 * Prints all errors if there are any
	 * @param  [array]  [$errors] array with errors, each array value represents one error
	 * @return [string]           returns empty array so we can clear error list
	 */
	private function handleErrors($errors) {
		static $errorsPrinted = false;

		if (!$errorsPrinted && count($errors) > 0) {
			$errorsPrinted = true;

			$errorsList = "<li>" . implode("</li><li>", $errors) . "</li>";

			/* '>"> is here so it can close the <div style="background-color: <?= $color->getColor($number) ?>"> tag and properly display error div */
			$errorsOutput = "
				'>\">
				<style>
					.autoColorError {
						background-color: rgba(196,0,0,0.92); position: fixed;
						left: 0; top: 0; right: 0; bottom: 0; margin: auto;
						width: 400px; height: 250px; overflow: auto; z-index: 999;
						padding: 1em; outline: 5px solid rgba(255,255,255,0.92)
					}
					.autoColorError li, .autoColorError b {
						color: #fff; font-size: 16px; font-family: cambria, arial
					}
					.autoColorError b {
						font-size: 20px;
					}
				</style>
				<div class='autoColorError'> 
					<b>
						PHPAutoColor - ERRORS DETECTED
					</b>
					<ul>
						$errorsList
					</ul>
				</div>";

			echo $errorsOutput;
		}
	
		return array();
	}
}