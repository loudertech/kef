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
 * @subpackage 	SessionRegistry
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */

/**
 * SessionRegistry
 *
 * Almacena variables en sesion separadas por instancia y aplicacion
 *
 * @category 	Kumbia
 * @package 	Registry
 * @subpackage 	SessionRegistry
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @abstract
 */
abstract class SessionRegistry {

	/**
	 * Establece un valor del registro
	 *
	 * @param string $index
	 * @param string $value
	 */
	public static function set($index, $value){
		$instanceName = Core::getInstanceName();
		$activeApp = Router::getApplication();
		$_SESSION[$index][$instanceName][$activeApp] = $value;
	}

	/**
	 * Establece un valor del registro
	 *
	 * @param string $index
	 * @param string $value
	 */
	public static function get($index){
		$instanceName = Core::getInstanceName();
		$activeApp = Router::getApplication();
		if(isset($_SESSION[$index][$instanceName][$activeApp])){
		 	return $_SESSION[$index][$instanceName][$activeApp];
		} else {
			return array();
		}
	}

}
