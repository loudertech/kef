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
 * @package		Dispatcher
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Dispatcher.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * Dispatcher
 *
 * Clase para que administra las peticiones del Servidor de Aplicaciones
 *
 * @category	Kumbia
 * @package		Dispatcher
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 * @abstract
 */
abstract class Dispatcher {

	/**
	 * Contiene referencias a los controladores instanciados
	 *
	 * @var array
	 * @staticvar
	 */
	static private $_controllerReferences = array();

	/**
	 * Estadisticas de ejecución de controladores
	 *
	 * @var array
	 */
	static private $_controllerStatistic = array();

	/**
	 * Indica si ya se han inicializado los componentes
	 *
	 * @var boolean
	 * @staticvar
	 */
	static private $_initializedComponents = false;

	/**
	 * Indica el estado de ejecucion de la aplicacion
	 *
	 * @var integer
	 * @staticvar
	 */
	static private $_requestStatus = self::STATUS_UNINITIALIZED;

	/**
	 * Valor devuelto por el metodo accion ejecutado
	 *
	 * @var string
	 * @staticvar
	 */
	static private $_valueReturned = null;

	/**
	 * Objeto del controlador en ejecucion
	 *
	 * @var mixed
	 * @staticvar
	 */
	static private $_controller;

	/**
	 * Directorio de controladores
	 *
	 * @var string
	 * @staticvar
	 */
	static private $_controllersDir;

	/**
	 * Lista de clases que no deben ser serializadas por el Dispatcher
	 *
	 * @var array
	 */
	static private $_notSerializableClasses = array('ActiveRecord', 'ActiveRecordResulset');

	/**
	 * Codigo de error cuando no encuentra la accion
	 */
	const NOT_FOUND_ACTION = 100;
	const NOT_FOUND_CONTROLLER = 101;
	const NOT_FOUND_FILE_CONTROLLER = 102;
	const NOT_FOUND_INIT_ACTION = 103;

	/**
	 * Otros codigos de excepciones
	 */
	const INVALID_METHOD_CALLBACK = 104;
	const INVALID_ARGUMENT_NUMBER = 105;

	/**
	 * Estados de Ejecucion de la Peticion
	 */
	const STATUS_UNINITIALIZED = 199;
	const STATUS_DISPATCHING = 200;
	const STATUS_RUNNING_BEFORE_FILTERS = 201;
	const STATUS_RUNNING_AFTER_FILTERS = 202;
	const STATUS_RENDER_PRESENTATION = 203;
	const STATUS_RUNNING_BEFORE_STORE_PERSISTENCE = 204;
	const STATUS_RUNNING_AFTER_STORE_PERSISTENCE = 205;
	const STATUS_RUNNING_CONTROLLER_ACTION = 206;

	/**
	 * Ejecuta la accion init en ApplicationController
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	static public function initBase(){

		/**
		 * Inicializa los componentes del Framework
		 */
		#if[dispatcher-status]
		self::$_requestStatus = self::STATUS_RUNNING_CONTROLLER_ACTION;
		#endif
		self::initComponents();

