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
 * @package		Core
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2007-2007 Emilio Rafael Silveira Tovar (emilio.rst@gmail.com)
 * @license		New BSD License
 * @version 	$Id: Core.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * Core
 *
 * Esta es la clase que integra todo el framework
 *
 * @category	Kumbia
 * @package		Core
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @abstract
 */
abstract class Core {

	/**
	 * Version del Framework
	 *
	 */
	const FRAMEWORK_VERSION = '1.7.4';

	/**
	 * Permite configurar el framework para pruebas remotas
	 *
	 */
	const TESTING_REMOTE = 1;

	/**
	 * Permite configurar el framework para pruebas locales
	 *
	 */
	const TESTING_LOCAL = 2;

	/**
	 * PATH donde esta instalada la instancia del framework
	 */
	private static $_instanceName = null;

	/**
	 * Almacena el ID del Cluster si aplica
	 *
	 * @var string
	 */
	private static $_clusterId = null;

	/**
	 * Directorio de controladores activo
	 *
	 * @var string
	 */
	private static $_activeControllersDir;

	/**
	 * Directorio de modelos activo
	 *
	 * @var string
	 */
	private static $_activeModelsDir;

	/**
	 * Directorio de vistas activo
	 *
	 * @var string
	 */
	private static $_activeViewsDir;

	/**
	 * Establece si el framework se encuentra en modo Test
	 *
	 * @var boolean
	 */
	private static $_testingMode = false;

	/**
	 * Indica si la aplicación está corriendo bajo IBM Websphere
	 *
	 * @var boolean
	 */
	private static $_isWebSphere = false;

	/**
	 * Indica si la aplicación está corriendo bajo HurricaneServer
	 *
	 * @var boolean
	 */
	private static $_isHurricane = false;

	/**
	 * Framework Path Inicial
	 *
	 * @var string
	 */
	private static $_frameworkPath = "";

	/**
	 * Inicializa el entorno de aplicación
	 *
	 * @access public
	 * @static
	 */
	public static function initApplication(){

		/**
		 * @see Extensions
		 */
		require KEF_ABS_PATH.'Library/Kumbia/Extensions/Extensions.php';

		//Carga las extensiones del boot.ini
		Extensions::loadBooteable();

		//Carga los plug-in de la aplicación actual
		PluginManager::loadApplicationPlugins();

		//Establece el timezone del sistema
		self::setTimeZone();

	}

	/**
	 * Establece el PATH inicial de la aplicación
	 *
	 * @param string $path
	 * @static
	 */
	static public function setInitialPath($path){
		self::$_frameworkPath = $path.'/';
	}

	/**
	 * Devuelve el path de la aplicación
	 *
	 * @return string
	 * @static
	 */
	static public function getInitialPath(){
		return self::$_frameworkPath;
	}

	/**
	 * Reestablece el PATH original de la aplicación
	 *
	 * @access	public
	 * @static
	 */
	static public function restoreInitialPath(){
		if(self::$_frameworkPath!=''){
			if(self::$_frameworkPath!=KEF_ABS_PATH){
				chdir(self::$_frameworkPath);
			}
		}
	}

	/**
	 * Establecer el timezone para las fechas y horas
	 *
	 * @access	public
	 * @param	string $timezone
	 * @static
	 */
	static public function setTimeZone($timezone=''){
		if($timezone==''){
			$config = CoreConfig::getInstanceConfig();
			if(isset($config->core->timezone)){
				$timezone = $config->core->timezone;
			} else {
				$timezone = 'America/Bogota';
			}
		}
		#if[compile-time]
		if(date_default_timezone_set($timezone)==false){
			throw new CoreException('Timezone inválido \''.$timezone.'\'');
		}
		#endif
		unset($timezone);
	}

	/**
	 * Obtiene el timezone actual
	 *
	 * @access	public
	 * @return	string
	 * @static
	 */
	static public function getTimezone(){
		return date_default_timezone_get();
	}

