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
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Report.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * Report
 *
 * El objetivo del componente Report es crear una capa de abstracción
 * consitente que permita mediante una misma API crear listados ó
 * reportes a varios formatos aprovechando las caracteristicas de
 * cada uno sin requerir esfuerzo adicional.
 *
 * @category 	Kumbia
 * @package 	Report
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class Report extends Object {

	/**
	 * Adaptador de Reporte
	 *
	 * @var mixed
	 */
	private $_adapter;

	/**
	 * Nombre del adaptador actual
	 *
	 * @var string
	 */
	private $_adapterName;

	/**
	 * Modo de visualizacion normal
	 *
	 */
	const DISPLAY_NORMAL = 0;

	/**
	 * Modo visualizacion Vista previa
	 *
	 */
	const DISPLAY_PRINT_PREVIEW = 1;

	/**
	 * Constructor de la clase Report
	 *
	 * @param string $adapter
	 */
	public function __construct($adapter){
		if(interface_exists('ReportInterface', false)==false){
			require KEF_ABS_PATH.'Library/Kumbia/Report/Interface.php';
		}
		$adapter = (string) $adapter;
		$className = $adapter.'Report';
		if(class_exists($className, false)==false){
			$classPath = 'Library/Kumbia/Report/Adapters/'.$adapter.'.php';
			if(Core::fileExists($classPath)){
				Core::requireFile('Report/Adapters/'.$adapter);
			} else {
				throw new ReportException("No existe el adaptador '$adapter'");
			}
		}
		$this->_adapterName = $adapter;
		$this->_adapter = new $className();
	}

	/**
	 * Proxy de llamados al Adaptador
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public function __call($method, $args=array()){
		if(method_exists($this->_adapter, $method)==false){
			throw new ReportException("No existe el método '$method' en el Adaptador '".$this->_adapterName."'");
		}
		return call_user_func_array(array($this->_adapter, $method), $args);
	}
}
