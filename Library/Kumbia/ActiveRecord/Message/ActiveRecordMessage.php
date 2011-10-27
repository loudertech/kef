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
 * @package		ActiveRecord
 * @subpackage	ActiveRecordMessage
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ActiveRecordMessage.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * ActiveRecordMessage
 *
 * ActiveRecord tiene un subsistema de mensajes que permite flexibilizar
 * la forma en que se presentan ó almacena la salida de validación que
 * se genera en los procesos de inserción ó actualización.
 *
 * Cada mensaje consta de una instancia de la clase ActiveRecordMessage.
 * Esta clase estrctura cada mensaje generado en la validación.
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	ActiveRecordMessage
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class ActiveRecordMessage extends Object {

	/**
	 * Tipo de Mensaje
	 *
	 * @var string
	 */
	private $_type;

	/**
	 * Texto del Mensaje
	 *
	 * @var string
	 */
	private $_message;

	/**
	 * Constructor de la clase
	 *
	 * @access public
	 * @param string $message
	 * @param string $field
	 */
	public function __construct($message, $field="", $type=""){
		$this->_message = $message;
		$this->_field = $field;
		$this->_type = $type;
	}

	/**
	 * Tipo de Mensaje
	 *
	 * @access public
	 * @param string $type
	 */
	public function setType($type){
		$this->_type = $type;
	}

	/**
	 * Devuelve el tipo de Mensaje
	 *
	 * @access public
	 * @return string
	 */
	public function getType(){
		return $this->_type;
	}

	/**
	 * Establece el Texto de Mensaje
	 *
	 * @access public
	 * @param string $message
	 */
	public function setMessage($message){
		$this->_message = $message;
	}

	/**
	 * Devuelve el texto del Mensaje
	 *
	 * @access public
	 * @return string
	 */
	public function getMessage(){
		return $this->_message;
	}

	/**
	 * Establece el Campo que origino el Mensaje
	 *
	 * @access public
	 * @param string $field
	 */
	public function setField($field){
		$this->_field = $field;
	}

	/**
	 * Devuelve el campo que origino el Mensaje
	 *
	 * @access public
	 * @return string
	 */
	public function getField(){
		return $this->_field;
	}

	/**
	 * Metodo to String
	 *
	 * @return string
	 */
	public function __toString(){
		return $this->_message;
	}

}
