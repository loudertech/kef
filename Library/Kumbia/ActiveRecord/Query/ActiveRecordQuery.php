<?php

/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.

 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category 	Kumbia
 * @package 	ActiveRecord
 * @subpackage 	Query
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id$
 */

/**
 * ActiveRecordQuery
 *
 * Subcomponente que permite crear sentencias SQL apartir de entidades
 *
 * @package 	ActiveRecord
 * @subpackage 	Query
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id$
 */
class ActiveRecordQuery extends Object {

	/**
	 * Tipo de KQL: SELECT
	 *
	 */
	const TYPE_SELECT = 0;

	/**
	 * Tipo de KQL: DELETE
	 *
	 */
	const TYPE_DELETE = 1;

	/**
	 * Tipo de KQL: UPDATE
	 *
	 */
	const TYPE_UPDATE = 2;

	/**
	 * Constante para clasificar joins tipo INNER JOIN
	 *
	 */
	const INNER_JOIN = 0;

	/**
	 * Constante para clasificar joins tipo LEFT JOIN
	 *
	 */
	const LEFT_JOIN = 1;

	/**
	 * Constante para clasificar joins tipo RIGHT JOIN
	 *
	 */
	const RIGHT_JOIN = 2;

	/**
	 * Constante para clasificar joins tipo FULL JOIN
	 *
	 */
	const FULL_JOIN = 3;

	/**
	 * Constante para clasificar joins tipo CARTESIAN ó CROSS JOIN
	 *
	 */
	const CROSS_JOIN = 4;

	/**
	 * Constante para clasificar joins tipo NATURAL JOIN
	 *
	 */
	const NATURAL_JOIN = 5;

	/**
	 * Entidades usadas en la consulta
	 *
	 * @var array
	 */
	private $_entities = array();

	/**
	 * Columnas seleccionados en la consulta
	 *
	 * @var array
	 */
	private $_selectedColumns = array();

	/**
	 * Lista de Objetos de la clausula FROM
	 *
	 * @var array
	 */
	private $_fromObjects = array();

	/**
	 * Columnas del ordenamiento de la clausula ORDER-BY
	 *
	 * @var array
	 */
	private $_orderColumns = array();

	/**
	 * Clausula WHERE de la consulta
	 *
	 * @var string
	 */
	private $_whereCondition = null;

	/**
	 * JOINs de la consulta (INNER JOINS, LEFT JOINS, RIGHT JOINS, OUTER JOINS)
	 *
	 * @var array
	 */
	private $_joins = array();

	/**
	 * Tipo de Consulta KQL
	 *
	 * @var int
	 */
	private $_type;

	/**
	 * Conexión con la que se hace
	 *
	 * @var DbBase
	 */
	private $_connection;

	/**
	 * Establece el tipo de consulta a realizar
	 *
	 * @param	int $type
	 * @throws	ActiveRecordException
	 */
	private function _setType($type){
		if($this->_type==null){
			$this->_type = $type;
		} else {
			throw new ActiveRecordException('No es posible mezclar diferentes tipos de sentencias KDL');
		}
	}

	/**
	 * Constructor de ActiveRecordQuery
	 *
	 * @param 	$connection
	 * @return	ActiveRecordQuery
	 */
	public function __construct($connection=null){
		$this->_connection = $connection;
	}

	/**
	 * Indica que es una consulta y especifica los campos a consultar
	 *
	 * @param	array $selectedColumns
	 * @return	ActiveRecordQuery
	 */
	public function select($selectedColumns){
		if(!is_array($selectedColumns)){
			$i = 0;
			$selectedColumns = explode(',', $selectedColumns);
			foreach($selectedColumns as $selectedField){
				$selectedColumns[$i] = trim($selectedField);
				$i++;
			}
		}
		$this->_selectedColumns = $selectedColumns;
		$this->_setType(self::TYPE_SELECT);
		return $this;
	}

	/**
	 * Establece una clausula FROM para la consulta
	 *
	 * @param	string $entity
	 * @return	ActiveRecordQuery
	 */
	public function from($entity){
		$entityParts = explode(' ', $entity);
		if(EntityManager::isModel($entityParts[0])){
			if(isset($entityParts[1])){
				$this->_entities[$entityParts[1]] = $entityParts[0];
				$this->_fromObjects[] = $entityParts[0];
			}
		}
		return $this;
	}

