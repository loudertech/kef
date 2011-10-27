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
 * @subpackage 	Components
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: ReportStyle.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * ReportRawColumn
 *
 * Componente para agregar columnas arbitrarias al formato mismo del reporte
 *
 * @category 	Kumbia
 * @package 	Report
 * @subpackage 	Components
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 */
class ReportRawColumn {

	/**
	 * Opciones del RawColumn
	 *
	 * @var array
	 */
	private $_options = array();

	/**
	 * Establece los parámetros del ReportRawColumn
	 *
	 * @param array $options
	 */
	public function __construct($options=array()){
		$this->_options = $options;
	}

	/**
	 * Devuelve el estilo de la columna
	 *
	 * @return ReportStyle
	 */
	public function getStyle(){
		if(isset($this->_options['style'])){
			return $this->_options['style']->getStyles();
		} else {
			return false;
		}
	}

	/**
	 * Devuelve el valor de la columna
	 *
	 * @return string
	 */
	public function getValue(){
		if(isset($this->_options['format'])){
			return $this->_options['format']->apply($this->_options['value']);
		} else {
			return $this->_options['value'];
		}
	}

	/**
	 * Devuelve el número de columnas que se deben combinar
	 *
	 * @return int
	 */
	public function getSpan(){
		if(isset($this->_options['span'])){
			return $this->_options['span'];
		} else {
			return 1;
		}
	}

}