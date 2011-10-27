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
 * @package		ActiveRecord
 * @subpackage	ActiveRecordMetaData
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: ActiveRecordMetaData.php 103 2009-10-09 01:30:42Z gutierrezandresfelipe $
 */

/**
 * ActiveRecordMetaData
 *
 * Gran parte de la ciencia en la implementación de ActiveRecord está
 * relacionada con la administración de los metadatos de las tablas
 * mapeadas.
 *
 * El almacenamiento de sus características es punto fundamental para
 * la utilización de los métodos que consultan, borran, modifican,
 * almacenan, etc.
 *
 * El subcomponente ActiveRecordMetadata implementa el patrón
 * Metadata Mapping el cual permite crear un data map por schema sobre
 * la información de las tablas y así reducir el consumo de memoria
 * por objeto ActiveRecord consolidando una base de datos in-memory
 * de las características de cada entidad utilizada en la aplicación.
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	ActiveRecordMetaData
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */
abstract class ActiveRecordMetaData {

	/**
	 * Indica si está disponible APC
	 *
	 * @var null
	 */
	private static $_hasAPC = null;

	/**
	 * Indica si esta disponible SHMOP
	 *
	 * @var boolean
	 */
	static private $_hasSharedMemory = null;

	/**
	 * Id de acceso para el bloque de memoria compartida
	 *
	 * @var int
	 */
	static private $_sharedId;

	/**
	 * Identificadores unicos para memoria compartida
	 *
	 * @var boolean
	 */
	static private $_sharedKeys = array();

	/**
	 * Identificadores unicos para APC
	 *
	 * @var boolean
	 */
	static private $_apcKeys = array();

	/**
	 * Meta-data temporal para modelos
	 *
	 * @var array
	 * @staticvar
	 */
	static private $_metaData;

	/**
	 * Constante para indexar los atributos de los modelos
	 *
	 */
	const MODELS_ATTRIBUTES = 0;

	/**
	 * Constante para indexar la llave primaria de los modelos
	 *
	 */
	const MODELS_PRIMARY_KEY = 1;

	/**
	 * Constante para indexar los campos que no son llave primaria de los modelos
	 *
	 */
	const MODELS_NON_PRIMARY_KEY = 2;

	/**
	 * Constante para indexar los campos que no nulos
	 *
	 */
	const MODELS_NOT_NULL = 3;

	/**
	 * Constante para indexar los tipos de datos
	 *
	 */
	const MODELS_DATA_TYPE = 4;

	/**
	 * Constante para indexar datos de tipo numérico
	 *
	 */
	const MODELS_DATA_TYPE_NUMERIC = 5;

	/**
	 * Constante para indexar campos de fecha automática al crear
	 *
	 */
	const MODELS_DATE_AT = 6;

	/**
	 * Constante para indexar campos de fecha automática al modificar
	 *
	 */
	const MODELS_DATE_IN = 7;

	/**
	 * Permite definir los atributos de un modelo en forma de memoria compartida
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	array $attributes
	 * @static
	 */
	static public function setAttributes($tableName, $schemaName, $attributes){
		self::$_modelsAttributes[$schemaName][$tableName][self::MODELS_ATTRIBUTES] = $attributes;
	}

	/**
	 * Obtiene los atributos de un modelo en forma de memoria compartida
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @return	array
	 * @static
	 */
	static public function getAttributes($tableName, $schemaName){
		if(isset(self::$_metaData[$schemaName][$tableName][self::MODELS_ATTRIBUTES])){
			return self::$_metaData[$schemaName][$tableName][self::MODELS_ATTRIBUTES];
		} else {
			return array();
		}
	}

	/**
	 * Permite definir los atributos que son llave primaria de un modelo en forma de memoria compartida
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	array $primaryKey
	 * @return	array
	 * @static
	 */
	static public function setPrimaryKeys($tableName, $schemaName, $primaryKey){
		self::$_modelsPrimaryKeys[$schemaName][$tableName][self::MODELS_PRIMARY_KEY] = $primaryKey;
	}

