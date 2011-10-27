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
 * Pecl_HTTPCommunicator
 *
 * Cliente para realizar peticiones HTTP usando la extensión de php pecl_http
 *
 * @category	Kumbia
 * @package 	Soap
 * @subpackage 	Adapters
 * @subpackage 	Client
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @abstract
 */
class Pecl_HTTPCommunicator {

	/**
	 * Host al que se le está realizando la petición
	 *
	 * @var string
	 */
	private $_host;

	/**
	 * Indica si se habilita el envío automático de las cookies recibidas
	 *
	 * @var boolean
	 */
	private $_enableCookies = false;

	/**
	 * Encabezados de la petición
	 *
	 * @var array
	 */
	private $_headers = array();

	/**
	 * Objeto de transporte HTTP
	 *
	 * @var HttpRequest
	 */
	private $_transport;

	/**
	 * Constructor del Pecl_HTTPCommunicator
	 *
	 * @param string $scheme
	 * @param string $host
	 * @param string $uri
	 * @param string $method
	 * @param int $port
	 */
	public function __construct($scheme, $host, $uri, $method, $port=80){
		$url = $scheme.'://'.$host.'/'.$uri;
		if($method=='POST'){
			$this->_transport = new HttpRequest($url, HttpRequest::METH_POST);
		} else {
			$this->_transport = new HttpRequest($url, HttpRequest::METH_GET);
		}
		$this->_host = $host;
	}

	/**
	 * Establece un encabezado HTTP a la petición
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function addHeader($name, $value){
		$this->_headers[$name] = $value;
	}

	/**
	 * Establece varios encabezados HTTP
	 *
	 * @param array $headers
	 */
	public function setHeaders($headers){
		foreach($headers as $name => $value){
			$this->_headers[$name] = $value;
		}
	}

	/**
	 * Establece el cuerpo HTTP de la petición
	 *
	 * @param string $rawBody
	 */
	public function setRawPostData($rawBody){
		return $this->_transport->setBody($rawBody);
	}

	/**
	 * Envía la petición
	 *
	 */
	public function send(){
		if($this->_enableCookies==true){
    			if(isset($_SESSION['KHC'][$this->_host])){
    				$this->_transport->setCookies($_SESSION['KHC'][$this->_host]);
    			}
		}
		$this->_transport->setOptions(array('timeout' => 60, 'connecttimeout' => 30, 'dns_cache_timeout' => 30));
		$this->_transport->setHeaders($this->_headers);
		for($i=0;$i<3;$i++){
			$success = false;
			try {
				$this->_transport->send();
				$success = true;
			}
			catch(HttpInvalidParamException $e){
				if($i==2){
					throw $e;
				}
			}
		}
		if($this->_enableCookies==true){
			$cookies = $this->_transport->getResponseCookies();
			if(count($cookies)){
		    	$_SESSION['KHC'][$this->_host] = $cookies[0]->cookies;
			}
    	}
	}

	/**
	 * Devuelve el código HTTP de la respuesta a la petición
	 *
	 * @return int
	 */
	public function getResponseCode(){
		return $this->_transport->getResponseCode();
	}

	/**
	 * Devuelve el cuerpo HTTP de la respuesta a la petición
	 *
	 * @return string
	 */
	public function getResponseBody(){
		return $this->_transport->getResponseBody();
	}

	/**
	 * Habilita el envio automático de las cookies recibidas
	 *
	 * @param boolean $enableCookies
	 */
	public function enableCookies($enableCookies){
		$this->_enableCookies = $enableCookies;
		if($enableCookies==true){
			$this->_transport->enableCookies();
		}
	}

	/**
	 * Graba en sesión las cookies de la petición
	 *
	 */
	public function getResponseCookies(){
		if($this->_enableCookies==true){
			$cookies = $this->_transport->getResponseCookies();
			if(count($cookies)){
		    	$_SESSION['KHC'][$this->_host] = $cookies[0]->cookies;
			}
    	}
	}

}

