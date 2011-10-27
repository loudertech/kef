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
 * @package 	Registry
 * @subpackage 	Memory
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: MemoryRegistry.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * MemoryRegistry
 *
 * Permite almacenar valores durante la ejecucion de la aplicacion. Implementa el
 * patron de diseño Registry
 *
 * @category	Kumbia
 * @package		Registry
 * @subpackage	Memory
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @abstract
 */
abstract class MemoryRegistry {

	/**
	 * Variable donde se guarda el registro
	 *
	 * @var array
	 */
	private static $_registry = array();

	/**
	 * Indica si hay un valor establecido en alguna clave
	 *
	 * @param 	string $index
	 * @return 	boolean
	 */
	public static function exists($index){
		if(isset(self::$_registry[$index])){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Establece un valor del registro
	 *
	 * @param	string $index
	 * @param	string $value
	 */
	public static function set($index, $value){
		self::$_registry[$index] = $value;
	}

	/**
	 * Agrega un valor al registro a uno ya establecido
	 *
	 * @param	string $index
	 * @param	string $value
	 */
	public static function append($index, $value){
		if(!isset(self::$_registry[$index])){
			self::$_registry[$index] = array();
		}
		self::$_registry[$index][] = $value;
	}

	/**
	 * Agrega un valor al registro al inicio de uno ya establecido
	 *
	 * @param	string $index
	 * @param	string $value
	 */
	public static function prepend($index, $value){
		if(!isset(self::$_registry[$index])){
			self::$_registry[$index] = array();
		}
		array_unshift(self::$_registry[$index], $value);
	}

	/**
	 * Obtiene un valor del registro
	 *
	 * @param	string $index
	 * @return	mixed
	 */
	public static function get($index){
		if(isset(self::$_registry[$index])){
			return self::$_registry[$index];
		} else {
			return null;
		}
	}

	/**
	 * Resetea un valor del registro
	 *
	 * @param	string $index
	 */
	public static function reset($index){
		unset(self::$_registry[$index]);
	}

}
