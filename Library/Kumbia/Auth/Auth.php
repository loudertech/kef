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
 * @package		Auth
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Auth.php 103 2009-10-09 01:30:42Z gutierrezandresfelipe $
 */

/**
 * Auth
 *
 * Componente que permite realizar autenticacion sobre multiples
 * gestores de identidad, directorios, PAM, etc.
 *
 * @category	Kumbia
 * @package		Auth
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class Auth extends Object {

	/**
	 * Nombre del adaptador usado para autenticar
	 *
	 * @var string
	 */
	private $_adapter;

	/**
	 * Objeto Adaptador actual
	 *
	 * @var mixed
	 */
	private $_adapterObject = null;

	/**
	 * Indica si un usuario debe loguearse solo una vez en el sistema desde
	 * cualquier parte
	 *
	 * @var boolean
	 */
	private $_activeSession = false;

	/**
	 * Tiempo en que expirara la sesion en caso de que no se termine con destroyActiveSession
	 *
	 * @var integer
	 */
	private $_expireTime = 3600;

	/**
	 * Argumentos extra enviados al Adaptador
	 *
	 * @var array
	 */
	private $_extraArgs = array();

	/**
	 * Tiempo que duerme la aplicacion cuando falla la autenticacion
	 */
	private $_sleepTime = 0;

	/**
	 * Indica si el ultimo llamado a authenticate tuvo exito o no (persistente en sesion)
	 *
	 * @var boolean
	 */
	static private $_isValid = null;

	/**
	 * Ultima identidad obtenida por Authenticate (persistente en sesion)
	 *
	 * @var array
	 */
	static private $_activeIdentity = array();

	/**
	 * Adaptadores Soportados
	 *
	 * @var array
	 */
	static private $_supportedAdapters = array('digest', 'http', 'model', 'kerberos5', 'radius', 'ldap');

	/**
	 * Constructor del Autenticador
	 *
	 * @access public
	 */
	public function __construct(){
		$numberArguments = func_num_args();
		$extraArgs = Utils::getParams(func_get_args(), $numberArguments);
		if(isset($extraArgs[0])){
			$adapter = $extraArgs[0];
			unset($extraArgs[0]);
		} else {
			if(isset($extraArgs['adapter'])){
				$adapter = $extraArgs['adapter'];
			} else {
				$adapter = 'model';
			}
		}
		$this->setAdapter($adapter, $this, $extraArgs);
	}

	/**
	 * Establece el Adaptador a ser utilizado
	 *
	 * @access 	public
	 * @param 	string $adapter
	 * @param 	string $auth
	 * @param 	string $extraArgs
	 */
	public function setAdapter($adapter, $auth = null, $extraArgs=array()){
		if(!in_array($adapter, self::$_supportedAdapters)){
			throw new AuthException('Adaptador de autenticación "'.$adapter.'" no soportado');
		}
		$this->_adapter = $adapter;
		$adapterClass = $adapter.'Auth';
		if(class_exists($adapterClass, false)==false){
			$filePath = 'Library/Kumbia/Auth/Adapters/'.ucfirst($adapter).'.php';
			if(Core::fileExists($filePath)){
				/**
	 			 * @see AuthInterface
	 			 */
				require KEF_ABS_PATH.'Library/Kumbia/Auth/Interface.php';
				require KEF_ABS_PATH.'Library/Kumbia/Auth/Adapters/'.ucfirst($adapter).'.php';
			} else {
				throw new AuthException("No existe el adaptador de autenticación: '$adapter'");
			}
		}
		$this->_extraArgs = $extraArgs;
		$this->_adapterObject = new $adapterClass($auth, $extraArgs);
	}

	/**
	 * Obtiene el nombre del adaptador actual
	 *
	 * @access public
	 * @return boolean
	 */
	public function getAdapterName(){
		return $this->_adapter;
	}

	/**
	 * Realiza el proceso de autenticación
	 *
	 * @access public
	 * @return array
	 */
	public function authenticate(){
		$result = $this->_adapterObject->authenticate();
		/**
		 * Si es una sesion activa maneja un archivo persistente para control
		 */
		if($result&&$this->_activeSession){
			$activeApp = Router::getApplication();
			$userHash = md5(serialize($this->_extraArgs));
			$filename = "cache/".base64_encode($activeApp);
			if(Core::fileExists($filename)){
				$fp = fopen($filename, "r");
				while(!feof($fp)){
					$line = fgets($fp);
					$user = explode(":", $line);
					if($userHash==$user[0]){
						if($user[1]+$user[2]>time()){
							if($this->sleepTime){
								sleep($this->sleepTime);
							}
							self::$_identity = array();
							self::$_isValid = false;
							return false;
						} else {
							fclose($fp);
							$this->_destroyActiveSession();
							file_put_contents($filename, $userHash.":".time().":".$this->expireTime."\n");
						}
					}
				}
				fclose($fp);
				$fp = fopen($filename, "a");
				fputs($fp, $userHash.":".time().":".$this->_expireTime."\n");
				fclose($fp);
			} else {
				file_put_contents($filename, $userHash.":".time().":".$this->_expireTime."\n");
			}
		}
		if(!$result){
			if($this->_sleepTime){
				sleep($this->_sleepTime);
			}
		}
		Session::set('AUTH_IDENTITY', $this->_adapterObject->getIdentity());
		self::$_activeIdentity = $this->_adapterObject->getIdentity();
		Session::set('AUTH_VALID', $result);
		self::$_isValid = $result;
		return $result;
	}

	/**
	 * Realiza el proceso de autenticación usando HTTP
	 *
	 * @access public
	 * @return array
	 */
	public function authenticateWithHttp(){
		if(!$_SERVER['PHP_AUTH_USER']){
    		header('WWW-Authenticate: Basic realm="basic"');
    		header('HTTP/1.0 401 Unauthorized');
    		return false;
		} else {
			$options = array('username' => $_SERVER['PHP_AUTH_USER'], 'password' => $_SERVER['PHP_AUTH_PW']);
			$this->_adapterObject->setParams($options);
			return $this->authenticate();
		}
	}

	/**
	 * Devuelve la identidad encontrada en caso de exito
	 *
	 * @access public
	 * @return array
	 */
	public function getIdentity(){
		return $this->_adapterObject->getIdentity();
	}

	/**
	 * Permite controlar que usuario no se loguee mas de una vez en el sistema desde cualquier parte
	 *
	 * @access public
	 * @param string $value
	 * @param integer $time
	 */
	public function setActiveSession($value, $time=3600){
		$this->_activeSession = $value;
		$this->_expireTime = $time;
	}

	/**
	 * Permite saber si existe una identidad en la sesion actual
	 *
	 * @access public
	 */
	public function existsIdentity(){
		if(Session::issetData('AUTH_IDENTITY')==true){
			if(is_array(Session::getData('AUTH_IDENTITY'))){
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Destruir sesion activa del usuario autenticado
	 *
	 */
	public function destroyActiveSession(){
		$activeApp = Router::getApplication();
		$userHash = md5(serialize($this->_extraArgs));
		$filename = "cache/".base64_encode($activeApp);
		$lines = file($filename);
		$linesOut = array();
		foreach($lines as $line){
			if(substr($line, 0, 32)!=$userHash){
				$linesOut[] = $line;
			}
		}
		file_put_contents($filename, join("\n", $linesOut));
	}

	/**
	 * Devuelve la instancia del adaptador
	 *
	 * @return string
	 */
	public function getAdapterInstance(){
		return $this->_adapterObject;
	}

	/**
	 * Determinar si debe dormir la aplicacion cuando falle la autenticación y cuanto tiempo en segundos
	 *
	 * @param boolean $value
	 * @param integer $time
	 */
	public function sleepOnFail($value, $time=2){
		$time = (int) $time;
		if($time<0){
			$time = 0;
		}
		if($value){
			$this->_sleepTime = $time;
		} else {
			$this->_sleepTime = 0;
		}
	}

	/**
	 * Devuelve el resultado del uttimo llamado a authenticate desde el ultimo objeto Auth instanciado
	 *
	 * @return boolean
	 * @static
	 */
	static public function isValid(){
		if(self::$_isValid!==null){
			return self::$_isValid;
		} else {
			self::$_isValid = Session::get('AUTH_VALID');
			return self::$_isValid;
		}
	}

	/**
	 * Establece el tiempo en que debe expirar la sesion
	 *
	 * @param int $time
	 */
	public function setExpireTime($time){
		Session::set('AUTH_EXPIRE', (int)$time);
		Session::set('AUTH_TIME', Core::getProximityTime());
	}

	/**
	 * Devuelve el resultado de la ultima identidad obtenida en authenticate desde el ultimo objeto Auth instanciado
	 *
	 * @access public
	 * @return array
	 * @static
	 */
	static public function getActiveIdentity(){
		if(count(self::$_activeIdentity)){
			return self::$_activeIdentity;
		} else {
			self::$_activeIdentity = Session::get('AUTH_IDENTITY');
			return self::$_activeIdentity;
		}
	}

	/**
	 * Establece programacionalmente la identidad actual
	 *
	 * @access 	public
	 * @param 	array $identity
	 * @static
	 */
	static public function setActiveIdentity($identity){
		if(is_array($identity)||is_object($identity)){
			Session::set('AUTH_IDENTITY', $identity);
			self::$_activeIdentity = $identity;
			Session::set('AUTH_VALID', true);
		} else {
			throw new AuthException('La identidad debe ser una variable array ó un objeto');
		}
	}

	/**
	 * Anula la identidad actual
	 *
	 * @access public
	 * @static
	 */
	static public function destroyIdentity(){
		self::$_isValid = null;
		Session::unsetData('AUTH_VALID');
		self::$_activeIdentity = null;
		Session::unsetData('AUTH_IDENTITY');
	}

}

