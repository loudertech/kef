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
 * @package		BusinessProcess
 * @subpackage	Operation
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: BusinessOperation.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

require KEF_ABS_PATH.'Library/Kumbia/BusinessProcess/Operation/BusinessOperationException.php';

/**
 * BusinessOperation
 *
 * Componente para la creacion de Procesos de Negocio (BPL)
 *
 * @category	Kumbia
 * @package		BusinessProcess
 * @subpackage	Operation
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class BusinessOperation {

	/**
	 * Parámetros de la accion
	 *
	 * @var array
	 */
	private $_actionParameters;

	/**
	 * URI de la Peticion
	 *
	 * @var string
	 */
	private $_uri;

	/**
	 * Respuesta de la Operacion
	 *
	 * @var BusinessOperationResponse
	 */
	private $_operationResponse;

	/**
	 * Objeto de comunicaciones HTTP
	 *
	 * @var HttpRequest
	 */
	static private $_http;

	/**
	 * Constructor de BusinessOperation
	 *
	 * @param string $controller
	 * @param string $action
	 * @param string $adapter
	 */
	public function __construct($controller, $action="", $adapter=''){
		$this->_uri = "/$controller";
		if($action!=""){
			$this->_uri.="/$action";
		}
	}

	/**
	 * Establece los parametros de la accion a ejecutar
	 *
	 * @param array $parameters
	 */
	public function setActionParams(array $parameters){
		$this->_actionParameters = $parameters;
	}

	/**
	 * Realiza la peticion y obtiene la respuesta
	 *
	 */
	public function perform(){
		$url = "http://localhost/".$this->_uri."/";
		if(count($this->_actionParameters)){
			$url.=join("/", $this->_actionParameters);
		}
		if(self::$_http==null){
			self::$_http = new HttpRequest($url);
			self::$_http->enableCookies();
		} else {
			self::$_http->setURL($url);
		}
		self::$_http->setHeaders(array(
			"X-Accept-Content" => "text/xml"
		));
		self::$_http->send();
		$headers = self::$_http->getResponseHeader();
		if(isset($headers['X-Application-State'])){
			if($headers['X-Application-State']=='Exception'){
				$xmlException = self::$_http->getResponseBody();
				$xml = new DOMDocument();
				if($xml->loadXML($xmlException)==false){
					print $xmlException;
					exit;
				} else {
					print " -> Excepción ejecutando: {$this->_uri}\n";
					$type = $xml->getElementsByTagName('type')->item(0)->nodeValue;
					$message = $xml->getElementsByTagName('message')->item(0)->nodeValue;
					$file = $xml->getElementsByTagName('file')->item(0)->nodeValue;
					$line = $xml->getElementsByTagName('line')->item(0)->nodeValue;
					print " -> Exception: ".$type."\n";
					print " -> Message: $message\n";
					print " -> Archivo: ".$file."\n";
					print " -> Línea: ".$line."\n";
					exit;
				}
			}
		}
	}

	public function getLocation(){
		$headers = self::$_http->getResponseHeader();
		if(isset($headers['X-Application-Location'])){
			return $headers['X-Application-Location'];
		} else {
			return "";
		}
	}

	/**
	 * Devuelve el Objeto OperationResponse
	 *
	 * @return BusinessOperationResponse
	 */
	public function getResponse(){
		if(!$this->_operationResponse){
			$this->_operationResponse = new BusinessOperationResponse(self::$_http->getResponseBody());
		}
		return $this->_operationResponse;
	}

	public function getResponseXMLData(){
		$headers = self::$_http->getResponseHeader();
		if($headers['Content-Type']=='text/xml'){
			$xmlResponse = self::$_http->getResponseBody();
			$xml = new DOMDocument();
			if($xml->loadXML($xmlResponse)==false){
				throw new BusinessOperationException("El servidor ha generado una respuesta XML. pero no se pudo evaluar(1)");
			} else {
				$exists = false;
				foreach($xml->getElementsByTagName('data') as $element){
					$value = $element->textContent;
					$exists = true;
				}
				if($exists==false){
					throw new BusinessOperationException("Se obtuvo una respuesta XML pero no es procesable");
				}
				return $value;
			}
		} else {
			print self::$_http->getResponseBody();
			throw new BusinessOperationException("No se pudo evaluar la respuesta XML (2)");
		}
	}

}
