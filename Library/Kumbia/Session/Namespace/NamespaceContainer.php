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
 * @package 	Session
 * @subpackage 	SessionNamespace
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: NamespaceContainer.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * NamespaceContainer
 *
 * Clase de Almacenamiento para los Namespaces
 *
 * @category 	Kumbia
 * @package 	Session
 * @subpackage 	SessionNamespace
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class NamespaceContainer {

	/**
	 * Indica si el NamespaceContainer es de solo lectura
	 *
	 * @var boolean
	 */
	private $_readOnly = false;

	/**
	 * Evita que se lance la excepcion al escribir en el Container
	 *
	 * @var boolean
	 */
	private $_writeLock = false;

	/**
	 * Obtiene o establece el valor a un atributo del Namespace
	 *
	 * @param string $method
	 * @param array $args
	 */
	public function __call($method, $args=array()){
		if(substr($method, 0, 3)=="set"){
			if($this->_readOnly==true){
				throw new SessionException("El namespace esta bloqueado. No se puede escribir");
			}
			$field = "_".substr($method, 3);
			$this->_writeLock = true;
			$this->$field = $args[0];
			$this->_writeLock = false;
			return;
		}
		if(substr($method, 0, 3)=="get"){
			$field = "_".substr($method, 3);
			return $this->$field;
		}
		throw new SessionException("No existe el metodo '$method' en el Namespace");
	}

	/**
	 * Lanza una excepcion al tratar de obtener un valor
	 * del namespace en forma directa
	 *
	 * @param $property
	 * @param $value
	 * @throws SessionException
	 */
	public function __get($property){
		throw new SessionException('Los valores del Namespace no pueden ser obtenidos directamente');
	}

	/**
	 * Lanza una excepcion al tratar de establecer un valor
	 * del namespace en forma directa
	 *
	 * @param string $property
	 * @param mixed $value
	 * @throws SessionException
	 */
	public function __set($property, $value){
		if($this->_writeLock==false){
			throw new SessionException('Los valores del Namespace no pueden ser establecidos directamente');
		}
		$this->$property = $value;
	}

	/**
	 * Establece si el NamespaceContainer es de solo lectura o no
	 *
	 * @param boolean $readOnly
	 */
	public function setReadOnly($readOnly){
		$this->_readOnly = $readOnly;
	}

	/**
	 * Permite establecer el valor de una propiedad dinamicamente
	 *
	 * @param string $index
	 * @param mixed $value
	 */
	public function setValue($index, $value){
		if($this->_readOnly==true){
			throw new SessionException('El namespace esta bloqueado. No se puede escribir');
		}
		$property = '_'.Utils::camelize($index);
		$this->$index = $value;
	}

	/**
	 * Devuelve el valor de una propiedad dinamicamente
	 *
	 * @param string $index
	 * @return mixed
	 */
	public function getValue($index){
		$property = '_'.Utils::camelize($index);
		return $this->$index;
	}

	/**
	 * Indica si se ha definido una propiedad $index en el namespace
	 *
	 * @param string $index
	 */
	public function hasIndex($index){
		return isset($this->$index);
	}

}
