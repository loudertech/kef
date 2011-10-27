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
 * @package		Config
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: Array.php 88 2009-09-19 19:10:13Z gutierrezandresfelipe $
 */

/**
 * ArrayConfig
 *
 * El adaptador de configuración PHP permite definir la configuración
 * en arrays multidimensionales nativos del lenguaje con lo cual se
 * obtiene la mayor velocidad al leerlos y procesarlos.
 *
 * @category	Kumbia
 * @package		Config
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */
class ArrayConfig {

	/**
	 * Procesa un Array interno del array multidimensional y lo devuelve como objeto
	 *
	 * @param  array $config
	 * @return Config
	 */
	private function _processSubArray($config){
		$configInstance = new Config();
		foreach($config as $cf => $value){
			if(!is_array($value)){
				$configInstance->$cf = $value;
			} else {
				$configInstance->$cf = $this->_processSubArray($value);
			}
		}
		return $configInstance;
	}

	/**
	 * Constructor de la Clase ArrayConfig
	 *
	 * @access 	public
	 * @param 	Config $config
	 * @param 	string $file
	 * @return 	Config
	 * @static
	 */
	public function read(Config $configInstance, $file){
		include KEF_ABS_PATH.$file;
		if(!isset($config)){
			throw new ConfigException('No se encontró la variable $config en el archivo de configuración Array');
		}
		if(!is_array($config)){
			throw new ConfigException('La variable de configuración $config debe ser un Array');
		}
		foreach($config as $cf => $value){
			if(!is_array($value)){
				$configInstance->$cf = $value;
			} else {
				$configInstance->$cf = $this->_processSubArray($value);
			}
		}
		return $configInstance;
	}

}