		$applicationController = new ApplicationController();
		if(method_exists($applicationController, 'init')){
			$applicationController->init();
		} else {
			if(self::executeNotFound($applicationController)==false){
				//No se encontró el método init en la clase ControllerBase
				$message = CoreLocale::getErrorMessage(-103);
				self::throwException($message, self::NOT_FOUND_INIT_ACTION);
			} else {
				self::$_controller = $applicationController;
			}
		}

	}

	/**
	 * Ejecuta la acción notFound si está definida
	 *
	 * @access 	private
	 * @param 	Controller $applicationController
	 * @static
	 */
	static private function executeNotFound($applicationController=''){
		if($applicationController==''){
			$applicationController = new ApplicationController();
		}
		#if[no-controller-plugins]
		PluginManager::notifyFromController('beforeNotFoundAction', $applicationController);
		#endif
		if(method_exists($applicationController, 'notFoundAction')){
			$notFoundStatus = call_user_func_array(
				array($applicationController, 'notFoundAction'),
				Router::getAllParameters()
			);
			if($notFoundStatus===false){
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Establece el directorio de los controladores
	 *
	 * @access	public
	 * @param	string $directory
	 * @static
	 */
	static public function setControllerDir($directory){
		self::$_controllersDir = $directory;
	}

	/**
	 * Establece el controlador interno de Dispatcher
	 *
	 * @access public
	 * @param Object $controller
	 * @static
	 */
	static public function setController($controller){
		self::$_controller = $controller;
	}

	/**
	 * Ejecuta el filtro before presente en el controlador
	 *
	 * @access	public
	 * @param	mixed $appController
	 * @param	string $controller
	 * @param	string $action
	 * @param	array $params
	 * @static
	 */
	static private function _runBeforeFilters($appController, $controller, $action, $params){
   	    // El método beforeFilter es llamado antes de ejecutar una acción en un
		// controlador, puede servir para realizar ciertas validaciones
		#if[dispatcher-status]
		self::$_requestStatus = self::STATUS_RUNNING_BEFORE_FILTERS;
		#endif
		if(method_exists($appController, 'beforeFilter')){
			if(call_user_func_array(array(self::$_controller, 'beforeFilter'), $params)===false){
				return false;
			}
		} else {
			if(isset(self::$_controller->beforeFilter)){
				if(call_user_func_array(array(self::$_controller, self::$_controller->beforeFilter), $params)===false){
					return false;
				}
			}
		}
	}

	/**
	 * Corre los filtros after en el controlador actual
	 *
	 * @param	string $appController
	 * @param	string $controller
	 * @param	string $action
	 * @param	array $params
	 * @static
	 */
	static private function _runAfterFilters($appController, $controller, $action, $params){
		// El método afterFilter es llamado despues de ejecutar una accion en un
		// controlador, puede servir para realizar ciertas validaciones
		#if[dispatcher-status]
		self::$_requestStatus = self::STATUS_RUNNING_BEFORE_FILTERS;
		#endif
		if(method_exists($appController, 'afterFilter')){
			call_user_func_array(array(self::$_controller, 'afterFilter'), $params);
		} else {
			if(isset(self::$_controller->afterFilter)){
				call_user_func_array(array(self::$_controller, self::$_controller->afterFilter), $params);
			}
		}
	}

	/**
	 * Incluye los componentes para ejecutar la petición
	 *
	 * @access public
	 * @static
	 */
	static public function initComponents(){
		if(self::$_initializedComponents==false){
			self::$_initializedComponents = true;
		} else {
			return;
		}
	}

	/**
	 * Agrega una clase que no debe ser serializada
	 *
	 * @access 	public
	 * @param 	string $className
	 * @static
	 */
	static public function addNotSerializableClass($className){
		self::$_notSerializableClasses[] = $className;
	}

	/**
	 * Realiza el dispatch de una ruta
	 *
	 * @access 	public
	 * @param	string $module
	 * @param 	string $controller
	 * @param 	string $action
	 * @param 	array $parameters
	 * @param 	array $allParameters
	 * @return 	boolean
	 * @static
	 */
	static public function executeRoute($module, $controller, $action, $parameters, $allParameters){

		// Aplicacion activa
		$activeApp = Router::getApplication();

		if($module!=''){
			$controllersDir = self::$_controllersDir.'/'.$module;
		} else {
			$controllersDir = self::$_controllersDir;
		}
		$notFoundExecuted = false;
		$appController = $controller.'Controller';
		if(class_exists($appController, false)==false){
			if(Core::fileExists($controllersDir.'/'.$controller.'_controller.php')){
				require KEF_ABS_PATH.$controllersDir.'/'.$controller.'_controller.php';
			} else {
				$applicationController = new ApplicationController();
				if(self::executeNotFound($applicationController)==false){
					//No se encontró el controlador
					$message = CoreLocale::getErrorMessage(-102, $controller);
					self::throwException($message, self::NOT_FOUND_FILE_CONTROLLER);
				} else {
					self::$_controller = $applicationController;
					$notFoundExecuted = true;
				}
			}
		}

		// Incializa el nombre de la instancia
		Core::setInstanceName();

		if(class_exists($controller.'Controller', false)){

			//Inicializa los componentes del Framework
			self::initComponents();

			// Dispatcher mantiene referencias los controladores instanciados
			$instanceName = Core::getInstanceName();
			if(!isset(self::$_controllerReferences[$appController])){
				if(!isset($_SESSION['KCON'][$instanceName][$activeApp][$module][$appController])){
					self::$_controller = new $appController();
				} else {
					// Obtiene el objeto persistente
					$persistedData = $_SESSION['KCON'][$instanceName][$activeApp][$module][$appController];
					if($persistedData['status']=='C'){
						$persistedData['data'] = gzuncompress($persistedData['data']);
					}
					self::$_controller = unserialize($persistedData['data']);
				}
				self::$_controllerReferences[$appController] = self::$_controller;
				// Envia a la persistencia por si se genera una excepción no controlada
				if(self::$_controller->getPersistance()==true){
					$_SESSION['KCON'][$instanceName][$activeApp][$module][$appController] = array(
						'data' => serialize(self::$_controller),
						'time' => Core::getProximityTime(),
						'status' => 'N'
					);
				}
			} else {
				self::$_controller = self::$_controllerReferences[$appController];
			}

			self::$_controller->setResponse('');
			self::$_controller->setControllerName($controller);
			self::$_controller->setActionName($action);

			if(isset($parameters[0])){
				self::$_controller->setId($parameters[0]);
			} else {
				self::$_controller->setId('');
			}
			self::$_controller->setAllParameters($allParameters);
			self::$_controller->setParameters($parameters);

			try {

			 	// Se ejecutan los filtros before
				if(self::_runBeforeFilters($appController, $controller, $action, $parameters)===false){
					return self::$_controller;
				}

			    //Se ejecuta el metodo con el nombre de la accion en la clase mas el sufijo Action
				$actionMethod = $action.'Action';
				#if[dispatcher-status]
				self::$_requestStatus = self::STATUS_DISPATCHING;
				#endif
				if(method_exists(self::$_controller, $actionMethod)==false){
					if(method_exists(self::$_controller, 'notFoundAction')){
						$notFoundReturned = call_user_func_array(array(self::$_controller, 'notFoundAction'), Router::getAllParameters());
						if($notFoundReturned===false){
							$message = CoreLocale::getErrorMessage(-100, $action, $controller, $action);
							self::throwException($message, Dispatcher::NOT_FOUND_ACTION);
						}
						return self::$_controller;
					} else {
						//No se encontró la acción
						$message = CoreLocale::getErrorMessage(-100, $action, $controller, $action);
						self::throwException($message, Dispatcher::NOT_FOUND_ACTION);
					}
				}

				#if[dispatcher-status]
				self::$_requestStatus = self::STATUS_RUNNING_CONTROLLER_ACTION;
				#endif

				#if[compile-time]
				$enviroment = CoreConfig::getAppSetting('mode');
				if($enviroment!='production'){
					$method = new ReflectionMethod($appController, $actionMethod);
					if($method->isPublic()==false){
						$message = CoreLocale::getErrorMessage(-104, $action);
						self::throwException($message, self::INVALID_METHOD_CALLBACK);
					}
					$methodParameters = $method->getParameters();
					$paramNumber = 0;
					foreach($methodParameters as $methodParameter){
						if($methodParameter->isOptional()==false&&!isset($parameters[$paramNumber])){
							//Numero inválido de argumentos
							$message = CoreLocale::getErrorMessage(-105, $methodParameter->getName(), $action);
							self::throwException($message, self::INVALID_ARGUMENT_NUMBER);
						}
						++$paramNumber;
					}
				}
				#endif

				self::$_valueReturned = call_user_func_array(array(self::$_controller, $actionMethod), $parameters);

			 	//Corre los filtros after
				self::_runAfterFilters($appController, $controller, $action, $parameters);

				#if[dispatcher-status]
				self::$_requestStatus = self::STATUS_RENDER_PRESENTATION;
				#endif

			}
			catch(Exception $e){

				$cancelThrowException = false;

				// Notifica la excepción a los Plugins
				#if[no-application-plugins]
				$cancelThrowException = PluginManager::notifyFromApplication('onControllerException', $e);
				#endif

				if(method_exists(self::$_controller, 'onException')){
					self::$_controller->onException($e);
				} else {
					if($cancelThrowException==false){
						if(is_subclass_of($e, 'CoreException')){
							$fileTraced = false;
							foreach($e->getTrace() as $trace){
								if(isset($trace['file'])){
									if($trace['file']==$e->getFile()){
										$fileTraced = true;
									}
								}
							}
							if($fileTraced==false){
								$exceptionFile = array(array(
									'file' => $e->getFile(),
									'line' => $e->getLine()
								));
								$e->setExtendedBacktrace(array_merge($exceptionFile, $e->getTrace()));
							} else {
								$e->setExtendedBacktrace($e->getTrace());
							}
						}
						throw $e;
					}
				}
			}

			// Se clona el controlador y se serializan las propiedades que no sean instancias de modelos
			if(self::$_controller->getPersistance()==true){
				$controller = clone self::$_controller;
				try {
					#if[dispatcher-status]
					self::$_requestStatus = self::STATUS_RUNNING_BEFORE_STORE_PERSISTENCE;
					#endif
					if(method_exists($controller, 'beforeStorePersistence')){
						$controller->beforeStorePersistence();
					}
					foreach($controller as $property => $value){
						if(is_object($value)){
							foreach(self::$_notSerializableClasses as $className){
								if(is_subclass_of($value, $className)){
									unset($controller->{$property});
								}
								unset($className);
							}
						}
						unset($property);
						unset($value);
					}
					if(isset($_SESSION['KCON'][$instanceName][$activeApp][$module][$appController])){
						$_SESSION['KCON'][$instanceName][$activeApp][$module][$appController] = array(
							'data' => serialize($controller),
							'time' => Core::getProximityTime(),
							'status' => 'N'
						);
					}
					#if[dispatcher-status]
					self::$_requestStatus = self::STATUS_RUNNING_AFTER_STORE_PERSISTENCE;
					#endif
				}
				catch(PDOException $e){
					throw new CoreException($e->getMessage(), $e->getCode());
				}
			}
			unset($module);
			unset($controller);
			unset($action);
			unset($parameters);
			unset($allParameters);
			unset($instanceName);
			unset($appController);
			unset($activeApp);
			return self::$_controller;
		} else {
			if($notFoundExecuted==false){
				//No se encontró el controlador
				$message = CoreLocale::getErrorMessage(-101, $appController);
				self::throwException($message, self::NOT_FOUND_CONTROLLER);
			} else {
				return $applicationController;
			}
		}
	}

	/**
	 * Devuelve una instancia del controlador base ControllerBase
	 *
	 * @return ApplicationController
	 */
	static public function getControllerBase(){
		if(class_exists('ControllerBase', false)){
			return new ControllerBase();
		} else {
			return false;
		}
	}

	/**
 	 * Obtener el controlador en ejecución ó el último ejecutado
	 *
	 * @access	public
	 * @return	Controller
	 * @static
	 */
	public static function getController(){
		return self::$_controller;
	}

	/**
	 * Devuelve el valor devuelto por el método ejecutado en la ultima acción
	 *
	 * @access 	public
	 * @return	mixed
	 * @static
	 */
	public static function getValueReturned(){
		return self::$_valueReturned;
	}

	/**
	 * Devuelve el estado de ejecucion de la peticion
	 *
	 * @access public
	 * @static
	 */
	public static function getDispatchStatus(){
		return self::$_requestStatus;
	}

	/**
	 * Indica si el estado de ejecucion es la logica de Controlador
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function isRunningController(){
		return self::$_requestStatus == Dispatcher::STATUS_RUNNING_CONTROLLER_ACTION;
	}

	/**
	 * Indica si el estado de ejecucion de la aplicacion esta a nivel de usuario
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function isRunningUserLevel(){
		return self::$_requestStatus != self::STATUS_DISPATCHING;
	}

	/**
	 * Lanza una excepción de tipo DispatcherException
	 *
	 * @access public
	 * @throws DispatcherException
	 * @static
	 */
	public static function throwException($message, $code){
		throw new DispatcherException($message, $code);
	}

}
