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
 * @version 	$Id: Mssql.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * PDO Microsoft SQL Server Database Support
 *
 * Estas funciones permiten acceder a MS SQL Server usando PDO ODBC
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	PDOAdapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @link		http://www.php.net/manual/es/ref.mysql.php
 * @access		Public
 *
 */
class DbPdoMsSQL extends DbPDO {

	/**
	 * Nombre del Driver RBDM
	 */
	protected $_dbRBDM = 'odbc';

	/**
	 * Tipo de Dato Integer
	 *
	 */
	const TYPE_INTEGER = 'INTEGER';

	/**
	 * Tipo de Dato Date
	 *
	 */
	const TYPE_DATE = 'SMALLDATETIME';

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
		$num = $this->fetchOne('SELECT COUNT(*) FROM sysobjects WHERE type = \'U\' AND name = \'$table\'');
		$this->setFetchMode($fetchMode);
		return (bool)$num[0];
	}

	/**
	 * Devuelve un LIMIT valido para un SELECT del RBDM
	 *
	 * @param integer $number
	 * @return string
	 */
	public function limit($sql, $number){
		if(!is_numeric($number)){
			return $sql;
		}
		$orderby = stristr($sql, 'ORDER BY');
        if($orderby!==false){
            $sort = (stripos($orderby, 'desc') !== false) ? 'desc' : 'asc';
            $order = str_ireplace('ORDER BY', '', $orderby);
            $order = trim(preg_replace('/ASC|DESC/i', '', $order));
        }
        $sql = preg_replace('/^SELECT\s/i', 'SELECT TOP '.($number).' ', $sql);
        $sql = 'SELECT * FROM (SELECT TOP '.$number. ' * FROM ('.$sql.') AS itable';
        if($orderby !== false) {
            $sql.= ' ORDER BY '.$order.' ';
            $sql.= (stripos($sort, 'asc') !== false) ? 'DESC' : 'ASC';
        }
        $sql.= ') AS otable';
        if ($orderby!==false) {
            $sql.=' ORDER BY '.$order.' '.$sort;
        }
        return $sql;

	}

	/**
	 * Borra una tabla de la base de datos
	 *
	 * @param string $table
	 * @param boolean $ifExists
	 * @return boolean
	 */
	public function dropTable($table, $ifExists=true){
		if($ifExists==true){
			if($this->tableExists($table)==true){
				return $this->query("DROP TABLE $table");
			} else {
				return true;
			}
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
	public function createTable($table, $definition, $index=array()){
		$create_sql = "CREATE TABLE $table (";
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
					$field_def['extra'] = isset($field_def['extra']) ? $field_def['extra']." IDENTITY" : "IDENTITY";
				}
			}
			if(isset($field_def['extra'])){
				$extra = $field_def['extra'];
			} else {
				$extra = "";
			}
			$create_lines[] = "$field ".$field_def['type'].$size.' '.$not_null.' '.$extra;
		}
		$create_sql.= join(',', $create_lines);
		$last_lines = array();
		if(count($primary)){
			$last_lines[] = 'PRIMARY KEY('.join(",", $primary).')';
		}
		if(count($index)){
			$last_lines[] = join(',', $index);
		}
		if(count($unique_index)){
			$last_lines[] = join(',', $unique_index);
		}
		if(count($last_lines)){
			$create_sql.= ','.join(',', $last_lines).')';
		}
		return $this->query($create_sql);

	}

	/**
	 * Listar las tablas en la base de datos
	 *
	 * @return array
	 */
	public function listTables(){
		return $this->fetchAll("SELECT name FROM sysobjects WHERE type = 'U' ORDER BY name");
	}

	public function createTable($table, $definition, $index=array()){
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @param string $table
	 * @return array
	 */
	public function describeTable($table, $schema=''){
		$describeTable = $this->fetchAll("exec sp_columns @table_name = '$table'");
		$finalDescribe = array();
		foreach($describeTable as $field){
			$finalDescribe[] = array(
				'Field' => $field['COLUMN_NAME'],
				'Type' => $field['LENGTH'] ? $field['TYPE_NAME'] : $field['TYPE_NAME'].'('.$field['LENGTH'].')',
				'Null' => $field['NULLABLE'] == 1 ? 'YES' : 'NO'
			);
		}
		$describeKeys = $this->fetchAll("exec sp_pkeys @table_name = '$table'");
		foreach($describeKeys as $field){
			for($i=0;$i<=count($finalDescribe)-1;++$i){
				if($finalDescribe[$i]['Field']==$field['COLUMN_NAME']){
					$finalDescribe[$i]['Key'] = 'PRI';
				} else {
					$finalDescribe[$i]['Key'] = "";
				}
			}
		}
		return $finalDescribe;
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
		return 'pdo_odbc';
	}

	/**
	 * Devuelve el SQL Dialect que debe ser usado
	 *
	 * @return	string
	 * @static
	 */
	public static function getSQLDialect(){
		return null;
	}

}
