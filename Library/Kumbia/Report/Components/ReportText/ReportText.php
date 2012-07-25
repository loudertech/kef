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
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 */

/**
 * ReportText
 *
 * Componente para crear lineas de Texto
 *
 * @category 	Kumbia
 * @package 	Report
 * @subpackage 	Components
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @abstract
 */
class ReportText {

	/**
	 * Texto del componente
	 *
	 * @var string
	 */
	private $_text;

	/**
	 * Propiedades del componente
	 *
	 * @var array
	 */
	private $_attributes = array();

	/**
	 * Contructor de la clase ReportText
	 *
	 * @param string $text
	 * @param array $settings
	 */
	public function __construct($text, $attributes=array()){
		$this->_text = $text;
		$this->_attributes = $attributes;
	}

	/**
	 * Establece los parametros del ReportText
	 *
	 * @param string $text
	 * @param array $settings
	 */
	public function setParameters($text, $attributes=array()){
		$this->_text = $text;
		$this->_attributes = $attributes;
	}

	/**
	 * Cambia los atributos del ReportText
	 *
	 * @param array $attributes
	 */
	public function setAttributes($attributes){
		$this->_attributes = $attributes;
	}

	/**
	 * Devuelve el texto del componente
	 *
	 * @return string
	 */
	public function getText(){
		return $this->_text;
	}

	/**
	 * Devuelve los atributos a aplicar al texto
	 *
	 * @return array
	 */
	public function getAttributes(){
		return $this->_attributes;
	}

}
