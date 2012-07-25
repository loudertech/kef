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
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Soap.php 123 2010-02-17 13:57:59Z gutierrezandresfelipe $
 */

/**
 * Soap
 *
 * Clase que administra el SoapServer y las excepciones SoapFault
 *
 * @category 	Kumbia
 * @package 	Soap
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @abstract
 */
abstract class Soap {

	/**
	 * Namespace de nodos Envelope
	 *
	 * @var string
	 * @staticvar
	 */
	private static $_envelopeNS = 'http://www.w3.org/2003/05/soap-envelope';

	/**
	 * Namespace para información de SoapFaults
	 *
	 * @var string
	 */
	private static $_faultsNS = 'http://schemas.loudertechnology.com/general/soapFaults';

	/**
	 * Namespace del XML Schema Instance (xsi)
	 *
	 * @var string
	 */
	private static $_xmlSchemaInstanceNS = 'http://www.w3.org/2001/XMLSchema-instance';

	/**
	 * DOMDocument Base
	 *
	 * @var DOMDocument
	 * @staticvar
	 */
	private static $_domDocument;

	/**
	 * Nodo Raiz de la respuesta SOAP
	 *
	 * @var DOMElement
	 */
	private static $_rootElement;

	/**
	 * Nodo Body de la respuesta SOAP
	 *
	 * @var DOMElement
	 */
	private static $_bodyElement;

	/**
	 * Crea un Envelope SOAP apto para SoapFaults y Respuestas
	 *
	 * @access private
	 * @return DOMElement
	 * @static
	 */
	static private function _createSOAPEnvelope(){
		self::$_domDocument = new DOMDocument('1.0', 'UTF-8');
		self::$_rootElement = self::$_domDocument->createElementNS(self::$_envelopeNS, 'SOAP-ENV:Envelope');
		self::$_domDocument->appendChild(self::$_rootElement);
		self::$_bodyElement = new DOMElement('Body', '', self::$_envelopeNS);
		self::$_rootElement->appendChild(self::$_bodyElement);
		return self::$_bodyElement;
	}

