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
 * @package 	Router
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 */

/**
 * JsonRouter
 *
 * Adaptador que modifica el enrutamiento de acuerdo a la peticion JSON-RPC
 *
 * @category 	Kumbia
 * @package 	Router
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 */
class JsonRouter implements RouterInterface {

	/**
	 * Modifica los parametros de enrutamiento de acuerdo a la peticion JSON-RPC
	 *
	 * @access public
	 */
	public function handleRouting(){
		$request = ControllerRequest::getInstance();
		$jsonRawRequest = $request->getRawBody();
		$parameters = json_decode($jsonRawRequest, true);
		Router::setParameters($parameters);
	}

	/**
	 * Devuelve el administrador de la respuesta de la petición por defecto
	 *
	 * @access public
	 * @return callback
	 */
	public function getResponseHandler(){
		return array('View', 'handleViewRender');
	}

	/**
	 * Devuelve el administrador de petición por defecto
	 *
	 * @access public
	 * @return callback
	 */
	public function getExceptionResponseHandler(){
		return array('View', 'handleViewExceptions');
	}

}