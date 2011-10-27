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
 * @version 	$Id: Uniqid.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * UniqIdGenerator
 *
 * Genera identificadores a partir de Uniqid
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	Generator
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @link 		http://www.php.net/uniqid
 * @link 		http://en.wikipedia.org/wiki/UUID
 */
class UuidGenerator implements ActiveRecordGeneratorInterface {

	/**
	 * UniqId generado
	 *
	 * @var string
	 */
	protected $_uniqid;

	/**
	 * Constructor de UuidGenerator
	 *
	 */
	public function __construct(){
		$this->_uniqid = md5(uniqid(mt_rand(), true));
	}

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
	 * @param ActiveRecord $record
	 */
	public function setIdentifier($record){
		$record->writeAttribute($this->_identityColumn, $this->_uniqid);
	}

	/**
	 * Actualiza el consecutivo
	 *
	 * @return boolean
	 */
	public function updateConsecutive($record){

	}

	/**
	 * Finaliza el generador
	 *
	 */
	public function finalizeConsecutive(){

	}

}
