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
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Controller.php,v f5add30bf4ba 2011/10/26 21:05:13 andres $
 */

/**
 * Controller
 *
 * Componente base para controladores
 *
 * @category	Kumbia
 * @package		Controller
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class Controller extends ControllerBase {

	/**
	 * Nombre del controlador activo
	 *
	 * @var array
	 */
	private static $_controllerName = array();

	/**
	 * Nombre de la accion actual
	 *
	 * @var array
	 */
	private static $_actionName = array();

	/**
	 * Nombre del primer parámetro después de action
	 * en la URL
	 *
	 * @var array
	 */
	private static $_id = array();

	/**
	 * Parámetros enviados por una clean URL
	 *
	 * @var array
	 */
	private static $_parameters = array();


	/**
	 * Todos los parámetros enviados por una clean URL
	 *
	 * @var array
	 */
	private static $_allParameters = array();

	/**
	 * Número de minutos que será cacheada la vista actual
	 *
	 * @var integer
	 */
	private static $_cacheView = array();

	/**
	 * Número de minutos que será cacheada el layout actual
	 *
	 * @var integer
	 */
	private static $_cacheLayout = array();

	/**
	 * Numero de minutos que será cacheado el template actual
	 *
	 * @var integer
	 */
	private static $_cacheTemplate = array();

	/**
	 * Template del controlador que se insertan antes del layout del controlador
	 *
	 * @var string
	 */
	private static $_templateBefore = array();

	/**
	 * Template del controlador que se insertan despues del layout del controlador
	 *
	 * @var string
	 */
	private static $_templateAfter = array();

	/**
	 * Indica si el controlador soporta persistencia
	 *
	 * @var boolean
	 */
	private static $_persistance = false;

	/**
	 * Tipo de respuesta que será generada
	 *
	 * @access private
	 * @var string
	 */
	private static $_response = '';

	/**
	 * Indica si el controlador es persistente o no
	 *
	 * @access public
	 * @staticvar
	 * @var boolean
	 */
	static public $force = false;

	/**
	 * Logger implicito del controlador
	 *
	 * @access	private
	 * @var		string
	 */
	private static $_logger;

	/**
	 * Permite asignar attributos sin generar una excepción
	 *
	 * @var boolean
	 */
	private static $_settingLock = array();

	/**
	 * Administrador de presentación por defecto
	 *
	 * @var array
	 */
	private static $_defaultOutputHandler = array('View', 'handleViewRender');

	/**
	 * Administrador de excepciones por defecto
	 *
	 * @var array
	 */
	private static $_defaultExceptionHandler = array('View', 'handleViewExceptions');

	/**
	 * Constructor de la clase
	 *
	 * @access public
	 */
	public function __construct(){
		if(method_exists($this, 'initialize')){
			$this->initialize();
		}
	}

	/**
	 * Cache la vista correspondiente a la accion durante $minutes
	 *
	 * @access protected
	 * @param int $minutes
	 */
	protected function cacheView($minutes){
		self::$_cacheView[get_class($this)] = $minutes;
	}

	/**
	 * Obtiene el valor en minutos para el cache de la
	 * vista actual
	 *
	 * @access public
	 * @return string
	 */
	public function getViewCache(){
		if(isset(self::$_cacheView[get_class($this)])){
			return self::$_cacheView[get_class($this)];
		} else {
			return 0;
		}
	}

	/**
	 * Cache la vista en views/layouts/
	 * correspondiente al controlador durante $minutes
	 *
	 * @access protected
	 * @param integer $minutes
	 */
	protected function cacheLayout($minutes){
		self::$_cacheLayout[get_class($this)] = $minutes;
	}

	/**
	 * Obtiene el valor en minutos para el cache del
	 * layout actual
	 *
	 * @access public
	 * @return int
	 */
	public function getLayoutCache(){
		if(isset(self::$_cacheLayout[get_class($this)])){
			return self::$_cacheLayout[get_class($this)];
		} else {
			return 0;
		}
	}

	/**
	 * Hace el enrutamiento desde un controlador a otro, o desde
	 * una accion a otra.
	 *
	 * Ej:
	 * <code>
	 * return $this->routeTo("controller: clientes", "action: consultar", "id: 1");
	 * </code>
	 *
	 * @access protected
	 */
	protected function routeTo(){
		$args = func_get_args();
		return call_user_func_array(array('Router', 'routeTo'), $args);
	}

	/**
	 * Hace un enrutamiento a otro controlador sin mantener los valores del enrutador
	 *
	 * @param string $controller
	 */
	protected function routeToController($controller){
		return Router::routeTo(array(
			'controller' => $controller,
			'action' => null,
			'id' => null
		));
	}

	/**
	 * Hace un enrutamiento a una acción del controlador actual sin mantener los valores del enrutador
	 *
	 * @param string $action
	 */
	protected function routeToAction($action){
		return Router::routeTo(array(
			'action' => $action,
			'id' => null
		));
	}

	/**
	 * Hace el enrutamiento desde un controlador a otro, o desde
	 * una accion a otra.
	 *
	 * @access protected
	 */
	protected function routeToURI(){
		$args = func_get_args();
		return call_user_func_array(array('Router', 'routeToURI'), $args);
	}

	/**
	 * Obtiene un valor del arreglo $_POST
	 *
	 * @access	protected
	 * @param	string $paramName
	 * @return	mixed
	 */
	protected function getPost($paramName){
		$funcGetArgs = func_get_args();
		return call_user_func_array(array($this->getRequestInstance(), 'getParamPost'), $funcGetArgs);
	}

	/**
	 * Obtiene un valor del arreglo $_POST
	 *
	 * @access	protected
	 * @param	string $paramName
	 * @return	mixed
	 */
	protected function getPostParam($paramName){
		$funcGetArgs = func_get_args();
		return call_user_func_array(array($this->getRequestInstance(), 'getParamPost'), $funcGetArgs);
	}

	/**
	 * Obtiene un valor del arreglo $_GET
	 *
	 * @access	protected
	 * @param	string $paramName
	 * @return	mixed
	 */
	protected function getQueryParam($paramName){
		$funcGetArgs = func_get_args();
		return call_user_func_array(array($this->getRequestInstance(), 'getParamQuery'), $funcGetArgs);
	}

	/**
	 * Obtiene un valor del arreglo $_GET
	 *
	 * @access	protected
	 * @param	string $paramName
	 * @return	mixed
	 */
	protected function getQuery($paramName){
		$funcGetArgs = func_get_args();
		return call_user_func_array(array($this->getRequestInstance(), 'getParamQuery'), $funcGetArgs);
	}

	/**
	 * Obtiene un valor del arreglo $_REQUEST
 	 *
 	 * @access	protected
	 * @param	string $paramName
	 * @return	mixed
	 */
	protected function getRequestParam($paramName){
		$funcGetArgs = func_get_args();
		return call_user_func_array(array($this->getRequestInstance(), 'getParamRequest'), $funcGetArgs);
	}

	/**
	 * Obtiene un valor del arreglo superglobal $_SERVER
 	 *
 	 * @access	protected
	 * @param	string $paramName
	 * @return	mixed
	 */
	protected function getServer($paramName){
		$funcGetArgs = func_get_args();
		return call_user_func_array(array($this->getRequestInstance(), 'getParamServer'), $funcGetArgs);
	}

	/**
	 * Obtiene un valor del arreglo superglobal $_ENV
 	 *
 	 * @access	protected
	 * @param	string $paramName
	 * @return	mixed
	 */
	protected function getEnvironment($paramName){
		$funcGetArgs = func_get_args();
		return call_user_func_array(array($this->getRequestInstance(), 'getParamEnv'), $funcGetArgs);
	}

	/**
	 * Obtiene un archivo enviado en la petición
	 *
	 * @access	protected
	 * @param	string $fileName
	 * @return	ControllerUploadFile
	 */
	protected function getFileParam($fileName){
		return $this->getRequestInstance()->getParamFile($fileName);
	}

	/**
	 * Filtra un valor
 	 *
 	 * @access	protected
	 * @param	string $paramValue
	 * @return	mixed
	 */
	protected function filter($paramValue){
		//Si hay más de un argumento, toma los demas como filtros
		if(func_num_args()>1){
			$params = func_get_args();
			unset($params[0]);
			return Filter::bring($paramValue, $params);
		} else {
			throw new ApplicationControllerException('Debe indicar al menos un filtro a aplicar');
		}
		return $paramValue;
	}

	/**
	 * Establece el valor de un parámetro enviado por $_REQUEST;
	 *
	 * @access	protected
	 * @param	mixed $index
	 * @param	mixed $value
	 */
	protected function setRequestParam($index, $value){
		$funcGetArgs = func_get_args();
		return call_user_func_array(array($this->getRequestInstance(), 'setParamRequest'), $funcGetArgs);
	}

	/**
	 * Establece el valor de un parámetro enviado por $_POST;
	 *
	 * @access	protected
	 * @param	mixed $index
	 * @param	mixed $value
	 */
	protected function setPostParam($index, $value){
		$funcGetArgs = func_get_args();
		return call_user_func_array(array($this->getRequestInstance(), 'setParamPost'), $funcGetArgs);
	}

	/**
	 * Establece el valor de un parámetro enviado por $_GET;
	 *
	 * @access	protected
	 * @param	mixed $index
	 * @param	mixed $value
	 */
	protected function setQueryParam($index, $value){
		$funcGetArgs = func_get_args();
		return call_user_func_array(array($this->getRequestInstance(), 'setParamQuery'), $funcGetArgs);
	}

	/**
	 * Establece el valor de un parámetro enviado por $_COOKIE;
	 *
	 * @access	protected
	 * @param	mixed $index
	 * @param	mixed $value
	 */
	protected function setCookie($index, $value){
		$funcGetArgs = func_get_args();
		return call_user_func_array(array($this->getRequestInstance(), 'setParamCookie'), $funcGetArgs);
	}

	/**
	 * Sube un archivo al directorio img/upload si esta en $_FILES
	 *
	 * @access	protected
	 * @param	string $name
	 * @return	string
	 */
	protected function uploadImage($name){
		if(isset($_FILES[$name])){
			move_uploaded_file($_FILES[$name]['tmp_name'], htmlspecialchars('public/img/upload/'.$_FILES[$name]['name']));
			return urlencode(htmlspecialchars('upload/'.$_FILES[$name]['name']));
		} else {
			return urlencode($this->request($name));
		}
	}

	/**
	 * Sube un archivo al directorio $dir si esta en $_FILES
	 *
	 * @access	public
	 * @param	string $name
	 * @param	string $dir
	 * @return	string
	 */
	protected function uploadFile($name, $dir){
		if(!isset($_FILES[$name])){
			return false;
		}
		if($_FILES[$name]){
			return move_uploaded_file($_FILES[$name]['tmp_name'], htmlspecialchars($dir.'/'.$_FILES[$name]['name']));
		} else {
			return false;
		}
	}

	/**
	 * Indica si un controlador va a ser persistente, en este
	 * caso los valores internos son automaticamente almacenados
	 * en sesion y disponibles cada vez que se ejecute una accion
	 * en el controlador
	 *
	 * @access 	public
	 * @param 	boolean $value
	 */
	protected function setPersistance($value){
		self::$_persistance[get_class($this)] = $value;
	}

	/**
	 * Indica si el controlador es persistente o no
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function getPersistance(){
		if(isset(self::$_persistance[get_class($this)])){
			return self::$_persistance[get_class($this)];
		} else {
			return false;
		}
	}

	/**
	 * Redirecciona la ejecución a otro controlador en un
	 * tiempo de ejecución determinado
	 *
	 * @access	protected
	 * @param	string $controller
	 * @param	integer $seconds
	 */
	protected function redirect($controller, $seconds=0.5){
		$config = CoreConfig::readEnviroment();
		$instancePath = Core::getInstancePath();
		if(headers_sent()==true){
			$seconds*=1000;
			echo "<script type='text/javascript'>window.setTimeout(\"window.location='", $instancePath, $controller, "'\", $seconds);</script>\n";
		} else {
			$application = Router::getActiveApplication();
			View::setRenderLevel(View::LEVEL_NO_RENDER);
			if($application==""){
				$this->getResponseInstance()->setHeader('Location: '.$instancePath.$controller, true);
			} else {
				$this->getResponseInstance()->setHeader('Location: '.$instancePath.$application.'/'.$controller, true);
			}
		}
	}

	/**
	 * Indica el tipo de Respuesta dada por el controlador (este metodo está obsoleto)
	 *
	 * @access 		public
	 * @param		string $type
	 * @deprecated
	 */
	public function setResponse($type){
		$response = ControllerResponse::getInstance();
		switch($type){
			case 'ajax':
			case 'view':
				View::setRenderLevel(View::LEVEL_ACTION_VIEW);
				$response->setResponseType(ControllerResponse::RESPONSE_NORMAL);
				$response->setResponseAdapter('');
				break;
			case 'xml':
				View::setRenderLevel(View::LEVEL_NO_RENDER);
				$response->setResponseType(ControllerResponse::RESPONSE_OTHER);
				$response->setResponseAdapter('xml');
				break;
			case 'json':
				$response->setResponseType(ControllerResponse::RESPONSE_OTHER);
				View::setRenderLevel(View::LEVEL_NO_RENDER);
				$response->setResponseAdapter('json');
				break;
			case 'rss':
				View::setRenderLevel(View::LEVEL_NO_RENDER);
				$response->setResponseType(ControllerResponse::RESPONSE_OTHER);
				$response->setResponseAdapter('rss');
				break;
		}
	}

	/**
	 * Reescribir este metodo permite controlar las excepciones generadas en un controlador
	 *
	 * @access protected
	 * @param Exception $exception
	 */
	protected function exceptions($exception){
		throw $exception;
	}

	/**
	 * Crea un log sino existe y guarda un mensaje
	 *
	 * @access	protected
	 * @param	string $message
	 * @param	integer $type
	 */
	protected function log($message, $type=Logger::DEBUG){
		if(is_array($message)||is_object($message)){
			$message = print_r($message, true);
		}
		$className = get_class($this);
		if(!isset(self::$_logger[$className])){
			$controllerName = self::$_controllerName[$className];
			self::$_logger[$className] = new Logger('File', $controllerName.'.txt');
		}
		self::$_logger[$className]->log($message, $type);
	}

	/**
	 * Devuelve una salida en JavaScript
	 *
	 * @access protected
	 * @param string $js
	 */
	protected function renderJavascript($js){
		$this->renderText("<script type='text/javascript'>$js</script>");
	}

	/**
	 * Convierte una variable a notacion JSON
	 *
	 * @access protected
	 * @param mixed $data
	 * @return string
	 */
	protected function jsonEncode($data){
		return json_encode($data);
	}

	/**
	 * Genera una salida JSON estableciendo el tipo de salida adecuada
	 *
	 * @access protected
	 * @param mixed $data
	 */
	protected function outputJSONResponse($data){
		$this->setResponse('json');
		echo json_encode($data);
	}

	/**
	 * Devuelve el nombre del controlador actual
	 *
	 * @access	public
	 * @return	string
	 */
	public function getControllerName(){
		$className = get_class($this);
		if(isset(self::$_controllerName[$className])){
			return self::$_controllerName[$className];
		} else {
			return $className;
		}
	}

	/**
	 * Establece el nombre del controlador actual
	 *
	 * @access	public
	 * @param	string $controllerName
	 */
	public function setControllerName($controllerName){
		self::$_controllerName[get_class($this)] = $controllerName;
	}

	/**
	 * Devuelve el nombre de la acción actual
	 *
	 * @access public
	 * @return string
	 */
	public function getActionName(){
		$className = get_class($this);
		if(isset(self::$_actionName[$className])){
			return self::$_actionName[$className];
		} else {
			return '';
		}
	}

	/**
	 * Establece el nombre de la acción actual
	 *
	 * @access	public
	 * @param	string $actionName
	 */
	public function setActionName($actionName){
		self::$_actionName[get_class($this)] = $actionName;
	}

	/**
	 * Establece el valor del parametro id del controlador
	 *
	 * @access	public
	 * @param	string $id
	 */
	public function setId($id){
		self::$_id[get_class($this)] = $id;
	}

	/**
	 * Devuelve el valor del parametro id del controlador
	 *
	 * @access public
	 */
	public function getId(){
		$className = get_class($this);
		if(isset(self::$_id[$className])){
			return self::$_id[$className];
		} else {
			return '';
		}
	}

	/**
	 * Establece el valor de los parametros adicionales en el controlador
	 *
	 * @access	public
	 * @param	array $parameters
	 */
	public function setParameters($parameters){
		self::$_parameters[get_class($this)] = $parameters;
	}

	/**
	 * Indica si el controlador actual tiene implementada una acción con el nombre indicado
	 *
	 * @param 	string $actionName
	 * @return	boolean
	 */
	public function hasAction($actionName){
		return method_exists($this, $actionName.'Action');
	}

	/**
	 * Establece el valor de todos los parametros adicionales en el controlador
	 *
	 * @access	public
	 * @param	array $allParameters
	 */
	public function setAllParameters($allParameters){
		self::$_allParameters[get_class($this)] = $allParameters;
	}

	/**
	 * Establece el/los template(s) que se insertan antes del layout del controlador
	 *
	 * @access public
	 * @param string|array $template
	 */
	public final function setTemplateBefore($template){
		self::$_templateBefore[get_class($this)] = $template;
	}

	/**
	 * Limpia los templates que se insertarán en la petición antes del layout del controlador
	 *
	 * @access public
	 */
	public final function cleanTemplateBefore(){
		self::$_templateBefore[get_class($this)] = '';
	}

	/**
	 * Establece el/los template(s) que se insertan despues del layout del controlador
	 *
	 * @access 	public
	 * @param 	string|array $template
	 */
	public final function setTemplateAfter($template){
		self::$_templateAfter[get_class($this)] = $template;
	}

	/**
	 * Limpia los templates que se insertarán en la petición despues del layout del controlador
	 *
	 * @access public
	 */
	public final function cleanTemplateAfter(){
		self::$_templateAfter[get_class($this)] = '';
	}

	/**
	 * Devuelve el/los nombre(s) del Template Before Actual
	 *
	 * @access public
	 * @return string|array|null
	 */
	public final function getTemplateBefore(){
		if(isset(self::$_templateBefore[get_class($this)])){
			return self::$_templateBefore[get_class($this)];
		} else {
			return null;
		}
	}

	/**
	 * Devuelve el/los nombre(s) del Template After Actual
	 *
	 * @access 	public
	 * @return 	string|array
	 */
	public final function getTemplateAfter(){
		if(isset(self::$_templateAfter[get_class($this)])){
			return self::$_templateAfter[get_class($this)];
		} else {
			return null;
		}
	}

	/**
	 * Devuelve la instancia del Objeto Request
	 *
	 * @access 	public
	 * @return 	ControllerRequest
	 */
	public function getRequestInstance(){
		return ControllerRequest::getInstance();
	}

	/**
	 * Devuelve la instancia del Objeto Response
	 *
	 * @access 	public
	 * @return 	ControllerResponse
	 */
	public function getResponseInstance(){
		return ControllerResponse::getInstance();
	}

	/**
	 * Establece una variable de la vista directamente
	 *
	 * @access 	public
	 * @param 	string $key
	 * @param 	string $value
	 */
	public function setParamToView($key, $value){
		View::setViewParam($key, $value);
	}

	/**
	 * Al deserializar asigna 0 a los tiempos del cache
	 *
	 * @access public
	 */
	public function __wakeup(){
		if(method_exists($this, 'initialize')){
			$this->initialize();
		}
	}

	/**
	 * Establece el control de acceso a las propiedades del controlador
	 *
	 * @access 	public
	 * @param 	boolean $lock
	 */
	public function setSettingLock($lock){
		self::$_settingLock[get_class($this)] = $lock;
	}

	/**
	 * La definición de este metodo indica si se debe exportar las variables publicas
	 *
	 * @access 	public
	 * @return 	boolean
	 */
	public function isExportable(){
		return false;
	}

	/**
	 * Obliga a que todas las propiedades del controlador esten definidas
	 * previamente
	 *
	 * @access	public
	 * @param	string $property
	 * @param	string $value
	 */
	public function __set($property, $value){
		if(self::$_settingLock[get_class($this)]==false){
			if(EntityManager::isModel($property)==false){
				throw new ApplicationControllerException('Asignando propiedad indefinida "'.$property.'" al controlador');
			}
		} else {
			$this->$property = $value;
		}
	}

	/**
	 * Obliga a que todas las propiedades del controlador esten definidas
	 * previamente
	 *
	 * @access	public
	 * @param	string $property
	 */
	public function __get($property){
		if(EntityManager::isModel($property)==false){
			throw new ApplicationControllerException('Leyendo propiedad indefinida "'.$property.'" del controlador');
		} else {
			$className = get_class($this);
			$entity = EntityManager::getEntityInstance($property);
			self::$_settingLock[$className] = true;
			$this->$property = $entity;
			self::$_settingLock[$className] = false;
			return $this->$property;
		}
	}

	/**
	 * Carga los modelos como atributos del controlador
	 *
	 */
	public function loadModel(){
		$className = get_class($this);
		foreach(func_get_args() as $model){
			$entity = EntityManager::getEntityInstance($model);
			self::$_settingLock[$className] = true;
			$this->$model = $entity;
			self::$_settingLock[$className] = false;
		}
	}

	/**
	 * Obtiene una instancia de un servicio web del contenedor ó mediante Naming Directory
	 *
	 * @param 	mixed $serviceNDI
	 * @return  WebServiceClient
	 */
	public function getService($serviceNDI){
		return Resolver::lookUp($serviceNDI);
	}

	/**
	 * Valida que los campos requeridos enten presentes
	 *
	 * @access	protected
	 * @param	string $fields
	 * @param	string $base
	 * @param	string $getMode
	 * @return	boolean
	 */
	protected function validateRequired($fields, $base='', $getMode=''){
		return Validation::validateRequired($fields, $base, $getMode);
	}

	/**
	 * Limpia la lista de Mensajes
	 *
	 * @access protected
	 */
	protected function cleanValidationMessages(){
		Validation::cleanValidationMessages();
	}

	/**
	 * Agrega un mensaje a la lista de mensajes
	 *
	 * @access 	protected
	 * @param 	string $fieldName
	 * @param 	string $message
	 */
	protected function addValidationMessage($message, $fieldName=''){
		Validation::addValidationMessage($message, $fieldName);
	}

	/**
	 * Devuelve los mensajes de validación generados
	 *
	 * @access 	protected
	 * @return 	array
	 */
	public function getValidationMessages(){
		return Validation::getMessages();
	}

	/**
	 * Devuelve un callback que administrara la forma en que se presente
	 * la vista del controlador
	 *
	 * @access public
	 */
	public function getViewHandler(){
		return self::$_defaultOutputHandler;
	}

	/**
	 * Devuelve un callback que administrará la forma en que se presente
	 * la vista del controlador
	 *
	 * @access 	public
	 * @return 	callback
	 */
	public function getViewExceptionHandler(){
		return self::$_defaultExceptionHandler;
	}

	/**
	 * Establece el administrador de presentación por defecto
	 *
	 * @param callback $handler
	 */
	public static function setDefaultOutputHandler($handler){
		self::$_defaultOutputHandler = $handler;
	}

	/**
	 * Establece el administrador de excepciones por defecto
	 *
	 * @param callback $handler
	 */
	public static function setDefaultExceptionHandler($handler){
		self::$_defaultExceptionHandler = $handler;
	}

}