	/**
	 * Obtiene los atributos de un modelo que son llave primaria en forma de memoria compartida
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @return	array
	 */
	static public function getPrimaryKeys($tableName, $schemaName){
		if(isset(self::$_metaData[$schemaName][$tableName][self::MODELS_PRIMARY_KEY])){
			return self::$_metaData[$schemaName][$tableName][self::MODELS_PRIMARY_KEY];
		} else {
			return array();
		}
	}

	/**
	 * Permite definir los atributos que no son llave primaria de un modelo en forma de memoria compartida
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	array $nonPrimaryKey
	 */
	static public function setNonPrimaryKeys($tableName, $schemaName, $nonPrimaryKey){
		self::$_modelsNonPrimaryKeys[$schemaName][$tableName][self::MODELS_NON_PRIMARY_KEY] = $nonPrimaryKey;
	}

	/**
	 * Obtiene los atributos de un modelo que no son llave primaria en forma de memoria compartida
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @return	array
	 */
	static public function getNonPrimaryKeys($tableName, $schemaName){
		if(isset(self::$_metaData[$schemaName][$tableName][self::MODELS_NON_PRIMARY_KEY])){
			return self::$_metaData[$schemaName][$tableName][self::MODELS_NON_PRIMARY_KEY];
		} else {
			return array();
		}
	}

	/**
	 * Permite definir los atributos que no permiten nulos de un modelo en forma de memoria compartida
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	array $notNull
	 */
	static public function setNotNull($tableName, $schemaName, $notNull){
		self::$_modelsNonPrimaryKeys[$schemaName][$tableName][self::MODELS_NOT_NULL] = $notNull;
	}

	/**
	 * Obtiene los atributos de un modelo que no permiten nulos en forma de memoria compartida
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @return	array
	 */
	static public function getNotNull($tableName, $schemaName){
		if(isset(self::$_metaData[$schemaName][$tableName][self::MODELS_NOT_NULL])){
			return self::$_metaData[$schemaName][$tableName][self::MODELS_NOT_NULL];
		} else {
			return array();
		}
	}

	/**
	 * Permite definir los tipos de datos de atributos de un modelo en forma de memoria compartida
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	array $dataType
	 * @static
	 */
	static public function setDataType($tableName, $schemaName, $dataType){
		self::$_modelsNonPrimaryKeys[$schemaName][$tableName][self::MODELS_DATA_TYPE] = $dataType;
	}

	/**
	 * Obtiene los tipos de datos de atributos en forma de memoria compartida
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @return	array
	 */
	static public function getDataTypes($tableName, $schemaName){
		if(isset(self::$_metaData[$schemaName][$tableName][self::MODELS_DATA_TYPE])){
			return self::$_metaData[$schemaName][$tableName][self::MODELS_DATA_TYPE];
		} else {
			return array();
		}
	}

	/**
	 * Obtiene los tipos de datos de atributos que sean numéricos en forma de memoria compartida
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @return	array
	 */
	static public function getDataTypesNumeric($tableName, $schemaName){
		if(isset(self::$_metaData[$schemaName][$tableName][self::MODELS_DATA_TYPE_NUMERIC])){
			return self::$_metaData[$schemaName][$tableName][self::MODELS_DATA_TYPE_NUMERIC];
		} else {
			return array();
		}
	}

	/**
	 * Permite definir los tipos de datos de atributos de un modelo en forma de memoria compartida
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	array $datesAt
	 * @static
	 */
	static public function setDatesAt($tableName, $schemaName, $datesAt){
		self::$_modelsNonPrimaryKeys[$schemaName][$tableName][self::MODELS_DATE_AT] = $datesAt;
	}

	/**
	 * Obtiene los tipos de datos de atributos en forma de memoria compartida
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @return	array
	 * @static
	 */
	static public function getDatesAt($tableName, $schemaName){
		if(isset(self::$_metaData[$schemaName][$tableName][self::MODELS_DATE_AT])){
			return self::$_metaData[$schemaName][$tableName][self::MODELS_DATE_AT];
		} else {
			return array();
		}
	}

	/**
	 * Permite definir los campos con fecha de atributo _in
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	array $datesIn
	 * @static
	 */
	static public function setDatesIn($tableName, $schemaName, $datesIn){
		self::$_modelsNonPrimaryKeys[$schemaName][$tableName][self::MODELS_DATE_IN] = $datesIn;
	}

