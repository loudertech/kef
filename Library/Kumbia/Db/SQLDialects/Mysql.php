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
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: Mysql.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * MySQL SQL Dialect
 *
 * Funciones de traductor de SQL para MySQL
 * Puede encontrar más información sobre MySQL en http://www.mysql.com/.
 * La documentación de MySQL puede encontrarse en http://dev.mysql.com/doc/.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	SQLDialects
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @link		http://www.php.net/manual/es/ref.mysql.php
 * @access		Public
 */
class MysqlSQLDialect {

	/**
	 * Obtiene un listado de columnas
	 *
	 * @param	array $columnList
	 * @return	string
	 */
	private static function _getColumnList($columnList){
		$strList = array();
		foreach($columnList as $column){
			$strList[] = '`'.$column.'`';
		}
		return join(', ', $strList);
	}

	/**
	 * Obtiene el nombre de columna de MySQL
	 *
	 * @param DbColumn $column
	 */
	public static function getColumnDefinition(DbColumn $column){
		$columnSql = '';
		switch($column->getType()){
			case DbColumn::TYPE_CHAR:
				$columnSql.='CHAR('.$column->getSize().')';
				break;
			case DbColumn::TYPE_VARCHAR:
				$columnSql.='VARCHAR('.$column->getSize().')';
				break;
			case DbColumn::TYPE_INTEGER :
				$columnSql.='INT('.$column->getSize().')';
				if($column->isUnsigned()==true){
					$columnSql.=' UNSIGNED';
				}
				break;
			case DbColumn::TYPE_DECIMAL:
				$columnSql.='DECIMAL('.$column->getSize().','.$column->getScale().')';
				if($column->isUnsigned()==true){
					$columnSql.=' UNSIGNED';
				}
				break;
			case DbColumn::TYPE_DATE:
				$columnSql.='DATE';
				break;
			case DbColumn::TYPE_DATETIME:
				$columnSql.='DATETIME';
				break;
			case DbColumn::TYPE_TEXT:
				$columnSql.='TEXT';
				break;
		}
		return $columnSql;
	}

	/**
	 * Genera el SQL para crear una tabla en MySQL
	 *
	 * @param 	string $table
	 * @param	string $schema
	 * @param	array $definition
	 * @return 	string
	 */
	public static function createTable($table, $schema, $definition){

		if($schema!=''){
			$table = '`'.$schema.'`.`'.$table.'`';
		} else {
			$table = '`'.$table.'`';
		}
		if(isset($definition['options']['temporary'])&&$definition['options']['temporary']==true){
			$sql = 'CREATE TEMPORARY TABLE '.$table.' ('."\n\t";
		} else {
			$sql = 'CREATE TABLE '.$table.' ('."\n\t";
		}

		$createLines = array();
		foreach($definition['columns'] as $column){
			$columnLine = '`'.$column->getName().'` ';
			$columnLine.= self::getColumnDefinition($column);
			if($column->isNotNull()==true){
				$columnLine.= ' NOT NULL';
			}
			if($column->isAutoIncrement()){
				$columnLine.= ' AUTO_INCREMENT';
			}
			$createLines[] = $columnLine;
		}

		if(isset($definition['indexes'])){
			foreach($definition['indexes'] as $index){
				if($index->getName()=='PRIMARY'){
					$createLines[] = 'PRIMARY KEY ('.self::_getColumnList($index->getColumns()).')';
				} else {
					$createLines[] = 'KEY `'.$index->getName().'` ('.self::_getColumnList($index->getColumns()).')';
				}
			}
		}

		if(isset($definition['references'])){
			foreach($definition['references'] as $reference){
				$createLines[] = 'CONSTRAINT `'.$reference->getName().'` FOREIGN KEY ('.self::_getColumnList($reference->getColumns()).') REFERENCES `'.$reference->getReferencedTable().'`('.self::_getColumnList($reference->getReferencedColumns()).')';
			}
		}

		$sql.=join(",\n\t", $createLines)."\n)";

		if(isset($definition['options'])){
			$tableOptions = array();
			if(isset($definition['options']['ENGINE'])){
				if($definition['options']['ENGINE']){
					$tableOptions[] = 'ENGINE='.$definition['options']['ENGINE'];
				}
			}
			if(isset($definition['options']['AUTO_INCREMENT'])){
				if($definition['options']['AUTO_INCREMENT']){
					$tableOptions[] = 'AUTO_INCREMENT='.$definition['options']['AUTO_INCREMENT'];
				}
			}
			if(isset($definition['options']['TABLE_COLLATION'])){
				if($definition['options']['TABLE_COLLATION']){
					$collationParts = explode('_', $definition['options']['TABLE_COLLATION']);
					$tableOptions[] = 'DEFAULT CHARSET='.$collationParts[0];
					$tableOptions[] = 'COLLATE='.$definition['options']['TABLE_COLLATION'];
				}
			}
			if(count($tableOptions)){
				$sql.=' '.join(' ', $tableOptions);
			}
		}

		return $sql;
	}

