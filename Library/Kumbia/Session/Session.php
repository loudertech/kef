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
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Session.php 99 2009-10-03 02:26:46Z gutierrezandresfelipe $
 */

/**
 * Session
 *
 * Modelo orientado a objetos para el acceso a datos en Sesiones.
 * Mantiene la memoria de sesion de forma independiente para cada aplicacion
 * en cada instancia del framework.
 *
 * @category 	Kumbia
 * @package 	Session
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @access 		public
 * @abstract
 */
abstract class Session {

	/**
	 * Indica si la sesion se ha iniciado o todavia no
	 *
	 * @var boolean
	 */
	static private $_sessionStarted = false;

	/**
	 * Indica si los datos de sesión han sido inicializados ó no
	 *
	 * @var boolean
	 */
	static private $_dataInitialized = false;

	/**
	 * Indica si la sesión está bloqueada
	 *
	 * @var boolean
	 */
	static private $_locked = false;

	/**
	 * Indica si el inicio automático esta deshabilitado
	 *
	 * @var boolean
	 */
	static private $_disabled = false;

	/**
	 * Deserializa los datos de sesion para su posterior uso en la aplicacion
	 *
	 */
	static public function initSessionData(){
		/*if(self::$_dataInitialized==true){
			return true;
		}*/
		if(isset($_SESSION['session_data'])){
			if(!is_array($_SESSION['session_data'])){
				$_SESSION['session_data'] = unserialize($_SESSION['session_data']);
			}
		} else {
			$_SESSION['session_data'] = array();
		}
		self::$_dataInitialized = true;
	}

	/**
	 * Serializa los datos de sesion para su posterior almacenamiento en el backend
	 *
	 * @access public
	 * @static
	 */
	static public function storeSessionData(){
		self::$_dataInitialized = false;
		if(isset($_SESSION['session_data'])&&is_array($_SESSION['session_data'])){
			$_SESSION['session_data'] = serialize($_SESSION['session_data']);
		}
		session_write_close();
	}

	/**
	 * Crear o especificar el valor para un indice de la sesi�n
	 * actual
	 *
	 * @access public
	 * @param string $index
	 * @param mixed $value
	 * @static
	 */
	static public function setData($index, $value){
		self::set($index, $value);
	}

	/**
	 * Obtener el valor para un indice de la sesion
	 *
	 * @access public
	 * @param string $index
	 * @return mixed
	 * @static
	 */
	static public function getData($index){
		Session::initSessionData();
		if(isset($_SESSION['session_data'][(string) $index])){
			if(func_num_args()>1){
				$args = func_get_args();
				unset($args[0]);
				return Filter::bring($_SESSION['session_data'][(string) $index], $args);
			}
	  		return $_SESSION['session_data'][(string) $index];
		} else {
			return null;
		}
	}

	/**
	 * Crear o especificar el valor para un indice de la sesion
	 * actual
	 *
	 * @access 	public
	 * @param 	string $index
	 * @param 	mixed $value
	 * @static
	 */
	static public function set($index, $value){
		Session::initSessionData();
	  	$_SESSION['session_data'][(string) $index] = $value;
	}

	/**
	 * Obtener el valor para un indice de la sesion
	 *
	 * @param string $index
	 * @return mixed
	 */
	static public function get($index){
		Session::initSessionData();
		if(isset($_SESSION['session_data'][(string) $index])){
			$value = $_SESSION['session_data'][(string) $index];
		} else {
			$value = null;
		}
		if(func_num_args()>1){
			$args = func_get_args();
			unset($args[0]);
			return Filter::bring($value, $args);
		}
		return $value;
	}

	/**
	 * Unset una variable de indice
	 *
	 * @static
	 */
	static public function unsetData(){
		Session::initSessionData();
	  	$listArgs = func_get_args();
	  	if($listArgs){
	  		if(is_array($_SESSION['session_data'])){
  	  			foreach($listArgs as $arg){
			  		unset($_SESSION['session_data'][(string) $arg]);
				}
	  		}
		}
	}

	/**
	 * Evalua si esta definido un valor dentro de
	 * los valores de sesion
	 *
	 * @param 	string $index
	 * @return 	mixed
	 * @static
	 */
	static public function isSetData($index){
		Session::initSessionData();
		return isset($_SESSION['session_data'][(string) $index]);
	}

	/**
	 * Indica si la sesión esta desbloqueada
	 *
	 * @static
	 */
	static public function isLocked(){
		return self::$_locked;
	}

	/**
	 * Indica si la sesion ha sido iniciada
	 *
	 * @return boolean
	 */
	static public function isStarted(){
		return self::$_sessionStarted;
	}

	/**
	 * Init Session Management
	 *
	 * @access public
	 * @static
	 */
	static public function startSession(){
		if(self::$_sessionStarted==true){
			return false;
		}
		$config = CoreConfig::readAppConfig();
		if(isset($config->application->sessionAdapter)){
			$sessionAdapter = ucfirst($config->application->sessionAdapter);
			$className = $sessionAdapter.'SessionAdapter';
			if(interface_exists('SessionInterface', false)==false){
				require KEF_ABS_PATH.'Library/Kumbia/Session/Interface.php';
			}
			if(class_exists($className, false)==false){
				require KEF_ABS_PATH.'Library/Kumbia/Session/Adapters/'.$sessionAdapter.'.php';
			}
			if(class_exists($className, false)){
				$sessionObject = new $className();
				ini_set('session.save_handler', $sessionObject->getSaveHandler());
				$sessionObject->initialize();
			} else {
				throw new SessionException("No existe la clase adaptador de session '$className'");
			}
		}
		#if[compile-time]
		if(isset($config->application->clustering)){
			if($config->application->clustering==true){
				if(isset($_SERVER['HTTP_X_CLUSTER_SESSID'])){
					session_id($_SERVER['HTTP_X_CLUSTER_SESSID']);
					unset($_SERVER['HTTP_X_CLUSTER_SESSID']);
				}
			}
		}
		#endif
		if(self::$_disabled==false){
		    session_start();
			register_shutdown_function(array('Session', 'storeSessionData'));
		}
		self::$_sessionStarted = true;
		return true;
	}

	/**
	 * Devuelve el id de la session actual
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	static public function getId(){
		return session_id();
	}

	/**
	 * Deshabilita el inicio automático de la sesión
	 *
	 * @param boolean $disable
	 */
	static public function disableAutoStart($disable){
		self::$_disabled = $disable;
	}

}
