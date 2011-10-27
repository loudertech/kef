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
 * @package 	Script
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Script.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * Script
 *
 * Componente que permite escribir scripts para uso en CLI
 *
 * @category 	Kumbia
 * @package 	Script
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @abstract
 */
abstract class Script extends Object {

	/**
	 * Codificación de salida del script
	 *
	 * @var string
	 */
	private $_encoding = 'UTF-8';

	/**
	 * Parametros recibidos por el script
	 *
	 * @var string
	 */
	private $_parameters = array();

	/**
	 * Argumentos posibles
	 *
	 * @var array
	 */
	private $_posibleArguments = array();

	/**
	 * Parsea los parametros pasados al script
	 *
	 * @param	array $parameters
	 * @param	array $posibleAlias
	 * @return	array
	 */
	public function parseParameters($parameters=array(), $posibleAlias=array()){

		$arguments = array();
		$posibleArguments = array();
		foreach($parameters as $parameter => $description){
			if(strpos($parameter, "=")!==false){
				$parameterParts = explode("=", $parameter);
				if(count($parameterParts)!=2){
					throw new ScriptException("Definición inválida para el parámetro '$parameter'");
				}
				if(strlen($parameterParts[0])==""){
					throw new ScriptException("Definición inválida para el parámetro '".$parameter."'");
				}
				if(!in_array($parameterParts[1], array('s', 'i'))){
					throw new ScriptException("Tipo de dato incorrecto en parámetro '".$parameter."'");
				}
				$this->_posibleArguments[$parameterParts[0]] = true;
				$arguments[$parameterParts[0]] = array(
					'have-option' => true,
					'option-required' => true,
					'data-type' => $parameterParts[1]
				);
			} else {
				if(strpos($parameter, "=")!==false){
					$parameterParts = explode("=", $parameter);
					if(count($parameterParts)!=2){
						throw new ScriptException("Definición invalida para el parámetro '$parameter'");
					}
					if(strlen($parameterParts[0])==""){
						throw new ScriptException("Definición invalida para el parámetro '$parameter'");
					}
					if(!in_array($parameterParts[1], array('s', 'i'))){
						throw new ScriptException("Tipo de dato incorrecto en parámetro '$parameter'");
					}
					$this->_posibleArguments[$parameterParts[0]] = true;
					$arguments[$parameterParts[0]] = array(
						'have-option' => true,
						'option-required' => false,
						'data-type' => $parameterParts[1]
					);
				} else {
					if(preg_match('/([a-zA-Z0-9]+)/', $parameter)){
						$this->_posibleArguments[$parameter] = true;
						$arguments[$parameter] = array(
							'have-option' => false,
							'option-required' => false
						);
					} else {
						throw new ScriptException("Parámetro inválido '$parameter'");
					}
				}
			}
		}

		$param = '';
		$paramName = '';
		$allParamNames = array();
		$receivedParams = array();
		$numberArguments = count($_SERVER['argv']);
		for($i=1;$i<$numberArguments;$i++){
			$argv = $_SERVER['argv'][$i];
			if(preg_match('/([\-]{1,2})([a-zA-Z0-9][a-zA-Z0-9\-]*)/', $argv, $matches)){
				if(strlen($matches[1])==1){
					$param = substr($matches[2], 1);
					$paramName = substr($matches[2], 0, 1);
				} else {
					if(strlen($matches[2])<2){
						throw new ScriptException("Parámetro de script inválido '$argv'");
					}
					$paramName = $matches[2];
				}
				if(!isset($this->_posibleArguments[$paramName])){
					if(!isset($posibleAlias[$paramName])){
						throw new ScriptException("Parámetro desconocido '$paramName'");
					} else {
						$paramName = $posibleAlias[$paramName];
					}
				}
				if(isset($arguments[$paramName])){
					if($param!=''){
						$receivedParams[$paramName] = $param;
						$param = '';
						$paramName = '';
					}
					if($arguments[$paramName]['have-option']==false){
						$receivedParams[$paramName] = true;
					}
				}
			} else {
				$param = $argv;
				if($paramName!=''){
					if(isset($arguments[$paramName])){
						if($param==''){
							if($arguments[$paramName]['have-option']==true){
								throw new ScriptException("El parámetro '$paramName' requiere una opción");
							}
						}
					}
					$receivedParams[$paramName] = $param;
					$param = '';
					$paramName = '';
				} else {
					$receivedParams[$i-1] = $param;
					$param = '';
				}
			}
		}
		$this->_parameters = $receivedParams;
		return $receivedParams;
	}

	/**
	 * Chequea que un conjunto de parametros se haya recibido
	 *
	 * @param array $required
	 */
	public function checkRequired($required){
		foreach($required as $fieldRequired){
			if(!isset($this->_parameters[$fieldRequired])){
				throw new ScriptException("El parámetro '$fieldRequired' es requerido por este script");
			}
		}
	}

