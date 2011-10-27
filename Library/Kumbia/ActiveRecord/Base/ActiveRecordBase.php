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
 * @package		ActiveRecord
 * @subpackage	ActiveRecordBase
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2007-2007 Roger Jose Padilla Camacho (rogerjose81 at gmail.com)
 * @copyright	Copyright (c) 2007-2008 Emilio Rafael Silveira Tovar (emilio.rst at gmail.com)
 * @copyright	Copyright (c) 2007-2008 Deivinson Tejeda Brito (deivinsontejeda at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ActiveRecordBase.php,v a434b34d7989 2011/10/26 22:23:04 andres $
 */

/**
 * @see ActiveRecordResultInterface
 */
require KEF_ABS_PATH.'Library/Kumbia/ActiveRecord/Interface.php';

/**
 * ActiveRecordBase
 *
 * Este componente es el encargado de realizar el mapeo objeto-relacional y
 * de encargarse de los modelos en la arquitectura MVC de las aplicaciones.
 * El concepto de ORM se refiere a una técnica de mapear las relaciones de
 * una base de datos a objetos nativos del lenguaje utilizado
 * (PHP en este caso), de tal forma que se pueda interactuar con ellos
 * en forma más natural.
 *
 * Los objetivos de este componente van más allá de mapear tablas y
 * convertirlas en clases (incluyendo tipos de datos, constraints,
 * lógica de dominio, etc.) ó de convertir registros en objetos.
 *
 * La idea es reducir el mantenimiento de la interacción con las bases
 * de datos en gran medida mediante varias capas de abstracción, esto
 * incluye reducir el uso de SQL ó lidiar con conexiones y sintaxis
 * programacional de bajo nivel.
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	ActiveRecordBase
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (C) 2007-2007 Roger Jose Padilla Camacho(rogerjose81 at gmail.com)
 * @copyright	Copyright (C) 2007-2008 Emilio Rafael Silveira Tovar (emilio.rst@gmail.com)
 * @copyright	Copyright (c) 2007-2008 Deivinson Tejeda Brito (deivinsontejeda at gmail.com)
 * @license		New BSD License
 * @access		public
 * @abstract
 */
abstract class ActiveRecordBase extends Object
#if[compile-time]
	implements ActiveRecordResultInterface, EntityInterface
#endif
	{

	/**
	 * Resource de conexión a la base de datos
	 *
	 * @var DbBase
	 */
	protected $_db = '';

	/**
	 * Schema donde esta la tabla
	 *
	 * @var string
	 */
	protected $_schema = '';

	/**
	 * Tabla utilizada para realizar el mapeo
	 *
	 * @var string
	 */
	protected $_source = '';

	/**
	 * Numero de resultados generados en la última consulta
	 *
	 * @var integer
	 */
	protected $_count = 0;

	/**
	 * Indica si la clase corresponde a un mapeo de una vista
	 * en la base de datos
	 *
	 * @var boolean
	 */
	protected $isView = false;

	/**
	 * Indica si el modelo esta en modo debug
	 *
	 * @var boolean
	 */
	private $_debug = false;

	/**
	 * Indica si se logearan los mensajes generados por la clase
	 *
	 * @var mixed
	 */
	private $_logger = false;

	/**
	 * Variable para crear una condicion basada en los
	 * valores del where
	 *
	 * @var string
	 */
	private $_wherePk = '';

	/**
	 * Puntero del objeto en la transacción
	 *
	 * @var int
	 */
	private $_dependencyPointer;

	/**
	 * Indica si ya se han obtenido los metadatos del Modelo
	 *
	 * @var boolean
	 * @access protected
	 */
	protected $_dumped = false;

	/**
	 * Indica si hay bloqueo sobre los warnings cuando una propiedad
	 * del modelo no esta definida
	 *
	 * @var	boolean
	 * @access protected
	 */
	protected $_dumpLock = false;

	/**
	 * Lista de mensajes de error
	 *
	 * @var array
	 * @access protected
	 */
	protected $_errorMessages = '';

	/**
	 * Indica la última operación realizada en el modelo
	 *
	 * @var int
	 */
	protected $_operationMade;

	/**
	 * Observers del objeto
	 *
	 * @var array
	 */
	protected $_observers = array();

	/**
	 * Indica si la entidad ya existe y/o obliga a comprobarlo
	 *
	 * @var boolean
	 */
	protected $_forceExists = false;

	/**
	 * Indica si el modelo debe usar la conexión predeterminada
	 *
	 * @var boolean
	 */
	protected $_defaultConnection = true;

	/**
	 * Nombre de la conexión que debe usar el modelo
	 *
	 * @var string
	 */
	protected $_connectionName;

	/**
	 * Indica si el UPDATE debe actualizar solo los campos que cambiaron
	 *
	 * @var boolean
	 */
	private static $_dynamicUpdate = false;

	/**
	 * Indica si el INSERT debe insertar solo los campos que contienen valores no-nulos
	 *
	 * @var boolean
	 */
	private static $_dynamicInsert = false;

	/**
	 * Indica si se deben deshabilitar los eventos
	 *
	 * @var boolean
	 */
	private static $_disableEvents = false;

	/**
	 * Indica si se deben refrescar de la base de datos los registros despues de insertar/actualizar
	 *
	 * @var boolean
	 */
	private static $_refreshPersistance = true;

	/**
	 * Indica que la última operación fue una inserción
	 *
	 */
	const OP_CREATE = 1;

	/**
	 * Indica que la última operación fue una actualización
	 *
	 */
	const OP_UPDATE = 2;

	/**
	 * Indica que la última operación fue una eliminación
	 *
	 */
	const OP_DELETE = 3;

	/**
	 * Constructor del Modelo
	 *
	 * @access public
	 */
	public function __construct(){
		if(Facility::getFacility()==Facility::USER_LEVEL){
			if($this->_source==''){
				$this->_source = EntityManager::getSourceName(get_class($this));
			}
			if(method_exists($this, 'initialize')){
				$this->initialize();
			}
			$numberArguments = func_num_args();
			if($numberArguments>0){
				$params = func_get_args();
				if(!isset($params[0])||!is_array($params[0])){
					$params = Utils::getParams($params, $numberArguments);
				}
				$this->dumpResultSelf($params);
			}
		} else {
			if(method_exists($this, 'initialize')){
				$this->initialize();
			}
		}
	}

	/**
	 * Obtiene el nombre de la relacion en el RDBM a partir del nombre de la clase
	 *
	 * @access private
	 */
	private function _findModelName(){
		if($this->_source==''){
            $this->_source = Utils::uncamelize(get_class($this));
        }
        if($this->_source==''){
			$this->_source = get_class($this);
		}
	}

	/**
	 * Establece públicamente el $source de la tabla
	 *
	 * @param	string $source
	 * @access	public
	 */
	public function setSource($source){
		$this->_source = $source;
	}

	/**
	 * Devuelve el source actual
	 *
	 * @access	public
	 * @return	string
	 */
	public function getSource(){
		return $this->_source;
	}

	/**
	 * Establece el schema del source
	 *
	 * @param string $schema
	 */
	public function setSchema($schema){
		#if[compile-time]
		CoreType::assertString($schema);
		#endif
		if($schema!=$this->_schema){
			$this->_dumped = false;
		}
		$this->_schema = $schema;
	}

	/**
	 * Devuelve el schema donde está la tabla
	 *
	 * @param	string $schema
	 * @return	string
	 */
	public function getSchema(){
		return $this->_schema;
	}

	/**
     * Establece la conexión con la que trabajará el modelo
     *
     * @access	public
     * @param	string $mode
     */
    public function setConnection($db){
        $this->_db = $db;
        if($this->_debug==true){
			$this->_db->setDebug($this->_debug);
		}
		if($this->_logger!=false){
			$this->_db->setLogger($this->_logger);
		}
    }

    /**
     * Establece el nombre de la conexión que debe usarse en el modelo
     *
     * @param string $name
     */
    protected function setConnectionName($name){
		$this->_defaultConnection = false;
		$this->_connectionName = $name;
    }

    /**
     * Devuelve el conteo del último Find ejecutado en el modelo
     *
     * @access	public
     * @return	integer
     */
    public function getCount(){
    	return $this->_count;
    }

	/**
	 * Pregunta si el ActiveRecord ya ha consultado la informacion de metadatos
	 * de la base de datos o del registro persistente
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function isDumped(){
		return $this->_dumped;
	}

	/**
	 * Se conecta a la base de datos y descarga los meta-datos si es necesario
	 *
	 * @param	boolean $newConnection
	 * @access	protected
	 */
	protected function _connect($newConnection=false){
		if($newConnection||$this->_db===''){
			if(TransactionManager::isAutomatic()==false){
				if($this->_defaultConnection==true){
					$this->_db = DbPool::getConnection($newConnection);
				} else {
					$this->_db = DbLoader::factoryFromName($this->_connectionName);
				}
			} else {
				$this->_db = TransactionManager::getUserTransaction()->getConnection();
			}
			if($this->_debug==true){
				$this->_db->setDebug($this->_debug);
			}
			if($this->_logger!=false){
				$this->_db->setLogger($this->_logger);
			}
		}
		$this->dump();
	}

	/**
	 * Cargar los metadatos de la tabla
	 *
	 * @access public
	 */
	public function dumpModel(){
		$this->_connect();
	}

	/**
	 * Verifica si la tabla definida en $this->_source existe
	 * en la base de datos y la vuelca en dumpInfo
	 *
	 * @access	protected
	 * @return	boolean
	 * @throws	ActiveRecordException
	 */
	protected function dump(){
		if($this->_dumped===true){
			return false;
		}
		if($this->_source==''){
			$this->_findModelName();
			if($this->_source==''){
				return false;
			}
		}
		$table = $this->_source;
		$schema = $this->_schema;
		if(!ActiveRecordMetaData::existsMetaData($table, $schema)) {
			$this->_dumped = true;
			if($this->isView==true){
				$exists = $this->_db->viewExists($table, $schema);
			} else {
				$exists = $this->_db->tableExists($table, $schema);
			}
			if($exists==true){
				$this->_dumpInfo($table, $schema);
			} else {
				if($schema!=''){
					throw new ActiveRecordException('No existe la entidad "'.$schema.'"."'.$table.'" en el gestor relacional: '.get_class($this));
				} else {
					throw new ActiveRecordException('No existe la entidad "'.$table.'" en el gestor relacional: '.get_class($this));
				}
				return false;
			}
		} else {
			if($this->isDumped()==false){
				$this->_dumped = true;
				$this->_dumpInfo($table, $schema);
			}
		}
		$this->_dumpLock = true;
		foreach(ActiveRecordMetaData::getAttributes($table, $schema) as $field){
			if(!isset($this->$field)){
				$this->$field = '';
			}
		}
		$this->_dumpLock = false;
		return true;
	}

	/**
	 * Establece el bloqueo de excepciones
	 *
	 * @param boolean $dumplock
	 */
	protected function _setDumpLock($dumplock){
		$this->_dumpLock = $dumplock;
	}

	/**
	 * Obtiene el estado del dumpLock
	 *
	 * @return boolean
	 */
	protected function _getDumpLock(){
		return $this->_dumpLock;
	}

	/**
	 * Volca la información de la tabla ó vista $table en la base de datos
	 * para crear los atributos y meta-data del ActiveRecord
	 *
	 * @access	protected
	 * @param	string $tablename
	 * @param	string $schemaName
	 * @return	boolean
	 */
	protected function _dumpInfo($tableName, $schemaName=''){
		$this->_dumpLock = true;
		if(!ActiveRecordMetaData::existsMetaData($tableName, $schemaName)){
			if($this->isView==true){
				$metaData = $this->_db->describeView($tableName, $schemaName);
			} else {
				$metaData = $this->_db->describeTable($tableName, $schemaName);
			}
			ActiveRecordMetaData::dumpMetaData($tableName, $schemaName, $metaData);
		}
		$fields = ActiveRecordMetaData::getAttributes($tableName, $schemaName);
		#if[compile-time]
		if(count($fields)==0){
			if($schemaName){
				$tablename = '"'.$tableName.'"."'.$schemaName.'"';
			} else {
				$tableName = '"'.$tableName.'"';
			}
			throw new ActiveRecordException('No se pudo obtener los meta-datos del source: '.$tableName);
		}
		#endif
		foreach($fields as $field){
			if(!isset($this->$field)){
				$this->$field = '';
			}
		}
		$this->_dumpLock = false;
		return true;
	}

	/**
	 * Devuelve la información de la tabla ó vista $table en la base de datos
	 * para crear los atributos y meta-data del ActiveRecord
	 *
	 * @access	protected
	 * @param	string $tablename
	 * @param	string $schemaName
	 * @return	boolean
	 */
	static function getDumpInfo($tableName, $schemaName=''){
		if(!ActiveRecordMetaData::existsMetaData($tableName, $schemaName)){
			if($this->isView==true){
				$metaData = $this->_db->describeView($tableName, $schemaName);
			} else {
				$metaData = $this->_db->describeTable($tableName, $schemaName);
			}
			ActiveRecordMetaData::dumpMetaData($tableName, $schemaName, $metaData);
		}
		$fields = ActiveRecordMetaData::getAttributes($tableName, $schemaName);

		return $fields;
	}

	/**
	 * Inicializa los valores
	 *
	 * @access public
	 */
	public function clear(){
		$this->_connect();
		$fields = $this->_getAttributes();
		foreach($fields as $field){
			$this->$field = null;
		}
	}

	/**
	 * Elimina la información de cache del objeto y hace que sea cargada en la proxima operación
	 *
	 * @access public
	 */
	public function resetMetaData(){
		$this->_dumped = false;
		if($this->isDumped()==false){
			$this->dump();
		}
	}

	/**
	 * Permite especificar si esta en modo debug o no
	 *
	 * @access	public
	 * @param	boolean $debug
	 */
	public function setDebug($debug){
		#if[compile-time]
		CoreType::assertBool($debug);
		#endif
		$this->_debug = $debug;
		if($debug==true){
			$this->_connect();
			$this->_db->setDebug($this->_debug);
		}
	}

	/**
	 * Permite especificar el logger del Modelo
	 *
	 * @access	public
	 * @param	boolean $logger
	 */
	public function setLogger($logger){
		$this->_logger = $logger;
	}

	/**
	 * Establece el administrador de Transaciones del Modelo
	 *
	 * @access	public
	 * @param	ActiveRecordTransaction $transaction
	 * @throws	ActiveRecordException
	 * @return  ActiveRecordBase
	 */
	public function setTransaction(ActiveRecordTransaction $transaction){
		#if[compile-time]
		if($transaction->getConnection()->isUnderTransaction()==false){
			throw new ActiveRecordException('La transacción no se ha iniciado');
		}
		#endif
		/*if($transaction->isManaged()==true){
			$this->_dependencyPointer = $transaction->attachDependency($this->_dependencyPointer, $this);
		}*/
		$this->_db = $transaction->getConnection();
		return $this;
	}

	/**
	 * Cambia la conexión transaccional por la conexión predeterminada
	 *
	 * @access public
	 */
	public function detachTransaction(){
		$this->_db = DbPool::getConnection();
	}

	/**
	 * Devuelve el objeto interno de conexión a la base de datos
	 *
	 * @access	public
	 * @param 	boolean $shouldConnect
	 * @return	DbBase
	 */
	public function getConnection($shouldConnect=true){
		if($shouldConnect==true){
			if(!$this->_db){
				$this->_connect();
			}
		}
		return $this->_db;
	}

	/**
	 * Find all records in this table using a SQL Statement
	 *
	 * @access	public
	 * @param	string $sqlQuery
	 * @return	ActiveRecordResultset
	 */
	public function findAllBySql($sqlQuery){
		$this->_connect();
		$resultSet = $this->_db->query($sqlQuery);
		if($this->_db->numRows($resultSet)>0){
			return new ActiveRecordResultset($this, $resultSet, $sqlQuery);
		} else {
			return new ActiveRecordResultset($this, false, $sqlQuery);
		}
	}

	/**
	 * Find a record in this table using a SQL Statement
	 *
	 * @access		ublic
	 * @param 		string $sqlQuery
	 * @return		ActiveRecordBase
	 * @deprecated
	 */
	public function findBySql($sqlQuery){
		$this->_connect();
		$this->_db->setFetchMode(DbBase::DB_ASSOC);
		$row = $this->_db->fetchOne($sqlQuery);
		if($row!==false){
			$this->dumpResultSelf($row);
			return $this->dumpResult($row);
		} else {
			return false;
		}
	}

	/**
	 * Consulta todos los registros en una entidad de forma estática
	 *
	 * @param 	string $params
	 * @return	ActiveRecordResultset
	 */
	public static function findAll($params=''){
		$activeModel = EntityManager::getEntityInstance(get_called_class());
		$arguments = func_get_args();
		return call_user_func_array(array($activeModel, 'find'), $arguments);
	}

	/**
	 * Consulta los registros dadas las condiciones y devuelve solamente el primero
	 *
	 * @param 	string $params
	 * @return	ActiveRecordBase
	 */
	public static function findOne($params=''){
		$activeModel = EntityManager::getEntityInstance(get_called_class());
		$arguments = func_get_args();
		return call_user_func_array(array($activeModel, 'findFirst'), $arguments);
	}

	/**
	 * Execute a SQL Query Statement directly
	 *
	 * @access		public
	 * @param		string $sqlQuery
	 * @return		DbResource
	 * @deprecated
	 */
	public function sql($sqlQuery){
		$this->_connect();
		return $this->_db->query($sqlQuery);
	}

	/**
	 * Devuelve el primer registro según las condiciones
	 *
	 * @access	public
	 * @param	mixed $params
	 * @return	ActiveRecordBase
	 */
	public function findFirst($params=''){
		$this->_connect();
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		$select = 'SELECT ';
		if(isset($params['columns'])){
			$this->clear();
			$select.= $params['columns'];
		} else {
			$select.= join(', ', $this->_getAttributes());
		}
		if($this->_schema!=''){
			$select.= ' FROM '.$this->_schema.'.'.$this->_source;
		} else {
			$select.= ' FROM '.$this->_source;
		}
		if(!isset($params['limit'])){
			$params['limit'] = 1;
		}
		$select = $this->convertParamsToSql($select, $params);
		$resp = false;
		try {
			$this->_db->setFetchmode(DbBase::DB_ASSOC);
			$result = $this->_db->fetchOne($select);
			if($result){
				$this->dumpResultSelf($result);
				$resp = $this->dumpResult($result);
			}
			$this->_db->setFetchmode(DbBase::DB_BOTH);
		}
		catch(Exception $e){
			$this->exceptions($e);
		}
		return $resp;
	}

	/**
	 * Devuelve el último registro según las condiciones
	 *
	 * @access	public
	 * @param	mixed $params
	 * @return	ActiveRecordBase
	 */
	public function findLast($params=''){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params['order'])){
			$params['order'] = '1 DESC';
		}
		return $this->findFirst($params);
	}

	/**
	 * Crea una sentencia SQL
	 *
	 * @access	private
	 * @param	array $params
	 * @return	string
	 */
	private function _createSQLSelect(array $params){
		$select = 'SELECT ';
		if(isset($params['columns'])){
			$this->clear();
			$select.= $params['columns'];
		} else {
			$select.= join(', ', $this->_getAttributes());
		}
		if($this->_schema){
			$select.= ' FROM '.$this->_schema.'.'.$this->_source;
		} else {
			$select.= ' FROM '.$this->_source;
		}
		$return = 'n';
		$primaryKeys = $this->_getPrimaryKeyAttributes();
		if(isset($params['conditions'])&&$params['conditions']){
			$select.= ' WHERE '.$params['conditions'].' ';
		} else {
			if(!isset($primaryKeys[0])){
				if($this->isView==true){
					$primaryKeys[0] = 'id';
				}
			}
			if(isset($params[0])){
				if(is_numeric($params[0])){
					if(isset($primaryKeys[0])){
						$params['conditions'] = $primaryKeys[0].' = '.$this->_db->addQuotes($params[0]);
						$return = '1';
					} else {
						throw new ActiveRecordException('No se ha definido una llave primaria para este objeto');
					}
				} else {
					if($params[0]===''){
						if(isset($primaryKeys[0])){
							$params['conditions'] = $primaryKeys[0]." = ''";
						} else {
							throw new ActiveRecordException('No se ha definido una llave primaria para este objeto');
						}
					} else {
						$params['conditions'] = $params[0];
					}
					$return = 'n';
				}
			}
			if(isset($params['conditions'])){
				$select.= ' WHERE '.$params['conditions'];
			}
		}
		if(isset($params['group'])&&$params['group']) {
			$select.= ' GROUP BY '.$params['group'];
		}
		if(isset($params['order'])&&$params['order']) {
			$select.= ' ORDER BY '.$params['order'];
		}
		if(isset($params['limit'])&&$params['limit']) {
			$select = $this->_limit($select, $params['limit']);
		}
		if(isset($params['for_update'])&&$params['for_update']==true){
			$select = $this->_db->forUpdate($select);
		}
		if(isset($params['shared_lock'])&&$params['shared_lock']==true){
			$select = $this->_db->sharedLock($select);
		}
		return array('return' => $return, 'sql' => $select);
	}

	/**
	 * Crea un resultset creado por _createSQLSelect
	 *
	 * @access	private
	 * @param	string $select
	 * @return	boolean|ActiveRecordResulset
	 */
	private function _createResultset($select, $resultResource){
		if($select['return']=='1'){
			if($this->_db->numRows($resultResource)==0){
				$this->_count = 0;
				return false;
			} else {
				$this->_db->setFetchMode(DbBase::DB_ASSOC);
				$uniqueRow = $this->_db->fetchArray($resultResource);
				$this->_db->setFetchMode(DbBase::DB_BOTH);
				$this->dumpResultSelf($uniqueRow);
				$this->_count = 1;
				return $this->dumpResult($uniqueRow);
			}
		} else {
			$this->_count = $this->_db->numRows($resultResource);
			if($this->_count>0){
				return new ActiveRecordResultset($this, $resultResource, $select['sql']);
			} else {
				return new ActiveRecordResultset($this, false, $select['sql']);
			}
		}
	}

	/**
	 * Find data on Relational Map table
	 *
	 * @access	public
	 * @param 	string $params
	 * @return 	ActiveRecordResulset
	 */
	public function find($params=''){
		$this->_connect();
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		$select = $this->_createSQLSelect($params);
		$resultResource = $this->_db->query($select['sql']);
		return $this->_createResultset($select, $resultResource);
	}

	/**
	 * Find data on Relational Map table and locks Resultset
	 *
	 * @access	public
	 * @param	string $params
	 * @return	ActiveRecordResulset
	 * @throws	ActiveRecordException
	 */
	public function findForUpdate($params=''){
		$this->_connect();
		#if[compile-time]
		if($this->_db->isUnderTransaction()==false){
			throw new ActiveRecordException('No se puede hacer el findForUpdate mientras no este bajo una transacción');
		}
		#endif
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		$params['for_update'] = true;
		$select = $this->_createSQLSelect($params);
		$resultResource = $this->_db->query($select['sql']);
		return $this->_createResultset($select, $resultResource);
	}

	/**
	 * Find data on Relational Map table and locks Resultset using SharedLock
	 *
	 * @access	public
	 * @param	string $params
	 * @return	ActiveRecordResulset
	 * @throws	ActiveRecordException
	 */
	public function findWithSharedLock($params=''){
		$this->_connect();
		#if[compile-time]
		if($this->_db->isUnderTransaction()==false){
			throw new ActiveRecordException('No se puede hacer el findWithSharedLock mientras no este bajo una transacción');
		}
		#endif
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		$params['shared_lock'] = true;
		$select = $this->_createSQLSelect($params);
		$resultResource = $this->_db->query($select['sql']);
		return $this->_createResultset($select, $resultResource);
	}

	/**
	 * Arma una consulta SQL con el parámetro $params
     *
     * @access	public
     * @param 	string $select
	 * @param	string $params
	 * @return	string
	 */
	public function convertParamsToSql($select, $params=''){
		if(is_array($params)){
			if(isset($params['conditions'])&&$params['conditions']){
				$select.= ' WHERE '.$params["conditions"].' ';
			} else {
				$primaryKeys = $this->_getPrimaryKeyAttributes();
				if(!isset($primaryKeys[0]) && (isset($this->id) || $this->isView)){
					$primaryKeys[0] = 'id';
				}
				if(isset($params[0])){
					if(is_numeric($params[0])){
						$params['conditions'] = $primaryKeys[0].' = '.$this->_db->addQuotes($params[0]);
					} else {
						if($params[0]==''){
							$params['conditions'] = $primaryKeys[0].' = \'\'';
						} else {
							$params['conditions'] = $params[0];
						}
					}
				}
				if(isset($params['conditions'])){
					$select.= ' WHERE '.$params['conditions'];
				}
			}
			if(isset($params['order'])&&$params['order']) {
				$select.=' ORDER BY '.$params['order'];
			} else {
				$select.=' ORDER BY 1';
			}
			if(isset($params['limit'])&&$params['limit']) {
				$select = $this->_limit($select, $params['limit']);
			}
			if(isset($params['for_update'])){
				if($params['for_update']==true){
					$select = $this->_db->forUpdate($select);
				}
			}
			if(isset($params['shared_lock'])){
				if($params['shared_lock']==true){
					$select = $this->_db->sharedLock($select);
				}
			}
		} else {
			if(strlen($params)>0){
				if(is_numeric($params)){
					$select.= 'WHERE '.$primaryKeys[0].' = \''.$params.'\'';
				} else {
					$select.= 'WHERE '.$params;
				}
			}
		}
		return $select;
	}

	/**
	 * Devuelve una clausula LIMIT adecuada al RDBMS empleado
	 *
	 * @access	private
	 * @param	string $sqlStatement
	 * @param	$number
	 * @return	string
	 */
	private function _limit($sqlStatement, $number = 1){
		return $this->_db->limit($sqlStatement, $number);
	}

	/**
	 * Obtiene ó crea una instancia dadas unas condiciones
	 *
	 * @param	string $entityName
	 * @param	array $conditions
	 * @param	array $findOptions
	 * @return	ActiveRecordBase
	 * @static
	 */
	static public function getInstance($entityName, array $conditions, array $findOptions=array()){
		$criteria = array();
		foreach($conditions as $field => $value){
			if(is_integer($value)||is_double($value)){
				$criteria[] = $field.' = '.$value;
			} else {
				$criteria[] = $field.' = '.$value;
			}
		}
		$queryConditions = join(' AND ', $criteria);
		$entity = EntityManager::getEntityInstance($entityName);
		$arguments = array($queryConditions) + $findOptions;
		$exists = call_user_func_array(array($entity, 'findFirst'), $arguments);
		if($exists==false){
			foreach($conditions as $field => $value){
				$entity->writeAttribute($field, $value);
			}
		}
		return $entity;
	}

	/**
	 * Realiza un SELECT distinct de una columna del Modelo
	 *
	 * @access	public
	 * @param	string $params
	 * @return	array
	 */
	public function distinct($params=''){
		$this->_connect();
		if($this->_schema){
			$table = $this->_schema.'.'.$this->_source;
		} else {
			$table = $this->_source;
		}
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params['column'])){
			$params['column'] = $params['0'];
		} else {
			if(!$params['column']) {
				$params['column'] = $params['0'];
			}
		}
		$select = 'SELECT DISTINCT '.$params['column'].' FROM '.$table;
		if(isset($params['conditions'])&&$params['conditions']) {
			$select.=' WHERE '.$params["conditions"];
		}
		if(isset($params['order'])&&$params['order']) {
			$select.=' ORDER BY '.$params["order"].' ';
		} else {
			$select.=' ORDER BY 1 ';
		}
		if(isset($params['limit'])&&$params['limit']) {
			$select = $this->_limit($select, $params['limit']);
		}
		$results = array();
		$this->_db->setFetchMode(DbBase::DB_NUM);
		foreach($this->_db->fetchAll($select) as $result){
			$results[] = $result[0];
		}
		$this->_db->setFetchMode(DbBase::DB_ASSOC);
		return $results;
	}

	/**
	 * Realiza un SELECT que ejecuta funciones del RBDM
	 *
	 * @access		public
	 * @param		string $sql
	 * @return		array
	 * @static
	 * @deprecated
	 */
	static public function singleSelect($sql){
		$db = DbPool::getConnection();
		if(substr(ltrim($sql), 0, 7)!='SELECT') {
			$sql = 'SELECT '.$sql;
		}
		$db->setFetchMode(DbBase::DB_NUM);
		$num = $db->fetchOne($sql);
		$db->setFetchMode(DbBase::DB_ASSOC);
		return $num[0];
	}

	/**
	 * Devuelve el resultado del agrupamiento
	 *
	 * @param	array $params
	 * @param 	string $selectStatement
	 * @param	string $alias
	 * @return	mixed
	 */
	private function _getGroupResult(array $params, $selectStatement, $alias){
		if(isset($params['group'])){
			$resultResource = $this->_db->query($selectStatement);
			$count = $this->_db->numRows($resultResource);
			if($count>0){
				$rowObject = new ActiveRecordRow();
				$rowObject->setConnection($this->_db);
				return new ActiveRecordResultset($rowObject, $resultResource, $selectStatement);
			} else {
				return new ActiveRecordResultset(new stdClass(), false, $selectStatement);
			}
		} else {
			$num = $this->_db->fetchOne($selectStatement);
			return $num[$alias];
		}
	}

	/**
	 * Realiza un conteo de filas
	 *
	 * @access	public
	 * @param	string $params
	 * @return	integer
	 */
	public function count($params=''){
		$this->_connect();
		if($this->_schema){
			$table = $this->_schema.'.'.$this->_source;
		} else {
			$table = $this->_source;
		}
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(isset($params['distinct'])&&$params['distinct']) {
			$select = 'SELECT COUNT(DISTINCT '.$params['distinct'].') AS rowcount FROM '.$table.' ';
		} else {
			if(isset($params['group'])&&$params['group']){
				$select = 'SELECT '.$params['group'].',COUNT(*) AS rowcount FROM '.$table.' ';
			} else {
				$select = 'SELECT COUNT(*) AS rowcount FROM '.$table.' ';
			}
		}
		if(isset($params['conditions'])&&$params['conditions']) {
			$select.=' WHERE '.$params['conditions'].' ';
		} else {
			if(isset($params[0])){
				if(is_numeric($params[0])){
					$primaryKeys = $this->_getPrimaryKeyAttributes();
					if($this->isView&&(!isset($primaryKeys[0])||!$primaryKeys[0])){
						$primaryKeys[0] = 'id';
					}
					$select.= ' WHERE '.$primaryKeys[0].' = \''.$params[0].'\'';
				} else {
					$select.= ' WHERE '.$params[0];
				}
			}
		}
		if(isset($params['group'])){
			$select.=' GROUP BY '.$params['group'].' ';
		}
		if(isset($params['having'])){
			$select.=' HAVING '.$params['having'].' ';
		}
		if(isset($params['order'])&&$params['order']) {
			$select.=' ORDER BY '.$params['order'].' ';
		}
		if(isset($params['limit'])&&$params['limit']) {
			$select = $this->_limit($select, $params['limit']);
		}
		return $this->_getGroupResult($params, $select, 'rowcount');
	}

	/**
	 * Realiza un promedio sobre el campo $params
	 *
	 * @param	string $params
	 * @return	array
	 */
	public function average($params=''){
		$this->_connect();
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(isset($params['column'])) {
			if(!$params['column']){
				$params['column'] = $params[0];
			}
		} else {
			$params['column'] = $params[0];
		}
		if($this->_schema){
			$table = $this->_schema.'.'.$this->_source;
		} else {
			$table = $this->_source;
		}
		if(isset($params['group'])&&$params['group']){
			$select = 'SELECT '.$params['group'].',AVG('.$params['column'].') AS average FROM '.$table.' ';
		} else {
			$select = 'SELECT AVG('.$params['column'].') AS average FROM '.$table.' ';
		}
		if(isset($params['conditions'])&&$params['conditions']){
			$select.= ' WHERE '.$params['conditions'].' ';
		}
		if(isset($params['group'])){
			$select.=' GROUP BY '.$params['group'].' ';
		}
		if(isset($params['having'])){
			$select.=' HAVING '.$params["having"].' ';
		}
		if(isset($params['order'])&&$params['order']){
			$select.=' ORDER BY '.$params['order'].' ';
		} else {
			$select.=' ORDER BY 1 ';
		}
		if(isset($params['limit'])&&$params['limit']){
			$select = $this->_limit($select, $params['limit']);
		}
		return $this->_getGroupResult($params, $select, 'average');
	}

	/**
	 * Realiza una sumatoria
	 *
	 * @access	public
	 * @param	string $params
	 * @return	double
	 */
	public function sum($params=''){
		$this->_connect();
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(isset($params['column'])) {
			if(!$params['column']){
				$params['column'] = $params[0];
			}
		} else {
			if(!isset($params[0])){
				throw new ActiveRecordException('No ha definido la columna a sumar');
			} else {
				$params['column'] = $params[0];
			}
		}
		if($this->_schema){
			$table = $this->_schema.'.'.$this->_source;
		} else {
			$table = $this->_source;
		}
		if(isset($params['group'])&&$params['group']){
			$select = 'SELECT '.$params['group'].',SUM('.$params['column'].') AS sumatory FROM '.$table.' ';
		} else {
			$select = 'SELECT SUM('.$params['column'].') AS sumatory FROM '.$table.' ';
		}
		if(isset($params['conditions'])&&$params['conditions']){
			$select.= ' WHERE '.$params['conditions'].' ';
		}
		if(isset($params['group'])){
			$select.=' GROUP BY '.$params['group'].' ';
		}
		if(isset($params['having'])){
			$select.=' HAVING '.$params["having"].' ';
		}
		if(isset($params['order'])&&$params['order']){
			$select.=' ORDER BY '.$params['order'].' ';
		} else {
			$select.=' ORDER BY 1 ';
		}
		if(isset($params['limit'])&&$params['limit']){
			$select = $this->_limit($select, $params['limit']);
		}
		return $this->_getGroupResult($params, $select, 'sumatory');
	}

	/**
	 * Busca el valor máximo para el campo $params
	 *
	 * @access	public
	 * @param	string $params
	 * @return	mixed
	 */
	public function maximum($params=''){
		$this->_connect();
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(isset($params['column'])) {
			if(!$params['column']){
				$params['column'] = $params[0];
			}
		} else {
			$params['column'] = $params[0];
		}
		if($this->_schema){
			$table = $this->_schema.'.'.$this->_source;
		} else {
			$table = $this->_source;
		}
		if(isset($params['group'])&&$params['group']){
			$select = 'SELECT '.$params['group'].',MAX('.$params['column'].') AS maximum FROM '.$table.' ';
		} else {
			$select = 'SELECT MAX('.$params['column'].') AS maximum FROM '.$table.' ';
		}
		if(isset($params['conditions'])&&$params['conditions']){
			$select.= ' WHERE '.$params['conditions'].' ';
		}
		if(isset($params['group'])){
			$select.=' GROUP BY '.$params['group'].' ';
		}
		if(isset($params['having'])){
			$select.=' HAVING '.$params["having"].' ';
		}
		if(isset($params['order'])&&$params['order']){
			$select.=' ORDER BY '.$params['order'].' ';
		} else {
			$select.=' ORDER BY 1 ';
		}
		if(isset($params['limit'])&&$params['limit']){
			$select = $this->_limit($select, $params['limit']);
		}
		return $this->_getGroupResult($params, $select, 'maximum');
	}

	/**
	 * Busca el valor minimo para el campo $params
	 *
	 * @access	public
	 * @param	string $params
	 * @return	mixed
	 */
	public function minimum($params=''){
		$this->_connect();
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(isset($params['column'])) {
			if(!$params['column']){
				$params['column'] = $params[0];
			}
		} else {
			$params['column'] = $params[0];
		}
		if($this->_schema){
			$table = $this->_schema.'.'.$this->_source;
		} else {
			$table = $this->_source;
		}
		if(isset($params['group'])&&$params['group']){
			$select = 'SELECT '.$params['group'].',MIN('.$params['column'].') AS minimum FROM '.$table.' ' ;
		} else {
			$select = 'SELECT MIN('.$params['column'].') AS minimum FROM '.$table.' ' ;
		}
		if(isset($params['conditions'])&&$params['conditions']){
			$select.= ' WHERE '.$params['conditions'].' ';
		}
		if(isset($params['group'])){
			$select.=' GROUP BY '.$params['group'].' ';
		}
		if(isset($params['having'])){
			$select.=' HAVING '.$params["having"].' ';
		}
		if(isset($params['order'])&&$params['order']){
			$select.=' ORDER BY '.$params['order'].' ';
		} else {
			$select.=' ORDER BY 1 ';
		}
		if(isset($params['limit'])&&$params['limit']){
			$select = $this->_limit($select, $params['limit']);
		}
		return $this->_getGroupResult($params, $select, 'minimum');
	}

	/**
	 * Realiza un conteo directo mediante $sql
	 *
	 * @param		string $sqlQuery
	 * @return		mixed
	 * @deprecated
	 */
	public function countBySql($sqlQuery){
		#if[compile-time]
		CoreType::assertString($sqlQuery);
		#endif
		$this->_connect();
		$this->_db->setFetchMode(DbBase::DB_NUM);
		$num = $this->_db->fetchOne($sqlQuery);
		return (int) $num[0];
	}

	/**
	 * Iguala los valores de un resultado de la base de datos
	 * en un nuevo objeto con sus correspondientes
	 * atributos de la clase
	 *
	 * @param	array $result
	 * @return	ActiveRecord
	 */
	public function dumpResult(array $result){
		$this->_connect();
		$object = clone $this;
		$object->_forceExists = true;
		/**
		 * Consulta si la clase es padre de otra y crea el tipo de dato correcto
		 */
		/*if(isset($result['type'])){
			if(in_array($result['type'], $this->_parentOf)){
				if(class_exists($result['type'])){
					$obj = new $result['type'];
					unset($result['type']);
				}
			}
		}*/
		$this->_dumpLock = true;
		if(is_array($result)==true){
			foreach($result as $key => $value){
				$object->$key = $value;
			}
		}
		$this->_dumpLock = false;
		return $object;
	}

	/**
	 * Iguala los valores de un resultado de la base de datos
	 * con sus correspondientes atributos de la clase
	 *
	 * @access	public
	 * @param	array $result
	 */
	public function dumpResultSelf(array $result){
		$this->_connect();
		$this->_dumpLock = true;
		if(is_array($result)==true){
			foreach($result as $key => $value){
				$this->$key = $value;
			}
		}
		$this->_dumpLock = false;
	}

	/**
	 * Obtiene los mensajes de error generados en el proceso de validación
	 *
	 * @access	public
	 * @return	array
	 */
	public function getMessages(){
		return $this->_errorMessages;
	}

	/**
	 * Agrega un mensaje a la lista de errores de validación
	 *
	 * @param	string $field
	 * @param	string $message
	 * @throws	ActiveRecordException
	 */
	public function appendMessage($message){
		if(is_object($message)){
			#if[compile-time]
			if(get_class($message)!='ActiveRecordMessage'){
				throw new ActiveRecordException("Formato de Mensaje inválido '".get_class($message)."'");
			}
			#endif
		} else {
			throw new ActiveRecordException("Formato de Mensaje inválido '".gettype($message)."'");
		}
		$this->_errorMessages[] = $message;
	}

	/**
	 * Establece el valor del DynamicUpdate
	 *
	 * @param boolean $dynamicUpdate
	 */
	protected function setDynamicUpdate($dynamicUpdate){
		#if[compile-time]
		CoreType::assertBool($dynamicUpdate);
		#endif
		self::$_dynamicUpdate = $dynamicUpdate;
	}

	/**
	 * Establece el valor del DynamicInsert
	 *
	 * @param boolean $dynamicInsert
	 */
	protected function setDynamicInsert($dynamicInsert){
		#if[compile-time]
		CoreType::assertBool($dynamicInsert);
		#endif
		self::$_dynamicInsert = $dynamicInsert;
	}

	/**
	 * Establece los meta-datos de un determinado campo del modelo
	 *
	 * @param	string $attributeName
	 * @param	array $definition
	 */
	public function setAttributeMetadata($attributeName, $definition){
		ActiveRecordMetaData::setAttributeMetadata($this->_source, $this->_schema, $attributeName, $definition);
	}

	/**
	 * Devuelve los atributos del modelo (campos) (internal)
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function _getAttributes(){
		return ActiveRecordMetaData::getAttributes($this->_source, $this->_schema);
	}

	/**
	 * Devuelve los atributos del modelo (campos)
	 *
	 * @access	public
	 * @return	array
	 */
	public function getAttributes(){
		$this->_connect();
		return $this->_getAttributes();
	}

	/**
	 * Devuelve los campos que son llave primaria (interno)
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function _getPrimaryKeyAttributes(){
		return ActiveRecordMetaData::getPrimaryKeys($this->_source,  $this->_schema);
	}

	/**
	 * Devuelve los campos que son llave primaria
	 *
	 * @access	public
	 * @return	array
	 */
	public function getPrimaryKeyAttributes(){
		$this->_connect();
		return $this->_getPrimaryKeyAttributes();
	}

	/**
	 * Devuelve los campos que no son llave primaria (internal)
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function _getNonPrimaryKeyAttributes(){
		return ActiveRecordMetaData::getNonPrimaryKeys($this->_source,  $this->_schema);
	}

	/**
	 * Devuelve los campos que no son llave primaria
	 *
	 * @access	public
	 * @return	array
	 */
	public function getNonPrimaryKeyAttributes(){
		$this->_connect();
		return $this->_getNonPrimaryKeyAttributes();
	}

	/**
	 * Devuelve los campos que son no nulos (internal)
	 *
	 * @access	protected
	 * @return	array
	 */
	protected function _getNotNullAttributes(){
		return ActiveRecordMetaData::getNotNull($this->_source, $this->_schema);
	}

	/**
	 * Devuelve los campos que son no nulos
	 *
	 * @access	public
	 * @return	array
	 */
	public function getNotNullAttributes(){
		$this->_connect();
		return $this->_getNotNullAttributes();
	}

	/**
	 * Devuelve los campos fecha que asignan la fecha del sistema automaticamente al insertar (internal)
	 *
	 * @access protected
	 * @return array
	 */
	protected function _getDatesAtAttributes(){
		return ActiveRecordMetaData::getDatesAt($this->_source, $this->_schema);
	}

	/**
	 * Obtiene los tipos de datos de los atributos
	 *
	 * @access public
	 * @return array
	 */
	public function getDataTypes(){
		$this->_connect();
		return $this->_getDataTypes();
	}

	/**
	 * Obtiene los tipos de datos de los atributos (internal)
	 *
	 * @access protected
	 * @return array
	 */
	protected function _getDataTypes(){
		return ActiveRecordMetaData::getDataTypes($this->_source, $this->_schema);
	}

	/**
	 * Obtiene los campos que tienen tipos de datos numéricos
	 *
	 * @return array
	 */
	public function getDataTypesNumeric(){
		$this->_connect();
		return array_keys($this->_getDataTypes());
	}

	/**
	 * Obtiene los campos que tienen tipos de datos numéricos (internal)
	 *
	 * @access protected
	 * @return array
	 */
	protected function _getDataTypesNumeric(){
		return ActiveRecordMetaData::getDataTypesNumeric($this->_source, $this->_schema);
	}

	/**
	 * Devuelve los nombres de los atributos del modelo
	 *
	 * @access public
	 * @return array
	 */
	public function getAttributesNames(){
		$this->_connect();
		return ActiveRecordMetaData::getAttributes($this->_source, $this->_schema);
	}

	/**
	 * Devuelve los campos fecha que asignan la fecha del sistema automaticamente al insertar
	 *
	 * @access public
	 * @return array
	 */
	public function getDatesAtAttributes(){
		$this->_connect();
		return $this->_getDatesAtAttributes();
	}

	/**
	 * Devuelve los campos fecha que asignan la fecha del sistema automaticamente al modificar (internal)
	 *
	 * @access protected
	 * @return array
	 */
	protected function _getDatesInAttributes(){
		return ActiveRecordMetaData::getDatesIn($this->_source, $this->_schema);
	}

	/**
	 * Devuelve los campos fecha que asignan la fecha del sistema automáticamente al modificar
	 *
	 * @access	public
	 * @return	array
	 */
	public function getDatesInAttributes(){
		$this->_connect();
		return $this->_getDatesInAttributes();
	}

	/**
	 * Lee un atributo de la entidad por su nombre
	 *
	 * @access	public
	 * @param	string $attribute
	 * @return	mixed
	 */
	public function readAttribute($attribute){
		#if[compile-time]
		CoreType::assertString($attribute);
		#endif
		$this->_connect();
		return $this->$attribute;
	}

	/**
	 * Escribe el valor de un atributo de la entidad por su nombre
	 *
	 * @access	public
	 * @param	string $attribute
	 * @param	mixed $value
	 */
	public function writeAttribute($attribute, $value){
		#if[compile-time]
		CoreType::assertString($attribute);
		#endif
		$this->_connect();
		$this->$attribute = $value;
	}

	/**
	 * Indica si el modelo tiene el campo indicado
	 *
	 * @param	string $field
	 * @return	boolean
	 */
	public function hasField($field){
		#if[compile-time]
		CoreType::assertString($field);
		#endif
		$this->_connect();
		$fields = $this->_getAttributes();
		return in_array($field, $fields);
	}

	/**
	 * Indica si el modelo tiene el campo indicado
	 *
	 * @param	string $field
	 * @return	boolean
	 */
	public function isAttribute($field){
		return $this->hasField($field);
	}

	/**
	 * Creates a new row in map table
	 *
	 * @access	public
	 * @param	mixed $values
	 * @return	boolean
	 * @throws	ActiveRecordException
	 */
	public function create($values=''){
		$this->_connect();
		$primaryKeys = $this->_getPrimaryKeyAttributes();
		if(is_array($values)){
			$fields = $this->getAttributes();
			if(isset($values[0])&&is_array($values[0])){
				foreach($values as $value){
					foreach($fields as $field){
						$this->$field = '';
					}
					foreach($value as $key => $r){
						if(isset($this->$key)){
							$this->$key = $r;
						} else {
							throw new ActiveRecordException('No existe el Atributo "'.$key.'" en la entidad "'.get_class($this).'" al ejecutar la inserción');
						}
					}
					if($primaryKeys[0]=='id'){
						$this->id = null;
					}
					return $this->save();
				}
			} else {
				foreach($fields as $f){
					$this->$f = '';
				}
				foreach($values as $key => $r){
					if(isset($this->$key)){
						$this->$key = $r;
					} else {
						throw new ActiveRecordException('No existe el atributo "'.$key.'" en la entidad "'.$this->_source.'" al ejecutar la inserción');
					}
				}
				if($primaryKeys[0]=='id'){
					$this->id = null;
				}
				return $this->save();
			}
		} else {
			if($values!==''){
				throw new ActiveRecordException("Parámetro incompatible en acción 'create'. No se pudo crear ningún registro");
			} else {
				//Detectar campo autonumérico
				$this->_forceExists = true;
				if($primaryKeys[0]=='id'){
					$this->id = null;
				}
				return $this->save();
			}
		}
		return true;
	}

	/**
	 * Consulta si un determinado registro existe o no en la entidad de la base de datos
	 *
	 * @access	private
	 * @param	string $wherePk
	 * @return	bool
	 */
	private function _exists($wherePk=''){
		if($this->_forceExists===false){
			if($this->_schema){
				$table = $this->_schema.'.'.$this->_source;
			} else {
				$table = $this->_source;
			}
			if($wherePk===''){
				$wherePk = array();
				$primaryKeys = $this->_getPrimaryKeyAttributes();
				$dataTypeNumeric = $this->_getDataTypesNumeric();
				if(count($primaryKeys)>0){
					foreach($primaryKeys as $key){
						if($this->$key!==null&&$this->$key!==''){
							if(isset($dataTypeNumeric[$key])){
								$wherePk[] = ' '.$key.' = '.$this->$key;
							} else {
								$wherePk[] = ' '.$key.' = \''.$this->$key.'\'';
							}
						}
					}
					if(count($wherePk)){
						$this->_wherePk = join(' AND ', $wherePk);
					} else {
						return 0;
					}
					$query = 'SELECT COUNT(*) AS rowcount FROM '.$table.' WHERE '.$this->_wherePk;
				} else {
					return 0;
				}
			} else {
				if(is_numeric($wherePk)){
					$query = 'SELECT COUNT(*) AS rowcount FROM '.$table.' WHERE id = '.$wherePk;
				} else {
					$query = 'SELECT COUNT(*) AS rowcount FROM '.$table.' WHERE '.$wherePk;
				}
			}
			$num = $this->_db->fetchOne($query);
			return (bool) $num['rowcount'];
		} else {
			$wherePk = array();
			$primaryKeys = $this->_getPrimaryKeyAttributes();
			$dataTypeNumeric = $this->_getDataTypesNumeric();
			if(count($primaryKeys)>0){
				foreach($primaryKeys as $key){
					if($this->$key!==null&&$this->$key!==''){
						if(isset($dataTypeNumeric[$key])){
							$wherePk[] = ' '.$key.' = '.$this->$key;
						} else {
							$wherePk[] = ' '.$key.' = \''.$this->$key.'\'';
						}
					}
				}
				if(count($wherePk)){
					$this->_wherePk = join(' AND ', $wherePk);
					return true;
				} else {
					return 0;
				}
			} else {
				return 0;
			}
		}
	}

	/**
	 * Consulta si un determinado registro existe o no en la entidad de la base de datos
	 *
	 * @access	public
	 * @param	string $wherePk
	 * @return	bool
	 */
	public function exists($wherePk=''){
		$this->_connect();
		return $this->_exists($wherePk);
	}

	/**
	 * Lanza eventos de cancelación de la operación de acuerdo a la que se esté ejecutando
	 *
	 * @return boolean
	 */
	protected function _cancelOperation(){
		if($this->_operationMade==self::OP_DELETE){
			$this->_callEvent('notDeleted');
		} else {
			$this->_callEvent('notSaved');
		}
		if(TransactionManager::isAutomatic()==true){
			$transaction = TransactionManager::getAutomaticTransaction();
			$transaction->setRollbackedRecord($this);
			$transaction->rollback();
		} else {
			return false;
		}
	}

	/**
	 * Saves Information on the ActiveRecord Properties
	 *
	 * @return	boolean
	 * @throws	ActiveRecordException
	 */
	public function save(){

		$this->_connect();
		$exists = $this->_exists();

		if($exists==false){
			$this->_operationMade = self::OP_CREATE;
		} else {
			$this->_operationMade = self::OP_UPDATE;
		}

		// Run Validation Callbacks Before
		$this->_errorMessages = array();
		if(self::$_disableEvents==false){
			if($this->_callEvent('beforeValidation')===false){
				$this->_cancelOperation();
				return false;
			}
			if(!$exists){
				if($this->_callEvent('beforeValidationOnCreate')===false){
					$this->_cancelOperation();
					return false;
				}
			} else {
				if($this->_callEvent('beforeValidationOnUpdate')===false){
					$this->_cancelOperation();
					return false;
				}
			}
		}

		//Generadores
		$generator = null;
		$className = get_class($this);
		if(EntityManager::hasGenerator($className)){
			$generator = EntityManager::getEntityGenerator($className);
			$generator->setIdentifier($this);
		}

		//LLaves foráneas virtuales
		if(EntityManager::hasForeignKeys($className)){
			$foreignKeys = EntityManager::getForeignKeys($className);
			$error = false;
			foreach($foreignKeys as $indexKey => $keyDescription){
				$entity = EntityManager::getEntityInstance($keyDescription['rt'], false);
				$field = $keyDescription['fi'];
				if($this->$field===null||$this->$field===''){
					continue;
				}
				$conditions = $keyDescription['rf']." = '".$this->$field."'";
				if(isset($keyDescription['op']['conditions'])){
					$conditions.= ' AND '.$keyDescription['op']['conditions'];
				}
				$entity->setConnection($this->getConnection());
				$rowcount = $entity->count($conditions);
				if($rowcount==0){
					if(isset($keyDescription['op']['message'])){
						$userMessage = $keyDescription['op']['message'];
					} else {
						$userMessage = 'El valor de "'.$keyDescription['fi'].'" no existe en la tabla referencia';
					}
					$message = new ActiveRecordMessage($userMessage, $keyDescription['fi'], 'ConstraintViolation');
					$this->appendMessage($message);
					$error = true;
					break;
				}
			}
			if($error==true){
				$this->_callEvent('onValidationFails');
				$this->_cancelOperation();
				return false;
			}
		}

		$notNull = $this->_getNotNullAttributes();
		$at = $this->_getDatesAtAttributes();
		$in = $this->_getDatesInAttributes();
		if(is_array($notNull)){
			$error = false;
			$numFields = count($notNull);
			for($i=0;$i<$numFields;++$i){
				$field = $notNull[$i];
				if($this->$field===null||$this->$field===''){
					if(!$exists&&$field=='id'){
						continue;
					}
					if(!$exists){
						if(isset($at[$field])){
							continue;
						}
					} else {
						if(isset($in[$field])){
							continue;
						}
					}
					$humanField = str_replace('_id', '', $field);
					$message = new ActiveRecordMessage("El campo $humanField no puede ser nulo ''", $field, 'PresenceOf');
					$this->appendMessage($message);
					$error = true;
				}
			}
			if($error==true){
				$this->_callEvent('onValidationFails');
				$this->_cancelOperation();
				return false;
			}
		}

		// Run Validation
		if($this->_callEvent('validation')===false){
			$this->_callEvent('onValidationFails');
			$this->_cancelOperation();
			return false;
		}

		if(self::$_disableEvents==false){
			// Run Validation Callbacks After
			if(!$exists){
				if($this->_callEvent('afterValidationOnCreate')===false){
					$this->_cancelOperation();
					return false;
				}
			} else {
				if($this->_callEvent('afterValidationOnUpdate')===false){
					$this->_cancelOperation();
					return false;
				}
			}
			if($this->_callEvent('afterValidation')===false){
				$this->_cancelOperation();
				return false;
			}

			// Run Before Callbacks
			if($this->_callEvent('beforeSave')===false){
				$this->_cancelOperation();
				return false;
			}
			if($exists){
				if($this->_callEvent('beforeUpdate')===false){
					$this->_cancelOperation();
					return false;
				}
			} else {
				if($this->_callEvent('beforeCreate')===false){
					$this->_cancelOperation();
					return false;
				}
			}
		}

		if($this->_schema){
			$table = $this->_schema.'.'.$this->_source;
		} else {
			$table = $this->_source;
		}

		$magicQuotesRuntime = get_magic_quotes_runtime();
		$dataType = $this->_getDataTypes();
		$primaryKeys = $this->_getPrimaryKeyAttributes();
		$dataTypeNumeric = $this->_getDataTypesNumeric();
		if($exists){
			if(self::$_dynamicUpdate==false){
				$fields = array();
				$values = array();
				$nonPrimary = $this->_getNonPrimaryKeyAttributes();
				foreach($nonPrimary as $np){
					if(isset($in[$np])){
						$this->$np = Date::now();
					}
					$fields[] = $np;
					if(is_object($this->$np)&&($this->$np instanceof DbRawValue)){
						$values[] = $this->$np->getValue();
					} else {
						if($this->$np===''||$this->$np===null){
							$values[] = 'NULL';
						} else {
							if(!isset($dataTypeNumeric[$np])){
								if($dataType[$np]=='date'){
									$values[] = $this->_db->getDateUsingFormat($this->$np);
								} else {
									$values[] = '\''.addslashes($this->$np).'\'';
								}
							} else {
								$values[] = addslashes($this->$np);
							}
						}
					}
				}
			} else {
				$conditions = array();
				foreach($primaryKeys as $field){
					if(!isset($dataTypeNumeric[$field])){
						$conditions[] = $field.' = \''.$this->field.'\'';
					} else {
						$conditions[] = $field.' = '.$this->field;
					}
				}
				$pkCondition = join(' AND ', $conditions);
				$existRecord = clone $this;
				$record = $existRecord->findFirst($pkCondition);
				$fields = array();
				$values = array();
				$nonPrimary = $this->_getNonPrimaryKeyAttributes();
				foreach($nonPrimary as $np){
					if(isset($in[$np])){
						$this->$np = $this->_db->getCurrentDate();
					}
					if(is_object($this->$np)){
						if($this->$np instanceof DbRawValue){
							$value = $this->$np->getValue();
						} else {
							if($this->$np instanceof Date){
								$value = (string) $this->$np;
							} else {
								if($this->$np instanceof Decimal){
									$value = (string) $this->$np;
								} else {
									throw new ActiveRecordException('El objeto instancia de "'.get_class($this->$field).'" en el campo "'.$field.'" es muy complejo, debe realizarle un "cast" a un tipo de dato escalar antes de almacenarlo');
								}
							}
						}
						if($record->$np!=$value){
							$fields[] = $np;
							$values[] = $values;
						}
					} else {
						if($this->$np===''||$this->$np===null){
							if($record->$np!==''&&$record->$np!==null){
								$fields[] = $np;
								$values[] = 'NULL';
							}
						} else {
							if(!isset($dataTypeNumeric[$np])){
								if($dataType[$np]=='date'){
									$value = $this->_db->getDateUsingFormat($this->$np);
									if($record->$np!=$value){
										$fields[] = $np;
										$values[] = $value;
									}
								} else {
									if($record->$np!=$this->$np){
										$fields[] = $np;
										$values[] = "'".addslashes($this->$np)."'";
									}
								}
							}
						}
					}
				}
			}
			$success = $this->_db->update($table, $fields, $values, $this->_wherePk);
		} else {
			$fields = array();
			$values = array();
			$attributes = $this->getAttributes();
			foreach($attributes as $field){
				if($field!='id'){
					if(isset($at[$field])){
						if($this->$field==null||$this->$field===""){
							$this->$field = $this->_db->getCurrentDate();
						}
					} else {
						if(isset($in[$field])){
							$this->$field = new DbRawValue('NULL');
						}
					}
					$fields[] = $field;
					if(is_object($this->$field)){
						if($this->$field instanceof DbRawValue){
							$values[] = $this->$field->getValue();
						} else {
							if($this->$field instanceof Date){
								$values[] =  (string) $this->$field;
							} else {
								if($this->$field instanceof Decimal){
									$values[] = (string) $this->$field;
								} else {
									throw new ActiveRecordException('El objeto instancia de "'.get_class($this->$field).'" en el campo "'.$field.'" es muy complejo, debe realizarle un "cast" a un tipo de dato escalar antes de almacenarlo');
								}
							}
						}
					} else {
						if(isset($dataTypeNumeric[$field])||$this->$field=='NULL'){
							if($this->$field===''||$this->$field===null){
								$values[] = 'NULL';
							} else {
								$values[] = addslashes($this->$field);
							}
						} else {
							if($dataType[$field]=='date'){
								if($this->$field===null||$this->$field===''){
									$values[] = 'NULL';
								} else {
									$values[] = $this->_db->getDateUsingFormat(addslashes($this->$field));
								}
							} else {
								if($this->$field===null||$this->$field===''){
									$values[] = 'NULL';
								} else {
									if($magicQuotesRuntime==true){
										$values[] = "'".$this->$field."'";
									} else {
									    $values[] = "'".addslashes($this->$field)."'";
									}
								}
							}
						}
					}
				}
			}
			$sequenceName = '';
			if($generator===null){
				if(count($primaryKeys)==1){
					// Hay que buscar la columna identidad aqui!
					if(!isset($this->id)||!$this->id){
						if(method_exists($this, 'sequenceName')){
							$sequenceName = $this->sequenceName();
						}
						$identityValue = $this->_db->getRequiredSequence($this->_source, $primaryKeys[0], $sequenceName);
						if($identityValue!==false){
							$fields[] = 'id';
							$values[] = $identityValue;
						}
					} else {
						if(isset($this->id)){
							$fields[] = 'id';
							$values[] = $this->id;
						}
					}
				}
			} else {
				if(isset($this->id)){
					$fields[] = 'id';
					$values[] = $this->id;
				}
			}
			$success = $this->_db->insert($table, $values, $fields);
		}
		if($this->_db->isUnderTransaction()==false){
			if($this->_db->getHaveAutoCommit()==true){
				$this->_db->commit();
			}
		}
		if($success){
			if($exists==true){
				$this->_callEvent('afterUpdate');
			} else {
				if($generator===null){
					if(count($primaryKeys)==1){
						if(isset($dataTypeNumeric[$primaryKeys[0]])){
						    $lastId = $this->_db->lastInsertId($table, $primaryKeys[0], $sequenceName);
						    if($lastId>0){
								if(self::$_refreshPersistance==true){
									$this->findFirst($lastId);
								} else {
									$this->{$primaryKeys[0]} = $lastId;
								}
						    }
						}
					}
				} else {
					//Actualiza el consecutivo para algunos generadores
					$generator->updateConsecutive($this);
				}
				$this->_callEvent('afterCreate');
			}
			$this->_callEvent('afterSave');
			return $success;
		} else {
			$this->_callEvent('notSave');
			$this->_cancelOperation();
			return false;
		}
	}

	/**
	 * Observa un evento individual del objeto
	 *
	 * @param string $eventName
	 * @param callback $callback
	 */
	public function observe($eventName, $callback){
		if(!isset($this->_observers[$eventName])){
			$this->_observers[$eventName] = array();
		}
		$this->_observers[$eventName][] = $callback;
	}

	/**
	 * Devuelve el código de la última operación realizada
	 *
	 * @return boolean
	 */
	public function getOperationMade(){
		return $this->_operationMade;
	}

	/**
	 * Indica si la ultima operación realizada fue una actualizacion
	 *
	 * @return boolean
	 */
	public function operationWasInsert(){
		return $this->_operationMade == self::OP_CREATE ? true : false;
	}

	/**
	 * Indica si la ultima operación realizada fue una actualizacion
	 *
	 * @return boolean
	 */
	public function operationWasUpdate(){
		return $this->_operationMade == self::OP_UPDATE ? true : false;
	}

	/**
	 * Permite establecer el tipo de generador de valores únicos a usar
	 *
	 * @param	string $adapter
	 * @param	string $column
	 * @param	array $options
	 */
	public function setIdGenerator($adapter, $column, $options=array()){
		EntityManager::setEntityGenerator(get_class($this), $adapter, $column, $options);
	}

	/**
	 * Find All data in the Relational Table
	 *
	 * @access	public
	 * @param	string $field
	 * @param	string $value
	 * @return	ActiveRecordResultset
	 */
	public function findAllBy($field, $value){
		return $this->find(array('conditions' => $field." = '$value'"));
	}

	/**
	 * Updates Data in the Relational Table
	 *
	 * @param	mixed $values
	 * @return	boolean
	 * @throws	ActiveRecordException
	 */
	public function update($values=''){
		$this->_connect();
		$numberArguments = func_num_args();
		$values = Utils::getParams(func_get_args(), $numberArguments);
		if(is_array($values)){
			foreach($values as $key => $value){
				if(isset($this->$key)){
					$this->$key = $value;
				} else {
					throw new ActiveRecordException('No existe el atributo "'.$key.'" en la entidad "'.$this->_source.'" al ejecutar la inserción');
				}
			}
			if($this->_exists()==true){
				return $this->save();
			} else {
				$this->appendMessage('', 'No se puede actualizar porque el registro no existe');
				return false;
			}
		} else {
			if($this->_exists()==true){
				return $this->save();
			} else {
				$this->appendMessage('', 'No se puede actualizar porque el registro no existe');
				return false;
			}
		}
	}

	/**
	 * Deletes data from Relational Map Table
	 *
	 * @access	public
	 * @param	mixed $params
	 * @return	boolean
	 */
	public function delete($params=''){

		$this->_connect();
		if($this->_schema){
			$table = $this->_schema.'.'.$this->_source;
		} else {
			$table = $this->_source;
		}

		$this->_operationMade = self::OP_DELETE;

		$conditions = '';
		if(is_array($params)){
			$numberArguments = func_num_args();
			$params = Utils::getParams(func_get_args(), $numberArguments);
			if(isset($params['conditions'])){
				$conditions = $params['conditions'];
			}
		} else {
			$primaryKeys = $this->_getPrimaryKeyAttributes();
			if(is_numeric($params)){
				if(count($primaryKeys)==1){
					$conditions = $primaryKeys[0]." = '".$params."'";
				} else {
					throw new ActiveRecordException('Número de parámetros insuficientes para realizar el borrado');
				}
			} else{
				if($params){
					$conditions = $params;
				} else {
					if(count($primaryKeys)==1){
						$primaryKeyValue = $this->readAttribute($primaryKeys[0]);
						$conditions = $primaryKeys[0]." = '".$primaryKeyValue."'";
					} else {
						$conditions = array();
						foreach($primaryKeys as $primaryKey){
							$primaryKeyValue = $this->readAttribute($primaryKey);
							$conditions[] = $primaryKey." = '".$primaryKeyValue."'";
						}
						$conditions = join(' AND ', $conditions);
					}
				}
			}
		}

		//LLaves foráneas virtuales (reversa)
		$className = get_class($this);
		$hasManyDefinitions = EntityManager::getAllHasManyDefinition($className);
		if($hasManyDefinitions){
			$error = false;
			foreach($hasManyDefinitions as $entityRelation => $definition){
				if(EntityManager::hasForeignKeys($entityRelation)){
					$foreignKey = EntityManager::getForeignKey($entityRelation, $className);
					if($foreignKey!==false){
						$localValue = $this->readAttribute($foreignKey['rf']);
						$referencedEntity = EntityManager::getEntityInstance($entityRelation);
						$referencedEntity->setConnection($this->getConnection());
						$conditions = "{$foreignKey['fi']} = '$localValue'";
						if(isset($foreignKey['conditions'])){
							$conditions.= " AND ".$foreignKey['conditions'];
						}
						$rowCount = $referencedEntity->count($conditions);
						if($rowCount>0){
							if(isset($keyDescription['op']['deleteMessage'])){
								$userMessage = $keyDescription['op']['deleteMessage'];
							} else {
								$userMessage = 'El registro esta referenciado en '.$entityRelation;
							}
							$message = new ActiveRecordMessage($userMessage, $foreignKey['fi'], 'ConstraintViolation');
							$this->appendMessage($message);
							$error = true;
							break;
						}
					}
				}
			}
			if($error==true){
				$this->_callEvent('onValidationFails');
				$this->_cancelOperation();
				return false;
			}
		}

		if($this->_callEvent('beforeDelete')===false){
			return false;
		}
		$success = $this->_db->delete($table, $conditions);
		if($success==true){
			$this->_callEvent('afterDelete');
		}
		return $success;
	}

	/**
	 * Actualiza todos los atributos de la entidad
	 *
	 * Clientes::updateAll("estado='A', fecha='2005-02-02'", "id>100");
	 * Clientes::updateAll("estado='A', fecha='2005-02-02'", "id>100", "limit: 10");
	 *
	 * @access	public
	 * @param	string $values
	 * @return	boolean
	 * @throws	ActiveRecordException
	 */
	public function updateAll($values){
		$this->_connect();
		$params = array();
		if($this->_schema){
			$table = $this->_schema.'.'.$this->_source;
		} else {
			$table = $this->_source;
		}
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params['conditions'])||!$params['conditions']){
			if(isset($params[1])){
				$params['conditions'] = $params[1];
			} else {
				$params['conditions'] = '';
			}
		}
		if($params['conditions']){
			$params['conditions'] = ' WHERE '.$params['conditions'];
		}
		if(!isset($params[0])){
			throw new ActiveRecordException('Debe indicar los valores a actualizar');
		}
		$sql = 'UPDATE '.$table.' SET '.$params[0].' '.$params['conditions'];
		if(isset($params['limit'])&&$params['limit']){
			$sql = $this->_limit($sql, $params["limit"]);
		}
		return $this->_db->query($sql);
	}

	/**
	 * Delete All data from Relational Map Table
	 *
	 * @access	public
	 * @param	string $conditions
	 * @return	ActiveRecordBase
	 */
	public function deleteAll($conditions=''){
		#if[compile-time]
		CoreType::assertString($conditions);
		#endif
		$this->_connect();
		if($this->_schema){
			$table = $this->_schema.'.'.$this->_source;
		} else {
			$table = $this->_source;
		}
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(isset($params['limit'])){
			$conditions = $this->_limit($params[0], $params['limit']);
		}
		if(isset($params[0])){
			$this->_db->delete($table, $params[0]);
		} else {
			$this->_db->delete($table);
		}
		return $this;
	}

	/**
	 * Imprime una version humana de los valores de los campos
	 * del modelo en una sola linea
	 *
	 * @access public
	 * @return string
	 */
	public function inspect(){
		$inspect = array();
		$fields = $this->_getAttributes();
		foreach($fields as $field){
			if(!is_array($field)){
				if(is_object($this->$field)){
					if(method_exists($this->$field, '__toString')){
						$inspect[] = $field.'='.$this->$field;
					} else {
						$inspect[] = $field.'=<'.get_class($this->$field).'>';
					}
				} else {
					$inspect[] = $field.'='.$this->$field;
				}
			}
		}
		return '<'.get_class($this).'> '.join(', ', $inspect);
	}

	/**
	 * Ejecuta el evento del modelo
	 *
	 * @param	string $eventName
	 * @return	boolean
	 */
	private function _callEvent($eventName){
		if(self::$_disableEvents==false){
			if(isset($this->_observers[$eventName])){
				foreach($this->_observers[$eventName] as $observer){
					if($observer($this)===false){
						return false;
					}
				}
			}
			if(method_exists($this, $eventName)){
				if($this->{$eventName}()===false){
					return false;
				}
			} else {
				if(isset($this->{$eventName})){
					$method = $this->{$eventName};
					if($this->$method()===false){
						return false;
					}
				}
			}
		}
		return true;
	}

	/**
	 * Ejecuta un validador sobre un campo de la entidad
	 *
	 * @param 	string $className
	 * @param 	string $field
	 * @param 	array $options
	 */
	private function _executeValidator($className, $field, $options){
		if(is_array($field)==false){
			$validator = new $className($this, $field, $this->$field, $options);
		} else {
			$values = array();
			foreach($field as $singleField){
				$values[] = $this->$singleField;
			}
			$validator = new $className($this, $field, $values, $options);
		}
		$validator->checkOptions();
		if($validator->validate()===false){
			foreach($validator->getMessages() as $message){
				$this->_errorMessages[] = $message;
			}
		}
	}

	/**
	 * Instancia los validadores y los ejecuta
	 *
	 * @access	public
	 * @param	string $validatorClass
	 * @param	array $options
	 * @throws	ActiveRecordException
	 */
	protected function validate($validatorClass, $options){
		if(!interface_exists('ActiveRecordValidatorInterface', false)){
			require KEF_ABS_PATH.'Library/Kumbia/ActiveRecord/Validator/Interface.php';
		}
		if(!class_exists('ActiveRecordValidator', false)){
			require KEF_ABS_PATH.'Library/Kumbia/ActiveRecord/Validator/ActiveRecordValidator.php';
		}
		$className = $validatorClass.'Validator';
		if(!class_exists($className, false)){
			if(Core::fileExists('Library/Kumbia/ActiveRecord/Validators/'.$className.'.php')){
				require KEF_ABS_PATH.'Library/Kumbia/ActiveRecord/Validators/'.$className.'.php';
			} else {
				$application = Router::getApplication();
				if(Core::fileExists('apps/'.$application.'/validators/'.$className.'.php')){
					require KEF_ABS_PATH.'apps/'.$application.'/validators/'.$className.'.php';
				}
			}
		}
		#if[compile-time]
		if(class_exists($className, false)==false){
			throw new ActiveRecordException("No se encontró el validador de entidades '$className'");
		}
		if(!in_array('ActiveRecordValidatorInterface', class_implements($className))){
			throw new ActiveRecordException("La clase validador '$className' debe implementar la interface ActiveRecordValidatorInteface");
		}
		#endif
		if(is_array($options)){
			if(!isset($options['field'])){
				throw new ActiveRecordException("No ha indicado el campo a validar para '$className'");
			} else {
				$field = $options['field'];
			}
		} else {
			if($options==''){
				throw new ActiveRecordException("No ha indicado el campo a validar para '$className'");
			} else {
				$field = $options;
			}
		}
		if(!is_array($field)){
			#if(!isset($this->$field)){
			#	throw new ActiveRecordException("No se puede validar el campo '$field' por que no esta presente en la entidad");
			#}
			$this->_executeValidator($className, $field, $options);
		} else {
			foreach($field as $singleField){
				if(!isset($this->$singleField)){
					throw new ActiveRecordException("No se puede validar el campo '$singleField' por que no esta presente en la entidad");
				}
			}
			$this->_executeValidator($className, $field, $options);
		}
	}

	/**
	 * Permite saber si el proceso de validación ha generado mensajes
	 *
	 * @return boolean
	 */
	public function validationHasFailed(){
		if(count($this->_errorMessages)>0){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Verifica si un campo es de tipo de dato numérico o no
	 *
	 * @param	string $field
	 * @return	boolean
	 */
	public function isANumericType($field){
		$dataTypeNumeric = $this->_getDataTypeNumeric();
		if(isset($dataTypeNumeric[$field])){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Crea una relación 1-1 entre dos modelos
	 *
	 * @access	protected
	 * @param	string $relation
	 */
	protected function hasOne($fields='', $referenceTable='', $referencedFields=''){
		EntityManager::addHasOne(get_class($this), $fields, $referenceTable, $referencedFields);
	}

	/**
	 * Crea una relación 1-1 inversa entre dos entidades
	 *
	 * @param	mixed $fields
	 * @param	string $referenceTable
	 * @param	string $referencedFields
	 * @param	string $relationName
	 */
	protected function belongsTo($fields='', $referenceTable='', $referencedFields='', $relationName=''){
		EntityManager::addBelongsTo(get_class($this), $fields, $referenceTable, $referencedFields, $relationName);
	}

	/**
	 * Crea una relación 1-n entre dos entidades
	 *
	 * @param	mixed $fields
	 * @param	string $referenceTable
	 * @param	string $referencedFields
	 */
	protected function hasMany($fields='', $referenceTable='', $referencedFields=''){
		EntityManager::addHasMany(get_class($this), $fields, $referenceTable, $referencedFields);
	}

	/**
	 * Crea una relación n-m entre dos modelos
	 *
	 * @param string $relation
	 */
	protected function hasAndBelongsToMany($relation){
		/*$relations =  func_get_args();
		foreach($relations as $relation){
			if(!in_array($relation, $this->_hasAndBelongsToMany)){
				$this->_hasAndBelongsToMany[] = $relation;
			}
		}*/
	}

	/**
	 * Agrega una llave primaria
	 *
	 * @param	mixed $fields
	 * @param	string $referencedTable
	 * @param	mixed $referencedFields
	 * @param	array $options
	 */
	protected function addForeignKey($fields, $referencedTable='', $referencedFields='', $options=array()){
		EntityManager::addForeignKey(get_class($this), $fields, $referencedTable, $referencedFields, $options);
	}

	/**
	 * Establece que un campo no debe ser persistido
	 *
	 * @param	string $attribute
	 */
	public function setTrasient($attribute){
		EntityManager::addTrasientAttribute(get_class($this), $attribute);
	}

	/**
	 * Forza a que la entidad existe y evita su comprobación
	 *
	 * @param bool $forceExists
	 */
	public function setForceExists($forceExists){
		$this->_forceExists = $forceExists;
	}

	/**
	 * Herencia Simple
	 */

	/**
	 * Especifica que la clase es padre de otra
	 *
	 * @param string $parent
	 */
	public function parentOf($parent){
		/*$parents = func_get_args();
		foreach($parents as $parent){
			if(!in_array($parent, $this->_parentOf)){
				$this->_parentOf[] = $parent;
			}
		}*/
	}

	/**
	 * Reescribiendo este método se puede controlar las excepciones generadas en los modelos
	 *
	 * @param	Exception $e
	 * @throws	Exception
	 */
	public function exceptions($e){
		throw $e;
	}

	/**
	 * Establece si los eventos de validacion estan activos o no
	 *
	 * @param boolean $disableEvents
	 */
	public static function disableEvents($disableEvents){
		self::$_disableEvents = $disableEvents;
	}

	/**
	 * Establece si se deben refrescar el registro al insertar
	 *
	 * @param boolean $refreshPersistance
	 */
	public static function refreshPersistance($refreshPersistance){
		self::$_refreshPersistance = $refreshPersistance;
	}

	/**
	 * Indica si los eventos de validacion estan activos o no
	 *
	 * @param boolean $disableEvents
	 */
	public static function getDisableEvents(){
		return self::$_disableEvents;
	}

	/**
	 * Devuelve si se debe refrescar el registro al insertar
	 *
	 * @return boolean
	 */
	public static function getRefreshPersistance(){
		return self::$_refreshPersistance;
	}

	/**
	 * Valida que los valores que sean leidos del objeto ActiveRecord esten definidos
	 * previamente o sean atributos de la entidad
	 *
	 * @access	public
	 * @param	string $property
	 * @throws	ActiveRecordException
	 */
	public function __get($property){
		$this->_connect();
		if($this->_dumpLock==false){
			if(!isset($this->$property)){
				throw new ActiveRecordException("Propiedad indefinida '$property' leida de el modelo '$this->_source'");
			} else {
				try {
					$reflectorProperty = new ReflectionProperty(get_class($this), $property);
					if($reflectorProperty->isPublic()==false){
						throw new ActiveRecordException("Propiedad protegida ó privada '$property' leida de el modelo '$this->_source' ");
					}
				}
				catch(Exception $e){
					if($e instanceof ActiveRecordException){
						throw $e;
					}
				}
			}
		}
		return null;
	}

	/**
	 * Valida que los valores que sean asignados al objeto ActiveRecord esten definidos
	 * o sean atributos de la entidad
	 *
	 * @param	string $property
	 * @param	mixed $value
	 * @throws	ActiveRecordException
	 */
	public function __set($property, $value){
		$this->_connect();
		if($this->_dumpLock==false){
			if(isset($this->$property)==false){
				throw new ActiveRecordException("La propiedad '$property' no existe en la entidad '".get_class($this)."'");
			}
		}
		$this->$property = $value;
	}

	/**
	 * Valida los llamados a los métodos del modelo cuando se llame un método que no exista
	 *
	 * @param	string $method
	 * @param	array $arguments
	 * @return	mixed
	 * @throws	ActiveRecordException
	 */
	public function __call($method, $arguments = array()){
		return $this->_handleInaccessibleCall($this, $method, $arguments);
	}

	/**
	 * Valida los llamados a los métodos estaticos del modelo cuando se llame un método no exista
	 *
	 * @param	string $method
	 * @param	array $arguments
	 * @return	mixed
	 * @throws	ActiveRecordException
	 */
	public static function __callStatic($method, $arguments=array()){
		$object = EntityManager::getEntityInstance(get_called_class());
		return $object->_handleInaccessibleCall($object, $method, $arguments);
	}

	/**
	 * Ejecuta y valida metodos inaccesibles llamados estaticamente o no
	 *
	 * @param	ActiveRecordBase $object
	 * @param	string $method
	 * @param	array $arguments
	 * @return	ActiveRecordResultset
	 */
	protected function _handleInaccessibleCall($object, $method, $arguments=array()){
		$object->_connect();
		$entityName = get_class($object);

		if(substr($method, 0, 3)=='get'){
			$requestedRelation = ucfirst(substr($method, 3));
			if(EntityManager::existsBelongsTo($entityName, $requestedRelation)==true){
				$entityArguments = array('findFirst', $entityName, $requestedRelation, $object);
				return call_user_func_array(array('EntityManager', 'getBelongsToRecords'), array_merge($entityArguments, $arguments));
			}
			if(EntityManager::existsHasMany($entityName, $requestedRelation)==true){
				$entityArguments = array('find', $entityName, $requestedRelation, $object);
				return call_user_func_array(array('EntityManager', 'getHasManyRecords'), array_merge($entityArguments, $arguments));
			}
			if(EntityManager::existsHasOne($entityName, $requestedRelation)==true){
				$entityArguments = array('findFirst', $entityName, $requestedRelation, $object);
				return call_user_func_array(array('EntityManager', 'getHasOneRecords'), array_merge($entityArguments, $arguments));
			}
		}

		if(substr($method, 0, 5)=='count'){
			$requestedRelation = ucfirst(substr($method, 5));
			if(EntityManager::existsBelongsTo($entityName, $requestedRelation)==true){
				$entityArguments = array('count', $entityName, $requestedRelation, $object);
				return call_user_func_array(array('EntityManager', 'getBelongsToRecords'), array_merge($entityArguments, $arguments));
			}
			if(EntityManager::existsHasMany($entityName, $requestedRelation)==true){
				$entityArguments = array('count', $entityName, $requestedRelation, $object);
				return call_user_func_array(array('EntityManager', 'getHasManyRecords'), array_merge($entityArguments, $arguments));
			}
			if(EntityManager::existsHasOne($entityName, $requestedRelation)==true){
				return EntityManager::getHasOneRecords('count', $entityName, $requestedRelation, $object);
			}
		}

		if(substr($method, 0, 6)=='findBy'){
			$field = Utils::uncamelize(Utils::lcfirst(substr($method, 6)));
			if (isset($arguments[0])) {
				$argument = array('conditions' => $field.' = '.$object->_db->addQuotes($arguments[0]));
				unset($arguments[0]);
			} else {
				$argument = array();
			}
			return call_user_func_array(array($object, 'findFirst'), array_merge($argument, $arguments));
		}

		if(substr($method, 0, 7)=='countBy'){
			$field = Utils::uncamelize(Utils::lcfirst(substr($method, 7)));
			if (isset($arguments[0])) {
				$argument = array($field.' = '.$object->_db->addQuotes($arguments[0]));
				unset($arguments[0]);
			} else {
				$argument = array();
			}
			return call_user_func_array(array($object, 'count'), array_merge($argument, $arguments));
		}

		if(substr($method, 0, 9)=='findAllBy'){
			$field = Utils::uncamelize(Utils::lcfirst(substr($method, 9)));
			if (isset($arguments[0])) {
				$argument = array($field.' = '.$object->_db->addQuotes($arguments[0]));
				unset($arguments[0]);
			} else {
				$argument = array();
			}
			return call_user_func_array(array($object, 'find'), array_merge($argument, $arguments));
		}

		if(substr($method, 0, 3)=='new'){
			$requestedRelation = ucfirst(substr($method, 3));
			if(EntityManager::existsHasMany($entityName, $requestedRelation)==true){
				$definition = EntityManager::getHasManyDefinition($entityName, $requestedRelation);
				$entity = EntityManager::getEntityInstance($requestedRelation, true);
				if(!is_array($definition['fields'])){
					$entity->writeAttribute($definition['fields'], $this->readAttribute($definition['referencedFields']));
				}
				return $entity;
			}
			if(EntityManager::existsHasOne($entityName, $requestedRelation)==true){
				$entity = EntityManager::getEntityInstance($entityName);
			}
		}

		if(substr($method, 0, 8)=='sanizite'){
			$fieldName = Utils::uncamelize(Utils::lcfirst(substr($method, 8)));
			array_unshift($arguments, get_class($object));
			return call_user_func_array(array('ActiveRecordUtils', 'saniziteByDataType'), $arguments);
		}

		throw new ActiveRecordException('No se encontró el método "'.$method.'" en el modelo "'.get_class($object).'"');
	}

	/**
	 * Une una tabla y genera los resultados
	 *
	 * @param 	array $entities
	 * @return	ActiveRecordJoin
	 */
	public static function join($entities){
		$entities = func_get_args();
		array_unshift($entities, get_called_class());
		return new ActiveRecordJoin(array('entities' => $entities));
	}

	/**
	 * Crea una instancia de una entidad apartir de un objeto
	 *
	 * @param	array $data
	 * @param	array $except
	 * @return	ActiveRecordBase
	 */
	public static function factory($data, $except=array()){
		if(is_array($data)){
			$entityName = get_called_class();
			$entity = EntityManager::get($entityName, true);
			$dataTypes = $entity->getDataTypes();
			foreach($data as $key => $value){
				$key = Utils::uncamelize($key);
				if(!in_array($key, $except)){
					if(isset($dataTypes[$key])){
						$value = ActiveRecordUtils::saniziteByDataType($entityName, $key, $value);
						$entity->writeAttribute($key, $value);
					}
				}
			}
			return $entity;
		} else {
			throw new ActiveRecordException('Factory requiere un array como parámetro');
		}
	}

	/**
	 * Sanea un dato de acuerdo al metadato del campo en la entidad
	 *
	 * @param	string $fieldName
	 * @param	mixed $value
	 * @return	string
	 */
	public static function sanizite($fieldName, $value){
		return ActiveRecordUtils::saniziteByDataType(get_called_class(), $fieldName, $value);
	}

	/**
	 * Método mágico al clonar el record, el nuevo objeto clonado contiene los mismos datos del original
	 * pero desconociendo si existe o no en la persistencia
	 *
	 */
	public function __clone(){
		$this->_forceExists = false;
		$this->_wherePk = false;
		$this->_dumped = false;
	}

}