	/**
	 * Inicializar el _INSTANCE_NAME
	 *
	 * @access	public
	 * @param 	string $instanceName
	 * @return	boolean
	 * @static
	 */
	static public function setInstanceName($instanceName=null){

		if(self::$_instanceName!==null){
			return false;
		}

		//Crear el _INSTANCE_NAME
		$exceptionThrown = false;
		if($instanceName===null){
			$path = substr(str_replace(array('/public/index.php', '/index.php'), '', $_SERVER['PHP_SELF']), 1);
			if(!isset($_SESSION['_INSTANCE_NAME'])){
				#if[compile-time]
				Facility::setFacility(Facility::FRAMEWORK_CORE);
				if(version_compare(PHP_VERSION, '5.2.0', '<')){
					$message = CoreLocale::getErrorMessage(-10, PHP_VERSION);
					throw new CoreException($message, -10);
				}
				if(!is_writable('public/temp')){
					Facility::setFacility(Facility::FRAMEWORK_CORE);
					$message = CoreLocale::getErrorMessage(-11);
					throw new CoreException($message, -11);
				}
				#endif
				$_SESSION['_INSTANCE_NAME'] = '';
			}
		} else {
			$_SESSION['_INSTANCE_NAME'] = $instanceName;
			$path = $instanceName;
		}

		//Ejecutar onStartApplication y onChangeInstance
		$e = null;
		try {
			if($path!=$_SESSION['_INSTANCE_NAME']){
				self::runStartApplicationEvent();
				self::runChangeInstanceEvent();
			} else {
				if(!isset($_SESSION['_APPNAME'])||$_SESSION['_APPNAME']!=Router::getApplication()){
					self::runStartApplicationEvent();
					$_SESSION['_APPNAME'] = Router::getApplication();
				}
			}
		}
		catch(Exception $e){
			// Espera a que se defina _INSTANCE_NAME y lanza la excepcion
			$exceptionThrown = true;
		}
		$_SESSION['_APPNAME'] = Router::getApplication();
		$_SESSION['_INSTANCE_NAME'] = $path;
		if($_SESSION['_INSTANCE_NAME']){
			self::$_instanceName = $_SESSION['_INSTANCE_NAME'];
		} else {
			self::$_instanceName = '';
		}
		if($exceptionThrown==true){
			throw $e;
		}
		return true;
	}

	/**
	 * Devuelve el nombre de la instancia actual
	 *
	 * @return string
	 */
	public static function getInstanceName(){
		if(self::$_instanceName===null){
			return join(array_slice(explode('/' ,dirname($_SERVER['PHP_SELF'])),1,-1),'/');
		} else {
			return self::$_instanceName;
		}
	}

	/**
	 * Ejecuta el evento de inicializar la aplicación
	 *
	 * @access 	public
	 * @static
	 */
	public static function runStartApplicationEvent(){
		#if[no-application-plugins]
		PluginManager::notifyFromApplication('beforeStartApplication');
		#endif
		if(class_exists('ControllerBase', false)){
			$controllerBase = new ControllerBase();
			if(method_exists($controllerBase, 'onStartApplication')){
				$controllerBase->onStartApplication();
			}
		}
		#if[no-application-plugins]
		PluginManager::notifyFromApplication('afterStartApplication');
		#endif
	}

	/**
	 * Ejecuta el evento de cambiar de Instancia del Framework
	 *
	 * @access public
	 * @static
	 */
	public static function runChangeInstanceEvent(){
		#if[no-application-plugins]
		PluginManager::notifyFromApplication('beforeChangeInstance');
		#endif
		if(class_exists('ControllerBase', false)){
			$controllerBase = new ControllerBase();
			if(method_exists($controllerBase, 'onChangeInstance')){
				$controllerBase->onChangeApplication(self::getInstanceName());
			}
		}
		#if[no-application-plugins]
		PluginManager::notifyFromApplication('afterChangeInstance');
		#endif
	}

