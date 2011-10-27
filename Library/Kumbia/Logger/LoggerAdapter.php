<?php

/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.

 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category 	Kumbia
 * @package 	Logger
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 * @version 	$Id: LoggerAdapter.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * LoggerAdapter
 *
 * Esta clase implementa metodos comunes para los loggers
 *
 * @category 	Kumbia
 * @package 	Logger
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 */
class LoggerAdapter {

	/**
	 * Indica si hay transaccion o no
	 *
	 * @var boolean
	 */
	protected $_transaction = false;

	/**
	 * Array con mensajes de log en cola en una transsaccion
	 *
	 * @var array
	 */
	protected $_quenue = array();

	/**
	 * Formato de fecha para logs
	 *
	 * @var string
	 */
	protected $_dateFormat = DATE_RFC1036;

	/**
	 * Formato en el que estara cada linea del log
	 *
	 * @var string
	 */
	protected $_format = '[%date%][%type%] %message%';

	/**
	 * Establece el formato de las lineas del log
	 *
	 * @access public
	 * @param string $format
	 */
	public function setFormat($format){
		$this->_format = $format;
	}

	/**
	 * Devuelve el formato de las lineas del log
	 *
	 * @access public
	 * @param string $format
	 */
	public function getFormat($format){
		$this->_format = $format;
	}

	/**
	 * Devuelve el nombre del tipo de log
	 *
	 * @access public
	 * @param integer $type
	 * @return string
	 */
	private function getTypeString($type){
		switch($type){
			case Logger::DEBUG:
				$type = 'DEBUG';
				break;
			case Logger::ERROR:
				$type = 'ERROR';
				break;
			case Logger::WARNING:
				$type = 'WARNING';
				break;
			case Logger::CUSTOM :
				$type = 'CUSTOM';
				break;
			case Logger::CRITICAL:
				$type = 'CRITICAL';
				break;
			case Logger::ALERT:
				$type = 'ALERT';
				break;
			case Logger::NOTICE:
				$type = 'NOTICE';
				break;
			case Logger::EMERGENCE:
				$type = 'EMERGENCE';
				break;
			case Logger::INFO:
				$type = 'INFO';
				break;
			case Logger::SPECIAL :
				$type = 'SPECIAL';
				break;
			default:
				$type = 'CUSTOM';
		}
		return $type;
	}

	/**
	 * Aplica el formato interno al mensaje
	 *
	 * @access protected
	 * @param string $message
	 */
	protected function _applyFormat($message, $type, $time=0){
		if($time==0){
			$time = time();
		}
		$format = $this->_format;
		if(class_exists('Router')==true){
			$format = str_replace('%controller%', Router::getController(), $format);
			$format = str_replace('%action%', Router::getAction(), $format);
			$format = str_replace('%application%', Router::getApplication(), $format);
			$format = str_replace('%url%', Router::getURL(), $format);
		}
		$format = str_replace('%date%', @date($this->_dateFormat, $time), $format);
		$format = str_replace('%type%', $this->getTypeString($type), $format);
		$format = str_replace('%message%', $message, $format);
		return $format;
	}

	/**
 	 * Inicia una transaccion
 	 *
 	 * @access public
 	 */
	public function begin(){
		$this->_transaction = true;
	}

	/**
 	 * Deshace una transaccion
 	 *
 	 * @access public
 	 */
	public function rollback(){
		if($this->_transaction==false){
			throw new LoggerException('No hay una transacci&oacute;n activa');
		}
		$this->_transaction = false;
		$this->_quenue = array();
	}

	/**
	 * Establece el formato de fecha interno
	 *
	 * @access public
	 * @param string $date
	 */
	public function setDateFormat($date){
		$this->_dateFormat = $date;
	}

	/**
	 * Establece el formato de fecha interno
	 *
	 * @access public
	 * @return string
	 */
	public function getDateFormat(){
		$this->_dateFormat = $date;
	}

}
