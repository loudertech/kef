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
 * @version 	$Id: Cache.php 111 2009-10-23 20:57:52Z gutierrezandresfelipe $
 */

/**
 * Cache
 *
 * Clase que implementa un componente de cacheo
 *
 * @category	Kumbia
 * @package		Cache
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */
class Cache {

	/**
	 * Constructor de Cache
	 *
	 * @param	string $adapter
	 * @param	array $frontendOptions
	 * @param	array $backendOptions
	 * @static
	 */
	public static function factory($adapter, $frontendOptions=array(), $backendOptions=array()){
		$adapterClass = $adapter.'Cache';
		if(class_exists($adapterClass, false)==false){
			$path = 'Library/Kumbia/Cache/Adapters/'.$adapter.'.php';
			if(Core::fileExists($path)){
				require KEF_ABS_PATH.$path;
			} else {
				throw new CacheException('No existe el adaptador "'.$adapter."'");
			}
		}
		return new $adapterClass($frontendOptions, $backendOptions);
	}

}
