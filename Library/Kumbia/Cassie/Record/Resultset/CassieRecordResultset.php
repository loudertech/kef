<?php

class CassieRecordResultset implements Iterator, ArrayAccess, SeekableIterator, Countable {

	private $_columnList;

	private $_cassieRecord;

	private $_pointer;

	private $_count = 0;

	/**
	 * Constructor de CassieResulset
	 *
	 * @param	CassieRecord $cassieRecord
	 * @param	array $columnList
	 */
	public function __construct(CassieRecord $cassieRecord, array $columnList){
		$this->_columnList = $columnList;
		$this->_cassieRecord = $cassieRecord;
		$this->_count = count($columnList);
	}

	/**
	 * Indica si el cursor interno es válido aún
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function valid(){
		if(isset($this->_columnList[$this->_pointer])){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Devuelve el record actual en el iterador
	 *
	 * @access	public
	 * @return	CassieRecord
	 */
	public function current(){
		$column = $this->_columnList[$this->_pointer];
		return $this->_cassieRecord->dumpResult($column);
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
		$this->_pointer = 0;
	}

	/**
	 * Mueve el puntero interno del iterador a una posición en especial
	 *
	 * @access	public
	 * @param	integer $position
	 */
	public function seek($position){
		$this->_pointer = (int) $position;
	}

	/**
	 * Devuelve el número de registros del iterador
	 *
	 * @access	public
	 * @return	integer
	 */
	public function count(){
		return $this->_count;
	}

	/**
	 * Requerido por la interfase SeekableIterator
	 *
	 * @param	integer $index
	 * @return	boolean
	 */
	public function offsetExists($index){
		if($index<$this->_count){
			return true;
		}
	}

	/**
	 * Requerido por la interfase SeekableIterator
	 *
	 * @param	integer $index
	 * @return	CassieRecord
	 */
	public function offsetGet($index){
		if($index<$this->_count){
			$this->seek($index);
			if($this->valid()){
				return $this->current();
			} else {
				return false;
			}
		} else {
			throw new CassieRecordException('El indice no existe en el cursor');
		}
	}

	/**
	 * Requerido por la interfase SeekableIterator
	 *
	 * @param	string $index
	 * @param	mixed $value
	 */
	public function offsetSet($index, $value){
		throw new CassieRecordException('El cursor es de solo lectura');
	}

	/**
	 * Requerido por la interfase SeekableIterator
	 *
	 * @access	public
	 * @param	integer $offset
	 */
	public function offsetUnset($offset){
		throw new CassieRecordException('El cursor es de solo lectura');
	}

	/**
	 * Obtiene el primer registro del cursor
	 *
	 * @return	CassieRecord
	 */
	public function getFirst(){
		if($this->_pointer!=0){
			$this->rewind();
		}
		if($this->valid()==true){
			return $this->current();
		} else {
			return false;
		}
	}

	/**
	 * Obtiene el último registro del cursor
	 *
	 * @return CassieRecord
	 */
	public function getLast(){
		$this->seek($this->_count-1);
		if($this->valid()==true){
			return $this->current();
		} else {
			return false;
		}
	}

}