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
 * @version 	$Id: run.php,v b9cc10ddf716 2011/10/19 23:38:16 andres $
 */

require 'public/index.config.php';
require KEF_ABS_PATH.'Library/Kumbia/Core/ClassPath/CoreClassPath.php';
require KEF_ABS_PATH.'Library/Kumbia/Autoload.php';

/**
 * RunMigration
 *
 * Corre la migración de la bd si existe
 *
 * @category 	Kumbia
 * @package 	Scripts
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: run.php,v b9cc10ddf716 2011/10/19 23:38:16 andres $
 */
class RunMigration extends Script {

	public function __construct(){

		$posibleParameters = array(
			'application=s' => "--application nombre \tNombre de la aplicación [opcional]",
			'environment=s' => "--environment nombre \tEntorno donde se correrá la base de datos a migrar [opcional]",
			'table-name=s' => "--table-name nombre \tNombre de la tabla a migrar [opcional]",
			'version=s' => "--version \t\tVersión de la migración que debe ser importada [opcional]",
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
			throw new ScriptException('No se encontraron versiones de migración en '.$migrationPath);
		} else {
			$version = Version::maximum($versions);
		}

		if(isset($config->migrations->version)){
			$fromVersion = $this->filter($config->migrations->version, 'version');
		} else {
			$fromVersion = (string) $version;
		}
		$config->migrations->version = (string) $version;

		ActiveRecordMigration::setup($environment);
		ActiveRecordMigration::setMigrationPath($migrationsDir.'/'.$version);
		$versionsBetween = Version::between($fromVersion, $version, $versions);
		foreach($versionsBetween as $version){
			if($tableName=='all'){
				$iterator = new DirectoryIterator($migrationsDir.'/'.$version);
			    foreach($iterator as $fileinfo){
			        if($fileinfo->isFile()){
			        	if(preg_match('/\.php$/', $fileinfo->getFilename())){
			            	ActiveRecordMigration::migrateFile((string) $version, $migrationsDir.'/'.$version.'/'.$fileinfo->getFilename());
			        	}
			        }
			    }
			} else {
				$migrationPath = $migrationsDir.'/'.$version.'/'.$tableName.'.php';
				if(file_exists($migrationPath)){
					ActiveRecordMigration::migrateFile((string) $version, $migrationPath);
				} else {
					throw new ScriptException('No se encontró la clase de migración '.$migrationPath);
				}
			}
		}

		CoreConfig::writeAppConfig($config);

	}

}

try {
	$script = new RunMigration();
}
catch(CoreException $e){
	ScriptColor::lookSupportedShell();
	echo ScriptColor::colorize(get_class($e).' : '.$e->getConsoleMessage()."\n", ScriptColor::LIGHT_RED);
	echo $e->getTraceAsString()."\n";
}
catch(Exception $e){
	echo 'Exception : '.$e->getMessage()."\n";
	echo $e->getTraceAsString()."\n";
}
