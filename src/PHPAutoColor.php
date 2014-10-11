<?php 

/**
 * PHPAutoColor - Simple PHP class for automated coloring 
 *                with visually distinct colors
 *
 * @author  Petr Huřťák
 * 
 * @link    github.com/Hurtak/PHPAutoColor
 * @license The MIT License (MIT)
 * 
 * @version 1.2.4
 */

class PHPAutoColor {
	private $lightnessMax = 1;
	private $lightnessMin = 0;

	private $colorPickingMethods = array(
		"static",
		"dynamic",
		"dynamic-random",
		"random"
	);
	private $colorPickingMethod = "static";

	private $colorTypes = array(
		"hex",
		"rgb",
		"rgba"
	);
	private $colorType = "hex";

	private $inputsWithAssignedColors = array();

	private $usedColors = 0;
	private $maximumColors = false;
	private $numberOfColors;

	private $inicializationCompleted = false;

	private $debuggingEnabled = false;
	private $errorsDetected = false;
	private $errors = array();

	// pregenerated colors with CIEDE2000 algorithm 
	// en.wikipedia.org/wiki/Color_difference#CIEDE2000
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
			$this->addError("Computation type value not specified properly, '" . implode("', '", $this->colorPickingMethods) . "' are the only accepted values. Entered value '" . $colorPickingMethod . "'.");
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
			$this->addError("Color type value not specified properly, '" . implode("', '", $this->colorTypes) . "' are the only accepted values. Entered value '" . $colorType . "'.");
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

