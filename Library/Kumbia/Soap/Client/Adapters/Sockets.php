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
 * @package 	Soap
 * @subpackage 	Adapters
 * @subpackage 	Client
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: Sockets.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * SocketsCommunicator
 *
 * Cliente para realizar peticiones HTTP
 *
 * @category	Kumbia
 * @package 	Soap
 * @subpackage 	Adapters
 * @subpackage 	Client
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @abstract
 */
class SocketsCommunicator {

	/**
	 * Handler del Socket
	 *
	 * @var resource
	 */
	private $_socketHandler;

	/**
	 * Peticion HTTP a realizar
	 *
	 * @var string
	 */
	private $_httpRequest;

	/**
	 * Host dirección de la petición
	 *
	 * @var string
	 */
	private $_host = '';

	/**
	 * Metodo utilizado para realizar la peticion
	 *
	 * @var string
	 */
	private $_method;

	/**
	 * URI solicitada
	 *
	 * @var string
	 */
	private $_uri;

	/**
	 * Parametros pasados por GET
	 *
	 * @var array
	 */
	private $_queryParams = array();

	/**
	 * Encabezados de la petición
	 *
	 * @var array
	 */
	private $_headers = array('Accept' => '*/*');

	/**
	 * Cookies de la petición
	 *
	 * @var array
	 */
	private $_cookies = array();

	/**
	 * Habilita el envio automático de las cookies recibidas
	 *
	 * @var boolean
	 */
	private $_enableCookies = false;

	/**
	 * Response status de la respuesta
	 *
	 * @var string
	 */
	private $_responseStatus;

	/**
	 * Response code de la respuesta
	 *
	 * @var int
	 */
	private $_responseCode;

	/**
	 * Encabezados de Respuesta
	 *
	 * @var array
	 */
	private $_responseHeaders = array();

	/**
	 * Cuerpo de la respuesta
	 *
	 * @var string
	 */
	private $_responseBody = '';

	/**
	 * Raw Post Data
	 *
	 * @var string
	 */
	private $_rawPostData;

	/**
	 * Metodos HTTP soportados por el Adaptador
	 *
	 * @var array
	 */
	private static $_supportedMethods = array('POST', 'GET');

	/**
	 * Constructor del SocketCommunicator
	 *
	 * @param string $scheme
	 * @param string $host
	 * @param string $uri
	 * @param string $method
	 * @param int $port
	 */
	public function __construct($scheme, $host, $uri, $method, $port=80){
		if($scheme=='https'){
			$address = 'ssl://'.$host;
		} else {
			$address = 'tcp://'.$host;
		}
		$this->_host = $host;
		$this->_socketHandler = @pfsockopen($host, $port, $errorString);
		if(!$this->_socketHandler){
			throw new SoapException($errorString);
		}
		if($this->_isSupportedMethod($method)==false){
			throw new SoapException('El tipo de metodo HTTP "'.$method.'" no está soportado');
		}
		$this->_method = $method;
		$this->_uri = $uri;
	}

	/**
	 * Valida si un método HTTP está soportado por el Adaptador
	 *
	 * @param string $method
	 */
	private function _isSupportedMethod($method){
		return in_array($method, self::$_supportedMethods);
	}

	/**
	 * Establece los encabezados de la peticion
	 *
	 * @param array $headers
	 */
	public function setHeaders($headers){
		foreach($headers as $headerName => $headerValue){
			$this->_headers[$headerName] = $headerValue;
		}
		unset($this->_headers['Accept-Encoding']);
	}

	/**
	 * Establece los encabezados de la peticion
	 *
	 * @param array $headers
	 */
	public function addHeaders($headers){
		CoreType::assertArray($headers);
		foreach($headers as $headerName => $headerValue){
			$this->_headers[$headerName] = $headerValue;
		}
	}

	/**
	 * Agrega un encabezado a la petición
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function addHeader($name, $value){
		$this->_headers[$name] = $value;
	}

	/**
	 * Establece las cookies de la petición
	 *
	 * @param array $cookies
	 */
	public function setCookies($cookies){
		$this->_cookies = $cookies;
	}

	/**
	 * Agrega los parametros por GET a la peticion
	 *
	 * @access public
	 */
	public function prepareQueryData($queryParams){
		$query = array();
		foreach($queryParams as $paramName => $paramValue){
			$query[] = $paramName.'='.urlencode($paramValue);
		}
		if(count($query)){
			if(strpos($this->_uri, '?')===false){
				$this->_uri.='?'.join('&', $query);
			}
		}
	}

	/**
	 * Establece el Raw POST data
	 *
	 * @param string $rawPostData
	 */
	public function setRawPostData($rawPostData){
		$this->_rawPostData = $rawPostData;
	}

