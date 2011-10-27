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
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: create_model.php,v b9cc10ddf716 2011/10/19 23:38:16 andres $
 */

//Establece tipo de notificacion de errores
error_reporting(E_ALL | E_NOTICE | E_STRICT);

require 'public/index.config.php';
require 'Library/Kumbia/Core/ClassPath/CoreClassPath.php';
require 'Library/Kumbia/Autoload.php';

/**
 * CreateModel
 *
 * Permite crear un modelo por linea de comandos
 *
 * @category 	Kumbia
 * @package 	Scripts
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2011 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: create_model.php,v b9cc10ddf716 2011/10/19 23:38:16 andres $
 */
class CreateModel extends Script {

	public function run(){

		$posibleParameters = array(
			'table-name=s' => "--table-name nombre \tNombre de la tabla source del modelo",
			'schema=s' => "--schema nombre \tNombre del schema donde está la tabla si este difiere del schema\n\t\t\tpor defecto [opcional]",
			'application=s' => "--application nombre \tNombre de la aplicación [opcional]",
			'force' => "--force \t\tForza a que se reescriba el modelo [opcional]",
			'debug' => "--debug \t\tMuetra la traza del framework en caso que se genere una excepción [opcional]",
			'help' => "--help \t\t\tMuestra esta ayuda"
		);

		$this->parseParameters($posibleParameters);

		if($this->isReceivedOption('help')){
			$this->showHelp($posibleParameters);
			return;
		}

		$this->checkRequired(array('table-name'));

		$name = $this->getOption('table-name');
		$application = $this->getOption('application');
		$schema = $this->getOption('schema');
		if(!$application){
			$application = 'default';
		}

		$className = $this->getOption('class-name');
		if(!$className){
			$className = $name;
		}

		$className = Utils::camelize($name);
		$fileName = Utils::uncamelize($className);

		Core::setTestingMode(Core::TESTING_LOCAL);
		Core::changeApplication($application);

		$modelBuilder = Builder::factory('Model', array(
			'name' => $name,
			'application' => $application,
			'schema' => $schema,
			'className' => $className,
			'fileName' => $fileName,
			'force' => $this->isReceivedOption('force')
		));

		$modelBuilder->build();

	}

}

try {
	$script = new CreateModel();
	$script->run();
}
catch(CoreException $e){
	ScriptColor::lookSupportedShell();
	echo ScriptColor::colorize(get_class($e).' : '.$e->getConsoleMessage()."\n", ScriptColor::LIGHT_RED);
	if($script->getOption('debug')=='yes'){
		echo $e->getTraceAsString()."\n";
	}
}
catch(Exception $e){
	echo 'Exception : '.$e->getMessage()."\n";
}
