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
 * @package 	Validation
 * @copyright 	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: ValidationMessage.php 10 2009-04-24 03:58:00Z gutierrezandresfelipe $
 */

/**
 * ValidationMessage
 *
 * Esta clase representa cada uno de los mensajes de validación
 *
 * @category 	Kumbia
 * @package 	Validation
 * @copyright 	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class ValidationMessage extends Object {

	/**
	 * Mensaje de Validacion
	 */
	private $_message;

	/**
	 * Campo que origino la validacion
	 */
	private $_field;

	/**
	 * Constructor de ValidationMessage
	 *
	 * @param string $message
	 * @param string $field
	 */
	public function __construct($message, $field=''){
		$this->_message = $message;
		$this->_field = $field;
	}

	/**
	 * Devuelve el campo que genero el mensaje
	 *
	 * @return string
	 */
	public function getField(){
		return $this->_field;
	}

	/**
	 * Devuelve el mensaje de validacion
	 *
	 * @return sttring
	 */
	public function getMessage(){
		return $this->_message;
	}

	/**
	 * Muestra un mensaje de error usando Flash::error
	 *
	 */
	public function showErrorMessage(){
		Flash::error($this->_message);
	}

	/**
	 * Muestra un mensaje de advertencia usando Flash::warning
	 *
	 */
	public function showWarningMessage(){
		Flash::warning($this->_message);
	}

	/**
	 * Muestra un mensaje de informacion usando Flash::notice
	 *
	 */
	public function showNoticeMessage(){
		Flash::notice($this->_message);
	}

}
