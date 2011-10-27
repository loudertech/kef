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
 * @category 	Kumbia
 * @package 	View
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: View.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * View
 *
 * El componente View se encarga de administrar la forma estándar en la que se
 * genera la presentación al usuario final en su explorador. La presentación
 * estándar en una aplicación en Kumbia Enterprise se basa en varios patrones
 * de diseño que permiten reducir la codificación y hacer más mantenible
 * esta parte del desarrollo.
 *
 * El primer patrón utilizado es Template View el cuál habla de utilizar
 * tags personalizados ó marcas embebidas en el contenido dinámico proporcionando
 * flexibilidad y poder para crear interfaces web. El segundo patrón es el
 * Two State View el cual permite definir múltiples interfaces de acuerdo
 * al dispositivo ó cliente desde el cuál se este se accediendo a la aplicación.
 *
 * Este tipo de implementación favorece principalmente aplicaciones
 * que accedan desde un browser ó un telefono celular en donde es
 * necesario personalizar detalles para cada tipo de interfaz.
 *
 * La arquitectura MVC presenta el concepto de vista la cuál actúa como
 * puente entre el usuario final y la lógica de dominio en los controladores.
 *
 * @category 	Kumbia
 * @package 	View
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @access		public
 * @abstract
 */
abstract class View {

	/**
	 * Nivel de presentación: Hasta la vista principal
	 *
	 */
	const LEVEL_MAIN_VIEW = 6;

	/**
	 * Nivel de presentación: Hasta los template after
	 *
	 */
	const LEVEL_AFTER_TEMPLATE = 5;

	/**
	 * Nivel de presentación: Hasta el layout del controlador
	 *
	 */
	const LEVEL_LAYOUT = 3;

	/**
	 * Nivel de presentación: Hasta los template before
	 *
	 */
	const LEVEL_BEFORE_TEMPLATE = 2;

	/**
	 * Nivel de presentación: Hasta la vista de la acción
	 *
	 */
	const LEVEL_ACTION_VIEW = 1;

	/**
	 * Nivel de presentación: No utilizar ninguna vista
	 *
	 */
	const LEVEL_NO_RENDER = 0;

	/**
	 * Cachea la salida al navegador
	 *
	 * @var string
	 */
	static private $_content = '';

	/**
	 * Variables de la Vista
	 *
	 * @var array
	 */
	static private $_data = array();

	/**
	 * Nivel de profundidad de la visualización
	 *
	 * @var integer
	 */
	static private $_renderLevel = 6;

	/**
	 * Proxy a componente de Terceros
	 *
	 * @var string
	 */
	static private $_proxyProvider;

	/**
	 * Opciones del proxy
	 *
	 * @var array
	 */
	static private $_proxyOptions;

	/**
	 * Componente usado como PluginManager
	 *
	 * @var array
	 */
	static private $_pluginManager = 'PluginManager';

	/**
	 * Envia la salida en buffer al navegador
	 *
	 * @access 	public
	 * @param 	boolean $returnContent
	 * @return 	string
	 * @static
	 */
	public static function getContent($returnContent=false){
		if($returnContent==false){
			echo self::$_content;
		} else {
			return self::$_content;
		}
		return "";
	}

	/**
	 * Establece el componente que será usado como PluginManager
	 *
	 * @access 	public
	 * @param 	array $pluginManager
	 * @static
	 */
	public static function setPluginManager($pluginManager){
		self::$_pluginManager = $pluginManager;
	}

	/**
	 * Inicializa la salida
	 *
	 * @access 	private
	 * @param 	$_controllerName
	 * @param 	$_actionName
	 * @static
	 */
	static private function _startResponse($_controllerName, $_actionName){
		$_controllerResponse = ControllerResponse::getInstance();
		//Establece una salida normal
		$_controllerResponse->setHeader('X-Application-State: OK');
		//Establece la ubicacion actual
		$location = $_controllerName.'/'.$_actionName;
		$_controllerResponse->setHeader('X-Application-Location: '.$location, true);
		#if[no-view-plugins]
		call_user_func_array(array(self::$_pluginManager, 'notifyFromView'), array('beforeRender', $_controllerResponse));
		#endif
	}

