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
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Oracle.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * Oracle Database Support
 *
 * Estas funciones le permiten acceder a servidores de bases de datos Oracle.
 * Puede encontrar mas información sobre Oracle en http://www.oracle.com/.
 * La documentación de Oracle puede encontrarse en http://www.oracle.com/technology/documentation/index.html.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @link		http://www.php.net/manual/es/ref.oci8.php
 */
class DbOracle extends DbBase implements DbBaseInterface  {

	/**
	 * Ultimo mensaje de error
	 *
	 * @var array
	 */
	private $_errorMessage = array();

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
	const TYPE_VARCHAR = 'VARCHAR2';

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
	 * @access	public
	 * @param	stdClass $descriptor
	 */
	public function __construct($descriptor=''){
		if($descriptor==''){
			$descriptor = $this->_descriptor;
		}
		$this->connect($descriptor);
	}

	/**
	 * Hace una conexión a la base de datos de Oracle
	 *
	 * @param	string $dbhost
	 * @param	string $dbuser
	 * @param	string $dbpass
	 * @param	string $dbname
	 * @param	string $dbport
	 * @param	string $dbdsn
	 * @return	resource
	 */
	public function connect($descriptor=''){
		if($descriptor==''){
			$descriptor = $this->_descriptor;
		}
		$host = isset($descriptor->host) ? $descriptor->host : '';
		$username = isset($descriptor->username) ? $descriptor->username : '';
		$password = isset($descriptor->password) ? $descriptor->password : '';
		$charset = isset($descriptor->charset) ? $descriptor->charset : 'AL32UTF8';
		$instance = isset($descriptor->instance) ? $descriptor->instance : '';
		if(isset($descriptor->port)){
			$dbstring = '//'.$host.':'.$descriptor->port.'/'.$instance;
		} else {
			$dbstring = '//'.$host.'/'.$instance;
		}
		if($this->_idConnection = @oci_connect($username, $password, $dbstring, $charset)){
			/*$sort = isset($descriptor->sort) ? $descriptor->sort : 'binary_ci';
			$comp = isset($descriptor->comp) ? $descriptor->comp : 'linguistic';
			$language = isset($descriptor->language) ? $descriptor->language : 'spanish';
			$territory = isset($descriptor->territory) ? $descriptor->territory : 'spain';
			$date_format = isset($descriptor->date_format) ? $descriptor->date_format : 'YYYY-MM-DD HH24:MI:SS';
			$this->query("ALTER SESSION SET nls_date_format='$date_format' nls_territory=$territory nls_language=$language nls_sort=$sort nls_comp=$comp");*/
			parent::__construct($descriptor);
			parent::connect();
		}
		if(!$this->_idConnection){
			throw new DbException($this->error($php_errormsg), $this->noError(), false);
		} else {
			return true;
		}
	}

	/**
	 * Efectua operaciones SQL sobre la base de datos
	 *
	 * @param	string $sqlQuery
	 * @return	resource|false
	 */
	public function query($sqlQuery){
		$this->debug($sqlQuery);
		$this->log($sqlQuery, Logger::DEBUG);
		if(!$this->_idConnection){
			$this->connect();
			if(!$this->_idConnection){
				return false;
			}
		}
		$this->_lastQuery = $sqlQuery;
		$resultQuery = @oci_parse($this->_idConnection, $sqlQuery);
		if($resultQuery){
			$this->_lastResultQuery = $resultQuery;
		} else {
			$this->_lastResultQuery = false;
			$errorCode = $this->noError($resultQuery);
			$errorMessage = $this->error($php_errormsg);
			$errorMessage = "\"$errorMessage\" al ejecutar \"$sqlQuery\"  en la conexión ".$this->_idConnection;
			switch($errorCode){
				case 1756:
					throw new DbSQLGrammarException($errorMessage, $errorCode, true, $this);
					break;
				default:
					throw new DbException($errorMessage, $errorCode, true, $this);
			}
			return false;
		}
		if($this->_autoCommit==true){
			$commit = OCI_COMMIT_ON_SUCCESS;
		} else {
			$commit = OCI_DEFAULT;
		}
		if(!@oci_execute($resultQuery, $commit)){
			$this->_lastResultQuery = false;
			$errorCode = $this->noError($resultQuery);
			$errorMessage = $this->error($php_errormsg);
			$errorMessage = "\"$errorMessage\" al ejecutar \"$sqlQuery\"  en la conexión ".$this->_idConnection;
			switch($errorCode){
				case 6550:
				case 907:
					throw new DbSQLGrammarException($errorMessage, $errorCode, true, $this);
					break;
				case 1839:
					throw new DbInvalidFormatException($errorMessage, $errorCode, true, $this);
					break;
				case 2291:
				case 2292:
				case 1:
					throw new DbConstraintViolationException($errorMessage, $errorCode, true, $this);
				case 11:
					throw new DbLockAdquisitionException($errorMessage, $errorCode, true, $this);
				default:
					throw new DbException($errorMessage, $errorCode, true, $this);
			}
			return false;
		}
		return $resultQuery;
	}

