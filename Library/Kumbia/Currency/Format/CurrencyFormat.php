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
 * @version		$Id: CurrencyFormat.php 118 2010-02-06 21:57:47Z gutierrezandresfelipe $
 */

/**
 * CurrencyFormat
 *
 * Toma un patrón unicode y lo aplica a una cantidad
 *
 * @category	Kumbia
 * @package		Currency
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class CurrencyFormat {

	/**
	 * Simbolo de la moneda localizada a usar
	 *
	 * @var Currency
	 */
	private $_symbol;

	/**
	 * Cantidad a formatear
	 *
	 * @var double
	 */
	private $_quantity;

	/**
	 * Número de decimales
	 *
	 * @var int
	 */
	private $_decimalPlaces;

	/**
	 * Separador de miles
	 *
	 * @var string
	 */
	private $_thousandsSeparator;

	/**
	 * Separador de decimales
	 *
	 * @var string
	 */
	private $_decimalSeparator;

	/**
	 * Constructor de CurrencyFormat
	 *
	 * @param	array $currency
	 * @param	double $quantity
	 * @param	int $decimalPlaces
	 * @param 	boolean $useSymbol
	 */
	public function __construct($currency, $quantity=null, $decimalPlaces=null, $useSymbol=false){
		if($quantity!==null){
			$this->toCurrency($quantity, $decimalPlaces);
		} else {
			$this->setPattern($currency);
		}
	}

	/**
	 * Establece el patrón
	 *
	 * @param double $currency
	 */
	public function setPattern($currency){
		if(preg_match('/0'.$currency['decimal'].'([0]+)/', $currency['pattern'], $matches)){
			$decimalPlaces = i18n::strlen($matches[1]);
		} else {
			$decimalPlaces = 2;
		}
		$this->_thousandsSeparator = $currency['group'];
		$this->_decimalSeparator = $currency['decimal'];
		if(isset($currency['symbol'])){
			$this->_symbol = $currency['symbol'];
		}
		$this->_decimalPlaces = $decimalPlaces;
	}

	/**
	 * Obtiene la cantidad formateada monetariamente
	 *
	 * @param	double $quantity
	 * @param	int $decimalPlaces
	 * @param 	boolean $useSymbol
	 */
	public function toCurrency($quantity, $decimalPlaces=null, $useSymbol=false){
		if($decimalPlaces==null){
			$decimalPlaces = $this->_decimalPlaces;
		}
		$quantity = LocaleMath::round($quantity, $decimalPlaces);
		$this->_quantity = number_format($quantity, $decimalPlaces, $this->_decimalSeparator, $this->_thousandsSeparator);
		if($useSymbol==true){
			$this->_quantity = $this->_symbol.' '.$this->_quantity;
		}
	}

	/**
	 * Obtiene la cantidad formateada numéricamente
	 *
	 * @param	double $quantity
	 * @param	double $decimalPlaces
	 */
	public function toNumeric($quantity, $decimalPlaces=null){
		if($decimalPlaces==null){
			$decimalPlaces = $this->_decimalPlaces;
		}
		$quantity = LocaleMath::round($quantity, $decimalPlaces);
		$this->_quantity = number_format($quantity, $decimalPlaces, $this->_decimalSeparator, $this->_thousandsSeparator);
	}

	/**
	 * Obtiene la cantidad formateada
	 *
	 * @return string
	 */
	public function getQuantity(){
		return $this->_quantity;
	}

}
