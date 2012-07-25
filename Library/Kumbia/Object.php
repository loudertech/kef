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
 * @package 	Kumbia
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright  	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Object.php 49 2009-05-09 04:37:04Z gutierrezandresfelipe $
 */

/**
 * Object
 *
 * La mayor parte de las clases en Kumbia Enterprise Framework poseen una
 * clase superior llamada Object. Esta clase implementa el patrón
 * Layer Supertype el cual permite implementar métodos que no puedan
 * ser duplicados a lo largo de toda la implementación de componentes
 * en el Framework.
 *
 * @category 	Kumbia
 * @package 	Kumbia
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @abstract
 */
abstract class Object {

	/**
	 * Devuelve el nombre de la clase actual
	 *
	 * @return string
	 */
	public function getClassName(){
		return get_class($this);
	}

	/**
	 * Inspecciona el valor interno de un objeto
	 *
	 * @return string
	 */
	public function inspect(){
		$inspect = array();
		foreach($this as $key => $value){
			if(is_object($value)){
				if(method_exists($value, '__toString')){
					$inspect[] = $key.'= '.$value;
				} else {
					$inspect[] = $key.'= '.get_class($value);
				}
			} else {
				$inspect[] = $key.'= '.$value;
			}
		}
		return join(',', $inspect);
	}

	/**
	 * Lanza una excepción cuando el método no está definido en el objeto
	 *
	 * @param	string $method
	 * @param	array $arguments
	 */
	public function __call($method, $arguments=array()){
		throw new CoreException('Llamado a método no definido "'.$method.'" en la clase "'.get_class($this)."'");
	}

	/**
	 * Implementacion del método toString
	 *
	 */
	public function __toString(){
		$parentClass = get_parent_class($this);
		if($parentClass){
			return '<Object '.get_class($this).'#'.$parentClass.'>';
		} else {
			return '<Object '.get_class($this).'>';
		}
	}

}