	/**
	 * Genera el SQL que agrega una columna a una tabla según la definición de columna
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	DbColumn $column
	 * @return	string
	 */
	public static function addColumn($tableName, $schemaName, DbColumn $column){
		if($schemaName){
			$sql = 'ALTER TABLE `'.$schemaName.'`.`'.$tableName.'` ADD ';
		} else {
			$sql = 'ALTER TABLE `'.$tableName.'` ADD ';
		}
		$sql.=$column->getName().' ';
		$sql.=self::getColumnDefinition($column);
		if($column->isNotNull()==true){
			$sql.=' NOT NULL';
		}
		if($column->isFirst()){
			$sql.=' FIRST';
		} else {
			if($column->getAfterPosition()){
				$sql.=' AFTER '.$column->getAfterPosition();
			}
		}
		return $sql;
	}

	/**
	 * Genera el SQL que modifica una columna a una tabla según la definición de columna
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	DbColumn $column
	 * @return	string
	 */
	public static function modifyColumn($tableName, $schemaName, DbColumn $column){
		if($schemaName){
			$sql = 'ALTER TABLE `'.$schemaName.'`.`'.$tableName.'` MODIFY ';
		} else {
			$sql = 'ALTER TABLE `'.$tableName.'` MODIFY ';
		}
		$sql.=$column->getName().' ';
		$sql.=self::getColumnDefinition($column);
		if($column->isNotNull()==true){
			$sql.=' NOT NULL';
		}
		return $sql;
	}

	/**
	 * Genera el SQL que elimina una columna de una tabla
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	string $column
	 * @return 	boolean
	 */
	public static function dropColumn($tableName, $schemaName, $columnName){
		if($schemaName){
			$sql = 'ALTER TABLE `'.$schemaName.'`.`'.$tableName.'`` DROP COLUMN ';
		} else {
			$sql = 'ALTER TABLE `'.$tableName.'` DROP COLUMN ';
		}
		$sql.='`'.$columnName.'`';
		return $sql;
	}

	/**
	 * Genera el SQL que agrega un indice a una tabla
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	DbIndex $index
	 * @return	string
	 */
	public static function addIndex($tableName, $schemaName, DbIndex $index){
		if($schemaName){
			$sql = 'ALTER TABLE `'.$schemaName.'`.`'.$tableName.'` ADD INDEX ';
		} else {
			$sql = 'ALTER TABLE `'.$tableName.'` ADD INDEX ';
		}
		$sql.='`'.$index->getName().'` ('.self::_getColumnList($index->getColumns()).')';
		return $sql;
	}

	/**
	 * Genera el SQL que borra un indice de una tabla
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	string $indexName
	 * @return	string
	 */
	public static function dropIndex($tableName, $schemaName, $indexName){
		if($schemaName){
			$sql = 'ALTER TABLE `'.$schemaName.'`.`'.$tableName.'` DROP INDEX ';
		} else {
			$sql = 'ALTER TABLE `'.$tableName.'` DROP INDEX ';
		}
		$sql.='`'.$indexName.'`';
		return $sql;
	}

	/**
	 * Genera el SQL que agrega la llave primaria a la tabla
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	DbIndex $index
	 * @return	string
	 */
	public static function addPrimaryKey($tableName, $schemaName, DbIndex $index){
		if($schemaName){
			$sql = 'ALTER TABLE `'.$schemaName.'`.`'.$tableName.'` ADD PRIMARY KEY ';
		} else {
			$sql = 'ALTER TABLE `'.$tableName.'` ADD PRIMARY KEY ';
		}
		$sql.='('.self::_getColumnList($index->getColumns()).')';
		return $sql;
	}

	/**
	 * Genera el SQL que borra la llave primaria de una tabla
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @return	string
	 */
	public static function dropPrimaryKey($tableName, $schemaName){
		if($schemaName){
			$sql = 'ALTER TABLE `'.$schemaName.'`.`'.$tableName.'` DROP PRIMARY KEY';
		} else {
			$sql = 'ALTER TABLE `'.$tableName.'` DROP PRIMARY KEY';
		}
		return $sql;
	}

