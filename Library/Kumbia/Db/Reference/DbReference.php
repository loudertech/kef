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
 * DbReference
 *
 * Allows to define reference constraints on tables
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage 	Index
 * @copyright	Copyright (c) 2011-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */
class DbReference extends Object {

	/**
	 * Constraint name
	 *
	 * @var string
	 */
	private $_referenceName;

	/**
	 * Referenced Table
	 *
	 * @var string
	 */
	private $_referencedTable;

	/**
	 * Local reference columns
	 *
	 * @var array
	 */
	private $_columns;

	/**
	 * Referenced Columns
	 *
	 * @var array
	 */
	private $_referencedColumns;

	/**
	 * DbReference construct
	 *
	 * @param string $indexName
	 * @param array $columns
	 */
	public function __construct($referenceName, $definition){
		$this->_referenceName = $referenceName;
		if(isset($definition['referencedTable'])){
			$this->_referencedTable = $definition['referencedTable'];
		} else {
			throw new DbException('La tabla referenciada es requerida', 0);
		}
		if(isset($definition['columns'])){
			$this->_columns = $definition['columns'];
		} else {
			throw new DbException('Las columnas de la llave foránea son requeridas', 0);
		}
		if(isset($definition['referencedColumns'])){
			$this->_referencedColumns = $definition['referencedColumns'];
		} else {
			throw new DbException('Las columnas referenciadas de la llave foranea son requeridas', 0);
		}
		if(isset($definition['schema'])){
			$this->_schema = $definition['schema'];
		}
		if(isset($definition['referencedSchema'])){
			$this->_referencedSchema = $definition['referencedSchema'];
		}
		if(count($this->_columns)!=count($this->_referencedColumns)){
			throw new DbException('El número de columnas no es igual al número de columnas referenciadas', 0);
		}
	}

	/**
	 * Gets the index name
	 *
	 * @return string
	 */
	public function getName(){
		return $this->_referenceName;
	}

	/**
	 * Gets the schema where referenced table is
	 *
	 * @return string
	 */
	public function getSchemaName(){
		return $this->_schemaName;
	}

	/**
	 * Gets the schema where referenced table is
	 *
	 * @return string
	 */
	public function getReferencedSchema(){
		return $this->_referencedSchema;
	}

	/**
	 * Gets local columns which reference is based
	 *
	 * @return array
	 */
	public function getColumns(){
		return $this->_columns;
	}

	/**
	 * Gets the referenced table
	 *
	 * @return string
	 */
	public function getReferencedTable(){
		return $this->_referencedTable;
	}

	/**
	 * Gets referenced columns
	 *
	 * @return array
	 */
	public function getReferencedColumns(){
		return $this->_referencedColumns;
	}

}