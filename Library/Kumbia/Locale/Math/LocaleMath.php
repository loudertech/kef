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
 * @category 	Kumbia
 * @package 	Locale
 * @subpackage 	LocaleMath
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */

/**
 * LocaleMath
 *
 * Clase para ejecutar operaciones matemáticas usando la extension bcmath si esta disponible
 *
 * @category 	Kumbia
 * @package 	Locale
 * @subpackage 	LocaleMath
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class LocaleMath {

	/**
	 * Indica si BCMATH esta habilitado
	 *
	 * @var boolean
	 */
	static private $_bcMathEnabled = false;

	/**
	 * Realiza una suma utilizando BCMATH si esta disponible
	 *
	 * @param 	double $value1
	 * @param 	double $value2
	 * @param 	int $scale
	 * @static
	 */
	static public function add($value1, $value2, $scale=0){
		if(self::$_bcMathEnabled==true){
			return bcadd($value1, $value2, $scale);
		} else {
			$result = $value1 + $value2;
			return self::round($result, $scale);
		}
	}

	/**
	 * Realiza una multiplicación utilizando BCMATH si esta disponible
	 *
	 * @param 	string $value1
	 * @param 	string $value2
	 * @param 	int $scale
	 * @return 	string
	 * @static
	 */
	static public function mul($value1, $value2, $scale=0){
		if(self::$_bcMathEnabled==true){
			return bcmul($value1, $value2, $scale);
		} else {
			$result = $value1 * $value2;
			return self::round($result, $scale);
		}
	}

	/**
	 * Realiza una resta utilizando BCMATH si esta disponible
	 *
	 * @param 	string $value1
	 * @param 	string $value2
	 * @param 	int $scale
	 * @return 	string
	 * @static
	 */
	static public function sub($value1, $value2, $scale=0){
		if(self::$_bcMathEnabled==true){
			return bcsub($value1, $value2, $scale);
		} else {
			$result = $value1 - $value2;
			return self::round($result, $scale);
		}
	}

	/**
	 * Realiza una division utilizando BCMATH si esta disponible
	 *
	 * @param 	string $value1
	 * @param 	string $value2
	 * @param 	int $scale
	 * @return 	string
	 * @static
	 */
	static public function div($value1, $value2, $scale=0){
		if(self::$_bcMathEnabled==true){
			return bcdiv($value1, $value2, $scale);
		} else {
			$result = $value1 / $value2;
			return self::round($result, $scale);
		}
	}

	/**
	 * Realiza un modulo de division utilizando BCMATH si esta disponible
	 *
	 * @param 	string $value1
	 * @param 	string $value2
	 * @return 	string
	 * @static
	 */
	static public function mod($value1, $value2){
		if(self::$_bcMathEnabled==true){
			return bcmod($value1, $value2);
		} else {
			$result = $value1 % $value2;
			return $result;
		}
	}

	/**
	 * Realiza una comparacion utilizando BCMATH si esta disponible
	 *
	 * @param string $value1
	 * @param string $value2
	 * @param int $scale
	 * @return int
	 * @static
	 */
	static public function cmp($value1, $value2, $scale=0){
		if(self::$_bcMathEnabled==true){
			return bccomp($value1, $value2, $scale);
		} else {
			if($value1==$value2){
				return 0;
			} else {
				if($value1>$value2){
					return 1;
				} else {
					return -1;
				}
			}
		}
	}

	/**
	 * Redondea un numero a la escala establecida
	 *
	 * @param 	double $value
	 * @param 	int $precision
	 * @return 	double
	 * @static
	 */
	static public function round($value, $precision=0){
		if(self::$_bcMathEnabled==true){
			$roundUp = 0;
			$value = (string) $value;
			$position = strpos($value, '.');
			$scientificNotation = strpos($value, 'E-');
			if($position!==false){
				if($scientificNotation===false){
					$value = substr($value, 0, $position+$precision+2);
					$decimalDigits = strlen(substr($value, $position))-1;
					if($precision!=0){
						if($decimalDigits==$precision+1){
							$lastdigit = substr($value, strlen($value)-1, 1);
							if($lastdigit=='5'){
								$roundUp = '0.'.str_pad($lastdigit, $decimalDigits, '0', STR_PAD_LEFT);
							} else {
								if($lastdigit>5){
									$roundUp = '0.'.str_pad(10-$lastdigit, $decimalDigits, '0', STR_PAD_LEFT);
								}
							}
						}
					}
				} else {
					if(preg_match('/E-([0-9]+)$/', $value, $matches)){
						if($matches[1]>$precision){
							return '0';
						}
					}
				}
			}
            return bcadd($value, $roundUp, $precision);
		} else {
			return round($value, $precision);
		}
	}

	/**
	 * Deshabilita BCMATH
	 *
	 * @static
	 */
	public static function disableBcMath(){
		self::$_bcMathEnabled = false;
	}

	/**
	 * Habilita BCMATH
	 *
	 * @static
	 */
	public static function enableBcMath(){
		#if[compile-time]
		if(extension_loaded('bcmath')==false){
			throw new LocaleException('Debe cargar la extension de PHP llamada php_bcmath');
			return false;
		}
		#endif
		self::$_bcMathEnabled = true;
	}

}
