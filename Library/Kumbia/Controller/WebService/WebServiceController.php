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
 * WebServiceController
 *
 * Este tipo de controlador Permite crear Servicios web basados en el
 * estándar SOAP, generar descripciones en WSDL y orquestar el intercambio
 * de datos entre aplicaciones usando este método.
 *
 * @category	Kumbia
 * @package		Controller
 * @subpackage	WebService
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class WebServiceController extends Controller {

	/**
	 * Permite indicar el archivo WSDL que contiene
	 * la descripcion del servicio
	 *
	 * @var string
	 */
	private $_soapWSDL = null;

	/**
	 * Opciones del constructor SOAP
	 *
	 * @var array
	 */
	private $_soapOptions = array();

	/**
	 * Inicializa las opciones del constructor SoapServer
	 *
	 */
	public final function indexAction(){
		$this->_soapOptions = array(
			'uri' => 'http://app-services',
			'actor' => 'http://'.$_SERVER['SERVER_ADDR'].'/'.Core::getInstanceName().'/'.$this->getControllerName(),
			'soap_version' => SOAP_1_2,
			'encoding' => 'UTF-8'
		);
	}

	/**
	 * Devuelve las opciones SOAP definidas
	 *
	 * @return array
	 */
	public function getSoapOptions(){
		return $this->_soapOptions;
	}

	/**
	 * El inicializador del Servicio
	 *
	 */
	public function initialize(){
		$this->setPersistance(true);
	}

	/**
	 * Establece el Soap::serverHandler como manejador de la presentación
	 *
	 */
	public final function getViewHandler(){
		return array('Soap', 'serverHandler');
	}

	/**
	 * Establece el Soap::faultSoapHandler como manejador de las excepciones
	 *
	 */
	public final function getViewExceptionHandler(){
		return array('Soap', 'faultSoapHandler');
	}

}