	/**
	 * Carga el adaptador de View
	 *
	 * @access	private
	 * @static
	 */
	static private function _loadAdapter(){
		$_controllerResponse = ControllerResponse::getInstance();
		$adapter = ucfirst($_controllerResponse->getResponseAdapter());
		$adapterClassName = $adapter.'ViewResponse';
		if(!class_exists($adapterClassName, false)){
			if(!interface_exists('ViewResponseInterface', false)){
				require 'Library/Kumbia/View/Interface.php';
			}
			$path = 'Library/Kumbia/View/Adapters/'.$adapter.'.php';
			if(file_exists(KEF_ABS_PATH.$path)==true){
				require KEF_ABS_PATH.$path;
			}
		}
		return new $adapterClassName();
	}

	/**
	 * Visualiza un valor con el adaptador de presentación
	 *
	 * @param string $value
	 */
	static private function _handleResponseAdapter($value){
		$_controllerResponse = ControllerResponse::getInstance();
		if($_controllerResponse->getResponseAdapter()){
			$responseHandler = self::_loadAdapter();
			$responseHandler->render($_controllerResponse, $value);
		}
	}

	/**
	 * Envia la excepcion al adaptador de presentación
	 *
	 * @param Exception $e
	 */
	static private function _handleExceptionAdapter($e){
		$_controllerResponse = ControllerResponse::getInstance();
		if($_controllerResponse->getResponseAdapter()){
			$responseHandler = self::_loadAdapter();
			$responseHandler->renderException($_controllerResponse, $e);
		}
	}

