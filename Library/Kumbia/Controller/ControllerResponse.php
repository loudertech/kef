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
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ControllerResponse.php 101 2009-10-08 03:49:31Z gutierrezandresfelipe $
 */

/**
 * ControllerResponse
 *
 * Esta clase encapusula toda la información de la respuesta HTTP
 * del controlador
 *
 * @category	Kumbia
 * @package		Controller
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class ControllerResponse extends Object {

	/**
	 * Respuesta normal
	 *
	 */
	const RESPONSE_NORMAL = 0;

	/**
	 * Indica que se debe generar la presentacion mediante un adaptador de respuesta
	 *
	 */
	const RESPONSE_OTHER = 1;

	/**
	 * Tipo de respuesta que generara la peticion
	 *
	 * @var integer
	 */
	private $_responseType = 0;

	/**
	 * Adaptador utilizado para presentar la vista
	 *
	 * @var string
	 */
	private $_responseAdapter;

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
	 * @access 	public
	 * @return 	ControllerResponse
	 * @static
	 */
	public static function getInstance(){
		if(self::$_instance===null){
			self::$_instance = new ControllerResponse();
		}
		return self::$_instance;
	}

	/**
	 * Resetea la respuesta para generar una nueva
	 *
	 * @static
	 */
	public static function resetResponse(){
		if(self::$_instance!==null){
			self::$_instance->_responseType = 0;
			self::$_instance->_responseAdapter = null;
		}
	}

	/**
	 * Establece el valor de un encabezado
	 *
	 * @access 	public
	 * @param 	string $header
	 * @param 	boolean $replace
	 */
	public function setHeader($header, $replace=false){
		if(Core::isHurricane()==false){
			if(Core::isTestingMode()<Core::TESTING_LOCAL){
				header($header, $replace);
			}
		} else {
			$header = explode(': ', $header);
			HurricaneServer::setHeader($header[0], $header[1]);
		}
	}

	/**
	 * Devuelve los encabezados que serán enviados en la petición
	 *
	 * @access 	public
	 * @param 	boolean $process
	 * @return 	array
	 */
	public function getHeaders($process=true){
		if($process==true){
			$list = headers_list();
			$headers = array();
			foreach($list as $header){
				if(preg_match('/([a-zA-Z\-]+): (.+)/', $header, $matches)){
					$headers[$matches[1]] = $matches[2];
				} else {
					$headers[] = $header;
				}
			}
			return $headers;
		} else {
			return headers_list();
		}
	}

	/**
	 * Indica si ya se ha enviado un determinado encabezado
	 *
	 * @param	string $headerName
	 * @return	boolean
	 */
	public function hasHeader($headerName){
		$headers = $this->getHeaders();
		return isset($headers[$headerName]);
	}

	/**
	 * Tipo de respuesta que generara la peticion
	 *
	 * @access	public
	 * @param	integer $type
	 */
	public function setResponseType($type){
		$this->_responseType = $type;
	}

	/**
	 * Devuelve el tipo de respuesta que generara la petición
	 *
	 * @access	public
	 * @return	integer
	 */
	public function getResponseType(){
		return $this->_responseType;
	}

	/**
	 * Establece el tipo de adaptador
	 *
	 * @param	string $adapter
	 */
	public function setResponseAdapter($adapter){
		$this->_responseAdapter = $adapter;
	}

	/**
	 * Devuelve el adaptador usado para la respuesta
	 *
	 * @return	string
	 */
	public function getResponseAdapter(){
		return $this->_responseAdapter;
	}

	/**
	 * Establece el tipo de salida generado por el controlador
	 *
	 * @param	string $contentType
	 */
	public function setContentType($contentType){
		$this->setHeader('Content-Type: '.$contentType);
	}

}