	/**
	 * Agrega una entidad con la que se hará un INNER JOIN a la entidad principal
	 *
	 * @param	string $condition
	 * @return	ActiveRecordQuery
	 * @throws  ActiveRecordException
	 */
	public function innerJoin($joinEntity){
		if($this->_type!==null){
			if($this->_type!=self::TYPE_SELECT){
				throw new ActiveRecordException('INNER JOIN no son aplicables a este tipo de sentencia');
			}
		}
		$this->_addJoin($joinEntity, self::INNER_JOIN);
		return $this;
	}

	/**
	 * Agrega una entidad con la que se hará un LEFT JOIN a la entidad principal
	 *
	 * @param	string $condition
	 * @return	ActiveRecordQuery
	 * @throws  ActiveRecordException
	 */
	public function leftJoin($joinEntity){
		if($this->_type!==null){
			if($this->_type!=self::TYPE_SELECT){
				throw new ActiveRecordException('LEFT JOIN no son aplicables a este tipo de sentencia');
			}
		}
		$this->_addJoin($joinEntity, self::LEFT_JOIN);
		return $this;
	}

	/**
	 * Agrega una entidad con la que se hará un RIGHT JOIN a la entidad principal
	 *
	 * @param	string $condition
	 * @return	ActiveRecordQuery
	 * @throws  ActiveRecordException
	 */
	public function rightJoin($joinEntity){
		if($this->_type!==null){
			if($this->_type!=self::TYPE_SELECT){
				throw new ActiveRecordException('RIGHT JOIN no son aplicables a este tipo de sentencia');
			}
		}
		$this->_addJoin($joinEntity, self::RIGHT_JOIN);
		return $this;
	}

	/**
	 * Agrega una entidad con la que se hará un NATURAL JOIN a la entidad principal
	 *
	 * @param	string $condition
	 * @return	ActiveRecordQuery
	 * @throws  ActiveRecordException
	 */
	public function naturalJoin($joinEntity){
		if($this->_type!==null){
			if($this->_type!=self::TYPE_SELECT){
				throw new ActiveRecordException('NATURAL JOIN no son aplicables a este tipo de sentencia');
			}
		}
		$this->_addJoin($joinEntity, self::NATURAL_JOIN);
		return $this;
	}

	/**
	 * Agrega una entidad con la que se hará un FULL JOIN a la entidad principal
	 *
	 * @param	string $condition
	 * @return	ActiveRecordQuery
	 * @throws  ActiveRecordException
	 */
	public function fullJoin($joinEntity){
		if($this->_type!==null){
			if($this->_type!=self::TYPE_SELECT){
				throw new ActiveRecordException('FULL JOIN no son aplicables a este tipo de sentencia');
			}
		}
		$this->_addJoin($joinEntity, self::FULL_JOIN);
		return $this;
	}

	/**
	 * Agrega una entidad con la que se hará un CROSS JOIN a la entidad principal
	 *
	 * @param	string $condition
	 * @return	ActiveRecordQuery
	 * @throws  ActiveRecordException
	 */
	public function crossJoin($joinEntity){
		if($this->_type!==null){
			if($this->_type!=self::TYPE_SELECT){
				throw new ActiveRecordException('CROSS JOIN no son aplicables a este tipo de sentencia');
			}
		}
		$this->_addJoin($joinEntity, self::CROSS_JOIN);
		return $this;
	}

	/**
	 * Agrega una entidad con la que se hará un NATURAL JOIN a la entidad principal
	 * Alias de naturalJoin
	 *
	 * @param	string $condition
	 * @return	ActiveRecordQuery
	 * @throws  ActiveRecordException
	 */
	public function join($joinEntity){
		return $this->naturalJoin($joinEntity);
	}

	/**
	 * Establece una ó más condiciones para la consulta, una clausula WHERE
	 *
	 * @param	string $condition
	 * @return	ActiveRecordQuery
	 * @throws  ActiveRecordException
	 */
	public function where($condition){
		if($this->_whereCondition!=''){
			throw new ActiveRecordException('Ya se había indicado una clausula WHERE');
		}
		$numberArguments = func_num_args();
		if($numberArguments>1){
			$arguments = func_get_args();
			$this->_whereCondition = $arguments;
		} else {
			$this->_whereCondition = $condition;
		}
		return $this;
	}

	/**
	 * Agrega una clausula ORDER BY a la consulta
	 *
	 * @param	string|array $orderColumns
	 * @return	ActiveRecordQuery
	 * @throws  ActiveRecordException
	 */
	public function orderBy($orderColumns){
		if($this->_type!==null){
			if($this->_type!=self::TYPE_SELECT){
				throw new ActiveRecordException('Clausulas ORDER BY no son aplicables a este tipo de sentencia');
			}
		}
		if(!is_array($orderColumns)){
			$i = 0;
			$orderColumns = explode(',', $orderColumns);
			foreach($orderColumns as $selectedField){
				$orderColumns[$i] = trim($selectedField);
				$i++;
			}
		}
		$this->_orderColumns = $orderColumns;
		return $this;
	}