	/**
	 * Envía la petición HTTP
	 *
	 * @access public
	 */
	public function send(){
		if($this->_method=='GET'){
			$this->_httpRequest = "GET /".$this->_uri." HTTP/1.1\r\n";
		} else {
			if($this->_method=='POST'){
				//?XDEBUG_SESSION_START=noshe
				$this->_httpRequest = "POST /".$this->_uri." HTTP/1.1\r\n";
			}
		}
		foreach($this->_headers as $headerName => $headerValue){
			$this->_httpRequest.=$headerName.': '.$headerValue."\r\n";
		}
		if(count($this->_cookies)>0||$this->_enableCookies==true){
			$this->_httpRequest.='Cookie: ';
			foreach($this->_cookies as $cookieName => $cookieValue){
				 $this->_httpRequest.=$cookieName.'='.$cookieValue.';';
			}
			if(isset($_SESSION['KHC'][$this->_host])){
				foreach($_SESSION['KHC'][$this->_host] as $cookieName => $cookieValue){
					$this->_httpRequest.=$cookieName.'='.$cookieValue.';';
				}
			}
			$this->_httpRequest.="\r\n";
		}
		if($this->_method=='POST'){
			if($this->_rawPostData==''){
				$this->_httpRequest.="Content-Length: 28\r\n";
				$this->_httpRequest.="Content-Type: application/x-www-form-urlencoded\r\n";
				$postData = array();
				/*if(isset($_POST)){
					foreach($_POST as $key => $value){
						$postData[] = $key."=".urlencode($value);
					}
					$this->_httpRequest.="\r\n".join("&", $postData);
				} else {
					$this->_httpRequest.="\r\n";
				}*/
				$this->_httpRequest.="\r\n";
			} else {
				$this->_httpRequest.="Content-Length: ".i18n::strlen($this->_rawPostData)."\r\n";
				$this->_httpRequest.="\r\n";
				$this->_httpRequest.=$this->_rawPostData;
			}
		} else {
			$this->_httpRequest.="\r\n";
		}
		fwrite($this->_socketHandler, $this->_httpRequest);

		$response = '';
		$header = true;
		$i = 0;
		$this->_responseHeaders = array();
		while(!feof($this->_socketHandler)){
			$line = fgets($this->_socketHandler);
			if($header==true){
				if($i==0){
					if($line!==false){
						$fline = explode(' ', $line);
						if(count($fline)>=2){
							$this->_responseCode = $fline[1];
							$this->_responseStatus = rtrim($fline[2]);
						}
					} else {
						throw new CoreException('La respuesta fue vacia', 0);
					}
				} else {
					if($line!="\r\n"){
						$pline = explode(': ', $line, 2);
						if(count($pline)==2){
							$this->_responseHeaders[$pline[0]] = substr($pline[1], 0, strlen($pline[1])-2);
						} else {
							break;
						}
					} else {
						break;
					}
				}
			}
			++$i;
    	}
    	$this->_responseBody = '';
    	if(isset($this->_responseHeaders['Content-Length'])){
    		$contentLength = $this->_responseHeaders['Content-Length'];
    		for($i=0;$i<$contentLength;$i++){
    			$this->_responseBody.=fgetc($this->_socketHandler);
    		}
    	} else {
    		while(!feof($this->_socketHandler)){
    			$this->_responseBody.=fgetc($this->_socketHandler);
    		}
    	}
    	if($this->_enableCookies==true){
    		if(!isset($_SESSION['KHC'][$this->_host])){
    			$_SESSION['KHC'][$this->_host] = $this->getResponseCookies();
    		}
    	}
	}

	/**
	 * Devuelve los headers recibidos de la petición
	 *
	 * @return array
	 */
	public function getResponseHeaders(){
		return $this->_responseHeaders;
	}

	/**
	 * Devuelve el cuerpo de la respuesta HTTP
	 *
	 * @return string
	 */
	public function getResponseBody(){
		return $this->_responseBody;
	}

	/**
	 * Devuelve el código de la respuesta HTTP
	 *
	 * @return string
	 */
	public function getResponseCode(){
		return $this->_responseCode;
	}

	/**
	 * Devuelve las COOKIES enviadas por el servidor
	 *
	 * @return array
	 */
	public function getResponseCookies(){
		if(isset($this->_responseHeaders['Set-Cookie'])){
			$responseCookies = array();
			$notCookie = array('path' => 1, 'expires' => 1, 'domain' => 1, 'secure' => 1);
			$cookies = explode('; ', $this->_responseHeaders['Set-Cookie']);
			foreach($cookies as $cookie){
				$cook = explode('=', $cookie);
				if(!isset($notCookie[$cook[0]])){
					if(isset($cook[1])){
						$responseCookies[$cook[0]] = $cook[1];
					} else {
						$responseCookies[$cook[0]] = null;
					}
				}
			}
			return $responseCookies;
		}
		return array();
	}

	/**
	 * Habilita el envio automático de las cookies recibidas
	 *
	 * @param boolean $enableCookies
	 */
	public function enableCookies($enableCookies){
		$this->_enableCookies = $enableCookies;
	}

}
