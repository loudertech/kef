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
 * @package 	Autoloader
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Autoload.php 51 2009-05-12 03:45:18Z gutierrezandresfelipe $
 */

/**
 * Class to autoload
 *
 * @category 	Kumbia
 * @package 	Autoloader
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @param 		string $className
 */
class Autoload {

	public static function load($className){
		if(CoreClassPath::lookupClass($className)){
			$classPath = CoreClassPath::getClassPath($className);
			require $classPath;
		} else {
			//Incluir Componentes Personalizados
			$activeApp = Router::getApplication();
			if($activeApp){
				$config = CoreConfig::readAppConfig();
				if(isset($config->application->libraryDir)){
					$libraryDir = 'apps/'.$config->application->libraryDir;
				} else {
					$libraryDir = 'apps/'.$activeApp.'/library';
				}
				if(Core::fileExists($libraryDir.'/'.$className.'/'.$className.'.php')){
					require KEF_ABS_PATH.$libraryDir.'/'.$className.'/'.$className.'.php';
				} else {
					$componentName = preg_replace('/Exception$/', '', $className);
					if(Core::fileExists($libraryDir.'/'.$componentName.'/'.$className.'.php')){
						require KEF_ABS_PATH.$libraryDir.'/'.$componentName.'/'.$className.'.php';
					} else {
						// Si los modelos no se autoinicializan trata de buscar la entidad
						if(EntityManager::getAutoInitialize()==false){
							EntityManager::isModel($className);
						}
					}
				}
			}
		}
	}

}

spl_autoload_register(array('Autoload', 'load'));
