<?php

/**
 * Kumbia Enteprise Framework
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
 * @category 	Kumbia
 * @package 	Session
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: SessionRecord.php 88 2009-09-19 19:10:13Z gutierrezandresfelipe $
 */

/**
 * SessionRecord
 *
 * Clase que actua como un ActiveRecord de Session
 *
 * @category 	Kumbia
 * @package 	Session
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @access 		public
 */
class SessionRecord extends ActiveRecordBase {

	/**
	 * Campo que mantiene separado los datos de cada sesión
	 *
	 * @var string
	 */
	private $_bindSessionId = "sid";

	/**
	 * Constructor de SessionRecord
	 *
	 */
	public function __construct(){
		parent::__construct();
		$this->{$this->_bindSessionId} = Session::getId();
	}

	/**
	 * Establece el campo Sid en el modelo
	 *
	 * @param string $sidField
	 */
	public function bindSessionId($sidField){
		$this->_bindSessionId = $sidField;
	}

	/**
	 * Find data on Relational Map table
	 *
	 * @access	public
	 * @param 	string $params
	 * @return 	ActiveRecordResulset
	 */
	public function find($params=''){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(isset($params[0])){
			$params[0].=' AND '.$this->_bindSessionId.' = \''.Session::getId().'\'';
		} else {
			if(isset($params['conditions'])){
				$params['conditions'].=' AND '.$this->_bindSessionId.' = \''.Session::getId().'\'';
			}
		}
		parent::find($arguments);
	}

	/**
	 * Find first record by conditions
	 *
	 * @access	public
	 * @param 	string $params
	 * @return 	ActiveRecordResulset
	 */
	public function findFirst($params=''){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(isset($params[0])){
			$params[0].=' AND '.$this->_bindSessionId.' = \''.Session::getId().'\'';
		} else {
			if(isset($params['conditions'])){
				$params['conditions'].=' AND '.$this->_bindSessionId.' = \''.Session::getId().'\'';
			}
		}
		parent::findFirst($arguments);
	}

	/**
	 * Guarda un registro
	 *
	 */
	public function save(){
		$this->{$this->_bindSessionId} = Session::getId();
		parent::save();
	}

}
