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
 * MongoDB Database Support
 *
 * Estas funciones le permiten acceder a servidores de bases de datos MongoDB.
 * Puede encontrar más información sobre MongoDB en http://www.mongodb.org/display/DOCS/Home
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	Adapters
 * @subpackage 	NoSQL
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @link		http://www.php.net/manual/en/book.mongo.php
 * @access		Public
 */
class DbMongo extends DbBase
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
	 * Objeto Conexión a MongoDB
	 *
	 * @var Mongo
	 */
	protected $_mongo;

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
	 * Hace una conexión a la base de datos de MongoDB
	 *
	 * @param stdClass $descriptor
	 * @return resource
	 */
	public function connect($descriptor=''){
		if($descriptor==''){
			$descriptor = $this->_descriptor;
		}
		if($this->_idConnection!==null){
			return $this->_idConnection;
		}
		$host = isset($descriptor->host) ? $descriptor->host : 'localhost';
		if(isset($descriptor->port)){
			$dbstring = $host.':'.$descriptor->port;
		} else {
			$dbstring = $host;
		}
		if(!isset($descriptor->name)){
			throw new DbException('No se ha indicado el nombre de la base de datos a usar');
		}
		$this->_mongo = new Mongo($dbstring);
		$this->_idConnection = $this->_mongo->selectDB($descriptor->name);
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
	 * Cierra la Conexion al Motor de Base de datos
	 *
	 * @access public
	 * @return boolean
	 */
	public function close(){
		$this->_mongo->close();
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
	 * Devuelve el numero de filas de un select
	 *
	 * @access public
	 * @param boolean $resultQuery
	 */
	public function numRows($resultQuery=''){
		return false;
	}

	/**
	 * Devuelve el nombre de un campo en el resultado de un select
	 *
	 * @param integer $number
	 * @param resource $resultQuery
	 * @return string
	 */
	public function fieldName($number, $resultQuery=''){
		return false;
	}

	/**
	 * Se Mueve al resultado indicado por $number en un select
	 *
	 * @param integer $number
	 * @param resource $resultQuery
	 * @return boolean
	 */
	public function dataSeek($number, $resultQuery=null){
		return false;
	}

	/**
	 * Numero de Filas afectadas en un insert, update o delete
	 *
	 * @param resource $resultQuery
	 * @return integer
	 */
	public function affectedRows($resultQuery=''){
		return false;
	}

	/**
	 * Devuelve el error de MongoDB
	 *
	 * @param	string $errorString
	 * @return	string
	 */
	public function error($errorString='', $resultQuery=null){

	}

	/**
	 * Devuelve el no error de MongoDB
	 *
	 * @return integer|boolean
	 */
	public function noError($resultQuery=null){

	}

	/**
	 * Devuelve el ultimo id autonumerico generado en la BD
	 *
	 * @access public
	 * @param string $table
	 * @param array $primaryKey
	 * @return integer
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
	 * @access public
	 * @param string $sqlQuery
	 * @param integer $number
	 * @return string
	 */
	public function limit($sqlQuery, $number){

	}

	/**
	 * Devuelve un FOR UPDATE valido para un SELECT del RBDM
	 *
	 * @param string $sqlQuery
	 * @return string
	 */
	public function forUpdate($sqlQuery){

	}

	/**
	 * Devuelve un SHARED LOCK valido para un SELECT del RBDM
	 *
	 * @param string $sqlQuery
	 * @return string
	 */
	public function sharedLock($sqlQuery){

	}

	/**
	 * Borra una colección de la base de datos
	 *
	 * @access public
	 * @param string $table
	 * @param boolean $ifExists
	 * @return boolean
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
	 * @param string $table
	 * @param string $schema
	 * @return array
	 */
	public function describeView($table, $schema=''){

	}

	/**
	 * Devuelve una fecha formateada de acuerdo al RBDM
	 *
	 * @param string $date
	 * @param string $format
	 * @return string
	 */
	public function getDateUsingFormat($date, $format='YYYY-MM-DD'){
		return "'$date'";
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
	 * Destructor de DbMongoDB
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
		return 'mongo';
	}

	/**
	 * Devuelve el SQL Dialect usado
	 *
	 * @return	string
	 * @static
	 */
	public static function getSQLDialect(){
		return null;
	}

	/**
	 * Hacer una consulta sobre una colección. Este método sobre escribe al de la clase
	 * base que solo funciona con bases de datos SQL
	 *
	 * @param string $table
	 * @param string $where
	 * @param array $fields
	 * @param array $orderBy
	 */
	public function find($table, $where='', $fields='*', $orderBy='1'){
		$collection = $this->_idConnection->selectCollection($table);
		if($where==''){
			return $collection->find();
		} else {
			return $collection->find($where);
		}
	}

	/**
	 * Realiza una inserción. Este método sobre escribe al de la clase
	 * base que solo funciona con bases de datos SQL
	 *
	 * @access	public
	 * @param	string $table
	 * @param	array $values
	 * @param	array $fields
	 * @param	boolean $automaticQuotes
	 * @return	boolean
	 */
	public function insert($table, $values, $fields=null, $automaticQuotes=false){
		if($this->isReadOnly()==true){
			throw new DbException("No se puede efectuar la operación. La transacción es de solo lectura");
		}
		$collection = $this->_idConnection->selectCollection($table);
		return $collection->insert($values);
	}

	/**
	 * Actualiza registros en una colección
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
		if($this->isReadOnly()==true){
			throw new DbException("No se puede efectuar la operación. La transacción es de solo lectura", 0, true, $this);
		}
		$collection = $this->_idConnection->selectCollection($table);
		if($whereCondition!==null){
			return $collection->update($whereCondition, array('$set' => $values), array('multiple' => true));
		} else {
			return $collection->update(array(), array('$set' => $values), array('multiple' => true));
		}
	}

}