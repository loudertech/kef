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
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Mysqli.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * MySQL Database Support (con MySQLi)
 *
 * Estas funciones le permiten acceder a servidores de bases de datos MySQL y
 * aprovechar la funcionalidad de los servidores 4.1 en adelante.
 * Puede encontrar mas informacion sobre MySQL en http://www.mysql.com/.
 * La documentacion de MySQL puede encontrarse en http://dev.mysql.com/doc/.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @link		http://www.php.net/manual/es/ref.mysqli.php
 * @access		Public
 */
class DbMySQLi extends DbBase implements DbBaseInterface  {

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
	 * @return	resource
	 */
	public function connect($descriptor=''){
		if($descriptor==''){
			$descriptor = $this->_descriptor;
		}
		$host = isset($descriptor->host) ? $descriptor->host : null;
		$username = isset($descriptor->username) ? $descriptor->username : "";
		$password = isset($descriptor->password) ? $descriptor->password : null;
		$port = isset($descriptor->port) ? $descriptor->port : null;
		$dbname = isset($descriptor->name) ? $descriptor->name : null;
		$socket = isset($descriptor->socket) ? $descriptor->socket : null;

		$clientFlags = null;
		if(isset($descriptor->compression)){
			if($descriptor->compression==true){
				if($clientFlags==null){
					$clientFlags = MYSQLI_CLIENT_COMPRESS;
				} else {
					$clientFlags |= MYSQLI_CLIENT_COMPRESS;
				}
			}
		}
		if(isset($descriptor->ssl)){
			if($descriptor->ssl==true){
				if($clientFlags==null){
					$clientFlags = MYSQLI_CLIENT_SSL;
				} else {
					$clientFlags |= MYSQLI_CLIENT_SSL;
				}
				$key = isset($descriptor->key) ? $descriptor->key : null;
				$cert = isset($descriptor->cert) ? $descriptor->cert : null;
				$ca = isset($descriptor->ca) ? $descriptor->ca : null;
				$capath = isset($descriptor->capath) ? $descriptor->capath : null;
				$cipher = isset($descriptor->cipher) ? $descriptor->cipher : null;
				mysqli_ssl_set($key, $cert, $ca, $capath, $cipher);
			}
		}
		if(isset($descriptor->interactive)){
			if($descriptor->interactive==true){
				if($clientFlags==null){
					$clientFlags = MYSQLI_CLIENT_INTERACTIVE;
				} else {
					$clientFlags |= MYSQLI_CLIENT_INTERACTIVE;
				}
			}
		}

		$this->_idConnection = mysqli_init();
		if(isset($descriptor->autocommit)){
			if($descriptor->autocommit==true){
				mysqli_options($this->_idConnection, MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT=1');
			} else {
				mysqli_options($this->_idConnection, MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT=0');
			}
			$this->_autoCommit = (bool) $descriptor->autocommit;
		} else {
			$this->_autoCommit = false;
		}
		if(mysqli_real_connect($this->_idConnection, $host, $username, $password, $dbname, $socket, $clientFlags)==true){
			if(isset($descriptor->charset)){
				if(@mysqli_set_charset($this->_idConnection, $descriptor->charset)==false){
					$this->_idConnection = null;
					throw new DbException($this->error("Charset inválido '".$descriptor->charset."'"), $this->noError(), false);
					return false;
				}
			}
			$this->_fetchMode = MYSQLI_BOTH;
			parent::__construct($descriptor);
			return true;
		} else {
			$this->_idConnection = null;
			throw new DbException($this->error(), $this->noError(), false);
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
		if($resultQuery = mysqli_query($this->_idConnection, $sqlStatement)){
			$this->_lastResultQuery = $resultQuery;
			parent::afterQuery($sqlStatement);
			return $resultQuery;
		} else {
			$this->_lastResultQuery = false;
			$errorMessage = $this->error(" al ejecutar \"$sqlStatement\" en la conexión \"".$this->getConnectionId(true)."\"");
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
			return false;
		}
	}

	/**
	 * Cierra la Conexion al Motor de Base de datos
	 *
	 * @access public
	 * @return boolean
	 */
	public function close(){
		if($this->_idConnection){
			parent::close();
			$success = mysqli_close($this->_idConnection);
			$this->_idConnection = null;
			return $success;
		} else {
			return false;
		}
	}

	/**
	 * Devuelve fila por fila el contenido de un select
	 *
	 * @param resource $resultQuery
	 * @param integer $opt
	 * @return array
	 */
	public function fetchArray($resultQuery='', $opt=''){
		if(!$this->_idConnection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->_lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		return mysqli_fetch_array($resultQuery, $this->_fetchMode);
	}

	/**
	 * Devuelve el numero de filas de un select
	 *
	 * @access public
	 * @param boolean $resultQuery
	 */
	public function numRows($resultQuery=''){
		if(!$this->_idConnection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->_lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($numberRows = @mysqli_num_rows($resultQuery))!==false){
			return $numberRows;
		} else {
			if(isset($php_errormsg)){
				throw new DbException($this->error($php_errormsg), $this->noError());
			} else {
				throw new DbException($this->error(), $this->noError());
			}
			return false;
		}
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
		if(!$this->_idConnection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->_lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($fieldName = @mysqli_field_seek($resultQuery, $number))!==false){
			$field = mysqli_fetch_field($resultQuery);
			return $field->name;
		} else {
			if(isset($php_errormsg)){
				throw new DbException($this->error($php_errormsg), $this->noError());
			} else {
				throw new DbException($this->error(), $this->noError());
			}
			return false;
		}
		return false;
	}

	/**
	 * Se mueve al resultado indicado por $number en un select
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
		if(($success = @mysqli_data_seek($resultQuery, $number))!==false){
			return $success;
		} else {
			if(isset($php_errormsg)){
				throw new DbException($this->error($php_errormsg), $this->noError());
			} else {
				throw new DbException($this->error(), $this->noError());
			}
			return false;
		}
		return false;
	}

	/**
	 * Número de filas afectadas en un insert, update o delete
	 *
	 * @param	resource $resultQuery
	 * @return	integer
	 */
	public function affectedRows($resultQuery=''){
		if(($numberRows = @mysqli_affected_rows($this->_idConnection))!==false){
			return $numberRows;
		} else {
			if(isset($php_errormsg)){
				$this->_lastError = $this->error($php_errormsg);
				throw new DbException($this->error($php_errormsg), $this->noError());
			} else {
				throw new DbException($this->error(), $this->noError());
			}
			return false;
		}
		return false;
	}

	/**
	 * Devuelve el error de MySQL
	 *
	 * @param string $errorString
	 * @return string
	 */
	public function error($errorString='', $resultQuery=null){
		if(!$this->_idConnection){
			$errorMessage = mysqli_connect_error();
			if($errorMessage){
				$this->_lastError = "\"".$errorMessage."\" ".$errorString;
			} else {
				$this->_lastError = "[Error Desconocido en MySQLi: $errorString]";
			}
			$this->log($this->_lastError, Logger::ERROR);
			return $this->_lastError;
		}
		$errorMessage = mysqli_error($this->_idConnection);
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
			return mysqli_connect_errno();
		}
		return mysqli_errno($this->_idConnection);
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
		return mysqli_insert_id($this->_idConnection);
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
	 * Verifica si una tabla existe o no
	 *
	 * @access public
	 * @param string $table
	 * @param string $schema
	 * @return boolean
	 */
	public function tableExists($tableName, $schemaName=''){
		$tableName = addslashes("$tableName");
		if($schemaName==''){
			$sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$tableName'";
		} else {
			$schemaName = addslashes("$schemaName");
			$sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$tableName' AND TABLE_SCHEMA = '$schemaName'";
		}
		$fetchMode = $this->_fetchMode;
		$this->_fetchMode = MYSQLI_NUM;
		$num = $this->fetchOne($sql);
		$this->_fetchMode = $fetchMode;
		return $num[0];
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
	 * @access public
	 * @param string $table
	 * @param string $schema
	 * @return boolean
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
	 * @access public
	 * @param string $sqlQuery
	 * @param integer $number
	 * @return string
	 */
	public function limit($sqlQuery, $number){
		if(is_numeric($number)){
			$number = (int) $number;
			return "$sqlQuery LIMIT $number";
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
		if($ifExists){
			return $this->query('DROP TABLE IF EXISTS '.$table);
		} else {
			return $this->query('DROP TABLE '.$table);
		}
	}

	/**
	 * Crea una tabla utilizando SQL nativo del RDBM
	 *
	 * TODO:
	 * - Falta que el parametro index funcione. Este debe listar indices compuestos multipes y unicos
	 * - Soporte para campos autonumericos
	 * - Soporte para llaves foraneas
	 *
	 * @access	public
	 * @param	string $table
	 * @param	array $definition
	 * @param	array $index
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
		if($schemaName!=""){
			$query = "SHOW TABLES `$schemaName`";
		} else {
			$query = "SHOW TABLES";
		}
		$fetchMode = $this->_fetchMode;
		$this->_fetchMode = self::DB_NUM;
		$tables = $this->fetchAll($query);
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
		if($schema==''){
			$query = "DESCRIBE `$table`";
		} else {
			$query = "DESCRIBE `$schema`.`$table`";
		}
		$this->_fetchMode = MYSQLI_ASSOC;
		$describe = $this->fetchAll($query);
		$this->_fetchMode = MYSQLI_BOTH;
		return $describe;
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
	 * Devuelve el Thread Id de la conexion
	 *
	 * @param boolean $asString
	 * @return int
	 */
	public function getConnectionId($asString=false){
		if($asString==true){
			return 'Thread: '.mysqli_thread_id($this->_idConnection);
		} else {
			return $this->_idConnection;
		}
	}

	/**
	 * Establece el modo en se que deben devolver los registros
	 *
	 * @param int $fetchMode
	 */
	public function setFetchMode($fetchMode){
		if($fetchMode==self::DB_BOTH){
			$this->_fetchMode = MYSQLI_BOTH;
			return;
		}
		if($fetchMode==self::DB_ASSOC){
			$this->_fetchMode = MYSQLI_ASSOC;
			return;
		}
		if($fetchMode==self::DB_NUM){
			$this->_fetchMode = MYSQLI_NUM;
			return;
		}
	}

	/**
	 * Destructor de DbMysqli
	 *
	 */
	public function __destruct(){
		$this->close();
	}

	/**
	 * Devuelve la extension ó extensiones de PHP requeridas para
	 * usar el adaptador
	 *
	 * @return string|array
	 */
	public static function getPHPExtensionRequired(){
		return 'mysqli';
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
