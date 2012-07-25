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
 * @subpackage	Definition
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ProcessDefinition.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

require KEF_ABS_PATH.'Library/Kumbia/BusinessProcess/Definition/ProcessDefinitionException.php';

/**
 * ProcessDefinition
 *
 * Carga una definicion de un Proceso de Negocio
 *
 * @category	Kumbia
 * @package		BusinessProcess
 * @subpackage	Definition
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class ProcessDefinition {

	/**
	 * Arbol XML de Definicion
	 *
	 * @var DOMDocument
	 */
	private $_xml;

	/**
	 * Objeto XPath
	 *
	 * @var DOMXPath
	 */
	private $_xpath;

	/**
	 * Establece el arbol XML de definicion
	 *
	 * @param DOMDocument $xml
	 */
	public function setDefinition(DOMDocument $xml){
		$this->_xml = $xml;
	}

	/**
	 * Crea una definicion de proceso apartir de un archivo XML
	 *
	 * @param string $file
	 * @return ProcessDefinition
	 */
	public static function parseXMLFile($file){
		if(Core::fileExists($file)==true){
			$xml = new DOMDocument();
			if($xml->load($file)==false){
				throw new ProcessDefinitionException("El archivo de definición XML no es válido");
			} else {
				$exists = false;
				foreach($xml->getElementsByTagName("start-state") as $element){
					$exists = true;
				}
				if($exists==false){
					throw new ProcessDefinitionException("No se ha definido el start-state");
				}
				$process = new ProcessDefinition();
				$process->setDefinition($xml);
				return $process;
			}
		} else {
			throw new ProcessDefinitionException("No existe el archivo '$file'");
		}
	}

	/**
	 * Crea/devuelve el objeto XPath
	 *
	 * @return DOMXPath
	 */
	private function _createXPath(){
		if(!$this->_xpath){
			$this->_xpath = new DOMXPath($this->_xml);
		}
		return $this->_xpath;
	}

	/**
	 * Obtiene el start-state del proceso
	 *
	 * @return ProcessNode
	 */
	public function getStartState(){
		foreach($this->_xml->getElementsByTagName('start-state') as $element){
			$node = new ProcessNode($element->getAttribute('name'), 'start-state');
			$node->setAttributes($element->attributes);
			$node->setChildNodes($element->childNodes);
		}
		return $node;
	}

	/**
	 * Devuelve un nodo de state por su nombre
	 *
	 * @param string $name
	 * @return ProcessNode
	 */
	public function getStateByName($name){
		$exists = false;
		foreach($this->_xml->getElementsByTagName('state') as $element){
			$stateName = $element->getAttribute('name');
			if($stateName==$name){
				$node = new ProcessNode($stateName, $element->nodeName);
				$node->setAttributes($element->attributes);
				$node->setChildNodes($element->childNodes);
				$exists = true;
				break;
			}
		}
		if($exists==false){
			return false;
		}
		return $node;
	}

}
