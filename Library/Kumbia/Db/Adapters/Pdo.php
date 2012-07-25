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
 * @version 	$Id: Pdo.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * @see DbPDOInterface
 */
require 'Library/Kumbia/Db/Adapters/Pdo/Interface.php';

/**
 * PHP Data Objects
 *
 * The PHP Data Objects (PDO) extension defines a lightweight, consistent interface
 * for accessing databases in PHP. Each database driver that implements the PDO interface
 * can expose database-specific features as regular extension functions. Note that you cannot
 * perform any database functions using the PDO extension by itself; you must use
 * a database-specific PDO driver to access a database server.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @link		http://www.php.net/manual/es/book.pdo.php
 * @access		Public
 */
abstract class DbPDO extends DbBase implements DbPDOInterface  {

	/**
	 * Instancia PDO
	 *
	 * @var PDO
	 */
	protected $_pdo;

	/**
	 * Ultimo Resultado de una Query
	 *
	 * @var PDOStatement
	 */
	private $_pdoStatement;

	/**
	 * DSN de conexión
	 *
	 * @var string
	 */
	private $_dbDsn;

	/**
	 * Numero de filas afectadas
	 */
	protected $_affectedRows;

	/**
	 * Indica si la opcion de cursores scrollables esta disponible
	 *
	 * @var string
	 */
	protected $_scrollableCursor = false;

	/**
	 * Indica si se deben usar transacciones a bajo nivel en vez de las PDO
	 *
	 * @var boolean
	 */
	protected $_useRawTransactions = false;

	/**
	 * Excepcion generica de motor
	 *
	 */
	const EX_DEFAULT = 0;

	/**
	 * Excepcion generada cuando hay un bloqueo en alguna tabla
	 */
	const EX_LOCK_ADQUISITION = 1;

	/**
	 * Excepcion generada por errores gramaticales
	 */
	const EX_GRAMMATICAL = 2;

	/**
	 * Excepcion generada por errores de formato
	 */
	const EX_INVALID_FORMAT = 3;

	/**
	 * ISOLATION : DIRTY READ
	 *
	 */
	const ISOLATION_READ_UNCOMMITED = 1;

	/**
	 * ISOLATION : COMMITED READ
	 *
	 */
	const ISOLATION_READ_COMMITED = 2;

	/**
	 * ISOLATION : REPETEABLE READ
	 *
	 */
	const ISOLATION_REPEATABLE_READ = 3;

	/**
	 * ISOLATION: SERIALIZABLE
	 *
	 */
	const ISOLATION_SERIALIZABLE = 4;

	/**
	 * Hace una conexión a la base de datos con PDO
	 *
	 * @param string $dbdsn
	 * @param string $dbUser
	 * @param string $dbPass
	 * @return $resource
	 */
	public function connect($descriptor=''){

		#if[compile-time]
		if(!extension_loaded('pdo')){
			throw new DbException('Debe cargar la extensión de PHP llamada php_pdo');
		}
		#endif

		$username = isset($descriptor->username) ? $descriptor->username : null;
		$password = isset($descriptor->password) ? $descriptor->password : null;
		$dsn = isset($descriptor->dsn) ? $descriptor->dsn : null;
		$dsn = $this->_dbRBDM.':'.$dsn;
		try {
			$this->_pdo = new PDO($dsn, $username, $password);
			if(!$this->_pdo){
				throw new DbException('No se pudo realizar la conexión con '.$this->_dbRBDM, 0, false);
			}
			if($this->_dbRBDM!='odbc'){
				$this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->_pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
				$this->_pdo->setAttribute(PDO::ATTR_CURSOR, PDO::CURSOR_FWDONLY);
			}
			$this->_fetchMode = PDO::FETCH_BOTH;
			$this->initialize();
			parent::__construct($descriptor);
			return true;
		}
		catch(PDOException $e){
			throw new DbException($this->error($e->getMessage()), $this->noError($e->getCode()), false, $this);
		}
		return false;
	}

