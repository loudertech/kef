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
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: Resolver.php 88 2009-09-19 19:10:13Z gutierrezandresfelipe $
 */

/**
 * Resolver
 *
 * Este componente permite resolver los servicios web en el contenedor
 * de servicios ó mediante un naming directory service
 *
 * @category 	Kumbia
 * @package 	Resolver
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license  	New BSD License
 * @abstract
 */
abstract class Resolver {

	/**
	 * Servicios resueltos
	 *
	 * @var array
	 */
	static private $_resolvedServices = array();

	/**
	 * Localiza la ubicación de un servicio web
	 *
	 * @access 	public
	 * @param 	mixed $serviceNDI
	 * @return 	WebServiceClient
	 * @static
	 */
	public static function lookUp($serviceNDI){
		if(is_array($serviceNDI)){
			if(isset($serviceNDI['contextId'])){
				$serviceName = $serviceNDI['contextId'];
			} else {
				$serviceName = array();
				if(isset($serviceNDI['app'])){
					$serviceName[] = $serviceNDI['app'];
				}
				if(isset($serviceNDI['uri'])){
					$serviceName[] = $serviceNDI['uri'];
				}
				$serviceName = join('.', $serviceName);
			}
		} else {
			$serviceName = $serviceNDI;
			$serviceNDI = array();
			if(strpos($serviceName, '.')===false){
				$serviceNDI['uri'] = $serviceName;
			} else {
				$serviceItems = explode('.', $serviceName);
				$serviceNDI['app'] = $serviceItems[0];
				$serviceNDI['uri'] = $serviceItems[1];
			}
		}
		if(!isset(self::$_resolvedServices[$serviceName])){
			$instancePath = Core::getInstancePath();
			if(is_array($serviceNDI)){
				if(!isset($serviceNDI['app'])){
					$serviceApp = Router::getApplication();
				} else {
					$serviceApp = $serviceNDI['app'];
				}
				if(!isset($serviceNDI['uri'])){
					$serviceURI = Router::getApplication();
				} else {
					$serviceURI = $serviceNDI['uri'];
				}
			} else {
				$serviceApp = Router::getApplication();
				$serviceURI = str_replace('.', '/', $serviceName);
			}
			$controllerRequest = ControllerRequest::getInstance();
			$serviceURL = 'http://'.$controllerRequest->getParamServer('SERVER_NAME').$instancePath.$serviceApp.'/'.$serviceURI;
			$params = array(
				'actor' => 'http://app-services/'.$serviceName,
				'location' => $serviceURL,
				'compression' => 0,
				'communicator' => 'Pecl_HTTP'
			);
			if(PHP_OS=='WINNT'){
				$params['communicator'] = 'Sockets';
			}
			self::$_resolvedServices[$serviceName] = new WebServiceClient($params);
		}
		return self::$_resolvedServices[$serviceName];
	}

}