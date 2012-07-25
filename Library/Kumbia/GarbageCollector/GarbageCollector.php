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
 * @package		GarbageCollector
 * @copyright	Copyright (c) 2008-2012 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: GarbageCollector.php,v b2e37ddd474e 2011/10/24 00:32:13 andres $
 */

/**
 * GarbageCollector
 *
 * Recolector de Basura. Comprmime ó elimina datos del estado de persistencia que no se esten utilizando
 *
 * @category	Kumbia
 * @package		GarbageCollector
 * @copyright	Copyright (c) 2008-2012 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierezandresfelipe at gmail.com)
 * @license		New BSD License
 * @abstract
 */
class GarbageCollector {

	/**
	 * Probabilidad que se active el recolector de Basura
	 *
	 * @var int
	 */
	static private $_probability = 50;

	/**
	 * Tiempo en que comprimira los datos no utilizados recientemente
	 *
	 * @var int
	 */
	static private $_compressTime = 900;

	/**
	 * Tiempo que debe pasar para liberar datos
	 *
	 * @var int
	 */
	static private $_collectTime = 1800;

	/**
	 * Establece el Tiempo que debe pasar para liberar datos
	 *
	 * @access public
	 * @param int $collectTime
	 * @static
	 */
	public static function setCollectTime($collectTime){
		self::$_collectTime = (int) $collectTime;
	}

	/**
	 * Establece la probabilidad de invocar el collector
	 *
	 * @access public
	 * @param int $probabilty
	 * @static
	 */
	public static function setProbability($probabilty){
		self::$_probability = (int) $probabilty;
	}

	/**
	 * Establece el Tiempo en que comprimira los datos no utilizados recientemente
	 *
	 * @access public
	 * @param int $probabilty
	 * @static
	 */
	public static function setCompressTime($compressTime){
		self::$_compressTime = (int) $compressTime;
	}

	/**
	 * Invoca el recolector de basura
	 *
	 * @access	public
	 * @param 	bool $always
	 * @static
	 */
	public static function startCollect($always=false){
		if($always){
			$rand = $prob_pos = 1;
		} else {
			$prob_pos = (int)(self::$_probability*50);
			$rand = mt_rand(1, self::$_probability*50+1);
		}
		if($rand==$prob_pos){

			//Controladores Persistentes
			self::freePersistentController();

			//ActiveRecord MetaData
			self::freeAllMetaData();

			//StandardForm Meta-Data
			self::freeStandardFormData();

			//Libera las cookies de sesión
			self::freeServiceCookies();
		}
	}

	/**
	 * Libera las cookies de sesión
	 *
	 */
	static public function freeServiceCookies(){
		//Cookies de servicios Web
		unset($_SESSION['KHC']);
	}