	/**
	 * Cierra la conexión al motor de Base de datos
	 *
	 * @return	boolean
	 */
	public function close(){
		if($this->_idConnection){
			return oci_close($this->_idConnection);
		}
		parent::close();
	}

	/**
	 * Devuelve fila por fila el contenido de un select
	 *
	 * @access	public
	 * @param	resource $resultQuery
	 * @param	integer $opt
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
		$result = oci_fetch_array($resultQuery, $this->_fetchMode+OCI_RETURN_NULLS);
		if(is_array($result)){
			return array_change_key_case($result, CASE_LOWER);
		} else {
			return false;
		}
		return false;
	}

	/**
	 * Devuelve el número de filas de la última sentencia SELECT ejecutada
	 *
	 * @param	resource $resultQuery
	 * @return	intger|boolean
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
				$resultQuery = @oci_parse($this->_idConnection, $sqlQuery);
				if($this->_autoCommit==true){
					$commit = OCI_COMMIT_ON_SUCCESS;
				} else {
					$commit = OCI_DEFAULT;
				}
				if(@oci_execute($resultQuery, $commit)){
					$count = oci_fetch_array($resultQuery, OCI_NUM);
					return $count[0];
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
				throw new DbException($this->error('Resource invalido para DbBase::fieldName'), $this->noError());
				return false;
			}
		}

		if(($fieldName = oci_field_name($resultQuery, $number+1))!==false){
			return strtolower($fieldName);
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
	public function dataSeek($number, $resultQuery=''){
		if(!$resultQuery){
			$resultQuery = $this->_lastResultQuery;
			if(!$resultQuery){
				throw new DbException($this->error('Resource invalido para '.__CLASS__.'::dataSeek'), $this->noError());
				return false;
			}
		}
		if($this->_autoCommit){
			$commit = OCI_COMMIT_ON_SUCCESS;
		} else {
			$commit = OCI_DEFAULT;
		}
		if(!@oci_execute($resultQuery, $commit)){
			$errorMessage = $php_errormsg." al ejecutar <i>'".$this->_lastQuery."'</i>";
			throw new DbException($this->error($errorMessage), $this->noError());
			return false;
		}
		if($number){
			for($i=0;$i<=$number-1;++$i){
				if(!oci_fetch_row($resultQuery)){
					return false;
				}
			}
		} else {
			return true;
		}
		return true;
	}

	/**
	 * Nómero de Filas afectadas en un insert, update ó delete
	 *
	 * @param resource $resultQuery
	 * @return integer
	 */
	public function affectedRows($resultQuery=''){
		if(!$this->_idConnection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->_lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($numberRows = oci_num_rows($resultQuery))!==false){
			return $numberRows;
		} else {
			throw new DbException($this->error('Resource inválido para '.__CLASS__.'::affectedRows'), $this->noError());
			return false;
		}
		return false;
	}

	/**
	 * Devuelve el error de Oracle
	 *
	 * @param string $err
	 * @return string
	 */
	public function error($err='', $resultQuery=''){
		if($resultQuery==''){
			if(!$this->_idConnection){
				$this->_errorMessage = oci_error();
				if(is_array($this->_errorMessage)){
					if($this->_errorMessage['message']==""){
						$this->_errorMessage['message'].=" > $err ";
					}
					return $this->_errorMessage['message'];
				} else {
					$this->_errorMessage['message'] = "[Error Desconocido en Oracle] $php_errormsg ";
					return $this->_errorMessage['message'];
				}
			}
			$this->_errorMessage = oci_error($this->_idConnection);
			if(is_array($this->_errorMessage)){
				$this->_errorMessage['message'].=" > $err ";
			} else {
				$this->_errorMessage['message'] = $err;
			}
			return $this->_errorMessage['message'];
		} else {
			$this->_errorMessage = oci_error($resultQuery);
			return $this->_errorMessage['message'];
		}
	}

