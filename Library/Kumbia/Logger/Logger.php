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
 * @version 	$Id: Logger.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * @see LoggerInterface
 */
require KEF_ABS_PATH.'Library/Kumbia/Logger/Interface.php';

/**
 * @see LoggerInterface
 */
require KEF_ABS_PATH.'Library/Kumbia/Logger/LoggerAdapter.php';

/**
 * @see LoggerItem
 */
require KEF_ABS_PATH.'Library/Kumbia/Logger/LoggerItem.php';

/**
 * Permite realizar logs en archivos de texto en la carpeta Logs
 *
 * $fileLogger = Es el File Handle para escribir los logs
 * $transaction = Indica si hay o no transaccion
 * $quenue = array con lista de logs pendientes
 *
 * Ej:
 * <code>
 * //Empieza un log en logs/logDDMMY.txt
 * $myLog = new Logger();
 *
 * $myLog->log("Loggear esto como un debug", Logger::DEBUG);
 *
 * //Esto se guarda al log inmediatamente
 * $myLog->log("Loggear esto como un error", Logger::ERROR);
 *
 * //Inicia una transaccion
 * $myLog->begin();
 *
 * //Esto queda pendiente hasta que se llame a commit para guardar
 * //ó rollback para cancelar
 * $myLog->log("Esto es un log en la fila", Logger::WARNING);
 * $myLog->log("Esto es un otro log en la fila", Logger::WARNING);
 *
 * //Se guarda al log
 * $myLog->commit();
 *
 * //Cierra el Log
 * $myLog->close();
 * </code>
 *
 * @category 	Kumbia
 * @package 	Logger
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class Logger extends Object {

	/**
	 * Objeto Adaptador
	 *
	 * @var object
	 */
	private $_objectLogger;

	/**
	 * Registro Especial
	 *
	 */
	const SPECIAL = 9;

	/**
	 * Registro Personalizado
	 *
	 */
	const CUSTOM = 8;

	/**
	 * Registro de seguimiento
	 *
	 */
	const DEBUG = 7;

	/**
	 * Registro informativo
	 *
	 */
	const INFO = 6;

	/**
	 * Registro de Notificación
	 *
	 */
	const NOTICE = 5;

	/**
	 * Registro de Advertencia
	 *
	 */
	const WARNING = 4;

	/**
	 * Registro de error
	 *
	 */
	const ERROR = 3;

	/**
	 * Registro de alerta
	 *
	 */
	const ALERT = 2;

	/**
	 * Registro critico
	 *
	 */
	const CRITICAL = 1;

	/**
	 * Registro de emergencia
	 *
	 */
	const EMERGENCE = 0;

	/**
 	 * Constructor del Logger
 	 *
 	 * @access public
 	 * @param string $adapter
 	 * @param string $name
 	 */
	public function __construct($adapter='File', $name='', $options=array()){
		$className = $adapter.'Logger';
		if(!class_exists($className, false)){
			require KEF_ABS_PATH.'Library/Kumbia/Logger/Adapters/'.$adapter.'.php';
		}
		if(!class_exists($className, false)){
			throw new LoggerException("No se encontró el adaptador '$className'");
		}
		$this->_objectLogger = new $className($name, $options);
	}

	/**
	 * Especifica el PATH donde se guardan los logs
	 *
	 * @param string $path
	 */
	public function setPath($path){
		$this->_objectLogger->setPath($path);
	}

	/**
	 * Establece el formato de los mensajes
	 *
	 * @param $string $format
	 */
	public function setFormat($format){
		$this->_objectLogger->setFormat($format);
	}

	/**
	 * Establece el formato de fecha con el que se imprime los logs
	 *
	 * @param $string $format
	 */
	public function setDateFormat($format){
		$this->_objectLogger->setDateFormat($format);
	}

	/**
	 * Obtener el path actual
	 *
	 * @return $path
	 */
	public function getPath(){
		return $this->_objectLogger->getPath();
	}

	/**
 	 * Almacena un mensaje en el log
 	 *
 	 * @access public
 	 * @param string $msg
 	 * @param ing $type
 	 */
	public function log($msg, $type=self::DEBUG){
		$this->_objectLogger->log($msg, $type);
	}

	/**
	 * Hace dinamicamente el llamado a log usando nombres definidos
	 *
	 * @access public
	 * @param string $type
	 * @param array $args
	 */
	public function __call($type, $args=array()){
		switch($type){
			case 'debug':
				$args[1] = Logger::DEBUG;
				call_user_func_array(array($this, 'log'), $args);
				break;
			case 'error':
				$args[1] = Logger::ERROR;
				call_user_func_array(array($this->_objectLogger, 'log'), $args);
				break;
			case 'warning':
				$args[1] = Logger::WARNING;
				call_user_func_array(array($this->_objectLogger, 'log'), $args);
				break;
			case 'alert':
				$args[1] = Logger::ALERT;
				call_user_func_array(array($this->_objectLogger, 'log'), $args);
				break;
			case 'notice':
				$args[1] = Logger::NOTICE;
				call_user_func_array(array($this->_objectLogger, 'log'), $args);
				break;
			case 'emergence':
				$args[1] = Logger::EMERGENCE;
				call_user_func_array(array($this->_objectLogger, 'log'), $args);
				break;
			case 'custom':
				$args[1] = Logger::CUSTOM;
				call_user_func_array(array($this->_objectLogger, 'log'), $args);
				break;
			case 'special':
				$args[1] = Logger::SPECIAL;
				call_user_func_array(array($this->_objectLogger, 'log'), $args);
				break;
			case 'info':
				$args[1] = Logger::INFO;
				call_user_func_array(array($this->_objectLogger, 'log'), $args);
				break;
			case 'critical':
				$args[1] = Logger::CRITICAL;
				call_user_func_array(array($this->_objectLogger, 'log'), $args);
				break;
			default:
				throw new LoggerException('Tipo indefinido de excepción ['.$args[0].']', 0);
		}
	}

	/**
 	 * Inicia una transaccion
 	 *
 	 */
	public function begin(){
		$this->_objectLogger->begin();
	}

	/**
 	 * Deshace una transaccion
 	 *
 	 */
	public function rollback(){
		$this->_objectLogger->rollback();
	}

	/**
 	 * Commit a una transaccion
 	 */
	public function commit(){
		$this->_objectLogger->commit();
	}

	/**
 	 * Cierra el log
 	 */
	public function close(){
		$this->_objectLogger->close();
	}

}
