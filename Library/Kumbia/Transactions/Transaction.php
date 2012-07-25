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
 * @package		Transactions
 * @copyright	Copyright (c) 2008-2012 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: ActiveRecordTransaction.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * Transaction
 *
 * Create a transaction involving multiple concurrent controls and other related actions
 *
 * @category	Kumbia
 * @package		Transactions
 * @copyright	Copyright (c) 2008-2012 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */
class Transaction {

	/**
	 * Conexion que mantiene la Transaccion
	 *
	 * @var DbBase
	 */
	private $_connection;

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
				$this->_connection = $connection;
				$isolationLevel = $definition->getIsolationLevel();
				if($isolationLevel!=TransactionDefinition::ISOLATION_DEFAULT){
					$this->_connection->setIsolationLevel($isolationLevel);
				}
				$this->_connection->setReadOnly($definition->getReadOnly());
				$this->_connection->setTimeout($definition->getTimeout());
				$this->setPropagation($definition->getPropagation());
			} else {
				throw new TransactionException("Invalid transaction definition");
			}
		} else {
			$connection = DbPool::getConnection(true);
			$this->_connection = $connection;
		}
		if($autoBegin==true){
			$this->_connection->begin();
		}
	}

	/**
	 * Sets the transaction manager
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
		return $this->_connection->begin();
	}

	/**
	 * Commits a Transaction
	 *
	 * @return boolean
	 */
	public function commit(){
		if($this->_manager!=''){
			call_user_func_array(array($this->_manager, 'notifyCommit'), array($this));
		}
		foreach($this->_dependencies as $dependency){
			$dependency->onCommit();
		}
		return $this->_connection->commit();
	}

	/**
	 * Realiza un rollback no progagable
	 *
	 * @return boolean
	 */
	public function noPropagableRollback(){
		foreach($this->_dependencies as $dependency){
			$dependency->onRollback();
		}
		return $this->_connection->rollback();
	}

	/**
	 * Rollbacks a Transaction
	 *
	 * @param 	string $rollbackMessage
	 * @param 	ActiveRecordBase $rollbackRecord
	 * @throws 	TransactionFailed
	 * @return 	boolean
	 */
	public function rollback($message='', $code=0, $record=null){
		if($this->_manager!=''){
			call_user_func_array(array($this->_manager, 'notifyRollback'), array($this));
		}
		$success = $this->noPropagableRollback();
		if($success==true){
			if($message==''){
				$message = 'Transacción abortada';
			}
			if($record!==null){
				$this->_rollbackRecord = $record;
			}
			throw new TransactionFailed($message, $code, $this->_rollbackRecord);
		}
	}

	/**
	 * Returns the connection handler for a transaction
	 *
	 * @return DbBase
	 */
	public function getConnection(){
		if($this->_rollbackOnAbort==true){
			if(connection_aborted()){
				$this->rollback('The request was aborted');
			}
		}
		return $this->_connection;
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
	 * Agrega un item dependiente de la transacción
	 *
	 * @param	int $pointer
	 * @param	object $element
	 * @return	int
	 */
	private function _attachDependency($pointer, $element){
		if($pointer==null){
			$pointer = ++$this->_pointer;
			$this->_dependencies[$pointer] = $element;
			return $pointer;
		} else {
			if(!isset($this->_dependencies[$pointer])){
				$this->_dependencies[$pointer] = $element;
				return $pointer;
			} else {
				$pointer = ++$this->_pointer;
				$this->_dependencies[$pointer] = $element;
				return $pointer;
			}
		}
	}

	/**
	 * Agrega un objeto dependiente de la transacción
	 *
	 * @param 	int $pointer
	 * @param	ActiveRecordBase $object
	 * @return 	int
	 */
	public function attachRecordDependency($pointer, ActiveRecordBase $object){
		return $this->_attachDependency($pointer, $object);
	}

	/**
	 * Agrega un service dependiente de la transacción
	 *
	 * @param 	int $pointer
	 * @param	ServiceConsumer $service
	 * @return 	int
	 */
	public function attachServiceDependency($pointer, ServiceConsumer $service){
		return $this->_attachDependency($pointer, $service);
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
		return $this->_connection->isUnderTransaction();
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