	/**
	 * Devuelve el no error de Oracle
	 *
	 * @return integer
	 */
	public function noError($resultQuery=null){
		if($resultQuery==null){
			if(!$this->_idConnection){
				$this->_errorMessage = oci_error() ? oci_error() : 0;
				if(is_array($this->_errorMessage)){
					return $this->_errorMessage['code'];
				} else {
					if(isset($this->_errorMessage['code'])){
						return $this->_errorMessage['code'];
					}
				}
			}
			$this->_errorMessage = oci_error($this->_idConnection);
			return $this->_errorMessage['code'];
		} else {
			$this->_errorMessage = oci_error($resultQuery);
			if(is_array($this->_errorMessage)){
				return $this->_errorMessage['code'];
			} else {
				if(isset($this->_errorMessage['code'])){
					return $this->_errorMessage['code'];
				}
				return 0;
			}
		}
	}

	/**
	 * Inicia una transacción
	 *
	 * @return boolean
	 */
	public function begin(){
		$this->_autoCommit = false;
		$this->setUnderTransaction(true);
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
			return oci_rollback($this->_idConnection);
		} else {
			if($this->_autoCommit==false){
				throw new DbException("No hay una transacción activa en la conexión al gestor relacional", 0, true, $this);
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
			return oci_commit($this->_idConnection);
		} else {
			if($this->_autoCommit==false){
				throw new DbException("No hay una transacción activa en la conexión al gestor relacional", 0, true, $this);
			}
		}
	}

	/**
	 * Devuelve un LIMIT válido para un SELECT del RBDM
	 *
	 * @param	string $sql
	 * @param	integer $number
	 * @return	string
	 */
	public function limit($sql, $number){
		return OracleSQLDialect::limit($sql, $number);
	}

	/**
	 * Devuelve un FOR UPDATE valido para un SELECT del RBDM
	 *
	 * @param	string $sql
	 * @return	string
	 */
	public function forUpdate($sql){
		return OracleSQLDialect::forUpdate($sql);
	}

	/**
	 * Devuelve un SHARED LOCK valido para un SELECT del RBDM
	 *
	 * @param	string $sql
	 * @return	string
	 */
	public function sharedLock($sql){
		return OracleSQLDialect::sharedLock($sql);
	}

