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
 * @subpackage	Node
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ProcessNode.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * ProcessNode
 *
 * Nodo de las operaciones de Negocio
 *
 * @category	Kumbia
 * @package		BusinessProcess
 * @subpackage	Node
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class ProcessNode {

	/**
	 * Nombre del Nodo
	 *
	 * @var string
	 */
	private $_name;

	/**
	 * Tipo de nodo
	 *
	 * @var string
	 */
	private $_type;

	/**
	 * Nodos Hijos del Nodo
	 *
	 * @var array
	 */
	private $_childNodes = array();

	/**
	 * Atributos del Nodo
	 *
	 * @var DOMNamedNodeMap
	 */
	private $_attributes;

	static $x = 0;

	/**
	 * Constructor de ProccessNode
	 *
	 * @param string $name
	 * @param string $type
	 */
	public function __construct($name, $type){
		$this->_name = $name;
		$this->_type = $type;
	}

	/**
	 * Establece los nodos hijo del Nodo
	 *
	 * @param DOMNodeList $nodeList
	 */
	public function setChildNodes(DOMNodeList $nodeList){
		$nodes = array();
		foreach($nodeList as $node){
			if($node instanceof DOMElement){
				$nd = new ProcessNode($node->getAttribute('name'), $node->nodeName);
				$nd->setAttributes($node->attributes);
				$nd->setChildNodes($node->childNodes);
				$nodes[] = $nd;
			}
		}
		$this->_childNodes = $nodes;
	}

	/**
	 * Devuelve el nombre del nodo
	 *
	 * @return string
	 */
	public function getName(){
		return $this->_name;
	}

	/**
	 * Devuelve el tipo de nodo
	 *
	 * @return string
	 */
	public function getType(){
		return $this->_type;
	}

	/**
	 * Devuelve los nodos hijos
	 *
	 * @return array
	 */
	public function getChildNodes(){
		return $this->_childNodes;
	}

	/**
	 * Establece los attributos del nodo
	 *
	 * @param DOMNamedNodeMap $nodeMap
	 */
	public function setAttributes(DOMNamedNodeMap $nodeMap){
		$this->_attributes = $nodeMap;
	}

	/**
	 * Devuelve el valor de un atributo
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getAttribute($name){
		return $this->_attributes->getNamedItem($name);
	}

}
