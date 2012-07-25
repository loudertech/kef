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
 * @copyright	Copyright (c) 2008-2012 Louder Technology COL. (http://www.loudertechnology.com)
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
 * @copyright	Copyright (c) 2008-2012 Louder Technology COL. (http://www.loudertechnology.com)
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
	 * Indica si hay una definición en el naming.ini para el servicio
	 *
	 * @param string $serviceDNI
	 */
	public static function hasDefinition($serviceNDI){
		$namings = CoreConfig::readFromActiveApplication('naming.ini');
		return isset($namings->$serviceNDI);
	}

	/**
	 * Localiza la ubicación de un servicio web
	 *
	 * @access 	public
	 * @param 	mixed $serviceNDI
	 * @return 	SoapServiceClient
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
			$namings = CoreConfig::readFromActiveApplication('naming.ini');
			if(isset($namings->$serviceNDI)){
				$serviceNDI = (array) $namings->$serviceNDI;
			} else {
				$serviceNDI = array();
				if(strpos($serviceName, '.')===false){
					$serviceNDI['uri'] = $serviceName;
				} else {
					$serviceItems = explode('.', $serviceName);
					$serviceNDI['app'] = $serviceItems[0];
					$serviceNDI['uri'] = $serviceItems[1];
				}
			}
		}

		if(!isset(self::$_resolvedServices[$serviceName])){

			if(is_array($serviceNDI)){
				if(!isset($serviceNDI['uri'])){
					$serviceURI = Router::getApplication();
				} else {
					$serviceURI = $serviceNDI['uri'];
				}
			} else {
				$serviceURI = Router::getApplication().'/'.str_replace('.', '/', $serviceName);
			}

			$serviceUrl = 'http://';
			if(isset($serviceNDI['host'])){
				$serviceUrl.=$serviceNDI['host'];
			} else {
				$controllerRequest = ControllerRequest::getInstance();
				$serviceUrl.=$controllerRequest->getParamServer('SERVER_NAME');
			}
			if(isset($serviceNDI['instancePath'])){
				$instancePath = $serviceNDI['instancePath'];
			} else {
				$instancePath = Core::getInstancePath();
			}
			$serviceUrl.=$instancePath.$serviceURI;

			$options = array(
				'actor' => 'http://app-services/'.$serviceName,
				'location' => $serviceUrl
			);

			if(!isset($serviceNDI['transport'])){
				if(PHP_OS=='WINNT'){
					$transport = 'Sockets';
				} else {
					$transport = 'Pecl_HTTP';
				}
			} else {
				$transport = $serviceNDI['transport'];
			}

			if(!isset($serviceNDI['protocol'])){
				$protocol = 'Flux';
			} else {
				$protocol = $serviceNDI['protocol'];
			}

			self::$_resolvedServices[$serviceName] = new ServiceConsumer($protocol, $transport, $options);
		}
		return self::$_resolvedServices[$serviceName];
	}

}