	/**
	 * Obtiene los campos con fecha de atributo _in
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @return	array
	 * @static
	 */
	static public function getDatesIn($tableName, $schemaName){
		if(isset(self::$_metaData[$schemaName][$tableName][self::MODELS_DATE_IN])){
			return self::$_metaData[$schemaName][$tableName][self::MODELS_DATE_IN];
		} else {
			return array();
		}
	}

	/**
	 * Crea un registro de meta datos para la tabla especificada
	 *
	 * @param string $table
	 * @param string $schema
	 * @param array $metaData
	 */
	static public function existsMetaData($table, $schema){
		if(isset(self::$_metaData[$schema][$table])){
			return true;
		} else {
			$enviroment = CoreConfig::getAppSetting('mode');
			if($enviroment=='production'){
				if($schema){
					$source = $schema.'.'.$table;
				} else {
					$source = $table;
				}
				self::_initializeAPC();
				if(self::$_hasAPC==true){
					$sharedKey = self::_getAPCKey($source);
					$metadata = apc_fetch($sharedKey);
					if($metadata!==false){
						self::$_metaData[$schema][$table] = $metadata;
						return true;
					} else {
						return false;
					}
				} else {
					self::_initializeSharedMemory();
					if(self::$_hasSharedMemory==true){
						$sharedKey = self::_getSharedKey($source);
						$metadata = @shm_get_var(self::$_sharedId, $sharedKey);
						if($metadata!==false){
							self::$_metaData[$schema][$table] = $metadata;
							return true;
						} else {
							return false;
						}
					} else {
						$modelsDir = Core::getActiveModelsDir();
						if(file_exists($modelsDir.'/metadata/'.$source.'.php')){
							$metadata = file_get_contents($modelsDir.'/metadata/'.$source.'.php');
							self::$_metaData[$schema][$table] = unserialize($metadata);
							return true;
						} else {
							return false;
						}
					}
				}
			} else {
				return false;
			}
		}
	}

	/**
	 * Establece los meta-datos de una tabla
	 *
	 * @param	string $table
	 * @param	string $schema
	 * @param	string $attribute
	 * @param	array $definition
	 */
	static public function setAttributeMetadata($table, $schema, $attribute, $definition){
		if(!isset(self::$_metaData[$schema][$table][self::MODELS_ATTRIBUTES])){
			self::$_metaData[$schema][$table][self::MODELS_ATTRIBUTES] = array($attribute);
		} else {
			if(!in_array($attribute, self::$_metaData[$schema][$table][self::MODELS_ATTRIBUTES])){
		 		self::$_metaData[$schema][$table][self::MODELS_ATTRIBUTES][] = $attribute;
			}
		}
	}

	static private function _initializeAPC(){
		if(self::$_hasAPC===null){
			if(function_exists('apc_store')){
				self::$_hasAPC = true;
			} else {
				self::$_hasAPC = false;
			}
		}
	}

	/**
	 * Inicializa el puntero de memoria compartida. Dependiendo del sistema se debe ajustar el
	 * tamaño maximo de memoria compartida posible a utilizar
	 *
	 */
	static private function _initializeSharedMemory(){
		if(self::$_hasSharedMemory===null){
			if(function_exists('shm_attach')){
				if(PHP_OS=='Darwin'){
					self::$_sharedId = shm_attach(0xff7, 0xffff, 0644);
				} else {
					self::$_sharedId = shm_attach(0xff7, 0x7d000, 0777);
				}
				self::$_hasSharedMemory = true;
			} else {
				self::$_hasSharedMemory = false;
			}
		}
	}

	/**
	 * Devuelve un identicador único de APC según el source
	 *
	 * @param	string $source
	 * @return	int
	 */
	static private function _getAPCKey($source){
		if(!isset(self::$_apcKeys[$source])){
			$sharedKey = Router::getApplication().$source;
			self::$_apcKeys[$source] = $sharedKey;
		}
		return self::$_apcKeys[$source];
	}

