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
 * @subpackage	Generator
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Native.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * NativeGenerator
 *
 * No realiza ninguna accion y deja que el motor asigne el consecutivo
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	Generator
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class NativeGenerator implements ActiveRecordGeneratorInterface {

	/**
	 * Columna del objeto que es identidad
	 *
	 * @var string
	 */
	private $_identityColumn;

	/**
	 * Establece las opciones del generador
	 *
	 * @param array $options
	 */
	public function setOptions($options){

	}

	/**
	 * Establece el nombre de la columna identidad
	 *
	 * @param string $identityColumn
	 */
	public function setIdentityColumn($identityColumn){
		$this->_identityColumn = $identityColumn;
	}

	/**
	 * Objeto que solicita el identificador
	 *
	 * @param ActiveRecordBase $record
	 */
	public function setIdentifier($record){

	}

	/**
	 * Actualiza el consecutivo en la BD
	 *
	 * @param ActiveRecordBase $record
	 * @return boolean
	 */
	public function updateConsecutive($record){
		if(method_exists($record, 'sequenceName')){
			$sequenceName = $record->sequenceName();
			$connection = $record->getConnection();
			if($record->getSchema()==''){
				$table = $record->getSource();
			} else {
				$table = $record->getSchema().'.'.$record->getSource();
			}
			$lastId = $connection->lastInsertId($table, $this->_identityColumn, $sequenceName);
			$record->writeAttribute($this->_identityColumn, $lastId);
			$record->findFirst($lastId);
		}
		return true;
	}

	/**
	 * Finaliza el generador
	 *
	 */
	public function finalizeConsecutive(){

	}

}
