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
 * @package		ActionHelpers
 * @subpackage	FormCriteria
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: FormCriteria.php,v f5add30bf4ba 2011/10/26 21:05:13 andres $
 */

/**
 * FormCriteria
 *
 * Facilita la construcción de condiciones de búsqueda a partir de la entrada de formularios
 *
 * @category	Kumbia
 * @package		ActionHelpers
 * @subpackage	FormCriteria
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 * @abstract
 */
abstract class FormCriteria {

	/**
	 * Indica si se debe filtrar de la superglobal $_POST
	 *
	 */
	const POST = 1;

	/**
	 * Indica si se debe filtrar de la superglobal $_GET
	 *
	 */
	const GET = 2;

	/**
	 * Indica si se debe filtrar de la superglobal $_REQUEST
	 *
	 */
	const REQUEST = 3;

	/**
	 * Tipos de dato int
	 *
	 * @var array
	 */
	private static $_intTypes = array('int', 'smallint', 'bigint');

	/**
	 * Tipos de dato double
	 *
	 * @var array
	 */
	private static $_doubleTypes = array('decimal', 'number', 'float', 'smallfloat');

	/**
	 * Condiciones temporales de la consulta
	 *
	 * @var array
	 */
	private $_conditions = array();

	/**
	 * Constructor de FormCritera
	 *
	 * @todo Falta por rango de campos
	 * @param array $provider
	 * @param array $criteria
	 */
	public function __construct($provider, $criteria){
		$conditions = array();
		$magicQuotes = get_magic_quotes_gpc();
		$mcriteria = array();
		foreach($criteria as $key => $descriptor){
			if(strpos($key, ':')!==false){
				$fields = explode(':', $key);
				foreach($fields as $field){
					$mcriteria[$field] = $descriptor;
					$mcriteria[$field]['subcondition'] = true;
					$mcriteria[$field]['joinOperator'] = 'AND';
				}
				$mcriteria[$fields[0]]['operator'] = '>=';
				$mcriteria[$fields[1]]['operator'] = '<=';
			} else {
				if(isset($descriptor['fieldName'])){
					if(strpos($descriptor['fieldName'], ':')!==false){

					}
				} else {
					$mcriteria[$key] = $descriptor;
				}
			}
		}
		foreach($mcriteria as $key => $descriptor){
			if(isset($provider[$key])){
				if(isset($descriptor['fieldName'])){
					$fieldName = $descriptor['fieldName'];
				} else {
					$fieldName = $key;
				}
				if(isset($descriptor['type'])){
					switch($descriptor['type']){
						case 'integer':
							if($provider[$key]!==null&&$provider[$key]!==''){
								$value = Filter::bring($provider[$key], 'int');
							} else {
								$value = null;
							}
							break;
						case 'double':
						case 'float':
							if($provider[$key]!==null&&$provider[$key]!==''){
								$value = Filter::bring($provider[$key], 'double');
							} else {
								$value = null;
							}
							break;
						case 'date':
							$value = Filter::bring($provider[$key], 'date');
							break;
						case 'string':
							$value = $provider[$key];
							if($magicQuotes==false){
								$value = addslashes($value);
							}
							if(!isset($descriptor['operator'])){
								$descriptor['operator'] = 'LIKE';
								$value = preg_replace('/[ ]+/', '%', $value);
								$value = "'%".$value."%'";
							} else {
								$value = "'".$value."'";
							}
							break;
						default:
							$value = $provider[$key];
					}
					if(isset($descriptor['missOnNull'])&&$descriptor['missOnNull']==false){
						$this->addCondition($descriptor, $fieldName, $value);
					} else {
						if(!isset($descriptor['nullValue'])){
							if($provider[$key]!==''&&$provider[$key]!==null){
								$this->addCondition($descriptor, $fieldName, $value);
							}
						} else {
							if($provider[$key]!=$descriptor['nullValue']){
								$this->addCondition($descriptor, $fieldName, $value);
							}
						}
					}
				} else {
					$value = $provider[$key];
					if($magicQuotes==false){
						$value = addslashes($value);
					}
					$this->addCondition($descriptor, $fieldName, $value);
				}
			}
		}
		$this->_post = $_POST;
	}