	/**
	 * Devuelve el PATH donde esta instalada la instancia despues del DOCUMENT ROOT
	 *
	 * @access 	public
	 * @return 	string
	 * @static
	 */
	public static function getInstancePath(){
		$instance = self::getInstanceName();
		if($instance){
			return '/'.self::getInstanceName().'/';
		} else {
			return '/';
		}
	}

	/**
	 * Inicializa las rutas MVC para hacerlas disponibles a todos los componentes
	 *
	 * @access 	private
	 * @param 	Config $config
	 * @static
	 */
	private static function _initializeMVCRoutes($config){

		//Aplicacion Activa
		$activeApp = Router::getApplication();

		//Directorio de controladores Activo
		if(isset($config->application->controllersDir)){
			self::$_activeControllersDir = 'apps/'.$config->application->controllersDir;
		} else {
			self::$_activeControllersDir = 'apps/'.$activeApp.'/controllers';
		}

		//Directorio de modelos activo
		if(isset($config->application->modelsDir)){
			self::$_activeModelsDir = 'apps/'.$config->application->modelsDir;
		} else {
			self::$_activeModelsDir = 'apps/'.$activeApp.'/models';
		}

		//Directorio de Vistas Activo
		if(isset($config->application->viewsDir)){
			self::$_activeViewsDir = 'apps/'.$config->application->viewsDir;
		} else {
			self::$_activeViewsDir = 'apps/'.$activeApp.'/views';
		}

		//Incluir Controller Base
		self::includeControllerBase();

	}

	/**
	 * Incluir el ControllerBase de la aplicación activa
	 *
	 * @access public
	 * @static
	 */
	public static function includeControllerBase(){
		if(class_exists('ControllerBase', false)==false){
			require KEF_ABS_PATH.self::$_activeControllersDir.'/application.php';
		}
	}

	/**
	 * Invoca el GarbageCollector de Sesion
	 *
	 * @access 	public
	 * @param 	Config $config
	 * @static
	 */
	private static function _executeGarbageCollector($config){
		if(isset($config->collector)){
			if(class_exists('GarbageCollector', false)==false){
				require KEF_ABS_PATH.'Library/Kumbia/GarbageCollector/GarbageCollector.php';
			}
			if(isset($config->collector->probability)){
				GarbageCollector::setProbability($config->collector->probability);
			}
			if(isset($config->collector->collectTime)){
				GarbageCollector::setCollectTime($config->collector->collectTime);
			}
			if(isset($config->collector->compressTime)){
				GarbageCollector::setCompressTime($config->collector->compressTime);
			}
			GarbageCollector::startCollect();
		}
	}

	/**
	 * Inicializa componentes comunes
	 *
	 * @access private
	 * @static
	 */
	private static function _initializeCommonComponents(){
		$commonComponents = array(
			'CommonEvent' => 'CommonEvent/CommonEvent',
			'Dispatcher' => 'Dispatcher/Dispatcher',
			'EntityManager' => 'EntityManager/EntityManager',
			'TransactionManager' => 'Transactions/TransactionManager',
			'DbLoader' => 'Db/Loader/DbLoader',
			'DbBase' => 'Db/DbBase',
			'ActiveRecordBase' => 'ActiveRecord/Base/ActiveRecordBase',
			'Security' => 'Security/Security',
			'Facility' => 'Facility/Facility',
			'View' => 'View/View',
			'i18n' => 'i18n/i18n',
			'ControllerResponse' => 'Controller/ControllerResponse',
			'Utils' => 'Utils/Utils'
		);
		foreach($commonComponents as $className => $filePath){
			if(class_exists($className, false)==false){
				require KEF_ABS_PATH.'Library/Kumbia/'.$filePath.'.php';
			}
		}
	}

