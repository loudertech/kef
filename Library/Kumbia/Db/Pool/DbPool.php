<?php

/**
 * Kumbia Enteprise Framework
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
 * @package 	Db
 * @subpackage	Loader
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: DbLoader.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * DbPool
 *
 * This class allows reuse of physical connections and reduced overhead for your application.
 * Connection pooling functionality minimizes expensive operations in the creation and closing of sessions.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	Loader
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 */
abstract class DbPool {

	/**
	 * Singleton de la conexión a la base de datos (no reciclable)
	 *
	 * @var resource
	 */
	static private $_persistentConnection = null;

	/**
	 * Descriptor de la BD por defecto
	 *
	 * @var stdClass
	 */
	static private $_databaseDescriptor = null;

	/**
	 * Obtiene el descriptor de la base de datos del entorno activo
	 *
	 * @return stdClass
	 */
	private static function _getDatabaseDescriptor(){
		if(self::$_databaseDescriptor==null){
			$config = CoreConfig::readEnviroment();
			#if[compile-time]
			if(isset($config->database)==false){
				throw new DbException('No se ha definido los parámetros de conexión de la base de datos en enviroment.ini', 0, true, $this);
			}
			#endif
			if(is_object($config->database)){
				self::$_databaseDescriptor = $config->database;
			} else {
				$description = $config->database;
				$databases = CoreConfig::readFile('databases');
				if(isset($databases->$description)){
					self::$_databaseDescriptor = $databases->$description;
				} else {
					throw new DbException('Los parámetros de conexión de la base de datos no son válidos', 0, true, $this);
				}
			}
		}
		return self::$_databaseDescriptor;
	}

	/**
	 * Crea/Obtiene una conexión del Pool de Conexiones
	 *
	 * @param	boolean $newConnection
	 * @param	boolean $renovate
	 * @return	DbBase
	 */
	public static function getConnection($newConnection=false, $renovate=false){
		$database = self::_getDatabaseDescriptor();
		if($newConnection==true){
			if($renovate==true){
				self::$_persistentConnection = DbLoader::factory($database->type, $database, true);
				$connection = self::$_persistentConnection;
			} else {
				$connection = DbLoader::factory($database->type, $database, false);
			}
		} else {
			if(self::$_persistentConnection==null){
				self::$_persistentConnection = DbLoader::factory($database->type, $database, true);
			}
			$connection = self::$_persistentConnection;
		}
		return $connection;
	}

}