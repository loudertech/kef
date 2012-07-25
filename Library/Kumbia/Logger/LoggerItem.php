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
 * @package		Logger
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license		New BSD License
 * @version 	$Id: LoggerItem.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * LoggerItem
 *
 * Cada Item de un Log
 *
 * @category	Kumbia
 * @package		Logger
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 */
class LoggerItem extends Object {

	/**
	 * Tipo de Log
	 *
	 * @var integer
	 */
	private $_type;

	/**
	 * Mensaje del Log
	 *
	 * @var string
	 */
	private $message;

	/**
	 * Momento en que ocurrio el evento
	 *
	 * @var integer
	 */
	private $_time;

	/**
	 * Contructor de LoggerItem
	 *
	 * @param string $message
	 * @param integer $type
	 */
	public function __construct($message, $type, $time=0){
		$this->_message = $message;
		$this->_type = $type;
		$this->_time = $time;
	}

	/**
	 * Devuelve el mensaje
	 *
	 * @return string
	 */
	public function getMessage(){
		return $this->_message;
	}

	/**
	 * Devuelve el tipo de log
	 *
	 * @return integer
	 */
	public function getType(){
		return $this->_type;
	}

	/**
	 * Devuelve el timestamp del item
	 *
	 * @return integer
	 */
	public function getTime(){
		return $this->_time;
	}

}