	/**
	 * Obtiene la sentencia SQL de la consulta
	 *
	 * @throws  ActiveRecordException
	 * @return	string
	 */
	public function getSQLString(){
		$sqlStatement = '';
		if($this->_type!==null){
			if($this->_type==self::TYPE_SELECT){
				if(count($this->_selectedColumns)==0){
					throw new ActiveRecordException('No se especificaron las columnas a seleccionar');
				} else {
					$sqlStatement.= 'SELECT ';
					$sqlStatement.= join(',', $this->_getSQLColumns($this->_selectedColumns));
				}
			}
		} else {
			throw new ActiveRecordException('No se pudo determinar el tipo de sentencia SQL a construir');
		}
		if(count($this->_entities)==0){
			throw new ActiveRecordException('No se especificaron las entidades de la consulta');
		}
		$sqlStatement.= ' FROM ';
		$fromEntities = array();
		foreach($this->_fromObjects as $entityName){
			$fromEntities[] = EntityManager::getCompleteSource($entityName);
		}
		$sqlStatement.= join(',', $fromEntities);
		if($this->_type==self::TYPE_SELECT){
			if(isset($this->_joins[self::LEFT_JOIN])){
				$sqlStatement.=$this->_processJoin(self::LEFT_JOIN);
			}
			if(isset($this->_joins[self::RIGHT_JOIN])){
				$sqlStatement.=$this->_processJoin(self::RIGHT_JOIN);
			}
			if(isset($this->_joins[self::INNER_JOIN])){
				$sqlStatement.=$this->_processJoin(self::INNER_JOIN);
			}
			if(isset($this->_joins[self::NATURAL_JOIN])){
				$sqlStatement.=$this->_processJoin(self::NATURAL_JOIN);
			}
			if(isset($this->_joins[self::FULL_JOIN])){
				$sqlStatement.=$this->_processJoin(self::FULL_JOIN);
			}
			if(isset($this->_joins[self::CROSS_JOIN])){
				$sqlStatement.=$this->_processJoin(self::CROSS_JOIN);
			}
		}
		if($this->_whereCondition!==null){
			if(is_array($this->_whereCondition)){
				$whereTokens = $this->_getSQLTokens($this->_whereCondition[0]);
				$paramNumber = 1;
				$whereCondition = '';
				foreach($whereTokens as $token){
					if($token=='?'){
						if(!isset($this->_whereCondition[$paramNumber])){
							throw new ActiveRecordException('No se indicó el parámetro número "#'.$paramNumber.'" en la clausula WHERE');
						}
						$paramValue = $this->_whereCondition[$paramNumber];
						if(is_string($paramValue)){
							$paramValue = '\''.addslashes($paramValue).'\'';
						}
						$whereCondition.= $paramValue;
						++$paramNumber;
						continue;
					} else {
						if(preg_match('/(\w+)\.(\w+)/', $token, $matches)){
							if(isset($this->_entities[$matches[1]])){
								$entitySource = $this->_getEntitySourceByAlias($matches[1]);
								$whereCondition.= str_replace($matches[1].'.', $entitySource.'.', $token);
							} else {
								$whereCondition.= $token;
							}
						} else {
							$whereCondition.= $token;
						}
					}
				}
			} else {
				$whereCondition = $this->_whereCondition;
			}
			$sqlStatement.= ' WHERE '.$whereCondition;
		}
		if(count($this->_orderColumns)>0){
			$sqlStatement.= ' ORDER BY ';
			$sqlStatement.= join(',', $this->_getSQLColumns($this->_orderColumns));
		}
		return $sqlStatement;
	}

	/**
	 * Agrega un JOIN del tipo indicado a la consulta para ser procesado posteriormente
	 *
	 * @param	string $joinEntity
	 * @param	int $joinType
	 */
	private function _addJoin($joinEntity, $joinType){
		$joinParts = explode(' ', $joinEntity);
		$entityJoinParts = explode('.', $joinParts[0]);
		if(isset($entityJoinParts[1])){
			if(EntityManager::isModel($entityJoinParts[1])){
				if(isset($joinParts[1])){
					$this->_entities[$joinParts[1]] = $entityJoinParts[1];
				}
				if(isset($this->_entities[$entityJoinParts[0]])){
					$this->_joins[$joinType][] = array($entityJoinParts[0], $joinParts[1]);
				}
			}
		} else {
			throw new ActiveRecordException('Definición de join incorrecta');
		}
	}

