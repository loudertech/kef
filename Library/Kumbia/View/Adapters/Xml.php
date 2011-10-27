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
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Xml.php 103 2009-10-09 01:30:42Z gutierrezandresfelipe $
 */

/**
 * XmlViewResponse
 *
 * Adaptador para generar salidas XML
 *
 * @category 	Kumbia
 * @package 	View
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @access 		public
 */
class XmlViewResponse {

	/**
	 * Establece el valor a presentar
	 *
	 * @param ControllerResponse $controllerResponse
	 * @param mixed $valueReturned
	 */
	public function render($controllerResponse, $valueReturned){

		$controllerResponse->setHeader('X-Content-Type: text/xml', true);
		$controllerResponse->setHeader('Content-Type: text/xml', true);
		$controllerResponse->setHeader('Pragma: no-cache', true);
		$controllerResponse->setHeader('Expires: 0', true);

		if($valueReturned!==null){
			if(!class_exists('SimpleXMLResponse', false)){
				require KEF_ABS_PATH.'Library/Kumbia/Xml/Xml.php';
			}
			$xml = new SimpleXMLResponse();
			$xml->addData($valueReturned);
			$xml->outXMLResponse();
		}
	}

}
