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
 * @version 	$Id: Jdbc.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * Java DataBase Connection (JDBC)
 *
 * Java Database Connectivity (JDBC) is an API for the Java programming
 * language that defines how a client may access a database. It provides
 * methods for querying and updating data in a database. JDBC is oriented
 * towards relational databases.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	Adapters
 * @subpackage	JDBC
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @link		http://java.sun.com/products/jdbc/overview.html
 * @access		Public
 * @abstract
 */
abstract class DbJDBC extends DbBase {

	/**
	 * Connection
	 *
	 * @var java.sql.Connection
	 */
	protected $_connection;

	/**
	 * ResulSet
	 *
	 * @var java.sql.ResulSet
	 */
	protected $_resultSet;

	/**
	 * Statement
	 *
	 * @var java.sql.Statement
	 */
	protected $_statement;

	/**
	 * Tipo de Dato Integer
	 *
	 */
	const TYPE_INTEGER = 2;

	/**
	 * Tipo de Dato Date
	 *
	 */
	const TYPE_DATE = 91;

	/**
	 * Tipo de Dato Varchar
	 *
	 */
	const TYPE_VARCHAR = 12;

	/**
	 * Tipo de Dato Decimal
	 *
	 */
	const TYPE_DECIMAL = 2;

	/**
	 * Tipo de Dato Datetime
	 *
	 */
	const TYPE_DATETIME = 91;

	/**
	 * Tipo de Dato Char
	 *
	 */
	const TYPE_CHAR = 1;

	/**
	 * Indica si se deben devolver el resultado como un array numerico
	 *
	 */
	const JDBC_FETCH_NUM = 0;

	/**
	 * Indica si se deben devolver el resultado como un array asociativo
	 *
	 */
	const JDBC_FETCH_ASSOC = 1;

	/**
	 * Indica si se deben devolver el resultado como un array numerico y asociativo
	 *
	 */
	const JDBC_FETCH_BOTH = 3;

	/**
	 * Constructor de DbJDBC
	 *
	 * @param stdClass $descriptor
	 */
	public function __construct($descriptor){
		$this->connect($descriptor);
		parent::__construct($descriptor);
	}

	/**
	 * Realiza una conexión
	 *
	 * @param stdClass $descriptor
	 */
	public function connect($descriptor){
		$username = isset($descriptor->username) ? $descriptor->username : null;
		$password = isset($descriptor->password) ? $descriptor->password : null;
		$dsn = isset($descriptor->dsn) ? $descriptor->dsn : null;
		if(!isset($descriptor->driver)){
			throw new DbException('Debe indicar el driver JDBC a utilizar', 0, true, $this);
		}
		$driver = isset($descriptor->driver) ? $descriptor->driver : null;
		$dsn = 'jdbc:'.$this->_dbRBDM.':'.$dsn;
		try {
			$class = new JavaClass('java.lang.Class');
			$class->forName($driver);
			$driverManager = new JavaClass('java.sql.DriverManager');
			$this->_connection = $driverManager->getConnection($dsn, $username, $password);
			$this->_fetchMode = self::JDBC_FETCH_BOTH;
			if(isset($descriptor->autocommit)){
				if($descriptor->autocommit==true){
					$this->_autoCommit = true;
					$this->_connection->setAutoCommit(true);
				} else {
					$this->_autoCommit = false;
					$this->_connection->setAutoCommit(false);
				}
			}
			$this->initialize($descriptor);
		}
		catch(JavaException $e){
			throw new DbException($e->getMessage(), 0, true, $this);
		}
	}

	public function query($sqlStatement){
		$this->debug($sqlStatement);
		$this->log($sqlStatement, Logger::DEBUG);
		$ResultSet = new JavaClass('java.sql.ResultSet');
		$this->_statement = $this->_connection->createStatement($ResultSet->TYPE_SCROLL_INSENSITIVE, $ResultSet->CONCUR_READ_ONLY);
		$this->_resultSet = $this->_statement->executeQuery($sqlStatement);
		return $this->_resultSet;
	}

