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
 * @package		Cache
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: File.php 111 2009-10-23 20:57:52Z gutierrezandresfelipe $
 */

/**
 * FileCache
 *
 * Adaptador que permite almacenar datos en un Cache
 *
 * @category	Kumbia
 * @package		Cache
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */
class FileCache {

	/**
	 * Opciones front-end del adaptador
	 *
	 * @var array
	 */
	private $_frontendOptions = array();

	/**
	 * Opciones back-end del adaptador
	 *
	 * @var array
	 */
	private $_backendOptions = array();

	/**
	 * Ultima llave que se le hizo una consulta
	 *
	 * @var string
	 */
	private $_lastKey = "";

	/**
	 * Contructor de FileCache
	 *
	 * @param	array $frontendOptions
	 * @param	array $backendOptions
	 */
	public function __construct($frontendOptions, $backendOptions){
		$this->_frontendOptions = $frontendOptions;
		$this->_backendOptions = $backendOptions;
		#if[compile-time]
		if(isset($backendOptions['cacheDir'])){
			if(!is_writable($backendOptions['cacheDir'])){
				throw new CacheException('El directorio para caches no existe ó no tiene permisos de escritura');
			}
		} else {
			throw new CacheException('Debe indicar el directorio para caches con la opción cachesDir');
		}
		#endif
	}

	/**
	 * Carga un valor cacheado mediante una llave
	 *
	 * @param 	string $keyName
	 * @return  mixed
	 */
	public function start($keyName){
		$backend = $this->_backendOptions;
		$cacheDir = $backend['cacheDir'];
		$cacheFile = $cacheDir.'/'.$keyName;
		if(file_exists($cacheFile)){
			$frontend = $this->_frontendOptions;
			$time = $_SERVER['REQUEST_TIME'];
			$lifetime = $frontend['lifetime'];
			if(($time-$lifetime)<filemtime($cacheFile)){
				return file_get_contents($cacheFile);
			} else {
				$this->_lastKey = $keyName;
				ob_start();
				return null;
			}
		} else {
			$this->_lastKey = $keyName;
			ob_start();
			return null;
		}
	}

	/**
	 * Almacena un resultado con load ó el valor en buffer por start
	 *
	 * @param mixed $value
	 * @param string $keyName
	 */
	public function save($value=null, $keyName=''){
		if($keyName==''){
			$keyName = $this->_lastKey;
			$this->_lastKey = '';
		}
		$backend = $this->_backendOptions;
		$cacheDir = $backend['cacheDir'];
		$cacheFile = $cacheDir.'/'.$keyName;
		$cachedContent = ob_get_contents();
		file_put_contents($cacheFile, $cachedContent);
		ob_end_clean();
		echo $cachedContent;
	}

}