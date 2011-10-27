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
 * @version 	$Id: TransactionFailed.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * TransactionFailed
 *
 * Excepcion lanzada cuando falla la transacción
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	ActiveRecordTransaction
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class TransactionFailed extends Exception {

	/**
	 * Record que produjo que la transacción fallara
	 *
	 * @var TransactionFailed
	 */
	private $_record = null;

	/**
	 * Constructor de TransactionFailed
	 *
	 * @param string $message
	 * @param ActiveRecordBase $record
	 */
	public function __construct($message, $record){
		$this->_record = $record;
		parent::__construct($message, 0);
	}

	/**
	 * Devuelve los mensajes generados por
	 *
	 * @return string
	 */
	public function getRecordMessages(){
		if($this->_record!==null){
			return $this->_record->getMessages();
		} else {
			return $this->_getMessage();
		}
	}

	/**
	 * Devuelve el record que produjo que la transaccion fallara
	 *
	 * @return ActiveRecordBase
	 */
	public function getRecord(){
		return $this->_record;
	}

}
