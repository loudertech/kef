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
 * @version 	$Id: File.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * FileLogger
 *
 * Permite generar logs a archivos planos de Texto
 *
 * @category 	Kumbia
 * @package 	Logger
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 */
class FileLogger extends LoggerAdapter implements LoggerInterface {

	/**
	 * Resource hacia el Archivo del Log
	 *
	 * @var resource
	 */
	private $_fileLogger;

	/**
	 * PATH donde se encuentra el log
	 *
	 * @var $string
	 */
	private $_logPath = 'logs';

	/**
	 * Constructor de la clase FileLogger
	 *
	 * @param string $name
	 * @param array $options
	 */
	public function __construct($name, $options=array()){
		if($name===''||$name===true){
			$name = 'log'.date('dmY').'.txt';
		}
		if(class_exists('Router', false)){
			$application = Router::getApplication();
			if($application){
				$this->_path = 'apps/'.$application.'/'.$this->_logPath.'/'.$name;
			} else {
				$this->_path = $name;
			}
		} else {
			$this->_path = $name;
		}
		if(PHP_VERSION<5.1){
			$this->_dateFormat = 'r';
		}
		if(isset($options['mode'])){
			$this->_fileLogger = @fopen($this->_path, $options['mode']);
		} else {
			$this->_fileLogger = @fopen($this->_path, 'ab');
		}
		if($this->_fileLogger==false){
			throw new LoggerException("No se pudo abrir el log en '".$this->_path."'");
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
		if(!$this->_fileLogger){
			throw new LoggerException("No se puede enviar mensaje al log porque es inválido");
		}
		if(is_array($msg)||is_object($msg)){
			$msg = print_r($msg, true);
		}
		if($this->_transaction==true){
			$this->_quenue[] = new LoggerItem($msg, $type, time());
		} else {
			fputs($this->_fileLogger, $this->_applyFormat($msg, $type).PHP_EOL);
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
 	 * Commit a una transaccion
 	 *
 	 * @access public
 	 */
	public function commit(){
		if($this->_transaction==false){
			throw new LoggerException("No hay una transacción activa");
		}
		$this->_transaction = false;
		foreach($this->_quenue as $msg){
			fputs($this->_fileLogger, $this->_applyFormat($msg->getMessage(), $msg->getType(), $msg->getTime()).PHP_EOL);
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
			throw new LoggerException('No se puede cerrar el log porque es invalido');
		}
		return fclose($this->_fileLogger);
	}

	/**
	 * Vuelve y abre el archivo al deserializar
	 *
	 */
	public function __wakeup(){
		$this->_fileLogger = @fopen($this->_path, 'ab');
	}

}
