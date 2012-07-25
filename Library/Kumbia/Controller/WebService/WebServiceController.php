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
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: WebServiceController.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * @see Transactionable
 */
require KEF_ABS_PATH.'Library/Kumbia/Controller/WebService/Transactionable.php';

/**
 * WebServiceController
 *
 * Este tipo de controlador permite servir servicios web usando diferentes protocolos y formatos.
 * La petición es atendida usando el adaptador y componente correcto
 *
 * @category	Kumbia
 * @package		Controller
 * @subpackage	WebService
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 */
class WebServiceController extends ApplicationController {

	private $_routingType;

	/**
	 * El inicializador del controlador, lo hace persistente si se accede por SOAP
	 *
	 */
	public function initialize(){
		if($this->_routingType==null){
			$this->_routingType = Router::getRoutingAdapterType();
		}
		if($this->_routingType!='default'){
			$this->setPersistance(true);
			switch($this->_routingType){
				case 'Flux':
					$this->setResponse('flux');
					break;
				case 'Json':
					$this->setResponse('json');
					break;
			}
		}
	}

	/**
	 * Establece el Soap::serverHandler como manejador de la presentación si la petición es SOAP
	 *
	 */
	public final function getViewHandler(){
		switch($this->_routingType){
			case 'Flux':
				break;
			case 'Json':
				break;
			case 'Soap':
				return array('Soap', 'serverHandler');
			default:
				throw new ControllerException("Invalid access to webservice using ".$this->_routingType." (".Router::getController()."/".Router::getAction().")", 0);
		}
		return array('View', 'handleViewRender');
	}

	/**
	 * Establece el Soap::faultSoapHandler como manejador de las excepciones, si la petición es SOAP
	 *
	 */
	public final function getViewExceptionHandler(){
		switch($this->_routingType){
			case 'Soap':
				return array('Soap', 'faultSoapHandler');
			case 'Json':
				break;
			case 'Flux':
				break;
			default:
				throw new ControllerException("Invalid access to webservice using ".$this->_routingType." (".Router::getController().")", 0);
		}
		return array('View', 'handleViewExceptions');
	}

}
