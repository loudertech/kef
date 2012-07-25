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
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 */

/**
 * DefaultRouter
 *
 * Adaptador por defecto para peticiones normales
 *
 * @category 	Kumbia
 * @package 	Router
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 */
class DefaultRouter
#if[compile-time]
	implements RouterInterface
#endif
	{

	/**
	 * Administra la petición de enrutamiento
	 *
	 * @access public
	 */
	public function handleRouting(){

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