	/**
	 * Limpia datos persistentes de controladores
	 *
	 */
	static public function freePersistentController(){

		$instanceName = Core::getInstanceName();
		$activeApp = Router::getApplication();
		$t = Core::getProximityTime();
		$expireTime = $t-self::$_collectTime;
		$compressTime = $t-self::$_compressTime;

		if(isset($_SESSION['KCON'][$instanceName][$activeApp])){
			foreach($_SESSION['KCON'][$instanceName][$activeApp] as $moduleName => $module){
				foreach($module as $controllerName => $controller){
					if($controller['time']<$expireTime){
						unset($_SESSION['KCON'][$instanceName][$activeApp][$moduleName][$controllerName]);
					} else {
						if($controller['time']<$compressTime){
							if($controller['status']=='N'){
								$data = gzcompress($_SESSION['KCON'][$instanceName][$activeApp][$moduleName][$controllerName]['data']);
								$_SESSION['KCON'][$instanceName][$activeApp][$moduleName][$controllerName]['data'] = $data;
								$_SESSION['KCON'][$instanceName][$activeApp][$moduleName][$controllerName]['status'] = 'C';
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Limpia meta-datos de standard form
	 *
	 */
	static public function freeStandardFormData(){

		$instanceName = Core::getInstanceName();
		$activeApp = Router::getApplication();
		$t = Core::getProximityTime();
		$expireTime = $t-self::$_collectTime;
		$compressTime = $t-self::$_compressTime;

		if(isset($_SESSION['KSF'][$instanceName][$activeApp])){
			foreach($_SESSION['KSF'][$instanceName][$activeApp] as $formName => $metaData){
				if($metaData['time']<$expireTime){
					unset($_SESSION['KSF'][$instanceName][$activeApp][$formName]);
				} else {
					if($metaData['time']<$compressTime){
						if($metaData['status']=='N'){
							if(isset($_SESSION['KCON'][$instanceName][$activeApp][$formName]['data'])){
								$data = gzcompress($_SESSION['KCON'][$instanceName][$activeApp][$formName]['data']);
								$_SESSION['KSF'][$instanceName][$activeApp][$formName]['data'] = $data;
								$_SESSION['KSF'][$instanceName][$activeApp][$formName]['status'] = 'C';
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Resetea la persistencia de un controlador
	 *
	 * @access 	public
	 * @param 	string $appController
	 * @param 	string $module
	 * @static
	 */
	static public function freeControllerData($appController, $module=''){
		$instanceName = Core::getInstanceName();
		$activeApp = Router::getApplication();
		$appController.='Controller';
		if(isset($_SESSION['KCON'][$instanceName][$activeApp][$module][$appController])){
			unset($_SESSION['KCON'][$instanceName][$activeApp][$module][$appController]);
		}
	}

	/**
	 * Libera toda los datos de sesión de entidades ActiveRecord
	 *
	 * @access public
	 * @static
	 */
	static public function freeAllMetaData(){

		$instanceName = Core::getInstanceName();
		$activeApp = Router::getApplication();
		$modelsDir = Core::getActiveModelsDir();

		//APC shared
		if(function_exists('apc_store')){
			$results = new APCIterator('user', null);
			foreach($results as $name => $result){
				apc_delete($name);
			}
		}

		//Shared memory
		if(function_exists('shm_attach')){
			if(PHP_OS=='Darwin'){
				$shmId = shm_attach(0xff7, 0xffff, 0644);
			} else {
				$shmId = shm_attach(0xff7, 0x7d000, 0777);
			}
			@shm_remove($shmId);
		} else {
			//File Metadata
			if(file_exists($modelsDir.'/metadata')){
				foreach(scandir($modelsDir.'/metadata') as $file){
					if(preg_match('/\.php$/', $file)){
						unlink($modelsDir.'/metadata/'.$file);
					}
				}
			}
		}

		//Session Meta-Data
		if(isset($_SESSION['KMD'][$instanceName][$activeApp])){
			foreach($_SESSION['KMD'][$instanceName][$activeApp] as $schemaName => $schema){
				foreach($schema as $tableName => $metaData){
					$metaData['dp'] = (int) $metaData['dp'];
					if($metaData['dp']<$expireTime||$metaData['dp']<$compressTime){
						unset($_SESSION['KMD'][$instanceName][$activeApp][$schemaName][$tableName]);
					}
				}
				if(count($schema)==0){
					unset($_SESSION['KMD'][$instanceName][$activeApp][$schemaName]);
				}
			}
		}
	}

	/**
	 * Libera los datos temporales de sesión
	 *
	 */
	public static function freeSessionData(){

		//Controladores Persistentes
		self::freePersistentController();

		//StandardForm Meta-Data
		self::freeStandardFormData();

		//Libera las cookies de sesión
		self::freeServiceCookies();
	}

	/**
	 * Libera toda los datos de sesión de formularios StandardForm
	 *
	 * @access public
	 * @static
	 */
	static public function freeAllStdForm(){
		$instanceName = Core::getInstanceName();
		$activeApp = Router::getApplication();
		if(isset($_SESSION['KSF'][$instanceName][$activeApp])){
			unset($_SESSION['KSF'][$instanceName][$activeApp]);
		}
	}

	/**
	 * Invoca el Garbage Collector de PHP y libera los objetos ZVAL que no están
	 * siendo referenciados por ninguna variable del proceso actual
	 *
	 * @access public
	 * @static
	 */
	static public function collectCycles(){
		if(function_exists('gc_collect_cycles')){
			gc_collect_cycles();
		}
	}

}
