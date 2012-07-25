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
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2006-2007 Giancarlo Corzo Vigil (www.antartec.com)
 * @license 	New BSD License
 * @version 	$Id: DbBase.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * @see DbBaseInterface
 */
require KEF_ABS_PATH.'Library/Kumbia/Db/Interface.php';

/**
 * DbBase
 *
 * Clase principal que deben heredar todas las clases driver de Kumbia
 * contiene metodos de debug, consulta y propiedades generales
 *
 * @category	Kumbia
 * @package		Db
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (C) 2006-2007 Giancarlo Corzo Vigil (www.antartec.com)
 * @license		New BSD License
 * @access		public
 */
class DbBase extends Object {

	/**
	 * Parámetros de conexion al gestor relacional
	 *
	 * @var stdClass
	 */
	protected $_descriptor;

	/**
	 * Nombre del adaptador utilizado
	 *
	 * @var int
	 */
	protected $_fetchMode;

	/**
	 * Indica si está en modo debug o no
	 *
	 * @var boolean
	 */
	private $_debug = false;

	/**
	 * Indica si se debe trazar todo el SQL ejecutado
	 *
	 * @var boolean
	 */
	private $_trace = false;

	/**
	 * Lista del SQL Trazado
	 *
	 * @var array
	 */
	private $_tracedSQL = array();

	/**
	 * Indica si debe loggear todo el SQL enviado mediante el objeto o no (también permite establecer el nombre del log)
	 *
	 * @var mixed
	 */
	private $_logger = false;

	/**
	 * Referencia al Objeto DbProfiler
	 *
	 * @var DbProfiler
	 */
	private $_profiler = null;

	/**
	 * Indica si la conexión a la base de datos se encuentra en una transacción
	 *
	 * @var boolean
	 */
	private $_underTransaction = false;

	/**
	 * Indica si la conexión es de solo lectura
	 *
	 * @var boolean
	 */
	private $_isReadOnly = false;

	/**
	 * Indica si el gestor está en modo autocommit
	 *
	 * @var boolean
	 */
	protected $_autoCommit = true;

	/**
	 * Resource de la conexión de bajo nivel
	 *
	 * @var resource
	 */
	protected $_idConnection = null;

	/**
	 * Ultimo recurso de una Query
	 *
	 * @var resource
	 */
	protected $_lastResultQuery;

	/**
	 * Última sentencia SQL enviada a Oracle
	 *
	 * @var string
	 */
	protected $_lastQuery;

	/**
	 * Ultimo error generado por Oracle
	 *
	 * @var string
	 */
	protected $_lastError;

	/**
	 * Resultado de Array Asociativo
	 *
	 */
	const DB_ASSOC = 1;

	/**
	 * Resultado de Array Asociativo y Numérico
	 *
	 */
	const DB_BOTH = 2;

	/**
	 * Resultado de Array Numérico
	 *
	 */
	const DB_NUM = 3;

	/**
	 * Constructor padre de los adaptadores de RBDMs
	 *
	 * @param stdClass $descriptor
	 */
	protected function __construct($descriptor){
		if(isset($descriptor->tracing)){
			if($descriptor->tracing==true){
				$this->setTracing(true);
			}
		}
		if(isset($descriptor->logging)){
			if($descriptor->logging){
				$this->setLogger($descriptor->logging);
			}
		}
		if(isset($descriptor->profiling)){
			if($descriptor->profiling){
				$this->setProfiling(true);
			}
		}
		$this->_descriptor = $descriptor;
	}

	/**
	 * Ejecuta tareas trás crear la conexión
	 *
	 * @access protected
	 */
	public function connect($descriptor=''){
		#if[no-db-plugins]
		PluginManager::notifyFrom('Db', 'onCreateConnection', $this);
		#endif
	}

