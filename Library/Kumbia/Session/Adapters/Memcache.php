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
 * @package 	Session
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Memcache.php 51 2009-05-12 03:45:18Z gutierrezandresfelipe $
 */

/**
 * MemcacheSessionAdapter
 *
 * Adaptador de Sesion para Memcache
 *
 * @category 	Kumbia
 * @package 	Session
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class MemcacheSessionAdapter implements SessionInterface {

	/**
	 * Nombre del manejador de session interno
	 *
	 * @var string
	 */
	private $_save_handler = 'memcache';

	/**
	 * Devuelve el nombre del manejador de session interno
	 *
	 * @access public
	 * @return string
	 */
	public function getSaveHandler(){
		return $this->_save_handler;
	}

	/**
	 * Inicializa el Session Handler
	 *
	 * @access public
	 */
	public function initialize(){
		$config = CoreConfig::readAppConfig();
		if(isset($config->application->sessionSavePath)){
			session_save_path($config->application->sessionSavePath);
		} else {
			throw new SessionException("El adaptador '".get_class($this)."' requiere que indique el sessionSavePath en el config.ini de la aplicaci&oacute;n");
		}
	}

}
