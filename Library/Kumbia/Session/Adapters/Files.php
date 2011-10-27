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
 * @version 	$Id: Files.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * FilesSessionAdapter
 *
 * Adaptador de Sesion para Archivos planos de texto serializados
 *
 * @category 	Kumbia
 * @package 	Session
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class FilesSessionAdapter implements SessionInterface {

	/**
	 * Nombre del manejador de session interno
	 *
	 * @var string
	 */
	private $_saveHandler = 'files';

	/**
	 * Devuelve el nombre del manejador de session interno
	 *
	 * @access public
	 * @return string
	 */
	public function getSaveHandler(){
		return $this->_saveHandler;
	}

	/**
	 * Inicializa el Session Handler
	 *
	 * @access public
	 */
	public function initialize(){
		if(isset($config->application->sessionSavePath)){
			session_save_path($config->application->sessionSavePath);
		}
	}

}
