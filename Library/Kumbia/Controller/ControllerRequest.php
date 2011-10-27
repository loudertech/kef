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
 * @package		Controller
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ControllerRequest.php,v f5add30bf4ba 2011/10/26 21:05:13 andres $
 */

/**
 * ControllerRequest
 *
 * Esta clase encapusula toda la información de la petición HTTP
 * al controlador
 *
 * @category	Kumbia
 * @package		Controller
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class ControllerRequest extends Object {

	/**
	 * Instancia singleton
	 *
	 * @var ControllerResponse
	 */
	private static $_instance;

	/**
	 * Constructor privado es de un Singleton
	 *
	 * @access private
	 */
	private function __construct(){

	}

	/**
	 * Devuelve la instancia del singleton de la clase
	 *
	 * @access public
	 * @return ControllerRequest
	 * @static
	 */
	public static function getInstance(){
		if(self::$_instance==null){
			self::$_instance = new ControllerRequest();
		}
		return self::$_instance;
	}

	/**
	 * Envia una cookie al cliente
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param int $expire
	 * @param string $path
	 * @param string $domain
	 */
	public function setCookie($name, $value, $expire=0, $path=null, $domain=null){
		setcookie($name, $value, $expire, $path, $domain=null);
	}

	/**
	 * Cambia el valor de un parámetro de $_REQUEST
	 *
	 * @param string $index
	 * @param mixed $value
	 */
	public function setParamRequest($index, $value){
		$_REQUEST[(string) $index] = $value;
		if(isset($_POST[(string) $index])){
			$_POST[(string) $index] = $value;
		}
		if(isset($_GET[(string) $index])){
			$_GET[(string) $index] = $value;
		}
	}

	/**
	 * Cambia el valor de un parámetro de $_POST
	 *
	 * @param string $index
	 * @param mixed $value
	 */
	public function setParamPost($index, $value){
		$_POST[(string) $index] = $value;
	}

	/**
	 * Cambia el valor de un parámetro de $_GET
	 *
	 * @param string $index
	 * @param mixed $value
	 */
	public function setParamGet($index, $value){
		$_GET[(string) $index] = $value;
	}

	/**
	 * Cambia el valor de un parámetro de $_COOKIE
	 *
	 * @param string $index
	 * @param mixed $value
	 */
	public function setParamCookie($index, $value){
		$_COOKIE[(string) $index] = $value;
	}

	/**
	 * Valida que exista un valor en $_REQUEST
	 *
	 * @param mixed $index
	 * @return boolean
	 */
	public function isSetRequestParam($index){
		return isset($_REQUEST[$index]);
	}

	/**
	 * Elimina un indice en $_REQUEST
	 *
	 * @param mixed $index
	 * @return boolean
	 */
	public function unsetRequestParam($index){
		unset($_REQUEST[$index]);
	}

	/**
	 * Elimina un indice en $_POST
	 *
	 * @param mixed $index
	 * @return boolean
	 */
	public function unsetPostParam($index){
		unset($_POST[$index]);
	}

	/**
	 * Elimina un indice en $_GET
	 *
	 * @param mixed $index
	 * @return boolean
	 */
	public function unsetQueryParam($index){
		unset($_GET[$index]);
	}

	/**
	 * Elimina un índice en $_COOKIE
	 *
	 * @param	mixed $index
	 * @return	boolean
	 */
	public function unsetCookieParam($index){
		unset($_COOKIE[$index]);
	}

	/**
	 * Valida que exista un valor en $_GET
	 *
	 * @param mixed $index
	 * @return boolean
	 */
	public function isSetQueryParam($index){
		return isset($_GET[$index]);
	}

	/**
	 * Valida que exista un valor en $_POST
	 *
	 * @param mixed $index
	 * @return boolean
	 */
	public function isSetPostParam($index){
		return isset($_POST[$index]);
	}

	/**
	 * Valida que exista un valor en $_COOKIE
	 *
	 * @access	public
	 * @param	mixed $index
	 * @return	boolean
	 */
	public function isSetCookieParam($index){
		return isset($_COOKIE[$index]);
	}

	/**
	 * Valida que exista un valor en $_SERVER
	 *
	 * @access public
	 * @param mixed $index
	 * @return boolean
	 */
	public function isSetServerParam($index){
		return isset($_SERVER[$index]);
	}

	/**
	 * Valida que exista un valor en $_ENV
	 *
	 * @access public
	 * @param mixed $index
	 * @return boolean
	 */
	public function isSetEnvParam($index){
		return isset($_ENV[$index]);
	}

	/**
	 * Devuelve un parámetro enviado usando una superglobal $_POST
	 * y aplica los filtros correspondientes
	 *
	 * @access	public
	 * @param	string $paramName
	 * @return	boolean
	 */
	public function getParamPost($paramName){
		if(func_num_args()>1){
			$paramValue = isset($_POST[$paramName]) ? $_POST[$paramName] : '';
			$params = func_get_args();
			unset($params[0]);
			return Filter::bring($paramValue, $params);
		}
		return isset($_POST[$paramName]) ? $_POST[$paramName] : '';
	}

	/**
	 * Devuelve un parámetro enviado usando una superglobal $_GET
	 * y aplica los filtros correspondientes
	 *
	 * @access	public
	 * @param	string $paramName
	 * @return	boolean
	 */
	public function getParamQuery($paramName){
		if(func_num_args()>1){
			$paramValue = isset($_GET[$paramName]) ? $_GET[$paramName] : '';
			$params = func_get_args();
			unset($params[0]);
			return Filter::bring($paramValue, $params);
		}
		return isset($_GET[$paramName]) ? $_GET[$paramName] : '';
	}

	/**
	 * Devuelve un parámetro enviado usando una superglobal $_FILES
	 * mediante un objeto ControllerUploadFile
	 *
	 * @access	public
	 * @param	string $paramName
	 * @return	ControllerUploadFile
	 */
	public function getParamFile($paramName){
		if(isset($_FILES[$paramName])){
			return new ControllerUploadFile($_FILES[$paramName]);
		} else {
			return false;
		}
	}

	/**
	 * Devuelve un parámetro enviado usando una superglobal $_REQUEST
	 * y aplica los filtros correspondientes
	 *
	 * @access	public
	 * @param	string $paramName
	 * @return	boolean
	 */
	public function getParamRequest($paramName){
		if(func_num_args()>1){
			$paramValue = isset($_REQUEST[$paramName]) ? $_REQUEST[$paramName] : '';
			$params = func_get_args();
			unset($params[0]);
			return Filter::bring($paramValue, $params);
		}
		return isset($_REQUEST[$paramName]) ? $_REQUEST[$paramName] : '';
	}

	/**
	 * Devuelve un parámetro enviado usando una superglobal $_SERVER
	 * y aplica los filtros correspondientes
	 *
	 * @access	public
	 * @param	string $paramName
	 * @return	boolean
	 */
	public function getParamServer($paramName){
		if(func_num_args()>1){
			$paramValue = isset($_SERVER[$paramName]) ? $_SERVER[$paramName] : '';
			$params = func_get_args();
			unset($params[0]);
			return Filter::bring($paramValue, $params);
		}
		return isset($_SERVER[$paramName]) ? $_SERVER[$paramName] : '';
	}

	/**
	 * Devuelve un parámetro enviado usando una superglobal $_ENV
	 * y aplica los filtros correspondientes
	 *
	 * @access	public
	 * @param	string $paramName
	 * @return	boolean
	 */
	public function getParamEnv(){
		if(func_num_args()>1){
			$paramValue = isset($_ENV[$paramName]) ? $_ENV[$paramName] : '';
			$params = func_get_args();
			unset($params[0]);
			return Filter::bring($paramValue, $params);
		}
		return isset($_ENV[$paramName]) ? $_ENV[$paramName] : '';
	}

	/**
	 * Devuelve un parámetro enviado usando una superglobal $_COOKIE
	 * y aplica los filtros correspondientes
	 *
	 * @access	public
	 * @param	string $paramName
	 * @return	boolean
	 */
	public function getParamCookie($paramName){
		if(func_num_args()>1){
			$paramValue = isset($_COOKIE[$paramName]) ? $_COOKIE[$paramName] : '';
			$params = func_get_args();
			unset($params[0]);
			return Filter::bring($paramValue, $params);
		}
		return isset($_COOKIE[$paramName]) ? $_COOKIE[$paramName] : '';
	}

	/**
     * Indica si la petición se realizo usando AJAX de Prototype
     *
     * @return boolean
     */
    public function isAjax(){
        return ($this->getHeader('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest');
    }

    /**
     * Indica si la petición fue realizada usando Flash
     *
     * @return boolean
     */
    public function isFlashRequested(){
        $header = strtolower($this->getHeader('HTTP_USER_AGENT'));
        if(strpos($header, ' flash')){
        	return true;
        } else {
        	return false;
        }
    }

    /**
     * Indica si la petición fue realizada usando un cliente SOAP
     *
     * @return boolean
     */
    public function isSoapRequested(){
        if(isset($_SERVER['HTTP_SOAPACTION'])){
        	return true;
        } else {
        	if(isset($_SERVER['CONTENT_TYPE'])){
	        	if(strpos($_SERVER['CONTENT_TYPE'], 'application/soap+xml')!==false){
	        		return true;
	        	} else {
	        		return false;
	        	}
        	} else {
        		return false;
        	}
        }
    }

    /**
     * Is https secure request
     *
     * @return boolean
     */
    public function isSecureRequest(){
        if($this->getScheme()==='https'){
        	return true;
        } else {
        	return false;
        }
    }

    /**
     * Devuelve el cuerpo de la peticion HTTP
     *
     * @return string
     */
    public function getRawBody(){
        return file_get_contents('php://input');
    }

    /**
     * Devuelve la IP del servidor
     *
     * @return string
     */
    public function getServerAddress(){
    	if(isset($_SERVER['SERVER_ADDR'])){
    		return $_SERVER['SERVER_ADDR'];
    	} else {
    		return gethostbyname('localhost');
    	}
    }

    /**
	 * Devuelve el Host que recibio la peticion
	 *
	 * @return string
	 */
    public function getServerName(){
        if(isset($_SERVER['SERVER_NAME'])){
                return $_SERVER['SERVER_NAME'];
        } else {
                return 'localhost';
        }
    }

    /**
     * Devuelve el Header HTTP si este existe
     *
     * @param string $header
     */
    public function getHeader($header){
        if(isset($_SERVER[$header])){
        	return $_SERVER[$header];
        } else {
        	if(isset($_SERVER['HTTP_'.$header])){
        		return $_SERVER['HTTP_'.$header];
        	} else {
        		if(Core::isHurricane()==false){
        			return '';
        		} else {
        			//Normalizar encabezado
	        		$header = str_replace('_', ' ', strtolower($header));
					$header = str_replace(' ', '-', ucwords($header));
					if(preg_match('/^Http/', $header)){
						$header = substr($header, 5);
					}
        			return HurricaneServer::getHeader($header);
        		}
        	}
        }
    }

    /**
     * Devuelve el protocolo usado para hacer la peticion
     *
     * @return string
     */
    public function getScheme(){
        if($this->getParamServer('HTTP_HTTPS')=='on'){
        	return 'https';
        } else {
        	return 'http';
        }
    }

    /**
     * Devuelve el Host que recibio la peticion
     *
     * @return string
     */
    public function getHttpHost(){
        $scheme = $this->getScheme();
        $name = $this->getParamServer('HTTP_SERVER_NAME');
        $port = $this->getParamServer('HTTP_SERVER_PORT');
        if(($scheme=='http'&&$port==80)||($scheme=='https'&&$port==443)){
            return $name;
        } else {
            return $name.':'.$port;
        }
    }

    /**
     * Devuelve el metodo HTTP con el que se realizo la petición
     *
     * @return string
     */
    public function getMethod(){
    	if(isset($_SERVER['REQUEST_METHOD'])){
			return $_SERVER['REQUEST_METHOD'];
    	} else {
    		return '';
    	}
    }

	/**
	 * Obtiene el User Agent Actual
	 *
	 * @return string
	 */
    public function getUserAgent(){
    	if(isset($_SERVER['HTTP_USER_AGENT'])){
    		return $_SERVER['HTTP_USER_AGENT'];
    	} else {
    		return '';
    	}
    }

    /**
	 * Indica si la petición fue realizada usando metodo HTTP POST
	 *
	 * @return boolean
	 */
	public function isPost(){
		if($this->getMethod()=='POST'){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Indica si la petición fue realizada usando metodo HTTP GET
	 *
	 * @return boolean
	 */
	public function isGet(){
		if($this->getMethod()=='GET'){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Indica si la petición fue realizada usando metodo HTTP PUT
	 *
	 * @return boolean
	 */
	public function isPut(){
		if($this->getMethod()=='PUT'){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Indica si la petición fue realizada usando metodo HTTP HEAD
	 *
	 * @return boolean
	 */
	public function isHead(){
		if($this->getMethod()=='HEAD'){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Indica si la petición fue realizada usando metodo HTTP DELETE
	 *
	 * @return boolean
	 */
	public function isDelete(){
		if($this->getMethod()=='DELETE'){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Indica si la petición fue realizada usando metodo HTTP OPTIONS
	 *
	 * @return boolean
	 */
	public function isOptions(){
		if($this->getMethod()=='OPTIONS'){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Indica si se han enviado archivos con la petición
	 *
	 * @return boolean
	 */
	public function hasFiles(){
		if(isset($_FILES)){
			return count($_FILES)>0 ? true : false;
		} else {
			return false;
		}
	}

	/**
	 * Devuelve un vector con los objetos ControllerUploadFile que son los archivos subidos
	 *
	 * @return array
	 */
	public function getUploadedFiles(){
		if(isset($_FILES)){
			$files = array();
			foreach($_FILES as $file){
				$controllerFile = new ControllerUploadFile($file);
				$files[] = $controllerFile;
			}
			return $files;
		} else {
			return array();
		}
	}

	/**
	 * Obtiene el HTTP REFERER de la petición
	 *
	 * @return string
	 */
	public function getHTTPReferer(){
		if(isset($_SERVER['HTTP_REFERER'])){
			return $_SERVER['HTTP_REFERER'];
		} else {
			return "";
		}
	}

	/**
	 * Devuelve la lista de idiomas soportados por el cliente HTTP
	 *
	 * return array
	 */
	public function getAcceptableContent(){
		$httpAccept = getenv('HTTP_ACCEPT');
		$accepted = preg_split('/,\s*/', $httpAccept);
		$returnedAccept = array();
		foreach($accepted as $accept){
			$acceptParts = explode(";", $accept);
			if(isset($acceptParts[1])){
				$quality = (float) substr($acceptParts[1], 2);
			} else {
				$quality = 1.0;
			}
			$returnedAccept[] = array(
				'accept' => $acceptParts[0],
				'quality' => $quality
			);
		}
		return $returnedAccept;
	}

	/**
	 * Devuelve los charsets soportados por el cliente
	 *
	 * @return array
	 */
	public function getClientCharsets(){
		$httpAcceptCharset = getenv('HTTP_ACCEPT_CHARSET');
		$accepted = preg_split('/,\s*/', $httpAcceptCharset);
		$returnedAccept = array();
		foreach($accepted as $accept){
			$acceptParts = explode(";", $accept);
			if(isset($acceptParts[1])){
				$quality = (float) substr($acceptParts[1], 2);
			} else {
				$quality = 1.0;
			}
			$returnedAccept[] = array(
				'accept' => $acceptParts[0],
				'quality' => $quality
			);
		}
		return $returnedAccept;
	}

	/**
	 * Obtiene el charset aceptado de mayor calidad
	 *
	 * @return string
	 */
	public function getBestQualityCharset(){
		$selectedCharsetQuality = 0;
		$selectedCharsetName = "";
		$i = 0;
		foreach($this->getClientCharsets() as $charset){
			if($i==0){
				$selectedCharsetQuality = $charset['quality'];
				$selectedCharsetName = $charset['accept'];
			} else {
				if($charset['quality']>$selectedCharsetQuality){
					$selectedCharsetQuality = $charset['quality'];
					$selectedCharsetName = $charset['accept'];
				}
			}
			++$i;
		}
		return $selectedCharsetName;
	}

	/**
	 * Indica si se está solicitando contenido estático
	 *
	 * @return boolean
	 */
	public function isRequestingStaticContent(){
		$staticMime = array(
			'text/javascript',
			'text/css',
			'image/gif',
			'image/png',
			'image/jpeg',
			'application/x-javascript'
		);
		foreach($this->getAcceptableContent() as $mime){
			return in_array($mime['accept'], $staticMime);
		}
		return false;
	}

	/**
	 * Devuelve el nombre de la acción que refirio la petición
	 *
	 * @return string
	 */
	public function getRefererAction(){
		if(isset($_SERVER['HTTP_REFERER'])){
			$instancePath = Core::getInstancePath();
			if(($pos = strpos($_SERVER['HTTP_REFERER'], $instancePath))!==false){
				$application = Router::getActiveApplication();
				$uri = substr($_SERVER['HTTP_REFERER'], $pos+strlen($instancePath.$application));
				$items = explode('/', $uri);
				if(isset($items[2])){
					if(($dotpos = strpos($items[2], '.'))!==false){
						return substr($items[2], 0, $dotpos);
					} else {
						return $items[2].'?';
					}
				}
			}
		} else {
			return false;
		}
	}

	/**
	 * Obtiene la IP del cliente, revisa si pasa por un Proxy HTTP
	 *
	 * @return string
	 */
	public function getClientAddress(){
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			if(isset($_SERVER['REMOTE_ADDR'])){
				return $_SERVER['REMOTE_ADDR'];
			} else {
				return '';
			}
		}
	}

	/**
	 * Devuelve los valores existentes en $_POST si el metodo es POST
	 *
	 * @return array
	 */
	public function getPostParams(){
		if($this->isPost()==true){
			return $_POST;
		} else {
			return array();
		}
	}

	/**
	 * Devuelve los valores existentes en $_POST si el metodo es POST
	 *
	 * @return array
	 */
	public function getQueryParams(){
		if($this->isPost()==true){
			return $_GET;
		} else {
			return array();
		}
	}

}
