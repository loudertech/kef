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
 * @version 	$Id: Db2.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * IBM DB2 Database Support
 *
 * Estas funciones le permiten acceder a servidores de bases de datos IBM DB2.
 * Puede encontrar mas informacion sobre DB2 en http://www-01.ibm.com/software/data/db2/.
 * La documentacion de DB2 puede encontrarse en http://publib.boulder.ibm.com/infocenter/dzichelp/v2r2/index.jsp?topic=/com.ibm.db2z10.doc/db2z_10_prodhome.htm.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @link		http://www.php.net/manual/en/book.ibm-db2.php
 * @access		Public
 */
class DbDb2 extends DbBase
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
	 * Indica si los cursores son scrollables por defecto
	 *
	 * @var boolean
	 */
	private $_scrollableCursors = true;

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
	 * Establece si los cursores deben ser scrollables o no
	 *
	 * @param boolean $scrollableCursors
	 */
	public function setScrollableCursors($scrollableCursors){
		$this->_scrollableCursors = $scrollableCursors;
	}

	/**
	 * Hace una conexión a la base de datos de DB2
	 *
	 * @param	stdClass $descriptor
	 * @param 	boolean $persistent
	 * @return	resource
	 */
	public function connect($descriptor='', $persistent=false){
		if($descriptor==''){
			$descriptor = $this->_descriptor;
		}
		$connectionString = '';
		if(isset($descriptor->name)){
			$connectionString.='DATABASE='.$descriptor->name.';';
		}
		if(isset($descriptor->host)){
			$connectionString.='HOSTNAME='.$descriptor->host.';PROTOCOL=TCPIP;';
		}
		if(isset($descriptor->port)){
			$connectionString.='PORT='.$descriptor->port.';';
		}
		if(isset($descriptor->username)){
			$connectionString.='UID='.$descriptor->username.';';
		}
		if(isset($descriptor->password)){
			$connectionString.='PWD='.$descriptor->password.';';
		}
		if(isset($descriptor->schema)){
			$connectionString.='PWD='.$descriptor->schema.';';
		}
		$this->_idConnection = db2_connect($connectionString, '', '');
		if(!$this->_idConnection){
			$this->_throwError();
		} else {
			$this->_autoCommit = false;
			$this->_fetchMode = self::DB_BOTH;
			parent::__construct($descriptor);
			parent::connect();
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
		if($this->_scrollableCursors==true){
			$resultQuery = @db2_exec($this->_idConnection, $sqlStatement, array(
				'cursor' => DB2_SCROLLABLE,
				'db2_attr_case' => DB2_CASE_LOWER
			));
		} else {
			$resultQuery = @db2_exec($this->_idConnection, $sqlStatement, array(
				'cursor' => DB2_FORWARD_ONLY,
				'db2_attr_case' => DB2_CASE_LOWER
			));
		}
		if($resultQuery){
			$this->_lastResultQuery = $resultQuery;
			parent::afterQuery($sqlStatement);
			return $resultQuery;
		} else {
			$this->_lastResultQuery = false;
			$this->_throwError($sqlStatement);
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
			$success = @db2_close($this->_idConnection);
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
		if($this->_fetchMode==DbBase::DB_BOTH){
			return db2_fetch_both($resultQuery);
		} else {
			if($this->_fetchMode==DbBase::DB_NUM){
				return db2_fetch_array($resultQuery);
			} else {
				return db2_fetch_assoc($resultQuery);
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
		if($this->_scrollableCursors==true){
			if(!$resultQuery){
				$resultQuery = $this->_lastResultQuery;
				if(!$resultQuery){
					return false;
				}
			}
			if(($numberRows = @db2_num_rows($resultQuery))!==false){
				if(($numberRows = @db2_num_rows($resultQuery))!==false){
					return $numberRows;
				} else {
					if(isset($php_errormsg)){
						$this->_lastError = $this->error($php_errormsg);
					}
					$this->_throwError();
				}
			}
		} else {
			$sql = $this->_lastQuery;
			if(preg_match('/SELECT [COUNT|MIN|MAX|AVG]/i', $sql)==false){
				$fromPosition = stripos($sql, 'FROM');
				if($fromPosition===false){
					return 0;
				} else {
					$sqlQuery = 'SELECT COUNT(*) AS rowcount '.substr($sql, $fromPosition);
					$resultQuery = @db2_exec($this->_idConnection, $sqlQuery, array('cursor' => DB2_FORWARD_ONLY));
					if($resultQuery){
						$row = db2_fetch_assoc($resultQuery);
						return $row['ROWCOUNT'];
					} else {
						$this->_throwError($sqlQuery);
					}
				}
			} else {
				return 1;
			}
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
		if(($fieldName = db2_field_name($resultQuery, $number))!==false){
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
		if(($success = @db2_fetch_row($resultQuery, $number))!==false){
			return $success;
		} else {
			$this->_throwError();
		}
	}

	/**
	 * Número de filas afectadas en un INSERT, UPDATE ó DELETE
	 *
	 * @param	resource $resultQuery
	 * @return	integer
	 */
	public function affectedRows($resultQuery=''){
		if(!$resultQuery){
			$resultQuery = $this->_lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($numberRows = @db2_num_rows($resultQuery))!==false){
			return $numberRows;
		} else {
			if(isset($php_errormsg)){
				$this->_lastError = $this->error($php_errormsg);
			}
			$this->_throwError();
		}
	}

	/**
	 * Lanza un DbException
	 *
	 * @param	string $errorString
	 * @return	string
	 */
	public function _throwError($sqlStatement=null){
		if($this->_idConnection){
			$dbErrorMessage = db2_stmt_errormsg();
			preg_match('/SQLCODE=([\-0-9]+)/', $dbErrorMessage, $matches);
			$errorMessage = $dbErrorMessage.' al ejecutar "'.$sqlStatement.'" en la conexión "'.$this->_idConnection.'"';
			if(isset($matches[1])){
				$numberError = $matches[1];
			} else {
				$numberError = 0;
			}
		} else {
			$errorMessage = db2_conn_errormsg();
			preg_match('/SQLCODE=([\-0-9]+)/', $errorMessage, $matches);
			if(isset($matches[1])){
				$numberError = $matches[1];
			} else {
				$numberError = 0;
			}
		}
		if($numberError==-204||$numberError==-104||$numberError==-99999){
			throw new DbSQLGrammarException($errorMessage, $numberError, true, $this);
		}
		if($numberError==-407){
			throw new DbConstraintViolationException($errorMessage, $numberError, true, $this);
		}
		throw new DbException($errorMessage, $numberError, true, $this);
	}

	/**
	 * Devuelve el error de DB2
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
				$this->_lastError = "[Error Desconocido en IBM-DB2: $errorString]";
			}
			$this->log($this->_lastError, Logger::ERROR);
			return $this->_lastError;
		}
		$errorMessage = mysql_error($this->_idConnection);
		if($errorMessage!=""){
			$this->_lastError = "\"".$errorMessage."\" ".$errorString;
		} else {
			$this->_lastError = "[Error Desconocido en IBM-DB2: $errorString]";
		}
		$this->log($this->_lastError, Logger::ERROR);
		return $this->_lastError;
	}

	/**
	 * Devuelve el no error de DB2
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
	 * @access	public
	 * @param	string $table
	 * @param	array $identityColumn
	 * @return	integer
	 */
	public function lastInsertId($table='', $identityColumn='', $sequenceName=''){
		if(!$this->_idConnection){
			return false;
		}
		if(function_exists('db2_last_insert_id')){
			return db2_last_insert_id($this->_idConnection);
		} else {
			$value = $this->fetchOne('SELECT IDENTITY_VAL_LOCAL() FROM SYSIBM.SYSDUMMY1');
			return $value[1];
		}
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
		$sql = Db2SQLDialect::tableExists($tableName, $schemaName);
		$fetchMode = $this->_fetchMode;
		$this->_fetchMode = DbBase::DB_NUM;
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
				$this->query("DESCRIBE TABLE `$schemaName`.`$tableName`");
			} else {
				$this->query("DESCRIBE TABLE $tableName");
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
		$createSQLs = Db2SQLDialect::createTable($table, $schema, $definition);
		foreach($createSQLs as $createSQL){
			$this->query($createSQL);
		}
		return true;
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
		$sql = Db2SQLDialect::addColumn($tableName, $schemaName, $column);
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
		$sql = Db2SQLDialect::modifyColumn($tableName, $schemaName, $column);
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
		$sql = Db2SQLDialect::dropColumn($tableName, $schemaName, $columnName);
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
		$sql = Db2SQLDialect::addIndex($tableName, $schemaName, $index);
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
		$sql = Db2SQLDialect::dropIndex($tableName, $schemaName, $indexName);
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
		$sql = Db2SQLDialect::addPrimaryKey($tableName, $schemaName, $index);
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
		$sql = Db2SQLDialect::dropPrimaryKey($tableName, $schemaName);
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
		$sql = Db2SQLDialect::addForeignKey($tableName, $schemaName, $reference);
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
		$sql = Db2SQLDialect::dropForeignKey($tableName, $schemaName, $referenceName);
		return $this->query($sql);
	}

	/**
	 * Obtiene la definición del tipo de dato de una columna según DB2
	 *
	 * @param	DbColumn $column
	 * @return	string
	 */
	public function getColumnDefinition(DbColumn $column){
		return Db2SQLDialect::getColumnDefinition($column);;
	}

	/**
	 * Obtiene el SQL de creación de una tabla según DB2
	 *
	 * @param	string $table
	 * @param	string $schema
	 * @return	string
	 */
	public function getTableDefinition($table, $schema=''){
		$sql = Db2SQLDialect::describeTable($table, $schema);
		$this->_fetchMode = DbBase::DB_ASSOC;
		$describe = $this->fetchAll($sql);
		return Db2SQLDialect::getTableDefinition($table, $schema, $describe);
	}

	/**
	 * Listar las tablas en la base de datos
	 *
	 * @param	string $schemaName
	 * @return	array
	 */
	public function listTables($schemaName=''){
		$tables = array();
		if($schemaName==''){
			$cursor = db2_tables($this->_idConnection, NULL);
		} else {
			$cursor = db2_tables($this->_idConnection, NULL, strtoupper($schemaName));
		}
		while($table = db2_fetch_assoc($cursor)){
			if($table['TABLE_TYPE']=='TABLE'){
				$tables[] = strtolower($table['TABLE_NAME']);
			}
		}
		return $tables;
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @param	string $table
	 * @param	string $schema
	 * @return	array
	 */
	public function describeTable($table, $schema=''){
		$sql = Db2SQLDialect::describeTable($table, $schema);
		$this->_fetchMode = DbBase::DB_ASSOC;
		$describe = $this->fetchAll($sql);
		$fields = array();
		foreach($describe as $field){
			$fields[] = array(
				'Field' => $field['colname'],
				'Type' => $field['typename'],
				'Null' => $field['nulls'] == 'N' ? 'NO' : 'YES',
				'Key' => $field['tabconsttype'] == 'P' ? 'PRI' : '',
				'Extra' => $field['identity'] == 'Y' ? 'auto_increment' : ''
			);
		}
		$this->_fetchMode = DbBase::DB_BOTH;
		return $fields;
	}

	/**
	 * Listar los indices de una tabla
	 *
	 * @param	string $table
	 * @param	string $schema
	 * @return	array
	 */
	public function describeIndexes($table, $schema=''){
		$sql = Db2SQLDialect::describeIndexes($table, $schema);
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
		$sql = Db2SQLDialect::describeReferences($table, $schema);
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
		$sql = Db2SQLDialect::tableOptions($table, $schema);
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
	 * Inicia una transacción
	 *
	 * @return boolean
	 */
	public function begin(){
		$this->_autoCommit = false;
		$this->setUnderTransaction(true);
		parent::beforeQuery('/* BEGIN */');
		db2_autocommit($this->_idConnection, DB2_AUTOCOMMIT_OFF);
		parent::afterQuery('/* BEGIN */');
		return true;
	}

	/**
	 * Cancela una transacción si es posible
	 *
	 * @access public
	 * @return boolean
	 */
	public function rollback(){
		if($this->isUnderTransaction()==true){
			$this->setUnderTransaction(false);
			$this->_autoCommit = true;
			parent::beforeQuery('/* ROLLBACK */');
			$success = db2_rollback($this->_idConnection);
			parent::afterQuery('/* ROLLBACK */');
			return $success;
		} else {
			if($this->_autoCommit==false){
				throw new DbException('No hay una transacción activa en la conexión al gestor relacional', 0, true, $this);
			}
		}
	}

	/**
	 * Hace commit sobre una transacción si es posible
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function commit(){
		if($this->isUnderTransaction()==true){
			$this->setUnderTransaction(false);
			$this->_autoCommit = true;
			parent::beforeQuery('/* COMMIT */');
			$success = db2_commit($this->_idConnection);
			parent::afterQuery('/* COMMIT */');
			return $success;
		} else {
			if($this->_autoCommit==false){
				throw new DbException('No hay una transacción activa en la conexión al gestor relacional', 0, true, $this);
			}
		}
	}

	/**
	 * Devuelve la fecha actual del motor
	 *
	 *@return string
	 */
	public function getCurrentDate(){
		return new DbRawValue('CURRENT DATE');
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
		$this->_fetchMode = $fetchMode;
	}

	/**
	 * Obtiene la información del servidor DB2
	 *
	 */
	public function getServerInfo(){
		return db2_server_info($this->_idConnection);
	}

	/**
	 * Destructor de DbDb2
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
		return array('ibm_db2');
	}

	/**
	 * Devuelve el SQL Dialect que debe ser usado
	 *
	 * @return	string
	 * @static
	 */
	public static function getSQLDialect(){
		return 'Db2';
	}

}
