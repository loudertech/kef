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
 * @subpackage	Profiler
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */

/**
 * DbProfilerItem
 *
 * Item de cada estadistica de Profile
 *
 * @category	Kumbia
 * @package		Db
 * @subpackage	Profiler
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class DbProfilerItem extends Object {

	/**
	 * Setencia SQL del Profile
	 *
	 * @var string
	 */
	private $_sqlStatement;

	/**
	 * Microtime cuando empezo el profile
	 *
	 * @var float
	 */
	private $_inialTime;

	/**
	 * Microtime cuando termino el profile
	 *
	 * @var float
	 */
	private $_finalTime;

	/**
	 * Establece la Setencia SQL del Profile
	 *
	 * @access public
	 * @param string $sqlStatement
	 */
	public function setSQLStatement($sqlStatement){
		$this->_sqlStatement = $sqlStatement;
	}

	/**
	 * Devuelve la sentencia SQL interna
	 *
	 * @access public
	 * @return string
	 */
	public function getSQLStatement(){
		return $this->_sqlStatement;
	}

	/**
	 * Establece el tiempo inicial del profile
	 *
	 * @param int $initialTime
	 */
	public function setInitialTime($initialTime){
		$this->_initialTime = $initialTime;
	}

	/**
	 * Establece el tiempo final del profile
	 *
	 * @param int $finalTime
	 */
	public function setFinalTime($finalTime){
		$this->_finalTime = $finalTime;
	}

	/**
	 * Devuelve el tiempo en milisegundos en que empezo a procesarse la sentencia SQL
	 *
	 * @access public
	 * @return float
	 */
	public function getInitialTime(){
		return $this->_initialTime;
	}

	/**
	 * Devuelve el tiempo en milisegundos en que termino de procesarse la sentencia SQL
	 *
	 * @access public
	 * @return float
	 */
	public function getFinalTime(){
		return $this->_finalTime;
	}

	/**
	 * Develve el tiempo total que han durado el profile
	 *
	 * @access public
	 * @return float
	 */
	public function getTotalElapsedSeconds(){
		return $this->_finalTime-$this->_initialTime;
	}

}