	/**
	 * Devuelve los campos de una tabla
	 *
	 * @access	public
	 * @param	string $tableName
	 * @return	array
	 */
	public function getFieldsFromTable($tableName){
		$description = $this->describeTable($tableName);
		$fields = array();
		foreach($description as $field){
			$fields[] = $field['Field'];
		}
		return $fields;
	}

	/**
	 * Ejecuta las tareas de Profile, Timeout, Traza, Debug y Logueo de SQL en la conexión
	 *
	 * @access	protected
	 * @param	string $sqlStatement
	 */
	protected function beforeQuery($sqlStatement){
		if($this->_debug==true){
			$this->debug($sqlStatement);
		}
		if($this->_logger){
			$this->log('['.$this->getConnectionId().'] '.$sqlStatement, Logger::DEBUG);
		}
		if($this->_trace==true){
			$this->trace($sqlStatement);
		}
		if($this->_profiler){
			$this->_profiler->startProfile($sqlStatement);
		}
		#if[no-db-plugins]
		PluginManager::notifyFrom('Db', 'beforeQuery', $sqlStatement);
		#endif
	}

	/**
	 * Ejecuta las tareas de Profile en la conexion
	 *
	 * @access	protected
	 * @param	string $sqlStatement
	 */
	protected function afterQuery($sqlStatement){
		if($this->_profiler){
			$this->_profiler->stopProfile($sqlStatement);
		}
		#if[no-db-plugins]
		PluginManager::notifyFrom('Db', 'afterQuery', $sqlStatement);
		#endif
	}

	/**
	 * Ejecuta tareas antes de cerrar la conexión
	 *
	 * @access protected
	 */
	protected function close(){
		#if[no-db-plugins]
		PluginManager::notifyFrom('Db', 'onCloseConnection', $this);
		#endif
	}

	/**
	 * Hace un SELECT de una forma más corta, listo para usar en un foreach
	 *
	 * @access	public
	 * @param	string $table
	 * @param	string $where
	 * @param	string $fields
	 * @param	string $orderBy
	 * @return	array
	 */
	public function find($table, $where='', $fields='*', $orderBy='1'){
		if($where!=''){
			$where = 'WHERE '.$where;
		}
		$q = $this->query('SELECT '.$fields.' FROM '.$table.' WHERE '.$where.' ORDER BY '.$orderBy);
		$results = array();
		while($row = $this->fetchArray($q)){
			$results[] = $row;
		}
		return $results;
	}

	/**
	 * Realiza un query SQL y devuelve un array con los array resultados en forma
	 * indexada por números y asociativamente
	 *
	 * @access	public
	 * @param	string $sqlQuery
	 * @param	integer $type
	 * @return	array
	 */
	public function inQuery($sqlQuery){
		$resultQuery = $this->query($sqlQuery);
		$results = array();
		if($resultQuery!=false){
			while($row = $this->fetchArray($resultQuery)){
				$results[] = $row;
			}
		}
		return $results;
	}

	/**
	 * Realiza un query SQL y devuelve un array con los array resultados en forma
	 * indexada por números y asociativamente (Alias para inQuery)
	 *
	 * @param	string $sqlQuery
	 * @param	integer $type
	 * @return	array
	 */
	public function fetchAll($sqlQuery){
		return $this->inQuery($sqlQuery);
	}

	/**
	 * Realiza un query SQL y devuelve un array con los array resultados en forma
	 * indexada asociativamente
	 *
	 * @param	string $sqlQuery
	 * @param	integer $type
	 * @return	array
	 */
	public function inQueryAssoc($sqlQuery){
		$q = $this->query($sqlQuery);
		$results = array();
		if($q){
			$this->setFetchMode(self::DB_ASSOC);
			while($row = $this->fetchArray($q)){
				$results[] = $row;
			}
		}
		return $results;
	}

