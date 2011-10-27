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
 * @subpackage	PDOAdapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Sqlite.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * PDO SQLite Database Support
 *
 * SQLite is not a client library used to connect to a big database server. SQLite is the server.
 * The SQLite library reads and writes directly to and from the database files on disk.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	PDOAdapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @link		http://us2.php.net/manual/es/ref.pdo-sqlite.php
 * @access		Public
 */
class DbPdoSQLite extends DbPDO {

	/**
	 * Nombre de RBDM
	 */
	protected $_dbRBDM = 'sqlite';

	/**
	 * Tipo de Dato Integer
	 *
	 */
	const TYPE_INTEGER = 'INTEGER';

	/**
	 * Tipo de Dato Date
	 *
	 */
	const TYPE_DATE = 'DATE';

	/**
	 * Tipo de Dato Varchar
	 *
	 */
	const TYPE_VARCHAR = 'VARCHAR';

	/**
	 * Tipo de Dato Decimal
	 *
	 */
	const TYPE_DECIMAL = 'DECIMAL';

	/**
	 * Tipo de Dato Datetime
	 *
	 */
	const TYPE_DATETIME = 'DATETIME';

	/**
	 * Tipo de Dato Char
	 *
	 */
	const TYPE_CHAR = 'CHAR';

	/**
	 * Ejecuta acciones de incializacion del driver
	 *
	 */
	public function initialize(){

	}

	/**
	 * Verifica si una tabla existe o no
	 *
	 * @param string $table
	 * @param string $schema
	 * @return boolean
	 */
	public function tableExists($table, $schema=''){
		$table = strtolower($table);
		$fetchMode = $this->_fetchMode;
		$this->setFetchMode(DbBase::DB_NUM);
		$num = $this->fetchOne("SELECT COUNT(*) FROM sqlite_master WHERE name = '$table'");
		$this->setFetchMode($fetchMode);
		return (bool)$num[0];
	}

	/**
	 * Verifica si una tabla temporal existe o no
	 *
	 * @param string $table
	 * @param string $schema
	 * @return boolean
	 */
	public function temporaryTableExists($table, $schema=''){
		$table = strtolower($table);
		$resultSet = $this->_pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE name = '$table'");
		$num = $resultSet->fetchColumn();
		return (bool)$num[0];
	}

	/**
	 * Devuelve un LIMIT valido para un SELECT del RBDM
	 *
	 * @param integer $number
	 * @return string
	 */
	public function limit($sql, $number){
		if(is_numeric($number)){
			$number = (int) $number;
			return "$sql LIMIT $number";
		} else {
			return $sql;
		}
	}

	/**
	 * Borra una tabla de la base de datos
	 *
	 * @param string $table
	 * @return boolean
	 */
	public function dropTable($table, $ifExists=true){
		if($ifExists==true){
			return $this->query("DROP TABLE IF EXISTS $table");
		} else {
			return $this->query("DROP TABLE $table");
		}
	}

	/**
	 * Crea una tabla utilizando SQL nativo del RDBM
	 *
	 * @param string $table
	 * @param array $definition
	 * @return boolean
	 */
	public function createTable($table, $definition, $index=array(), $tableOptions=array()){
		$createSQL = "CREATE TABLE $table (";
		if(!is_array($definition)){
			new DbException("Definición invalida para crear la tabla '$table'");
			return false;
		}
		$create_lines = array();
		$index = array();
		$unique_index = array();
		$primary = array();
		$not_null = "";
		$size = "";
		foreach($definition as $field => $field_def){
			if(isset($field_def['not_null'])){
				$not_null = $field_def['not_null'] ? 'NOT NULL' : '';
			} else {
				$not_null = "";
			}
			if(isset($field_def['size'])){
				$size = $field_def['size'] ? '('.$field_def['size'].')' : '';
			} else {
				$size = "";
			}
			if(isset($field_def['index'])){
				if($field_def['index']){
					$index[] = "INDEX($field)";
				}
			}
			if(isset($field_def['unique_index'])){
				if($field_def['unique_index']){
					$index[] = "UNIQUE($field)";
				}
			}
			if(isset($field_def['primary'])){
				if($field_def['primary']){
					$primary[] = "$field";
				}
			}
			if(isset($field_def['auto'])){
				if($field_def['auto']){
					$not_null = "";
				}
			}
			if(isset($field_def['extra'])){
				$extra = $field_def['extra'];
			} else {
				$extra = "";
			}
			$create_lines[] = "$field ".$field_def['type'].$size.' '.$not_null.' '.$extra;
		}
		$createSQL.= join(',', $create_lines);
		$lastLines = array();
		if(count($primary)){
			$lastLines[] = 'PRIMARY KEY('.join(",", $primary).')';
		}
		if(count($index)){
			$lastLines[] = join(',', $index);
		}
		if(count($unique_index)){
			$lastLines[] = join(',', $unique_index);
		}
		if(count($lastLines)){
			$createSQL.= ','.join(',', $lastLines).')';
		}
		return $this->exec($createSQL);

	}

