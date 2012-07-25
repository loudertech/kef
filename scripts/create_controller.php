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
 * @package 	Scripts
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: create_controller.php,v b9cc10ddf716 2011/10/19 23:38:16 andres $
 */

//Establece tipo de notificacion de errores
error_reporting(E_ALL | E_NOTICE | E_STRICT);

require 'public/index.config.php';
require 'Library/Kumbia/Core/ClassPath/CoreClassPath.php';
require 'Library/Kumbia/Autoload.php';

/**
 * CreateController
 *
 * Permite crear un controlador por linea de comandos
 *
 * @category 	Kumbia
 * @package 	Scripts
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: create_controller.php,v b9cc10ddf716 2011/10/19 23:38:16 andres $
 */
class CreateController extends Script {

	public function __construct(){

		$posibleParameters = array(
			'name=s' => '--name nombre \t\tNombre del controlador',
			'application=s' => '--application nombre \tNombre de la aplicación [opcional]',
			'force' => '--force \t\tForza a que se reescriba el controlador [opcional]',
			'help' => '--help \t\t\tMuestra esta ayuda'
		);

		$this->parseParameters($posibleParameters);

		if($this->isReceivedOption('help')){
			$this->showHelp($posibleParameters);
			return;
		}

		$this->checkRequired(array("name"));

		$name = $this->getOption('name');
		$application = $this->getOption('application');
		if($application==''){
			$application = 'default';
		}
		Router::setActiveApplication($application);
		Core::reloadMVCLocations();
		if($name){
			$controllersDir = Core::getActiveControllersDir();
			$code = "<?php\n\nclass ".Utils::camelize($name)."Controller extends ApplicationController {\n\n\tpublic function indexAction(){\n\n\t}\n\n}\n\n";
			if(!file_exists("$controllersDir/{$name}_controller.php")||$this->isReceivedOption('force')){
				file_put_contents("$controllersDir/{$name}_controller.php", $code);
			} else {
	 			throw new ScriptException("Ya existe el nombre del controlador en la aplicación '$application'");
			}
		} else {
			throw new ScriptException("Debe indicar el nombre del controlador");
		}
	}

}

try {
	$script = new CreateController();
}
catch(CoreException $e){
	ScriptColor::lookSupportedShell();
	echo ScriptColor::colorize(get_class($e).' : '.$e->getConsoleMessage()."\n", ScriptColor::LIGHT_RED);
	echo $e->getTraceAsString()."\n";
}
catch(Exception $e){
	echo "Exception : ".$e->getMessage()."\n";
}
