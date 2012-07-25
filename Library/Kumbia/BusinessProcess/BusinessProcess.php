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
 * @package		BusinessProcess
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: BusinessProcess.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

require KEF_ABS_PATH.'Library/Kumbia/BusinessProcess/BusinessProcessException.php';
require KEF_ABS_PATH.'Library/Kumbia/BusinessProcess/Operation/BusinessOperation.php';
require KEF_ABS_PATH.'Library/Kumbia/BusinessProcess/Operation/Response/BusinessOperationResponse.php';
require KEF_ABS_PATH.'Library/Kumbia/BusinessProcess/Definition/ProcessDefinition.php';
require KEF_ABS_PATH.'Library/Kumbia/BusinessProcess/Instance/ProcessInstance.php';
require KEF_ABS_PATH.'Library/Kumbia/BusinessProcess/Node/ProcessNode.php';

/**
 * BusinessProcess
 *
 * Componente para la creacion de Procesos de Negocio (BPM)
 *
 * @category	Kumbia
 * @package		BusinessProcess
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class BusinessProcess {

	/**
	 * Memoria para variables
	 *
	 * @var array
	 */
	private $_memory = array();

	/**
	 * Constructor de BusinessProcess
	 *
	 */
	public function __construct(){
		if(method_exists($this, 'initialize')){
			$this->initialize();
		}
	}

	/**
	 * Establece un valor de memoria
	 *
	 * @param string $index
	 * @param string $value
	 */
	public function setVariable($index, $value){
		$this->_memory[$index] = $value;
	}

	/**
	 * Indica la variable ya existe
	 *
	 * @param string $index
	 * @return boolean
	 */
	public function isSetVariable($index){
		return isset($this->_memory[$index]);
	}

	/**
	 * Devuelve el valor de una variable
	 *
	 * @param string $index
	 * @return mixed
	 */
	public function getVariable($index){
		if(isset($this->_memory[$index])){
			return $this->_memory[$index];
		}
	}

	/**
	 * Genera un debug en pantalla
	 *
	 * @param string $message
	 */
	public function debug($message){
		print "Debug: ".$message;
	}

	/**
	 * Aserción si las ubicaciones son iguales
	 *
	 * @param string $location
	 */
	public function assertLocation($location1, $location2){
		if($location1!=$location2){
			throw new BusinessProcessException("Aserción-Falló: Las locaciones '$location1' y '$location2' no son iguales");
		}
	}

	/**
	 * Asericion si un valor no se encuentra en un dominio
	 *
	 * @param string $value
	 * @param array $domain
	 */
	public function assertDomain($value, array $domain){
		if(!in_array($value, $domain)){
			throw new BusinessProcessException("Aserción-Falló: El valor '$value' no se encuentra en el dominio");
		}
	}

	/**
	 * Asercion si los valores son iguales
	 *
	 */
	public function assertEquals($value1, $value2){
		if($value1!=$value2){
			throw new BusinessProcessException("Aserción-Falló: El valor '$value1' no es igual a '$value2'");
		}
	}

}