	/**
	 * Efectua operaciones SQL sobre la base de datos
	 *
	 * @param string $sqlQuery
	 * @return resource or false
	 */
	public function query($sqlQuery){
		if(!$this->_pdo){
			throw new DbException("No hay conexión para realizar esta acción:", 0);
		}
		parent::beforeQuery($sqlQuery);
		$this->_lastQuery = $sqlQuery;
		$this->_pdoStatement = null;
		try {
			if($pdoStatement = $this->_pdo->query($sqlQuery)){
				$this->_pdoStatement = $pdoStatement;
				$this->_pdoStatement->setFetchMode($this->_fetchMode);
				return $pdoStatement;
			} else {
				return false;
			}
		}
		catch(PDOException $e){
			$errorCode = $this->noError($e->getCode());
			$errorType = $this->_getErrorType($errorCode);
			$message = "El gestor relacional generó el mensaje '".$this->error($e->getMessage()."' al ejecutar <i>\"$sqlQuery\"</i> {$this->_dbDsn}");
			switch($errorType){
				case self::EX_DEFAULT:
					throw new DbException($message, $errorCode, true, $this);
				case self::EX_LOCK_ADQUISITION:
					throw new DbLockAdquisitionException($message, $errorCode, true, $this);
				case self::EX_GRAMMATICAL:
					throw new DbSQLGrammarException($message, $errorCode, true, $this);
				case self::EX_INVALID_FORMAT:
					throw new DbInvalidFormatException($message, $errorCode, true, $this);
				default:
					throw new DbException($message, $errorCode, true, $this);
			}
		}
		return false;
	}

	/**
	 * Efectua operaciones SQL sobre la base de datos y devuelve el numero de filas afectadas
	 *
	 * @param string $sqlQuery
	 * @return boolean
	 */
	public function exec($sqlQuery){
		if(!$this->_pdo){
			throw new DbException("No hay conexión para realizar esta acción:", 0);
		}
		parent::beforeQuery($sqlQuery);
		$this->_lastQuery = $sqlQuery;
		$this->_pdoStatement = null;
		try {
			$result = $this->_pdo->exec($sqlQuery);
			$this->_affectedRows = $result;
			if($result===false){
				throw new DbException($this->error(" al ejecutar <i>\"$sqlQuery\"</i>"), $this->noError(), true, $this);
			}
			return $result;
		}
		catch(PDOException $e) {
			$errorCode = $this->noError($e->getCode());
			$errorType = $this->_getErrorType($errorCode);
			$message = "El gestor relacional generó el mensaje '".$this->error($e->getMessage()."' al ejecutar <i>\"$sqlQuery\"</i> {$this->_dbDsn}");
			switch($errorType){
				case self::EX_DEFAULT:
					throw new DbException($message, $errorCode, true, $this);
				case self::EX_LOCK_ADQUISITION:
					throw new DbLockAdquisitionException($message, $errorCode, true, $this);
				case self::EX_GRAMMATICAL:
					throw new DbSQLGrammarException($message, $errorCode, true, $this);
				case self::EX_INVALID_FORMAT:
					throw new DbInvalidFormatException($message, $errorCode, true, $this);
				default:
					throw new DbException($message, $errorCode, true, $this);
			}
		}
	}

	/**
	 * Cierra la Conexion al Motor de Base de datos
	 *
	 * @access public
	 */
	public function close(){
		if($this->_pdo) {
			unset($this->_pdo);
			$this->_pdo = "";
			return true;
		}
		return false;
	}

	/**
	 * Devuelve fila por fila el contenido de un select
	 *
	 * @param resource $resultQuery
	 * @param integer $opt
	 * @return array
	 */
	public function fetchArray($pdoStatement=''){
		if(!$this->_pdo){
			throw new DbException("No hay conexión para realizar esta acción:", 0, true, $this);
		}
		if(!$pdoStatement){
			$pdoStatement = $this->_pdoStatement;
			if(!$pdoStatement){
				return false;
			}
		}
		try {
			return $pdoStatement->fetch($this->_fetchMode, PDO::FETCH_ORI_NEXT);
		}
		catch(PDOException $e){
			$errorCode = $this->noError($e->getCode());
			$errorType = $this->_getErrorType($errorCode);
			switch($errorType){
				case self::EX_DEFAULT:
					throw new DbException($this->error($e->getMessage()), $errorCode, true, $this);
				case self::EX_LOCK_ADQUISITION:
					throw new DbLockAdquisitionException($this->error($e->getMessage()), $errorCode, true, $this);
				case self::EX_GRAMMATICAL:
					throw new DbSQLGrammarException($this->error($e->getMessage()), $errorCode, true, $this);
				default:
					throw new DbException($this->error($e->getMessage()), $errorCode, true, $this);
			}
		}
		return false;
	}