	/**
	 * Toma el objeto controlador y ejecuta la presentación correspondiente a este
	 *
	 * @access 	public
	 * @param 	Controller $_controller
	 * @static
	 */
	static public function handleViewRender($_controller){

		$_controllerResponse = ControllerResponse::getInstance();
		$_valueReturned = Dispatcher::getValueReturned();
		if($_controllerResponse->getResponseType()!=ControllerResponse::RESPONSE_NORMAL){
			self::_handleResponseAdapter($_valueReturned);
			#if[no-view-plugins]
			call_user_func_array(array(self::$_pluginManager, 'notifyFromView'), array('afterRender', $_controllerResponse));
			#endif
			unset($_valueReturned);
			return;
		}

		$_controllerName = $_controller->getControllerName();
		$_actionName = $_controller->getActionName();
		self::_startResponse($_controllerName, $_actionName);

		if(!empty($_controllerName)){
			foreach(EntityManager::getEntities() as $_entityName => $_entity){
				$$_entityName = $_entity;
			}
			if($_controller->isExportable()==true){
				foreach($_controller as $_var => $_value) {
					$$_var = $_value;
				}
			}
			foreach(self::$_data as $_key => $_value){
				$$_key = $_value;
			}
			if(!isset(${'id'})){
				${'id'} = $_controller->getId();
			}

			/**
			 * View busca un los templates correspondientes al nombre de la acción y el layout
			 * del controlador. Si el controlador tiene un atributo $template también va a
			 * cargar la vista ubicada en layouts con el valor de esta
			 *
			 * en views/$_controller/$action
			 * en views/layouts/$_controller
			 * en views/layouts/$template
			 *
			 * Los archivos con extensión .phtml son archivos template de kumbia que
			 * tienen codigo html y php y son el estandar
			 *
			 */
			self::$_content = ob_get_contents();


			//Obtener directorio de vistas activo
			$_activeApp = Router::getActiveApplication();
			$_viewsDir = Core::getActiveViewsDir();

			/**
			 * Verifica si existe cache para el layout, vista ó template
	 		 * sino, crea un directorio en cache
	 		 */
			if($_controllerName!=""){

				//Crear los directorios de cache si es necesario
				#if[compile-time]
				if($_controller->getViewCache()||$_controller->getLayoutCache()){
					$_viewCacheDir = 'cache/'.session_id().'/';
					if(!file_exists(KEF_ABS_PATH.'cache/'.session_id().'/')){
						mkdir($_viewCacheDir);
					}
					$_viewCacheDir.=$_activeApp.'_'.$_controllerName;
					if(!file_exists(KEF_ABS_PATH.$_viewCacheDir)){
						mkdir($_viewCacheDir);
					}
				}
				#endif

				//Insertar la vista si es necesario
				if(self::$_renderLevel>=self::LEVEL_ACTION_VIEW){
					if(file_exists(KEF_ABS_PATH.$_viewsDir.'/'.$_controllerName.'/'.$_actionName.'.phtml')){
						ob_clean();
						// Aqui verifica si existe un valor en minutos para el cache
						#if[compile-time]
						if($_controller->getViewCache()>0){
							/**
					 		 * Busca el archivo en el directorio de cache que se crea
					 		 * a partir del valor $_SESSION['SID'] para que sea único
					 		 * para cada sesión
					 		 */
							if(file_exists(KEF_ABS_PATH.$_viewCacheDir.'/'.$_actionName)==false){
								include KEF_ABS_PATH.$_viewsDir.'/'.$_controllerName.'/'.$_actionName.'.phtml';
								file_put_contents($_viewCacheDir.'/'.$_actionName, ob_get_contents());
							} else {
								$time_cache = $_controller->get_view_cache();
								if((time()-$time_cache*60)<filemtime($_viewCacheDir.'/'.$_actionName)){
									include KEF_ABS_PATH.$_viewCacheDir.'/'.$_actionName;
								} else {
									include KEF_ABS_PATH.$_viewsDir.'/'.$_controllerName.'/'.$_actionName.'.phtml';
									file_put_contents($_viewCacheDir.'/'.$_actionName, ob_get_contents());
								}
							}
						} else {
							#endif
							include KEF_ABS_PATH.$_viewsDir.'/'.$_controllerName.'/'.$_actionName.'.phtml';
							#if[compile-time]
						}
						#endif
						self::$_content = ob_get_contents();
					}
				}

				// Incluir el/los Template(s) before
				if(self::$_renderLevel>=self::LEVEL_BEFORE_TEMPLATE){
					$_template = $_controller->getTemplateBefore();
					if($_template!=""){
						if(is_array($_template)==false){
							// Aqui verifica si existe un valor en minutos para el cache
							if(file_exists(KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_controller->getTemplateBefore().'.phtml')){
								ob_clean();
								#if[compile-time]
								if($_controller->getLayoutCache()){
									/**
							   		 * Busca el archivo en el directorio de cache que se crea
							 	 	 * a partir del valor session_id() para que sea único
							 	 	 * para cada sesion
							 	 	 */
									if(!file_exists(KEF_ABS_PATH.$_viewCacheDir.'/layout')){
										include KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_controller->getTemplateBefore().'.phtml';
										file_put_contents($_viewCacheDir.'/layout', ob_get_contents());
									} else {
										$time_cache = $_controller->getLayoutCache();
										if((time()-$time_cache*60)<filemtime($_viewCacheDir.'/layout')){
											include KEF_ABS_PATH.$_viewCacheDir.'/layout';
										} else {
											include KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_controller->getTemplateBefore().'.phtml';
											file_put_contents($_viewCacheDir.'/layout', ob_get_contents());
										}
									}
								} else {
									#endif
									include KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_controller->getTemplateBefore().'.phtml';
									#if[compile-time]
								}
								#endif
								self::$_content = ob_get_contents();
							} else {
								throw new ViewException("No existe el template '$_template' en views/layouts");
							}
						} else {
							foreach(array_reverse($_template) as $_singleTemplate){
								/**
								 * Aqui verifica si existe un valor en minutos para el cache
							 	 */
								if(file_exists(KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_singleTemplate.'.phtml')){
									ob_clean();
									#if[compile-time]
									if($_controller->getLayoutCache()){
								   		// Busca el archivo en el directorio de cache que se crea
								 	 	// a partir del valor session_id() para que sea único
								 	 	// para cada sesión
										if(!file_exists(KEF_ABS_PATH.$_viewCacheDir.'/layout')){
											include KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_singleTemplate.'.phtml';
											file_put_contents($_viewCacheDir.'/layout', ob_get_contents());
										} else {
											$time_cache = $_controller->getLayoutCache();
											if((time()-$time_cache*60)<filemtime($_viewCacheDir.'/layout')){
												include KEF_ABS_PATH.$_viewCacheDir.'/layout';
											} else {
												include KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_singleTemplate.'.phtml';
												file_put_contents($_viewCacheDir."/layout", ob_get_contents());
											}
										}
									} else {
										#endif
										include KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_singleTemplate.'.phtml';
										#if[compile-time]
									}
									#endif
									self::$_content = ob_get_contents();
								} else {
									throw new ViewException("No existe el template '$_singleTemplate' en views/layouts");
								}
							}
						}
					}
				}

				//Incluir Layout
				if(self::$_renderLevel>=self::LEVEL_LAYOUT){
					if(file_exists(KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_controllerName.'.phtml')){
						ob_clean();
						#if[compile-time]
						if($_controller->getLayoutCache()){
							/**
				 			 * Busca el archivo en el directorio de cache que se crea
				 	 		 * a partir del valor session_id() para que sea único
				 	 		 * para cada sesion
				 	 		 */
							if(!file_exists(KEF_ABS_PATH.$_viewCacheDir.'/layout')){
								include KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_controllerName.'.phtml';
								file_put_contents($_viewCacheDir.'/layout', ob_get_contents());
							} else {
								$time_cache = $_controller->getLayoutCache();
								if((time()-$time_cache*60)<filemtime($_viewCacheDir.'/layout')){
									include KEF_ABS_PATH.$_viewCacheDir.'/layout';
								} else {
									include KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_controllerName.'.phtml';
									file_put_contents($_viewCacheDir.'/layout', ob_get_contents());
								}
							}
						} else {
							#endif
							include KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_controllerName.'.phtml';
							#if[compile-time]
						}
						#endif
						self::$_content = ob_get_contents();
					}
				}
			}

			//Incluir el/los Template(s) After
			if(self::$_renderLevel>=self::LEVEL_AFTER_TEMPLATE){
				$_template = $_controller->getTemplateAfter();
				if($_template!=""){
					if(is_array($_template)==false){
						/**
						 * Aqui verifica si existe un valor en minutos para el cache
						 */
						if(file_exists(KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_controller->getTemplateAfter().'.phtml')){
							ob_clean();
							#if[compile-time]
							if($_controller->getLayoutCache()){
								/**
							   	 * Busca el archivo en el directorio de cache que se crea
							 	 * a partir del valor session_id() para que sea único
							 	 * para cada sesion
							 	 */
								if(!file_exists(KEF_ABS_PATH.$_viewCacheDir.'/layout')){
									include KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_controller->getTemplateAfter().".phtml";
									file_put_contents($_viewCacheDir."/layout", ob_get_contents());
								} else {
									$time_cache = $_controller->getLayoutCache();
									if((time()-$time_cache*60)<filemtime($_viewCacheDir."/layout")){
										include KEF_ABS_PATH.$_viewCacheDir."/layout";
									} else {
										include KEF_ABS_PATH."$_viewsDir/layouts/".$_controller->getTemplateAfter().".phtml";
										file_put_contents($_viewCacheDir."/layout", ob_get_contents());
									}
								}
							} else {
								#endif
								include KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_controller->getTemplateAfter().'.phtml';
								#if[compile-time]
							}
							#endif
							self::$_content = ob_get_contents();
						} else {
							throw new ViewException("No existe el template '$_template' en views/layouts");
						}
					} else {
						foreach(array_reverse($_template) as $_singleTemplate){
							/**
							 * Aqui verifica si existe un valor en minutos para el cache
							 */
							if(file_exists(KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_singleTemplate.'.phtml')){
								ob_clean();
								#if[compile-time]
								if($_controller->getLayoutCache()){
									/**
								   	 * Busca el archivo en el directorio de cache que se crea
								 	 * a partir del valor session_id() para que sea único
								 	 * para cada sesion
								 	 */
									if(!file_exists(KEF_ABS_PATH.$_viewCacheDir.'/layout')){
										include KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_singleTemplate.'.phtml';
										file_put_contents($_viewCacheDir.'/layout', ob_get_contents());
									} else {
										$time_cache = $_controller->getLayoutCache();
										if((time()-$time_cache*60)<filemtime($_viewCacheDir.'/layout')){
											include KEF_ABS_PATH.$_viewCacheDir.'/layout';
										} else {
											include KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_singleTemplate.'.phtml';
											file_put_contents($_viewCacheDir.'/layout', ob_get_contents());
										}
									}
								} else {
									#endif
									include KEF_ABS_PATH.$_viewsDir.'/layouts/'.$_singleTemplate.'.phtml';
									#if[compile-time]
								}
								#endif
								self::$_content = ob_get_contents();
							} else {
								throw new ViewException("No existe el template '$_singleTemplate' en views/layouts");
							}
						}
					}
				}
			}

			/**
			 * Incluir Vista Principal
			 */
			if(self::$_renderLevel>=self::LEVEL_MAIN_VIEW){
				if(file_exists(KEF_ABS_PATH.$_viewsDir.'/index.phtml')){
					ob_clean();
					include KEF_ABS_PATH.$_viewsDir.'/index.phtml';
					self::$_content = ob_get_contents();
				}
				$_controller = null;
				if(Core::isTestingMode()==true){
					ob_clean();
				}
			}
			unset($_valueReturned);
			unset($_activeApp);
			unset($_viewsDir);
			unset($_controller);
		}
		#if[no-view-plugins]
		call_user_func_array(array(self::$_pluginManager, 'notifyFromView'), array('afterRender', $_controllerResponse));
		#endif
	}

