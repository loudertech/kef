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
 * @package 	Report
 * @subpackage 	Components
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: ReportStyle.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * ReportFormat
 *
 * Componente para definir el formato de las columnas
 *
 * @category 	Kumbia
 * @package 	Report
 * @subpackage 	Components
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 */
class ReportFormat {

	/**
	 * Objeto que administra el formato
	 *
	 * @var Object
	 */
	private $_format;

	/**
	 * Opciones del Formato de la columna
	 *
	 * @var array
	 */
	private $_options = array();

	/**
	 * Establece los parametros del ReportFormat
	 *
	 * @param array $options
	 */
	public function __construct($options=array()){
		if(isset($options['type'])){
			$className = 'Format'.$options['type'];
			if(class_exists($className, false)==false){
				$classPath = 'Library/Kumbia/Report/Formats/'.$options['type'].'.php';
				if(Core::fileExists($classPath)){
					require KEF_ABS_PATH.$classPath;
				} else {
					throw new ReportComponentException("No existe tipo de formato de columna '".$options['type']."'");
				}
			}
			$this->_format = new $className($options);
			$this->_options = $options;
		} else {
			throw new ReportComponentException("No se indicó el tipo de formato de columna");
		}
	}

	/**
	 * Aplica el formato al valor de la columna
	 *
	 * @param	mixed $value
	 * @return	mixed
	 */
	public function apply($value){
		return $this->_format->apply($value);
	}

	/**
	 * Devuelve el tipo de dato más cercano que administra el formato
	 *
	 * @return string
	 */
	public function getStdType(){
		return $this->_format->getStdType();
	}

}