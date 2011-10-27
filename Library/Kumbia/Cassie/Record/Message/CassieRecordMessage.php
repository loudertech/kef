<?php

class CassieRecordMessage extends Object {

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
	 * @access	public
	 * @param	string $message
	 * @param	string $field
	 */
	public function __construct($message, $field='', $type=''){
		$this->_message = $message;
		$this->_field = $field;
		$this->_type = $type;
	}

	/**
	 * Tipo de Mensaje
	 *
	 * @access	public
	 * @param	string $type
	 */
	public function setType($type){
		$this->_type = $type;
	}

	/**
	 * Devuelve el tipo de Mensaje
	 *
	 * @access	public
	 * @return	string
	 */
	public function getType(){
		return $this->_type;
	}

	/**
	 * Establece el Texto de Mensaje
	 *
	 * @access	public
	 * @param	string $message
	 */
	public function setMessage($message){
		$this->_message = $message;
	}

	/**
	 * Devuelve el texto del Mensaje
	 *
	 * @access	public
	 * @return	string
	 */
	public function getMessage(){
		return $this->_message;
	}

	/**
	 * Establece el Campo que origino el Mensaje
	 *
	 * @access	public
	 * @param	string $field
	 */
	public function setField($field){
		$this->_field = $field;
	}

	/**
	 * Devuelve el campo que origino el Mensaje
	 *
	 * @access	public
	 * @return	string
	 */
	public function getField(){
		return $this->_field;
	}

}