	/**
	 * Procesa el JOIN y agrega las clausulas y condiciones requeridas
	 *
	 * @param string $joinType
	 */
	private function _processJoin($joinType){
		$sqlStatement = '';
		foreach($this->_joins[$joinType] as $joinRelations){
			$mainEntity = $this->_entities[$joinRelations[0]];
			$joinEntity = $this->_entities[$joinRelations[1]];
			$hasRelation = false;
			if(EntityManager::existsHasMany($joinEntity, $mainEntity)){
				$relation = EntityManager::getHasManyDefinition($joinEntity, $mainEntity, true);
				$hasRelation = true;
			} else {
				if(EntityManager::existsBelongsTo($joinEntity, $mainEntity)){
					$relation = EntityManager::getBelongsToDefinition($joinEntity, $mainEntity);
					$hasRelation = true;
				} else {
					if(EntityManager::existsHasOne($joinEntity, $mainEntity)){
						$relation = EntityManager::getHasOneDefinition($joinEntity, $mainEntity);
						$hasRelation = true;
					}
				}
			}
			if($hasRelation==false){
				throw new ActiveRecordException('Las entidades "'.$mainEntity.'" y "'.$joinEntity.'" no tienen cardinalidad definida');
			}
			$joinSource = EntityManager::getCompleteSource($joinEntity);
			$mainSource = EntityManager::getCompleteSource($relation['referencedEntity']);
			switch($joinType){
				case self::LEFT_JOIN:
				case self::RIGHT_JOIN:
					if($joinType==self::LEFT_JOIN){
						$sqlStatement.=' LEFT OUTER JOIN ';
					} else {
						$sqlStatement.=' RIGHT OUTER JOIN ';
					}
					$sqlStatement.=$joinSource.' ON ';
					if(is_string($relation['fields'])){
						$sqlStatement.= $joinSource.'.'.$relation['fields'].' = '.$mainSource.'.'.$relation['referencedFields'];
						if($joinType==self::LEFT_JOIN){
							$condition = $joinSource.'.'.$relation['fields'].' IS NULL';
						} else {
							$condition = $relation['referencedSource'].'.'.$mainSource.' IS NULL';
						}
						$this->_appendCondition($condition);
					} else {
						$j = 0;
						$joinConditions = array();
						$whereConditions = array();
						foreach($relation['fields'] as $relationField){
							$joinConditions[] = $joinSource.'.'.$relationField.' = '.$mainSource.'.'.$relation['referencedFields'][$j];
							if($joinType==self::LEFT_JOIN){
								$whereConditions[] = $joinSource.'.'.$relationField.' IS NULL';
							} else {
								$whereConditions[] = $mainSource.'.'.$relation['referencedFields'][$j].' IS NULL';
							}
							$j++;
						}
						$sqlStatement.=join(' AND ', $joinConditions);
						$this->_appendCondition(join(' AND ', $whereConditions));
					}
					break;
				case self::INNER_JOIN:
					$sqlStatement.=' INNER JOIN '.$joinSource.' ON ';
					if(is_string($relation['fields'])){
						$sqlStatement.= $joinSource.'.'.$relation['fields'].' = '.$mainSource.'.'.$relation['referencedFields'];
					} else {
						$j = 0;
						$joinConditions = array();
						$whereConditions = array();
						foreach($relation['fields'] as $relationField){
							$joinConditions[] = $joinSource.'.'.$relationField.' = '.$mainSource.'.'.$relation['referencedFields'][$j];
							$j++;
						}
						$sqlStatement.=join(' AND ', $joinConditions);
					}
					break;
				case self::NATURAL_JOIN:
					$sqlStatement.=','.$joinSource;
					if(is_string($relation['fields'])){
						$condition = $joinSource.'.'.$relation['fields'].' = '.$mainSource.'.'.$relation['referencedFields'];
					} else {
						$j = 0;
						$joinConditions = array();
						$whereConditions = array();
						foreach($relation['fields'] as $relationField){
							$joinConditions[] = $joinSource.'.'.$relationField.' = '.$mainSource.'.'.$relation['referencedFields'][$j];
							$j++;
						}
						$condition = join(' AND ', $joinConditions);
					}
					$this->_appendCondition($condition);
					break;
				case self::FULL_JOIN:
					$sqlStatement.=' FULL OUTER JOIN '.$joinSource.' ON ';
					if(is_string($relation['fields'])){
						$sqlStatement.= $joinSource.'.'.$relation['fields'].' = '.$mainSource.'.'.$relation['referencedFields'];
						$condition = $joinSource.'.'.$relation['fields'].' IS NULL OR '.$mainSource.'.'.$relation['referencedFields'].' IS NULL';
						$this->_appendCondition($condition);
					} else {
						$j = 0;
						$joinConditions = array();
						$whereConditions = array();
						foreach($relation['fields'] as $relationField){
							$joinConditions[] = $joinSource.'.'.$relationField.' = '.$mainSource.'.'.$relation['referencedFields'][$j];
							$whereConditions[] = $joinSource.'.'.$relationField.' IS NULL OR '.$mainSource.'.'.$relation['referencedFields'][$j].' IS NULL';
							$j++;
						}
						$sqlStatement.=join(' AND ', $joinConditions);
						$this->_appendCondition(join(' AND ', $whereConditions));
					}
					break;
				case self::CROSS_JOIN:
					$sqlStatement.=','.$joinSource;
					break;
			}
		}
		return $sqlStatement;
	}

