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
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Mysql.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * MySQL Database Support
 *
 * Estas funciones le permiten acceder a servidores de bases de datos MySQL.
 * Puede encontrar mas informacion sobre MySQL en http://www.mysql.com/.
 * La documentacion de MySQL puede encontrarse en http://dev.mysql.com/doc/.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @link		http://www.php.net/manual/es/ref.mysql.php
 * @access		Public
 */
class DbMySQL extends DbBase
#if[compile-time]
	implements DbBaseInterface
#endif
	{

	/**
	 * SELECT statements are performed in a non-locking fashion
	 *
	 */
	const ISOLATION_READ_UNCOMMITED = 1;

	/**
	 * Somewhat Oracle-like isolation level with respect to consistent (non-locking) reads
	 *
	 */
	const ISOLATION_READ_COMMITED = 2;

	/**
	 * This is the default isolation level for InnoDB
	 *
	 */
	const ISOLATION_REPEATABLE_READ = 3;

	/**
	 * This level is like REPEATABLE READ, but InnoDB implicitly converts
	 * all plain SELECT  statements to SELECT ... LOCK IN SHARE MODE  if autocommit is disabled
	 *
	 */
	const ISOLATION_SERIALIZABLE = 4;

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
	 * Hace una conexión a la base de datos de MySQL
	 *
	 * @param	stdClass $descriptor
	 * @param 	boolean	$persistent
	 * @return	resource
	 */
	public function connect($descriptor='', $persistent=false){
		if($descriptor==''){
			$descriptor = $this->_descriptor;
		}
		$host = isset($descriptor->host) ? $descriptor->host : '';
		$username = isset($descriptor->username) ? $descriptor->username : '';
		$password = isset($descriptor->password) ? $descriptor->password : '';
		if(isset($descriptor->port)){
			$dbstring = $host.':'.$descriptor->port;
		} else {
			$dbstring = $host;
		}
		$clientFlags = null;
		if(isset($descriptor->compression)){
			if($descriptor->compression==true){
				if($clientFlags==null){
					$clientFlags = MYSQL_CLIENT_COMPRESS;
				} else {
					$clientFlags |= MYSQL_CLIENT_COMPRESS;
				}
			}
		}
		if(isset($descriptor->ssl)){
			if($descriptor->ssl==true){
				if($clientFlags==null){
					$clientFlags = MYSQL_CLIENT_SSL;
				} else {
					$clientFlags |= MYSQL_CLIENT_SSL;
				}
			}
		}
		if(isset($descriptor->interactive)){
			if($descriptor->interactive==true){
				if($clientFlags==null){
					$clientFlags = MYSQL_CLIENT_INTERACTIVE;
				} else {
					$clientFlags |= MYSQL_CLIENT_INTERACTIVE;
				}
			}
		}
		if($persistent==false){
			$this->_idConnection = mysql_connect($dbstring, $username, $password, true, $clientFlags);
		} else {
			$this->_idConnection = mysql_pconnect($dbstring, $username, $password, $clientFlags);
		}
		if($this->_idConnection){
			$dbname = isset($descriptor->name) ? $descriptor->name : "";
			if($dbname!==''){
				if(mysql_select_db($dbname, $this->_idConnection)==false){
					throw new DbException($this->error(), $this->noError(), false);
				}
			}
			$autocommit = isset($descriptor->autocommit) ? $descriptor->autocommit : false;
			$this->_autoCommit = $autocommit;
			$this->_fetchMode = MYSQL_BOTH;
			parent::__construct($descriptor);
			parent::connect();
			if(isset($descriptor->charset)){
				$this->query('SET NAMES '.$descriptor->charset);
			}
			if(isset($descriptor->collation)){
				$this->query('SET collation_connection='.$descriptor->collation);
				$this->query('SET collation_database='.$descriptor->collation);
			}
			return true;
		} else {
			throw new DbException($this->error(), $this->noError(), false);
		}
	}

	/**
	 * Efectúa operaciones SQL sobre la base de datos
	 *
	 * @param	string $sqlStatement
	 * @return	resource|false
	 */
	public function query($sqlStatement){
		parent::beforeQuery($sqlStatement);
		if(!$this->_idConnection){
			$this->connect();
			if(!$this->_idConnection){
				return false;
			}
		}
		$this->_lastQuery = $sqlStatement;
		if($resultQuery = mysql_query($sqlStatement, $this->_idConnection)){
			$this->_lastResultQuery = $resultQuery;
			parent::afterQuery($sqlStatement);
			return $resultQuery;
		} else {
			$this->_lastResultQuery = false;
			$errorMessage = $this->error(' al ejecutar "'.$sqlStatement.'" en la conexión "'.$this->getConnectionId(true).'"');
			$numberError = $this->noError();
			if($numberError==1205||$numberError==1213){
				throw new DbLockAdquisitionException($errorMessage, $numberError, true, $this);
			}
			if($numberError==1064||$numberError==1054){
				throw new DbSQLGrammarException($errorMessage, $numberError, true, $this);
			}
			if($numberError==1451||$numberError==1062){
				throw new DbConstraintViolationException($errorMessage, $numberError, true, $this);
			}
			if($numberError==1292){
				throw new DbInvalidFormatException($errorMessage, $numberError, true, $this);
			}
			if($numberError==2006){
				$this->connect();
			}
			throw new DbException($errorMessage, $this->noError(), true, $this);
		}
	}

	/**
	 * Cierra la conexión al motor de base de datos
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function close(){
		if($this->_idConnection){
			parent::close();
			$success = @mysql_close($this->_idConnection);
			$this->_idConnection = null;
			return $success;
		} else {
			return true;
		}
	}

	/**
	 * Devuelve registro por registro el contenido de una consulta
	 *
	 * @param	resource $resultQuery
	 * @return	array
	 */
	public function fetchArray($resultQuery=''){
		if(!$this->_idConnection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->_lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		return mysql_fetch_array($resultQuery, $this->_fetchMode);
	}

	/**
	 * Devuelve el número de filas de un SELECT
	 *
	 * @access	public
	 * @param	boolean $resultQuery
	 */
	public function numRows($resultQuery=''){
		if(!$this->_idConnection){
			return false;
		}
		if($resultQuery===''){
			$resultQuery = $this->_lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($numberRows = @mysql_num_rows($resultQuery))!==false){
			return $numberRows;
		} else {
			throw new DbException($this->error($php_errormsg), $this->noError(), true, $this);
			return false;
		}
		return false;
	}

	/**
	 * Devuelve el nombre de un campo en el resultado de un SELECT
	 *
	 * @param	integer $number
	 * @param	resource $resultQuery
	 * @return	string
	 */
	public function fieldName($number, $resultQuery=''){
		if(!$this->_idConnection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->_lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($fieldName = mysql_field_name($resultQuery, $number))!==false){
			return $fieldName;
		} else {
			throw new DbException($this->error(), $this->noError());
			return false;
		}
		return false;
	}

	/**
	 * Se mueve al resultado indicado por $number en un SELECT
	 *
	 * @param	integer $number
	 * @param	resource $resultQuery
	 * @return	boolean
	 */
	public function dataSeek($number, $resultQuery=null){
		if(!$resultQuery){
			$resultQuery = $this->_lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($success = @mysql_data_seek($resultQuery, $number))!==false){
			return $success;
		} else {
			throw new DbException($this->error($php_errormsg), $this->noError());
			return false;
		}
		return false;
	}

	/**
	 * Número de filas afectadas en un INSERT, UPDATE ó DELETE
	 *
	 * @param	resource $resultQuery
	 * @return	integer
	 */
	public function affectedRows($resultQuery=''){
		if(($numberRows = @mysql_affected_rows($this->_idConnection))!==false){
			return $numberRows;
		} else {
			$this->_lastError = $this->error($php_errormsg);
			throw new DbException($this->error($php_errormsg), $this->noError());
			return false;
		}
		return false;
	}

	/**
	 * Devuelve el error de MySQL
	 *
	 * @param	string $errorString
	 * @return	string
	 */
	public function error($errorString='', $resultQuery=null){
		if(!$this->_idConnection){
			$errorMessage = mysql_error();
			if($errorMessage){
				$this->_lastError = '"'.$errorMessage.'" '.$errorString;
			} else {
				$this->_lastError = "[Error Desconocido en MySQL: $errorString]";
			}
			$this->log($this->_lastError, Logger::ERROR);
			return $this->_lastError;
		}
		$errorMessage = mysql_error($this->_idConnection);
		if($errorMessage!=""){
			$this->_lastError = "\"".$errorMessage."\" ".$errorString;
		} else {
			$this->_lastError = "[Error Desconocido en MySQL: $errorString]";
		}
		$this->log($this->_lastError, Logger::ERROR);
		return $this->_lastError;
	}

	/**
	 * Devuelve el no error de MySQL
	 *
	 * @return integer|boolean
	 */
	public function noError($resultQuery=null){
		if(!$this->_idConnection){
			return false;
		}
		return mysql_errno($this->_idConnection);
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
		if(!$this->_idConnection){
			return false;
		}
		return mysql_insert_id($this->_idConnection);
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
		$tableName = addslashes($tableName);
		$sql = MysqlSQLDialect::tableExists($tableName, $schemaName);
		$fetchMode = $this->_fetchMode;
		$this->_fetchMode = MYSQL_NUM;
		$num = $this->fetchOne($sql);
		$this->_fetchMode = $fetchMode;
		return (bool) $num[0];
	}

	/**
	 * Verifica si una vista existe ó no
	 *
	 * @param string $viewName
	 * @param string $schemaName
	 */
	public function viewExists($viewName, $schemaName=''){
		return $this->tableExists($viewName, $schemaName);
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
		try {
			if($schemaName!=''){
				$this->query("DESC `$schemaName`.`$tableName`");
			} else {
				$this->query("DESC $tableName");
			}
			return true;
		}
		catch(DbException $e){
			if($e->getCode()==1146){
				return false;
			} else {
				throw $e;
			}
		}
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
		if(is_numeric($number)){
			$number = (int) $number;
			return $sqlQuery.' LIMIT '.$number;
		} else {
			return $sqlQuery;
		}
	}

	/**
	 * Devuelve un FOR UPDATE valido para un SELECT del RBDM
	 *
	 * @param	string $sqlQuery
	 * @return	string
	 */
	public function forUpdate($sqlQuery){
		return $sqlQuery.' FOR UPDATE';
	}

	/**
	 * Devuelve un SHARED LOCK valido para un SELECT del RBDM
	 *
	 * @param	string $sqlQuery
	 * @return	string
	 */
	public function sharedLock($sqlQuery){
		return $sqlQuery.' LOCK IN SHARE MODE';
	}

	/**
	 * Borra una tabla de la base de datos
	 *
	 * @access	public
	 * @param	string $table
	 * @param	boolean $ifExists
	 * @return	boolean
	 */
	public function dropTable($table, $ifExists=true){
		if($ifExists==true){
			return $this->query("DROP TABLE IF EXISTS $table");
		} else {
			return $this->query("DROP TABLE $table");
		}
	}

	/**
	 * Crea una tabla utilizando SQL nativo del RDBM
	 *
	 * @access	public
	 * @param	string $table
	 * @param	string $schema
	 * @param	array $definition
	 * @return	boolean
	 */
	public function createTable($table, $schema, $definition){
		if(is_array($definition)==false){
			throw new DbException("Definición inválida para crear la tabla '$table'", 0);
		}
		if(!isset($definition['columns'])){
			throw new DbException("La tabla debe contener al menos una columna", 0);
		}
		if(count($definition['columns'])==0){
			throw new DbException("La tabla debe contener al menos una columna", 0);
		}
		$sql = MysqlSQLDialect::createTable($table, $schema, $definition);
		return $this->query($sql);
	}

	/**
	 * Agrega una columna a una tabla según la definición de columna
	 *
	 * @param	string $tableName
	 * @param 	string $schemaName
	 * @param	DbColumn $column
	 * @return	boolean
	 */
	public function addColumn($tableName, $schemaName, DbColumn $column){
		$sql = MysqlSQLDialect::addColumn($tableName, $schemaName, $column);
		return $this->query($sql);
	}

	/**
	 * Modifica una columna a una tabla según la definición de columna
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	DbColumn $column
	 * @return 	boolean
	 */
	public function modifyColumn($tableName, $schemaName, DbColumn $column){
		$sql = MysqlSQLDialect::modifyColumn($tableName, $schemaName, $column);
		return $this->query($sql);
	}

	/**
	 * Elimina una columna de una tabla
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	string $column
	 * @return 	boolean
	 */
	public function dropColumn($tableName, $schemaName, $columnName){
		$sql = MysqlSQLDialect::dropColumn($tableName, $schemaName, $columnName);
		return $this->query($sql);
	}

	/**
	 * Agrega un indice a una tabla
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	DbIndex $index
	 * @return 	boolean
	 */
	public function addIndex($tableName, $schemaName, DbIndex $index){
		$sql = MysqlSQLDialect::addIndex($tableName, $schemaName, $index);
		return $this->query($sql);
	}

	/**
	 * Borra un indice de una tabla
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	string $indexName
	 * @return 	boolean
	 */
	public function dropIndex($tableName, $schemaName, $indexName){
		$sql = MysqlSQLDialect::dropIndex($tableName, $schemaName, $indexName);
		return $this->query($sql);
	}

	/**
	 * Agrega la llave primaria a la tabla
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	DbIndex $index
	 * @return 	boolean
	 */
	public function addPrimaryKey($tableName, $schemaName, DbIndex $index){
		$sql = MysqlSQLDialect::addPrimaryKey($tableName, $schemaName, $index);
		return $this->query($sql);
	}

	/**
	 * Borra la llave primaria de una tabla
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @return 	boolean
	 */
	public function dropPrimaryKey($tableName, $schemaName){
		$sql = MysqlSQLDialect::dropPrimaryKey($tableName, $schemaName);
		return $this->query($sql);
	}

	/**
	 * Agrega la llave primaria a la tabla
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	DbReference $reference
	 * @return	boolean true
	 */
	public function addForeignKey($tableName, $schemaName, DbReference $reference){
		$sql = MysqlSQLDialect::addForeignKey($tableName, $schemaName, $reference);
		return $this->query($sql);
	}

	/**
	 * Borra un indice de una tabla
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	string $referenceName
	 * @return	boolean true
	 */
	public function dropForeignKey($tableName, $schemaName, $referenceName){
		$sql = MysqlSQLDialect::dropForeignKey($tableName, $schemaName, $referenceName);
		return $this->query($sql);
	}

	/**
	 * Obtiene la definición del tipo de dato de una columna según MySQL
	 *
	 * @param	DbColumn $column
	 * @return	string
	 */
	public function getColumnDefinition(DbColumn $column){
		return MysqlSQLDialect::getColumnDefinition($column);;
	}

	/**
	 * Listar las tablas en la base de datos
	 *
	 * @param	string $schemaName
	 * @return	array
	 */
	public function listTables($schemaName=''){
		$sql = MysqlSQLDialect::listTables($schemaName);
		$fetchMode = $this->_fetchMode;
		$this->_fetchMode = self::DB_NUM;
		$tables = $this->fetchAll($sql);
		$allTables = array();
		foreach($tables as $table){
			$allTables[] = $table[0];
		}
		$this->_fetchMode = $fetchMode;
		return $allTables;
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @param	string $table
	 * @param	string $schema
	 * @return	array
	 */
	public function describeTable($table, $schema=''){
		$sql = MysqlSQLDialect::describeTable($table, $schema);
		$this->_fetchMode = MYSQL_ASSOC;
		$describe = $this->fetchAll($sql);
		$this->_fetchMode = MYSQL_BOTH;
		return $describe;
	}

	/**
	 * Listar los indices de una tabla
	 *
	 * @param	string $table
	 * @param	string $schema
	 * @return	array
	 */
	public function describeIndexes($table, $schema=''){
		$sql = MysqlSQLDialect::describeIndexes($table, $schema);
		$this->_fetchMode = MYSQL_ASSOC;
		$describe = $this->fetchAll($sql);
		$indexes = array();
		foreach($describe as $index){
			if(!isset($indexes[$index['Key_name']])){
				$indexes[$index['Key_name']] = array();
			}
			$indexes[$index['Key_name']][] = $index['Column_name'];
		}
		$this->_fetchMode = MYSQL_BOTH;
		return $indexes;
	}

	/**
	 * Listar los indices de una tabla
	 *
	 * @param	string $table
	 * @param	string $schema
	 * @return	array
	 */
	public function describeReferences($table, $schema=''){
		$sql = MysqlSQLDialect::describeReferences($table, $schema);
		$this->_fetchMode = MYSQL_ASSOC;
		$references = array();
		$describe = $this->fetchAll($sql);
		foreach($describe as $reference){
			if(!isset($references[$reference['CONSTRAINT_NAME']])){
				$references[$reference['CONSTRAINT_NAME']] = array(
					'referencedSchema' => $reference['REFERENCED_TABLE_SCHEMA'],
					'referencedTable' => $reference['REFERENCED_TABLE_NAME'],
					'columns' => array(),
					'referencedColumns' => array()
				);
			}
			$references[$reference['CONSTRAINT_NAME']]['columns'][] = $reference['COLUMN_NAME'];
			$references[$reference['CONSTRAINT_NAME']]['referencedColumns'][] = $reference['REFERENCED_COLUMN_NAME'];
		}
		$this->_fetchMode = MYSQL_BOTH;
		return $references;
	}

	/**
	 * Listar los campos de una vista
	 *
	 * @param string $table
	 * @param string $schema
	 * @return array
	 */
	public function describeView($table, $schema=''){
		return $this->describeTable($table, $schema);
	}

	/**
	 * Ver las opciones de creación de una tabla
	 *
	 * @param	string $table
	 * @param	string $schema
	 * @return	array
	 */
	public function tableOptions($table, $schema=''){
		$sql = MysqlSQLDialect::tableOptions($table, $schema);
		$this->_fetchMode = MYSQL_ASSOC;
		$references = array();
		$describe = $this->fetchAll($sql);
		return $describe[0];
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
		return new DbRawValue('now()');
	}

	/**
	 * Permite establecer el nivel de isolacion de la conexion
	 *
	 * @param int $isolationLevel
	 */
	public function setIsolationLevel($isolationLevel){
		switch($isolationLevel){
			case 1:
				$isolationCommand = 'SET SESSION TRANSACTION READ UNCOMMITED';
				break;
			case 2:
				$isolationCommand = 'SET SESSION TRANSACTION READ COMMITED';
				break;
			case 3:
				$isolationCommand = 'SET SESSION TRANSACTION REPETEABLE READ';
				break;
			case 4:
				$isolationCommand = 'SET SESSION TRANSACTION SERIALIZABLE';
				break;
		}
		$this->query($isolationCommand);
		return true;
	}

	/**
	 * Establece el modo en se que deben devolver los registros
	 *
	 * @param int $fetchMode
	 */
	public function setFetchMode($fetchMode){
		if($fetchMode==self::DB_ASSOC){
			$this->_fetchMode = MYSQL_ASSOC;
			return;
		}
		if($fetchMode==self::DB_BOTH){
			$this->_fetchMode = MYSQL_BOTH;
			return;
		}
		if($fetchMode==self::DB_NUM){
			$this->_fetchMode = MYSQL_NUM;
			return;
		}
	}

	/**
	 * Obtiene la información del servidor DB2
	 *
	 */
	public function getServerInfo(){
		return mysql_get_client_info();
	}

	/**
	 * Destructor de DbMysql
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
		return array('mysql', 'mysqlnd');
	}

	/**
	 * Devuelve el SQL Dialect que debe ser usado
	 *
	 * @return	string
	 * @static
	 */
	public static function getSQLDialect(){
		return 'Mysql';
	}

}
