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
 * @package 	Plugin
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Plugin.php 51 2009-05-12 03:45:18Z gutierrezandresfelipe $
 */

/**
 * @see Plugin
 */
require KEF_ABS_PATH.'Library/Kumbia/Plugin/Abstract/Plugin.php';

/**
 * PluginManager
 *
 * Este componente permite administrar los plugins cargados en una aplicación
 * facilitando  el agregar ó quitar dinámicamente plug-ins.
 *
 * @category 	Kumbia
 * @package 	Plugin
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
abstract class PluginManager {

	/**
	 * Indica si los plug-ins deben ser autoinicializados
	 *
	 * @var boolean
	 */
	static private $_autoInitialize = true;

	/**
	 * Array de todos los plugins
	 *
	 * @var array
	 */
	static private $_plugins = array();

	/**
	 * Array de los plugins de aplicacion
	 *
	 * @var array
	 */
	static private $_pluginsApplication = array();

	/**
	 * Array de los plugins controlador
	 *
	 * @var array
	 */
	static private $_pluginsController = array();

	/**
	 * Array de los plugins de modelos
	 *
	 * @var array
	 */
	static private $_pluginsModel = array();

	/**
	 * Array de los plugins de vistas
	 *
	 * @var array
	 */
	static private $_pluginsView = array();

	/**
	 * Array de plugins de componentes
	 *
	 * @var array
	 */
	static private $_pluginsComponents = array();

	/**
	 * Clases de Plugins
	 *
	 * @var array
	 */
	static private $_pluginClasses = array();

	/**
	 * Carga los plugins de la aplicacion activa
	 *
	 * @access 	public
	 * @return 	array
	 * @static
	 */
	static public function loadApplicationPlugins(){

		//Leer configuracion
		$config = CoreConfig::readAppConfig();

		//Esta variable permite que no se inicialicen los plug-ins
		if(!isset($config->plugins->autoInitialize)||$config->plugins->autoInitialize==true){

			//Aplicacion activa
			$activeApp = Router::getApplication();

			//Obtener las ruta a los plugins
			if(isset($config->application->pluginsDir)){
				$pluginsDir = 'apps/'.$config->application->pluginsDir;
			} else {
				$pluginsDir = 'apps/'.$activeApp.'/plugins';
			}
			self::$_pluginClasses = array();
			if(Core::fileExists($pluginsDir)){
				foreach(scandir($pluginsDir) as $plugin){
					if(strpos($plugin, '.php')){
						self::$_pluginClasses[] = str_replace('.php', '', $plugin).'Plugin';
						require KEF_ABS_PATH.$pluginsDir.'/'.$plugin;
					}
				}
			}

		}
	}

	/**
	 * Inicializa los plugins cargados
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	static public function initializePlugins(){

		$plugins = array();
		$pluginsApplication = array();
		$pluginsController = array();
		$pluginsModel = array();
		$pluginsView = array();
		$pluginsComponents = array();

		foreach(self::$_pluginClasses as $pluginClass){
			if(class_exists($pluginClass, false)){
				$plugIn = new $pluginClass();
			} else {
				throw new PluginException('No existe la clase plug-in "'.$pluginClass.'" en el archivo '.$pluginClass.'.php');
			}
			if(is_subclass_of($plugIn, 'Plugin')){
				$plugins[] = $plugIn;
			}
			if(is_subclass_of($plugIn, 'ApplicationPlugin')){
				$pluginsApplication[] = $plugIn;
			} else {
				if(is_subclass_of($plugIn, 'ControllerPlugin')){
					$pluginsController[] = $plugIn;
				} else {
					if(is_subclass_of($plugIn, 'ModelPlugin')){
						$pluginsModel[] = $plugIn;
					} else {
						if(is_subclass_of($plugIn, 'ViewPlugin')){
							$pluginsView[] = $plugIn;
						} else {
							if(is_subclass_of($plugIn, 'ComponentPlugin')){
								$pluginsComponents[] = $plugIn;
							}
						}
					}
				}
			}
		}

		self::$_plugins = $plugins;
		self::$_pluginsApplication = $pluginsApplication;
		self::$_pluginsController = $pluginsController;
		self::$_pluginsModel = $pluginsModel;
		self::$_pluginsView = $pluginsView;
		self::$_pluginsComponents = $pluginsComponents;
	}

	/**
	 * Obtiene instancias de los plugins
	 *
	 * @access public
	 * @return array
	 * @static
	 */
	static public function getPlugins(){
		return self::$plugins;
	}

	/**
	 * Obtiene instancias de los plugins tipo Controller
	 *
	 * @access public
	 * @return array
	 * @static
	 */
	static public function getControllerPlugins(){
		return self::$_pluginsController;
	}

	/**
	 * Obtiene instancias de los plugins tipo Model
	 *
	 * @access public
	 * @return array
	 * @static
	 */
	static public function getModelPlugins(){
		return self::$_pluginsModel;
	}

	/**
	 * Obtiene instancias de los plugins tipo View
	 *
	 * @access public
	 * @return array
	 * @static
	 */
	static public function getViewPlugins(){
		return self::$_pluginsView;
	}

	/**
	 * Notifica un evento de la aplicacion a los plugins
	 *
	 * @access public
	 * @param string $event
	 * @static
	 */
	static public function notifyFromApplication($event){
		foreach(self::$_pluginsApplication as $plugin){
			if(method_exists($plugin, $event)){
				$args = func_get_args();
				unset($args[0]);
				call_user_func_array(array($plugin, $event), $args);
			}
		}
	}

	/**
	 * Notifica un evento de los controladores a los plugins
	 *
	 * @param	string $event
	 * @param	Controller $controller
	 */
	static public function notifyFromController($event, $controller){
		if(count(self::$_pluginsController)>0){
			foreach(self::$_pluginsController as $plugin){
				if(method_exists($plugin, $event)){
					$plugin->$event($controller);
					Router::ifRouted();
				}
			}
		}
	}

	/**
	 * Notifica un evento de los controladores a los plugins
	 *
	 * @param	string $event
	 * @param	ControllerResponse $controllerResponse
	 * @static
	 */
	static public function notifyFromView($event, $controllerResponse){
		if(count(self::$_pluginsView)>0){
			foreach(self::$_pluginsView as $plugin){
				if(method_exists($plugin, $event)){
					$plugin->$event($controllerResponse);
				}
			}
		}
	}

	/**
	 * Notifica un evento desde un componente
	 *
	 * @access 	public
	 * @param 	string $component
	 * @param 	string $event
	 * @param 	string $reference
	 * @static
	 */
	static public function notifyFrom($component, $event, $reference){
		if(count(self::$_pluginsComponents)>0){
			foreach(self::$_pluginsComponents as $plugin){
				if(method_exists($plugin, $event)){
					$plugin->$event($reference);
				}
			}
		}
	}

	/**
	 * Registra dinámicamente un plugin de controlador
	 *
	 * @access 	public
	 * @param 	ControllerPlugin $plugin
	 * @static
	 */
	static public function registerControllerPlugin($plugin){
		self::$_plugins[] = $plugin;
		self::$_pluginsController[] = $plugin;
	}

	/**
	 * Registra dinámicamente un plugin de aplicacion
	 *
	 * @access 	public
	 * @param 	ApplicationPlugin $plugin
	 * @static
	 */
	static public function registerApplicationPlugin($plugin){
		self::$_plugins[] = $plugin;
		self::$_pluginsApplication[] = $plugin;
	}

	/**
	 * Registra dinámicamente un plugin de View
	 *
	 * @access 	public
	 * @param 	ViewPlugin $plugin
	 * @static
	 */
	static public function registerViewPlugin($plugin){
		self::$_plugins[] = $plugin;
		self::$_pluginsApplication[] = $plugin;
	}

	/**
	 * Deshabilita un plugin por su nombre de clase
	 *
	 * @param string $pluginName
	 */
	static public function disablePlugin($pluginName){

	}

}
