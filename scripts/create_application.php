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
 * to kumbia@kumbia.org so we can send you a copy immediately.
 *
 * @category 	Kumbia
 * @package 	Scripts
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: create_application.php,v b9cc10ddf716 2011/10/19 23:38:16 andres $
 */


//Establece tipo de notificacion de errores
error_reporting(E_ALL | E_NOTICE | E_STRICT);

require 'public/index.config.php';
require 'Library/Kumbia/Core/ClassPath/CoreClassPath.php';
require 'Library/Kumbia/Autoload.php';

/**
 * CreateApplication
 *
 * Permite crear el esqueleto de una aplicación
 *
 * @category 	Kumbia
 * @package 	Scripts
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class CreateApplication extends Script {

	public function run(){

		$posibleParameters = array(
			'name=s' => '--name nombre \t\tNombre de la tabla source del modelo',
			'help' => '--help \t\t\tMuestra esta ayuda'
		);

		$this->parseParameters($posibleParameters);

		if($this->isReceivedOption('help')){
			$this->showHelp($posibleParameters);
			return;
		}

		$this->checkRequired(array('name'));

		$name = $this->getOption('name', 'alpha', 'extraspaces');
		if(!$name){
			throw new ScriptException('Debe indicar el nombre de la aplicación');
		}

		$modelBuilder = Builder::factory('Application', array(
			'name' => $name
		));

		$modelBuilder->build();

	}

}

try {
	$script = new CreateApplication();
	$script->run();
}
catch(CoreException $e){
	ScriptColor::lookSupportedShell();
	echo ScriptColor::colorize(get_class($e).' : '.$e->getConsoleMessage()."\n", ScriptColor::LIGHT_RED);
	if($script->getOption('debug')=='yes'){
		echo $e->getTraceAsString(), "\n";
	}
}
catch(Exception $e){
	echo 'Exception : ', $e->getMessage(), "\n";
}

