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
 * @package		ActiveRecord
 * @subpackage	ActiveRecordJoin
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ActiveRecordJoin.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * ActiveRecordJoin
 *
 * El subcomponente ActiveRecordJoin permite aprovechar las relaciones
 * establecidas en el modelo de datos para generar consultas simples ó
 * con agrupamientos en más de 2 entidades relacionadas ó no relacionadas,
 * proponiendo una forma adicional de utilizar el Object-Relational-Mapping (ORM).
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	ActiveRecordJoin
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class ActiveRecordJoin extends Object {

	/**
	 * Conexion al motor con el que se hará la consulta
	 *
	 * @access	private
	 * @var		dbBase
	 */
	private $_db = null;

	/**
	 * Consulta generada apartir de los parámetros
	 *
	 * @access	private
	 * @var		string
	 */
	private $_sqlQuery;

	/**
	 * Parametros con los que ejecuta la consulta
	 *
	 * @var string
	 */
	private $_params = array();

	/**
	 * Constructor de la clase
	 *
	 * @access public
	 */
	public function __construct($params){
		$this->_buildSQLQuery($params);
		$this->_params = $params;
	}

	/**
	 * Construye el SQL de la consulta
	 *
	 * @param	array $params
	 * @return	string
	 */
	private function _buildSQLQuery($params){
		#if[compile-time]
		if(!isset($params['entities'])||count($params['entities'])==0){
			throw new ActiveRecordException('Debe indicar las entidades con las que se hará la consulta');
		}
		#endif
		$entitiesSources = array();
		$groupFields = array();
		$requestedFields = array();
		foreach($params['entities'] as $entityName){
			$entitiesSources[$entityName] = EntityManager::getCompleteSource($entityName);
		}
		if(!isset($params['fields'])){
			if(isset($params['groupFields'])){
				foreach($params['groupFields'] as $alias => $field){
					if(preg_match('/\{\#([a-zA-Z0-9\_]+)\}/', $field, $regs)){
						if(!isset($entitiesSources[$regs[1]])){
							throw new ActiveRecordException('La entidad "'.$regs[1].'" en los campos solicitados no se encontró en la lista de entidades a agrupar');
						} else {
							$sqlField = str_replace($regs[0], $entitiesSources[$regs[1]], $field);
							if(!is_numeric($alias)){
								$requestedFields[] = $sqlField.' AS '.$alias;
							} else {
								$requestedFields[] = $sqlField;
							}
							if(strpos($sqlField, ' ')==false){
								$groupFields[] = $sqlField;
							} else {
								$groupFields[] = substr($sqlField, 0, strpos($sqlField, ' '));
							}
						}
					} else {
						$groupFields[] = $field;
					}
				}
			}
			$groupFunctions = array(
				'sumatory' => 'SUM',
				'count' => 'COUNT',
				'minimum' => 'MIN',
				'maximum' => 'MAX',
				'average' => 'AVG',
			);
			foreach($groupFunctions as $key => $function){
				if(isset($params[$key])){
					if(is_array($params[$key])){
						foreach($params[$key] as $alias => $field){
							$this->_groupFunction($requestedFields, $function, $alias, $field, $entitiesSources);
						}
					} else {
						$this->_groupFunction($requestedFields, $function, $key, $params[$key], $entitiesSources);
					}
				}
			}
		} else {
			$requestedFields = array();
			if(is_array($params['fields'])){
				foreach($params['fields'] as $alias => $field){
					if(preg_match('/\{\#([a-zA-Z0-9]+)\}/', $field, $regs)){
						if(!in_array($regs[1], $params['entities'])){
							throw new ActiveRecordException("La entidad '{$regs[1]}' en los campos solicitados no se encontró en la lista de entidades");
	 					} else {
	 						if(is_numeric($alias)){
	 							$requestedFields[] = str_replace($regs[0], $entitiesSources[$regs[1]], $field);
	 						} else {
	 							$alias = (string) $alias;
	 							$requestedFields[] = str_replace($regs[0], $entitiesSources[$regs[1]], $field).' AS '.$alias;
	 						}
	 					}
					} else {
						if(is_numeric($alias)){
							$requestedFields[] = $field;
						} else {
							$alias = (string) $alias;
							$requestedFields[] = $field.' AS '.$alias;
						}
					}
				}
			}
		}
		$join = array();
		if(!isset($params['noRelations'])||$params['noRelations']==false){
			foreach($params['entities'] as $entityName){
				$relations = EntityManager::getRelationsOf($entityName);
				if(count($relations)>0){
					if(isset($relations['belongsTo'])){
						foreach($params['entities'] as $relationEntity){
							if($relationEntity!=$entityName){
								if(isset($relations['belongsTo'][$relationEntity])){
									$belongsTo = $relations['belongsTo'][$relationEntity];
									$source = $entitiesSources[$entityName];
									if(!is_array($belongsTo['rf'])){
										$sourceName = EntityManager::getSourceName($belongsTo['rt']);
										$join[] = $sourceName.'.'.$belongsTo['rf'].' = '.$source.'.'.$belongsTo['fi'];
									} else {
										$i = 0;
										$sourceName = EntityManager::getSourceName($belongsTo['rt']);
										foreach($belongsTo['rf'] as $rf){
											$join[] = $sourceName.'.'.$rf.' = '.$source.'.'.$belongsTo['fi'][$i];
											++$i;
										}
									}
								}
							}
						}
					}
					if(isset($relations['hasMany'])){
						foreach($params['entities'] as $relationEntity){
							if($relationEntity!=$entityName){
								if(isset($relations['hasMany'][$relationEntity])){
									$hasMany = $relations['hasMany'][$relationEntity];
									$source = $entitiesSources[$entityName];
									$sourceName = EntityManager::getSourceName($hasMany['rt']);
									if(!is_array($hasMany['rf'])){
										$join[] = $source.'.'.$hasMany['rf'].' = '.$sourceName.'.'.$hasMany['fi'];
									} else {
										$i = 0;
										foreach($hasMany['rf'] as $rf){
											$join[] = $source.'.'.$rf.' = '.$sourceName.'.'.$hasMany['fi'][$i];
											++$i;
										}
									}
								}
							}
						}
					}
				}
			}
			if(count($params['entities'])>1&&count($join)==0){
				if(isset($params['noRelations'])){
					if($params['noRelations']==false){
						throw new ActiveRecordException('No se pudo encontrar las relaciones entre las entidades');
					}
				} else {
					throw new ActiveRecordException('No se pudo encontrar las relaciones entre las entidades');
				}
			} else {
				$join = array_unique($join);
				if(isset($params['conditions'])){
					if($params['conditions']!=""){
						foreach($params['entities'] as $entityName){
							$params['conditions'] = str_replace('{#'.$entityName.'}', $entitiesSources[$entityName], $params['conditions']);
						}
						$join[] = $params['conditions'];
					}
				}
			}
		} else {
			if(isset($params['conditions'])){
				if($params['conditions']!=''){
					foreach($params['entities'] as $entityName){
						$params['conditions'] = str_replace('{#'.$entityName.'}', $entitiesSources[$entityName], $params['conditions']);
					}
					$join[] = $params['conditions'];
				}
			}
		}
		if(isset($params['order'])){
			if(!is_array($params['order'])){
				foreach($params['entities'] as $entityName){
					$params['order'] = str_replace('{#'.$entityName.'}', $entitiesSources[$entityName], $params['order']);
				}
				$order = $params['order'];
			} else {
				foreach($params['order'] as $key => $valueOrder){
					if(preg_match('/\{\#([a-zA-Z0-9]+)\}/', $valueOrder, $regs)){
						if(in_array($regs[1], $params['entities'])){
							$params['order'][$key] = str_replace('{#'.$regs[1].'}', $entitiesSources[$regs[1]], $valueOrder);
						} else {
							throw new DbSQLGrammarException('No se encuentra la entidad "'.$regs[1].'" en la lista de ordenamiento', 0);
						}
					}
				}
				$order = join(',', $params['order']);
			}
		} else {
			$order = '1';
		}
		if($this->_db===null){
			$this->_db = DbPool::getConnection();
		}
		if(count($requestedFields)>0){
			$fields = join(', ', $requestedFields);
		} else {
			$fields = '*';
		}
		$this->_sqlQuery = 'SELECT '.$fields.' FROM '.join(', ', $entitiesSources).' WHERE '.join(' AND ', $join);
		if(count($groupFields)){
			$this->_sqlQuery.= ' GROUP BY '.join(', ', $groupFields);
		}
		if(isset($params['having'])){
			$this->_sqlQuery.=' HAVING '.$params['having'];
		}
		$this->_sqlQuery.=' ORDER BY '.$order;
		if(isset($params['limit'])){
			$this->_sqlQuery = $this->_db->limit($this->_sqlQuery, $params['limit']);
		}
	}

	/**
	 * Crea el SQL de una función agrupamiento
	 *
	 * @param array $requestedFields
	 * @param string $function
	 * @param string $alias
	 * @param string $field
	 */
	private function _groupFunction(&$requestedFields, $function, $alias, $field, $entitiesSources){
		$existsEntity = false;
		$replacedField = $field;
		while(preg_match('/\{\#([a-zA-Z0-9\_]+)\}/', $replacedField, $regs)){
			if(!isset($entitiesSources[$regs[1]])){
				throw new ActiveRecordException('La entidad '.$regs[1].' en los campos solicitados no se encontró en la lista de entidades con función de agrupamiento');
			} else {
				$replacedField = str_replace($regs[0], $entitiesSources[$regs[1]], $replacedField);
				if(is_numeric($alias)){
					if(strpos($replacedField, '.')==false){
						$alias = $replacedField;
					} else {
						$alias = substr($replacedField, strpos($replacedField, '.')+1);
					}
				}
			}
			$existsEntity = true;
		}
		if($existsEntity==false){
			if($alias==''||is_numeric($alias)){
				$requestedFields[] = $function.'('.$field.')';
			} else {
				$requestedFields[] = $function.'('.$field.') AS '.$alias;
			}
		} else {
			$requestedFields[] = $function.'('.$replacedField.') AS '.$alias;
		}
	}

	/**
	 * Coloca la conexión en modo debug
	 *
	 * @param boolean $debug
	 */
	public function setDebug($debug){
		if($this->_db===null){
			$this->_db = DbPool::getConnection();
		}
		$this->_db->setDebug($debug);
	}

	/**
	 * Devuelve los resultados del JOIN
	 *
	 * @access public
	 * @return ActiveRecordResultset
	 */
	public function getResultSet(){
		$resultResource = $this->_db->query($this->_sqlQuery);
		$count = $this->_db->numRows($resultResource);
		if($count>0){
			$rowObject = new ActiveRecordRow();
			$rowObject->setConnection($this->_db);
			return new ActiveRecordResultset($rowObject, $resultResource, $this->_sqlQuery);
		} else {
			return new ActiveRecordResultset(new stdClass(), false, $this->_sqlQuery);
		}
	}

	/**
	 * Simula la ejecución de un find sobre las entidades del JOIN
	 *
	 * @param mixed $params
	 * @return ActiveRecordResultset
	 */
	public function find($conditions=''){
		$params = $this->_params;
		if(!is_array($conditions)){
			$params['conditions'] = $conditions;
		} else {
			$params = array_merge($params, $conditions);
		}
		$this->_buildSQLQuery($params);
		return $this->getResultSet();
	}

	/**
	 * Devuelve el SQL interno generado
	 *
	 * @access public
	 * @return string
	 */
	public function getSQLQuery(){
		return $this->_sqlQuery;
	}

	/**
	 * Obtiene un resumen de la sumatoria de los valores de una columna
	 *
	 * @access public
	 * @param string $columnName
	 */
	public function getSummaryBy($columnName){
		$resulSet = $this->getResultSet();
		$summary = array();
		foreach($resultSet as $result){
			#$summary->
		}
	}

}
