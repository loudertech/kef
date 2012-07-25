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
 * @subpackage	PDO
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (C) 2006-2007 Giancarlo Corzo Vigil (www.antartec.com)
 * @license		New BSD License
 * @version 	$Id: Interface.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * DbPdoInterface
 *
 * Interface que deben implementar los adaptadores PDO
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	PDO
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2007-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (C) 2006-2007 Giancarlo Corzo Vigil (www.antartec.com)
 * @license		New BSD License
 * @access		public
 */
interface DbPdoInterface {

	public function initialize();
	public function connect($descriptor);
	public function query($sql);
	public function exec($sql);
	public function fetchArray($resultQuery='');
	public function close();

	/**
	 * Este metodo no esta soportado por PDO, usar fetchAll y luego contar con count()
	 *
	 * @param resource $resultQuery
	 */
	public function numRows($resultQuery='');
	public function fieldName($number, $resultQuery='');

	/**
	 * Este metodo no esta soportado por PDO, usar fetchAll y luego contar con count()
	 *
	 * @param resource $resultQuery
	 */
	public function dataSeek($number, $resultQuery='');
	public function affectedRows($resultQuery='');
	public function error($err='');
	public function noError($number=0);
	public function inQuery($sql);
	public function inQueryAssoc($sql);
	public function inQueryNum($sql);
	public function fetchOne($sql);
	public function fetchAll($sql);
	public function lastInsertId($name='');
	public function insert($table, $values, $pk='');
	public function update($table, $fields, $values, $whereCondition=null);
	public function delete($table, $whereCondition='');
	public function limit($sql, $number);
	public function forUpdate($sqlStatement);
	public function sharedLock($sqlStatement);
	public function createTable($table, $definition, $index=array(), $tableOptions=array());
	public function dropTable($table, $ifExists=false);
	public function tableExists($table, $schema='');
	public function temporaryTableExists($table, $schema='');
	public function describeTable($table, $schema='');
	public function getRequiredSequence($tableName='', $identityColumn='', $sequenceName='');
	public function setIsolationLevel($isolationLevel);
	public function getDateUsingFormat($date, $format='YYYY-MM-DD');
	public function getCurrentDate();
	public function getLastResultQuery();
	public function getConnectionId($asString=false);
	public function getDatabaseName();
	public function getUsername();
	public function getHostName();
	public function setFetchMode($fetchMode);
	public static function getPHPExtensionRequired();

}