	/**
	 * Constructor de la Clase
	 *
	 * @param string $dbhost
	 * @param string $dbuser
	 * @param string $dbpass
	 * @param string $dbname
	 * @param string $dbport
	 * @param string $dbdsn
	 */
	public function __construct($descriptor=''){
		$this->connect($descriptor);
	}

	/**
	 * Devuelve el numero de filas de un select (No soportado en PDO)
	 *
	 * @param PDOStatement $pdoStatement
	 * @deprecated
	 * @return integer
	 */
	public function numRows($pdoStatement=''){
		if($pdoStatement==''){
			$pdoStatement = $this->_pdoStatement;
			if(!$pdoStatement){
				return false;
			}
		}
		if($pdoStatement){
			$pdo = clone $pdoStatement;
			return count($this->_pdo->query($pdoStatement->queryString)->fetchAll());
			//return count($pdo->fetchAll(PDO::FETCH_NUM));
		} else {
			return 0;
		}
	}

	/**
	 * Devuelve el nombre de un campo en el resultado de un select
	 *
	 * @access public
	 * @param integer $number
	 * @param resource $resultQuery
	 * @return string
	 */
	public function fieldName($number, $pdoStatement=''){
		if(!$this->_pdo){
			throw new DbException("No hay conexión para realizar esta acción:", 0, true, $this);
		}
		if(!$pdoStatement){
			$pdoStatement = $this->_pdoStatement;
			if(!$pdoStatement){
				return false;
			}
		}
		try {
			$meta = $pdoStatement->getColumnMeta($number);
			return $meta['name'];
		}
		catch(PDOException $e) {
			throw new DbException($this->error($e->getMessage()), $this->noError($e->getCode()));
		}
		return false;
	}

	/**
	 * Se Mueve al resultado indicado por $number en un select (No soportado por PDO)
	 *
	 * @param integer $number
	 * @param PDOStatement $resultQuery
	 * @return boolean
	 */
	public function dataSeek($number, $pdoStatement=''){
		$pdoStatement->closeCursor();
		$pdoStatement->execute();
		for($i=0;$i<$number;++$i){
			$pdoStatement->fetch(PDO::FETCH_NUM);
		}
		return true;
	}

	/**
	 * Numero de Filas afectadas en un insert, update o delete
	 *
	 * @param resource $resultQuery
	 * @deprecated
	 * @return integer
	 */
	public function affectedRows($pdoStatement=''){
		if(!$this->_pdo){
			throw new DbException("No hay conexión para realizar esta acción:", 0);
		}
		if($pdoStatement){
			try {
				$rowCount = $pdoStatement->rowCount();
				if($rowCount===false){
					throw new DbException($this->error(" al ejecutar <i>\"$sqlQuery\"</i>"), $this->noError(), true, $this);
				}
				return $rowCount;
			}
			catch(PDOException $e) {
				throw new DbException($this->error($e->getMessage()), $this->noError($e->getCode()), true, $this);
			}
		} else {
			return $this->_affectedRows;
		}
		return false;
	}

	/**
	 * Devuelve el error de MySQL
	 *
	 * @return string
	 */
	public function error($err=''){
		if($this->_pdo){
			$error = $this->_pdo->errorInfo();
			$error = $error[2];
		} else {
			$error = "";
		}
		$this->_lastError.= $error." [".$err."]";
		$this->log($this->_lastError, Logger::ERROR);
		return $this->_lastError;
	}

	/**
	 * Devuelve el no error de PDO
	 *
	 * @return integer
	 */
	public function noError($number=0){
		if($this->_pdo){
			$error = $this->_pdo->errorInfo();
			$number = $error[1];
		}
		return $number;
	}

	/**
	 * Obtiene el tipo de error generado
	 *
	 * @param int $errorCode
	 */
	protected function _getErrorType($errorCode){
		return self::EX_DEFAULT;
	}

	/**
	 * Devuelve el ultimo id autonumerico generado en la BD
	 *
	 * @access public
	 * @return integer
	 */
	public function lastInsertId($table='', $primaryKey=''){
		if(!$this->_pdo){
			return false;
		}
		return $this->_pdo->lastInsertId();
	}

