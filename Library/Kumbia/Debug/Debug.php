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
 * @package		Debug
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Debug.php 103 2009-10-09 01:30:42Z gutierrezandresfelipe $
 */

/**
 * Debug
 *
 * Clase que facilita el debug de aplicaciones
 *
 * @category	Kumbia
 * @package		Debug
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @abstract
 */
abstract class Debug {

	/**
	 * Mensajes de Debug
	 *
	 * @var array
	 */
	private static $_messages = array();

	/**
	 * Memoria de Variables
	 *
	 * @var array
	 */
	private static $_variables = array();

	/**
	 * Handlers de debugs a archivos
	 *
	 * @var array
	 */
	private static $_fileDebugs = array();

	/**
	 * Accion para generar una excepcion al terminar la peticion
	 *
	 */
	const ACTION_HALT = 0;

	/**
	 * Accion para generar una excepcion un log al terminar
	 *
	 */
	const ACTION_LOG = 1;

	/**
	 * Almacena un mensaje para seguimiento
	 *
	 * @param string $value
	 */
	static public function add($value, $completeBackTrace=false){
		$backtrace = debug_backtrace();
		if($value===null){
			$value = 'NULL';
		}
		if($value===true){
			$value = 'TRUE';
		}
		if($value===false){
			$value = 'FALSE';
		}
		if(is_resource($value)){
			$value = '<'.get_resource_type($value).'> '.((string) $value);
		}
		if(isset($backtrace[0])){
			self::$_messages[] = array(
				'backtrace' => $backtrace,
				'completeBacktrace' => $completeBackTrace,
				'class' => isset($backtrace[1]['class']) ? $backtrace[1]['class'] : '',
				'function' => $backtrace[1]['function'],
				'line' => $backtrace[0]['line'],
				'file' => $backtrace[0]['file'],
				'time' => microtime(true),
				'value' => $value
			);
		} else {
			self::$_messages[] = array(
				'backtrace' => $backtrace,
				'completeBacktrace' => $completeBackTrace,
				'class' => '',
				'function' => '',
				'line' => '',
				'file' => '',
				'time' => microtime(true),
				'value' => $value
			);
		}
	}

	/**
	 * Obtiene los mensajes de debug creados
	 *
	 * @return unknown
	 */
	static public function getMessages(){
		return self::$_messages;
	}

	/**
	 * Obtiene las variables de memoria
	 *
	 * @return array
	 */
	static public function getMemory(){
		return self::$_variables;
	}

	/**
	 * Genera una cadena con el llamado a un metodo o funcion
	 *
	 * @param array $backtrace
	 */
	static public function getFunctionCallAsString($backtrace){
		$arguments = array();
		foreach($backtrace['args'] as $arg){
			if($arg===false){
				$arguments[] = 'FALSE';
			}
			if($arg===true){
				$arguments[] = 'TRUE';
			}
			if($arg===null){
				$arguments[] = 'NULL';
			}
			if(is_array($arg)){
				$arguments[] = 'Array';
			}
			if(is_numeric($arg)){
				$arguments[] = $arg;
			}
			if(is_resource($arg)){
				$arguments[] = '<'.get_resource_type($arg).'>'.((string) $arg);
			}
			if(is_object($arg)){
				$arguments[] = '<'.get_class($arg).">";
			}
			if(is_string($arg)){
				$arguments[] = "\"$arg\"";
			}
		}
		if(isset($backtrace['class'])){
			return $backtrace['class']."::".$backtrace['function']."(".join(", ", $arguments).")";
		} else {
			return $backtrace['function'].'()';
		}
	}

	/**
	 * Asercion si son iguales
	 *
	 * @param mixed $val1
	 * @param mixed $val2
	 * @param boolean $showTrace
	 * @throws DebugException
	 */
	public static function assertEquals($val1, $val2, $showTrace=false){
		Debug::add('assetEquals - '.$val1." ".$val2, $showTrace);
		if($val1==$val2){
			throw new DebugException("assetEquals - ".$val1." ".$val2);
		}
	}

	/**
	 * Asercion si no son iguales
	 *
	 * @param mixed $val1
	 * @param mixed $val2
	 * @param boolean $showTrace
	 * @throws DebugException
	 */
	public static function assertNotEquals($val1, $val2, $showTrace=false){
		Debug::add("assetNotEquals - ".$val1." ".$val2, $showTrace);
		if($val1!=$val2){
			throw new DebugException("assetNotEquals - ".$val1." ".$val2);
		}
	}

	/**
	 * Asercion si $val1 es verdadero
	 *
	 * @param mixed $val1
	 * @param mixed $val2
	 * @param boolean $showTrace
	 * @throws DebugException
	 */
	public static function assertTrue($val1, $showTrace=false){
		Debug::add("assetTrue - ".$val1, $showTrace);
		if($val1==true){
			throw new DebugException("assetTrue - ".$val1);
		}
	}

	/**
	 * Asercion si $val1 es falso
	 *
	 * @param mixed $val1
	 * @param mixed $val2
	 * @param boolean $showTrace
	 * @throws DebugException
	 */
	public static function assertFalse($val1, $showTrace=false){
		Debug::add("assetTrue - ".$val1, $showTrace);
		if($val1==false){
			throw new DebugException("assetTrue - ".$val1);
		}
	}

	/**
	 * Valores de Memoria
	 *
	 * @param string $varname
	 * @param mixed $value
	 */
	public static function addVariable($varname, $value){
		if($value===null){
			$value = 'NULL';
		}
		if($value===true){
			$value = 'TRUE';
		}
		if($value===false){
			$value = 'FALSE';
		}
		if(is_array($value)){
			$value = '<Array> '.print_r($value, true);
		}
		if(is_object($value)){
			#if(method_exists($value, 'inspect')){
			#	$value = $value->inspect();
			#} else {
				$value = '<'.get_class($value).'>';
			#}
		}
		if(is_resource($value)){
			$value = '<'.get_resource_type($value)."> ".((string) $value);
		}
		self::$_variables[$varname] = $value;
	}

	/**
	 * Detiene la aplicacion generando un excepcion de Debug
	 *
	 * @param int $action
	 * @param array $options
	 */
	public static function setActionOnFinish($action, $options=array()){
		if($action==self::ACTION_HALT){
			EventManager::attachEvent(new Event('finishRequest', array('Debug', 'haltRequest')));
		}
		if($action==self::ACTION_LOG){

		}
	}

	/**
	 * Termina la ejecución de la petición
	 *
	 * @throws DebugException
	 */
	public static function haltRequest(){
		$exception = new DebugException('Visualizando entorno de seguimiento');
		$exception->setUserCatchable(false);
		throw $exception;
	}

	/**
	 * Envia un debug a un archivo
	 *
	 * @param	string $filePath
	 * @param	mixed $value
	 * @param 	string $flags
	 */
	public static function addToFile($filePath, $value, $flags='w'){
		if(!isset(self::$_fileDebugs[$filePath])){
			self::$_fileDebugs[$filePath] = fopen($filePath, $flags);
		}
		fputs(self::$_fileDebugs[$filePath], print_r($value, true));
	}

}
