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
 * @version 	$Id: Stream.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * StreamLogger
 *
 * Permite escribir logs a protocolos que soporte escritura/envolturas
 *
 * @category 	Kumbia
 * @package 	Logger
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 */
class StreamLogger extends LoggerAdapter implements LoggerInterface {

	/**
	 * Resource hacia el Archivo del Log
	 *
	 * @var resource
	 */
	private $_streamLogger;

	/**
	 * Constructor de la clase streamLogger
	 *
	 * @param string $stream
	 * @param array $options
	 */
	public function __construct($stream, $options=array()){
		if(PHP_VERSION<5.1){
			$this->_dateFormat = 'r';
		}
		if(isset($options['mode'])){
			$this->_streamLogger = @fopen($stream, $options['mode']);
		} else {
			$this->_streamLogger = @fopen($stream, 'ab');
		}
		if($this->_streamLogger==false){
			throw new LoggerException('No se pudo abrir el stream "'.$stream.'" '.$php_errormsg);
		}
	}

	/**
	 * Realiza el proceso del log
	 *
	 * @access public
	 * @param string $msg
	 * @param int $type
	 */
	public function log($msg, $type){
		if(!$this->_streamLogger){
			throw new LoggerException("No se puede enviar mensaje al log porque es invalido");
		}
		if(is_array($msg)||is_object($msg)){
			$msg = print_r($msg, true);
		}
		if($this->_transaction==true){
			$this->_quenue[] = new LoggerItem($msg, $type, time());
		} else {
			fputs($this->_streamLogger, $this->_applyFormat($msg, $type).PHP_EOL);
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
			fputs($this->_streamLogger, $this->_applyFormat($msg->getMessage(), $msg->getType(), $msg->getTime()).PHP_EOL);
		}
	}

	/**
 	 * Cierra el Logger
 	 *
 	 * @access public
 	 * @return boolean
 	 */
	public function close(){
		if(!$this->_streamLogger){
			throw new LoggerException('No se puede cerrar el log porque es invalido');
		}
		return fclose($this->_streamLogger);
	}

	/**
	 * Vuelve y abre el archivo al deserializar
	 *
	 */
	public function __wakeup(){
		$this->_streamLogger = @fopen($this->_path, 'a');
	}

}
