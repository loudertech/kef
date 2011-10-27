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
 * @package		ActiveRecord
 * @subpackage	Generator
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ActiveRecordGenerator.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * ActiveRecordGenerator
 *
 * Las clases mapeadas con ActiveRecord que pretendan efectuar operaciones de
 * manipulación de datos deben declarar llaves primarias.
 *
 * Por defecto se utilizan estrategias para obtener el valor de estas
 * al hacer una inserción.
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	Generator
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class ActiveRecordGenerator {

	/**
	 * Columna identidad donde se establecera el valor del generador
	 *
	 * @var string
	 */
	private $_identityColumn;

	/**
	 * Adaptador Generador
	 *
	 * @var mixed
	 */
	private $_adapter;

	/**
	 * Constructor de la clase ActiveRecordGenerator
	 *
	 * @param string $adapter
	 * @param string $identityColumn
	 * @param array $options
	 */
	public function __construct($adapter, $identityColumn, $options){
		$adapterClass = $adapter.'Generator';
		if(!class_exists($adapterClass, false)){
			$path = "Library/Kumbia/ActiveRecord/Generator/Strategies/$adapter.php";
			if(Core::fileExists($path)){
				if(interface_exists('ActiveRecordGeneratorInterface', false)==false){
					require KEF_ABS_PATH.'Library/Kumbia/ActiveRecord/Generator/Interface.php';
				}
				require $path;
			}
			if(!class_exists($adapterClass, false)){
				throw new ActiveRecordException('No existe el generador de identificadores "'.$adapter.'"');
			}
		}
		$this->_identityColumn = $identityColumn;
		$this->_adapter = new $adapterClass();
		$this->_adapter->setIdentityColumn($identityColumn);
		$this->_adapter->setOptions($options);
	}

	/**
	 * Hace un proxy al adaptador
	 *
	 * @param string $method
	 * @param array $arguments
	 */
	public function __call($method, $arguments){
		return call_user_func_array(array($this->_adapter, $method), $arguments);
	}

}