	/**
	 * Devuelve un identicador único de acuerdo al source
	 *
	 * @param	string $source
	 * @return	int
	 */
	static private function _getSharedKey($source){
		if(!isset(self::$_sharedKeys[$source])){
			$pad = 1;
			$sharedKey = 0;
			$sourceKey = Router::getApplication().$source;
			$length = strlen($sourceKey);
			for($j=0;$j<$length;++$j){
				$sharedKey+=($pad*ord($sourceKey[$j]));
				$pad+=10;
			}
			self::$_sharedKeys[$source] = $sharedKey;
		}
		return self::$_sharedKeys[$source];
	}

	/**
	 * Trae los metadatos de la base de tatos
	 *
	 * @param	string $table
	 * @param	string $schema
	 * @param	array $metaData
	 * @static
	 */
	static public function dumpMetaData($table, $schema, $metaData){
		$fields = array();
		$primaryKey = array();
		$nonPrimary = array();
		$notNull = array();
		$dataType = array();
		$dataTypeNumeric = array();
		$at = array();
		$in = array();
		$numericTypes = array('int', 'decimal', 'number', 'smallint', 'float', 'smallfloat', 'bigint');
		foreach($metaData as $field){
			$fields[] = $field['Field'];
			if($field['Key']=='PRI'){
				$primaryKey[] = $field['Field'];
			} else {
				$nonPrimary[] = $field['Field'];
			}
			if($field['Null']=='NO'){
				$notNull[] = $field['Field'];
			}
			if(isset($field['Type'])){
				$dataType[$field['Field']] = strtolower($field['Type']);
			}
			if(preg_match('/_at$/', $field['Field'])){
				$at[$field['Field']] = 1;
			} else {
				if(preg_match('/_in$/', $field['Field'])){
					$in[$field['Field']] = 1;
				}
			}
			foreach($numericTypes as $type){
				if(preg_match('/^'.$type.'/', $field['Type'])){
					$dataTypeNumeric[$field['Field']] = true;
				}
			}
			unset($field);
		}
		if($schema){
			$source = $schema.'.'.$table;
		} else {
			$source = $table;
		}
		#if[compile-time]
		if(count($fields)==0){
			throw new ActiveRecordMetaDataException("Meta-datos inválidos para '$table'");
		}
		#endif
		self::$_metaData[$schema][$table][self::MODELS_ATTRIBUTES] = $fields;
		self::$_metaData[$schema][$table][self::MODELS_PRIMARY_KEY] = $primaryKey;
		self::$_metaData[$schema][$table][self::MODELS_NON_PRIMARY_KEY] = $nonPrimary;
		self::$_metaData[$schema][$table][self::MODELS_NOT_NULL] = $notNull;
		self::$_metaData[$schema][$table][self::MODELS_DATA_TYPE] = $dataType;
		self::$_metaData[$schema][$table][self::MODELS_DATA_TYPE_NUMERIC] = $dataTypeNumeric;
		self::$_metaData[$schema][$table][self::MODELS_DATE_AT] = $at;
		self::$_metaData[$schema][$table][self::MODELS_DATE_IN] = $in;

		//Grabar meta-data en un bodegas persistentes
		$enviroment = CoreConfig::getAppSetting('mode');
		if($enviroment=='production'){
			$modelsDir = Core::getActiveModelsDir();
			if(self::$_hasAPC==true){
				$sharedKey = self::_getAPCKey($source);
				apc_store($sharedKey, self::$_metaData[$schema][$table]);
				unset($sharedKey);
			} else {
				self::_initializeSharedMemory();
				if(self::$_hasSharedMemory==true){
					$sharedKey = self::_getSharedKey($source);
					shm_put_var(self::$_sharedId, $sharedKey, self::$_metaData[$schema][$table]);
					unset($sharedKey);
				} else {
					if(!file_exists($modelsDir.'/metadata')){
						mkdir($modelsDir.'/metadata');
					}
					file_put_contents($modelsDir.'/metadata/'.$source.'.php', serialize(self::$_metaData[$schema][$table]));
			 	}
			}
			unset($modelsDir);
		}

		unset($schema);
		unset($table);
		unset($source);
		unset($fields);
		unset($primaryKey);
		unset($nonPrimary);
		unset($notNull);
		unset($dataType);
		unset($dataTypeNumeric);
		unset($at);
		unset($in);
	}

}