	/**
	 * Administra el objeto SoapServer y genera la respuesta SOAP
	 *
	 * @access public
	 * @param mixed $controller
	 * @static
	 */
	static public function serverHandler($controller){

		if(isset($_SERVER['HTTP_SOAPACTION'])){
			$soapAction = str_replace('"', '', $_SERVER['HTTP_SOAPACTION']);
		} else {
			if(isset($_SERVER['CONTENT_TYPE'])){
				if(preg_match('/action="(.+)"/', $_SERVER['CONTENT_TYPE'], $matches)){
					$soapAction = $matches[1];
				}
			} else {
				throw new SoapException('No se indicó la acción SOAP a ejecutar');
			}
		}

		$response = ControllerResponse::getInstance();
		$response->setContentType('application/soap+xml; charset=utf-8');
		$soapAction = explode('#', $soapAction); ;
		$serviceNamespace = $soapAction[0];
		$bodyElement = self::_createSOAPEnvelope();

		//Service Namespace
		$attributeNS = new DOMAttr('xmlns:ns1', $serviceNamespace);
		self::$_rootElement->setAttributeNodeNS($attributeNS);

		//XSI Namespace
		$attributeNS = new DOMAttr('xmlns:xsi', self::$_xmlSchemaInstanceNS);
		self::$_rootElement->setAttributeNodeNS($attributeNS);

		//SOAP-ENC Namespace
		$attributeNS = new DOMAttr('xmlns:SOAP-ENC', 'http://schemas.xmlsoap.org/soap/encoding/');
		self::$_rootElement->setAttributeNodeNS($attributeNS);

		//XSD Namespace
		$attributeNS = new DOMAttr('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
		self::$_rootElement->setAttributeNodeNS($attributeNS);

		//NS2 Namespace
		$attributeNS = new DOMAttr('xmlns:ns2', 'http://xml.apache.org/xml-soap');
		self::$_rootElement->setAttributeNodeNS($attributeNS);

		self::$_rootElement->setAttributeNS(self::$_envelopeNS, 'encondingStyle', 'http://schemas.xmlsoap.org/soap/encoding/');

		$responseElement = self::$_domDocument->createElement('ns1:'.$soapAction[1].'Response');

		$valueReturned = Dispatcher::getValueReturned();
		$dataEncoded = self::_getDataEncoded($valueReturned, 'return');
		if($dataEncoded!=null){
			$responseElement->appendChild($dataEncoded);
		}
		$bodyElement->appendChild($responseElement);

		$xmlResponse = self::$_domDocument->saveXML();
		$controllerResponse = ControllerResponse::getInstance();
		$controllerResponse->setHeader('Content-Length: '.i18n::strlen($xmlResponse), true);
		echo $xmlResponse;

	}

	/**
	 * Devuelve el tipo de dato XSD de acuerdo al tipo de dato Nativo en PHP
	 *
	 * @param 	string $nativeDataType
	 * @return 	string
	 */
	private static function _getDataXSD($nativeDataType){
		if($nativeDataType=='int'){
			return 'int';
		}
		return 'ur-type';
	}

	/**
	 * Formatea el valor devuelto por el metodo accion en el controlador
	 * usando el tipo de dato SOAP adecuado
	 *
	 * @access	private
	 * @param	mixed $valueReturned
	 * @param 	$nodeType return
	 * @return	DOMElement
	 * @static
	 */
	private static function _getDataEncoded($valueReturned=null, $nodeType='return'){
		if(!is_array($valueReturned)){
			if(is_resource($valueReturned)){
				throw new SoapException('Los recursos no pueden ser enviados como parte de un mensaje SOAP');
			}
			$element = self::$_domDocument->createElement($nodeType, $valueReturned);
			if(is_integer($valueReturned)==true){
				$element->setAttribute('xsi:type', 'xsd:int');
				$element->nodeValue = $valueReturned;
			} else {
				if(is_string($valueReturned)==true){
					$element->setAttribute('xsi:type', 'xsd:string');
					$element->nodeValue = $valueReturned;
				} else {
					if(is_float($valueReturned)==true){
						$element->setAttribute('xsi:type', 'xsd:float');
						$element->nodeValue = $valueReturned;
					} else {
						if(is_bool($valueReturned)==true){
							$element->setAttribute('xsi:type', 'xsd:boolean');
							if($valueReturned===false){
								$stringValue = 'false';
							} else {
								$stringValue = 'true';
							}
							$element->nodeValue = $stringValue;
						}
					}
				}
			}
			return $element;
		} else {
			$element = self::$_domDocument->createElement($nodeType);
			$dataType = '';
			$oldDataType = '';
			$associativeArray = false;
			$numberKey = 0;
			foreach($valueReturned as $key => $value){
				if($dataType!='mixed'){
					$dataType = gettype($value);
					if(!$oldDataType){
						$oldDataType = $dataType;
					} else {
						if($dataType!=$oldDataType){
							$dataType = 'mixed';
						}
						$oldDataType = $dataType;
					}
				}
				if($associativeArray==false){
					if($numberKey!==$key){
						$associativeArray = true;
					}
					$numberKey++;
				}
			}
			if($associativeArray==false){
				if($dataType=='mixed'){
					$element->setAttribute('SOAP-ENC:arrayType', 'xsd:ur-type['.count($valueReturned).']');
				} else {
					$element->setAttribute('SOAP-ENC:arrayType', 'xsd:'.self::_getDataXSD($dataType).'['.count($valueReturned).']');
				}
				$element->setAttribute('xsi:type', 'SOAP-ENC:Array');
			} else {
				$element->setAttribute('xsi:type', 'ns2:Map');
			}
			$returnString = '';
			foreach($valueReturned as $key => $value){
				if($associativeArray==false){
					$element->appendChild(self::_getDataEncoded($value, 'item'));
				} else {
					$itemElement = self::$_domDocument->createElement('item');
					$itemElement->appendChild(self::_getDataEncoded($key, 'key'));
					$itemElement->appendChild(self::_getDataEncoded($value, 'value'));
					$element->appendChild($itemElement);
				}
			}
			return $element;
		}
	}

	/**
	 * Genera las fault exceptions del Servidor SOAP
	 *
	 * @access 	public
	 * @param 	Exception $e
	 * @param 	mixed $controller
	 * @static
	 */
	static public function faultSoapHandler($e, $controller){

		//Genera una respuesta HTTP de error
		$controllerResponse = ControllerResponse::getInstance();
		$controllerResponse->setHeader('X-Application-State: Exception', true);
		$controllerResponse->setHeader('HTTP/1.1 500 Application Exception', true);
		$controllerResponse->setContentType('application/soap+xml; charset=utf-8');

		$faultMessage = str_replace('\n', '', html_entity_decode($e->getMessage(), ENT_COMPAT, 'UTF-8'));
		$controllerResponse->setResponseType(ControllerResponse::RESPONSE_OTHER);
		$controllerResponse->setResponseAdapter('soap');
		$bodyElement = self::_createSOAPEnvelope();
		self::$_domDocument->createAttributeNS(self::$_faultsNS, 'fault:dummy');
		$faultElement = new DOMElement('Fault', '', self::$_envelopeNS);
		$bodyElement->appendChild($faultElement);

		//SOAP 1.1
		#$faultElement->appendChild(new DOMElement('faultcode', 'Server'));
		#$faultElement->appendChild(new DOMElement('faultstring', $faultMessage));

		//Código de la excepcion
		$codeElement = new DOMElement('Code', '', self::$_envelopeNS);
		$faultElement->appendChild($codeElement);

		if(get_class($e)=='SoapException'){
			$faultCode = $e->getFaultCode();
		} else {
			$faultCode = 'Receiver';
		}
		$codeValue = new DOMElement('Value', 'SOAP-ENV:'.$faultCode, self::$_envelopeNS);
		$codeElement->appendChild($codeValue);

		//Motivo de la excepcion
		$reasonElement = new DOMElement('Reason', '', self::$_envelopeNS);
		$faultElement->appendChild($reasonElement);
		$reasonText = new DOMElement('Text', $e->getMessage(), self::$_envelopeNS);
		$reasonElement->appendChild($reasonText);

		//Idioma del mensaje
		$locale = Locale::getApplication();
		$reasonText->setAttribute('xml:lang', $locale->getRFC4646String());

		//Subcodigo de la excepcion
		$subcodeElement = new DOMElement('Subcode', '', self::$_envelopeNS);
		$codeElement->appendChild($subcodeElement);
		$subcodeValue = new DOMElement('Value', 'fault:'.get_class($e), self::$_envelopeNS);
		$subcodeElement->appendChild($subcodeValue);

		//Detalle de la excepción
		$detailElement = new DOMElement('Detail', '', self::$_envelopeNS);
		$faultElement->appendChild($detailElement);
		$faultType = new DOMElement('Type', get_class($e), self::$_faultsNS);
		$faultCode = new DOMElement('Code', $e->getCode(), self::$_faultsNS);
		$faultTime = new DOMElement('Time', @date('r'), self::$_faultsNS);
		$faultFile = new DOMElement('File', $e->getSafeFile(), self::$_faultsNS);
		$faultLine = new DOMElement('Line', $e->getLine(), self::$_faultsNS);

		$detailElement->appendChild($faultType);
		$detailElement->appendChild($faultCode);
		$detailElement->appendChild($faultFile);
		$detailElement->appendChild($faultLine);
		$detailElement->appendChild($faultTime);

		//Remote backtrace
		$config = CoreConfig::readAppConfig();
		if(isset($config->application->debug)&&$config->application->debug){

			//Backtrace
			$faultBacktrace = new DOMElement('Backtrace', '', self::$_faultsNS);
			$detailElement->appendChild($faultBacktrace);
			if(is_subclass_of($e, 'CoreException')){
				$backtrace = $e->getCompleteTrace();
			} else {
				$backtrace = $e->getTrace();
			}
			foreach($backtrace as $trace){

				$faultTrace = new DOMElement('Trace', '', self::$_faultsNS);
				$faultBacktrace->appendChild($faultTrace);

				if(isset($trace['file'])){
					$faultFile = new DOMElement('File', CoreException::getSafeFilePath($trace['file']), self::$_faultsNS);
					$faultTrace->appendChild($faultFile);
				}
				if(isset($trace['line'])){
					$faultLine = new DOMElement('Line', $trace['line'], self::$_faultsNS);
					$faultTrace->appendChild($faultLine);
				}
				if(!isset($trace['class'])){
					$trace['class'] = '';
					$trace['type'] = '';
				}
				if(!isset($trace['function'])){
					$trace['function'] = '';
				}
				$functionLocation = $trace['class'].$trace['type'].$trace['function'];
				$faultFunction = new DOMElement('Function', $functionLocation, self::$_faultsNS);
				$faultTrace->appendChild($faultFunction);
			}
		}

		$xmlResponse = self::$_domDocument->saveXML();
		$controllerResponse = ControllerResponse::getInstance();
		$controllerResponse->setHeader('Content-Length: '.i18n::strlen($xmlResponse), true);
		echo $xmlResponse;
	}

}