	/**
	 * Realiza un query SQL y devuelve un array con los array resultados en forma
	 * numérica
	 *
	 * @param	string $sqlQuery
	 * @param	integer $type
	 * @return	array
	 */
	public function inQueryNum($sqlQuery){
		$resultQuery = $this->query($sqlQuery);
		$results = array();
		if($resultQuery){
			$this->setFetchMode(self::DB_NUM);
			while($row = $this->fetchArray($q)){
				$results[] = $row;
			}
		}
		return $results;
	}

	/**
	 * Devuelve un array del resultado de un SELECT de un solo registro
	 *
	 * @access	public
	 * @param	string $sqlQuery
	 * @param	integer $fetchType
	 * @return	array
	 */
	public function fetchOne($sqlQuery){
		$resultQuery = $this->query($sqlQuery);
		if($resultQuery){
			#if[compile-time]
			if($this->numRows($resultQuery)>1){
				Facility::issueEvent('La sentencia SQL: "'.$sqlQuery.'" retornó más de un registro cuando se esperaba uno sola', Facility::I_WARNING);
			}
			#endif
			return $this->fetchArray($resultQuery);
		} else {
			return array();
		}
	}

	/**
	 * Realiza una inserción
	 *
	 * @access	public
	 * @param	string $table
	 * @param	array $values
	 * @param	array $fields
	 * @param	boolean $automaticQuotes
	 * @return	boolean
	 */
	public function insert($table, $values, $fields=null, $automaticQuotes=false){
		$insertSQL = '';
		if($this->isReadOnly()==true){
			throw new DbException('No se puede efectuar la operación. La transacción es de solo lectura', 0, true, $this);
		}
		#if[compile-time]
		if(is_array($values)==true){
			if(count($values)==0){
				throw new DbException('Imposible realizar inserción en '.$table.' sin datos');
			} else {
			#endif
				if($automaticQuotes==true){
					foreach($values as $key => $value){
						if(is_object($value)&&($value instanceof DbRawValue)){
							$values[$key] = addslashes($value->getValue());
						} else {
							$values[$key] = "'".addslashes($value)."'";
						}
					}
				}
			#if[compile-time]
			}
			#endif
			if(is_array($fields)==true){
				$insertSQL = 'INSERT INTO '.$table.' ('.join(', ', $fields).') VALUES ('.join(', ', $values).')';
			} else {
				$insertSQL = 'INSERT INTO '.$table.' VALUES ('.join(', ', $values).')';
			}
			return $this->query($insertSQL);
		#if[compile-time]
		} else{
			throw new DbException('El segundo parámetro para insert no es un Array', 0, true, $this);
		}
		#endif
	}

	/**
	 * Actualiza registros en una tabla
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
		$updateSql = 'UPDATE '.$table.' SET ';
		#if[compile-time]
		if(count($fields)!=count($values)){
			throw new DbException('Los número de valores a actualizar no es el mismo de los campos', 0, true, $this);
		}
		#endif
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
			$i++;
		}
		$updateSql.= join(', ', $updateValues);
		if($whereCondition!=null){
			$updateSql.= ' WHERE '.$whereCondition;
		}
		return $this->query($updateSql);
	}

	/**
	 * Borra registros de una tabla
	 *
	 * @access	public
	 * @param	string $table
	 * @param	string $whereCondition
	 * @return	boolean
	 */
	public function delete($table, $whereCondition=''){
		if($this->isReadOnly()==true){
			throw new DbException("No se puede efectuar la operación. La transacción es de solo lectura", 0, true, $this);
		}
		if(trim($whereCondition)!=""){
			return $this->query('DELETE FROM '.$table.' WHERE '.$whereCondition);
		} else {
			return $this->query('DELETE FROM '.$table);
		}
	}

	/**
	 * Inicia una transacción si es posible
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function begin(){
		$this->_autoCommit = false;
		$this->_underTransaction = true;
		return $this->query('BEGIN');
	}

	/**
	 * Cancela una transacción si es posible
	 *
	 * @access public
	 * @return boolean
	 */
	public function rollback(){
		if($this->_underTransaction==true){
			$this->_underTransaction = false;
			$this->_autoCommit = true;
			return $this->query('ROLLBACK');
		} else {
			throw new DbException("No hay una transacción activa en la conexión al gestor relacional", 0, true, $this);
		}
	}

