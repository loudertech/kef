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
 * @package		Db
 * @subpackage	Adapters
 * @subpackage 	NoSQL
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * Cassandra Database Support
 *
 * Estas funciones le permiten acceder a servidores de bases de datos Cassandra.
 * Puede encontrar más información sobre Cassandra en http://cassandra.apache.org/
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	Adapters
 * @subpackage 	NoSQL
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @link		http://cassandra.apache.org/
 * @access		Public
 */
class DbCassandra extends DbBase
#if[compile-time]
	implements DbBaseInterface
#endif
	{

	/**
	 * Ultimo descriptor usado para realizar una consulta
	 *
	 * @var stdClass
	 */
	protected $_descriptor;

	/**
	 * Cliente Cassandra
	 *
	 * @var CassandraClient
	 */
	private $_client;

	/**
	 * Capa de transporte usada por Thrift
	 *
	 * @var TBufferedTransport
	 */
	private $_transport;

	/**
	 * Keyspace por defecto de la conexión
	 *
	 * @var string
	 */
	private $_keySpace;

	/**
	 * Constructor de la Clase
	 *
	 * @param stdClass $descriptor
	 */
	public function __construct($descriptor=''){
		if($descriptor==''){
			$descriptor = $this->_descriptor;
		}
		$this->connect($descriptor);
	}

	/**
	 * Hace una conexión a la base de datos de Cassandra
	 *
	 * @param	stdClass $descriptor
	 * @return	CassandraClient
	 */
	public function connect($descriptor=''){
		if($descriptor==''){
			$descriptor = $this->_descriptor;
		}
		if($this->_idConnection!==null){
			return $this->_client;
			return $this->_idConnection;
		}
		$host = isset($descriptor->host) ? $descriptor->host : '127.0.0.1';
		$port = isset($descriptor->port) ? $descriptor->port : 9160;
		$keySpace = isset($descriptor->keyspace) ? $descriptor->keyspace : null;

		$this->_idConnection = 'Cassandra #'.$host.':'.$port;
		$this->_keySpace = $keySpace;

		$socket = new TSocket($descriptor->host, $descriptor->port);
		$this->_transport = new TBufferedTransport($socket, 1024, 1024);
		$protocol = new TBinaryProtocolAccelerated($this->_transport);
		$this->_client = new CassandraClient($protocol);
		$this->_transport->open();


		return $this->_idConnection;
	}

	/**
	 * Efectua operaciones sobre la base de datos
	 *
	 * @param	string $sqlStatement
	 * @return	resource|false
	 */
	public function query($sqlStatement){
		parent::beforeQuery($sqlStatement);
	}

	/**
	 * Cierra la conexión al motor de base de datos
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function close(){
		$this->_transport->close();
	}

	/**
	 * Devuelve fila por fila el contenido de un select
	 *
	 * @param	resource $resultQuery
	 * @return	array
	 */
	public function fetchArray($resultQuery=''){

	}

	/**
	 * Devuelve el número de filas de un select
	 *
	 * @access	public
	 * @param	boolean $resultQuery
	 */
	public function numRows($resultQuery=''){
		return false;
	}

	/**
	 * Devuelve el nombre de un campo en el resultado de un select
	 *
	 * @param	integer $number
	 * @param	resource $resultQuery
	 * @return	string
	 */
	public function fieldName($number, $resultQuery=''){
		return false;
	}

	/**
	 * Se Mueve al resultado indicado por $number en un select
	 *
	 * @param	integer $number
	 * @param	resource $resultQuery
	 * @return	boolean
	 */
	public function dataSeek($number, $resultQuery=null){
		return false;
	}

	/**
	 * Número de filas afectadas en un insert, update o delete
	 *
	 * @param	resource $resultQuery
	 * @return	integer
	 */
	public function affectedRows($resultQuery=''){
		return false;
	}

	/**
	 * Devuelve el error de Cassandra
	 *
	 * @param	string $errorString
	 * @return	string
	 */
	public function error($errorString='', $resultQuery=null){

	}

	/**
	 * Devuelve el no error de Cassandra
	 *
	 * @return integer|boolean
	 */
	public function noError($resultQuery=null){

	}

	/**
	 * Devuelve el último id autonumerico generado en la BD
	 *
	 * @access	public
	 * @param	string $table
	 * @param	array $primaryKey
	 * @return	integer
	 */
	public function lastInsertId($table='', $primaryKey='', $sequenceName=''){

	}

	/**
	 * Indica si el RBDM requiere de secuencias y devuelve el nombre por convencion
	 *
	 * @param	string $tableName
	 * @param	array $primaryKey
	 * @return	boolean
	 */
	public function getRequiredSequence($tableName='', $identityColumn='', $sequenceName=''){
		return false;
	}

	/**
	 * Verifica si una tabla existe o no
	 *
	 * @access	public
	 * @param	string $table
	 * @param	string $schema
	 * @return	boolean
	 */
	public function tableExists($tableName, $schemaName=''){

	}

	/**
	 * Verifica si una vista existe ó no
	 *
	 * @param string $viewName
	 * @param string $schemaName
	 */
	public function viewExists($viewName, $schemaName=''){

	}

	/**
	 * Verifica si una tabla temporal existe o no
	 *
	 * @access	public
	 * @param	string $table
	 * @param	string $schema
	 * @return	boolean
	 */
	public function temporaryTableExists($tableName, $schemaName=''){

	}

	/**
	 * Devuelve un LIMIT valido para un SELECT del RBDM
	 *
	 * @access	public
	 * @param	string $sqlQuery
	 * @param	integer $number
	 * @return	string
	 */
	public function limit($sqlQuery, $number){

	}

	/**
	 * Devuelve un FOR UPDATE válido para un SELECT del RBDM
	 *
	 * @param	string $sqlQuery
	 * @return	string
	 */
	public function forUpdate($sqlQuery){

	}

	/**
	 * Devuelve un SHARED LOCK válido para un SELECT del RBDM
	 *
	 * @param	string $sqlQuery
	 * @return	string
	 */
	public function sharedLock($sqlQuery){

	}

	/**
	 * Borra una colección de la base de datos
	 *
	 * @access	public
	 * @param	string $table
	 * @param	boolean $ifExists
	 * @return	boolean
	 */
	public function dropTable($table, $ifExists=true){
		return $this->_idConnection->dropCollection($table);
	}

	/**
	 * Crea una colección en la base de datos activa
	 *
	 * @access	public
	 * @param	string $table
	 * @param	array $definition
	 * @param	array $index
	 * @return	boolean
	 */
	public function createTable($table, $definition, $index=array(), $tableOptions=array()){
		return $this->_idConnection->createCollection($table);
	}

	/**
	 * Listar las colecciones en la base de datos
	 *
	 * @param	string $schemaName
	 * @return	array
	 */
	public function listTables($schemaName=''){
		return $this->_idConnection->listCollections();
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @param	string $table
	 * @param	string $schema
	 * @return	array
	 */
	public function describeTable($table, $schema=''){

	}

	/**
	 * Listar los campos de una vista
	 *
	 * @param	string $table
	 * @param	string $schema
	 * @return	array
	 */
	public function describeView($table, $schema=''){

	}

	/**
	 * Devuelve una fecha formateada de acuerdo al RBDM
	 *
	 * @param	string $date
	 * @param	string $format
	 * @return	string
	 */
	public function getDateUsingFormat($date, $format='YYYY-MM-DD'){
		return "'".$date."'";
	}

	/**
	 * Devuelve la fecha actual del motor
	 *
	 *@return string
	 */
	public function getCurrentDate(){

	}

	/**
	 * Permite establecer el nivel de isolacion de la conexion
	 *
	 * @param int $isolationLevel
	 */
	public function setIsolationLevel($isolationLevel){

	}

	/**
	 * Establece el modo en se que deben devolver los registros
	 *
	 * @param int $fetchMode
	 */
	public function setFetchMode($fetchMode){

	}

	/**
	 * Destructor de DbCassandra
	 *
	 */
	public function __destruct(){
		$this->close();
	}

	/**
	 * Devuelve la extension ó extensiones de PHP requeridas para
	 * usar el adaptador
	 *
	 * @return	string|array
	 * @static
	 */
	public static function getPHPExtensionRequired(){
		return null;
	}

	/**
	 * Devuelve el SQL Dialect usado (No Aplica)
	 *
	 * @return	string
	 * @static
	 */
	public static function getSQLDialect(){
		return null;
	}

	/**
	 * Sobreescribe al método DbBase::find
	 *
	 * @param	string $table
	 * @param	string $where
	 * @param	array $fields
	 * @param	array $orderBy
	 */
	public function find($table, $where='', $fields='*', $orderBy='1'){

	}

	/**
	 * Sobreescribe al método DbBase::insert
	 *
	 * @access	public
	 * @param	string $table
	 * @param	array $values
	 * @param	array $fields
	 * @param	boolean $automaticQuotes
	 * @return	boolean
	 */
	public function insert($table, $values, $fields=null, $automaticQuotes=false){

	}

	/**
	  * Sobreescribe al método DbBase::update
	 *
	 * @access	public
	 * @param	string $table
	 * @param	array $fields
	 * @param	array $values
	 * @param	string $whereCondition
	 * @param	boolean $automaticQuotes
	 * @return	boolean
	 */
	public function update($table, $fields, $values, $whereCondition=null, $automaticQuotes=false){

	}

	public function getKey($keySpace, $columnFamily, $keyName, $isSuperColumn=false){
		if($keySpace==null){
			$keySpace = $this->_keySpace;
		}
		$columnPath = new cassandra_ColumnPath();
		$columnPath->column_family = $columnFamily;
		if($isSuperColumn==false){
			$columnPath->column = $keyName;
		} else {
			$columnPath->super_column = $keyName;
		}
		try {
			$rowKey = $this->_client->get($keySpace, $keyName, $columnPath, cassandra_ConsistencyLevel::ONE);
			return $rowKey;
		}
		catch(cassandra_NotFoundException $e){
        	Debug::add('Not Found: '.$keyName);
        	return false;
        }
        catch(cassandra_InvalidRequestException $e){
			throw new DbException($e->why.' (cassandra_InvalidRequestException)', 0);
        }
	}

	/**
	 * Realiza un conteneo O(n) sobre un keySpace-columnFamily-userKey
	 *
	 * @param 	string $keySpace
	 * @param 	string $columnFamily
	 * @param	string $userKey
	 * @return 	integer
	 */
	public function countSuperKey($keySpace, $columnFamily, $userKey){
		if($keySpace==null){
			$keySpace = $this->_keySpace;
		}
		$columnParent = new cassandra_ColumnParent();
		$columnParent->column_family = $columnFamily;
		try {
			return $this->_client->get_count($keySpace, $userKey, $columnParent, cassandra_ConsistencyLevel::ONE);
		}
		catch(cassandra_InvalidRequestException $e){
			throw new DbException($e->why.' (cassandra_InvalidRequestException)', 0);
		}
	}

	/**
	 * Realiza una búsqueda en un KeySpace
	 *
	 * @param 	string $keySpace
	 * @param 	string $columnFamily
	 * @param	string $userKey
	 * @param 	array $options
	 */
	public function findSuperKey($keySpace, $columnFamily, $userKey, $options=array()){
		if($keySpace==null){
			$keySpace = $this->_keySpace;
		}

		$columnParent = new cassandra_ColumnParent();
        $columnParent->column_family = $columnFamily;
        $columnParent->super_column = null;

        $sliceRange = new cassandra_SliceRange();
        $sliceRange->start = '';
        $sliceRange->finish = '';
        if(isset($options['count'])){
        	$sliceRange->count = $options['count'];
        }

        $slicePredicate = new cassandra_SlicePredicate();
        $slicePredicate->slice_range = $sliceRange;

        return $this->_client->get_slice(
        	$keySpace,
        	$userKey,
        	$columnParent,
        	$slicePredicate,
        	cassandra_ConsistencyLevel::ONE
        );

	}

	/**
	 * Inserta una Clave-Valor en un ColumnFamily tipo Super
	 *
	 * @param	string $keySpace
	 * @param	array $data
	 * @param	int $consistencyLevel
	 * @return 	boolean
	 */
	public function insertSuperKey($keySpace, $data, $consistencyLevel){
		if($this->isReadOnly()==true){
			throw new DbException('No se puede efectuar la operación. La transacción es de solo lectura');
		}
		if($keySpace==null){
			$keySpace = $this->_keySpace;
		}
		$timestamp = time();
		try {
			foreach($data as $columnFamily => $columns){
				foreach($columns as $keyName => $values){
					$mutation = array();
					if(is_array($values)){
						foreach($values as $name => $value){
							$columnOrSuper = new cassandra_ColumnOrSuperColumn();
							$superColumn = new cassandra_SuperColumn();
							$superColumn->name = $name;
							$superColumn->columns = $this->_getArrayAsColumns($values, $timestamp);
							$superColumn->timestamp = $timestamp;
							$columnOrSuper->super_column = $superColumn;
							$mutation[$columnFamily][] = $columnOrSuper;
						}
					} else {
						$columnOrSuper = new cassandra_ColumnOrSuperColumn();
						$column = new cassandra_Column();
						$column->name = $keyName;
						$column->value = $values;
						$column->timestamp = $timestamp;
						$columnOrSuper->column = $column;
						$mutation[$columnFamily][] = $columnOrSuper;
					}
					$this->_client->batch_insert($keySpace, $keyName, $mutation, $consistencyLevel);
				}
			}
			return true;
		}
		catch(cassandra_InvalidRequestException $e){
			throw new DbException($e->why.' (cassandra_InvalidRequestException)', 0);
		}
		catch(TException $e){
			throw new DbException($e->getMessage().' (TException)', 0);
		}
	}

	/**
	 * Inserta una Clave-Valor en un ColumnFamily tipo Super
	 *
	 * @param	string $keySpace
	 * @param	array $data
	 * @param	int $consistencyLevel
	 * @return 	boolean
	 */
	public function insertKey($keySpace, $data, $consistencyLevel){
		if($this->isReadOnly()==true){
			throw new DbException('No se puede efectuar la operación. La transacción es de solo lectura');
		}
		if($keySpace==null){
			$keySpace = $this->_keySpace;
		}
		$timestamp = time();
		try {
			foreach($data as $columnFamily => $columns){
				foreach($columns as $keyName => $values){
					$mutation = array();
					$columnOrSuper = new cassandra_ColumnOrSuperColumn();
					if(is_array($values)){
						$superColumn = new cassandra_SuperColumn();
						$superColumn->name = $keyName;
						$superColumn->columns = $this->_getArrayAsColumns($values, $timestamp);
						$superColumn->timestamp = $timestamp;
						$columnOrSuper->super_column = $superColumn;
					} else {
						$columnOrSuper = new cassandra_ColumnOrSuperColumn();
						$column = new cassandra_Column();
						$column->name = $keyName;
						$column->value = $values;
						$column->timestamp = $timestamp;
						$columnOrSuper->column = $column;
					}
					$mutation[$columnFamily][] = $columnOrSuper;
					$this->_client->batch_insert($keySpace, $keyName, $mutation, $consistencyLevel);
				}
			}
			return true;
		}
		catch(cassandra_InvalidRequestException $e){
			throw new DbException($e->why.' (cassandra_InvalidRequestException)', 0);
		}
		catch(TException $e){
			throw new DbException($e->getMessage().' (TException)', 0);
		}
	}

	/**
	 * Elimina una columna según su $userKey
	 *
	 * @param 	string $keySpace
	 * @param 	string $columnFamily
	 * @param	string $userKey
	 * @param 	boolean $isSuperColumn
	 * @param 	int $consistencyLevel
	 * @return 	boolean
	 */
	public function removeKey($keySpace, $columnFamily, $userKey, $consistencyLevel){
		try {
			if($keySpace==null){
				$keySpace = $this->_keySpace;
			}
			$timestamp = time();
			$columnPath = new cassandra_ColumnPath();
			$columnPath->column_family = $columnFamily;
			//$columnPath->column = $userKey;
			return $this->_client->remove($keySpace, $userKey, $columnPath, $timestamp, $consistencyLevel);
		}
		catch(cassandra_InvalidRequestException $e){
			throw new DbException($e->why.' (cassandra_InvalidRequestException)', 0);
		}
	}

	/**
	 * Elimina una super-columna según su $userKey
	 *
	 * @param 	string $keySpace
	 * @param 	string $columnFamily
	 * @param	string $userKey
	 * @param	string $superColumn
	 * @param 	int $consistencyLevel
	 * @return 	boolean
	 */
	public function removeSuperKey($keySpace, $columnFamily, $userKey, $superColumn, $consistencyLevel){
		try {
			if($keySpace==null){
				$keySpace = $this->_keySpace;
			}
			$timestamp = time();
			$columnPath = new cassandra_ColumnPath();
			$columnPath->column_family = $columnFamily;
			$columnPath->super_column = $superColumn;
			return $this->_client->remove($keySpace, $userKey, $columnPath, $timestamp, $consistencyLevel);
		}
		catch(cassandra_InvalidRequestException $e){
			throw new DbException($e->why.' (cassandra_InvalidRequestException)', 0);
		}
	}

	/**
	 * Convierte un Array de PHP a un Array de cassandra_Column
	 *
	 * @param 	array $value
	 * @param 	int $timestamp
	 * @return 	array
	 */
	private function _getArrayAsColumns($valuesArray, $timestamp){
		$node = array();
		foreach($valuesArray as $name => $value){
			$column = new cassandra_Column();
			$column->name = $name;
			$column->value = $value;
			$column->timestamp = $timestamp;
			$node[] = $column;
		}
		return $node;
	}

}