	/**
	 * Inicia una transacción si es posible
	 *
	 * @access public
	 * @return boolean
	 */
	public function begin(){
		$this->_autoCommit = false;
		$this->setUnderTransaction(true);
		return $this->_pdo->beginTransaction();
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
			return $this->_pdo->rollBack();
		} else {
			return false;
		}
	}

	/**
	 * Hace commit sobre una transacción si es posible
	 *
	 * @access public
	 * @return boolean
	 */
	public function commit(){
		if($this->isUnderTransaction()==true){
			try {
				$this->setUnderTransaction(false);
				$this->_autoCommit = true;
				return $this->_pdo->commit();
			}
			catch(PDOException $e){
				throw new DbException($e->getMessage(), $e->getCode(), true, $this);
			}
		} else {
			return false;
		}
		return false;
	}

	/**
	 * Agrega comillas o simples segun soporte el RBDM
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	static public function addQuotes($value){
		return "'".addslashes($value)."'";
	}

	/**
	 * Realiza una inserción
	 *
	 * @access public
	 * @param string $table
	 * @param array $values
	 * @param array $fields
	 * @return boolean
	 */
	public function insert($table, $values, $fields=null, $automaticQuotes=false){
		$insertSQL = "";
		if($this->isReadOnly()==true){
			throw new DbException("No se puede efectuar la operación. La transacción es de solo lectura");
		}
		if(is_array($values)==true){
			if(count($values)==0){
				throw new DbException("Imposible realizar inserción en '$table'' sin datos");
			} else {
				if($automaticQuotes==true){
					foreach($values as $key => $value){
						if(is_object($value)&&($value instanceof DbRawValue)){
							$values[$key] = addslashes($value->getValue());
						} else {
							$values[$key] = "'".addslashes($value)."'";
						}
					}
				}
			}
			if(is_array($fields)==true){
				$insertSQL = 'INSERT INTO '.$table.' ('.join(', ', $fields).') VALUES ('.join(', ', $values).')';
			} else {
				$insertSQL = 'INSERT INTO '.$table.' VALUES ('.join(', ', $values).')';
			}
			return $this->exec($insertSQL);
		} else{
			throw new DbException("El segundo parametro para insert no es un Array", 0, true, $this);
		}
	}

	/**
	 * Actualiza registros en una tabla
	 *
	 * @param string $table
	 * @param array $fields
	 * @param array $values
	 * @param string $whereCondition
	 * @return boolean
	 */
	public function update($table, $fields, $values, $whereCondition=null, $automaticQuotes=false){
		if($this->isReadOnly()==true){
			throw new DbException("No se puede efectuar la operación. La transacción es de solo lectura", 0, true, $this);
		}
		$updateSql = 'UPDATE '.$table.' SET ';
		if(count($fields)!=count($values)){
			throw new DbException('Los número de valores a actualizar no es el mismo de los campos', 0, true, $this);
		}
		$i = 0;
		$updateValues = array();
		foreach($fields as $field){
			if($automaticQuotes==true){
				if(is_object($values[$i])&&($values[$i] instanceof DbRawValue)){
					$values[$i] = addslashes($values[$i]->getValue());
				} else {
					$values[$i] = "'".addslashes($values[$i])."'";
				}
			}
			$updateValues[] = $field.' = '.$values[$i];
			++$i;
		}
		$updateSql.= join(', ', $updateValues);
		if($whereCondition!=null){
			$updateSql.= ' WHERE '.$whereCondition;
		}
		return $this->exec($updateSql);
	}

	/**
	 * Borra registros de una tabla!
	 *
	 * @access public
	 * @param string $table
	 * @param string $whereCondition
	 */
	public function delete($table, $whereCondition=''){
		if($this->isReadOnly()==true){
			throw new DbException('No se puede efectuar la operación. La transacción es de solo lectura', 0, true, $this);
		}
		if($whereCondition){
			return $this->exec('DELETE FROM '.$table.' WHERE '.$whereCondition);
		} else {
			return $this->exec('DELETE FROM '.$table);
		}
	}

	/**
	 * Establece el modo en se que deben devolver los registros
	 *
	 * @param int $fetchMode
	 */
	public function setFetchMode($fetchMode){
		if($fetchMode==self::DB_BOTH){
			$this->_fetchMode = PDO::FETCH_BOTH;
			return;
		}
		if($fetchMode==self::DB_ASSOC){
			$this->_fetchMode = PDO::FETCH_ASSOC;
			return;
		}
		if($fetchMode==self::DB_NUM){
			$this->_fetchMode = PDO::FETCH_NUM;
			return;
		}
	}

}