	/**
	 * Genera el SQL que agrega un indice a una tabla
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	DbReference $reference
	 * @return	string
	 */
	public static function addForeignKey($tableName, $schemaName, DbReference $reference){
		if($schemaName){
			$sql = 'ALTER TABLE `'.$schemaName.'`.`'.$tableName.'` ADD FOREIGN KEY ';
		} else {
			$sql = 'ALTER TABLE `'.$tableName.'` ADD FOREIGN KEY ';
		}
		$sql.='`'.$reference->getName().'`('.self::_getColumnList($reference->getColumns()).') REFERENCES ';
		if($reference->getReferencedSchema()){
			$sql.='`'.$reference->getReferencedSchema().'`.';
		}
		$sql.='`'.$reference->getReferencedTable().'`('.self::_getColumnList($reference->getReferencedColumns()).')';
		return $sql;
	}

	/**
	 * Genera el SQL que borra un indice de una tabla
	 *
	 * @param	string $tableName
	 * @param	string $schemaName
	 * @param	string $referenceName
	 * @return	string
	 */
	public static function dropForeignKey($tableName, $schemaName, $referenceName){
		if($schemaName){
			$sql = 'ALTER TABLE `'.$schemaName.'`.`'.$tableName.'` DROP FOREIGN KEY ';
		} else {
			$sql = 'ALTER TABLE `'.$tableName.'` DROP FOREIGN KEY ';
		}
		$sql.='`'.$referenceName.'`';
		return $sql;
	}

	/**
	 * Listar las tablas en la base de datos
	 *
	 * @param	string $schemaName
	 * @return	array
	 */
	public static function listTables($schemaName=''){
		if($schemaName!=""){
			$sql = "SHOW TABLES FROM `$schemaName`";
		} else {
			$sql = "SHOW TABLES";
		}
		return $sql;
	}

	/**
	 * Genera el SQL que describe una tabla
	 *
	 * @param	string $table
	 * @param	string $schema
	 * @return	string
	 */
	public static function describeTable($table, $schema=''){
		if($schema==''){
			$sql = 'DESCRIBE `'.$table.'`';
		} else {
			$sql = 'DESCRIBE `'.$schema.'`.`'.$table.'`';
		}
		return $sql;
	}

	/**
	 * Genera el SQL que consulta los indices de una tabla
	 *
	 * @param	string $table
	 * @param	string $schema
	 * @return	string
	 */
	public static function describeIndexes($table, $schema=''){
		if($schema==''){
			$sql = 'SHOW INDEXES FROM `'.$table.'`';
		} else {
			$sql = 'SHOW INDEXES FROM `'.$schema.'`.`'.$table.'`';
		}
		return $sql;
	}

	/**
	 * Genera el SQL que consulta las llaves foraneas de una tabla
	 *
	 * @param	string $table
	 * @param	string $schema
	 * @return	string
	 */
	public static function describeReferences($table, $schema=''){
		$sql = 'SELECT TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME,REFERENCED_TABLE_SCHEMA,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
		FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME IS NOT NULL AND ';
		if($schema!=''){
			$sql.= 'CONSTRAINT_SCHEMA = "'.$schema.'" AND TABLE_NAME = "'.$table.'"';
		} else {
			$sql.= 'TABLE_NAME = "'.$table.'"';
		}
		return $sql;
	}

	/**
	 * Genera el SQL que verifica si una tabla existe o no
	 *
	 * @access	public
	 * @param	string $table
	 * @param	string $schema
	 * @return	string
	 * @static
	 */
	public static function tableExists($tableName, $schemaName=''){
		if($schemaName==''){
			return 'SELECT COUNT(*) FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_NAME`=\''.$tableName.'\'';
		} else {
			$schemaName = addslashes("$schemaName");
			return 'SELECT COUNT(*) FROM `INFORMATION_SCHEMA`.`TABLES` WHERE `TABLE_NAME`= \''.$tableName.'\' AND `TABLE_SCHEMA`=\''.$schemaName.'\'';
		}
	}

	/**
	 * Genera el SQL que de un SELECT ... FOR UPDATE
	 *
	 * @access
	 * @param	string $sqlQuery
	 * @return	string
	 * @static
	 */
	public static function forUpdate($sqlQuery){
		return $sqlQuery.' FOR UPDATE';
	}

	/**
	 * Genera el SQL que describe una tabla
	 *
	 * @param	string $table
	 * @param	string $schema
	 * @return	string
	 */
	public function tableOptions($table, $schema=''){
		$sql = 'SELECT TABLES.TABLE_TYPE,TABLES.AUTO_INCREMENT,TABLES.ENGINE,TABLES.TABLE_COLLATION FROM INFORMATION_SCHEMA.TABLES WHERE ';
		if($schema!=''){
			$sql.= 'TABLES.TABLE_SCHEMA = "'.$schema.'" AND TABLES.TABLE_NAME = "'.$table.'"';
		} else {
			$sql.= 'TABLES.TABLE_NAME = "'.$table.'"';
		}
		return $sql;
	}

}