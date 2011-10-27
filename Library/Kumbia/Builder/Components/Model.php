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
 * @package		Builder
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: Model.php,v a434b34d7989 2011/10/26 22:23:04 andres $
 */

/**
 * ModelBuilderComponent
 *
 * Builder para construir modelos
 *
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: Model.php,v a434b34d7989 2011/10/26 22:23:04 andres $
 */
class ModelBuilderComponent {

	/**
	 * Opciones del ModelBuilder
	 *
	 * @var array
	 */
	private $_options = array();

	/**
	 * Mapa de datos escalares a objetos
	 *
	 * @var array
	 */
	private $_typeMap = array(
		'Date' => 'Date',
		'Decimal' => 'Decimal'
	);

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
		if(stripos($type, 'decimal')!==false){
			return 'Decimal';
		}
		return 'string';
	}

	public function __construct($options){
		if(!isset($options['name'])){
			throw new BuilderException("No se indicó el nombre del modelo");
		}
		if(!isset($options['force'])){
			$options['force'] = false;
		}
		if(!isset($options['className'])){
			$options['className'] = Utils::camelize($options['name']);
		}
		if(!isset($options['fileName'])){
			$options['fileName'] = $options['name'];
		}
		$this->_options = $options;
	}

	public function build(){
		if($this->_options['name']){

			$modelsDir = Core::getActiveModelsDir();
			$modelPath = $modelsDir.'/'.$this->_options['fileName'].'.php';

			if(file_exists($modelPath)){
				if(!$this->_options['force']){
					throw new BuilderException("El archivo del modelo '{$this->_options['name']}.php' ya existe en el directorio de modelos");
				}
			}

			if(!DbLoader::loadDriver()){
				throw new DbException('No se puede conectar a la base de datos');
			}

			$initialize = array();

			$db = DbBase::rawConnect();
			if(isset($this->_options['schema'])){
				if($this->_options['schema']!=$db->getDatabaseName()){
					$initialize[] = "\t\t\$this->setSchema(\"{$this->_options['schema']}\");";
				}
				$schema = $this->_options['schema'];
			} else {
				$schema = null;
			}
			if($this->_options['fileName']!=$this->_options['name']){
				$initialize[] = "\t\t\$this->setSource(\"{$this->_options['name']}\");";
			}

			$table = $this->_options['name'];
			if($db->tableExists($table, $schema)){
				$fields = $db->describeTable($table, $schema);
			} else {
				throw new BuilderException("No existe la tabla $table");
			}

			if(isset($this->_options['hasMany'])){
				if(count($this->_options['hasMany'])){
					foreach($this->_options['hasMany'] as $entityName => $relation){
						if(is_string($relation['fields'])){
							if(preg_match('/_id$/', $relation['relationFields'])&&$relation['fields']=='id'){
								$initialize[] = "\t\t\$this->hasMany(\"$entityName\")";
							} else {
								$initialize[] = "\t\t\$this->hasMany(\"{$relation['fields']}\", \"$entityName\", \"{$relation['relationFields']}\")";
							}
						}
					}
				}
			}

			if(isset($this->_options['belongsTo'])){
				if(count($this->_options['belongsTo'])){
					foreach($this->_options['belongsTo'] as $entityName => $relation){
						if(is_string($relation['fields'])){
							if(preg_match('/_id$/', $relation['fields'])&&$relation['relationFields']=='id'){
								$initialize[] = "\t\t\$this->belongsTo(\"$entityName\")";
							} else {
								$initialize[] = "\t\t\$this->belongsTo(\"{$relation['fields']}\", \"$entityName\", \"{$relation['relationFields']}\")";
							}
						} else {

						}
					}
				}
			}

			if(isset($this->_options['foreignKeys'])){
				if(count($this->_options['foreignKeys'])){
					foreach($this->_options['foreignKeys'] as $foreignKey){
						$initialize[] = "\t\t\$this->addForeignKey(\"{$foreignKey['fields']}\", \"{$foreignKey['entity']}\", \"{$foreignKey['referencedFields']}\")";
					}
				}
			}

			$methodRawCode = array();
			$alreadyInitialized = false;
			$alreadyValidations = false;
			if(file_exists($modelPath)){
				try {
					$posibleMethods = array();
					foreach($fields as $field){
						$methodName = Utils::camelize($field['Field']);
						$posibleMethods['set'.$methodName] = true;
						$posibleMethods['get'.$methodName] = true;
					}
					require $modelPath;
					$linesCode = file($modelPath);
					$reflection = new ReflectionClass($this->_options['className']);
					foreach($reflection->getMethods() as $method){
						if($method->getDeclaringClass()->getName()==$this->_options['className']){
							$methodName = $method->getName();
							if(!isset($posibleMethods[$methodName])){
								$methodRawCode[$methodName] = join('', array_slice($linesCode, $method->getStartLine()-1, $method->getEndLine()-$method->getStartLine()+1));
							} else {
								continue;
							}
							if($methodName=='initialize'){
								$alreadyInitialized = true;
							} else {
								if($methodName=='validation'){
									$alreadyValidations = true;
								}
							}
						}
					}
				}
				catch(ReflectionException $e){

				}
			}

			$validations = array();
			foreach($fields as $field){
				if(strpos($field['Type'], 'enum')!==false){
					$domain = array();
					if(preg_match('/\((.*)\)/', $field['Type'], $matches)){
						foreach(explode(',', $matches[1]) as $item){
							$domain[] = $item;
						}
					}
					$varItems = join(', ', $domain);
					$validations[] = "\t\t\$this->validate(\"InclusionIn\", array(\n\t\t\t\"field\" => \"{$field['Field']}\",\n\t\t\t\"domain\" => array($varItems),\n\t\t\t\"required\" => true\n\t\t))";
				}
				if($field['Field']=='email'){
					$validations[] = "\t\t\$this->validate(\"Email\", array(\n\t\t\t\"field\" => \"{$field['Field']}\",\n\t\t\t\"required\" => true\n\t\t))";
				}
			}
			if(count($validations)){
				$validations[] = "\t\tif(\$this->validationHasFailed()==true){\n\t\t\treturn false;\n\t\t}";
			}

			$attributes = array();
			$setters = array();
			$getters = array();
			foreach($fields as $field){
				$type = $this->getPHPType($field['Type']);
				$attributes[] = "\t/**\n\t * @var $type\n\t */\n\tprotected \${$field['Field']};\n";
				$setterName = Utils::camelize($field['Field']);
				$setters[] = "\t/**\n\t * Metodo para establecer el valor del campo {$field['Field']}\n\t * @param $type \${$field['Field']}\n\t */\n\tpublic function set$setterName(\${$field['Field']}){\n\t\t\$this->{$field['Field']} = \${$field['Field']};\n\t}\n";
				if(isset($this->_typeMap[$type])){
					$getters[] = "\t/**\n\t * Devuelve el valor del campo {$field['Field']}\n\t * @return $type\n\t */\n\tpublic function get$setterName(){\n\t\tif(\$this->{$field['Field']}){\n\t\t\treturn new {$this->_typeMap[$type]}(\$this->{$field['Field']});\n\t\t} else {\n\t\t\treturn null;\n\t\t}\n\t}\n";
				} else {
					$getters[] = "\t/**\n\t * Devuelve el valor del campo {$field['Field']}\n\t * @return $type\n\t */\n\tpublic function get$setterName(){\n\t\treturn \$this->{$field['Field']};\n\t}\n";
				}
			}
			if($alreadyValidations==false){
				if(count($validations)>0){
					$validationsCode = "\n\t/**\n\t * Validaciones y reglas de negocio\n\t */\n\tprotected function validation(){\t\t\n".join(";\n", $validations)."\n\t}\n";
				} else {
					$validationsCode = "";
				}
			} else {
				$validationsCode = "";
			}
			if($alreadyInitialized==false){
				if(count($initialize)>0){
					$initCode = "\n\t/**\n\t * Metodo inicializador de la Entidad\n\t */\n\tprotected function initialize(){\t\t\n".join(";\n", $initialize).";\n\t}\n";
				} else {
					$initCode = "";
				}
			} else {
				$initCode = "";
			}
			$code = "<?php\n";
			if(file_exists('license.txt')){
				$code.=PHP_EOL.file_get_contents('license.txt');
			}
			$code.="\nclass ".$this->_options['className']." extends ActiveRecord {\n\n".join("\n", $attributes)."\n\n".join("\n", $setters)."\n\n".join("\n", $getters).$validationsCode.$initCode."\n";
			foreach($methodRawCode as $methodCode){
				$code.=$methodCode.PHP_EOL;
			}
			$code.="}\n\n";
			file_put_contents("$modelsDir/{$this->_options['name']}.php", $code);

		} else {
			throw new BuilderException("Debe indicar el nombre del modelo");
		}
	}

}