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
 * @subpackage	SQLDialects
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Oracle.php 121 2010-02-06 22:43:30Z gutierrezandresfelipe $
 */

/**
 * Oracle SQL Dialect
 *
 * Funciones de traductor de SQL para Oracle
 * Puede encontrar mas información sobre Oracle en http://www.oracle.com/.
 * La documentación de Oracle puede encontrarse en http://www.oracle.com/technology/documentation/index.html.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	SQLDialects
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @link		http://www.php.net/manual/es/ref.oci8.php
 */
class OracleSQLDialect {

	/**
	 * Devuelve un LIMIT válido para un SELECT del RBDM
	 *
	 * @access 	public
	 * @param	string $sql
	 * @param	integer $number
	 * @return	string
	 * @static
	 */
	public static function limit($sql, $number){
		if(!is_numeric($number)||$number<0){
			return $sql;
		}
		if(preg_match('/ORDER[\t\n\r ]+BY/i', $sql)){
			if(stripos($sql, 'WHERE')){
				return preg_replace('/ORDER[\t\n\r ]+BY/i', 'AND ROWNUM <= '.$number.' ORDER BY', $sql);
			} else {
				return preg_replace('/ORDER[\t\n\r ]+BY/i', 'WHERE ROWNUM <= '.$number.' ORDER BY', $sql);
			}
		} else {
			if(stripos($sql, 'WHERE')){
				return $sql.' AND ROWNUM <= '.$number;
			} else {
				return $sql.' WHERE ROWNUM <= '.$number;
			}
		}
	}

	/**
	 * Verifica si una tabla existe o no
	 *
	 * @access	public
	 * @param	string $table
	 * @param	string $schema
	 * @return	string
	 */
	public static function tableExists($table, $schema){
		return 'SELECT COUNT(*) FROM ALL_TABLES WHERE TABLE_NAME = UPPER(\''.$table.'\') AND OWNER = UPPER(\''.$schema.'\')';
	}

	/**
	 * Verifica si una vista existe o no
	 *
	 * @access	public
	 * @param	string $table
	 * @param	string $schema
	 * @return	string
	 */
	public static function viewExists($table, $schema){
		return 'SELECT COUNT(*) FROM ALL_VIEWS WHERE VIEW_NAME = UPPER(\''.$table.'\') AND OWNER = UPPER(\''.$schema.'\')';
	}

	/**
	 * Devuelve un FOR UPDATE válido para un SELECT del RBDM
	 *
	 * @access 	public
	 * @param	string $sql
	 * @return	string
	 * @static
	 */
	public static function forUpdate($sql){
		return $sql.' FOR UPDATE';
	}

	/**
	 * Devuelve un SHARED LOCK válido para un SELECT del RBDM
	 *
	 * @param	string $sql
	 * @return	string
	 */
	public static function sharedLock($sql){
		return $sql;
	}

	/**
	 * Borra una tabla de la base de datos
	 *
	 * @access 	public
	 * @param	string $table
	 * @param	boolean $ifExists
	 * @return	string
	 * @static
	 */
	public static function dropTable($table){
		return 'DROP TABLE '.$table;
	}

	/**
	 * Crea una tabla utilizando SQL nativo del RDBM
	 *
	 * @access	public
	 * @param	string $table
	 * @param	array $definition
	 * @param	array $index
	 * @return	string
	 * @static
	 */
	public static function createTable($table, $definition, $index=array(), $tableOptions=array()){
		$sqlStatements = array();
		$createSQL = 'CREATE TABLE '.$table.' (';
		if(!is_array($definition)){
			new DbException("Definición invalida para crear la tabla '$table'");
			return false;
		}
		$create_lines = array();
		$index = array();
		$unique_index = array();
		$primary = array();
		$not_null = '';
		$size = '';
		foreach($definition as $field => $fieldDefinition){
			if(isset($fieldDefinition['not_null'])){
				$not_null = $fieldDefinition['not_null'] ? 'NOT NULL' : '';
			} else {
				$not_null = '';
			}
			if(isset($fieldDefinition['size'])){
				$size = $fieldDefinition['size'] ? '('.$fieldDefinition['size'].')' : '';
			} else {
				$size = '';
			}
			if(isset($fieldDefinition['index'])){
				if($fieldDefinition['index']){
					$index[] = "INDEX($field)";
				}
			}
			if(isset($fieldDefinition['unique_index'])){
				if($fieldDefinition['unique_index']){
					$index[] = "UNIQUE($field)";
				}
			}
			if(isset($fieldDefinition['primary'])){
				if($fieldDefinition['primary']){
					$primary[] = "$field";
				}
			}
			if(isset($fieldDefinition['auto'])){
				if($fieldDefinition['auto']){
					$sqlStatements[] = 'CREATE SEQUENCE '.$table.'_'.$field.'_seq START WITH 1';
				}
			}
			if(isset($fieldDefinition['extra'])){
				$extra = $fieldDefinition['extra'];
			} else {
				$extra = "";
			}
			$create_lines[] = $field.' '.$fieldDefinition['type'].$size.' '.$not_null.' '.$extra;
		}
		$createSQL.= join(',', $create_lines);
		$last_lines = array();
		if(count($primary)){
			$last_lines[] = 'PRIMARY KEY('.join(',', $primary).')';
		}
		if(count($index)){
			$last_lines[] = join(',', $index);
		}
		if(count($unique_index)){
			$last_lines[] = join(',', $unique_index);
		}
		if(count($last_lines)){
			$createSQL.= ','.join(',', $last_lines).')';
		}
		$sqlStatements[] = $createSQL;
		return $sqlStatements;
	}

