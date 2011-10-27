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
 * @package 	Xml
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright  	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Xml.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * SimpleXMLResponse
 *
 * Permite generar salidas en XML
 *
 * @category 	Kumbia
 * @package 	Xml
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @access 		public
 */
class SimpleXMLResponse extends Object {

	/**
	 * Codigo XML de la salida
	 *
	 * @var DOMDocument
	 */
	private $_domDocument;

	/**
	 * Raiz del Documento XML
	 *
	 * @var DOMElement
	 */
	private $_rootElement;

	/**
	 * Constructor de la clase
	 *
	 */
	public function __construct(){
		$this->_domDocument = new DOMDocument('1.0', 'UTF-8');
		$this->_rootElement = $this->_domDocument->createElement('response');
		$this->_domDocument->appendChild($this->_rootElement);
	}

	/**
	 * Agrega un nodo a la respuesta XML
	 *
	 * @param array $dataArray
	 */
	public function addNode($dataArray){
		$rowElement = new DOMElement('row');
		foreach($dataArray as $key => $value){
			$rowElement->setAttribute($key, $value);
		}
		$this->_rootElement->appendChild($rowElement);
	}

	/**
	 * Agrega datos CDDATA a la salida XML
	 *
	 * @param mixed $val
	 */
	public function addData($val){
		$cdDataElement = $this->_domDocument->createCDATASection((string)$val);
		$dataElement = $this->_domDocument->createElement('data');
		$dataElement->appendChild($cdDataElement);
		$this->_rootElement->appendChild($dataElement);
	}

	/**
	 * Imprime la salida directamente a la consola o navegador junto con los
	 *
	 * @access public
	 */
	public function outXMLResponse(){
		echo $this->_domDocument->saveXML();
	}

	/**
	 * Devuelve la salida XML como un string
	 *
	 * @access public
	 * @return string
	 */
	public function getXMLResponse(){
		return $this->_domDocument->saveXML();
	}
}
