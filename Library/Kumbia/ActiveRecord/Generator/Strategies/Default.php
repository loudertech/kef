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
 * @license		New BSD License
 * @version 	$Id: Native.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * DefaultGenerator
 *
 * Asigna el valor DEFAULT para que el motor asigne el valor de la secuencia
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	Generator
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 */
class DefaultGenerator implements ActiveRecordGeneratorInterface {

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
		$record->writeAttribute($this->_identityColumn, 'DEFAULT');
	}

	/**
	 * Actualiza el consecutivo en la BD
	 *
	 * @param ActiveRecordBase $record
	 * @return boolean
	 */
	public function updateConsecutive($record){
		$record->writeAttribute($this->_identityColumn, $record->getConnection()->lastInsertId());
		return true;
	}

	/**
	 * Finaliza el generador
	 *
	 */
	public function finalizeConsecutive(){

	}

}