	/**
	 * Administra la presentación cuando se genera una excepción en la presentación
	 *
	 * @access 	public
	 * @param 	Exception $e
	 * @param 	Controller $_controller
	 * @static
	 */
	static public function handleViewExceptions($e, $_controller){
		if(Core::isTestingMode()==false){

			if(!$_controller){
				$_controller = new Controller();
			}
			$_controllerResponse = ControllerResponse::getInstance();
			$_controllerRequest = ControllerRequest::getInstance();

			//Se está solicitando contenido estático
			if($_controllerRequest->isRequestingStaticContent()==true){
				$location = Router::getController().'/'.Router::getAction();
				$_controllerResponse->setHeader('HTTP/1.1 500 Application Exception', true);
				$_controllerResponse->setHeader('X-Application-State: Exception', true);
				$_controllerResponse->setHeader('X-Application-Location: '.$location, true);
				if(get_class($e)=='DispatcherException'){
					return;
				}
			}

			//Se genera un encabezado HTTP de problema
			$_controllerResponse->setHeader('HTTP/1.1 500 Application Exception', true);
			$_controllerResponse->setHeader('X-Application-State: Exception', true);
			// Si el encabezado solicita la salida en de la excepcion en XML se realiza asi
			if(isset($_SERVER['HTTP_X_ACCEPT_CONTENT'])&&$_SERVER['HTTP_X_ACCEPT_CONTENT']=='text/xml'){
				//Genera una salida XML valida
				$_controllerResponse->setHeader('Content-Type: text/xml', true);
				$_controllerResponse->setHeader('Pragma: no-cache', true);
				$_controllerResponse->setHeader('Expires: 0', true);
				ob_end_clean();
				echo $e->showMessageAsXML();
			} else {
				// Si no es una Accion AJAX incluye index.phtml y muestra
				// el contenido de las excepciones dentro de este.
				if($_controllerResponse->getResponseAdapter()!='json'){
					Tag::removeStylesheets();
					if(count(ob_get_status(true))>0){
						ob_clean();
					}
					$e->showMessage();
					self::$_content = ob_get_contents();
					@ob_end_clean();
					View::xhtmlTemplate('white');
				} else {
					self::_handleExceptionAdapter($e);
				}
			}
		} else {
			throw $e;
		}
	}

