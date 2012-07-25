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
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * PDO SQLite Database Support
 *
 * SQLite is not a client library used to connect to a big database server. SQLite is the server.
 * The SQLite library reads and writes directly to and from the database files on disk.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	SQLDialects
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @link		http://us2.php.net/manual/es/ref.pdo-sqlite.php
 * @access		Public
 */
class SQLiteSQLDialect {

	/**
	 * Verifica si una tabla existe o no
	 *
	 * @access	public
	 * @param	string $table
	 * @param	string $schema
	 * @return	string
	 * @static
	 */
	public static function tableExists($tableName, $schemaName=''){
		return 'SELECT COUNT(*) FROM RDB$RELATIONS WHERE RDB$SYSTEM_FLAG = 0 AND RDB$RELATION_NAME= \''.strtoupper($tableName).'\'';
	}

	/**
	 * Listar las tablas en la base de datos
	 *
	 * @param	string $schemaName
	 * @return	string
	 */
	public static function listTables($schemaName=''){
		return 'SELECT RDB$RELATION_NAME FROM RDB$RELATIONS WHERE RDB$SYSTEM_FLAG = 0';
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @param	string $table
	 * @param	string $schemaName
	 * @return	array
	 */
	public static function describeTable($tableName, $schemaName=''){
		return 'SELECT r.RDB$FIELD_NAME AS name,
        r.RDB$NULL_FLAG AS not_null,
        f.RDB$FIELD_LENGTH AS field_length,
        f.RDB$FIELD_PRECISION AS field_precision,
        f.RDB$FIELD_SCALE AS field_scale,
        f.RDB$FIELD_TYPE AS type
	   	FROM RDB$RELATION_FIELDS r LEFT JOIN RDB$FIELDS f ON r.RDB$FIELD_SOURCE = f.RDB$FIELD_NAME
	  	WHERE r.RDB$RELATION_NAME=\''.strtoupper($tableName).'\'
		ORDER BY r.RDB$FIELD_POSITION';
	}

	/**
	 * Borra una tabla de la base de datos
	 *
	 * @access	public
	 * @param	string $table
	 * @return	boolean
	 */
	public function dropTable($table){
		return 'DROP TABLE '.$table;
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
			return 'NEXT VALUE FOR '.i18n::strtoupper($tableName).'_'.i18n::strtoupper($identityColumn).'_SEQ';
		} else {
			return 'NEXT VALUE FOR '.i18n::strtoupper($sequenceName);
		}
	}

	/**
	 * Devuelve un LIMIT válido para un SELECT del RBDM
	 *
	 * @access	public
	 * @param	string $sqlQuery
	 * @param	integer $number
	 * @return	string
	 */
	public function limit($sqlQuery, $number){
		if($number>0){
			return str_ireplace('SELECT', 'SELECT FIRST '.$number, $sqlQuery);
		} else {
			return $sqlQuery;
		}
	}

	/**
	 * Devuelve el último id autonumérico generado en la BD
	 *
	 * @param	string $table
	 * @param	array $identityColumn
	 * @param 	string $sequenceName
	 * @return	integer
	 */
	public function lastInsertId($table='', $identityColumn='', $sequenceName=''){
		return 'SELECT GEN_ID('.i18n::strtoupper($sequenceName).', 0) FROM RDB$DATABASE';
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @param	string $table
	 * @param	string $schemaName
	 * @return	array
	 */
	public static function getPrimaryKey($tableName, $schemaName){
		return 'SELECT s.RDB$FIELD_NAME AS name
		FROM RDB$INDEX_SEGMENTS s
		LEFT JOIN RDB$INDICES i ON i.RDB$INDEX_NAME = s.RDB$INDEX_NAME
		LEFT JOIN RDB$RELATION_CONSTRAINTS rc ON rc.RDB$INDEX_NAME = s.RDB$INDEX_NAME
		LEFT JOIN RDB$REF_CONSTRAINTS refc ON rc.RDB$CONSTRAINT_NAME = refc.RDB$CONSTRAINT_NAME
		LEFT JOIN RDB$RELATION_CONSTRAINTS rc2 ON rc2.RDB$CONSTRAINT_NAME = refc.RDB$CONST_NAME_UQ
		LEFT JOIN RDB$INDICES i2 ON i2.RDB$INDEX_NAME = rc2.RDB$INDEX_NAME
		LEFT JOIN RDB$INDEX_SEGMENTS s2 ON i2.RDB$INDEX_NAME = s2.RDB$INDEX_NAME
		WHERE
		rc.RDB$RELATION_NAME=\''.strtoupper($tableName).'\' AND
		rc.RDB$CONSTRAINT_TYPE=\'PRIMARY KEY\'';
	}

}