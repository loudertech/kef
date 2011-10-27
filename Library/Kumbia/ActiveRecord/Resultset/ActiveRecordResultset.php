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
 * @subpackage	ActiveRecordResultset
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ActiveRecordResultset.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * ActiveRecordResultset
 *
 * Los resultados devueltos por los métodos de consulta de ActiveRecord son
 * objetos instancias de la clase ActiveRecordResulset que encapsulan la
 * manipulación y obtención de los registros individuales en el cursor
 * enviado por el RBDM.
 *
 * La clase implementa las interfaces Iterator, ArrayAccess, SeekableIterator
 * y Countable con lo cuál el objeto se puede recorrer usando una sentencia
 * como foreach, acceder a indices individuales mediante el operador de
 * acceso de vectores y contar el total de registros usando funciones
 * como count ó sizeof.
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	ActiveRecordResultset
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class ActiveRecordResultset implements Iterator, ArrayAccess, SeekableIterator, Countable {

	/**
	 * Objeto ActiveRecord asociado al resultSet
	 *
	 * @var resource
	 */
	private $_activeRecordObject;

	/**
	 * Resulset cursor del resultado
	 *
	 * @var string
	 */
	private $_resultResource;

	/**
	 * Puntero interno para administrar el recorrido del cursor
	 *
	 * @var integer
	 */
	private $_pointer = 0;

	/**
	 * Numero de registros del cursor
	 *
	 * @var integer
	 */
	private $_count = null;

	/**
	 * Objeto ActiveRecord actual en el Iterador
	 *
	 * @var ActiveRecord
	 */
	private $_activeRow;

	/**
	 * Sentencia SQL ejecutada para obtener el resultado
	 *
	 * @var string
	 */
	private $_sqlQuery;

	/**
	 * Constructor del Resultset
	 *
	 * @param ActiveRecordBase $activeRecordObject
	 * @param resource $resultResource
	 * @param string $sqlQuery
	 */
	public function __construct($activeRecordObject, $resultResource, $sqlQuery){
		$this->_activeRecordObject = $activeRecordObject;
		$this->_resultResource = $resultResource;
		$this->_sqlQuery = $sqlQuery;
	}

	/**
	 * Indica si el cursor interno es valido aun
	 *
	 * @access public
	 * @return boolean
	 */
	public function valid(){
		if($this->_resultResource===false){
			return false;
		}
		$dbResource = $this->_activeRecordObject->getConnection();
		$dbResource->setFetchMode(DbBase::DB_ASSOC);
		$row = $dbResource->fetchArray($this->_resultResource);
		if($row){
			if(is_object($this->_activeRow)){
				unset($this->_activeRow);
			}
			$this->_activeRow = $this->_activeRecordObject->dumpResult($row);
			return true;
		} else {
			$dbResource->setFetchMode(DbBase::DB_BOTH);
			return false;
		}
	}

	/**
	 * Devuelve el record actual en el iterador
	 *
	 * @access	public
	 * @return	ActiveRecord
	 */
	public function current(){
		return $this->_activeRow;
	}

	/**
	 * Modifica el puntero interno en el iterador
	 *
	 * @access public
	 */
	public function next(){
		++$this->_pointer;
	}

	/**
	 * Devuelve la clave actual en el recorrido del iterador
	 *
	 * @access public
	 * @return integer
	 */
	public function key(){
		return $this->_pointer;
	}

	/**
	 * Devuelve el iterador a su estado inicial
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function rewind(){
		if($this->_resultResource===false){
			return false;
		}
		$this->pointer = 1;
		$dbResource = $this->_activeRecordObject->getConnection();
		$dbResource->dataSeek(0, $this->_resultResource);
	}

	/**
	 * Mueve el puntero interno del iterador a una posicion en especial
	 *
	 * @access	public
	 * @param	integer $position
	 */
	public function seek($position){
		$this->_pointer = (int) $position;
		$dbResource = $this->_activeRecordObject->getConnection();
		$dbResource->dataSeek((int) $position, $this->_resultResource);
	}

	/**
	 * Devuelve el número de registros del iterador
	 *
	 * @access	public
	 * @return	integer
	 */
	public function count(){
		if($this->_resultResource===false){
			return 0;
		}
		if($this->_count===null){
			$dbResource = $this->_activeRecordObject->getConnection();
			$this->_count = $dbResource->numRows($this->_resultResource);
		}
		return $this->_count;
	}

	/**
	 * Requerido por la interfase SeekableIterator
	 *
	 * @param	integer $index
	 * @return	boolean
	 */
	public function offsetExists($index){
		if($index<$this->count()){
			return true;
		}
	}

	/**
	 * Requerido por la interfase SeekableIterator
	 *
	 * @param	integer $index
	 * @return	ActiveRecord
	 */
	public function offsetGet($index){
		if($index<$this->count()){
			$this->seek($index);
			if($this->valid()){
				return $this->current();
			} else {
				return false;
			}
		} else {
			throw new ActiveRecordException('El indice no existe en el cursor');
		}
	}

	/**
	 * Requerido por la interfase SeekableIterator
	 *
	 * @param	string $index
	 * @param	mixed $value
	 */
	public function offsetSet($index, $value){
		throw new ActiveRecordException('El cursor es de solo lectura');
	}

	/**
	 * Requerido por la interfase SeekableIterator
	 *
	 * @access	public
	 * @param	integer $offset
	 */
	public function offsetUnset($offset){
		throw new ActiveRecordException('El cursor es de solo lectura');
	}

	/**
	 * Obtiene el primer registro del cursor
	 *
	 * @return	ActiveRecord
	 */
	public function getFirst(){
		if($this->_pointer!=1){
			$this->rewind();
		}
		if($this->_resultResource!==false){
			if($this->valid()==true){
				return $this->current();
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Obtiene el último registro del cursor
	 *
	 * @return ActiveRecord
	 */
	public function getLast(){
		if($this->_resultResource!==false){
			$this->seek($this->count()-1);
			if($this->valid()==true){
				return $this->current();
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Obtiene el SQL con el que se generó la consulta
	 *
	 * @return string
	 */
	public function getSQLQuery(){
		return $this->_sqlQuery;
	}

	/**
	 * Devuelve la entidad que produjo el resultado
	 *
	 * @return string
	 */
	public function getEntity(){
		return $this->_activeRecordObject;
	}

	/**
	 * Recorre un cursor
	 *
	 * @param lambda $lambda
	 */
	public function each($lambda){
		$this->rewind();
		while($this->valid()){
			$record = $this->current();
			$lambda($record);
			$this->next();
		}
	}

}
