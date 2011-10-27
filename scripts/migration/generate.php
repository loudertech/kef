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
 * @version 	$Id: generate.php,v b9cc10ddf716 2011/10/19 23:38:16 andres $
 */

require 'public/index.config.php';
require KEF_ABS_PATH.'Library/Kumbia/Core/ClassPath/CoreClassPath.php';
require KEF_ABS_PATH.'Library/Kumbia/Autoload.php';

/**
 * GenerateMigration
 *
 * Genera la migración de la bd por defecto de una aplicación
 *
 * @category 	Kumbia
 * @package 	Scripts
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: generate.php,v b9cc10ddf716 2011/10/19 23:38:16 andres $
 */
class GenerateMigration extends Script {

	public function __construct(){

		$posibleParameters = array(
			'application=s' => "--application nombre \tNombre de la aplicación [opcional]",
			'environment=s' => "--environment nombre \tEntorno donde se encuentra la base de datos a migrar [opcional]",
			'table-name=s' => "--table-name nombre \tNombre de la tabla a migrar [opcional]",
			'version=s' => "--version nombre \tVersión a la que corresponde la migración [opcional]",
			'export-data=s' => "--export-data type \tExportar los datos de la tabla. Type: always,on-create [opcional]",
			'force' => "--force \t\tForza a que se reescriba la migración [opcional]",
			'help' => "--help \t\t\tMuestra esta ayuda"
		);

		$this->parseParameters($posibleParameters);
		if($this->isReceivedOption('help')){
			$this->showHelp($posibleParameters);
			return;
		}

		$this->checkRequired(array('table-name'));

		$application = $this->getOption('application');
		if(!$application){
			$application = 'default';
		}
		Core::setTestingMode(Core::TESTING_LOCAL);
		Core::changeApplication($application);

		$environment = $this->getOption('environment');
		$tableName = $this->getOption('table-name');

		$config = CoreConfig::readAppConfig();
		if(isset($config->application->migrationDir)){
			$migrationsDir = $config->application->migrationDir;
		} else {
			$migrationsDir = 'apps/'.Router::getApplication().'/migrations';
		}
		if(!file_exists($migrationsDir)){
			mkdir($migrationsDir);
		}

		$originalVersion = $this->getOption('version');
		if($originalVersion){
			$version = $this->filter($originalVersion, 'version');
			if(!$version){
				throw new ScriptException('La versión '.$originalVersion.' no es valida');
			}
			$version = new Version($version, 3);
			if(file_exists($migrationsDir.'/'.$version)){
				if(!$this->isReceivedOption('force')){
					throw new ScriptException('La versión '.$version.' ya fue generada');
				}
			}
		} else {
			$versions = array();
			$iterator = new DirectoryIterator($migrationsDir);
		    foreach($iterator as $fileinfo){
		        if($fileinfo->isDir()){
		        	$version = $this->filter($fileinfo->getFilename(), 'version');
		        	if($version){
		            	$versions[] = new Version($version, 3);
		        	}
		        }
		    }
		    if(count($versions)==0){
		    	$version = new Version('1.0.0');
		    } else {
				$version = Version::maximum($versions);
				$version = $version->addMinor(1);
		    }
		}
		if(!file_exists($migrationsDir.'/'.$version)){
			mkdir($migrationsDir.'/'.$version);
		}

		$exportData = $this->getOption('export-data');
		ActiveRecordMigration::setup($environment);
		ActiveRecordMigration::setMigrationPath($migrationsDir.'/'.$version);
		if($tableName=='all'){
			$migrations = ActiveRecordMigration::generateAll($version, $exportData);
			foreach($migrations as $tableName => $migration){
				file_put_contents($migrationsDir.'/'.$version.'/'.$tableName.'.php', '<?php '.PHP_EOL.PHP_EOL.$migration);
			}
		} else {
			$migration = ActiveRecordMigration::generate($version, $tableName, $exportData);
			file_put_contents($migrationsDir.'/'.$version.'/'.$tableName.'.php', '<?php '.PHP_EOL.PHP_EOL.$migration);
		}

	}

}

try {
	$script = new GenerateMigration();
}
catch(CoreException $e){
	echo get_class($e).' : '.$e->getConsoleMessage()."\n";
}
catch(Exception $e){
	echo 'Exception : '.$e->getMessage()."\n";
}