	/**
	 * Establece la codificación de la salida del script
	 *
	 * @param string $encoding
	 */
	public function setEncoding($encoding){
		$this->_encoding = $encoding;
	}

	/**
	 * Muestra la ayuda del script
	 *
	 * @param array $posibleParameters
	 */
	public function showHelp($posibleParameters){
		echo basename($_SERVER['PHP_SELF']).' - Modo de uso:'.PHP_EOL.PHP_EOL;
		foreach($posibleParameters as $parameter => $description){
			echo html_entity_decode($description, ENT_COMPAT, $this->_encoding).PHP_EOL;
		}
	}

	/**
	 * Devuelve el valor de una opción recibida. Si recibe más parámetros los toma como
	 * filtros
	 *
	 * @param string $option
	 */
	public function getOption($option){
		if(isset($this->_parameters[$option])){
			if(func_num_args()>1){
				$params = func_get_args();
				unset($params[0]);
				return Filter::bring($this->_parameters[$option], $params);
			}
			return $this->_parameters[$option];
		} else {
			return null;
		}
	}

	/**
	 * Indica si el script recibió una determinada opción
	 *
	 * @param	string $option
	 * @return	boolean
	 */
	public function isReceivedOption($option){
		return isset($this->_parameters[$option]);
	}

	/**
	 * Filtra un valor
 	 *
 	 * @access	protected
	 * @param	string $paramValue
	 * @return	mixed
	 */
	protected function filter($paramValue){
		//Si hay más de un argumento, toma los demas como filtros
		if(func_num_args()>1){
			$params = func_get_args();
			unset($params[0]);
			return Filter::bring($paramValue, $params);
		} else {
			throw new ScriptException('Debe indicar al menos un filtro a aplicar');
		}
		return $paramValue;
	}

	/**
	 * Obtiene el último parámetro no asociado a ningún nombre de parámetro
	 *
	 * @return string
	 */
	public function getLastUnNamedParam(){
		foreach(array_reverse($this->_parameters) as $key => $value){
			if(is_numeric($key)){
				return $value;
			}
		}
		return false;
	}

	/**
	 * Muestra un mensaje en la consola de texto
	 *
	 * @param Exception $exception
	 */
	public static function showConsoleException($exception){

		$isXTermColor = false;
		if(isset($_ENV['TERM'])){
			foreach(array('256color') as $term){
				if(preg_match('/'.$term.'/', $_ENV['TERM'])){
					$isXTermColor = true;
				}
			}
		}

		$isSupportedShell = false;
		if($isXTermColor){
			if(isset($_ENV['SHELL'])){
				foreach(array('bash', 'tcl') as $shell){
					if(preg_match('/'.$shell.'/', $_ENV['SHELL'])){
						$isSupportedShell = true;
					}
				}
			}
		}

		if(!class_exists('ScriptColor', false)){
			require KEF_ABS_PATH.'Library/Kumbia/Script/Color/ScriptColor.php';
		}

		ScriptColor::setFlags($isSupportedShell && $isSupportedShell);

		$output = "";
		$output.= ScriptColor::colorize(get_class($exception).': ', ScriptColor::RED, ScriptColor::BOLD);
		$message = str_replace("\"", "\\\"", $exception->getMessage());
		$message.= ' ('.$exception->getCode().')';
		$output.= ScriptColor::colorize($message, ScriptColor::WHITE, ScriptColor::BOLD);
		$output.='\\n';

		$output.= Highlight::getString(file_get_contents($exception->getFile()), 'console', array(
			'firstLine' => ($exception->getLine()-3<0 ? $exception->getLine() : $exception->getLine()-3),
			'lastLine' => $exception->getLine()+3
		));

		$i = 1;
		$getcwd = getcwd();
		foreach($exception->getTrace() as $trace){
			$output.= ScriptColor::colorize('#'.$i, ScriptColor::WHITE, ScriptColor::UNDERLINE);
			$output.= ' ';
			if(isset($trace['file'])){
				$file = str_replace($getcwd, '', $trace['file']);
				$output.= ScriptColor::colorize($file.'\\n', ScriptColor::NORMAL);
			}
			$i++;
		}

		if($isSupportedShell){
			system('echo -e "'.$output.'"');
		} else {
			echo $output;
		}

	}

	/**
	 * Obliga a que todas las propiedades del testcase esten definidas previamente
	 *
	 * @access	public
	 * @param	string $property
	 * @return 	mixed
	 */
	public function __get($property){
		if(EntityManager::isModel($property)==false){
			throw new UserComponentException("Leyendo propiedad indefinida '$property' del script");
		} else {
			$entity = EntityManager::getEntityInstance($property);
			$this->_settingLock = true;
			$this->$property = $entity;
			$this->_settingLock = false;
			return $this->$property;
		}
	}

}

