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
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: WebServiceController.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * MutableController
 *
 * Este tipo de controlador genera salidas en diferentes formatos de acuerdo al tipo
 * de solicitud que se le haga. SOAP, REST, JSON, XML, etc.
 *
 * @category	Kumbia
 * @package		Controller
 * @subpackage	Mutable
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 */
class MutableController extends ApplicationController {

	private $_isSoapRequested = false;

	/**
	 * El inicializador del controlador, lo hace persistente si se accede por SOAP
	 *
	 */
	public function initialize(){
		$this->_isSoapRequested = $this->getRequestInstance()->isSoapRequested();
		if($this->_isSoapRequested){
			$this->setPersistance(true);
		}
	}

	/**
	 * Establece el Soap::serverHandler como manejador de la presentación si la petición es SOAP
	 *
	 */
	public final function getViewHandler(){
		if($this->_isSoapRequested==true){
			return array('Soap', 'serverHandler');
		} else {
			return array('View', 'handleViewRender');
		}
	}

	/**
	 * Establece el Soap::faultSoapHandler como manejador de las excepciones, si la petición es SOAP
	 *
	 */
	public final function getViewExceptionHandler(){
		if($this->_isSoapRequested==true){
			return array('Soap', 'faultSoapHandler');
		} else {
			return array('View', 'handleViewExceptions');
		}
	}

}
