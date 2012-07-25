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
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Oracle.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * Oracle Database Support (JDBC)
 *
 * Estas funciones le permiten acceder a servidores de bases de datos Oracle.
 * Puede encontrar mas información sobre Oracle en http://www.oracle.com/.
 * La documentación de Oracle puede encontrarse en http://www.oracle.com/technology/documentation/index.html.
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @link		http://www.php.net/manual/es/ref.oci8.php
 */
class DbJDBCOracle extends DbJDBC {

	/**
	 * Tipo de Gestor utilizado
	 *
	 * @var string
	 */
	protected $_dbRBDM = 'oracle';

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
	const TYPE_VARCHAR = 'VARCHAR2';

	/**
	 * Tipo de Dato Decimal
	 *
	 */
	const TYPE_DECIMAL = 'DECIMAL';

	/**
	 * Tipo de Dato Datetime
	 *
	 */
	const TYPE_DATETIME = 'DATE';

	/**
	 * Tipo de Dato Char
	 *
	 */
	const TYPE_CHAR = 'CHAR';

	/**
	 * Inicializa opciones especificas del adaptador
	 *
	 * @param stdClass $descriptor
	 */
	public function initialize($descriptor){
		$sort = isset($descriptor->sort) ? $descriptor->sort : 'binary_ci';
		$comp = isset($descriptor->comp) ? $descriptor->comp : 'linguistic';
		$language = isset($descriptor->language) ? $descriptor->language : 'spanish';
		$territory = isset($descriptor->territory) ? $descriptor->territory : 'spain';
		$date_format = isset($descriptor->date_format) ? $descriptor->date_format : 'YYYY-MM-DD HH24:MI:SS';
		$this->query("ALTER SESSION SET nls_date_format='$date_format' nls_territory=$territory nls_language=$language nls_sort=$sort nls_comp=$comp");
	}

	/**
	 * Verifica si una tabla existe o no
	 *
	 * @access public
	 * @param string $table
	 * @param string $schema
	 * @return boolean
	 */
	public function tableExists($table, $schema=''){
		if($schema!=""){
			$sql = "SELECT COUNT(*) FROM ALL_TABLES WHERE TABLE_NAME = UPPER('$table') AND OWNER = UPPER('$schema')";
		} else {
			$sql = "SELECT COUNT(*) FROM ALL_TABLES WHERE TABLE_NAME = UPPER('$table') AND OWNER = UPPER('".$this->getUsername()."')";
		}
		$fetchMode = $this->_fetchMode;
		$this->_fetchMode = self::JDBC_FETCH_NUM;
		$num = $this->fetchOne($sql);
		$this->_fetchMode = $fetchMode;
		return $num[0];
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @access public
	 * @param string $table
	 * @param string $schema
	 * @return array
	 */
	public function describeTable($table, $schema=''){
		if($schema==""){
			$schema = $this->getUsername();
		}
		$sql = "SELECT LOWER(ALL_TAB_COLUMNS.COLUMN_NAME) AS FIELD,
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
		UPPER(ALL_TAB_COLUMNS.OWNER) = UPPER('$schema')
		ORDER BY COLUMN_ID";
 		$fetchMode = $this->_fetchMode;
 		$this->_fetchMode = self::JDBC_FETCH_ASSOC;
		$describe = $this->fetchAll($sql);
		$this->_fetchMode = $fetchMode;
		$finalDescribe = array();
		$fields = array();
		foreach($describe as $key => $value){
			if(!in_array($value['field'], $fields)){
				if($value['data_scale']==0){
					$type = $value['type'].'('.$value['data_precision'].')';
				} else {
					$type = $value['type'].'('.$value['data_precision'].','.$value['data_scale'].')';
				}
				$finalDescribe[] = array(
					'Field' => $value['field'],
					'Type' => $type,
					'Null' => $value['isnull'] == 'Y' ? 'YES' : 'NO',
					'Key' => $value['key'] == 'P' ? 'PRI' : ''
				);
				$fields[] = $value['field'];
			}
		}
		return $finalDescribe;
	}

	/**
	 * Devuelve un LIMIT valido para un SELECT del RBDM
	 *
	 * @param string $sql
	 * @param integer $number
	 * @return string
	 */
	public function limit($sql, $number){
		if(!is_numeric($number)||$number<0){
			return $sql;
		}
		if(preg_match('/ORDER[\t\n\r ]+BY/i', $sql)){
			if(stripos($sql, 'WHERE')){
				return preg_replace('/ORDER[\t\n\r ]+BY/i', "AND ROWNUM <= $number ORDER BY", $sql);
			} else {
				return preg_replace('/ORDER[\t\n\r ]+BY/i', "WHERE ROWNUM <= $number ORDER BY", $sql);
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
	 * Devuelve un FOR UPDATE valido para un SELECT del RBDM
	 *
	 * @param string $sql
	 * @return string
	 */
	public function forUpdate($sql){
		return $sql.' FOR UPDATE';
	}

	/**
	 * Devuelve un SHARED LOCK valido para un SELECT del RBDM
	 *
	 * @param string $sql
	 * @return string
	 */
	public function sharedLock($sql){
		return $sql;
	}

	/**
	 * Devuelve las extensiones requeridas por el adaptador
	 *
	 * @return string
	 */
	public static function getPHPExtensionRequired(){
		return 'java';
	}

}
