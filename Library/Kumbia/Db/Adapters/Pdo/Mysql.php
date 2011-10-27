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
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Mysql.php 60 2009-05-27 02:22:25Z gutierrezandresfelipe $
 */

/**
 * PDO MySQL Database Support
 *
 * Estas funciones le permiten acceder a servidores de bases de datos MySQL
 * usando la interfase de PDO.
 *
 * Puede encontrar mas informacion sobre MySQL en http://www.mysql.com/.
 * La documentacion de MySQL puede encontrarse en http://dev.mysql.com/doc/.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	PDOAdapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @link		http://www.php.net/manual/es/ref.mysql.php
 * @access		Public
 */
class DbPdoMySQL extends DbPDO {

	/**
	 * Nombre de RBDM
	 */
	protected $_dbRBDM = 'mysql';

	/**
	 * Puerto de Conexi&oacute;n a MySQL
	 *
	 * @var integer
	 */
	protected $_dbPort = 3306;

	/**
	 * Tipo de Dato Integer
	 *
	 */
	const TYPE_INTEGER = "INTEGER";

	/**
	 * Tipo de Dato Date
	 *
	 */
	const TYPE_DATE = "DATE";

	/**
	 * Tipo de Dato Varchar
	 *
	 */
	const TYPE_VARCHAR = "VARCHAR";

	/**
	 * Tipo de Dato Decimal
	 *
	 */
	const TYPE_DECIMAL = "DECIMAL";

	/**
	 * Tipo de Dato Datetime
	 *
	 */
	const TYPE_DATETIME = "DATETIME";

	/**
	 * Tipo de Dato Char
	 *
	 */
	const TYPE_CHAR = "CHAR";

	/**
	 * Ejecuta acciones de incializacion del driver
	 *
	 */
	public function initialize(){
		$this->_pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
		$this->_pdo->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
		$this->_autoCommit = true;
	}

	/**
	 * Verifica si una tabla existe o no
	 *
	 * @param string $table
	 * @return boolean
	 */
	public function tableExists($table, $schema=''){
		$table = addslashes("$table");
		if($schema==''){
			$num = $this->fetchOne("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table'");
		} else {
			$schema = addslashes("$schema");
			$num = $this->fetchOne("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table' AND TABLE_SCHEMA = '$schema'");
		}
		return $num[0];
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
	 * Devuelve un FOR UPDATE valido para un SELECT del RBDM
	 *
	 * @param string $sql
	 * @return string
	 */
	public function forUpdate($sql){
		return "$sql FOR UPDATE";
	}

	/**
	 * Devuelve un SHARED LOCK valido para un SELECT del RBDM
	 *
	 * @param string $sql
	 * @return string
	 */
	public function sharedLock($sql){
		return "$sql LOCK IN SHARE MODE";
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
	 * TODO:
	 * - Falta que el parametro index funcione. Este debe listar indices compuestos multipes y unicos
	 * - Agregar el tipo de tabla que debe usarse (MySQL)
	 * - Soporte para campos autonumericos
	 * - Soporte para llaves foraneas
	 *
	 * @param string $table
	 * @param array $definition
	 * @return boolean
	 */
	public function createTable($table, $definition, $index=array()){
		$create_sql = "CREATE TABLE $table (";
		if(!is_array($definition)){
			new DbException("Definici&oacute;n invalida para crear la tabla '$table'");
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
					$index[] = "INDEX(`$field`)";
				}
			}
			if(isset($field_def['unique_index'])){
				if($field_def['unique_index']){
					$index[] = "UNIQUE(`$field`)";
				}
			}
			if(isset($field_def['primary'])){
				if($field_def['primary']){
					$primary[] = "`$field`";
				}
			}
			if(isset($field_def['auto'])){
				if($field_def['auto']){
					$field_def['extra'] = isset($field_def['extra']) ? $field_def['extra']." AUTO_INCREMENT" :  "AUTO_INCREMENT";
				}
			}
			if(isset($field_def['extra'])){
				$extra = $field_def['extra'];
			} else {
				$extra = "";
			}
			$create_lines[] = "`$field` ".$field_def['type'].$size.' '.$not_null.' '.$extra;
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
	 * @access public
	 * @return array
	 */
	public function listTables(){
		return $this->fetchAll("SHOW TABLES");
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @param string $table
	 * @return array
	 */
	public function describeTable($table, $schema=''){
		if($schema==''){
			$describe = $this->fetchAll("DESCRIBE `$table`");
		} else {
			$describe = $this->fetchAll("DESCRIBE `$schema`.`$table`");
		}
		$final_describe = array();
		foreach($describe as $key => $value){
			$final_describe[] = array(
				"Field" => $value["field"],
				"Type" => $value["type"],
				"Null" => $value["null"],
				"Key" => $value["key"]
			);
		}
		return $final_describe;
	}

	/**
	 * Indica si el RBDM requiere de secuencias y devuelve el nombre por convencion
	 *
	 * @param string $tableName
	 * @param array $primaryKey
	 * @return boolean
	 */
	public function getRequiredSequence($tableName='', $identityColumn='', $sequenceName=''){
		return false;
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
		return new DbRawValue("now()");
	}

	/**
	 * Permite establecer el nivel de isolacion de la conexion
	 *
	 * @param int $isolationLevel
	 */
	public function setIsolationLevel($isolationLevel){
		switch($isolationLevel){
			case 1:
				$isolationCommand = "SET SESSION TRANSACTION READ UNCOMMITED";
				break;
			case 2:
				$isolationCommand = "SET SESSION TRANSACTION READ COMMITED";
				break;
			case 3:
				$isolationCommand = "SET SESSION TRANSACTION REPETEABLE READ";
				break;
			case 4:
				$isolationCommand = "SET SESSION TRANSACTION SERIALIZABLE";
				break;
		}
		$this->query($isolationCommand);
		return true;
	}

	/**
	 * Indica las extensiones PHP requeridas para utilizar el adaptador
	 *
	 * @return string
	 */
	public static function getPHPExtensionRequired(){
		return 'pdo_mysql';
	}

	/**
	 * Devuelve el SQL Dialect que debe ser usado
	 *
	 * @return	string
	 * @static
	 */
	public static function getSQLDialect(){
		return 'MysqlSQLDialect';
	}

}
