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
 * @package 	Report
 * @subpackage 	Formats
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: ReportStyle.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * ReportFormat
 *
 * Formato numérico para columnas de Report
 *
 * @category 	Kumbia
 * @package 	Report
 * @subpackage 	Formats
 * @copyright 	Copyright (c) 2005-2010 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class FormatNumber {

	/**
	 * Opciones del formateador
	 *
	 * @var array
	 */
	private $_options = array();

	/**
	 * Constructor de FormatNumber
	 *
	 * @param array $options
	 */
	public function __construct($options){
		$this->_options = $options;
	}

	/**
	 * Aplica el formato numérico a una columna
	 *
	 * @param	mixed $value
	 * @return	string
	 */
	public function apply($value){
		if(is_object($value)){
			$value = (string) $value;
		}
		if(isset($this->_options['decimals'])){
			return Currency::number($value, $this->_options['decimals']);
		} else {
			return Currency::number($value, 0);
		}
	}

	/**
	 * Devuelve el tipo de dato más cercano que administra el formato
	 *
	 * @return string
	 */
	public function getStdType(){
		return 'number';
	}

}