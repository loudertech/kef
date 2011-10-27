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
 * @package		Controller
 * @subpackage	WebService
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: WebServiceControllerException.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * WebServiceControllerException
 *
 * Excepcion generada por un servicio Web
 *
 * @category	Kumbia
 * @package		Controller
 * @subpackage	WebService
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class WebServiceException extends SoapFault {

	/**
	 * Constructor de la excepcion
	 *
	 * @param string $errorMessage
	 * @param string $errorCode
	 */
	public function __construct($faultMessage, $faultCode="0"){
		parent::__construct($faultCode, $faultMessage);
	}

}
