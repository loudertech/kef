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
 * DbIndex
 *
 * Allows to define columns to be used on tables
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage 	Index
 * @copyright	Copyright (c) 2011-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */
class DbIndex extends Object {

	/**
	 * Index name
	 *
	 * @var string
	 */
	private $_indexName;

	/**
	 * DbIndex construct
	 *
	 * @param string $indexName
	 * @param array $columns
	 */
	public function __construct($indexName, $columns){
		$this->_indexName = $indexName;
		$this->_columns = $columns;
	}

	/**
	 * Gets the index name
	 *
	 * @return string
	 */
	public function getName(){
		return $this->_indexName;
	}

	/**
	 * Obtiene las columnas que componenten indice
	 *
	 * @return array
	 */
	public function getColumns(){
		return $this->_columns;
	}

}