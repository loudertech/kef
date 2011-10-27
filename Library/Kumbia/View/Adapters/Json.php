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
 * @license		New BSD License
 * @version 	$Id: Json.php 51 2009-05-12 03:45:18Z gutierrezandresfelipe $
 */

/**
 * JsonViewResponse
 *
 * Adaptador para generar salidas JSON
 *
 * @category 	Kumbia
 * @package 	View
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @access 		public
 */
class JsonViewResponse implements ViewResponseInterface {

	/**
	 * Prepara la salida JSON
	 *
	 * @param ControllerResponse $controllerResponse
	 */
	private function _prepareOutput($controllerResponse){
		//$controllerResponse->setHeader('X-Content-Type: text/json', true);
		//$controllerResponse->setHeader('Content-Type: text/json', true);
		$controllerResponse->setHeader('Pragma: no-cache', true);
		$controllerResponse->setHeader('Expires: 0', true);
	}

	/**
	 * Genera la presentacion en JSON
	 *
	 * @param ControllerResponse $controllerResponse
	 * @param mixed $valueReturned
	 */
	public function render($controllerResponse, $valueReturned){
		$this->_prepareOutput($controllerResponse);
		echo json_encode($valueReturned);
	}

	/**
	 * Administra las excepciones en JSON
	 *
	 * @param	ControllerResponse $controllerResponse
	 * @param	Exception $e
	 */
	public function renderException($controllerResponse, $e){
		$this->_prepareOutput($controllerResponse);
		$config = CoreConfig::readAppConfig();
		if(isset($config->application->debug)&&$config->application->debug){
			$traceback = array();
			foreach($e->getTrace() as $trace){
				if(isset($trace['file'])){
					$traceback[] = array(
						'file' => CoreException::getSafeFilePath($trace['file']),
						'line' => $trace['line']
					);
				}
			}
			$exception = array(
				'type' => get_class($e),
				'code' => $e->getCode(),
				'message' => $e->getMessage(),
				'file' => CoreException::getSafeFilePath($e->getFile()),
				'line' => $e->getLine(),
				'trace' => $traceback
			);
		} else {
			$exception = array(
				'type' => get_class($e),
				'code' => $e->getCode(),
				'message' => $e->getMessage()
			);
		}
		echo json_encode($exception);
	}

}