	/**
	 * Hace commit sobre una transacción si es posible
	 *
	 * @access public
	 * @return boolean
	 */
	public function commit(){
		if($this->_underTransaction==true){
			$this->_underTransaction = false;
			$this->_autoCommit = true;
			return $this->query('COMMIT');
		} else {
			throw new DbException("No hay una transacción activa en la conexión al gestor relacional", 0, true, $this);
		}
	}

	/**
	 * Agrega comillas o simples segun soporte el RBDM
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 * @static
	 */
	static public function addQuotes($value){
		return "'".addslashes($value)."'";
	}

	/**
	 * Loggea las operaciones sobre la base de datos si estan habilitadas
	 *
	 * @access	protected
	 * @param	string $sqlStatement
	 * @param	string $type
	 */
	protected function log($sqlStatement, $type){
		if($this->_logger){
			if(is_bool($this->_logger)&&$this->_logger==true){
				$this->_logger = new Logger('File', 'db'.date('Ymd').'.txt');
				if(isset($this->_descriptor->log_format)){
					$this->_logger->setFormat($this->_descriptor->log_format);
				}
			} else {
				if(is_object($this->_logger)){
					$this->_logger = $this->_logger;
				} else {
					if(is_string($this->_logger)){
						$this->_logger = new Logger('File', $this->_logger);
						if(isset($this->_descriptor->log_format)){
							$this->_logger->setFormat($this->_descriptor->log_format);
						}
					} else {
						return false;
					}
				}
			}
			$this->_logger->log($sqlStatement, $type);
		}
	}

	/**
	 * Almacena una traza interna de todo el SQL en una conexión
	 *
	 * @access	protected
	 * @param	string $sqlStatement
	 */
	protected function trace($sqlStatement){
		if($this->_trace==true){
			$this->_tracedSQL[] = $sqlStatement;
		}
	}

	/**
	 * Devuelve el vector del SQL trazado
	 *
	 * @access public
	 * @return array
	 */
	public function getTracedSQL(){
		return $this->_tracedSQL;
	}

	/**
	 * Muestra mensajes de debug en pantalla si está habilitado
	 *
	 * @access	protected
	 * @param	string $sqlStatement
	 */
	protected function debug($sqlStatement){
		if($this->_debug==true){
			Flash::notice($this->getConnectionId(true).': '.$sqlStatement);
		}
	}

	/**
	 * Realiza una conexión directa al motor de base de datos
	 *
	 * @access	public
	 * @param	boolean $renovate
	 * @param	boolean $newConnection
	 * @return	DbBase
	 * @static
	 */
	public static function rawConnect($newConnection=false, $renovate=false){
		return DbPool::getConnection($newConnection, $renovate);
	}

	/**
	 * Permite especificar si esta en modo debug o no
	 *
	 * @param boolean $debug
	 */
	public function setDebug($debug){
		$this->_debug = $debug;
	}

	/**
	 * Permite especificar el logger del Adaptador
	 *
	 * @param boolean $logger
	 */
	public function setLogger($logger){
		$this->_logger = $logger;
	}

	/**
	 * Devuelve logger interno del adaptador
	 *
	 * @return Logger
	 */
	public function getLogger(){
		return $this->_logger;
	}

	/**
	 * Alias de setLogger
	 *
	 * @param boolean $logging
	 */
	public function setLogging($logging){
		$this->_logger = $logging;
	}

	/**
	 * Establece si va a realizar Profile en la conexión
	 *
	 * @param DbProfiler|boolean $profiler
	 */
	public function setProfiling($profiler){
		if(is_object($profiler)){
			$this->_profiler = $profiler;
		} else {
			if($profiler){
				$this->_profiler = new DbProfiler();
			}
		}
	}

