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
 * @subpackage 	Client
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: WebServiceClient.php 123 2010-02-17 13:57:59Z gutierrezandresfelipe $
 */

class WebServiceClient2 extends SoapClient {

	public function __construct($options){
		$options['uri'] = 'http://app-services';
		$options['encoding'] = 'UTF-8';
		//$options['soap_version'] = SOAP_1_2;
		parent::__construct(null, $options);
	}

}

/**
 * WebServiceClient
 *
 * Cliente para invocar servicios Web
 *
 * @category	Kumbia
 * @package 	Soap
 * @subpackage 	Client
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class WebServiceClient {

	/**
	 * Namespace de nodos Envelope
	 *
	 * @var string
	 * @staticvar
	 */
	private static $_envelopeNS = 'http://www.w3.org/2003/05/soap-envelope';

	/**
	 * Namespace del XML Schema Instance (xsi)
	 *
	 * @var string
	 */
	private static $_xmlSchemaInstanceNS = 'http://www.w3.org/2001/XMLSchema-instance';

	/**
	 * Namespace para información de SoapFaults
	 *
	 * @var string
	 */
	private static $_faultsNS = 'http://schemas.loudertechnology.com/general/soapFaults';

	/**
	 * DOMDocument Base
	 *
	 * @var DOMDocument
	 */
	private $_domDocument;

	/**
	 * Transporte usado para generar las peticiones
	 *
	 * @var Http
	 */
	private $_transport;

	/**
	 * Opciones del servicio
	 *
	 * @var array
	 */
	private $_options = array();

	/**
	 * Constructor del cliente del Servicio
	 *
	 * @param string $wsdl
	 * @param array $options
	 */
	public function __construct($options){
		if(!is_array($options)){
			$options = array(
				'wsdl' => null,
				'location' => $options,
				'actor' => $options
			);
		}
		if(!isset($options['wsdl'])){
			$options['wsdl'] = null;
		}
		if(!isset($options['uri'])){
			$options['uri'] = 'http://app-services';
		}
		if(!isset($options['actor'])){
			$options['actor'] = $options['uri'];
		}
		if(!isset($options['encoding'])){
			$options['encoding'] = 'UTF-8';
		}
		if(!isset($options['compression'])){
			if(extension_loaded('soap')){
				$options['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP;
			} else {
				$options['compression'] = 32;
			}
		}
		$this->_options = $options;
		$this->_transport = $this->_getHTTPTransport($options['location']);
		$this->_addHeaders();
	}

	/**
	 * Agrega los encabezados a la petición
	 *
	 */
	private function _addHeaders(){
		$headers = array(
			'Host' => 'HTTP_HOST',
			'Connection' => 'HTTP_CONNECTION',
			'Accept-Encoding' => 'HTTP_ACCEPT_ENCODING'
		);
		$transportHeaders = array();
		foreach($headers as $headerName => $serverIndex){
			if(isset($_SERVER[$serverIndex])){
				$transportHeaders[$headerName] = $_SERVER[$serverIndex];
			}
		}
		$transportHeaders['User-Agent'] = 'KEF/PHP/SOAP '.Core::FRAMEWORK_VERSION.'/'.PHP_VERSION;
		$transportHeaders['Content-Type'] = 'text/xml; charset=utf-8';
		$this->_transport->setHeaders($transportHeaders);
	}

	/**
	 * Establece una Cookie de la petición
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function __setCookie($name, $value){
		$this->_transport->setCookies(array($name => $value));
	}

	/**
	 * Obtiene el transporte HTTP
	 *
	 * @param 	string $url
	 * @return 	HTTPTransport
	 */
	private function _getHTTPTransport($url){
		if(!isset($this->_options['communicator'])){
			$adapterName = 'Sockets';
		} else {
			$adapterName = $this->_options['communicator'];
		}
		$className = $adapterName.'Communicator';
		if(class_exists($className, false)==false){
			require KEF_ABS_PATH.'Library/Kumbia/Soap/Client/Adapters/'.$adapterName.'.php';
		}
		$uri = new HttpUri($url);
		$transport = new $className($uri->getSchema(), $uri->getHostname(), $uri->getUri(), 'POST', $uri->getPort());
		$transport->enableCookies(true);
		return $transport;
	}

	/**
	 * Devuelve el tipo de dato XSD de acuerdo
	 *
	 * @param 	string $type
	 * @return	string
	 */
	private function _getXSDTypeByCode($type){
		switch($type){
			case 'integer':
				return 'xsd:int';
				break;
			case 'string':
				return 'xsd:string';
				break;
			case 'double':
			case 'float':
				return 'xsd:float';
				break;
		}
		return $type;
	}

	/**
	 * Agrega un parámetro a la petición SOAP
	 *
	 * @param	int $n
	 * @param	string $param
	 */
	private function _encodeItem($nodeType, $param){
		if(is_resource($param)){
			throw new SoapException('Los recursos no pueden ser enviados como parte de un mensaje SOAP');
		}
		if(!is_array($param)&&!is_object($param)){
			if(is_integer($param)){
				return '<'.$nodeType.' xsi:type="xsd:int">'.$param.'</'.$nodeType.'>';
			} else {
				if(is_string($param)){
					$param = htmlspecialchars($param, ENT_NOQUOTES, 'UTF-8');
					return '<'.$nodeType.' xsi:type="xsd:string">'.$param.'</'.$nodeType.'>';
				} else {
					if(is_float($param)){
						return '<'.$nodeType.' xsi:type="xsd:float">'.$param.'</'.$nodeType.'>';
					} else {
						if(is_bool($param)){
							if($param==true){
								$nodeString = 'true';
							} else {
								$nodeString = 'false';
							}
							return '<'.$nodeType.' xsi:type="xsd:boolean">'.$nodeString.'</'.$nodeType.'>';
						}
					}
				}
			}
		} else {
			$itemNodeXML = '';
			$selfType = null;
			$type = null;
			$isArray = is_array($param);

			$numberElement = 0;
			$asociativeArray = false;
			foreach($param as $keyName => $item){
				if($asociativeArray==false){
					$asociativeArray = true;
				}
			}

			foreach($param as $keyName => $item){
				if($isArray==true){
					if($asociativeArray==false){
						$itemNodeXML.=$this->_encodeItem('item', $item);
					} else {
						$itemNodeXML.='<item>';
						$itemNodeXML.=$this->_encodeItem('key', $keyName);
						$itemNodeXML.=$this->_encodeItem('value', $item);
						$itemNodeXML.='</item>';
					}
				} else {
					$itemNodeXML.=$this->_encodeItem($keyName, $item);
				}
				if($selfType===null){
					$type = gettype($item);
					$selfType = true;
				} else {
					if($selfType===true){
						if($type!=gettype($item)){
							$selfType = false;
						}
					}
				}
				$numberElement++;
			}
			if($selfType==true){
				$xsdType = $this->_getXSDTypeByCode($type);
			} else {
				$xsdType = 'xsd:ur-type';
			}
			if($isArray==true){
				if($asociativeArray==false){
					return '<'.$nodeType.' SOAP-ENC:arrayType="'.$xsdType.'['.count($param).']" xsi:type="SOAP-ENC:Array">'.$itemNodeXML.'</'.$nodeType.'>';
				} else {
					return '<'.$nodeType.' xsi:type="ns2:Map">'.$itemNodeXML.'</'.$nodeType.'>';
				}
			} else {
				return '<'.$nodeType.' xsi:type="SOAP-ENC:Struct">'.$itemNodeXML.'</'.$nodeType.'>';
			}
		}
	}

	/**
	 * Devuelve true si el XSD corresponde a un literal
	 *
	 * @access	private
	 * @param	string $xsdDataType
	 */
	private function _isTypeLiteral($xsdDataType){
		return in_array($xsdDataType, array('xsd:string', 'xsd:boolean', 'xsd:int', 'xsd:float'));
	}

	/**
	 * Agrega el SOAP Envelope a un Mensaje SOAP
	 *
	 * @param 	string $message
	 * @return	string
	 */
	private function _createMessageEnvelope($message){
		return '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="'.self::$_envelopeNS.'" xmlns:ns1="'.$this->_options['uri'].'" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"  xmlns:ns2="http://xml.apache.org/xml-soap" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body>'.$message.'</SOAP-ENV:Body></SOAP-ENV:Envelope>';
	}

	/**
	 * Realiza el llamado a una función del servicio
	 *
	 * @param	string $method
	 * @param	array $arguments
	 * @return 	mixed
	 */
	public function __call($method, $arguments){

		$this->_transport->addHeader('SOAPAction', '"'.$this->_options['uri'].'#'.$method.'"');
		$n = 0;
		$messageRequest = '<ns1:'.$method.'>';
		foreach($arguments as $argument){
			$messageRequest.=$this->_encodeItem('param'.$n, $argument);
			++$n;
		}
		$messageRequest.='</ns1:'.$method.'>';
		$this->_transport->setRawPostData($this->_createMessageEnvelope($messageRequest));
		$this->_transport->send();

		$responseBody = $this->_transport->getResponseBody();
		$responseCode = $this->_transport->getResponseCode();
		return $this->_processResponse($method, $responseCode, $responseBody);
	}

	/**
	 * Procesa la respuesta XML
	 *
	 * @param 	string $method
	 * @param	int $responseCode
	 * @param	string $responseBody
	 * @return	mixed
	 */
	private function _processResponse($method, $responseCode, &$responseBody){
		if($responseCode>=200&&$responseCode<300){
			return $this->_bindResponseData($method, $responseBody);
		} else {
			if($responseCode>=400&&$responseCode<600){
				$this->_throwSoapFault($responseBody);
			}
		}
	}

	/**
	 * Analiza la respuesta SOAP y la convierte en datos nativos PHP
	 *
	 * @param	string $responseBody
	 * @param	string $method
	 * @return	mixed
	 */
	private function _bindResponseData($method, $responseBody){
		if($responseBody!=''){
			$this->_domDocument = new DOMDocument();
			//file_put_contents('x.txt', $responseBody);
			$this->_domDocument->loadXML($responseBody);
			$localName = $method.'Response';
			$responseNodes = $this->_domDocument->getElementsByTagNameNS($this->_options['uri'], $localName);
			foreach($responseNodes as $element){
				foreach($element->getElementsByTagName('return') as $returnElement){
					return $this->_getNativeValue($returnElement);
				}
			}
		} else {
			throw new SoapException('El mensaje SOAP está vacio');
		}
	}

	/**
	 * Analiza el tipo XSI y devuelve un dato nativo PHP
	 *
	 * @param 	DOMElement $element
	 * @return	mixed
	 */
	private function _getNativeValue($element){
		$xsdDataType = $element->getAttribute('xsi:type');
		if($xsdDataType=='SOAP-ENC:Array'){
			return $this->_decodeSOAPArray($element);
		} else {
			if($xsdDataType=='ns2:Map'){
				return $this->_decodeSOAPMap($element);
			} else {
				return $this->_decodeXSDType($xsdDataType, $element->nodeValue);
			}
		}
	}

	/**
	 * Convierte el valor XSD a un valor nativo PHP
	 *
	 * @param	string $xsdDataType
	 * @param	mixed $returnValue
	 * @return	mixed
	 */
	private function _decodeXSDType($xsdDataType, $returnValue){
		switch($xsdDataType){
			case 'xsd:string':
				return (string) $returnValue;
				break;
			case 'xsd:int':
				return (int) $returnValue;
				break;
			case 'xsd:float':
				return (float) $returnValue;
				break;
			case 'xsd:boolean':
				return $returnValue=='true' ? true : false;
				break;
		}
	}

	/**
	 * Decodifica un SOAP-ENC:Array a un valor nativo PHP
	 *
	 * @param  $returnElement
	 * @return array
	 */
	private function _decodeSOAPArray($returnElement){
		$nativeArray = array();
		foreach($returnElement->childNodes as $element){
			if($element->localName=='item'){
				if($element->nodeType==1){
					$nativeArray[] = $this->_getNativeValue($element);
				} else {
					$nativeArray[] = $element->nodeValue;
				}
			}
		}
		return $nativeArray;
	}

	/**
	 * Decodifica un ns2:Map asociativo a un valor nativo PHP
	 *
	 * @param  $returnElement
	 * @return array
	 */
	private function _decodeSOAPMap($returnElement){
		$nativeArray = array();
		foreach($returnElement->childNodes as $element){
			if($element->localName=='item'){
				$keyName = null;
				$value = null;
				foreach($element->childNodes as $itemElement){
					if($itemElement->localName=='key'){
						$keyName = $itemElement->nodeValue;
					} else {
						if($itemElement->localName=='value'){
							$value = $this->_getNativeValue($itemElement);
						}
					}
				}
				$nativeArray[$keyName] = $value;
			}
		}
		return $nativeArray;
	}

	/**
	 * Lanza una excepción
	 *
	 * @param string $responseBody
	 */
	private function _throwSoapFault($responseBody){
		$this->_transport->getResponseCookies();
		$this->_domDocument = new DOMDocument();
		$this->_domDocument->loadXML($responseBody);
		$exceptionClassName = '';
		$subcodeNode = $this->_domDocument->getElementsByTagNameNS(self::$_envelopeNS, 'Subcode');
		foreach($subcodeNode as $element){
			$subcodeParts = explode(':', $element->nodeValue);
			if(isset($subcodeParts[1])){
				$exceptionClassName = $subcodeParts[1];
			} else {
				$exceptionClassName = $subcodeParts[0];
			}
		}
		$exceptionMessage = '';
		$reasonNode = $this->_domDocument->getElementsByTagNameNS(self::$_envelopeNS, 'Reason');
		foreach($reasonNode as $element){
			foreach($element->childNodes as $child){
				if($child->localName=='Text'){
					$exceptionMessage = $child->nodeValue;
					break;
				}
			}
		}
		$remoteBacktrace = array();
		$faultDetails = $this->_domDocument->getElementsByTagNameNS(self::$_faultsNS, 'Backtrace');
		foreach($faultDetails as $backtrace){
			foreach($backtrace->childNodes as $trace){
				if($trace->localName=='Trace'){
					$remoteTrace = array();
					foreach($trace->childNodes as $detailNode){
						if($detailNode->localName=='File'){
							$remoteTrace['file'] = $detailNode->nodeValue;
						} else {
							if($detailNode->localName=='Line'){
								$remoteTrace['line'] = $detailNode->nodeValue;
							} else {
								if($detailNode->localName=='Function'){
									$remoteTrace['function'] = $detailNode->nodeValue;
								}
							}
						}
					}
					$remoteBacktrace[] = $remoteTrace;
 				}
			}
		}
		if(class_exists($exceptionClassName)){
			$exception = new $exceptionClassName($exceptionMessage,0);
			$exception->setRemote(true);
			$exception->setRemoteActor($this->_options['actor']);
			$exception->setRemoteTrace($remoteBacktrace);
			throw $exception;
		} else {
			throw new SoapException($exceptionClassName);
		}
	}

}