	/**
	 * Función principal donde se inicia el flujo de la aplicación
	 *
	 * @access 	public
	 * @return 	boolean
	 * @throws 	CoreException
	 * @static
	 */
	public static function fastMain(){

		self::$_frameworkPath = KEF_ABS_PATH.'/';

		//Leer configuracion de la aplicación
		$config = CoreConfig::readAppConfig();

		//Inicializa las rutas MVC
		self::_initializeMVCRoutes($config);

		try {

			//Inicializa la respuesta
			$controller = null;

			//Iniciar el buffer de salida
			ob_start();

 	 	    //El driver de la BD es cargado según lo que diga en config.ini
			if(DbLoader::loadDriver()==false){
				return false;
			}

			// Inicializa el modelo base
			EntityManager::initModelBase(self::$_activeModelsDir);
			EntityManager::setAutoInitialize(false);
			EntityManager::setModelsDirectory(self::$_activeModelsDir);

			//Inicializa el administrador de transacciones
			TransactionManager::initializeManager();

			$controller = self::handleRequest();

		}
		catch(CoreException $e){
			return self::_handleException($e, $controller);
		}
		catch(Exception $e){
			//Las excepciones se convierten en CoreException sin perder la traza
			try {
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
					$backtrace = array_merge($exceptionFile, $e->getTrace());
				} else {
					$backtrace = $e->getTrace();
				}
				throw new CoreException($e->getMessage().' ('.get_class($e).')', $e->getCode(), true, $backtrace);
			}
			catch(CoreException $e){
				return self::_handleException($e, $controller);
			}
		}
		return true;

	}

	/**
	 * Función Principal donde se inicia el flujo de ejecucion
	 *
	 * @access 	public
	 * @return 	boolean
	 * @throws 	CoreException
	 * @static
	 */
	public static function main(){

		//Establece el Path Inicial
		if(defined('KEF_ABS_PATH')){
			if(KEF_ABS_PATH){
				self::setInitialPath(KEF_ABS_PATH);
			} else {
				self::setInitialPath(getcwd());
			}
		} else {
			self::setInitialPath(getcwd());
			define('KEF_ABS_PATH', getcwd());
		}

		//Inicializa componentes comunes
		self::_initializeCommonComponents();

		//Leer configuracion de la aplicación
		$config = CoreConfig::readAppConfig();

		//Inicializa las rutas MVC
		self::_initializeMVCRoutes($config);

		try {

			//Inicializa la respuesta
			$controller = null;

			//Iniciar el buffer de salida
			if(self::$_testingMode<self::TESTING_LOCAL){
				ob_start();
			}

 	 	    //El driver de la BD es cargado según lo que diga en config.ini
			if(DbLoader::loadDriver()==false){
				return false;
			}

			// Inicializa el modelo base
			EntityManager::initModelBase(self::$_activeModelsDir);
			if(isset($config->entities->autoInitialize)&&$config->entities->autoInitialize==false){
				EntityManager::setAutoInitialize(false);
				EntityManager::setModelsDirectory(self::$_activeModelsDir);
			} else {
				//Los demas modelos estan en el directorio de modelos
				EntityManager::initModels(self::$_activeModelsDir);
			}

			//Inicializa el administrador de transacciones
			TransactionManager::initializeManager();

		 	//Inicializa el administrador de acceso
			Security::initAccessManager();

			//Atiende la petición
			if(self::$_testingMode<self::TESTING_LOCAL){
				$controller = self::handleRequest();
				//Ejecuta el GC
				self::_executeGarbageCollector($config);
			}

		}
		catch(CoreException $e){
			return self::_handleException($e, $controller);
		}
		catch(Exception $e){
			//Las excepciones se convierten en CoreException sin perder la traza
			try {
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
					$backtrace = array_merge($exceptionFile, $e->getTrace());
				} else {
					$backtrace = $e->getTrace();
				}
				throw new CoreException($e->getMessage().' ('.get_class($e).')', $e->getCode(), true, $backtrace);
			}
			catch(CoreException $e){
				return self::_handleException($e, $controller);
			}
		}
		return true;
	}

	/**
	 * Realiza el proceso de atender una petición
	 *
	 * @access public
	 * @static
	 */
	public static function handleRequest(){

		/**
		 * Inicializa los plug-ins
		 */
		PluginManager::initializePlugins();
		#if[no-application-plugins]
		PluginManager::notifyFromApplication('beforeStartRequest');
		#endif
		Facility::setFacility(Facility::USER_LEVEL);

		//Inicializar componente Router
		Router::initialize();
		Router::setRouted(true);
		Router::ifRouted();
		$controller = null;
		$controllerName = Router::getController();

		// Inicia la sesion de acuerdo al adaptador instalado
		Session::startSession();

		/**
		 * Ejectutar Plugin::beforeDispatchLoop()
		 */
		#if[no-controller-plugins]
		PluginManager::notifyFromController('beforeDispatchLoop', $controller);
		#endif

		//Establecer directorio de controladores
		Dispatcher::setControllerDir(self::$_activeControllersDir);

		//Ciclo del enrutador
		while(Router::getRouted()==true){
			Router::setRouted(false);

			//Ejectutar Plugin::beforeDispatch()
			#if[no-controller-plugins]
			PluginManager::notifyFromController('beforeDispatch', $controller);
			#endif

			//Si no hay controlador ejecuta ControllerBase::init()
			if($controllerName==null){
				Dispatcher::initBase();
			} else {

				//Valida que si se tenga acceso al recurso solicitado
				Security::checkResourceAccess($controller);

				//Ejectutar Plugin::beforeExecuteRoute()
				#if[no-controller-plugins]
				PluginManager::notifyFromController('beforeExecuteRoute', $controller);
				#endif

				$controller = Dispatcher::executeRoute(Router::getModule(), Router::getController(), Router::getAction(),
				Router::getParameters(), Router::getAllParameters());

				//Ejectutar Plugin::afterExecuteRoute()
				#if[no-controller-plugins]
				PluginManager::notifyFromController('afterExecuteRoute', $controller);
				#endif

			}
			Router::ifRouted();

			// Ejectutar Plugin::afterDispatch()
			#if[no-controller-plugins]
			PluginManager::notifyFromController('afterDispatch', $controller);
			#endif

			//Nombre del último controlador
			$controllerName = Router::getController();

		}

		//Ejectutar Plugin::afterDispatchLoop() y CommonEvent::notifyEvent()
		#if[no-common-event]
		CommonEvent::notifyEvent('afterDispatchLoop');
		#endif
		#if[no-controller-plugins]
		$controllerName = PluginManager::notifyFromController('afterDispatchLoop', $controller);
		#endif
		$controller = Dispatcher::getController();

		/**
		 * Cada tipo de Controlador puede tener un tipo diferente
		 * de administrador de presentacion
		 */
		if($controller!==null){
			$handler = $controller->getViewHandler();
			call_user_func_array($handler, array($controller));
		}
		#if[no-common-event]
		CommonEvent::notifyEvent('finishRequest');
		#endif
		#if[no-application-plugins]
		PluginManager::notifyFromApplication('beforeFinishRequest');
		#endif

		return $controller;
	}

	/**
	 * Administra el comportamiento del framework al generarse una excepcion
	 *
	 * @access 	public
	 * @param 	string $e
	 * @param 	Controller $controller
	 * @static
	 */
	private static function _handleException($e, $controller){

		//Notifica la excepcion a los Plugins
		#if[no-application-plugins]
		PluginManager::notifyFromApplication('beforeUncaughtException', $e);
		#endif

		$controller = Dispatcher::getController();
		Session::storeSessionData();
		if($controller){
			$exceptionHandler = $controller->getViewExceptionHandler();
		} else {
			$controllerBase = Dispatcher::getControllerBase();
			if($controllerBase){
				if(method_exists($controllerBase, 'getViewExceptionHandler')){
					$exceptionHandler = $controllerBase->getViewExceptionHandler();
				} else {
					$exceptionHandler = self::determineExceptionHandler();
				}
			} else {
				$exceptionHandler = self::determineExceptionHandler();
			}
		}
		call_user_func_array($exceptionHandler, array($e, $controller));
		return;
	}

	/**
	 * Determina el exception handler adecuado para dar respuesta
	 *
	 * @access public
	 * @return callback
	 * @static
	 */
	public static function determineExceptionHandler(){
		$routingAdapter = Router::getRoutingAdapter();
		return $routingAdapter->getExceptionResponseHandler();
	}

	/**
	 * Cambiar todo el entorno de aplicación a otra
	 *
	 * @param string $application
	 */
	public static function changeApplication($application){
		if(!file_exists('apps/'.$application)){
			throw new CoreException('No existe la aplicación "'.$application.'"');
		}
		Router::setActiveApplication($application);
		Core::main();
	}

	/**
	 * Agrega un evento que se ejecutará dinámicamente
	 *
	 * @param 	$eventName
	 * @param 	callback $callback
	 */
	public static function on($eventName, $callback){
		CommonEvent::observe($eventName, $callback);
	}

	/**
	 * Carga el framework javascript y funciones auxiliares. (Método obsoleto)
	 *
	 * @access 		public
	 * @static
	 * @deprecated
	 */
	public static function javascriptBase(){
		echo Tag::javascriptBase();
	}

	/**
	 * Imprime los CSS cargados mediante Tag::stylesheetLink. (Método obsoleto)
	 *
	 * @access 		public
	 * @static
	 * @deprecated
	 */
	public static function stylesheetLinkTags(){
		echo Tag::stylesheetLinkTags();
	}

	/**
	 * Proxy a Router::routeTo (Método Obsoleto)
	 *
	 * @access 		public
	 * @static
	 * @return 		null
	 * @deprecated
	 */
	public static function routeTo(){
		$args = func_get_args();
		return call_user_func_array(array('Router', 'routeTo'), $args);
	}

	/**
	 * Método que muestra información del Framework y la licencia (Método Obsoleto)
	 *
	 * @access 		public
	 * @static
	 * @deprecated
	 */
	public static function info(){
		CoreInfo::showInfoScreen();
	}

	/**
	 * Importa un archivo desde la ubicación actual
	 *
	 * @param string $dir
	 */
	public static function importFromActiveApp($dir){
		require_once KEF_ABS_PATH.'apps/'.Router::getApplication().'/'.$dir;
	}

	/**
	 * Indica si un archivo existe en la aplicación actual
	 *
	 * @param string $path
	 */
	public static function fileExistsOnActiveApp($path){
		return self::fileExists('apps/'.Router::getApplication().'/'.$path);
	}

	/**
	 * Importa un archivo de una libreria en Library/
	 *
	 * @access 	public
	 * @param 	string $libraryName
	 * @param 	string $dir
	 * @static
	 */
	public static function importFromLibrary($libraryName, $dir){
		require_once KEF_ABS_PATH.'Library/'.$libraryName.'/'.$dir;
	}

	/**
	 * Realiza un require en forma condicional
	 *
	 * @param string $file
	 * @static
	 */
	public static function requireFile($file){
		require KEF_ABS_PATH.'Library/Kumbia/'.$file.'.php';
	}

	/**
	 * Realiza un require en forma condicional
	 *
	 * @param 	string $className
	 * @static
	 */
	public static function requireLogicalFile($className){
		if(self::$_isWebSphere==true){
			foreach(func_get_args() as $className){
				if(class_exists($className, false)==false){
					require CoreClassPath::getClassPath($className);
				}
			}
		}
	}

	/**
	 * Devuelve el buffer de salida
	 *
	 * @access 		public
	 * @return		string
	 * @deprecated
	 * @static
	 */
	public static function getContent(){
		return View::getContent(true);
	}

	/**
	 * Permite lanzar excepciones de PHP o externas a Kumbia como propias
	 *
	 * @access	public
	 * @param 	Exception $exception
	 * @throws 	CoreException
	 * @static
	 */
	public static function manageExceptions($exception){
		throw new CoreException($exception->getMessage(), $exception->getCode());
	}

	/**
	 * Permite lanzar errores, warnings, notices con excepciones
	 *
	 * @access	public
	 * @param 	int $number
	 * @param 	string $message
	 * @param 	string $file
	 * @param 	int	$num
	 * @param 	array $enviroment
	 * @throws 	CoreException
	 * @static
	 */
	public static function manageErrors($number, $message, $file, $num, $enviroment){
		$errortype = array (
                E_ERROR              => 'Error',
                E_WARNING            => 'Warning',
                E_PARSE              => 'Parsing Error',
                E_NOTICE             => 'Notificación',
                E_CORE_ERROR         => 'Core Error',
                E_CORE_WARNING       => 'Core Warning',
                E_COMPILE_ERROR      => 'Compile Error',
                E_COMPILE_WARNING    => 'Compile Warning',
                E_USER_ERROR         => 'User Error',
                E_USER_WARNING       => 'User Warning',
                E_USER_NOTICE        => 'User Notice',
                E_STRICT             => 'Runtime Notice',
                E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
		);
		$errorReporting = ini_get('error_reporting');
		if(!($errorReporting & $number)){
        	return;
    	}
		self::restoreInitialPath();
		if(isset($errortype[$number])&&$errorReporting>0){
			if(!class_exists('Debug', false)){
				require KEF_ABS_PATH.'Library/Kumbia/Debug/Debug.php';
			}
			if(!class_exists('CoreException', false)){
				require KEF_ABS_PATH.'Library/Kumbia/Core/CoreException.php';
			}
			foreach($enviroment as $var => $value){
				Debug::addVariable($var, $value);
			}
			$exists = false;
			foreach(debug_backtrace() as $trace){
				if(isset($trace['file'])){
					if($trace['file']==$file&&$trace['line']==$num){
						$exists = true;
						break;
					}
				}
			}
			$message = $errortype[$number]." - ".$message;
			if($exists==false){
				$backtrace = array(array(
					'file' => $file,
					'line' => $num
				));
				throw new CoreException($message, -$number, true, $backtrace);
			} else {
				throw new CoreException($message, -$number);
			}
		} else {
			return false;
		}
	}

	/**
	 * Indica si una aplicación existe
	 *
	 * @access	public
	 * @param	string $application
	 * @return	boolean
	 * @static
	 */
	public static function applicationExists($application){
		return self::fileExists('apps/'.$application);
	}

	/**
	 * Devuelve el directorio de vistas de la aplicación activa
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function getActiveViewsDir(){
		return self::$_activeViewsDir;
	}

	/**
	 * Devuelve el directorio de modelos de la aplicación activa
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function getActiveModelsDir(){
		return self::$_activeModelsDir;
	}

	/**
	 * Devuelve el directorio de controladores de la aplicación activa
	 *
	 * @access public
	 * @return string
	 * @static
	 */
	public static function getActiveControllersDir(){
		return self::$_activeControllersDir;
	}

	/**
	 * Recarga los valores de los directorios de controladores, modelos y vistas
	 *
	 * @access public
	 * @static
	 */
	public static function reloadMVCLocations(){
		//Aplicacion Activa
		$config = CoreConfig::readAppConfig();
		self::_initializeMVCRoutes($config);
	}

	/**
	 * Establece si el framework esta en modo Test
	 *
	 * @param boolean $testingMode
	 */
	public static function setTestingMode($testingMode){
		self::$_testingMode = $testingMode;
		if($testingMode==self::TESTING_LOCAL){
			Session::disableAutoStart(true);
		}
	}

	/**
	 * Indica si el framework se encuentra en modo Test
	 *
	 * @return boolean
	 */
	public static function isTestingMode(){
		return self::$_testingMode;
	}

	/**
	 * Resetea la petición
	 *
	 * @access public
	 * @static
	 */
	public static function resetRequest(){
		Tag::resetStylesheetLinks();
		Router::cleanRouter();
		ControllerResponse::resetResponse();
		View::setContent('');
		View::setRenderLevel(View::LEVEL_MAIN_VIEW);
		View::cleanViewParams();
	}

	/**
	 * Obtener el valor de un Kumbia Naming and Directory Interface
	 *
	 * @access 	public
	 * @param 	string $kumbiaNDI
	 * @static
	 */
	public static function getKumbiaNDI($kumbiaNDI){
		$kumbiaNDI = str_replace('%localserver%', gethostbyname('localhost'), $kumbiaNDI);
		$kumbiaNDI = str_replace('%active-instance%', self::getInstanceName(), $kumbiaNDI);
		$kumbiaNDI = str_replace('%active-app%', Router::getApplication(), $kumbiaNDI);
		$kumbiaNDI = str_replace('%app-base%', 'apps/'.Router::getApplication(), $kumbiaNDI);
		return $kumbiaNDI;
	}

	/**
	 * Devuelve un timestamp aproximado de la peticion
	 *
	 * @return int
	 */
	public static function getProximityTime(){
		if(isset($_SERVER['REQUEST_TIME'])){
			return $_SERVER['REQUEST_TIME'];
		} else {
			return time();
		}
	}

	/**
	 * Indica si un archivo existe
	 *
	 * @param 	string $filePath
	 * @return 	boolean
	 */
	public static function fileExists($filePath){
		/*
		//Permite el debug usando Zend Platform
		if(isset($_GET['start_debug'])){
			return file_exists("/Applications/MAMP/htdocs/hfos/".$filePath);
		} else {
			return file_exists($filePath);
		}*/
		return file_exists(KEF_ABS_PATH.$filePath);
	}

	/**
	 * Obtiene el PATH a un archivo
	 *
	 * @param	string $path
	 * @return	string
	 */
	public static function getFilePath($path){
		/*
		//Permite el debug usando Zend Platform
		if(isset($_GET['start_debug'])){
			return "/Applications/MAMP/htdocs/hfos/".$path;
		} else {

		}*/
		return $path;
	}

	/**
	 * Indica si un directorio existe en el sistema de archivos
	 *
	 * @param	string $path
	 * @return	string
	 */
	public static function isDir($path){
		/*
		//Permite el debug usando Zend Platform
		if(isset($_GET['start_debug'])){
			return is_dir("/Applications/MAMP/htdocs/hfos/".$path);
		} else {
			return is_dir($path);
		}*/
		return is_dir($path);
	}

	/**
	 * Establece que la aplicación se esta ejecutando bajo IBM Websphere
	 *
	 * @param	boolean $webSphere
	 */
	public static function setIsWebsphere($webSphere){
		self::$_isWebSphere = $webSphere;
	}

	/**
	 * Indica si se esta usando la aplicación en IBM® Websphere
	 *
	 * @return	boolean
	 */
	public static function isWebsphere(){
		return self::$_isWebSphere;
	}

	/**
	 * Establece que la aplicación se está ejecutando bajo HurricaneServer
	 *
	 * @param	boolean $hurricane
	 */
	public static function setIsHurricane($hurricane){
		self::$_isHurricane = $hurricane;
	}

	/**
	 * Indica si la aplicación se está ejecutando bajo HurricaneServer
	 *
	 * @return	boolean
	 */
	public static function isHurricane(){
		return self::$_isHurricane;
	}

}
