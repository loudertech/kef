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
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Database.php 87 2009-09-19 19:02:50Z gutierrezandresfelipe $
 */

/**
 * DatabaseSessionAdapter
 *
 * Adaptador de Sesion para Archivos planos de texto serializados
 *
 * @category 	Kumbia
 * @package 	Session
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class DatabaseSessionAdapter implements SessionInterface {

	/**
	 * Nombre del manejador de session interno
	 *
	 * @var string
	 */
	private $_saveHandler = 'user';

	/**
	 * Conexion al gestor relacional
	 *
	 * @var DbBase
	 */
	private static $_connection;

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
		$sql = "SELECT data FROM session_data WHERE session_id = '$id'";
		$row = self::$_connection->fetchOne($sql);
		if($row==false){
			return stripslashes($row['data']);
		} else {
			return "";
		}
	}

	/**
	 * Destruye la sesión
	 *
	 * @param string $id
	 * @return boolean
	 */
	public static function destroy($id){
		return true;
	}

	/**
	 * Garbage Collector de Sesión
	 *
	 * @param int $maxTime
	 * @return boolean
	 */
	public static function garbageCollector($maxTime){
		return true;
	}

	/**
	 * Escribe los datos de sesión
	 *
	 * @static
	 * @param string $id
	 * @param string $data
	 * @return boolean
	 */
	public static function write($id, $data){
		$sql = "SELECT COUNT(*) AS rowcount FROM session_data WHERE session_id = '$id'";
		$row = self::$_connection->fetchOne($sql);
		$data = addslashes($data);
		if($row['rowcount']==0){
			$sql = "INSERT INTO session_data (session_id, data, timelife) VALUES ('$id', '$data', ".time().")";
		} else {
			$sql = "UPDATE session_data SET data = '".$data."', timelife = ".time()." WHERE session_id = '".$id."'";
		}
		return self::$_connection->query($sql);
	}

	/**
	 * Inicializa el Session Handler
	 *
	 * @access public
	 */
	public function initialize(){
		$config = CoreConfig::readAppConfig();
		if(isset($config->application->sessionSavePath)){
			self::$_connection = DbLoader::factoryFromDescriptor($config->application->sessionSavePath);
			session_set_save_handler(
				array('DatabaseSessionAdapter', 'open'),
				array('DatabaseSessionAdapter', 'close'),
				array('DatabaseSessionAdapter', 'read'),
				array('DatabaseSessionAdapter', 'write'),
				array('DatabaseSessionAdapter', 'destroy'),
				array('DatabaseSessionAdapter', 'garbageCollector')
			);
		} else {
			throw new SessionException("El adaptador '".get_class($this)."' requiere que indique el sessionSavePath en el config.ini de la aplicaci&oacute;n");
		}
	}

}

