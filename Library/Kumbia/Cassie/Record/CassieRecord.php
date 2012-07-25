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
 * @package		Cassie
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id$
 */

require KEF_ABS_PATH.'Library/Kumbia/Cassie/Generator/Uuid.php';

class CassieRecord implements EntityInterface {

	/**
	 * Objeto DbBase de conexión a Cassandra
	 *
	 * @var DbCassandra
	 */
	protected $_db = '';

	/**
	 * Indica si al conectarse debe usar la base de datos predeterminada
	 *
	 * @var boolean
	 */
	protected $_defaultConnection = true;

	/**
	 * Nombre de la conexión que debe usar el modelo para conectarse a Cassandra
	 *
	 * @var string
	 */
	protected $_connectionName;

	/**
	 * Datos Clave-Valor del modelo
	 *
	 * @var array
	 */
	protected $_data = array(
		'id' => null
	);

	/**
	 * Nombre del KeySpace del Modelo
	 *
	 * @var string
	 */
	protected $_keySpace = null;

	/**
	 * Nombre del UserKey del registro
	 *
	 * @var string
	 */
	protected $_userKey = null;

	/**
	 * Nombre de la SuperColumna relacionada a este Modelo
	 *
	 * @var string
	 */
	private $_source;

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
     * Establece el nombre de la conexión que debe usarse en el modelo
     *
     * @param string $name
     */
    protected function setConnectionName($name){
		$this->_defaultConnection = false;
		$this->_connectionName = $name;
    }

	/**
	 * Se conecta a la base de datos y descarga los meta-datos si es necesario
	 *
	 * @param	boolean $newConnection
	 * @access	protected
	 */
	protected function _connect($newConnection=false){
		if($newConnection||$this->_db===''){
			if($this->_defaultConnection==true){
				$this->_db = DbPool::getConnection($newConnection);
			} else {
				$this->_db = DbLoader::factoryFromName($this->_connectionName);
			}
			/*if($this->_debug==true){
				$this->_db->setDebug($this->_debug);
			}
			if($this->_logger!=false){
				$this->_db->setLogger($this->_logger);
			}*/
		}
	}

	/**
	 * Establece el KeySpace por defecto del modelo
	 *
	 * @param string $keySpace
	 */
	public function setKeyspace($keySpace){
		$this->_keySpace = $keySpace;
	}

	/**
	 * Establce el ColumnFamily por defecto del modelo
	 *
	 * @param string $columnFamily
	 */
	public function setColumnFamily($columnFamily){
		$this->_columnFamily = $columnFamily;
	}

	/**
	 * Almacena un dato clave-valor en un array interno
	 *
	 * @param	string $key
	 * @param	string $value
	 */
	public function __set($key, $value){
		$this->_data[$key] = $value;
	}

	/**
	 * Devuelve un dato por su clave que esté previamente definido
	 *
	 * @param	string $key
	 * @return 	string
	 */
	public function __get($key){
		if(isset($this->_data[$key])){
			return $this->_data[$key];
		} else {
			throw new CassieRecordException('Accediendo a atributo indefinido "'.$key.'"');
		}
	}

	public function count($params=''){
		$this->_connect();
		if($params===''){
			return $this->_db->countSuperKey($this->_keySpace, 'Schema', $this->_source);
		} else {
			$primaryIndex = $this->_source.'$id$'.$params;
			$rowIndex = $this->_db->getKey($this->_keySpace, 'Indexes', $primaryIndex);
			if($rowIndex!==false){
				return $this->dumpResult($rowIndex);
			} else {
				return false;
			}
		}
	}

	public function find($params=''){
		$this->_connect();
		if($params===''){
			$columnList = $this->_db->findSuperKey($this->_keySpace, 'Schema', $this->_source);
			return new CassieRecordResultset($this, &$columnList);
		} else {
			$primaryIndex = $this->_source.'$id$'.$params;
			$rowData = $this->_db->getKey($this->_keySpace, 'Indexes', $primaryIndex);
			if($rowData!==false){
				return new CassieRecordResultset($this, array($rowData));
			} else {
				return new CassieRecordResultset(null, array());
			}
		}
	}

