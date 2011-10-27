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
 * @subpackage	ActiveRecordTransaction
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ActiveRecordTransaction.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * ActiveRecordTransaction
 *
 * Permite crear una transaccion asociando varios objetos ActiveRecord en una misma conexión
 *
 * @package		ActiveRecord
 * @subpackage	ActiveRecordTransaction
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class ActiveRecordTransaction {

	/**
	 * Conexion que mantiene la Transaccion
	 *
	 * @var DbBase
	 */
	private $_db;

	/**
	 * Indica si hay una transaccion activa
	 *
	 */
	private $_activeTransaction = false;

	/**
	 * Indica si la transaccion esta recien creada o es reutilizada
	 *
	 * @var boolean
	 */
	private $_isNewTransaction = true;

	/**
	 * Indica si la transaccion es propagable
	 *
	 * @var boolean
	 */
	private $_propagation = false;

	/**
	 * Indica si la transacción debe abortarse cuando el cliente aborta la petición
	 *
	 * @var boolean
	 */
	private $_rollbackOnAbort = false;

	/**
	 * Administrador de transacciones usado
	 *
	 * @var string
	 */
	private $_manager;

	/**
	 * Puntero para ubicar el objeto en la lista de dependencias
	 *
	 * @var int
	 */
	private $_pointer = 0xFF;

	/**
	 * Objetos dependientes de la transaccion
	 *
	 * @var array
	 */
	private $_dependencies = array();

	/**
	 * Mensajes de validación de la transacción
	 *
	 * @var array
	 */
	private $_messages = array();

	/**
	 * Registro que produjo el rollback
	 *
	 * @var ActiveRecordBase
	 */
	private $_rollbackRecord = null;

	/**
	 * Constructor de la Transaccion
	 *
	 * @param boolean $autoBegin
	 */
	public function __construct($autoBegin=false, $definition=''){
		if(is_object($definition)){
			if($definition instanceof TransactionDefinition){
				$connection = DbPool::getConnection($definition->getCreateConnection());
				$this->_db = $connection;
				$isolationLevel = $definition->getIsolationLevel();
				if($isolationLevel!=TransactionDefinition::ISOLATION_DEFAULT){
					$this->_db->setIsolationLevel($isolationLevel);
				}
				$this->_db->setReadOnly($definition->getReadOnly());
				$this->_db->setTimeout($definition->getTimeout());
				$this->setPropagation($definition->getPropagation());
			} else {
				throw new ActiveRecordTransactionException("Definición de transacción invalida");
			}
		} else {
			$connection = DbPool::getConnection(true);
			$this->_db = $connection;
		}
		if($autoBegin==true){
			$this->_db->begin();
		}
	}

	/**
	 * Establece el admnistrador de Transacciones Utilizado
	 *
	 * @param string $manager
	 */
	public function setTransactionManager($manager){
		$this->_manager = $manager;
	}

	/**
	 * Start a transaction in RDBM
	 *
	 * @return boolean
	 */
	public function begin(){
		return $this->_db->begin();
	}

	/**
	 * Commit a Transaction
	 *
	 * @return boolean
	 */
	public function commit(){
		if($this->_manager!=''){
			call_user_func_array(array($this->_manager, 'notifyCommit'), array($this));
		}
		return $this->_db->commit();
	}

	/**
	 * Rollback a Transaction
	 *
	 * @param 	string $rollbackMessage
	 * @param 	ActiveRecordBase $rollbackRecord
	 * @throws 	TransactionFailed
	 * @return 	boolean
	 */
	public function rollback($rollbackMessage='', $rollbackRecord=null){
		if($this->_manager!=''){
			call_user_func_array(array($this->_manager, 'notifyRollback'), array($this));
		}
		$success = $this->_db->rollback();
		if($success==true){
			if($rollbackMessage==''){
				$rollbackMessage = 'Transacción abortada';
			}
			if($rollbackRecord!==null){
				$this->_rollbackRecord = $rollbackRecord;
			}
			throw new TransactionFailed($rollbackMessage, $this->_rollbackRecord);
		}
	}

	/**
	 * Devuelve la conexion que maneja la transacción
	 *
	 * @return DbBase
	 */
	public function getConnection(){
		if($this->_rollbackOnAbort==true){
			if(connection_aborted()){
				$this->rollback('La petición fue abortada');
			}
		}
		return $this->_db;
	}

	/**
	 * Indica si la transaccion se esta reutilizando
	 *
	 * @param boolean $isNew
	 */
	public function setIsNewTransaction($isNew){
		$this->_isNewTransaction = $isNew;
	}

	/**
	 * Establece si la transaccion es propagable
	 *
	 * @param boolean $propagation
	 */
	protected function setPropagation($propagation){
		$this->_propagation = $propagation;
	}

	/**
	 * Devuelve si la transaccion es propagable
	 *
	 * @return bool
	 */
	public function getPropagation(){
		return $this->_propagation;
	}

	/**
	 * Establece si se debe anular la transacción cuando el cliente aborte la petición
	 *
	 * @param boolean $rollbackOnAbort
	 */
	public function setRollbackOnAbort($rollbackOnAbort){
		$this->_rollbackOnAbort = $rollbackOnAbort;
	}

	/**
	 * Indica si la transaccion es administrada
	 *
	 * @return boolean
	 */
	public function isManaged(){
		return ($this->_manager==null) ? false : true;
	}

	/**
	 * Establece el puntero de control de dependencias
	 *
	 * @param int $pointer
	 */
	public function setDependencyPointer($pointer){
		$this->_pointer = $pointer;
	}

	/**
	 * Agrega un objeto dependiente de la transacción
	 *
	 * @param 	int $pointer
	 * @param	ActiveRecordBase $object
	 */
	public function attachDependency($pointer, ActiveRecordBase $object){
		if($pointer==null){
			$pointer = ++$this->_pointer;
			$this->_dependencies[$pointer] = $object;
			return $pointer;
		} else {
			if(!isset($this->_dependencies[$pointer])){
				$this->_dependencies[$pointer] = $object;
				return $pointer;
			} else {
				$pointer = ++$this->_pointer;
				$this->_dependencies[$pointer] = $object;
				return $pointer;
			}
		}
	}

	/**
	 * Guarda todos los objetos asociados a la transacción
	 *
	 * @return boolean
	 */
	public function save(){
		$this->_messages = array();
		foreach($this->_dependencies as $dependency){
			if($dependency->save()==false){
				$this->_messages = $dependency->getMessages();
				return false;
			}
		}
		return true;
	}

	/**
	 * Devuelve mensajes de validación si save falla
	 *
	 * @return array
	 */
	public function getMessages(){
		return $this->_messages;
	}

	/**
	 * Indica si aún no se ha hecho commit ó rollback a la transacción
	 *
	 * @return boolean
	 */
	public function isValid(){
		return $this->_db->isUnderTransaction();
	}

	/**
	 * Registro que produjo el rollback
	 *
	 * @param ActiveRecordBase $record
	 */
	public function setRollbackedRecord($record){
		$this->_rollbackRecord = $record;
	}


}