	/**
	 * Agrega una condición a la clausula WHERE de la consulta
	 *
	 * @param string $condition
	 */
	private function _appendCondition($condition){
		if($this->_whereCondition!==null){
			if(is_array($this->_whereCondition)){
				$this->_whereCondition[0] = '('.$this->_whereCondition[0].') AND '.$condition;
			} else {
				$this->_whereCondition = '('.$this->_whereCondition.') AND '.$condition;
			}
		} else {
			$this->_whereCondition = $condition;
		}
	}

	/**
	 * Reemplaza entidades por su source en un listado de columnas SQL
	 *
	 * @param	array $sqlColumns
	 * @return	array
	 */
	private function _getSQLColumns($sqlColumns){
		$sqlColumnsArray = array();
		foreach($sqlColumns as $sqlColumn){
			$strSqlColumn = '';
			$columnTokens = $this->_getSQLTokens($sqlColumn);
			foreach($columnTokens as $token){
				if(preg_match('/(\w+)\.(\w+)/', $token, $matches)){
					$entitySource = $this->_getEntitySourceByAlias($matches[1]);
					$strSqlColumn.= $entitySource.'.'.$matches[2];
				} else {
					$strSqlColumn.= $token;
				}
			}
			$sqlColumnsArray[] = $strSqlColumn;
		}
		return $sqlColumnsArray;
	}

	/**
	 * Obtiene los tokens de una clausula SQL
	 *
	 * @param	string $sqlClause
	 * @return 	array
	 */
	private function _getSQLTokens($sqlClause){
		$sqlTokens = array();
		$length = i18n::strlen($sqlClause);
		$token = '';
		for($i=0;$i<$length;++$i){
			$char = substr($sqlClause, $i, 1);
			if($char==' '||$char==','||$char=='('||$char==')'){
				if($token!=''){
					$sqlTokens[] = $token;
				}
				$sqlTokens[] = $char;
				$token = '';
			} else {
				$token.=$char;
			}
		}
		if($token!=''){
			$sqlTokens[] = $token;
		}
		return $sqlTokens;
	}

	/**
	 * Devuelve el source de una entidad apartir del alias en el KQL
	 *
	 * @param	string $alias
	 * @return 	string
	 * @throws  ActiveRecordException
	 */
	private function _getEntitySourceByAlias($alias){
		if(!isset($this->_entities[$alias])){
			throw new ActiveRecordException('El alias "'.$alias.'" no está asignado a ninguna entidad');
		} else {
			$entityName = $this->_entities[$alias];
			return EntityManager::getCompleteSource($entityName);
		}
	}

	/**
	 * Ejecuta la consulta
	 *
	 * @return	ActiveRecordResultset
	 */
	public function execute(){
		$sqlStatement = $this->getSQLString();
		if($this->_connection==null){
			$this->_connection = DbPool::getConnection();
		}
		$resultResource = $this->_connection->query($sqlStatement);
		$count = $this->_connection->numRows($resultResource);
		if($count>0){
			$rowObject = new ActiveRecordRow();
			$rowObject->setConnection($this->_connection);
			return new ActiveRecordResultset($rowObject, $resultResource, $sqlStatement);
		} else {
			return new ActiveRecordResultset(new stdClass(), false, $sqlStatement);
		}
	}

	/**
	 * Crea una nueva consulta
	 *
	 * @return ActiveRecordQuery
	 */
	public static function create(){
		return new self(null);
	}

}