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
 * @package		Debug
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: DebugRemote.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * DebugRemote
 *
 * Clase que facilita el debug de aplicaciones en forma Remota
 *
 * @category	Kumbia
 * @package		Debug
 * @subpackage	Remote
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @abstract
 */
class RemoteDebug {

	/**
	 * Puntero de Memoria
	 *
	 * @var int
	 */
	private static $_pointer;

	/**
	 * Agrega un valor al Debug remoto
	 *
	 * @param mixed $value
	 */
	public static function add($value){
		#$shm_id = shmop_open($shm_key, "c", 0644, 100);
		echo ftok(__FILE__, 't');
	}

}
