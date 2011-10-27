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
 * @subpackage	CoreConfig
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: CoreConfig.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * CoreConfig
 *
 * Se encarga de leer los archivos de configuración de las aplicaciones
 * e integrar las opciones definidas en ellos a los componentes del framework.
 *
 * @category	Kumbia
 * @package		Core
 * @subpackage	CoreConfig
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 * @abstract
 */
abstract class CoreConfig {

	/**
	 * Adaptador de configuración por defecto
	 *
	 * @var string
	 */
	static private $_configAdapter = 'ini';

	/**
	 * Lee un archivo de configuración
	 *
	 * @param	string $file
	 * @return	Config
	 * @static
	 */
	static public function read($path){
		return Config::read($path);
	}

	/**
	 * Lee un archivo de configuración usando el adaptador por defecto
	 *
	 * @param	string $file
	 * @return	Config
	 * @static
	 */
	static public function readFile($name){
		return self::readFromActiveApplication($name.'.'.self::$_configAdapter, self::$_configAdapter);
	}

	/**
	 * Lee el archivo de configuración Environment
	 *
	 * @access	public
	 * @return	Config
	 * @throws	CoreConfigException
	 * @static
	 */
	static public function readEnviroment(){
		$application = Router::getApplication();

		//Configuración entorno
		$config = self::readFile('environment');

		//Configuración general
		$core = self::readAppConfig();

		if(!isset($core->application->mode)){
			//No se ha definido el entorno por defecto
			$message = CoreLocale::getErrorMessage(-12);
			throw new CoreConfigException($message, -12);
		}
		//Carga las variables db del modo indicado
		$mode = $core->application->mode;
		if(isset($config->$mode)){
			foreach($config->$mode as $conf => $value){
				if(preg_match('/(\w+)\.(\w+)/', $conf, $registers)){
					if(!isset($config->{$registers[1]})){
						$config->{$registers[1]} = new stdClass();
					}
					$config->{$registers[1]}->{$registers[2]} = $value;
				} else {
					$config->$conf = $value;
				}
			}
		} else {
			//No existe el entorno en environment.ini
			$message = CoreLocale::getErrorMessage(-13, $mode);
			throw new CoreConfigException($message, -13);
		}

		//Carga las variables de la seccion [project]
		if(isset($config->project)){
			foreach($config->project as $conf => $value){
				if(preg_match('/(\w+)\.(\w+)/', $conf, $registers)){
					if(!isset($config->{$registers[1]})){
						$config->{$registers[1]} = new stdClass();
					}
					$config->{$registers[1]}->{$registers[2]} = $value;
				} else {
					$config->$conf = $value;
				}
			}
		}
		return $config;
	}

	/**
	 * Establece el adaptador a utilizar para definir los archivos de configuración
	 *
	 * @access 	public
	 * @param 	string $adapter
	 * @static
	 */
	public static function setAdapter($adapter){
		self::$_configAdapter = $adapter;
	}

	/**
	 * Devuelve el adaptador para leer los archivos por defecto
	 *
	 * @return string
	 * @static
	 */
	public static function getAdapter(){
		return self::$_configAdapter;
	}

	/**
	 * Devuelve la configuracion de la aplicacion indicada
	 *
	 * @access 	public
	 * @param 	string $application
	 * @param 	string $file
	 * @param 	string $adapter
	 * @return 	Config
	 * @static
	 */
	public static function getConfigurationFrom($application, $file, $adapter=''){
		if($application==''){
			throw new CoreConfigException('Debe indicar el nombre de la aplicación donde está el archivo "'.$file.'"');
		}
		if($adapter==''){
			$adapter = self::$_configAdapter;
		}
		return Config::read('apps/'.$application.'/config/'.$file, $adapter);
	}

	/**
	 * Devuelve la configuracion de la aplicacion indicada
	 *
	 * @access 	public
	 * @param 	Config $config
	 * @param 	string $application
	 * @param 	string $file
	 * @param 	string $adapter
	 * @return 	boolean
	 * @static
	 */
	public static function setConfigurationOn($config, $application, $file, $adapter=''){
		if($application==''){
			throw new CoreConfigException('Debe indicar el nombre de la aplicación donde está el archivo "'.$file.'"');
		}
		if($adapter==''){
			$adapter = self::$_configAdapter;
		}
		return Config::write($config, 'apps/'.$application.'/config/'.$file, $adapter);
	}

	/**
	 * Devuelve la configuración de la aplicación actual
	 *
	 * @access 	public
	 * @param 	string $file
	 * @param 	string $adapter
	 * @return 	Config
	 * @static
	 */
	public static function readFromActiveApplication($file, $adapter=''){
		$application = Router::getApplication();
		return self::getConfigurationFrom($application, $file, $adapter);
	}

	/**
	 * Lee el archivo de configuración de una aplicación
	 *
	 * @param string $application
	 * @return Config
	 * @static
	 */
	public static function readAppConfig($application=''){
		if($application==''){
			$application = Router::getApplication();
		}
		return self::getConfigurationFrom($application, 'config.'.self::$_configAdapter, self::$_configAdapter);
	}

	/**
	 * Escribe el archivo de configuración de una aplicación
	 *
	 * @param Config $config
	 * @param string $application
	 * @static
	 */
	public static function writeAppConfig(Config $config, $application=''){
		if($application==''){
			$application = Router::getApplication();
		}
		return self::setConfigurationOn($config, $application, 'config.'.self::$_configAdapter, self::$_configAdapter);
	}

	/**
	 * Lee una configuración del apartado $section de config.ini
	 *
	 * @param	string $setting
	 * @return	string
	 */
	public static function getAppSetting($setting, $section='application'){
		$config = self::readAppConfig();
		if(isset($config->$section->$setting)){
			return $config->$section->$setting;
		} else {
			return null;
		}
	}

	/**
	 * Lee el archivo de carga de extensiones
	 *
	 * @return Config
	 * @static
	 */
	public static function readBootConfig(){
		return self::readFile('boot');
	}

	/**
	 * Lee el archivo de rutas estáticas
	 *
	 * @return Config
	 * @static
	 */
	public static function readRoutesConfig(){
		return self::readFile('routes');
	}

	/**
	 * Devuelve la configuracion de la instancia
	 *
	 * @access public
	 * @return Config
	 * @static
	 */
	public static function getInstanceConfig(){
		return Config::read('config/config.ini', 'ini');
	}

}
