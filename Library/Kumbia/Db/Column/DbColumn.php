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
 * @copyright	Copyright (c) 2011-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: DbBase.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * DbColumn
 *
 * Allows to define columns to be used for create or alter tables
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage 	Column
 * @copyright	Copyright (c) 2011-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */
class DbColumn extends Object {

	/**
	 * Integer abstract type
	 *
	 */
	const TYPE_INTEGER = 0;

	/**
	 * Date abstract type
	 *
	 */
	const TYPE_DATE = 1;

	/**
	 * Varchar abstract type
	 *
	 */
	const TYPE_VARCHAR = 2;

	/**
	 * Decimal abstract type
	 *
	 */
	const TYPE_DECIMAL = 3;

	/**
	 * Datetime abstract type
	 *
	 */
	const TYPE_DATETIME = 4;

	/**
	 * Char abstract type
	 *
	 */
	const TYPE_CHAR = 5;

	/**
	 * Text abstract data type
	 *
	 */
	const TYPE_TEXT = 6;

	/**
	 * Column's name
	 *
	 * @var string
	 */
	private $_columnName;

	/**
	 * Schema which table related is
	 *
	 * @var string
	 */
	private $_schemaName;

	/**
	 * Column data type
	 *
	 * @var int
	 */
	private $_type;

	/**
	 * Integer column size
	 *
	 * @var int
	 */
	private $_size;

	/**
	 * Integer column number scale
	 *
	 * @var int
	 */
	private $_scale;

	/**
	 * Integer column unsigned?
	 *
	 * @var boolean
	 */
	private $_unsigned = false;

	/**
	 * Column not nullable?
	 *
	 * @var boolean
	 */
	private $_notNull = false;

	/**
	 * Column is autoIncrement?
	 *
	 * @var boolean
	 */
	private $_autoIncrement = false;

	/**
	 * Position is first
	 *
	 * @var boolean
	 */
	private $_first;

	/**
	 * Column Position
	 *
	 * @var string
	 */
	private $_after;

	/**
	 * DbColumn construct
	 *
	 * @param string $columnName
	 * @param array $definition
	 */
	public function __construct($columnName, $definition){
		$this->_columnName = $columnName;
		if(isset($definition['type'])){
			$this->_type = $definition['type'];
		} else {
			throw new DbException('El tipo de columna es requerido');
		}
		if(isset($definition['notNull'])){
			$this->_notNull = $definition['notNull'];
		}
		if(isset($definition['size'])){
			$this->_size = $definition['size'];
		}
		if(isset($definition['scale'])){
			$this->_scale = $definition['scale'];
		}
		if(isset($definition['unsigned'])){
			$this->_unsigned = $definition['unsigned'];
		}
		if(isset($definition['autoIncrement'])){
			$this->_autoIncrement = $definition['autoIncrement'];
		}
		if(isset($definition['first'])){
			$this->_first = $definition['first'];
		}
		if(isset($definition['after'])){
			$this->_after = $definition['after'];
		}
	}

	/**
	 * Returns schema's table related to column
	 *
	 * @return string
	 */
	public function getSchemaName(){
		return $this->_schemaName;
	}

	/**
	 * Returns column name
	 *
	 * @return string
	 */
	public function getName(){
		return $this->_columnName;
	}

	/**
	 * Returns column type
	 *
	 * @return int
	 */
	public function getType(){
		return $this->_type;
	}

	/**
	 * Returns column size
	 *
	 * @return int
	 */
	public function getSize(){
		return $this->_size;
	}

	/**
	 * Returns column scale
	 *
	 * @return int
	 */
	public function getScale(){
		return $this->_scale;
	}

	/**
	 * Returns true if number column is unsigned
	 *
	 * @return bolean
	 */
	public function isUnsigned(){
		return $this->_unsigned;
	}

	/**
	 * Not null
	 *
	 * @return boolean
	 */
	public function isNotNull(){
		return $this->_notNull;
	}

	/**
	 * Auto-Increment
	 *
	 * @return boolean
	 */
	public function isAutoIncrement(){
		return $this->_autoIncrement;
	}

	/**
	 * Check if column have first position on table
	 *
	 * @return boolean
	 */
	public function isFirst(){
		return $this->_first;
	}

	/**
	 * Check next field absolute to position on table
	 *
	 * @return string
	 */
	public function getAfterPosition(){
		return $this->_after;
	}

}