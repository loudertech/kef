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
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Compressed.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * CompressedLogger
 *
 * Permite generar logs a archivos planos de Texto
 *
 * @category 	Kumbia
 * @package 	Logger
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 */
class CompressedLogger extends LoggerAdapter implements LoggerInterface {

	/**
	 * Permite especificar el directorio en el que guardaran los logs
	 *
	 * @var string
	 */
	private $_logPath = 'logs';

	/**
	 * Resource hacia el Archivo del Log
	 *
	 * @var resource
	 */
	private $_fileLogger;

	/**
	 * Constructor de la clase FileLogger
	 *
	 * @param string $name
	 */
	public function __construct($name){
		if($name===''||$name===true){
			$name = 'log'.date('dmY').".txt";
		}
		if(class_exists("Router")){
			$application = Router::getApplication();
			if($application){
				$path = "apps/".$application."/".$this->_logPath."/".$name;
			} else {
				$path = $name;
			}
		} else {
			$path = $name;
		}
		if(PHP_VERSION<5.1){
			$this->_dateFormat = 'r';
		}
		$this->_fileLogger = @gzopen($path, 'a');
		if($this->_fileLogger==false){
			throw new LoggerException("No se pudo abrir el log en '$path'");
		}
	}

	/**
	 * Permite establecer el logPath del Logger
	 *
	 * @access public
	 * @param string $path
	 */
	public function setPath($path){
		$this->_logPath = $path;
	}

	/**
	 * Devuelve el log path
	 *
	 * @access public
	 * @return string
	 */
	public function getPath(){
		return $this->_logPath;
	}

	/**
	 * Realiza el proceso del log
	 *
	 * @access public
	 * @param string $msg
	 * @param int $type
	 */
	public function log($msg, $type){
		if(!$this->_fileLogger){
			throw new LoggerException("No se puede enviar mensaje al log porque es invalido");
		}
		if(is_array($msg)||is_object($msg)){
			$msg = print_r($msg, true);
		}
		if($this->_transaction==true){
			$this->_quenue[] = new LoggerItem($msg, $type, time());
		} else {
			gzwrite($this->_fileLogger, $this->_applyFormat($msg, $type).PHP_EOL);
		}
	}

	/**
 	 * Commit a una transaccion
 	 *
 	 * @access public
 	 */
	public function commit(){
		if($this->_transaction==false){
			throw new LoggerException("No hay una transacci&oacute;n activa");
		}
		$this->_transaction = false;
		foreach($this->_quenue as $msg){
			gzwrite($this->_fileLogger, $this->_applyFormat($msg->getMessage(), $msg->getType(), $msg->getTime()).PHP_EOL);
		}
	}

	/**
 	 * Cierra el Logger
 	 *
 	 * @access public
 	 * @return boolean
 	 */
	public function close(){
		if(!$this->_fileLogger){
			throw new LoggerException("No se puede cerrar el log porque es invalido");
		}
		return gzclose($this->_fileLogger);
	}

}