	/**
	 * Agrega una condicion
	 *
	 * @param array $descriptor
	 * @param string $fieldName
	 * @param mixed $value
	 */
	private function addCondition($descriptor, $fieldName, $value){
		if(!isset($descriptor['operator'])){
			$op = '=';
		} else {
			$op = $descriptor['operator'];
		}
		if(isset($descriptor['subcondition'])&&$descriptor['subcondition']){
			if(isset($descriptor['joinOperator'])){
				$this->_conditions[$descriptor['joinOperator']][] = $fieldName.' '.$op.' '.$value;
			} else {
				$this->_conditions['AND'][] = $fieldName.' '.$op.' '.$value;
			}
		} else {
			$this->_conditions[0][] = $fieldName.' '.$op.' '.$value;
		}
	}

	/**
	 * Obtiene las condiciones
	 *
	 * @param string $joinOperator
	 * @return string
	 */
	public function getConditions($joinOperator='OR'){
		if(isset($this->_conditions['AND'])){
			$andConditions = '('.join(' AND ', $this->_conditions['AND']).')';
			$this->_conditions[0][] = $andConditions;
		}
		return join(' '.$joinOperator.' ', $this->_conditions[0]);
	}

	/**
	 * Une 2 ó más objetos FormCriteria
	 *
	 * @param string $operator
	 * @param array $criteriaArray
	 * @return string
	 */
	public static function join($operator, $criteriaArray){
		return join(' '.$operator.' ', $criteriaArray);
	}

	/**
	 * Obtiene una superglobal y convierte todo un string con condiciones segun modelo
	 *
	 * @param	string $modelName
	 * @param	int $superglobal
	 * @return	string
	 */
	public static function fromModel($modelName, $globalType){

		$superGlobal = array();
		if($globalType==self::POST){
			$superGlobal = $_POST;
		} else {
			if($globalType==self::GET){
				$superGlobal = $_GET;
			} else {
				$superGlobal = $_REQUEST;
			}
		}

		$conditions = array();
		$dataTypes = EntityManager::get($modelName)->getDataTypes();
		foreach($dataTypes as $fieldName => $dataType){
			$indexName = Utils::lcfirst(Utils::camelize($fieldName));
			if(isset($superGlobal[$indexName])){
				$value = null;
				if(self::_isIntType($dataType['Type'])){
					$operator = '=';
					$value = Filter::bring($superGlobal[$indexName], 'int');
				} else {
					$operator = 'LIKE';
					$value = Filter::bring($superGlobal[$indexName], 'striptags', 'extraspaces');
				}
				if($value!==null&&$value!==''&&$value!='@'){
					if($operator=='LIKE'){
						$conditions[] = $fieldName.' LIKE \'%'.$value.'%\'';
					} else {
						$conditions[] = $fieldName.' = \''.$value.'\'';
					}
				}
			}
		}
		if(count($conditions)>0){
			return implode(' AND ', $conditions);
		} else {
			return '1 = 1';
		}
	}

	/**
	 * Indica si el tipo de dato es entero
	 *
	 * @param	string $type
	 * @return	boolean
	 */
	private static function _isIntType($type){
		foreach(self::$_intTypes as $intType){
			if(strpos($type, $intType)!==false){
				return true;
			}
		}
		return false;
	}

	/**
	 * Metodo que hace bring por el metadata del campo en la tabla
	 *
	 * @param	$metaDataField
	 * @return	$value
	 */
	public static function bringByMetaDataField($metaDataField, $value){
		$filterType = 'alpha';
		//int
		$numericTypes = array('int', 'smallint', 'bigint');
		foreach($numericTypes as $type){
			if(strstr($metaDataField['Type'], $type)==true){
				$filterType = 'int';
			}
		}
		//double
		$numericTypes = array('decimal', 'number', 'float', 'smallfloat');
		foreach($numericTypes as $type){
			if(strstr($metaDataField['Type'], $type)==true){
				$filterType = 'double';
			}
		}
		$value = self::bring($value, $filterType);
		return $value;
	}

}