	public function numRows($resultSet=''){
		if(!$resultSet){
			$resultSet = $this->_resultSet;
		}
		$position = $resultSet->getRow();
		$resultSet->last();
		$numberRows = $resultSet->getRow();
		if($position>0){
			$resultSet->absolute($position);
		} else {
			$resultSet->beforeFirst();
		}
		$this->log('Filas Devueltas='.$numberRows, Logger::DEBUG);
		return $numberRows;
	}

	/**
	 * Devuelve un registro en el resulset activo
	 *
	 * @param java.sql.ResultSet $resultSet
	 * @return array|bool
	 */
	public function fetchArray($resultSet=''){
		if(!$resultSet){
			$resultSet = $this->_resultSet;
		}
		if($resultSet->next()){
			$metaData = $resultSet->getMetaData();
			$numberCols = $metaData->getColumnCount();
			if($this->_fetchMode==self::JDBC_FETCH_NUM){
				$row = array();
				$j = 0;
				for($i=1;$i<=$numberCols;++$i){
					$row[$j] = $resultSet->getString($i);
					++$j;
				}
				return $row;
			} else {
				if($this->_fetchMode==self::JDBC_FETCH_ASSOC){
					$row = array();
					for($i=1;$i<=$numberCols;++$i){
						if($metaData->getColumnType($i)==self::TYPE_DATE){
							$row[$metaData->getColumnName($i)] = $this->_formatDate($resultSet->getString($i));
						} else {
							$row[$metaData->getColumnName($i)] = $resultSet->getString($i);
						}
					}
					$row = array_change_key_case($row, CASE_LOWER);
					return $row;
				} else {
					$row = array();
					$j = 0;
					for($i=1;$i<=$numberCols;++$i){
						$row[$metaData->getColumnName($i)] = $resultSet->getString($i);
						$row[$j] = $resultSet->getString($i);
						++$j;
					}
					$row = array_change_key_case($row, CASE_LOWER);
					return $row;
				}
			}
		} else {
			return false;
		}
	}

	/**
	 * Mueve el puntero del resultado a la posición establecida
	 *
	 * @param int $number
	 * @param java.sql.ResultSet
	 */
	public function dataSeek($number, $resultSet){
		if(!$resultSet){
			$resultSet = $this->_resultSet;
		}
		if($number>0){
			$resultSet->absolute($number);
		} else {
			$resultSet->beforeFirst();
		}
	}

	/**
	 * Devuelve un string apartir de una fecha Java
	 *
	 * @param java.util.Date $date
	 * @return string
	 */
	private function _formatDate($date){
		$dateParts = explode(' ', $date);
		$dateP = explode('/', $dateParts[0]);
		return $dateP[2].'-'.sprintf('%02s', $dateP[0]).'-'.sprintf('%02s', $dateP[1]);
	}

	/**
	 * Inicia una transacción
	 *
	 * @return bool
	 */
	public function begin(){
		$this->_autoCommit = false;
		$this->_connection->setAutoCommit(false);
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
			return $this->_connection->rollback();
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
			$this->setUnderTransaction(false);
			$this->_autoCommit = true;
			return $this->_connection->commit();
		} else {
			return false;
		}
		return false;
	}

	/**
	 * Establece el modo en el que se deben devolver los registros
	 *
	 * @param int $fetchMode
	 */
	public function setFetchMode($fetchMode){
		if($fetchMode==DbBase::DB_NUM){
			$this->_fetchMode = self::JDBC_FETCH_NUM;
		} else {
			if($fetchMode==DbBase::DB_ASSOC){
				$this->_fetchMode = self::JDBC_FETCH_ASSOC;
			} else {
				$this->_fetchMode = self::JDBC_FETCH_BOTH;
			}
		}
	}

}
