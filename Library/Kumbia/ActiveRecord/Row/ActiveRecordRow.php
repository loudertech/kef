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
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ActiveRecordRow.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * ActiveRecordRow
 *
 * Permite crear una instancia de un resultado de un ActiveRecordJoin
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	ActiveRecordJoin
 * @copyright	Copyright (c) 2008-20010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class ActiveRecordRow extends Object
#if[compile-time]
	implements ActiveRecordResultInterface
#endif
	{

	/**
	 * Conexion al motor con el que se hara la consulta
	 *
	 * @var dbBase
	 */
	private $_db;

	/**
	 * Columnas del resultset
	 *
	 * @var array
	 */
	private $_columns = array();

	/**
	 * Constructor de la clase
	 * @access public
	 */
	public function __construct(){

	}

	/**
	 * Establece la conexión interna para obtener los resultados
	 *
	 * @param	DbBase $db
	 * @access	public
	 */
	public function setConnection($db){
		$this->_db = $db;
	}

	/**
	 * Devuelve el objeto de conexión interna
	 *
	 * @access	public
	 * @return	DbBase
	 */
	public function getConnection(){
		return $this->_db;
	}

	/**
	 * Devuelve un resultado con los valores establecidos
	 *
	 * @access	public
	 * @param	array $result
	 */
	public function dumpResult(array $result){
		$objectRow = clone $this;
		if(count($this->_columns)==0){
			$columns = array();
			foreach($result as $field => $value){
				$columns[$field] = true;
				$objectRow->$field = $value;
			}
			$objectRow->_columns = $columns;
			$this->_columns = $columns;
		} else {
			foreach($result as $field => $value){
				$objectRow->$field = $value;
			}
			$objectRow->_columns = $this->_columns;
		}
		return $objectRow;
	}

	/**
	 * Envia una excepcion cuando se accede a una propiedad no inicializada
	 *
	 * @access	public
	 * @param	string $property
	 * @throws	ActiveRecordException
	 */
	public function __get($property){
		throw new ActiveRecordException('No existe el atributo "'.$property.'" en la consulta');
	}

	/**
	 * Lee un atributo del resultado por su nombre
	 *
	 * @param string $property
	 * @return mixed
	 */
	public function readAttribute($property){
		return $this->$property;
	}

	/**
	 * Genera una excepción cuando se trata de sobreescribir algun miembro del resultado
	 *
	 * @access public
	 * @param string $property
	 * @param string $value
	 */
	#public function __set($property, $value){
		#throw new ActiveRecordException("El resultset es de solo lectura");
	#}

	/**
	 * Permite obtener los valores mediantes
	 *
	 * @param	string $method
	 * @param	array $arguments
	 * @return 	mixed
	 * @throws	ActiveRecordException
	 */
	public function __call($method, $arguments=array()){
		if(substr($method, 0, 3)=='get'){
			$property = Utils::uncamelize(substr($method, 3));
			if(isset($this->_columns[$property])){
				return $this->$property;
			} else {
				throw new ActiveRecordException('El método "'.$method.'" ó atributo "'.$property.'" del resultset no existe');
			}
		} else {
			throw new ActiveRecordException('El método ó atributo "'.$method.'" del resultset no existe');
		}
	}

	/**
	 * Método mágico Sleep
	 *
	 * @return 	array
	 */
	public function sleep(){
		return array('_columns');
	}

}
