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
 * @category	Kumbia
 * @package		Currency
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Currency.php 118 2010-02-06 21:57:47Z gutierrezandresfelipe $
 */

/**
 * Currency
 *
 * El objetivo de este componente es proporcionar facilidades al desarrollador
 * para trabajar con cantidades numéricas relacionadas con dinero y monedas,
 * su representación de acuerdo a la localización activa y la generación
 * de montos en letras en diferentes idiomas.
 *
 * @category	Kumbia
 * @package		Currency
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class Currency {

	/**
	 * Formateador de cantidades
	 *
	 * @var CurrencyFormat
	 */
	private static $_currencyFormater;

	/**
	 * Formateador cuando no se usa estaticamente
	 *
	 * @var CurrencyFormat
	 */
	private $_currencyFormat;

	/**
	 * Datos de la moneda usada actualmente
	 *
	 * @var array
	 */
	private $_currencyData;

	/**
	 * Localizacion
	 *
	 * @var Locale
	 */
	private $_locale;

	/**
	 * Constructor de Currency
	 *
	 * @param Locale $locale
	 */
	public function __construct(Locale $locale=null){
		if($locale==null){
			$this->_locale = Locale::getApplication();
		} else {
			$this->_locale = $locale;
		}
	}

	/**
	 * Establece/Cambia la localizacion
	 *
	 * @access 	public
	 * @param	Locale $locale
	 */
	public function setLocale(Locale $locale){
		$this->_locale = $locale;
		$pattern = $this->_locale->getCurrencyFormat();
		$formater = $this->_getFormater();
		$formater->setPattern($pattern);
		$this->_currencyData = null;
	}

	/**
	 * Crea/Obtiene el formateador de monedas
	 *
	 * @access	public
	 * @return	CurrencyFormat
	 */
	private function _getFormater(){
		if($this->_currencyFormat==null){
			$pattern = $this->_locale->getCurrencyFormat();
			$this->_currencyFormat = new CurrencyFormat($pattern);
		}
		return $this->_currencyFormat;
	}

	/**
	 * Obtiene una cantidad formateada de acuerdo a la localizacion interna
	 *
	 * @param 	double $quantity
	 * @param 	string $format
	 * @param 	string $codeISO
	 * @return 	string
	 */
	public function getMoney($quantity, $format='', $codeISO=''){
		$formater = $this->_getFormater();
		$formater->toCurrency($quantity);
		if($format==null){
			return $formater->getQuantity();
		} else {
			$quantity = $formater->getQuantity();
			return $this->_applyFormat($format, $quantity, $codeISO);
		}
	}

	/**
	 * Aplica el formato a la salida
	 *
	 * @param	string $format
	 * @param	int $quantity
	 * @param	string $codeISO
	 * @return	string
	 */
	private function _applyFormat($format, $quantity, $codeISO){
		if(strpos($format, '%symbol%')!==false){
			$format = str_replace('%symbol%', $this->getMoneySymbol($codeISO), $format);
		}
		if(strpos($format, '%displayName%')!==false){
			$format = str_replace('%displayName%', $this->getMoneyDisplayName($codeISO), $format);
		}
		if(strpos($format, '%name%')!==false){
			$format = str_replace('%name%', $this->getMoneyISOCode($codeISO), $format);
		}
		return str_replace('%quantity%', $quantity, $format);
	}

	/**
	 * Obtiene el simbolo de la moneda utilizada
	 *
	 * @param 	string $codeISO
	 * @return 	string
	 */
	public function getMoneySymbol($codeISO=''){
		$currency = $this->getCurrency($codeISO);
		return $currency['symbol'];
	}

	/**
	 * Obtiene el nombre de la moneda utilizada
	 *
	 * @param 	string $codeISO
	 * @param	string $type
	 * @return	string
	 */
	public function getMoneyDisplayName($codeISO='', $type=''){
		$currency = $this->getCurrency($codeISO, $type);
		return $currency['displayName'];
	}

	/**
	 * Obtiene el codigo ISO de la moneda utilizada
	 *
	 * @param	string $codeISO
	 * @return	string
	 */
	public function getMoneyISOCode($codeISO=''){
		$currency = $this->getCurrency($codeISO);
		return $currency['name'];
	}

	/**
	 * Obtiene el simbolo y nombre de la moneda especificada
	 *
	 * @param	string $codeISO
	 * @param	string $displayType
	 * @return	string
	 */
	public function getCurrency($codeISO='', $displayType=''){
		if($codeISO){
			return $this->_locale->getCurrency($codeISO, $displayType);
		} else {
			if(!$this->_currencyData){
				$this->_currencyData = $this->_locale->getCurrency(null, $displayType);
			}
			return $this->_currencyData;
		}
	}

	/**
	 * Devuelve una cantidad monetaria formateada
	 *
	 * @access 	public
	 * @param	double	$quantity
	 * @param 	boolean	$useSymbol
	 * @return	string
	 */
	public static function money($quantity, $useSymbol=false){
		if(self::$_currencyFormater==null){
			$locale = Locale::getApplication();
			$pattern = $locale->getCurrencyFormat();
			self::$_currencyFormater = new CurrencyFormat($pattern, null, null, $useSymbol);
		}
		self::$_currencyFormater->toCurrency($quantity, null, $useSymbol);
		return self::$_currencyFormater->getQuantity();
	}

	/**
	 * Devuelve una cantidad numerica formateada
	 *
	 * @access 	public
	 * @param 	string $quantity
	 * @param 	int $decimalPlaces
	 * @static
	 */
	public static function number($quantity, $decimalPlaces=null){
		if(self::$_currencyFormater==null){
			$locale = Locale::getApplication();
			$pattern = $locale->getNumericFormat();
			self::$_currencyFormater = new CurrencyFormat($pattern, null, $decimalPlaces);
		}
		self::$_currencyFormater->toNumeric($quantity, $decimalPlaces);
		return self::$_currencyFormater->getQuantity();
	}

	/**
	 * Resetea el formateador interno cuando cambia la localización
	 *
	 * @access public
	 * @static
	 */
	public static function resetFormater(){
		self::$_currencyFormater = null;
	}

	/**
	 * Obtiene una cantidad en letras
	 *
	 * @param double $quantity
	 */
	public static function toWords($quantity){
		$currency = new Currency();
		if($quantity==1){
			$displayType = 'one';
		} else {
			$displayType = 'other';
		}
		return Linguistics::getNumberToWords($quantity, $currency->getMoneyDisplayName(null, $displayType), 'CENTAVOS');
	}

	/**
	 * Obtiene una cantidad en letras
	 *
	 * @param double $quantity
	 */
	public function getMoneyAsText($quantity){
		if($quantity==1){
			$displayType = 'one';
		} else {
			$displayType = 'other';
		}
		return Linguistics::getNumberToWords($quantity, $this->getMoneyDisplayName(null, $displayType), 'CENTAVOS');
	}

}
