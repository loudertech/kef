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
 * @license 	New BSD License
 * @version 	$Id: scaffold.php,v f5add30bf4ba 2011/10/26 21:05:13 andres $
 */

//Establece tipo de notificacion de errores
error_reporting(E_ALL | E_NOTICE | E_STRICT);

require 'public/index.config.php';
require 'Library/Kumbia/Core/ClassPath/CoreClassPath.php';
require 'Library/Kumbia/Autoload.php';

/**
 * ScaffoldScript
 *
 * Permite generar formularios
 *
 * @category 	Kumbia
 * @package 	Scripts
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: scaffold.php,v f5add30bf4ba 2011/10/26 21:05:13 andres $
 */
class ScaffoldScript extends Script {

	public function run(){

		$posibleParameters = array(
			'table-name=s' => "--table-name nombre \tNombre de la tabla source del modelo",
			'schema=s' => "--schema nombre \tNombre del schema donde está la tabla si este difiere del schema\n\t\t\tpor defecto [opcional]",
			'application=s' => "--application nombre \tNombre de la aplicación [opcional]",
			'autocomplete=s' => "--autocomplete nombre \tCampos relación que usarán autocomplete en vez de listas SELECT [opcional]",
			'theme=s' => "--theme nombre \tTema que debe aplicarse [opcional]",
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

		$className = Utils::camelize($name);
		$fileName = Utils::uncamelize($className);

		Core::setTestingMode(Core::TESTING_LOCAL);
		Core::changeApplication($application);

		$scaffoldBuilder = Builder::factory('Scaffold', array(
			'name' => $name,
			'application' => $application,
			'schema' => $schema,
			'className' => $className,
			'fileName' => $fileName,
			'autocomplete' => $this->getOption('autocomplete'),
			'theme' => $this->getOption('theme'),
			'force' => $this->isReceivedOption('force')
		));

		$scaffoldBuilder->build();

	}

}


try {
	$script = new ScaffoldScript();
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