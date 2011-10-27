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
 * @category 	Kumbia
 * @package 	Transactions
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright  	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierezandresfelipe at gmail.com)
 * @license 	New BSD License
 */

/**
 * TransactionDefinition
 *
 * Permite crear una definicion para una transaccion
 *
 * @category 	Kumbia
 * @package 	Transactions
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright  	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @access 		public
 */
class TransactionDefinition extends Object {

	/**
	 * Indica si se debe crear una nueva conexion o utilizar la existente
	 *
	 * @var boolean
	 */
	private $_createConnection = true;

	/**
	 * Nivel de Isolacion del gestor relacional
	 *
	 * @var integer
	 */
	private $_isolationLevel = 0;

	/**
	 * Indica si la transaccion se debe propagar a otras transacciones activas
	 *
	 * @var boolean
	 */
	private $_propagation = false;

	/**
	 * Indica si la transaccion sera de solo lectura
	 *
	 * @var boolean
	 */
	private $_readOnly = false;

	/**
	 * Indica el timeout de la transaccion
	 *
	 * @var integer
	 */
	private $_timeout = 0;

	//Contantes de Isolacion
	const ISOLATION_DEFAULT = 0;
	const ISOLATION_READ_COMMITED = 1;
	const ISOLATION_READ_UNCOMMITED = 2;
	const ISOLATION_REPETEABLE_READ = 3;
	const ISOLATION_SERIALIZABLE = 4;

	/**
	 * Permite establecer el nivel de Isolation usado por el gestor
	 *
	 * @param integer $isolationLevel
	 */
	public function setIsolationLevel($isolationLevel){
		if(!in_array($isolationLevel, range(0, 4))){
			throw new TransactionException("Nivel de isolaci&oacute;n ($isolationLevel) indefinido");
		}
		$this->_isolationLevel = $isolationLevel;
	}

	/**
	 * Devuelve el nivel de isolacion
	 *
	 * @return boolean
	 */
	public function getIsolationLevel(){
		return $this->_isolationLevel;
	}

	/**
	 * Establece la progragacion de la transaccion
	 *
	 * @param boolean $propagation
	 */
	public function setPropagation($propagation){
		$this->_propagation = (bool) $propagation;
	}

	/**
	 * Devuelve si el rollback se debe propagar a las demas transacciones
	 *
	 * @return boolean
	 */
	public function getPropagation(){
		return $this->_propagation;
	}

	/**
	 * Establece el timeout de la transaccion
	 *
	 * @param integer $timeout
	 */
	public function setTimeout($timeout){
		$this->_timeout = (int) $timeout;
	}

	/**
	 * Devuelve el tiempo en segundos maximo que puede durar una transaccion
	 *
	 * @return int
	 */
	public function getTimeout(){
		return $this->_timeout;
	}

	/**
	 * Permite establecer si la transaccion sera de solo lectura
	 *
	 * @param bool $readOnly
	 */
	public function setReadOnly($readOnly){
		$this->_readOnly = (bool) $readOnly;
	}

	/**
	 * Devuelve si la transaccion es de solo lectura
	 *
	 * @return bool
	 */
	public function getReadOnly(){
		return $this->_readOnly;
	}

	/**
	 * Establece si se debe crear una nueva conexion al crear la transaccion
	 *
	 * @param bool $createConnection
	 */
	public function setCreateConnection($createConnection){
		$this->_createConnection = $createConnection;
	}

	/**
	 * Devuelve si se debe crear una nueva conexion al crear la transaccion
	 *
	 * @return bool
	 */
	public function getCreateConnection(){
		return $this->_createConnection;
	}

}
