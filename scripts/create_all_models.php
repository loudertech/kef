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
 * @category	Kumbia
 * @package		Scripts
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */

//Establece tipo de notificacion de errores
error_reporting(E_ALL | E_NOTICE | E_STRICT);

require 'public/index.config.php';
require 'Library/Kumbia/Core/ClassPath/CoreClassPath.php';
require 'Library/Kumbia/Autoload.php';

/**
 * CreateAllModels
 *
 * Permite crear todos los modelos de una aplicacion por linea de comandos
 *
 * @category	Kumbia
 * @package		Scripts
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class CreateAllModels extends Script {

	/**
	 * Devuelve el tipo PHP asociado
	 *
	 * @param string $type
	 * @return string
	 */
	public function getPHPType($type){
		if(stripos($type, 'int')!==false){
			return 'integer';
		}
		if(stripos($type, 'int')!==false){
			return 'integer';
		}
		if(strtolower($type)=='date'){
			return 'Date';
		}
		return 'string';
	}

	public function run(){

		$posibleParameters = array(
			'application=s' => "--application nombre \tNombre de la aplicación [opcional]",
			'force' => "--force \t\tForza a que se reescriba los modelos existentes [opcional]",
			'schema=s' => "--schema nombre \tNombre del schema donde están la tablas si este difiere del schema\n\t\t\tpor defecto [opcional]",
			'define-relations' => "--define-relations \tDefine posibles relaciones existentes de acuerdo a convenciones [opcional]",
			'foreign-keys' => "--foreign-keys \t\tDefine posibles llaves foraneas virtuales [opcional]",
			'validations' => "--validations \t\tDefine posibles validaciones de dominio de acuerdo a convenciones [opcional]",
			'help' => "--help \t\t\tVisualiza esta ayuda"
		);

		$this->parseParameters($posibleParameters);

		if($this->isReceivedOption('help')){
			$this->showHelp($posibleParameters);
			return;
		}

		$application = $this->getOption('application');
		if(!$application){
			$application = 'default';
		}

		Core::setTestingMode(Core::TESTING_LOCAL);
		Core::changeApplication($application);

		$modelsDir = Core::getActiveModelsDir();
		if(!DbLoader::loadDriver()){
			throw new DbException("No se puede conectar a la base de datos");
		}
		$forceProcess = $this->isReceivedOption('force');
		$defineRelations = $this->isReceivedOption('define-relations');
		$defineForeignKeys = $this->isReceivedOption('foreign-keys');

		$db = DbBase::rawConnect();
		$schema = $this->getOption('schema');
		if(!$schema){
			$schema = $db->getDatabaseName();
		}

		$hasMany = array();
		$belongsTo = array();
		$foreignKeys = array();
		if($defineRelations||$defineForeignKeys){
			foreach($db->listTables($schema) as $name){
				if($db->tableExists($name, $schema)){
					if($defineRelations){
						if(!isset($hasMany[$name])){
							$hasMany[$name] = array();
						}
						if(!isset($belongsTo[$name])){
							$belongsTo[$name] = array();
						}
					}
					if($defineForeignKeys){
						$foreignKeys[$name] = array();
					}
					foreach($db->describeTable($name, $schema) as $field){
						if(preg_match('/([a-z_]+)_id$/', $field['Field'], $matches)){
							if($defineRelations){
								$hasMany[$matches[1]][Utils::camelize($name)] = array(
									'fields' => 'id',
									'relationFields' => $field['Field']
								);
								$belongsTo[$name][Utils::camelize($matches[1])] = array(
									'fields' => $field['Field'],
									'relationFields' => 'id'
								);
							}
							if($defineForeignKeys){
								$foreignKeys[$name][] = array(
									'fields' => $field['Field'],
									'entity' => Utils::camelize($matches[1]),
									'referencedFields' => 'id'
								);
							}
						}
					}
					foreach($db->describeReferences($name, $schema) as $reference){
						if($defineRelations){
							if($reference['referencedSchema']==$schema){
								if(count($reference['columns'])==1){
									$belongsTo[$name][Utils::camelize($reference['referencedTable'])] = array(
										'fields' => $reference['columns'][0],
										'relationFields' => $reference['referencedColumns'][0]
									);
									$hasMany[$reference['referencedTable']][$name] = array(
										'fields' => $reference['columns'][0],
										'relationFields' => $reference['referencedColumns'][0]
									);
								} else {

								}
							}
						}
						if($defineForeignKeys){
							if($reference['referencedSchema']==$schema){
								if(count($reference['columns'])==1){
									$foreignKeys[$name][] = array(
										'fields' => $reference['columns'][0],
										'entity' => Utils::camelize($reference['referencedTable']),
										'referencedFields' => $reference['referencedColumns'][0]
									);
								}
							}
						}
					}
				} else {
					throw new ScriptException("No existe la tabla '$name'");
				}
			}
		} else {
			foreach($db->listTables($schema) as $name){
				if($defineRelations){
					$hasMany[$name] = array();
					$belongsTo[$name] = array();
					$foreignKeys[$name] = array();
				}
			}
		}

		foreach($db->listTables($schema) as $name){
			if(!file_exists($modelsDir.'/'.$name.'.php')||$forceProcess){
				$modelBuilder = Builder::factory('Model', array(
					'name' => $name,
					'application' => $application,
					'schema' => $schema,
					'force' => $forceProcess,
					'hasMany' => @$hasMany[$name],
					'belongsTo' => @$belongsTo[$name],
					'foreignKeys' => @$foreignKeys[$name]
				));
				$modelBuilder->build();
			} else {
				echo "INFO: Saltando el modelo \"$name\" ya que el archivo de modelo ya existe\n";
			}
		}

	}

}

try {
	$script = new CreateAllModels();
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
	echo "Exception : ".$e->getMessage()."\n";
}
