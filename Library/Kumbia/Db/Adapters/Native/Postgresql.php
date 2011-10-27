<?php

/**
 * Kumbia Entreprise Framework
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
 * @package	Db
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2007 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (C) 2007-2007 Emilio Silveira (emilio.rst@gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Postgresql.php 90 2009-09-21 01:29:23Z gutierrezandresfelipe@gmail.com $
 */

/**
 * PostgreSQL Database Support (alpha)
 *
 * La base de datos PostgreSQL es un producto Open Source y disponible sin costo.
 * Postgres, desarrollado originalmente en el Departamento de Ciencias de
 * Computación de UC Berkeley, fue pionero en muchos de los conceptos de
 * objetos y relacionales que ahora est&aacute;n apareciendo en algunas bases de
 * datos comerciales. Provee soporte para lenguajes SQL92/SQL99, transacciones,
 * integridad referencial, procedimientos almacenados y extensibilidad de tipos.
 * PostgreSQL es un descendiente de código abierto de su código original de Berkeley.
 *
 * Estas funciones le permiten acceder a servidores de bases de datos PostgreSQL.
 * Puede encontrar m&aacute;s información sobre PostgreSQL en http://www.postgresql.org.
 * La documentación de PostgreSQL puede encontrarse en http://www.postgresql.org/docs.
 *
 * @category	Kumbia
 * @package	Db
 * @subpackage	Adapters
 * @copyright 	Copyright (c) 2005-2007 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (C) 2007-2007 Emilio Silveira (emilio.rst@gmail.com)
 * @license	New BSD License
 * @link	http://www.php.net/manual/es/ref.pgsql.php
 * @access	Public
 */
class DbPostgreSQL extends DbBase implements DbBaseInterface {

	/**
	 * ISOLATION READ UNCOMMITED
	 *
	 */
	const ISOLATION_READ_UNCOMMITED = 1;

	/**
	 * ISOLATION READ COMMITED
	 *
	 */
	const ISOLATION_READ_COMMITED = 2;

	/**
	 * This is the default isolation level for InnoDB
	 *
	 */
	const ISOLATION_REPEATABLE_READ = 3;

	/**
	 * ISOLATION SERIALIZABLE	 
	 */
	const ISOLATION_SERIALIZABLE = 4;

	/**
	 * Tipo de Dato Integer
	 *
	 */
	const TYPE_INTEGER = 'INT';

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
	const TYPE_DATETIME = 'TIMESTAMP';

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
	 * Hace una conexión a la base de datos de PostgreSQL
	 *
	 * @param	stdClass $descriptor
	 * @return	resource
	 */
	public function connect($descriptor=''){
		if($descriptor==''){
			$descriptor = $this->_descriptor;
		}
		$connectionString = "";
		if(isset($descriptor->hostname)){
			$connectionString.='host='.$descriptor->hostname.' ';
		}
		if(isset($descriptor->username)){
			$connectionString.='user='.$descriptor->username.' ';
		}
		if(isset($descriptor->password)){
			$connectionString.='password='.$descriptor->password.' ';
		}	
		if(isset($descriptor->port)){
			$connectionString.='port='.$descriptor->port.' ';
		}
		if(isset($descriptor->name)){
			$connectionString.='dbname='.$descriptor->name.' ';
		}
		if($this->_idConnection = @pg_connect($connectionString)){			
			$this->_fetchMode = PGSQL_BOTH;
			parent::__construct($descriptor);
			return true;
		} else {
			throw new DbException($this->error($php_errormsg), $this->noError(), false);
			return false;
		}
	}