	/**
	 * SQL que lista de tablas del usuario
	 *
	 * @param	string $table
	 * @return	string
	 */
	public function listTables($schema=''){
		return  'SELECT TABLE_NAME FROM ALL_TABLES WHERE OWNER = UPPER(\''.$schema.'\')';
	}

	/**
	 * Indica si el RBDM requiere de secuencias y devuelve el nombre por convención
	 *
	 * @access 	public
	 * @param	string $tableName
	 * @param	array $primaryKey
	 */
	public static function getRequiredSequence($tableName='', $identityColumn='', $sequenceName=''){
		if($sequenceName==''){
			return '"'.i18n::strtoupper($tableName).'_'.i18n::strtoupper($identityColumn).'_SEQ".NEXTVAL';
		} else {
			return '"'.i18n::strtoupper($sequenceName).'".NEXTVAL';
		}
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @access	public
	 * @param	string $table
	 * @param	string $owner
	 * @return	array
	 */
	public static function describeTable($table, $owner){
		return "SELECT LOWER(ALL_TAB_COLUMNS.COLUMN_NAME) AS FIELD,
		LOWER(ALL_TAB_COLUMNS.DATA_TYPE) AS TYPE,
		ALL_TAB_COLUMNS.NULLABLE AS ISNULL,
		ALL_TAB_COLUMNS.DATA_SCALE,
		ALL_TAB_COLUMNS.DATA_PRECISION,
		ALL_CONSTRAINTS.CONSTRAINT_TYPE AS KEY,
		ALL_CONS_COLUMNS.POSITION
		FROM ALL_TAB_COLUMNS
		LEFT JOIN (ALL_CONS_COLUMNS JOIN ALL_CONSTRAINTS
		ON (ALL_CONS_COLUMNS.CONSTRAINT_NAME = ALL_CONSTRAINTS.CONSTRAINT_NAME AND
		ALL_CONS_COLUMNS.TABLE_NAME = ALL_CONSTRAINTS.TABLE_NAME AND
		ALL_CONSTRAINTS.CONSTRAINT_TYPE = 'P'))
		ON ALL_TAB_COLUMNS.TABLE_NAME = ALL_CONS_COLUMNS.TABLE_NAME AND
		ALL_TAB_COLUMNS.COLUMN_NAME = ALL_CONS_COLUMNS.COLUMN_NAME
		JOIN ALL_TABLES ON (ALL_TABLES.TABLE_NAME = ALL_TAB_COLUMNS.TABLE_NAME
		AND ALL_TABLES.OWNER = ALL_TAB_COLUMNS.OWNER)
		WHERE
		UPPER(ALL_TAB_COLUMNS.TABLE_NAME) = UPPER('$table') AND
		UPPER(ALL_TAB_COLUMNS.OWNER) = UPPER('$owner')
		ORDER BY COLUMN_ID";
	}

	/**
	 * Listar los campos de una vista
	 *
	 * @access	public
	 * @param	string $view
	 * @param	string $owner
	 * @return	array
	 */
	public static function describeView($view, $owner){
		return "SELECT LOWER(USER_TAB_COLUMNS.COLUMN_NAME) AS FIELD,
		LOWER(USER_TAB_COLUMNS.DATA_TYPE) AS TYPE,
		USER_TAB_COLUMNS.NULLABLE AS ISNULL,
		USER_TAB_COLUMNS.DATA_SCALE,
		USER_TAB_COLUMNS.DATA_PRECISION,
		'' AS KEY,
		USER_TAB_COLUMNS.COLUMN_ID
		FROM USER_TAB_COLUMNS
		WHERE
		UPPER(USER_TAB_COLUMNS.TABLE_NAME) = UPPER('$view')
		ORDER BY COLUMN_ID";
	}

}