	/**
	 * Establece si se debe trazar el SQL enviado en la conexión activa
	 *
	 * @param boolean $trace
	 */
	public function setTracing($trace){
		$this->_trace = $trace;
	}

	/**
	 * Indica si la conexion se le esta haciendo traza
	 *
	 * @param boolean $trace
	 */
	public function isTracing(){
		return $this->_trace;
	}

	/**
	 * Indica si la conexión se encuentra en una transacción
	 *
	 * @access public
	 * @return boolean
	 */
	public function isUnderTransaction(){
		return $this->_underTransaction;
	}

	/**
	 * Permite establecer si se encuentra bajo transacción
	 *
	 * @param boolean $underTransaction
	 */
	protected function setUnderTransaction($underTransaction){
		$this->_underTransaction = $underTransaction;
	}

	/**
	 * Indica si la conexión tiene auto-commit habilitado
	 *
	 * @access public
	 * @return boolean
	 */
	public function getHaveAutoCommit(){
		return $this->_autoCommit;
	}

	/**
	 * Permite establecer si la conexión es de solo lectura
	 *
	 * @access public
	 * @param boolean $readOnly
	 */
	public function setReadOnly($readOnly){
		$this->_isReadOnly = $readOnly;
	}

	/**
	 * Indica si la conexión es de solo lectura
	 *
	 * @access public
	 * @return boolean
	 */
	public function isReadOnly(){
		return $this->_isReadOnly;
	}

	/**
	 * Indica si la conexión esta bajo debug
	 *
	 * @access public
	 * @return boolean
	 */
	public function isDebugged(){
		return $this->_debug;
	}

	/**
	 * Ejecuta una sentencia SQL en el gestor relacional
	 *
	 * @param	string $sqlStatement
	 * @return	boolean
	 */
	public function query($sqlStatement){
		return false;
	}

	/**
	 * Devuelve el nombre de la base de datos
	 *
	 * @return	string
	 */
	public function getDatabaseName(){
		if(isset($this->_descriptor->name)){
			return $this->_descriptor->name;
		} else {
			return '';
		}
	}


	/**
	 * Devuelve el nombre del esquema por defecto
	 *
	 * @return	string
	 */
	public function getDefaultSchema(){
		if(isset($this->_descriptor->schema)){
			return $this->_descriptor->schema;
		} else {
			if(isset($this->_descriptor->name)){
				return $this->_descriptor->name;
			} else {
				return '';
			}
		}
	}

	/**
	 * Devuelve el nombre del usuario de la base de datos ó propietario del schema
	 *
	 * @return	string
	 */
	public function getUsername(){
		if(isset($this->_descriptor->username)){
			return $this->_descriptor->username;
		} else {
			return '';
		}
	}

	/**
	 * Devuelve el nombre del host ó dirección IP del servidor del RBDM
	 *
	 * @access 	public
	 * @return	string
	 */
	public function getHostName(){
		if(isset($this->_descriptor->host)){
			return $this->_descriptor->host;
		} else {
			return '';
		}
	}

	/**
	 * Devuelve el descriptor de la base de datos
	 *
	 * @return stdClass
	 */
	public function getDescriptor(){
		return $this->_descriptor;
	}

	/**
	 * Devuelve el id de conexión generado por el driver
	 *
	 * @access 	public
	 * @param	boolean $asString
	 * @return	resource
	 */
	public function getConnectionId($asString=false){
		return $this->_idConnection;
	}

	/**
	 * Devuelve el último cursor generado por el driver
	 *
	 * @return resource
	 */
	public function getLastResultQuery(){
		return $this->_lastResultQuery;
	}

	/**
	 * Devuelve la última sentencia SQL ejecutada
	 *
	 * @return string
	 */
	public function getLastQuery(){
		return $this->_lastQuery;
	}

	/**
	 * Establece el timeout de la conexión
	 *
	 * @param int $timeout
	 */
	public function setTimeout($timeout){

	}

}
