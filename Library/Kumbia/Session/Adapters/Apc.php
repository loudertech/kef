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
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: Eaccelerator.php 88 2009-09-19 19:10:13Z gutierrezandresfelipe $
 */

/**
 * ApcSessionAdapter
 *
 * Adaptador de Sesion para APC
 *
 * @category 	Kumbia
 * @package 	Session
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @link		http://eaccelerator.net/
 */
class ApcSessionAdapter implements SessionInterface {

	/**
	 * Nombre del manejador de session interno
	 *
	 * @var string
	 */
	private $_saveHandler = 'user';

	/**
	 * Devuelve el nombre del manejador de session interno
	 *
	 * @access public
	 * @return string
	 */
	public function getSaveHandler(){
		return $this->_saveHandler;
	}

	/**
	 * Abre la sesion
	 *
	 * @return boolean
	 */
	public static function open($savePath, $sessionName){
		return true;
	}

	/**
	 * Cierra la sesion
	 *
	 * @return boolean
	 */
	public static function close(){
		return true;
	}

	/**
	 * Lee los datos de sesion
	 *
	 * @param	string $id
	 * @return	string
	 */
	public static function read($id){
		return apc_fetch($id);
	}

	/**
	 * Destruye la sesión
	 *
	 * @param	string $id
	 * @return	boolean
	 */
	public static function destroy($id){
		return apc_delete($id);;
	}

	/**
	 * Garbage Collector de Sesión
	 *
	 * @param	int $maxTime
	 * @return	boolean
	 */
	public static function garbageCollector($maxTime){
		return true;
	}

	/**
	 * Escribe los datos de sesión
	 *
	 * @static
	 * @param	string $id
	 * @param	string $data
	 * @return	boolean
	 */
	public static function write($id, $data){
		return apc_store($id, $data);
	}

	/**
	 * Inicializa el Session Handler
	 *
	 * @access public
	 */
	public function initialize(){
		session_set_save_handler(
			array('ApcSessionAdapter', 'open'),
			array('ApcSessionAdapter', 'close'),
			array('ApcSessionAdapter', 'read'),
			array('ApcSessionAdapter', 'write'),
			array('ApcSessionAdapter', 'destroy'),
			array('ApcSessionAdapter', 'garbageCollector')
		);
	}

}
