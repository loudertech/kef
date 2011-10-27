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
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * Firebird Database Support
 *
 * Estas funciones le permiten acceder a servidores de bases de datos Firebird CS y SS.
 * Puede encontrar más información sobre FirebirdSQL en http://www.firebirdsql.org/
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	PDOAdapters
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @link		http://co.php.net/manual/en/ref.pdo-firebird.php
 * @access		Public
 *
 */
class DbPdoFirebird extends DbPDO {

	/**
	 * Nombre de RBDM
	 */
	protected $_dbRBDM = 'firebird';

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
		$this->_useRawTransactions = true;
	}

	/**
	 * Verifica si una tabla existe o no
	 *
	 * @param string $table
	 * @return boolean
	 */
	public function tableExists($table, $schema=''){
		//Informix no soporta schemas
		$pdoStatement = $this->_pdo->query("SELECT COUNT(*) FROM systables WHERE tabname = LOWER('$table')");
		return (bool)$pdoStatement->fetchColumn();;
	}

	/**
	 * Verifica si una tabla temporal existe o no
	 *
	 * @access public
	 * @param string $table
	 * @param string $schema
	 * @return boolean
	 */
	public function temporaryTableExists($tableName, $schemaName=''){
		try {
			$this->_pdo->query('SELECT * FROM '.$tableName);
			return true;
		}
		catch(PdoException $e){
			return false;
		}
	}

	/**
	 * Devuelve un LIMIT valido para un SELECT del RBDM
	 *
	 * @param 	integer $number
	 * @return 	string
	 */
	public function limit($sql, $number){
		$number = (int) $number;
		if($number==0){
			$sql = str_ireplace('SELECT', 'SELECT * FROM (SELECT', $sql);
			$sql .= ') WHERE 0 = 1';
			$sql = str_ireplace('SELECT', 'SELECT FIRST '.$count, $sql);
		}
		return $sql;
	}

	/**
	 * Devuelve la fecha actual del motor
	 *
	 * @return string
	 */
	public function getCurrentDate(){
		return new DbRawValue('today');
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
	 * Borra una tabla de la base de datos
	 *
	 * @param string $table
	 * @param boolean $ifExists
	 * @return boolean
	 */
	public function dropTable($table, $ifExists=true){
		if($if_exists){
			if($this->tableExists($table)==true){
				return $this->query('DROP TABLE '.$table);
			} else {
				return true;
			}
		} else {
			$this->setReturnRows(false);
			return $this->query('DROP TABLE '.$table);
		}
	}

	/**
	 * Crea una tabla utilizando SQL nativo del RDBM
	 *
	 * TODO:
	 * - Falta que el parametro index funcione. Este debe listar indices compuestos multipes y unicos
	 * - Soporte para llaves foraneas
	 *
	 * @param 	string $table
	 * @param 	array $definition
	 * @return 	boolean
	 */
	public function createTable($table, $definition, $index=array(), $tableOptions=array()){
		if(isset($tableOptions['temporary'])&&$tableOptions['temporary']==true){
			$createSQL = "CREATE TEMP TABLE $table (";
		} else {
			$createSQL = "CREATE TABLE $table (";
		}
		if(is_array($definition)==false){
			new DbException("Definición invalida para crear la tabla '$table'");
			return false;
		}
		$createLines = array();
		$index = array();
		$unique_index = array();
		$primary = array();
		$not_null = "";
		$size = "";
		foreach($definition as $field => $fieldDef){
			if(isset($fieldDef['not_null'])){
				$not_null = $fieldDef['not_null'] ? 'NOT NULL' : '';
			} else {
				$not_null = "";
			}
			if(isset($fieldDef['size'])){
				$size = $fieldDef['size'] ? '('.$fieldDef['size'].')' : '';
			} else {
				$size = "";
			}
			if(isset($fieldDef['index'])){
				if($fieldDef['index']){
					$index[] = "INDEX($field)";
				}
			}
			if(isset($fieldDef['unique_index'])){
				if($fieldDef['unique_index']){
					$index[] = "UNIQUE($field)";
				}
			}
			if(isset($fieldDef['primary'])){
				if($fieldDef['primary']){
					$primary[] = "$field";
				}
			}
			if(isset($fieldDef['auto'])){
				if($fieldDef['auto']){
					$fieldDef['type'] = "SERIAL";
				}
			}
			if(isset($fieldDef['extra'])){
				$extra = $fieldDef['extra'];
			} else {
				$extra = "";
			}
			$createLines[] = "$field ".$fieldDef['type'].$size.' '.$not_null.' '.$extra;
		}
		$createSQL.= join(',', $createLines);
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
		return $this->query($createSQL);
	}

	/**
	 * Listar las tablas en la base de datos
	 *
	 * @return array
	 */
	public function listTables(){
		$query = FirebirdSQLDialect::listTables();
		$fetchMode = $this->_fetchMode;
		$this->_fetchMode = PDO::FETCH_NUM;
		$tables = $this->fetchAll($query);
		$allTables = array();
		foreach($tables as $table){
			$allTables[] = $table[0];
		}
		$this->_fetchMode = $fetchMode;
		return $allTables;
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @param string $table
	 * @param string $schema
	 * @return array
	 */
	public function describeTable($table, $schema=''){
		$fetchMode = $this->_fetchMode;
		$this->_fetchMode = PDO::FETCH_ASSOC;
		$sql = 'SELECT i.indexkeys FROM sysindices i, systables t, sysconstraints c WHERE
		i.idxname = c.idxname AND c.constrtype = "P" AND c.tabid = t.tabid AND
		t.tabname = "'.$table.'" AND t.tabid = i.tabid';
		$resourcePrimaryKeys = $this->_pdo->query($sql);
		$primaryKeys = array();
		$data = $resourcePrimaryKeys->fetchColumn();
		if($data!=false){
			$primaryKeysData = fgets($data);
			foreach(explode(',', $primaryKeysData) as $field){
				$primaryKeys[] = (int)substr($field, 0, 2);
			}
		}
		$sql = 'SELECT c.colname AS Field, c.coltype AS Type, c.collength as Length
				 FROM systables t, syscolumns c WHERE c.tabid = t.tabid AND t.tabname = "'.$table.'" ORDER BY c.colno';
		$describe = $this->_pdo->query($sql);
		$finalDescribe = array();
		$n = 1;
		$fields = array();
		foreach($describe as $field){
			if(in_array($n, $primaryKeys)){
				$field['key'] = 'PRI';
			} else {
				$field['key'] = '';
			}
			$fields[$n] = $field['field'];
			switch($field['type']){
				case 262:
					$field['type'] = 'integer';
					$field['null'] = 'YES';
					break;
				case 13:
					$field['type'] = 'varchar('.$field['length'].')';
					$field['null'] = 'YES';
					break;
				case 269:
					$field['type'] = 'varchar('.$field['length'].')';
					$field['null'] = 'NO';
					break;
				case 0:
					$field['type'] = 'char('.$field['length'].')';
					$field['null'] = 'YES';
					break;
				case 256:
					$field['type'] = 'char('.$field['length'].')';
					$field['null'] = 'NO';
					break;
				case 2:
					$field['type'] = 'int('.$field['length'].')';
					$field['null'] = 'YES';
					break;
				case 258:
					$field['type'] = 'int('.$field['length'].')';
					$field['null'] = 'NO';
					break;
				case 5:
					$field['length'] = ceil(($field['length']-$field['length']%10)/256);
					$field['type'] = 'decimal('.$field['length'].')';
					$field['null'] = 'YES';
					break;
				case 261:
					$field['length'] = ceil(($field['length']-$field['length']%10)/256);
					$field['type'] = 'decimal('.$field['length'].')';
					$field['null'] = 'NO';
					break;
				case 7:
					$field['type'] = 'date';
					$field['null'] = 'YES';
					break;
				case 263:
					$field['type'] = 'date';
					$field['null'] = 'NO';
					break;
				case 267:
                    $field['type'] = 'byte';
                    $field['null'] = 'NO';
                    break;
			}
			$finalDescribe[] = array(
				'Field' => $field['field'],
				'Type' => $field['type'],
				'Null' => $field['null'],
				'Key' => $field['key']
			);
			++$n;
		}
		$this->_fetchMode = $fetchMode;
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
	 * Obtiene el tipo de error de acuerdo al codigo
	 *
	 * @param int $errorNumber
	 * @return int
	 */
	public function _getErrorType($errorNumber){
		switch($errorNumber){
			case -244:
				return self::EX_LOCK_ADQUISITION;
			case -201:
				return self::EX_GRAMMATICAL;
			case -1204:
				return self::EX_INVALID_FORMAT;
			default:
				return self::EX_DEFAULT;
		}
	}

	/**
	 * Establece el nivel de isolación de la conexión
	 *
	 * @param int $isolationLevel
	 */
	public function setIsolationLevel($isolationLevel){
		switch($isolationLevel){
			case self::ISOLATION_READ_UNCOMMITED:
				break;
			case self::ISOLATION_READ_COMMITED:
				break;
			case self::ISOLATION_REPEATABLE_READ:
				break;
			case self::ISOLATION_SERIALIZABLE:
				break;
		}
	}

	/**
	 * Obtiene la extension PHP requerida para usar el adaptador
	 *
	 * @return string
	 */
	public static function getPHPExtensionRequired(){
		return 'pdo_firebird';
	}

	/**
	 * Devuelve el SQL Dialect que debe ser usado
	 *
	 * @return	string
	 * @static
	 */
	public static function getSQLDialect(){
		return 'FirebirdSQLDialect';
	}

}