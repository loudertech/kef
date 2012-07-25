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
 * @copyright	Copyright (c) 2005-2007 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Pgsql.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * PostgreSQL Database Support (alpha)
 *
 * La base de datos PostgreSQL es un producto Open Source y disponible sin costo.
 * Postgres, desarrollado originalmente en el Deportamento de Ciencias de
 * Computación de UC Berkeley, fue pionero en muchos de los conceptos de
 * objetos y relacionales que ahora están apareciendo en algunas bases de
 * datos comerciales. Provee soporte para lenguajes SQL92/SQL99, transacciones,
 * integridad referencial, procedimientos almacenados y extensibilidad de tipos.
 * PostgreSQL es un descendiente de código abierto de su código original de Berkeley.
 *
 * Estas funciones le permiten acceder a servidores de bases de datos PostgreSQL.
 * Puede encontrar más información sobre PostgreSQL en http://www.postgresql.org.
 * La documentación de PostgreSQL puede encontrarse en http://www.postgresql.org/docs.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	PDOAdapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2007 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (C) 2007-2007 Emilio Silveira (emilio.rst@gmail.com)
 * @license 	New BSD License
 * @link 		http://www.php.net/manual/es/ref.pgsql.php
 * @access 		Public
 **/
class DbPdoPgSQL extends DbPDO {

 	/**
	 * Nombre de RBDM
	 */
	protected $db_rbdm = "pgsql";

	/**
	 * Puerto de Conexi&oacute;n a PostgreSQL
	 *
	 * @var integer
	 */
	protected $db_port = 5432;

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

	}

	/**
	 * Devuelve el ultimo id autonumerico generado en la BD
	 *
	 * @return integer
	 */
	public function last_insert_id($table='', $primary_key=''){
		if(!$this->pdo){
			return false;
		}
		return $this->pdo->lastInsertId("{$table}_{$primary_key}_seq");
	}

	/**
	 * Verifica si una tabla existe o no
	 *
	 * @param string $table
	 * @return boolean
	 */
	function table_exists($table, $schema=''){
		$table = addslashes(strtolower($table));
		if(strpos($table, ".")){
			list($schema, $table) = explode(".", $table);
		}
		if($schema==''){
			$num = $this->fetch_one("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'public' AND TABLE_NAME ='$table'");
		} else {
			$schema = addslashes(strtolower($schema));
			$num = $this->fetch_one("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$schema' AND TABLE_NAME ='$table'");
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
	 * Borra una tabla de la base de datos
	 *
	 * @param string $table
	 * @return boolean
	 */
	public function drop_table($table, $if_exists=true){
		if($if_exists){
			if($this->table_exists($table)){
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
	 * TODO:
	 * - Falta que el parametro index funcione. Este debe listar indices compuestos multipes y unicos
	 * - Agregar el tipo de tabla que debe usarse (PostgreSQL)
	 * - Soporte para campos autonumericos
	 * - Soporte para llaves foraneas
	 *
	 * @param string $table
	 * @param array $definition
	 * @return boolean
	 */
	public function create_table($table, $definition, $index=array()){
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
					$field_def['type'] = "SERIAL";
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
	public function list_tables(){
		return $this->fetch_all("SELECT c.relname AS table_name FROM pg_class c, pg_user u "
             ."WHERE c.relowner = u.usesysid AND c.relkind = 'r' "
             ."AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) "
             ."AND c.relname !~ '^(pg_|sql_)' UNION "
             ."SELECT c.relname AS table_name FROM pg_class c "
             ."WHERE c.relkind = 'r' "
             ."AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) "
             ."AND NOT EXISTS (SELECT 1 FROM pg_user WHERE usesysid = c.relowner) "
             ."AND c.relname !~ '^pg_'");
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @param string $table
	 * @return array
	 */
	public function describe_table($table, $schema=''){
		$describe = $this->fetch_all("SELECT a.attname AS Field, t.typname AS Type,
			 	CASE WHEN attnotnull=false THEN 'YES' ELSE 'NO' END AS Null,
			 	CASE WHEN (select cc.contype FROM pg_catalog.pg_constraint cc WHERE
			 	cc.conrelid = c.oid AND cc.conkey[1] = a.attnum)='p' THEN 'PRI' ELSE ''
			 	END AS Key FROM pg_catalog.pg_class c, pg_catalog.pg_attribute a,
			 	pg_catalog.pg_type t WHERE c.relname = '$table' AND c.oid = a.attrelid
			 	AND a.attnum > 0 AND t.oid = a.atttypid order by a.attnum");
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
	 * Devuelve el id de Conexion generado por el driver
	 *
	 * @return resource
	 */
	public function getConnectionId(){
		return $this->_idConnection;
	}

	/**
	 * Obtiene el nombre de la base de datos actual en el adaptador
	 *
	 * @return string
	 */
	public function getDatabaseName(){
		return $this->_dbName;
	}

	/**
	 * Devuelve el ultimo cursor generado por el driver
	 *
	 * @return resource
	 */
	public function getLastResultQuery(){
		return $this->_lastResultQuery;
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

}