	public function findFirst($params=''){
		$this->_connect();
		if($params===''){
			$rowIndex = $this->_db->findSuperKey($this->_keySpace, 'Schema', $this->_source, array(
				'count' => 1
			));
			if(count($rowIndex)>0){
				return $this->dumpResult($rowIndex[0]);
			} else {
				return false;
			}
		} else {
			$primaryIndex = $this->_source.'$id$'.$params;
			$rowIndex = $this->_db->getKey($this->_keySpace, 'Indexes', $primaryIndex);
			if($rowIndex!==false){
				return $this->dumpResult($rowIndex);
			} else {
				return false;
			}
		}
	}

	public function setUserKey($userKey){
		$this->_userKey = $userKey;
	}

	private function _getDataByUuid($uuid){
		$rowData = $this->_db->getKey($this->_keySpace, 'Data', $uuid, true);
		if($rowData!==false){
			return $rowData;
		} else {
			throw new CassieRecordException('No existe el registro con el UUID indicado');
		}
	}

	public function dumpResult($columnOrSuper){
		$record = clone $this;
		if($columnOrSuper->super_column!==null){
			$columnUuid = $columnOrSuper->super_column->columns[0];
		} else {
			$columnUuid = $columnOrSuper->column;
		}
		$superColumnData = $this->_getDataByUuid($columnUuid->value);
		foreach($superColumnData->super_column->columns as $column){
			$record->writeAttribute($column->name, $column->value);
		}
		$record->setUserKey($columnUuid->value);
		return $record;
	}

	public function writeAttribute($attribute, $value){
		$this->_data[$attribute] = $value;
	}

	public function exists($id){
		$this->_connect();
	}

	protected function _exists($primaryKey){
		$indexName = $this->_source.'$id$'.$primaryKey;
		$rowKey = $this->_db->getKey($this->_keySpace, 'Indexes', $indexName);
		if($rowKey!==false){
			$this->_userKey = $rowKey->column->value;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Almacena el contexto clave-valor de un objeto
	 *
	 * @return boolean
	 */
	public function save(){
		$this->_connect();
		$exists = false;
		$insertData = array();
		if($this->_userKey===null){
			if($this->_data['id']!==null){
				$this->_exists($this->_data['id']);
			} else {
				$keyName = $this->_source.'#auto#id';
				$rowKey = $this->_db->getKey($this->_keySpace, 'Indexes', $keyName);
				if($rowKey!==false){
					$this->_data['id'] = $rowKey->column->value;
				} else {
					$this->_data['id'] = 0;
				}
				$this->_data['id']++;
				$insertData['Indexes'][$keyName] = $this->_data['id'];
			}
			if($this->_userKey===null){
				$userKey = UuidCassieGenerator::generate();
			} else {
				$userKey = $this->_userKey;
			}
		} else {
			$userKey = $this->_userKey;
		}

		$insertSchema = array();
		$insertSchema['Schema'][$this->_source][$this->_data['id']] = $userKey;
		$this->_db->insertSuperKey(
			$this->_keySpace,
			$insertSchema,
			cassandra_ConsistencyLevel::ONE
		);
		$indexName = $this->_source.'$id$'.$this->_data['id'];
		$insertData['Data'][$userKey] = $this->_data;
		$insertData['Indexes'][$indexName] = $userKey;

		$this->_db->insertKey(
			$this->_keySpace,
			$insertData,
			cassandra_ConsistencyLevel::ONE
		);
		return true;
	}

	public function getMessages(){
		return array();
	}

	public function delete(){
		if($this->_userKey===null){
			throw new CassieRecordException('El modelo no ha sido inicializado (dirty-state)');
		} else {
			$this->_db->removeKey(
				$this->_keySpace,
				'Data',
				$this->_userKey,
				cassandra_ConsistencyLevel::ONE
			);
			$indexName = $this->_source.'$id$'.$this->_data['id'];
			$this->_db->removeKey(
				$this->_keySpace,
				'Indexes',
				$indexName,
				cassandra_ConsistencyLevel::ONE
			);
			$this->_db->removeSuperKey(
				$this->_keySpace,
				'Schema',
				$this->_source,
				$this->_data['id'],
				cassandra_ConsistencyLevel::ONE
			);

		}
	}

	public function setSource($source){
		$this->_source = $source;
	}

	public function getSource(){
		if(Facility::getFacility()!=Facility::USER_LEVEL){
			if($this->_source==''){
				$this->_source = get_class($this);
			}
		}
		return $this->_source;
	}

	public function setSchema($schema){
		$this->setKeySpace($schema);
	}

	public function getSchema(){
		return $this->_keySpace;
	}

	public function getConnection(){
		return $this->_db;
	}

}