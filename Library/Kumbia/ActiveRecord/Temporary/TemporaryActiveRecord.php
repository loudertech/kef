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
 * @subpackage	TemporaryActiveRecord
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: TemporaryActiveRecord.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * TemporaryActiveRecord
 *
 * El componente ActiveRecord proporciona el subcomponente TemporaryActiveRecord
 * el cual permite crear modelos que administran sus datos sobre entidades
 * temporales en el gestor relacional.
 *
 * Este tipo de entidades pueden ser consideradas de alto rendimiento
 * ya que no requieren de escritura de disco además este tipo de modelos
 * están optimizados para un procesamiento más efectivo en memoria.
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	TemporaryActiveRecord
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class TemporaryActiveRecord extends ActiveRecordBase {

	/**
	 * MetaDato de llaves primarias
	 *
	 * @var array
	 */
	static private $_primaryKeys = array();

	/**
	 * MetaDato de campos no llave primaria
	 *
	 * @var array
	 */
	static private $_nonPrimaryKeys = array();

	/**
	 * MetaDato de llaves primarias
	 *
	 * @var array
	 */
	static private $_notNull = array();

	/**
	 * MetaDato de atributos temporal
	 *
	 * @var array
	 */
	static private $_attributes = array();

	/**
	 * MetaDato de Tipos de datos temporal
	 *
	 * @var array
	 */
	static private $_dataTypes = array();

	/**
	 * Fechas _at
	 *
	 * @var array
	 */
	static private $_datesAt = array();

	/**
	 * Fechas _in
	 *
	 * @var array
	 */
	static private $_datesIn = array();

	/**
	 * Indica si hay bloqueo sobre los warnings cuando una propiedad
	 * del modelo no esta definida-
	 *
	 * @var boolean
	 * @access protected
	 */
	protected $_dumpLock = false;

	/**
	 * Constructor de la tabla
	 *
	 * @access public
	 */
	public function __construct(){
		if(Facility::getFacility()==Facility::USER_LEVEL){
			$tableName = $this->getSource();
			$schemaName = $this->getSchema();
			if($schemaName==''){
				$schemaName = $this->getConnection()->getDatabaseName();
			}
			if(EntityManager::existsTemporaryEntity($tableName, $schemaName)==false){
				$this->_createTemporaryTable($tableName, $schemaName);
				EntityManager::addTemporaryEntity($tableName);
			}
		}
		parent::__construct();
	}

	/**
	 * Crea la tabla temporal en el gestor relacional
	 *
	 * @access	private
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	DbBase $db
	 */
	private function _createTemporaryTable($tableName, $schemaName, $db=null){
		if(method_exists($this, '_tableDefinition')==true){
			$tableDefinition = $this->_tableDefinition();
			if(!isset($tableDefinition['attributes'])){
				throw new ActiveRecordException('Los atributos en la definición de la entidad no son correctos');
			}
			if(!isset($tableDefinition['indexes'])){
				$tableDefinition['indexes'] = array();
			}
			if($db==null){
				$db = DbPool::getConnection();
			}
			if($db->createTable($tableName, $schemaName, $tableDefinition['attributes'], $tableDefinition['indexes'], array('temporary' => true))==false){
				throw new Exception("No se pudo crear la tabla temporal '$tableName'");
			}
			$this->_dumpLock = true;
			foreach($tableDefinition['attributes'] as $attributeName => $definition){
				if(isset($this->$attributeName)==false){
					$this->$attributeName = '';
				}
			}
			$this->_dumpLock = false;
		} else {
			throw new ActiveRecordException('No ha definido el metodo "_tableDefinition" para obtener la definición de la tabla temporal');
		}
	}

	/**
	 * Volca la información de la tabla $tableName en la base de datos
	 * para crear los atributos y meta-data del TemporaryActiveRecord
	 *
	 * @access	protected
	 * @param	string $tablename
	 * @param	string $schemaName
	 * @return	boolean
	 */
	protected function _dumpInfo($tableName, $schemaName=''){
		$db = $this->getConnection();
		$tableName = i18n::strtolower($tableName);
		$schemaName = i18n::strtolower($schemaName);
		if($db->temporaryTableExists($tableName, $schemaName)==false){
			$this->_createTemporaryTable($tableName, $schemaName, $db);
		}
	}

	/**
	 * Controla que la tabla temporal exista en la conexion actual (no es necesario)
	 *
	 * @access	protected
	 * @return	boolean
	 */
	protected function dump(){
		$connection = $this->getConnection();
		$entityName = get_class($this);
		if(EntityManager::isCreatedTemporaryEntity($connection, $entityName)==false){
			if($this->_schema==""){
				$schemaName = $connection->getDatabaseName();
			} else {
				$schemaName = $this->_schema;
			}
			$this->_dumpInfo($entityName, $schemaName);
			EntityManager::addTemporaryEntity($entityName);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Destruye la entidad del gestor relacional
	 *
	 * @param	DbBase $connection
	 * @access	public
	 */
	public function destroy($connection=null){
		$tableName = get_class($this);
		if($connection==null){
			$connection = $this->getConnection();
		}
		$connection->dropTable($tableName);
	}

	/**
	 * Devuelve los atributos de la entidad
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function _getAttributes(){
		if(!isset(self::$_attributes[get_class($this)])){
			$tableDefinition = $this->_tableDefinition();
			$attributes = array();
			foreach($tableDefinition['attributes'] as $attributeName => $definition){
				$attributes[] = $attributeName;
			}
			self::$_attributes[get_class($this)] = $attributes;
			return $attributes;
		} else {
			return self::$_attributes[get_class($this)];
		}
	}

	/**
	 * Obtiene los tipos de datos de los atributos
	 *
	 * @access protected
	 * @return array
	 */
	protected function _getDataTypes(){
		if(!isset(self::$_dataTypes[get_class($this)])){
			$tableDefinition = $this->_tableDefinition();
			$dataTypes = array();
			foreach($tableDefinition['attributes'] as $attributeName => $definition){
				$dataTypes[$attributeName] = strtolower($definition['type']);
			}
			self::$_dataTypes[get_class($this)] = $dataTypes;
			return $dataTypes;
		} else {
			return self::$_dataTypes[get_class($this)];
		}
	}

	/**
	 * Devuelve los atributos que son llave primaria
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function _getPrimaryKeyAttributes(){
		if(!isset(self::$_primaryKeys[get_class($this)])){
			$tableDefinition = $this->_tableDefinition();
			$primaryKeys = array();
			foreach($tableDefinition['attributes'] as $attributeName => $definition){
				if(isset($definition['primary'])&&$definition['primary']==true){
					$primaryKeys[] = $attributeName;
				}
			}
			self::$_primaryKeys[get_class($this)] = $primaryKeys;
			return $primaryKeys;
		} else {
			return self::$_primaryKeys[get_class($this)];
		}
	}

	/**
	 * Devuelve los atributos que no son llave primaria
	 *
	 * @access protected
	 * @return array
	 */
	protected function _getNonPrimaryKeyAttributes(){
		if(!isset(self::$_nonPrimaryKeys[get_class($this)])){
			$tableDefinition = $this->_tableDefinition();
			$nonPrimaryKeys = array();
			foreach($tableDefinition['attributes'] as $attributeName => $definition){
				if(!isset($definition['primary'])||$definition['primary']==false){
					$nonPrimaryKeys[] = $attributeName;
				}
			}
			self::$_nonPrimaryKeys[get_class($this)] = $nonPrimaryKeys;
			return $nonPrimaryKeys;
		} else {
			return self::$_nonPrimaryKeys[get_class($this)];
		}
	}

	/**
	 * Devuelve los atributos que no permiten nulos
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function _getNotNullAttributes(){
		if(!isset(self::$_notNull[get_class($this)])){
			$tableDefinition = $this->_tableDefinition();
			$notNull = array();
			foreach($tableDefinition['attributes'] as $attributeName => $definition){
				if(isset($definition['notNull'])&&$definition['notNull']==false){
					$notNull[] = $attributeName;
				}
			}
			self::$_notNull[get_class($this)] = $notNull;
			return $notNull;
		} else {
			return self::$_notNull[get_class($this)];
		}
	}

	/**
	 *  Devuelve los campos fecha que asignan la fecha del sistema automaticamente al insertar (internal)
	 *
	 * @access protected
	 * @return array
	 */
	protected function _getDatesAtAttributes(){
		if(!isset(self::$_datesAt[get_class($this)])){
			$tableDefinition = $this->_tableDefinition();
			$datesAt = array();
			foreach($tableDefinition['attributes'] as $attributeName => $definition){
				if($definition['type']=='date'){
					if(preg_match('/_at$/', $attributeName)){
						$datesAt[] = $attributeName;
					}
				}
			}
			self::$_datesAt[get_class($this)] = $datesAt;
			return $datesAt;
		} else {
			return self::$_datesAt[get_class($this)];
		}
	}

	/**
	 *  Devuelve los campos fecha que asignan la fecha del sistema automaticamente al modificar (internal)
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function _getDatesInAttributes(){
		if(!isset(self::$_datesIn[get_class($this)])){
			$tableDefinition = $this->_tableDefinition();
			$datesIn = array();
			foreach($tableDefinition['attributes'] as $attributeName => $definition){
				if($definition['type']=='date'){
					if(preg_match('/_in$/', $attributeName)){
						$datesIn[] = $attributeName;
					}
				}
			}
			self::$_datesIn[get_class($this)] = $datesIn;
			return $datesIn;
		} else {
			return self::$_datesIn[get_class($this)];
		}
	}

}

