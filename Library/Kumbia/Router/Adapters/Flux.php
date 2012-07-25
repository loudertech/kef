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
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 */

/**
 * FluxRouter
 *
 * Adapter that modifies the routing according to the FLUX-RPC request
 *
 * @category 	Kumbia
 * @package 	Router
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 */
class FluxRouter implements RouterInterface {

	/**
	 * Modify the routing parameters according to the FLUX-RPC request
	 *
	 * @access public
	 */
	public function handleRouting(){
		$request = ControllerRequest::getInstance();
		$fluxRawRequest = $request->getRawBody();
		$parameters = unserialize($fluxRawRequest);
		Router::setParameters($parameters);
	}

	/**
	 * Returns the administrator of the response of the default request
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