	/**
	 * Efectua operaciones SQL sobre la base de datos
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
		if($resultQuery = pg_query($this->_idConnection, $sqlStatement)){
			$this->_lastResultQuery = $resultQuery;
			parent::afterQuery($sqlStatement);
			return $resultQuery;
		} else {
			$this->_lastResultQuery = false;
			$errorMessage = $this->error(" al ejecutar <i>\"$sqlStatement\"</i> en la conexión \"".$this->getConnectionId(true)."\"");
			/*if($this->noError()==1205||$this->noError()==1213){
				throw new DbLockAdquisitionException($errorMessage, $this->noError(), true, $this);
			}
			if($this->noError()==1064||$this->noError()==1054){
				throw new DbSQLGrammarException($errorMessage, $this->noError(), true, $this);
			}
			if($this->noError()==1451){
				throw new DbConstraintViolationException($errorMessage, $this->noError(), true, $this);
			}
			if($this->noError()==1292){
				throw new DbInvalidFormatException($errorMessage, $this->noError(), true, $this);
			}*/
			throw new DbException($errorMessage, $this->noError(), true, $this);
			return false;
		}
	}

	/**
	 * Cierra la Conexion al Motor de Base de datos
	 */
	public function close(){
		if($this->_idConnection) {
			pg_close($this->_idConnection);
		}
	}

	/**
	 * Devuelve fila por fila el contenido de una consulta
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
		return pg_fetch_array($resultQuery, null, $this->_fetchMode);
	}

	/**
	 * Devuelve el numero de filas de un select
	 *
	 * @param resource $resultQuery
	 * @return boolean|integer
	 */
	public function numRows($resultQuery=''){
		if(!$this->_idConnection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($numberRows = pg_num_rows($resultQuery))!==false){
			return $numberRows;
		} else {
			$this->log($this->error(), Logger::ERROR);
			$this->lastError = $this->error();
			return false;
		}
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
		if(!$this->_idConnection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($fieldName = pg_field_name($resultQuery, $number))!==false){
			return $fieldName;
		} else {
			$this->lastError = pg_last_error($this->_idConnection);
			$this->log($this->error(), Logger::ERROR);
			return false;
		}
		return false;
	}


	/**
	 * Se Mueve al resultado indicado por $number en un select
	 *
	 * @param integer $number
	 * @param resource $resultQuery
	 * @return boolean
	 */
	public function dataSeek($number, $resultQuery=''){
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($success = pg_result_seek($resultQuery, $number))!==false){
			return $success;
		} else {
			if($this->display_errors){
				throw new DbException($this->error());
			}
			$this->lastError = $this->error();
			$this->log($this->error(), Logger::ERROR);
			return false;
		}
		return false;
	}

	/**
	 * Numero de Filas afectadas en un insert, update o delete
	 *
	 * @param resource $resultQuery
	 * @return integer
	 */
	public function affectedRows($resultQuery=''){
		if(!$this->_idConnection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($numberRows = pg_affected_rows($resultQuery))!==false){
			return $numberRows;
		} else {
			$this->lastError = $this->error();
			$this->log($this->error(), Logger::ERROR);
			return false;
		}
		return false;
	}

	/**
	 * Devuelve el error de PostgreSQL
	 *
	 * @param	string $errorString
	 * @return	string
	 */
	public function error($errorString='', $resultQuery=null){
		if(!$this->_idConnection){					
			$this->_lastError = $errorString;
			$this->log($this->_lastError, Logger::ERROR);
			return $this->_lastError;
		}
		$errorMessage = pg_last_error($this->_idConnection);
		if($errorMessage!=""){
			$this->_lastError = "\"".$errorMessage."\" ".$errorString;
		} else {
			$this->_lastError = "[Error Desconocido en PostgreSQL: $errorString]";
		}
		$this->log($this->_lastError, Logger::ERROR);
		return $this->_lastError;
	}

	/**
	 * Devuelve el no error de PostgreSQL
	 *
	 * @return	integer|boolean
	 */
	public function noError($resultQuery=null){
		if(!$this->_idConnection){
			return false;
		}
		return 0;
		//return mysql_errno($this->_idConnection);
	}

	/**
	 * Verifica si una tabla existe o no
	 *
	 * @param	string $schemaName
	 * @param	string $tableName
	 * @return	boolean
	 */
	public function tableExists($tableName, $schemaName=''){
		$table = strtolower($tableName);
		if($schemaName==''){
			$sql = "select count(*) from information_schema.tables where table_schema = 'public' and table_name='$tableName'";
		} else {
			$sql = "select count(*) from information_schema.tables where table_schema = '$schemaName' and table_name='$tableName'";
		}
		return $this->fetchOne($sql);
	}

	/**
	 * Devuelve un FOR UPDATE valido para un SELECT del RBDM
	 *
	 * @param string $sqlQuery
	 * @return string
	 */
	public function forUpdate($sqlQuery){
		return "$sqlQuery FOR UPDATE";
	}

	/**
	 * Devuelve un SHARED LOCK valido para un SELECT del RBDM
	 *
	 * @param string $sqlQuery
	 * @return string
	 */
	public function sharedLock($sqlQuery){
		return "$sqlQuery LOCK IN SHARE MODE";
	}

	/**
	 * Devuelve un LIMIT valido para un SELECT del RBDM
	 *
	 * @param string $sql
	 * @param integer $number
	 * @return string
	 */
	public function limit($sql, $number){
		if(is_numeric($number)){
			$number = (int) $number;
			return "$sql LIMIT $number";
		} else {
			return $sql;
		}
	}

	/**
	 * Borra una tabla de la base de datos
	 *
	 * @param	string $table
	 * @param	boolean $ifExists
	 * @return	boolean
	 */
	public function dropTable($table, $ifExists=true){
		if($ifExists){
			if($this->tableExists($table)){
				return $this->query("DROP TABLE $table");
			} else {
				return true;
			}
		} else {
			return $this->query("DROP TABLE $table");
		}
	}

	/**
	 * Devuelve el ultimo ROW'id en la ultima inserción
	 *
	 * @param	string $table
	 * @param	array $primaryKey
	 * @param	string $sequenceName
	 * @return	integer
	 */
	public function lastInsertId($table='', $primaryKey=array(), $sequenceName=''){
		return pg_last_oid($this->lastResultQuery);
	}

	/**
	 * Indica si el RBDM requiere de secuencias y devuelve el nombre por convencion
	 *
	 * @param string $tableName
	 * @param array $primaryKey
	 * @return boolean
	 */
	public function getRequiredSequence($tableName='', $identityColumn='', $sequenceName=''){
		return false;
	}

	/**
	 * Crea una tabla utilizando SQL nativo del RDBM
	 *
	 * TODO:
	 * - Falta que el parametro index funcione. Este debe listar indices compuestos multipes y unicos
	 * - Soporte para campos autonumericos
	 * - Soporte para llaves foraneas
	 *
	 * @param	string $table
	 * @param	array $definition
	 * @param	array $index
	 * @param	array $tableOptions
	 * @return	boolean
	 */
	public function createTable($table, $definition, $index=array(), $tableOptions=array()){
		if(isset($tableOptions['temporary'])&&$tableOptions['temporary']==true){
			$createSQL = 'CREATE TEMPORARY TABLE '.$table.' (';
		} else {
			$createSQL = 'CREATE TABLE '.$table.' (';
		}
		if(is_array($definition)==false){
			throw new DbException("Definición invalida para crear la tabla '$table'");
			return false;
		}
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
					$index[] = "INDEX($field)";
				}
			}
			if(isset($fieldDefinition['unique_index'])){
				if($fieldDefinition['unique_index']){
					$index[] = "UNIQUE($field)";
				}
			}
			if(isset($fieldDefinition['primary'])){
				if($fieldDefinition['primary']){
					$primary[] = "$field";
				}
			}
			if(isset($fieldDefinition['auto'])){
				if($fieldDefinition['auto']){
					$fieldDefinition['type'] = 'SERIAL';
				}
			}
			if(isset($fieldDefinition['extra'])){
				$extra = $fieldDefinition['extra'];
			} else {
				$extra = "";
			}
			$createLines[] = "$field ".$fieldDefinition['type'].$size.' '.$notNull.' '.$extra;
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
		}
		return $this->query($createSQL);
	}

	/**
	 * Listar las tablas en la base de datos
	 *
	 * @return array
	 */
	public function listTables($schemaName=''){
		return $this->fetchAll("SELECT c.relname AS table_name FROM pg_Class c, pg_user u "
		."WHERE c.relowner = u.usesysid AND c.relkind = 'r' "
		."AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) "
		."AND c.relname !~ '^(pg_|sql_)' UNION "
		."SELECT c.relname AS table_name FROM pg_Class c "
		."WHERE c.relkind = 'r' "
		."AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) "
		."AND NOT EXISTS (SELECT 1 FROM pg_user WHERE usesysid = c.relowner) "
		."AND c.relname !~ '^pg_'");
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @param string $table
	 * @param string $schema
	 * @return array
	 */
	public function describeTable($table, $schema=''){
		$describe = $this->fetchAll("SELECT a.attname AS Field, t.typname AS Type,
		CASE WHEN attnotnull=false THEN 'YES' ELSE 'NO' END AS Null,
		CASE WHEN (select cc.contype FROM pg_catalog.pg_constraint cc WHERE
		cc.conrelid = c.oid AND cc.conkey[1] = a.attnum)='p' THEN 'PRI' ELSE ''
		END AS Key FROM pg_catalog.pg_Class c, pg_catalog.pg_attribute a,
		pg_catalog.pg_type t WHERE 
		c.relname = '$table' AND 
		c.oid = a.attrelid AND 
		a.attnum > 0 AND
		c.relhaspkey = 't' AND 
		t.oid = a.atttypid 
		order by a.attnum");
		$finalDescribe = array();
		foreach($describe as $key => $value){
			$finalDescribe[] = array(
				"Field" => $value["field"],
				"Type" => $value["type"],
				"Null" => $value["null"],
				"Key" => $value["key"]
			);
		}
		return $finalDescribe;
	}

	/**
	 * Devuelve el ultimo cursor generado por el driver
	 *
	 * @return resource
	 */
	public function getLastResultQuery(){
		return $this->_lastResultQuery;
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
		return new DbRawValue("now()");
	}

	/**
	 * Permite establecer el nivel de isolacion de la conexion
	 *
	 * @param int $isolationLevel
	 */
	public function setIsolationLevel($isolationLevel){
		switch($isolationLevel){
			case 1:
				$isolationCommand = "SET SESSION TRANSACTION READ UNCOMMITED";
				break;
			case 2:
				$isolationCommand = "SET SESSION TRANSACTION READ COMMITED";
				break;
			case 3:
				$isolationCommand = "SET SESSION TRANSACTION REPETEABLE READ";
				break;
			case 4:
				$isolationCommand = "SET SESSION TRANSACTION SERIALIZABLE";
				break;
		}
		$this->query($isolationCommand);
		return true;
	}

	/**
	 * Establece el modo en se que deben devolver los registros
	 *
	 * @param	int $fetchMode
	 */
	public function setFetchMode($fetchMode){
		if($fetchMode==self::DB_ASSOC){
			$this->_fetchMode = PGSQL_ASSOC;
			return;
		}
		if($fetchMode==self::DB_BOTH){
			$this->_fetchMode = PGSQL_BOTH;
			return;
		}
		if($fetchMode==self::DB_NUM){
			$this->_fetchMode = PGSQL_NUM;
			return;
		}
	}

	/**
	 * Destructor de DbMysql
	 *
	 */
	public function __destruct(){
		#$this->close();
	}


}
