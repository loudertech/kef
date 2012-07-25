<?php

/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category Kumbia
 * @package PdfDocument
 * @copyright Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license New BSD License
 */

/**
 * Colores para utilizar en un documento PDF
 *
 * @category Kumbia
 * @package PdfDocument
 * @copyright Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license New BSD License
 */
class PdfColor {

	/**
	 * Nivel de color Rojo
	 *
	 * @var int
	 */
	private $_red = 0;

	/**
	 * Nivel de color Verde
	 *
	 * @var int
	 */
	private $_green = 0;

	/**
	 * Nivel de color Azul
	 *
	 * @var string
	 */
	private $_blue = 0;

	/**
	 * Color Negro
	 */
	const COLOR_BLACK = '#000000';

	/**
	 * Color Blanco
	 */
	const COLOR_WHITE = '#FFFFFF';

	/**
	 * Color Rojo
	 */
	const COLOR_RED = '#FF0000';

	/**
	 * Color Azul
	 */
	const COLOR_BLUE = '#0000FF';

	/**
	 * Color Verde
	 */
	const COLOR_GREEN = '#00FF00';

	/**
	 * Color Naranja
	 */
	const COLOR_ORANGE = '#FF8C00';

	/**
	 * Color Café
	 *
	 */
	const COLOR_COFFEE = '#B1773A';

	/**
	 * Color Amarillo
	 *
	 */
	const COLOR_YELLOW = '#FAFF00';


	/**
	 * Constructor de PdfColor
	 *
	 * @param int $red
	 * @param int $green
	 * @param int $blue
	 */
	public function __construct($red=0, $green=0, $blue=0){
		$this->_red = $red;
		$this->_green = $green;
		$this->_blue = $blue;
	}

	/**
	 * Obtiene el nivel de rojo
	 *
	 * @return int
	 */
	public function getRed(){
		return $this->_red;
	}

	/**
	 * Obtiene el nivel de verde
	 *
	 * @return int
	 */
	public function getGreen(){
		return $this->_green;
	}

	/**
	 * Obtiene el nivel de Azul
	 *
	 * @return int
	 */
	public function getBlue(){
		return $this->_blue;
	}

	/**
	 * Obtiene el color inverso
	 *
	 * @return PdfColor
	 */
	public function getInverse(){
		return new self(1-$this->getRed(), 1-$this->getGreen(), 1-$this->getBlue());
	}

	/**
	 * Crea un color apartir de su nombre
	 *
	 * @param name $colorName
	 */
	public static function fromName($colorName){
		$red = hexdec(substr($colorName, 1, 2))/255;
		$green = hexdec(substr($colorName, 3, 2))/255;
		$blue = hexdec(substr($colorName, 5, 2))/255;
		return new self($red, $green, $blue);
	}

	/**
	 * Crea un color desde una escala de grises
	 *
	 * @param float $scale
	 */
	public static function fromGrayScale($scale){
		if($scale<0){
			$scale = 0;
		} else {
			if($scale>1){
				$scale = 1;
			}
		}
		return new self($scale, $scale, $scale);
	}

	/**
	 * Crea un color desde una escala de rojo
	 *
	 * @param float $scale
	 */
	public static function fromRedScale($scale){
		if($scale<0){
			$scale = 0;
		} else {
			if($scale>1){
				$scale = 1;
			}
		}
		return new self($scale, 0, 0);
	}

	/**
	 * Crea un color desde una escala de azul
	 *
	 * @param float $scale
	 */
	public static function fromBlueScale($scale){
		if($scale<0){
			$scale = 0;
		} else {
			if($scale>1){
				$scale = 1;
			}
		}
		return new self(0, 0, $scale);
	}

	/**
	 * Obtiene el color intermedio entre 2 colores
	 *
	 * @param PdfColor $color1
	 * @param PdfColor $color2
	 * @return PdfColor
	 */
	public static function intermediate(PdfColor $color1, PdfColor $color2){
		$red = $color1->getRed()+$color2->getRed()/2;
		$green = $color1->getGreen()+$color2->getGreen()/2;
		$blue = $color1->getBlue()+$color2->getBlue()/2;
		return new self($red, $green, $blue);
	}

	/**
	 * Crea un color desde una escala de azul
	 *
	 * @param float $scale
	 */
	public static function fromYellowScale($scale){
		if($scale<0){
			$scale = 0;
		} else {
			if($scale>1){
				$scale = 1;
			}
		}
		return new self(1, 1, 1-$scale);
	}

	/**
	 * Crea un color desde una escala de green
	 *
	 * @param float $scale
	 */
	public static function fromGreenScale($scale){
		if($scale<0){
			$scale = 0;
		} else {
			if($scale>1){
				$scale = 1;
			}
		}
		return new self(0, $scale, 0);
	}


}
