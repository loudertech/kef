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
 * @package 	Traslate
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Array.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * ArrayTraslate
 *
 * Traducción de contenido multi-idioma
 *
 * @category 	Kumbia
 * @package 	Traslate
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class ArrayTraslate implements TraslateInterface {

	/**
	 * Datos de Traducción
	 *
	 * @var array
	 */
	private $_traslate;

	/**
	 * Constructor del Adaptador
	 *
	 * @param array $data
	 */
	public function __construct(array $data){
		$this->_traslate = $data;
	}

	/**
	 * Realiza una consulta en los datos de traducción
	 *
	 * @param	string $index
	 * @return	string
	 */
	public function query($index){
		if(isset($this->_traslate[$index])){
			return $this->_traslate[$index];
		} else {
			return $index;
		}
	}

	/**
	 * Indica si está definido un indice de traducción en el diccionario
	 *
	 * @param 	string $index
	 * @return	string
	 */
	public function exists($index){
		return isset($this->_traslate[$index]);
	}

}
