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
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: DbRawValue.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * DbRawValue
 *
 * Clase que permite establecer los valores nativos de un determinado motor
 *
 * @category	Kumbia
 * @package		Db
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class DbRawValue extends Object {

	/**
	 * Valor Interno Almacenado
	 *
	 * @var mixed
	 */
	private $_value;

	/**
	 * Constructor de la clase
	 *
	 * @param mixed $value
	 */
	public function __construct($value){
		$this->_value = $value;
	}

	/**
	 * Devuelve el valor interno almacenado
	 *
	 * @return mixed
	 */
	public function getValue(){
		return $this->_value;
	}

}
