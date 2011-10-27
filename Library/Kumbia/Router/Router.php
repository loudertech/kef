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
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright 	Copyright (c) 2007-2008 Emilio Rafael Silveira Tovar (emilio.rst at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Router.php 118 2010-02-06 21:57:47Z gutierrezandresfelipe $
 */

/**
 * Router
 *
 * Clase que actua como router del Front-Controller
 *
 * @category 	Kumbia
 * @package 	Router
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright 	Copyright (c) 2007-2008 Emilio Rafael Silveira Tovar (emilio.rst at gmail.com)
 * @license  	New BSD License
 * @abstract
 */
abstract class Router {

	/**
	 * Indica si el router esta inicializado
	 *
	 * @var boolean
	 */
	static private $_initialized = false;

	/**
	 * Nombre de la aplicacion actual
	 *
	 * @var string
	 */
	static private $_application;

	/**
	 * Nombre del modulo actual
	 *
	 * @var string
	 */
	static private $_module;

	/**
	 * Nombre del controlador actual
	 *
	 * @var string
	 */
	static private $_controller;

	/**
	 * Nombre de la accion actual
	 *
	 * @var string
	 */
	static private $_action;

	/**
	 * Nombre del primer parametro despues de action
	 *
	 * @var string
	 */
	static private $_id;

	/**
	 * URL de la ultima reescritura
	 *
	 * @var string
	 */
	static private $_url;

	/**
	 * Lista de Todos los parametros de la URL
	 *
	 * @var array
	 */
	static private $_allParameters;

	/**
	 * Lista de Parametros Adicionales de la URL
	 *
	 * @var array
	 */
	static private $_parameters;

	/**
	 * Indica si esta pendiente la ejecucion de una ruta por
	 * parte del dispatcher
	 *
	 * @var boolean
	 */
	static private $_routed;

	/**
	 * Indica si la invocación de una acción fue producida por un enrutamiento
	 *
	 * @var boolean
	 */
	static private $_wasRouted = false;

	/**
	 * Detector de enrutamiento ciclico
	 */
	static private $_routedCyclic;

	/**
	 * Nombre de la acción por defecto
	 *
	 * @var string
	 */
	static private $_defaultActionName = 'index';

	/**
	 * Nombre del Adaptador de Enrutamiento
	 *
	 * @var string
	 */
	static private $_routingAdapterType = 'Default';

	/**
	 * Objeto de enrutamiento utilizado
	 *
	 * @var object
	 */
	static private $_routerAdapter;

	/**
	 * Indica si hay rutas estaticas
	 *
	 * @var boolean
	 */
	static private $_staticRoutes = true;

	/**
	 * Indica si las rutas estáticas están habilitadas
	 *
	 * @var boolean
	 */
	static private $_enableStaticRoutes = false;

	/**
	 * Tipo de Enrutamiento por defecto (Web/HTML)
	 *
	 */
	const ROUTING_DEFAULT = 0;

	/**
	 * Tipo de Enrutamiento distino a Web/HTML
	 *
	 */
	const ROUTING_OTHER = 1;

	/**
	 * Inicializa el componente Router
	 *
	 * @access public
	 * @static
	 */
	static public function initialize(){
		self::$_initialized = true;
	}

	/**
	 * Toma $url y la descompone en controlador, accion y argumentos
	 *
	 * @access	public
	 * @static
	 * @param	string $url
	 */
	static public function rewrite($url){

		self::$_url = $url;
		$urlItems = explode('/', $url);


		// El router puede detectar si el controlador corresponde a una aplicación
		// o a un controlador
		if($urlItems[0]!=""&&Core::applicationExists($urlItems[0])==true){

			$config = CoreConfig::readAppConfig($urlItems[0]);
			if(isset($config->application)){

				//Nombre de la aplicacion actual
				self::$_application = $urlItems[0];

				//Hay algún controlador?
				if(isset($urlItems[1])&&$urlItems[1]){
					self::$_controller = $urlItems[1];
				}

				//Hay alguna acción
				if(isset($urlItems[2])&&$urlItems[2]){
					self::$_action = $urlItems[2];
				}

		 		//Hay algún id
				if(isset($urlItems[3])&&$urlItems[3]){
					self::$_id = $urlItems[3];
				}
			}

		} else {

			$appServerConfig = CoreConfig::getInstanceConfig();
			$activeApp = $appServerConfig->core->defaultApp;

			self::$_application = $activeApp;

			#if[compile-time]
			if(!isset($appServerConfig->core)){
				throw new RouterException('No existe la sección [core] en el archivo de configuración de la instancia');
			}
			if(!isset($appServerConfig->core->defaultApp)){
				throw new RouterException('No se ha indicado la aplicación por defecto en config/config.ini (defaultApp)');
			}
			#endif

			$config = CoreConfig::readAppConfig($activeApp);
			#if[compile-time]
			if(!isset($config->application)){
				throw new RouterException('No existe la sección [application] en el config.ini de la aplicación');
			}
			#endif

			//Hay alguna controlador
			if(isset($urlItems[0])&&$urlItems[0]){
				self::$_controller = $urlItems[0];
			}

			//Hay alguna acción
			if(isset($urlItems[1])&&$urlItems[1]){
				self::$_action = $urlItems[1];
			}

			//Hay algún id
			if(isset($urlItems[2])&&$urlItems[2]){
				self::$_id = $urlItems[2];
			}

		}

		if(isset($config->application->controllersDir)){
			$controllersDir = $config->application->controllersDir;
		} else {
			$controllersDir = self::$_application.'/controllers';
		}

		if(self::$_controller!=null){
			self::$_controller = str_replace(array('/', '\\', '.'), '', self::$_controller);
			if(Core::isDir('apps/'.$controllersDir.'/'.self::$_controller)){
				if(self::$_application=='default'){

					self::$_module = $urlItems[0];

			 	 	// Hay algún controlador?
					if(isset($urlItems[1])&&$urlItems[1]){
						self::$_controller = $urlItems[1];
					}

			 	 	// Hay alguna acción?
					if(isset($urlItems[2])&&$urlItems[2]){
						self::$_action = $urlItems[2];
					} else {
						self::$_action = self::$_defaultActionName;
					}

					/**
			 	 	 * Hay algun id?
			 	 	 */
					if(isset($urlItems[3])&&$urlItems[3]){
						self::$_id = $urlItems[3];
					} else {
						self::$_id = null;
					}

					//En parameters quedan los valores de parametros por URL
					unset($urlItems[0], $urlItems[1], $urlItems[2]);

				} else {

					if(isset($urlItems[1])){
						self::$_module = $urlItems[1];
					}

					/**
			 	 	 * Hay algún controlador
			 	 	 */
					if(isset($urlItems[2])&&$urlItems[2]){
						self::$_controller = $urlItems[2];
					}

					/**
			 	 	 * Hay alguna accion
			 		 */
					if(isset($urlItems[3])&&$urlItems[3]){
						self::$_action = $urlItems[3];
					} else {
						self::$_action = self::$_defaultActionName;
					}

					/**
			 	 	 * Hay algun id?
			 	 	 */
					if(isset($urlItems[4])&&$urlItems[4]){
						self::$_id = $urlItems[4];
					} else {
						self::$_id = null;
					}

					//En parameters quedan los valores de parametros por URL
					unset($urlItems[0], $urlItems[1], $urlItems[2], $urlItems[3]);

				}

			} else {

				//En parameters quedan los valores de parametros por URL
				if(self::$_application=='default'){
					unset($urlItems[0], $urlItems[1]);
				} else {
					unset($urlItems[0], $urlItems[1], $urlItems[2]);
				}
			}

		}

		self::$_allParameters = $urlItems;
		self::$_parameters = array_values($urlItems);
		if(self::$_action==null){
			self::$_action = self::$_defaultActionName;
		}

	}

	/**
 	 * Busca en la tabla de entutamiento si hay una ruta en config/routes.ini
 	 * para el controlador, accion, id actual
     *
     * @access public
	 * @static
     */
	public static function ifRouted(){
		if(self::$_enableStaticRoutes==true){
			#if[compile-time]
			if(self::$_staticRoutes==true){
				if(!isset($_SESSION['KSR'])){
					$routes = CoreConfig::readRoutesConfig();
					if(isset($routes->routes)){
						foreach($routes->routes as $source => $destination){
							if(count(explode('/', $source))!=3||count(explode('/', $destination))!=3){
								throw new RouterException("Política de enrutamiento invalida '$source' a '$destination' en config/routes.ini");
							} else {
								list($controllerSource,
								$actionSource,
								$id_source) = explode('/', $source);
								list($controller_destination,
								$action_destination,
								$id_destination) = explode('/', $destination);
								if(($controllerSource==$controller_destination)&&
								($actionSource==$action_destination)&&
								($id_source==$id_destination)){
									throw new RouterException("Política de enrutamiento ciclica de '$source' a '$destination' en config/routes.ini");
								} else {
									$_SESSION['KSR'][$controllerSource][$actionSource][$id_source] = array(
										'controller' => $controller_destination,
										'action' => $action_destination,
										'id' => $id_destination
									);
								}
							}
						}
					} else {
						self::$_staticRoutes = false;
					}
				}
			}
			#endif

			$controller = self::$_controller;
			$action = self::$_action;
			$id = self::$_id;

			#if[compile-time]
			$newRoute = array('controller' => '*', 'action' => '*', 'id' => '*');
			if(isset($_SESSION['KSR']['*'][$action]['*'])){
				$newRoute = $_SESSION['KSR']['*'][$action]['*'];
			}
			if(isset($_SESSION['KSR'][$controller]['*']['*'])){
				$newRoute = $_SESSION['KSR'][$controller]['*']['*'];
			}
			if(isset($_SESSION['KSR'][$controller]['*'][$id])){
				$newRoute = $_SESSION['KSR'][$controller]['*'][$id];
			}
			if(isset($_SESSION['KSR'][$controller][$action]['*'])){
				$newRoute = $_SESSION['KSR'][$controller][$action]['*'];
			}
			if(isset($_SESSION['KSR'][$controller][$action][$id])){
				$newRoute = $_SESSION['KSR'][$controller][$action][$id];
			}
			if($newRoute['controller']!='*'){
				self::$_controller = $newRoute['controller'];
			}
			if($newRoute['action']!='*'){
				self::$_action = $newRoute['action'];
			}
			if($newRoute['id']!='*'){
				self::$_id = $newRoute['id'];
			}
			#endif
			return;
		}
	}

	/**
	 * Devuelve el estado del router
	 *
	 * @access public
	 * @static
	 * @return boolean
	 */
	public static function getRouted(){
		return self::$_routed;
	}

	/**
	 * Devuelve el nombre de la aplicación actual
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function getApplication(){
		return self::$_application;
	}

	/**
	 * Devuelve el nombre del modulo actual
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function getModule(){
		return self::$_module;
	}

	/**
	 * Devuelve el nombre del controlador actual
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function getController(){
		return self::$_controller;
	}

	/**
	 * Devuelve el nombre del controlador actual
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function getAction(){
		return self::$_action;
	}

	/**
	 * Establece la accion del enrutador
	 *
	 * @param string $action
	 */
	public static function setAction($action){
		self::$_action = $action;
	}

	/**
	 * Devuelve el primer parametro (id)
	 *
	 * @access public
	 * @return mixed
	 * @static
	 */
	public static function getId(){
		return self::$_id;
	}

	/**
	 * Establece el ID de la ruta (primer parámetro adicional)
	 *
	 * @access public
	 * @param string $id
	 * @static
	 */
	public static function setId($id){
		self::$_id = $id;
	}

	/**
	 * Devuelve los parametros de la ruta
	 *
	 * @access public
	 * @return array
	 * @static
	 */
	public static function getParameters(){
		return self::$_parameters;
	}

	/**
	 * Establece los parametros adicionales de la ruta
	 *
	 * @param array $parameters
	 */
	public static function setParameters(array $parameters){
		self::$_parameters = $parameters;
	}

	/**
	 * Devuelve todos los parametros de la ruta
	 *
	 * @access public
	 * @static
	 * @return array
	 */
	public static function getAllParameters(){
		return self::$_allParameters;
	}

	/**
	 * Establece los parametros de enrutamiento enviados por URL
	 *
	 * @param array $allParameters
	 */
	public static function setAllParameters(array $allParameters){
		self::$_allParameters = $allParameters;
	}

	/**
	 * Establece el estado del Router
	 *
	 * @access public
	 * @param boolean $value
	 * @static
	 */
	public static function setRouted($value){
		self::$_routed = $value;
	}

	/**
	 * Indica si el router ya está inicializado ó no
	 *
	 * @access public
	 * @static
	 */
	static public function isInitialized(){
		return self::$_initialized;
	}

	/**
	 * Valida si el router se encuentra en une estado de ejecución valido
	 *
	 * @access private
	 * @static
	 */
	static private function validateRouter(){
		if(self::$_initialized==false){
			throw new RouterException("Se esta tratando de hacer un enrutamiento en un estado de ejecución invalido");
		}
	}

	/**
	 * Realiza el enrutamiento usando una Uniform Resource Identifier
	 *
	 * @access public
	 * @static
	 * @param string $uri
	 */
	static public function routeToURI($uri){
		self::validateRouter();
		self::$_routed = false;
		self::$_wasRouted = true;
		$items = explode('/', $uri);
		self::$_allParameters = array();
		if(isset($items[1])){
			self::$_controller = $items[1];
			self::$_allParameters[1] = $items[1];
			self::$_routed = true;
		}
		if(isset($items[2])){
			self::$_action = $items[2];
			self::$_allParameters[2] = $items[2];
			self::$_routed = true;
		}
		if(isset($items[3])){
			self::$_id = $items[3];
			self::$_allParameters[3] = $items[3];
			self::$_parameters[0] = $items[3];
			self::$_routed = true;
		}
		if(self::$_routed==true){
			$numberItems = count($items);
			for($i=4;$i<$numberItems;++$i){
				self::$_allParameters[] = $items[$i];
				self::$_parameters[] = $items[$i];
			}
		}
	}

	/**
	 * Enruta el controlador actual a otro controlador, ó a otra acción
	 * Ej:
	 * <code>
	 * Router::routeTo("controller: nombre", ["action: accion"], ["id: id"])
	 * </code>
	 *
	 * @access public
	 * @static
	 * @return null
	 */
	static public function routeTo($route){
		if(is_array($route)==false){
			$numberArguments = func_num_args();
			$route = Utils::getParams(func_get_args(), $numberArguments);
		}
		self::validateRouter();
		self::$_routed = false;
		self::$_wasRouted = true;
		$cyclicRouting = false;
		self::$_allParameters = array();
		if(isset($route['controller'])){
			if(self::$_controller==$route['controller']){
				$cyclicRouting = true;
			}
			self::$_controller = $route['controller'];
			self::$_allParameters[0] = $route['controller'];
			self::$_action = self::$_defaultActionName;
			self::$_routed = true;
		}
		if(isset($route['action'])){
			if(self::$_action==$route['action']){
				$cyclicRouting = true;
			}
			self::$_action = $route['action'];
			self::$_allParameters[1] = $route['action'];
			self::$_routed = true;
		}
		if(isset($route['id'])){
			if(self::$_id==$route['id']){
				$cyclicRouting = true;
			}
			self::$_id = $route['id'];
			self::$_allParameters[2] = $route['id'];
			self::$_parameters[0] = $route['id'];
			self::$_routed = true;
		}
		if(isset($route['0'])){
			$numberParam = 0;
			while(isset($route[$numberParam])){
				self::$_allParameters[$numberParam+2] = $route[$numberParam];
				self::$_parameters[$numberParam] = $route[$numberParam];
				$numberParam++;
			}
			self::$_routed = true;
		}
		if($cyclicRouting){
			self::$_routedCyclic++;
			if(self::$_routedCyclic>=1000){
				throw new RouterException('Se ha detectado un enrutamiento ciclico. Esto puede causar problemas de estabilidad', 1000);
			}
		} else {
			self::$_routedCyclic = 0;
		}
		return null;
	}

	/**
	 * Nombre de la aplicación activa actual devuelve "" en caso de
	 * que la aplicacion sea default
	 *
	 * @access public
	 * @static
	 * @return string
	 */
	static public function getActiveApplication(){
		return self::$_application != 'default' ? self::$_application : '';
	}

	/**
	 * Permite establecer el nombre de la aplicacion activa sobre escribiendo la actual
	 *
	 * @access public
	 * @param string $application
	 * @static
	 */
	static public function setActiveApplication($application){
		self::$_application = $application;
	}

	/**
	 * Permite establecer el nombre de la accion por defecto en todos los controladores
	 *
	 * @access public
	 * @static
	 * @param string $actionName
	 */
	static public function setDefaultActionName($actionName){
		self::$_defaultActionName = $actionName;
	}

	/**
	 * Devuelve el nombre de la accion por defecto en todos los controladores
	 *
	 * @access public
	 * @static
	 * @return string
	 */
	static public function getDefaultActionName(){
		return self::$_defaultActionName;
	}

	/**
	 * Devuelve la ultima URL enrutada por el Router
	 *
	 * @access public
	 * @static
	 */
	static public function getURL(){
		return self::$_url;
	}

	/**
	 * Obtiene el URL dentro de la aplicación actual
	 *
	 * @access public
	 * @static
	 */
	static public function getApplicationURL(){
		if(self::$_application!=''){
			return substr(self::$_url, strlen(self::$_application)+1);
		} else {
			return self::$_url;
		}
	}

	/**
	 * Redirecciona el flujo de ejecucion a otra aplicacion
	 * en la misma instancia del framework
	 *
	 * @access 	public
	 * @param 	string $uri
	 * @static
	 */
	static public function redirectToApplication($uri){
		$instancePath = Core::getInstancePath();
		$response = ControllerResponse::getInstance();
		$response->setHeader('Location: '.$instancePath.'/'.$uri, true);
	}

	/**
	 * Indica si la invocación de una acción fue producida por un enrutamiento
	 *
	 * @access 	public
	 * @static
	 */
	static public function wasRouted(){
		return self::$_wasRouted;
	}

	/**
	 * Detecta como se deben tratar los parámetros del enrutador
	 *
	 * @access public
	 * @static
	 */
	static public function handleRouterParameters(){
		if(!isset($_GET['_url'])){
			$_GET['_url'] = '';
		}
		Router::rewrite($_GET['_url']);
		if(self::_detectRoutingType()==self::ROUTING_OTHER){
			if(class_exists(self::$_routingAdapterType.'Router', false)==false){
				if(Core::fileExists('Library/Kumbia/Router/Adapters/'.self::$_routingAdapterType.'.php')==false){
					throw new RouterException("No existe el adaptador de enrutamiento ".self::$_routingAdapterType);
				}
			}
		}
		/**
		 * @see RouterInterface
		 */
		if(!interface_exists('RouterInterface', false)){
			require 'Library/Kumbia/Router/Interface.php';
		}
		$className = self::$_routingAdapterType.'Router';
		if(class_exists($className, false)==false){
			require 'Library/Kumbia/Router/Adapters/'.self::$_routingAdapterType.'.php';
		}
		self::$_routerAdapter = new $className();
		self::$_routerAdapter->handleRouting();
		unset($className);
	}

	/**
	 * Detecta el tipo de enrutamiento solicitado por el cliente
	 *
	 * @return int
	 */
	static private function _detectRoutingType(){
		if(isset($_SERVER['HTTP_JSONACTION'])){
			self::$_routingAdapterType = 'Json';
			return self::ROUTING_OTHER;
		} else {
			if(isset($_SERVER['HTTP_SOAPACTION'])){
				self::$_routingAdapterType = 'Soap';
				return self::ROUTING_OTHER;
			} else {
				if(isset($_SERVER['CONTENT_TYPE'])){
		        	if(strpos($_SERVER['CONTENT_TYPE'], 'application/soap+xml')!==false){
		        		self::$_routingAdapterType = 'Soap';
						return self::ROUTING_OTHER;
		        	}
				}
			}
		}
		return self::ROUTING_DEFAULT;
	}

	/**
	 * Devuelve el adaptador de enrutamiento utilizado
	 *
	 * @access 	public
	 * @return 	object
	 * @static
	 */
	static public function getRoutingAdapter(){
		return self::$_routerAdapter;
	}

	/**
	 * Reinicializa el Router para atender una segunda petición
	 *
	 * @access 	public
	 * @static
	 */
	static public function cleanRouter(){
		self::$_application = null;
		self::$_module = null;
		self::$_controller = null;
		self::$_action = null;
		self::$_id = null;
		self::$_url = null;
		self::$_allParameters = null;
		self::$_parameters = null;
		self::$_routed = null;
		self::$_wasRouted = null;
		self::$_routedCyclic = null;
	}

}
