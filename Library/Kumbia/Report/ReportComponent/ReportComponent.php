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
 * @subpackage 	ReportComponent
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: ReportComponent.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * ReportComponent
 *
 * Carga dinámicamente los componentes de reporte
 *
 * @category 	Kumbia
 * @package 	Report
 * @subpackage 	ReportComponent
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class ReportComponent extends Object {

	/**
	 * Realiza el Lazy Loading de un componente de Reportes
	 *
	 * @param string $component
	 * @param array $settings
	 */
	static public function add($component, $attributes=array()){
		$component = (string) $component;
		$className = 'Report'.$component;
		if(class_exists($className, false)==false){
			$classPath = 'Library/Kumbia/Report/Components/Report'.$component.'/Report'.$component.'.php';
			if(Core::fileExists($classPath)){
				require KEF_ABS_PATH.$classPath;
			} else {
				throw new ReportComponentException("No existe el componente de reporte '$component'");
			}
		}
		$componentObject = new $className();
		call_user_func_array(array($componentObject, 'setParameters'), $attributes);
		return $componentObject;
	}

	static public function load($components){
		foreach($components as $component){
			$component = (string) $component;
			$className = 'Report'.$component;
			if(class_exists($className, false)==false){
				$classPath = 'Library/Kumbia/Report/Components/Report'.$component.'/Report'.$component.'.php';
				if(Core::fileExists($classPath)){
					require KEF_ABS_PATH.$classPath;
				} else {
					throw new ReportComponentException("No existe el componente de reporte '$component'");
				}
			}
		}
	}

}