	/**
	 * Permite visualizar una vista parcial
	 *
	 * @access	public
	 * @param	string $_partialView
	 * @param	string $_partialValue
	 * @static
	 */
	public static function renderPartial($_partialView, $_partialValue=''){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(!isset($params['controller'])){
			$_controllerName = Router::getController();
		} else {
			$_controllerName = $params['controller'];
		}
		$_viewsDir = Core::getActiveViewsDir();
		$partialPath = $_viewsDir.'/'.$_controllerName.'/_'.$_partialView.'.phtml';
		if(file_exists(KEF_ABS_PATH.$partialPath)==false){
			$partialPath = $_viewsDir.'/partials/_'.$_partialView.'.phtml';
			if(file_exists(KEF_ABS_PATH.$partialPath)==false){
				throw new ViewException('No se puede encontrar la vista parcial: "'.$_partialView.'"', 0);
			}
		}
		foreach(EntityManager::getEntities() as $_entityName => $_entity){
			$$_entityName = $_entity;
		}
		foreach(self::$_data as $_key => $_value){
			$$_key = $_value;
		}
		$_controller = Dispatcher::getController();
		if($_controller->isExportable()==true){
			foreach($_controller as $_var => $_value) {
				$$_var = $_value;
			}
			${'id'} = $_controller->getId();
		}
		$$_partialView = $_partialValue;
		include KEF_ABS_PATH.$partialPath;
	}

