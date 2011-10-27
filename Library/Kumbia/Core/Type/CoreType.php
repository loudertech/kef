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
 * @package		Core
 * @subpackage	CoreType
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: CoreType.php 101 2009-10-08 03:49:31Z gutierrezandresfelipe $
 */

/**
 * CoreType
 *
 * Permite realizar aserciones sobre tipos de datos
 *
 * @category	Kumbia
 * @package		Core
 * @subpackage	CoreType
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @abstract
 */
abstract class CoreType {

	/**
	 * Realiza una asercion de un valor entero
	 *
	 * @param int $var
	 */
	public static function assertNumeric($var){
		#if[compile-time]
		if(is_int($var)==false){
			throw new CoreException("Se esperaba recibir un valor entero");
		}
		#endif
	}

	/**
	 * Realiza una asercion de un valor booleano
	 *
	 * @access 	public
	 * @param 	bool $var
	 * @static
	 */
	public static function assertBool($var){
		#if[compile-time]
		if(is_bool($var)==false){
			throw new CoreException("Se esperaba recibir un valor booleano");
		}
		#endif
	}

	/**
	 * Realiza una asercion de un valor string
	 *
	 * @access 	public
	 * @param 	string $str
	 * @static
	 */
	public static function assertString($str){
		#if[compile-time]
		if(is_string($str)==false){
			throw new CoreException("Se esperaba recibir un valor string");
		}
		#endif
	}

	/**
	 * Realiza una asercion de un valor array
	 *
	 * @access 	public
	 * @param 	array $var
	 * @static
	 */
	public static function assertArray($var){
		#if[compile-time]
		if(is_array($var)==false){
			throw new CoreException("Se esperaba recibir un valor array");
		}
		#endif
	}

	/**
	 * Realiza una asercion de un valor resource
	 *
	 * @access 	public
	 * @param 	resource $var
	 * @static
	 */
	public static function assertResource($var){
		#if[compile-time]
		if(is_resource($var)==false){
			throw new CoreException("Se esperaba recibir un valor resource");
		}
		#endif
	}

	/**
	 * Realiza una asercion de un objeto
	 *
	 * @access 	public
	 * @param 	object $var
	 * @static
	 */
	public static function assertObject($var){
		#if[compile-time]
		if(is_object($var)==false){
			throw new CoreException("Se esperaba recibir un objeto");
		}
		#endif
	}

}
