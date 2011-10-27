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
 * @package		ApplicationMonitor
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ApplicationMonitor.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * ApplicationMonitor
 *
 * Clase que permite establecer el estado de ejecucion de una aplicacion
 *
 * @category	Kumbia
 * @package		ApplicationMonitor
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @access 		public
 * @abstract
 */
abstract class ApplicationMonitor extends Object {

	/**
	 * Backends soportados
	 *
	 * @var array
	 */
	private static $_supportedBackends = array('Db');

	/**
	 * Recurso para acceder al Backend de Monitoreo
	 *
	 * @var resource
	 */
	private static $_backendResource;

	/**
	 * Tipo de Backend a utilizar
	 *
	 * @var string
	 */
	private static $_backendType;

	/**
	 * Opciones del backend
	 *
	 * @var array
	 */
	private static $_backendOptions = array();

	/**
	 * Indica que el estado es normal
	 *
	 */
	const STATUS_OK = 0;

	/**
	 * Indica que el estado es error
	 *
	 */
	const STATUS_ERROR = 1;

	/**
	 * Indica que el estado es advertencia
	 *
	 */
	const STATUS_WARNING = 2;

	/**
	 * Establece el estado de la aplicacion
	 *
	 * @access public
	 * @param string $status
	 * @static
	 */
	public static function initApplicationStatus($status){
		$sid = session_id();
		self::_initializeBackend();
		$exists = self::$_backendResource->fetchOne("SELECT COUNT(*) FROM appmonitor WHERE sid = '$sid' AND instance = '".Core::getInstanceName()."' AND application = '".Router::getApplication()."'");
		if($exists[0]==0){
			self::$_backendResource->insert("appmonitor",
				array($sid, Core::getInstanceName(), Router::getApplication(), $_SERVER['REMOTE_ADDR'], $status),
				array("sid", "instance", "application", "ipaddress", "status"),
				true
			);
		} else {
			self::$_backendResource->update("appmonitor",
				array("lasttime", "memoryusage", "status"),
				array(time(), memory_get_usage(true), $status),
				"sid = '$sid' AND instance = '".Core::getInstanceName()."' AND application = '".Router::getApplication()."'",
				true
			);
		}
	}

	/**
	 * Actualiza el estado de monitoreo de la aplicacion
	 *
	 * @access public
	 * @param integer $status
	 * @param string $userMessage
	 * @param string $userCode
	 * @static
	 */
	public static function updateApplicationStatus($status, $userMessage='', $userCode=''){
		self::_initializeBackend();
		$sid = session_id();
		self::$_backendResource->update("appmonitor",
			array("lasttime", "memoryusage", "lasturl", "lastmessage", "lastcode", "status"),
			array(time(), memory_get_usage(true), Router::getURL(), addslashes($userMessage), $userCode, $status),
			"sid = '$sid' AND instance = '".Core::getInstanceName()."' AND application = '".Router::getApplication()."'",
			true
		);
	}

	/**
	 * Establece las opciones de almacenamiento del estado de monitoreo
	 *
	 * @access public
	 * @param string $backendType
	 * @param array $options
	 * @static
	 */
	public static function setBackendOptions($backendType, $options){
		if(!in_array($backendType, self::$_supportedBackends)){
			throw new ApplicationMonitorException("Backend '$backendType' no soportado por ApplicationMonitor");
		} else {
			self::$_backendType = $backendType;
		}
		if($backendType=='Db'){
			if(!isset($options['type'])){
				throw new ApplicationMonitorException("Debe definir el gestor relacional a usar por el ApplicationMonitor");
			} else {
				self::$_backendOptions = $options;
			}
		}
	}

	/**
	 * Inicializar Backend resource
	 *
	 * @access private
	 * @static
	 */
	private static function _initializeBackend(){
		if(self::$_backendResource==null){
			self::$_backendResource = DbLoader::factory(self::$_backendOptions['type'], self::$_backendOptions);
		}
	}

}