	/**
	 * Borra una tabla de la base de datos
	 *
	 * @param	string $table
	 * @param	boolean $ifExists
	 * @return	boolean
	 */
	public function dropTable($table, $ifExists=true){
		if($ifExists==true){
			if($this->tableExists($table)){
				$sql = OracleSQLDialect::dropTable($table);
				return $this->query($sql);
			} else {
				return true;
			}
		} else {
			$sql = OracleSQLDialect::dropTable($table);
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
		$sqlStatements = OracleSQLDialect::createTable($table, $definition, $index, $tableOptions);
		foreach($sqlStatements as $sqlStatement){
			$this->query($sqlStatement);
		}
		return true;
	}

	/**
	 * Devuelve un array con el listado de tablas del usuario
	 *
	 * @access 	public
	 * @param	string $table
	 * @return	boolean
	 */
	public function listTables($schema=''){
		if($schema==''){
			$sql = OracleSQLDialect::listTables($this->getUsername());
		} else {
			$sql = OracleSQLDialect::listTables($schema);
		}
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
	 * Indica si el RBDM requiere de secuencias y devuelve el nombre por convencion
	 *
	 * @access 	public
	 * @param	string $tableName
	 * @param	array $primaryKey
	 */
	public function getRequiredSequence($tableName='', $identityColumn='', $sequenceName=''){
		return OracleSQLDialect::getRequiredSequence($tableName, $identityColumn, $sequenceName);
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
		/**
		 * Oracle No soporta columnas identidad
		 */
		if($table&&$identityColumn){
			if($sequenceName==''){
				$sequenceName = i18n::strtoupper($table).'_'.$identityColumn.'_SEQ';
			}
			$value = $this->fetchOne('SELECT "'.i18n::strtoupper($sequenceName).'".CURRVAL FROM dual');
			return $value[0];
		}
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
	public function tableExists($table, $schema=''){
		if($schema!=''){
			$sql = OracleSQLDialect::tableExists($table, $schema);
		} else {
			$sql = OracleSQLDialect::tableExists($table, $this->getUsername());
		}
		$fetchMode = $this->_fetchMode;
		$this->_fetchMode = OCI_NUM;
		$num = $this->fetchOne($sql);
		$this->_fetchMode = $fetchMode;
		return $num[0];
	}

	/**
	 * Verifica si una vista existe o no
	 *
	 * @access	public
	 * @param	string $table
	 * @param	string $schema
	 * @return	boolean
	 */
	public function viewExists($view, $schema=''){
		if($schema!=''){
			$sql = OracleSQLDialect::viewExists($view, $schema);
		} else {
			$sql = OracleSQLDialect::viewExists($view, $this->getUsername());
		}
		$fetchMode = $this->_fetchMode;
		$this->_fetchMode = OCI_NUM;
		$num = $this->fetchOne($sql);
		$this->_fetchMode = $fetchMode;
		return $num[0];
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @access	public
	 * @param	string $table
	 * @param	string $schema
	 * @return	array
	 */
	public function describeTable($table, $schema=''){
		if($schema==''){
			$schema = $this->getUsername();
		}
		$sql = OracleSQLDialect::describeTable($table, $schema);
 		$fetchMode = $this->_fetchMode;
 		$this->_fetchMode = OCI_ASSOC;
		$describe = $this->fetchAll($sql);
		if(count($describe)>0){
			$this->_fetchMode = $fetchMode;
			$finalDescribe = array();
			$fields = array();
			foreach($describe as $key => $value){
				if(!in_array($value['field'], $fields)){
					if($value['data_precision']!=''){
						if($value['data_scale']==0){
							$type = $value['type'].'('.$value['data_precision'].')';
						} else {
							$type = $value['type'].'('.$value['data_precision'].','.$value['data_scale'].')';
						}
					} else {
						$type = $value['type'];
					}
					$finalDescribe[] = array(
						'Field' => $value['field'],
						'Type' => $type,
						'Null' => $value['isnull'] == 'Y' ? 'YES' : 'NO',
						'Key' => $value['key'] == 'P' ? 'PRI' : ''
					);
					$fields[] = $value['field'];
				}
			}
			return $finalDescribe;
		} else {
			throw new DbException('No se pudo obtener la descripción de la tabla "'.$table.'.'.$schema.'"', 0);
		}
	}

	/**
	 * Listar los campos de una vista
	 *
	 * @access	public
	 * @param	string $view
	 * @return	array
	 */
	public function describeView($view, $schema=''){
		if($schema==''){
			$schema = $this->getUsername();
		}
		$sql = OracleSQLDialect::describeView($view, $schema);
 		$fetchMode = $this->_fetchMode;
 		$this->_fetchMode = OCI_ASSOC;
		$describe = $this->fetchAll($sql);
		if(count($describe)>0){
			$this->_fetchMode = $fetchMode;
			$finalDescribe = array();
			$fields = array();
			foreach($describe as $key => $value){
				if(!in_array($value['field'], $fields)){
					if($value['data_precision']!=''){
						if($value['data_scale']==0){
							$type = $value['type'].'('.$value['data_precision'].')';
						} else {
							$type = $value['type'].'('.$value['data_precision'].','.$value['data_scale'].')';
						}
					} else {
						$type = $value['type'];
					}
					$finalDescribe[] = array(
						'Field' => $value['field'],
						'Type' => $type,
						'Null' => $value['isnull'] == 'Y' ? 'YES' : 'NO',
						'Key' => $value['key'] == 'P' ? 'PRI' : ''
					);
					$fields[] = $value['field'];
				}
			}
			return $finalDescribe;
		} else {
			throw new DbException('No se pudo obtener la descripción de la tabla "'.$table.'.'.$schema.'"', 0);
		}
	}

	/**
	 * Devuelve una fecha formateada de acuerdo al RBDM
	 *
	 * @access 	public
	 * @param	string $date
	 * @param	string $format
	 * @return	string
	 */
	public function getDateUsingFormat($date, $format='YYYY-MM-DD HH24:MI:SS'){
		if(strlen($date)<=10){
			$format = 'YYYY-MM-DD';
		}
		return "TO_DATE('$date', '$format')";
	}

	/**
	 * Devuelve la fecha actual segun el motor
	 *
	 * @return string
	 */
	public function getCurrentDate(){
		return new DbRawValue('sysdate');
	}

	/**
	 * Permite establecer el nivel de isolacion de la conexion
	 *
	 * @param int $isolationLevel
	 */
	public function setIsolationLevel($isolationLevel){
		return true;
	}

	/**
	 * Establece el modo en se que deben devolver los registros
	 *
	 * @param int $fetchMode
	 */
	public function setFetchMode($fetchMode){
		if($fetchMode==self::DB_ASSOC){
			$this->_fetchMode = OCI_ASSOC;
			return;
		}
		if($fetchMode==self::DB_BOTH){
			$this->_fetchMode = OCI_BOTH;
			return;
		}
		if($fetchMode==self::DB_NUM){
			$this->_fetchMode = OCI_NUM;
			return;
		}
	}

	/**
	 * Devuelve la extension o extensiones de PHP requeridas para
	 * usar el adaptador
	 *
	 * @return string|array
	 */
	public static function getPHPExtensionRequired(){
		return 'oci8';
	}

	/**
	 * Devuelve el SQL Dialect que debe ser usado
	 *
	 * @return	string
	 * @static
	 */
	public static function getSQLDialect(){
		return 'OracleSQLDialect';
	}

}
