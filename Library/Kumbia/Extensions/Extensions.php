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
 * @package		Extensions
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2008-2008 Emilio Rafael Silveira Tovar (emilio.rst at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Extensions.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * Extensions
 *
 * Componente que permite cargar extensiones dinámicamente
 *
 * @category	Kumbia
 * @package		Extensions
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2008-2008 Emilio Rafael Silveira Tovar (emilio.rst at gmail.com)
 * @license		New BSD License
 * @abstract
 */
abstract class Extensions {

	/**
	 * Listado de las extensiones cargadas
	 *
	 * @var array
	 */
	static private $_extensions = array();

	/**
	 * Directorio donde se encuentran las extensiones de usuario
	 *
	 * @var string
	 */
	static private $_userDirectory = '';

	/**
	 * Directorio donde se encuentran las extensiones Zend
	 *
	 * @var string
	 */
	static private $_zendDirectory = '';

	/**
	 * Limpia la extensiones cargadas
	 *
	 */
	static public function cleanExtensions(){
		self::$_extensions = array();
	}

	/**
	 * Carga los modulos que esten en el archivo boot.ini de cada
	 * aplicacion
	 *
	 * @access public
	 * @static
	 */
	static public function loadBooteable(){

		//Instancia Activa
		$instanceName = Core::getInstanceName();

	 	//Aplicacion Activa
		$activeApp = Router::getApplication();

		/**
         * La lista de modulos en boot.ini son cacheados en la variable de sesion
         * $_SESSION['KMOD'] para no leer este archivo muchas veces
         *
         * La variable extensiones en el apartado modules en config/boot.ini
         * tiene valores estilo Kumbia.Acl,... esto hace que Kumbia cargue
         * automaticamente en el directorio Library/Kumbia/Acl el archivo Helpers.php.
         *
         * Esta variable tambien puede ser utilizada para cargar modulos de
         * usuario y clases personalizadas
         *
         * Chequee la funcion Core::import() en este mismo archivo para encontrar una forma
         * alternativa para cargar modulos y clases de usuario en Kumbia
         *
         */
		if(!isset($_SESSION['KMOD'])){
			$_SESSION['KMOD'] = array();
		}
		if(!isset($_SESSION['KMOD'][$instanceName])){
			$_SESSION['KMOD'][$instanceName] = array();
		}
		if(!isset($_SESSION['KMOD'][$instanceName][$activeApp])){
			$_SESSION['KMOD'][$instanceName][$activeApp] = array();
			$bootConfig = CoreConfig::readBootConfig();
			if(isset($bootConfig->modules->extensions)){
				$bootConfig->modules->extensions = str_replace(' ', '', $bootConfig->modules->extensions);
				$extensions = explode(',', $bootConfig->modules->extensions);
				if($extensions[0]!=''){
					foreach($extensions as $extension){
						self::_addExtension($extension);
					}
				}
			}
			if(isset($bootConfig->classes->files)){
				$_SESSION['KFIL'][$instanceName][$activeApp] = array();
				$bootConfig->classes->files = str_replace(' ', '', $bootConfig->classes->files);
				$files = explode(',', $bootConfig->classes->files);
				if($files[0]!=''){
					foreach($files as $file){
						$_SESSION['KFIL'][$instanceName][$activeApp][] = $file;
						unset($file);
					}
				}
				unset($files);
			}
		}
		foreach($_SESSION['KMOD'][$instanceName][$activeApp] as $extension){
			require KEF_ABS_PATH.$extension;
			unset($extension);
		}
		if(isset($_SESSION['KFIL'][$instanceName][$activeApp])){
			foreach($_SESSION['KFIL'][$instanceName][$activeApp] as $file){
				require KEF_ABS_PATH.$file;
				unset($file);
			}
		}

		unset($activeApp);
		unset($instanceName);

	}

	/**
	 * Indica si una extension ha sido cargada en la aplicacion
	 *
	 * @access public
	 * @param string $extension
	 * @return boolean
	 * @static
	 */
	static public function isLoaded($extension){
		return in_array($extension, self::$_extensions);
	}

	/**
	 * Devuelve un array con las extensiones cargadas
	 *
	 * @access public
	 * @static
	 */
	static public function getLoadedExtensions(){
		return self::$_extensions;
	}

	/**
	 * Cargar una extension dinamicamente
	 *
	 * @access public
	 * @param string $extension
	 * @static
	 */
	static public function loadExtension($extension){
		if(self::isLoaded($extension)==false){
			$extensionPath = self::_addExtension($extension);
			require KEF_ABS_PATH.$extensionPath;
		}
	}

	/**
	 * Establece el directorio de extensiones dinámicamente
	 *
	 * @param string $directory
	 */
	static public function setUserDirectory($directory){
		self::$_userDirectory = $directory;
	}

	/**
	 * Establece el directorio de extensiones Zend
	 *
	 * @param string $directory
	 */
	static public function setZendDirectory($directory){
		self::$_zendDirectory = $directory;
	}

	/**
	 * Obtiene el path a una extension
	 *
	 * @access private
	 * @param string $extension
	 * @return string
	 * @static
	 */
	static private function _getExtensionPath($extension){
		$ex = explode('.', $extension);
		if($ex[0]=='Kumbia'){
			$extensionPath = 'Library/Kumbia/'.$ex[1].'/'.$ex[1].'.php';
		} else {
			if($ex[0]=='User'){
				$activeApp = Router::getApplication();
				if(self::$_userDirectory==''){
					$config = CoreConfig::readAppConfig();
					if(isset($config->application->libraryDir)){
						$libraryDir = 'apps/'.$config->application->libraryDir;
					} else {
						$libraryDir = 'apps/'.$activeApp.'/library';
					}
				} else {
					$libraryDir = self::$_userDirectory;
				}
				$extensionPath = $libraryDir.'/'.$ex[1].'/'.$ex[0].'.php';
			} else {
				if($ex[0]=='Zend'){
					if(self::$_zendDirectory!=''){
						$zendDir = self::$_zendDirectory;
					} else {
						$zendDir = 'Library/Zend';
					}
					$pwd = getcwd();
					chdir($zendDir);
					require KEF_ABS_PATH.$ex[1].'.php';
					chdir($pwd);
				} else {
					$extensionPath = 'Library/'.$ex[0].'/'.$ex[0].'.php';
				}
			}
		}
		return $extensionPath;
	}

	/**
	 * Obtiene el nombre de una extension apartir de su path
	 *
	 * @access private
	 * @param string $extensionPath
	 * @return string
	 * @static
	 */
	static private function _getExtensionNameByPath($extensionPath){
		$ex = explode('/', $extensionPath);
		return $ex[1].'.'.$ex[2];
	}

	/**
	 * Carga una extension a la lista de extensiones
	 *
	 * @access private
	 * @param string $extension
	 * @static
	 */
	static private function _addExtension($extension){
		$activeApp = Router::getApplication();
		$extensionPath = self::_getExtensionPath($extension);
		$instanceName = Core::getInstanceName();
		if(!in_array($extensionPath, $_SESSION['KMOD'][$instanceName][$activeApp])){
			$_SESSION['KMOD'][$instanceName][$activeApp][] = $extensionPath;
		}
		self::$_extensions[] = $extension;
		return $extensionPath;
	}

}