		$maximumLightnessRange = 0.2; // <0.2;1.0>
		$minimumLightnessRange = 0.8; // <0.0;0.8>
		if ($type !== "max" && $type !== "min") {
			$this->addError("Lightness limitation type (first parameter) not specified properly, only 'max' and 'min' are accepted values. Entered value '" . $type . "'.");
		} elseif (!is_numeric($lightness)) {
			$this->addError("Lightness value (second parameter) not specified properly, only numbers are accepted. Entered value '" . $lightness . "'.");
		} elseif ($type === "max" && ($lightness < $maximumLightnessRange || $lightness > 1)) {
			$this->addError("Maximum lightness value (second parameter) must be in <" . $maximumLightnessRange . ";1> range. Entered value '" . $lightness . "'.");
		} elseif ($type === "min" && ($lightness > $minimumLightnessRange || $lightness < 0)) {
			$this->addError("Minimum lightness value (second parameter) must be in <0;" . $minimumLightnessRange . "> range. Entered value '" . $lightness . "'.");
		} else {
			if ($type === "max") {
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
		$min = 2;

		if (!is_numeric($maximumColors)) {
			$this->addError("Maximum number of colors value not specified properly, only numbers are accepted. Entered value '" . $maximumColors . "'.");
		} elseif ($maximumColors < $min) {
			$this->addError("Maximum number of colors must be bigger or equal to " . $min . ". Entered value '" . $maximumColors . "'.");
		} else {
			$this->maximumColors = $maximumColors;
		}
	}

	/**
	 * Enables debbuging mode. If there are any errors, they will be displayed
	 * in debugging window. 
	 */
	public function enableDebugging() {
		$this->debuggingEnabled = true;
	}

	/**
	 * Assigns color to entered input and returns that color accordingly to
	 * selected setColorPickingMethod() and setColorType()
	 * @param  [variable] [$input]   string or number to which we want to
	 *                               assign color
	 * @param  [number]   [$opacity] opracity used for rgba
	 * @return [string]              visually most distinct color 
	 */
	public function getColor($input, $opacity = 1) {
		if (!is_numeric($opacity)) {
			$this->addError("Opacity value (second parameter) is not a number. Entered value '" . $opacity . "'.");
		} elseif ($opacity > 1 || $opacity < 0) {
			$this->addError("Opacity value (second parameter) must be in <0;1> range. Entered value '" . $opacity . "'.");
		} elseif (is_array($input)) {
			$this->addError("Input (first parameter) must be number or string, array detected.");
		}

		// initialization
		if (!$this->inicializationCompleted) {
			$this->inicializationCompleted = true;

			$minimumDifference = 0.2;
			if ($this->lightnessMax - $this->lightnessMin < $minimumDifference) {
				$this->addError("Difference between maximum and minimum lightness must be at least " . $minimumDifference . ". Entered values: max '" . $this->lightnessMax . "', min  '" . $this->lightnessMin . "', difference " . ($this->lightnessMax - $this->lightnessMin) . ".");
			}

			if ($this->lightnessMin !== 0 || $this->lightnessMax !== 1) {
				$this->CIEDE2000 = $this->removeColorsOutsideLightnessSettings($this->lightnessMin, $this->lightnessMax, $this->CIEDE2000);
			}

			if ($this->maximumColors) {
				$this->CIEDE2000 = $this->limitNumberOfUsedColors($this->maximumColors, $this->CIEDE2000);
			}

			$this->numberOfColors = count($this->CIEDE2000);
		}

		// debugging and error displaying
		static $errorsPrinted = false;
		
		if ($this->debuggingEnabled && !$errorsPrinted && $this->errorsDetected){
			// prevents displaying more than one error message if there are more instances of PHPAutoColor class
			$this->displayErrors($this->errors);
			$errorsPrinted = true;
		}

		if ($this->errorsDetected) {
			// if there are any errors detected, returns empty string
			return "";
		}
		
		$input = (string)$input;

		// assigning color to entered number
		if (isset($this->inputsWithAssignedColors[$input])) {
			$color = $this->inputsWithAssignedColors[$input];
		} else {
			switch ($this->colorPickingMethod) {
				case 'random':
					$color = dechex(mt_rand(0, 0xFFFFFF));
					$color = $this->addLeadingZeros($color);

					$this->inputsWithAssignedColors[$input] = $color;

					break;
				case "dynamic":
					$color = $this->CIEDE2000[$this->usedColors%$this->numberOfColors];

					break;
				case "dynamic-random":
					$color = $this->CIEDE2000[mt_rand(0, $this->numberOfColors - 1)];

					break;
				case "static":
					$inputAdjusted = $input;
					if ((int)$input != $input) {
						$inputAdjusted = abs(crc32($input)); // transfers input to integer
					}

					$color = $this->CIEDE2000[$inputAdjusted%$this->numberOfColors];

					break;
			}
			$this->usedColors++;
			$this->inputsWithAssignedColors[$input] = $color;
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

				if ($this->colorType === "rgba") {
					$opacity = "," . $opacity;
				} else {
					$opacity = "";
				}
				$color = $this->colorType . "(" . implode(",", $colorRGB) . $opacity . ")";
				break;						
		}

		return $color;
	}

	/**
	 * Returns lightness of color in range from 0 (black) to 1 (white). 
	 * Based on the HSP color model
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
	 * @return [array]                 array with removed colors
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
	 * @return [array]                   array with removed colors
	 */
	private function limitNumberOfUsedColors($numberOfColors, $colors) {
		$adjustedColors = array();

		foreach ($colors as $colorsAdded => $color) {
			$adjustedColors[] = $color; 
			if ($colorsAdded === $numberOfColors - 1) {
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
		if (substr($hex, 0, 1) === "#") {
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
		if ($hexNumber[0] === $hexNumber[1] && 
			$hexNumber[2] === $hexNumber[3] && 
			$hexNumber[4] === $hexNumber[5]) {
			return ($hexNumber[0] . $hexNumber[2] . $hexNumber[4]);
		}
		return $hexNumber;
	}

	/**
	 * Adds error message into $errors array along with information about
	 * in what function error occured and what were the paremeters
	 * @param [string] [$message] Error message
	 */
	private function addError($message) {
		$this->errorsDetected = true;

		$backtrace = debug_backtrace();

		$functionName = $backtrace[1]['function'];

		if ($backtrace[1]['args'] === array()) {
			$parameters = "";
		} else {
			$parameters = "<i>'" . implode("'</i>, <i>'", $backtrace[1]['args']) . "'</i>";
		}

		$this->errors[] = "<span>" . $functionName . "(" . $parameters . ")</span>" . $message;
	}	

	/**
	 * Displays errors list
	 * @param [array] [$errors] array with errors, each array value represents
	 *                          one error
	 */
	private function displayErrors($errors) {
		$css = file_get_contents("errors.css", true);
		$errorsList = implode("</p><p>", $errors);

		/* '>"> is here so it can close the <div style="background-color: <?= $color->getColor($number) ?>"> tag and properly display errors div */
		$errorsOutput = 
			"'>\">
			<style>" . $css . "</style>
			<div id='auto-color'>
				<b>PHPAutoColor - ERRORS DETECTED</b>
				<p>" . $errorsList . "</p>
			</div>";

		echo $errorsOutput;
	}
}