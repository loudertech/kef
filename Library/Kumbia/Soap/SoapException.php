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
 * @category 	Kumbia
 * @package 	Session
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: SoapException.php 88 2009-09-19 19:10:13Z gutierrezandresfelipe $
 */

/**
 * SoapException
 *
 * Excepciones generadas por el componente Soap
 *
 * @category 	Kumbia
 * @package 	Soap
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 */
class SoapException extends CoreException {

	/**
	 * Fault codes
	 *
	 * @var string
	 */
	private $_faultCode = 'Server';

	/**
	 * Establece el tipo de codigo de falta
	 *
	 * @param string $faultCode
	 */
	public function setFaultCode($faultCode){
		$this->_faultCode = $faultCode;
	}

	/**
	 * Obtiene el code de la fault ocurrida
	 *
	 * @return string
	 */
	public function getFaultCode(){
		return $this->_faultCode;
	}

}
