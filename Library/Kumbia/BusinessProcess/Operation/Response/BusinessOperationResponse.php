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
 */

/**
 * BusinessOperationResponse
 *
 * Obtiene los datos de la respuesta a una operación de negocios
 *
 * @category	Kumbia
 * @package		BusinessProcess
 * @subpackage	Operation
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class BusinessOperationResponse {

	/**
	 * Código respuesta HTML
	 *
	 * @var string
	 */
	private $_htmlResponse;

	/**
	 * Objeto DOM del arbol HTML
	 *
	 * @var DOMDocument
	 */
	private $_htmlDOM;

	/**
	 * Objeto XPath
	 *
	 * @var DOMXPath
	 */
	private $_xpath;

	/**
	 * Constructor de BusinessOperationResponse
	 *
	 * @param string $htmlResponse
	 */
	public function __construct($htmlResponse){
		$this->_htmlResponse = $htmlResponse;
		$this->_htmlDOM = new DOMDocument();
		if($this->_htmlDOM->loadHTML($this->_htmlResponse)==false){
			$this->_htmlDOM = false;
		}
	}

	/**
	 * Crear/Obtener el objeto XPATH
	 *
	 * @return DOMXPath
	 */
	private function _getXPath(){
		if(!$this->_xpath){
			if($this->_htmlDOM){
				$this->_xpath = new DOMXPath($this->_htmlDOM);
			} else {
				throw new BusinessOperationException("No se puede buscar en el documento HTML porque contiene código inválido");
			}
		}
		return $this->_xpath;
	}

	/**
	 * Devuelve elementos de cierto tipo por
	 *
	 * @param string $element
	 * @param string $className
	 * @return string
	 */
	public function getElementsByClass($className){
		$xpath = $this->_getXPath();
		return $xpath->query("//*[@class='$className']");
	}

}