	/**
	 * Permite renderizar una vista del controlador actual
	 *
	 * @access 	public
	 * @param	string $_view
	 * @static
	 */
	static public function renderView($_view){
		$_viewsDir = Core::getActiveViewsDir();
		if(file_exists(KEF_ABS_PATH.$_viewsDir.'/'.$_view.'.phtml')){
			$_controller = Dispatcher::getController();
			if($_controller->isExportable()){
				foreach($_controller as $_var => $_value) {
					$$_var = $_value;
				}
				foreach(self::$_data as $_key => $_value){
					$$_key = $_value;
				}
				${'id'} = $_controller->getId();
			}
			include KEF_ABS_PATH.$_viewsDir.'/'.$_view.'.phtml';
		} else {
			throw new ViewException("La vista '$_view' no existe o no se puede cargar", 0);
		}
	}

	/**
	 * Devuelve los mensajes de validacion generados en el controlador
	 *
	 * @access	public
	 * @return	array
	 * @static
	 */
	public static function getValidationMessages(){
		$_controller = Dispatcher::getController();
		return $_controller->getValidationMessages();
	}

	/**
	 * Permite definir el contenido de salida
	 *
	 * @access 	public
	 * @param 	string $content
	 * @static
	 */
	public static function setContent($content){
		self::$_content = $content;
	}

	/**
	 * Establece una variable de vista
	 *
	 * @access 	public
	 * @param	string $index
	 * @param 	string $value
	 */
	public static function setViewParam($index, $value){
		self::$_data[$index] = $value;
	}

	/**
	 * Devuelve las variables de vistas
	 *
	 * @access	public
	 * @return	array
	 * @static
	 */
	public static function getViewParams(){
		return self::$_data;
	}

