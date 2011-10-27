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
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version		$Id: Decimal.php,v 7a54c57f039b 2011/10/19 23:41:19 andres $
 */

/**
 * Decimal
 *
 * Permite que numero doble se convierta en Decimal
 *
 * @category	Kumbia
 * @package		Currency
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */


class Decimal extends Object {

	private $_number = 0;

	public function __construct($number){
		$this->_number = $number;
	}

	public function toLocalized(){
		return Currency::number($this->_number);
	}

	public function toMoney(){
		return Currency::money($this->_number, true);
	}

	public function toWords(){
		return Currency::toWords($this->_number);
	}

	public function __toString(){
		return $this->_number;
	}

}