	/**
	 * Listar las tablas en la base de datos
	 *
	 * @return array
	 */
	public function listTables(){
		return $this->fetchAll("SELECT name FROM sqlite_master WHERE type='table' ".
							    "UNION ALL SELECT name FROM sqlite_temp_master ".
							    "WHERE type='table' ORDER BY name");
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @param string $table
	 * @return array
	 */
	public function describeTable($table, $schema=''){
		$fields = array();
		if($schema==""){
			$query = "PRAGMA table_info($table)";
		} else {
			$query = "PRAGMA table_info($schema.$table)";
		}
		$this->_fetchMode = PDO::FETCH_ASSOC;
		$results = $this->fetchAll($query);
		$this->_fetchMode = PDO::FETCH_BOTH;
		foreach($results as $field){
			$fields[] = array(
				'Field' => $field['name'],
				'Type' => $field['type'],
				'Null' => $field['notnull'] == 99 ? 'NO' : 'YES',
				'Key' => $field['pk'] == 1 ? 'PRI' : ''
			);
		}
		return $fields;
	}

	/**
	 * Devuelve una fecha formateada de acuerdo al RBDM
	 *
	 * @param string $date
	 * @param string $format
	 * @return string
	 */
	public function getDateUsingFormat($date, $format='YYYY-MM-DD'){
		return "'$date'";
	}

	/**
	 * Devuelve la fecha actual del motor
	 *
	 *@return string
	 */
	public function getCurrentDate(){
		return new DbRawValue('current_date');
	}

	/**
	 * Devuelve un FOR UPDATE valido para un SELECT del RBDM
	 *
	 * @param string $sqlQuery
	 * @return string
	 */
	public function forUpdate($sqlQuery){
		return $sqlQuery.' FOR UPDATE';
	}

	/**
	 * Devuelve un SHARED LOCK valido para un SELECT del RBDM
	 *
	 * @param string $sqlQuery
	 * @return string
	 */
	public function sharedLock($sqlQuery){
		return $sqlQuery.' LOCK IN SHARE MODE';
	}

	/**
	 * Cambia el nivele de isolación de la base de datos (No Soportado)
	 *
	 * @param int $isolationLevel
	 */
	public function setIsolationLevel($isolationLevel){

	}

	/**
	 * Indica si requiere secuencias para reemplazar columnas identidad
	 *
	 * @param string $tableName
	 * @param string $identityColumn
	 * @param string $sequenceName
	 * @return boolean
	 */
	public function getRequiredSequence($tableName='', $identityColumn='', $sequenceName=''){
		return false;
	}

	/**
	 * Indica las extensiones PHP requeridas para utilizar el adaptador
	 *
	 * @return string
	 */
	public static function getPHPExtensionRequired(){
		return 'pdo_sqlite';
	}

	/**
	 * Devuelve el SQL Dialect que debe ser usado
	 *
	 * @return	string
	 * @static
	 */
	public static function getSQLDialect(){
		return 'SQLite';
	}

}