	/**
	 * Elimina todas las variables de vistas definida
	 *
	 */
	public static function cleanViewParams(){
		self::$_data = array();
	}

	/**
	 * Establece el nivel de profundidad de la visualización
	 *
	 * @access 	public
	 * @param 	int $level
	 * @static
	 */
	public static function setRenderLevel($level){
		self::$_renderLevel = $level;
	}

	/**
	 * Establece el nivel de profundidad de la visualización
	 *
	 * @return 	int
	 * @static
	 */
	public static function getRenderLevel(){
		return self::$_renderLevel;
	}

	/**
	 * Indica que no se debe visualizar la vista
	 *
	 * @static
	 */
	public static function noRender(){
		self::$_renderLevel = self::LEVEL_NO_RENDER;
	}

	/**
	 * Establece el proxy provider para las vistas
	 *
	 * @access 	public
	 * @param 	string $proxy
	 * @param 	array $options
	 * @static
	 */
	public static function setProxyProvider($proxy, $options){
		self::$_proxyProvider = $proxy;
		self::$_proxyOptions = $options;
	}

	/**
	 * Reenvia las peticiones de vistas a otros componentes de terceros
	 *
	 * @access public
	 * @static
	 */
	public static function proxyHandler(){
		//Cargar el ProxyProvider
		$path = 'Library/Kumbia/View/Proxy/'.self::$_proxyProvider.'.php';
		if(file_exists(KEF_ABS_PATH.$path)){
			require KEF_ABS_PATH.$path;
			$proxyClass = self::$_proxyProvider.'ProxyView';
			$proxyAdapter = new $proxyClass(self::$_proxyOptions);

			$_controller = Dispatcher::getController();
			$_controllerName = $_controller->getControllerName();
			$_actionName = $_controller->getActionName();
			self::_startResponse($_controllerName, $_actionName);

			//Exportar datos
			foreach(EntityManager::getEntities() as $_entityName => $_entity){
				$proxyAdapter->setData($_entityName, $_entity);
			}
			if($_controller->isExportable()==true){
				foreach($_controller as $_var => $_value){
					$proxyAdapter->setData($_var, $_value);
				}
			}
			foreach(self::$_data as $_key => $_value){
				$proxyAdapter->setData($_key, $_value);
			}
			$proxyAdapter->setData('id', $_controller->getId());

			//Salida del controlador
			self::$_content = ob_get_contents();

			if($_controllerName!=""){
				$_activeApp = Router::getActiveApplication();
				$_viewsDir = Core::getActiveViewsDir();
				// Insertar la vista si es necesario
				if(self::$_renderLevel>=self::LEVEL_ACTION_VIEW){
					$path = $_viewsDir.'/'.$_controllerName.'/';
					if(file_exists(KEF_ABS_PATH.$path.$_actionName.'.phtml')){
						ob_clean();
						echo $proxyAdapter->renderView($path, $_actionName);
						self::$_content = ob_get_contents();
					}
				}

				//Incluir el/los Template(s) before
				if(self::$_renderLevel>=self::LEVEL_BEFORE_TEMPLATE){
					$_template = $_controller->getTemplateBefore();
					if($_template!=""){
						if(is_array($_template)==false){
							/**
							 * Aqui verifica si existe un valor en minutos para el cache
						 	 */
							$path = $_viewsDir.'/layouts/';
							if(file_exists(KEF_ABS_PATH.$path.$_controller->getTemplateBefore().'.phtml')){
								ob_clean();
								echo $proxyAdapter->renderView($path.$_controller->getTemplateBefore());
								self::$_content = ob_get_contents();
							} else {
								throw new ViewException("No existe el template '$_template' en views/layouts");
							}
						} else {
							foreach(array_reverse($_template) as $_singleTemplate){
								// Aqui verifica si existe un valor en minutos para el cache
								$path = $_viewsDir.'/layouts/';
								if(file_exists(KEF_ABS_PATH.$path.$_singleTemplate.'.phtml')){
									ob_clean();
									echo $proxyAdapter->renderView($path, $_singleTemplate);
									self::$_content = ob_get_contents();
								} else {
									throw new ViewException("No existe el template '$_singleTemplate' en views/layouts");
								}
							}
						}
					}
				}

				// Incluir Layout
				if(self::$_renderLevel>=self::LEVEL_LAYOUT){
					$path = $_viewsDir.'/layouts/';
					if(file_exists(KEF_ABS_PATH.$path.$_controllerName.'.phtml')){
						ob_clean();
						echo $proxyAdapter->renderView($path, $_controllerName);
						self::$_content = ob_get_contents();
					}
				}
			}

			// Incluir el/los Template(s) After
			if(self::$_renderLevel>=self::LEVEL_AFTER_TEMPLATE){
				$_template = $_controller->getTemplateAfter();
				if($_template!=""){
					if(is_array($_template)==false){
						// Aqui verifica si existe un valor en minutos para el cache
						$path = $_viewsDir.'/layouts/';
						if(file_exists(KEF_ABS_PATH.$path.$_controller->getTemplateAfter().'.phtml')){
							ob_clean();
							echo $proxyAdapter->renderView($path, $_controller->getTemplateAfter());
							self::$_content = ob_get_contents();
						} else {
							throw new ViewException("No existe el template '$_template' en views/layouts");
						}
					} else {
						foreach(array_reverse($_template) as $_singleTemplate){
							// Aqui verifica si existe un valor en minutos para el cache
							$path = $_viewsDir.'/layouts/';
							if(file_exists(KEF_ABS_PATH.$path.$_singleTemplate.'.phtml')){
								ob_clean();
								echo $proxyAdapter->renderView($path, $_singleTemplate);
								self::$_content = ob_get_contents();
							} else {
								throw new ViewException("No existe el template '$_singleTemplate' en views/layouts");
							}
						}
					}
				}
			}

			/**
			 * Incluir Vista Principal
			 */
			if(self::$_renderLevel>=self::LEVEL_MAIN_VIEW){
				if(file_exists(KEF_ABS_PATH.$_viewsDir.'/index.phtml')){
					ob_clean();
					include KEF_ABS_PATH.$_viewsDir.'/index.phtml';
					self::$_content = ob_get_contents();
				}
				$_controller = null;
				if(Core::isTestingMode()==true){
					ob_clean();
				}
			}

			$_controllerResponse = ControllerResponse::getInstance();
			#if[no-view-plugins]
			call_user_func_array(array(self::$_pluginManager, 'notifyFromView'), array('afterRender', $_controllerResponse));
			#endif
		} else {
			throw new ViewException('No existe el proxy a "'.self::$_proxyProvider.'"');
		}
	}

	/**
	 * Consulta si una vista de accion existe
	 *
	 * @param 	string $name
	 * @param 	string $_controllerName
	 * @return	boolean
	 * @static
	 */
	public static function existsActionView($name, $_controllerName=''){
		if($_controllerName==''){
			$_controllerName = Router::getController();
		}
		$_viewsDir = Core::getActiveViewsDir();
		$path = $_viewsDir.'/'.$_controllerName.'/'.$name.'.phtml';
		return file_exists(KEF_ABS_PATH.$path);
	}

	/**
	 * Inserta un documento XHTML antes de una salida en buffer
	 *
	 * @access public
	 * @param string $template
	 * @static
 	 */
	public static function xhtmlTemplate($template='template'){
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  '.Tag::getDocumentTitle().'
  <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />'."\n";

		//Cargar estilos
		Tag::stylesheetLink('style');
		echo Tag::stylesheetLinkTags();

		//Cargar Javascripts
		echo Tag::javascriptSources();

		echo '</head><body class="'.$template.'">';
		View::getContent();
		echo '
 </body>
</html>';
	}

}
