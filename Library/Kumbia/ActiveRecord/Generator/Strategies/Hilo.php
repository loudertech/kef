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
 * @subpackage	Generator
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */

/**
 * HiloGenerator
 *
 * Genera identificadores usando el algoritmo Hi/Lo
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	Generator
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class HiloGenerator implements ActiveRecordGeneratorInterface {

	/**
	 * Schema donde esta la tabla
	 *
	 * @var string
	 */
	private $_schema;

	/**
	 * Tabla donde está el consecutivo
	 *
	 * @var string
	 */
	private $_table;

	/**
	 * Columna del consecutivo
	 *
	 * @var string
	 */
	private $_column;

	/**
	 * Numero de consecutivos cacheados por el generador
	 *
	 * @var int
	 */
	private $_number = 3;

	/**
	 * Columna del objeto que es identidad
	 *
	 * @var string
	 */
	private $_identityColumn;

	/**
	 * Consecutivo actual
	 *
	 * @var int
	 */
	private $_activeConsecutive;

	/**
	 * Consecutivo actual
	 *
	 * @var int
	 */
	private $_initialConsecutive;

	/**
	 * Consecutivos generados
	 *
	 * @var array
	 */
	private $_consecutives = array();

	/**
	 * Conexion donde se encuentra la conexion
	 *
	 * @var DbBase
	 */
	private $_connection;

	/**
	 * Establece las opciones del generador
	 *
	 * @param array $options
	 */
	public function setOptions($options){
		foreach($options as $option => $value){
			$this->{"_".$option} = $value;
		}
		if($this->_table==""){
			throw new ActiveRecordGeneratorException("Debe indicar la tabla para el generador 'Hi/lo'");
		}
		if($this->_column==""){
			throw new ActiveRecordGeneratorException("Debe indicar la columna para el generador 'Hi/lo'");
		}
	}

	/**
	 * Establece el nombre de la columna identidad
	 *
	 * @param string $identityColumn
	 */
	public function setIdentityColumn($identityColumn){
		$this->_identityColumn = $identityColumn;
	}

	/**
	 * Objeto que solicita el identificador
	 *
	 * @param ActiveRecord $record
	 */
	public function setIdentifier($record){
		if(count($this->_consecutives)==0){
			$this->_connection = $record->getConnection();
			$sql = 'SELECT '.$this->_column.' FROM '.$this->_table;
			if($this->_connection->isUnderTransaction()==true){
				$sql = $this->_connection->forUpdate($sql);
				$row = $this->_connection->fetchOne($sql);
				$this->_consecutives = range($row[$this->_column]+1, $row[$this->_column]+($this->_number-1));
				$this->_activeConsecutive = $row[$this->_column];
			} else {
				$row = $this->_connection->fetchOne($sql);
			}
			$this->_initialConsecutive = $row[$this->_column];
		} else {
			$this->_activeConsecutive = array_shift($this->_consecutives);
		}
		$record->writeAttribute($this->_identityColumn, $this->_activeConsecutive);
	}

	/**
	 * Actualiza el consecutivo en la BD
	 *
	 * @return boolean
	 */
	public function updateConsecutive($record){
		if(count($this->_consecutives)==0){
			$newConsecutive = $this->_activeConsecutive+1;
			$sql = 'UPDATE '.$this->_table.' SET '.$this->_column.' = '.$newConsecutive.' WHERE '.$this->_column.' = '.$this->_initialConsecutive;
			$this->_connection->query($sql);
		}
		return true;
	}

	/**
	 * Finaliza el generador
	 *
	 */
	public function finalizeConsecutive(){
		if(count($this->_consecutives)!=0){
			$newConsecutive = $this->_activeConsecutive+1;
			$sql = 'UPDATE '.$this->_table.' SET '.$this->_column.' = '.$newConsecutive.' WHERE '.$this->_column.' = '.$this->_initialConsecutive;
			$this->_connection->query($sql);
		}
	}

}
