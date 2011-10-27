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
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * Firebird/Interbase Database Support
 *
 * Estas funciones le permiten acceder a servidores de bases de datos Firebird/Interbase.
 * Puede encontrar mas informacion sobre Firebird/Interbase en http://www.firebirdsql.org/.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @link		http://www.php.net/manual/en/book.ibase.php
 * @access		Public
 */
class DbFirebird extends DbBase
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
	 * Tipo de Dato Integer
	 *
	 */
	const TYPE_INTEGER = 'INTEGER';

	/**
	 * Tipo de Dato Date
	 *
	 */
	const TYPE_DATE = 'DATE';

	/**
	 * Tipo de Dato Varchar
	 *
	 */
	const TYPE_VARCHAR = 'VARCHAR';

	/**
	 * Tipo de Dato Decimal
	 *
	 */
	const TYPE_DECIMAL = 'DECIMAL';

	/**
	 * Tipo de Dato Datetime
	 *
	 */
	const TYPE_DATETIME = 'DATETIME';

	/**
	 * Tipo de Dato Char
	 *
	 */
	const TYPE_CHAR = 'CHAR';

	/**
	 * Tipo de Text
	 *
	 */
	const TYPE_TEXT = 'TEXT';

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
	 * Hace una conexión a la base de datos de Firebird/Intebase
	 *
	 * @param stdClass $descriptor
	 * @return resource
	 */
	public function connect($descriptor=''){
		if($descriptor==''){
			$descriptor = $this->_descriptor;
		}
		$host = isset($descriptor->host) ? $descriptor->host : '';
		$username = isset($descriptor->username) ? $descriptor->username : '';
		$password = isset($descriptor->password) ? $descriptor->password : '';
		if(isset($descriptor->name)){
			$dbstring = $host.':'.$descriptor->name;
		} else {
			$dbstring = $host;
		}
		$clientFlags = null;
		if($this->_idConnection = @ibase_connect($dbstring, $username, $password)){
			$autocommit = isset($descriptor->autocommit) ? $descriptor->autocommit : false;
			$this->_autoCommit = $autocommit;
			$this->_fetchMode = self::DB_BOTH;
			parent::__construct($descriptor);
			parent::connect();
			return true;
		} else {
			throw new DbException($this->error($php_errormsg), $this->noError(), false);
			return false;
		}
	}

	/**
	 * Efectua operaciones SQL sobre la base de datos
	 *
	 * @param string $sqlStatement
	 * @return resource|false
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
		if($resultQuery = @ibase_query($this->_idConnection, $sqlStatement)){
			$this->_lastResultQuery = $resultQuery;
			parent::afterQuery($sqlStatement);
			return $resultQuery;
		} else {
			$this->_lastResultQuery = false;
			$errorMessage = $this->error(' al ejecutar "'.$sqlStatement.'" en la conexión "'.$this->getConnectionId(true).'"');
			$numberError = $this->noError();
			if($numberError==-615){
				throw new DbLockAdquisitionException($errorMessage, $numberError, true, $this);
			}
			if($numberError==-104){
				throw new DbSQLGrammarException($errorMessage, $numberError, true, $this);
			}
			if($numberError==-803){
				throw new DbConstraintViolationException($errorMessage, $numberError, true, $this);
			}
			if($numberError==-901){
				throw new DbInvalidFormatException($errorMessage, $numberError, true, $this);
			}
			throw new DbException($errorMessage, $this->noError(), true, $this);
			return false;
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
			$success = @ibase_close($this->_idConnection);
			$this->_idConnection = null;
			return $success;
		} else {
			return true;
		}
	}

	/**
	 * Devuelve fila por fila el contenido de un SELECT
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
		if($this->_fetchMode==self::DB_BOTH){
			$row = ibase_fetch_object($resultQuery);
			if($row){
				$n = 0;
				$returnedRow = array();
				foreach($row as $key => $value){
					$returnedRow[$n] = $value;
					$returnedRow[$key] = $value;
					++$n;
				}
				return array_change_key_case($returnedRow, CASE_LOWER);
			} else {
				return false;
			}
		} else {
			if($this->_fetchMode==self::DB_NUM){
				return ibase_fetch_row($resultQuery);
			} else {
				$row = ibase_fetch_assoc($resultQuery);
				if($row){
					return array_change_key_case($row, CASE_LOWER);
				} else {
					return false;
				}
			}
		}
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
		$sql = $this->_lastQuery;
		if(preg_match('/SELECT [COUNT|MIN|MAX|AVG]/i', $sql)==false){
			$fromPosition = stripos($sql, 'FROM');
			if($fromPosition===false){
				return 0;
			} else {
				$sqlQuery = 'SELECT COUNT(*) '.substr($sql, $fromPosition);
				$resultQuery = @ibase_query($this->_idConnection, $sqlQuery);
				if($resultQuery){
					$rowCount = ibase_fetch_row($resultQuery);
					return $rowCount[0];
				} else {
					return false;
				}
			}
		} else {
			return 1;
		}
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
		if(($fieldName = ibase_field_info($resultQuery, $number))!==false){
			return $fieldName;
		} else {
			throw new DbException($this->error(), $this->noError());
			return false;
		}
		return false;
	}

	/**
	 * Mueve el puntero del cursor a la fila indicada por $number en un SELECT
	 *
	 * @param	integer $number
	 * @param	resource $resultQuery
	 * @return	boolean
	 */
	public function dataSeek($number, $resultQuery=null){
		if(!$resultQuery){
			$resultQuery = &$this->_lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		ibase_free_result($resultQuery);
		$resultQuery = @ibase_query($this->_idConnection, $this->_lastQuery);
		if($resultQuery!==false){
			$n = 0;
			while($n<$number){
				ibase_fetch_row($resultQuery);
				$n++;
			}
			return true;
		} else {
			throw new DbException($this->error($php_errormsg), $this->noError());
			return false;
		}
	}

	/**
	 * Número de Filas afectadas en un INSERT, UPDATE ó DELETE
	 *
	 * @param	resource $resultQuery
	 * @return	integer
	 */
	public function affectedRows($resultQuery=''){
		if(($numberRows = @ibase_affected_rows($this->_idConnection))!==false){
			return $numberRows;
		} else {
			$this->_lastError = $this->error($php_errormsg);
			throw new DbException($this->error($php_errormsg), $this->noError());
			return false;
		}
		return false;
	}

	/**
	 * Devuelve el error de Firebird/Intebase
	 *
	 * @param	string $errorString
	 * @return	string
	 */
	public function error($errorString='', $resultQuery=null){
		$errorMessage = ibase_errmsg();
		if($errorMessage){
			$this->_lastError = '"'.$errorMessage.'" '.$errorString;
		} else {
			$this->_lastError = '[Error Desconocido : '.$errorString.']';
		}
		$this->log($this->_lastError, Logger::ERROR);
		return $this->_lastError;
	}

	/**
	 * Devuelve el no error de Firebird/Intebase
	 *
	 * @return integer|boolean
	 */
	public function noError($resultQuery=null){
		return ibase_errcode();
	}

	/**
	 * Devuelve el último id autonumérico generado en la BD
	 *
	 * @param	string $table
	 * @param	array $identityColumn
	 * @param 	string $sequenceName
	 * @return	integer
	 */
	public function lastInsertId($table='', $identityColumn='', $sequenceName=''){
		if(!$this->_idConnection){
			return false;
		}
		if($table&&$identityColumn){
			if($sequenceName==''){
				$sequenceName = $table.'_'.$identityColumn.'_SEQ';
			}
			$sql = FirebirdSQLDialect::lastInsertId($table, $identityColumn, $sequenceName);
			$value = $this->fetchOne($sql);
			return $value[0];
		}
		return false;
	}

	/**
	 * Indica si el RBDM requiere de secuencias y devuelve el nombre por convencion
	 *
	 * @param	string $tableName
	 * @param	array $primaryKey
	 * @return	boolean
	 */
	public function getRequiredSequence($tableName='', $identityColumn='', $sequenceName=''){
		return FirebirdSQLDialect::getRequiredSequence($tableName, $identityColumn, $sequenceName);
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
		$sql = FirebirdSQLDialect::tableExists($tableName, $schemaName);
		$fetchMode = $this->_fetchMode;
		$this->_fetchMode = self::DB_NUM;
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
	 * Verifica si una tabla temporal existe o no. Firebird/Interbase no soporta tablas temporales
	 *
	 * @access	public
	 * @param	string $table
	 * @param	string $schema
	 * @return	boolean
	 */
	public function temporaryTableExists($tableName, $schemaName=''){
		return false;
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
			return FirebirdSQLDialect::limit($sqlQuery, $number);
		} else {
			return $sqlQuery;
		}
	}

	/**
	 * Devuelve un FOR UPDATE valido para un SELECT del RBDM
	 *
	 * @param string $sqlQuery
	 * @return string
	 */
	public function forUpdate($sqlQuery){
		return $sqlQuery.' FOR UPDATE';
	}

	/**
	 * Devuelve un SHARED LOCK valido para un SELECT del RBDM
	 *
	 * @param string $sqlQuery
	 * @return string
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
			if($this->existsTable($table)){
				$sql = FirebirdSQLDialect::dropTable(table);
				return $this->query($sql);
			} else {
				return true;
			}
		} else {
			$sql = FirebirdSQLDialect::dropTable(table);
			return $this->query($sql);
		}
	}

	/**
	 * Crea una tabla utilizando SQL nativo del RDBM
	 *
	 * @access	public
	 * @param	string $table
	 * @param	array $definition
	 * @param	array $index
	 * @return	boolean
	 */
	public function createTable($table, $definition, $index=array(), $tableOptions=array()){
		if(isset($tableOptions['temporary'])&&$tableOptions['temporary']==true){
			throw new DbException('Firebird/Interbase no soporta tablas temporales', 0);
		}
		$createSQL = 'CREATE TABLE '.$table.' (';
		#if[compile-time]
		if(is_array($definition)==false){
			throw new DbException('Definición invalida para crear la tabla "'.$table.'"');
			return false;
		}
		#end-if
		$createLines = array();
		$index = array();
		$uniqueIndex = array();
		$primary = array();
		$notNull = "";
		$size = "";
		foreach($definition as $field => $fieldDefinition){
			if(isset($fieldDefinition['notNull'])){
				$notNull = $fieldDefinition['notNull'] ? 'NOT NULL' : '';
			} else {
				$notNull = "";
			}
			if(isset($fieldDefinition['size'])){
				$size = $fieldDefinition['size'] ? '('.$fieldDefinition['size'].')' : '';
			} else {
				$size = "";
			}
			if(isset($fieldDefinition['index'])){
				if($fieldDefinition['index']){
					$index[] = "INDEX(`$field`)";
				}
			}
			if(isset($fieldDefinition['unique_index'])){
				if($fieldDefinition['unique_index']){
					$index[] = "UNIQUE(`$field`)";
				}
			}
			if(isset($fieldDefinition['primary'])){
				if($fieldDefinition['primary']){
					$primary[] = "`$field`";
				}
			}
			if(isset($fieldDefinition['auto'])){
				if($fieldDefinition['auto']){
					$fieldDefinition['extra'] = isset($fieldDefinition['extra']) ? $fieldDefinition['extra']." AUTO_INCREMENT" :  "AUTO_INCREMENT";
				}
			}
			if(isset($fieldDefinition['extra'])){
				$extra = $fieldDefinition['extra'];
			} else {
				$extra = "";
			}
			$createLines[] = "`$field` ".$fieldDefinition['type'].$size.' '.$notNull.' '.$extra;
		}
		$createSQL.= join(',', $createLines);
		$lastLines = array();
		if(count($primary)){
			$lastLines[] = 'PRIMARY KEY('.join(",", $primary).')';
		}
		if(count($index)){
			$lastLines[] = join(',', $index);
		}
		if(count($uniqueIndex)){
			$lastLines[] = join(',', $uniqueIndex);
		}
		if(count($lastLines)){
			$createSQL.= ','.join(',', $lastLines).')';
		}else{
            $createSQL.= ')';
        }
		return $this->query($createSQL);
	}


	/**
	 * Listar las tablas en la base de datos
	 *
	 * @param	string $schemaName
	 * @return	array
	 */
	public function listTables($schemaName=''){
		$sql = FirebirdSQLDialect::listTables($schemaName);
		$fetchMode = $this->_fetchMode;
		$this->_fetchMode = self::DB_NUM;
		$tables = $this->fetchAll($sql);
		$allTables = array();
		foreach($tables as $table){
			$allTables[] = strtolower($table[0]);
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

		$fetchMode = $this->_fetchMode;
		$this->_fetchMode = self::DB_ASSOC;
		$sql = FirebirdSQLDialect::getPrimaryKey($table, $schema);
		$primaryFields = $this->fetchAll($sql);
		$primaryKeys = array();
		foreach($primaryFields as $primaryField){
			$primaryKeys[rtrim($primaryField['name'])] = true;
		}

		$sql = FirebirdSQLDialect::describeTable($table, $schema);
		$describe = $this->fetchAll($sql);
		$finalDescribe = array();
		foreach($describe as $field){
			$fieldDescribe = array(
				'Field' => strtolower(rtrim($field['name'])),
				'Null' => $field['not_null'] == 1 ? 'YES' : 'NO'
			);
			switch($field['type']){
				case 7:
				case 8:
					$fieldDescribe['type'] = self::TYPE_INTEGER;
					break;
				case 37:
					$fieldDescribe['type'] = self::TYPE_VARCHAR.'('.$field['field_length'].')';
					break;
				case 40:
				case 14:
					$fieldDescribe['type'] = self::TYPE_CHAR.'('.$field['field_length'].')';
					break;
				case 2:
					$fieldDescribe['type'] = self::TYPE_INTEGER.'('.$field['field_length'].')';
					break;
				case 5:
					$fieldDescribe['type'] = self::TYPE_DECIMAL.'('.$field['length'].')';
					break;
				case 12:
					$fieldDescribe['Type'] = self::TYPE_DATE;
					break;
				case 35:
					$fieldDescribe['Type'] = self::TYPE_DATETIME;
					break;
				case 261:
					$fieldDescribe['Type'] = 'blob';
					break;
			}
			if(isset($primaryKeys[$field['name']])){
				$fieldDescribe['Primary'] = 'YES';
			} else {
				$fieldDescribe['Primary'] = 'NO';
			}
			$finalDescribe[] = $fieldDescribe;
		}
		$this->_fetchMode = $fetchMode;
		return $finalDescribe;
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
	 * Devuelve una fecha formateada de acuerdo al RBDM
	 *
	 * @param string $date
	 * @param string $format
	 * @return string
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
		return new DbRawValue('CURRENT_TIMESTAMP');
	}

	/**
	 * Permite establecer el nivel de isolación de la conexión
	 *
	 * @param int $isolationLevel
	 */
	public function setIsolationLevel($isolationLevel){
		switch($isolationLevel){
			case 1:
				$isolationCommand = 'SET TRANSACTION READ UNCOMMITED';
				break;
			case 2:
				$isolationCommand = 'SET TRANSACTION READ COMMITTED';
				break;
			case 3:
				$isolationCommand = 'SET TRANSACTION SNAPSHOT';
				break;
			case 4:
				$isolationCommand = 'SET TRANSACTION SNAPSHOT TABLE STABILITY';
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
		$this->_fetchMode = $fetchMode;
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
		return array('interbase');
	}

	/**
	 * Devuelve el SQL Dialect que debe ser usado
	 *
	 * @return	string
	 * @static
	 */
	public static function getSQLDialect(){
		return 'FirebirdSQLDialect';
	}

}
