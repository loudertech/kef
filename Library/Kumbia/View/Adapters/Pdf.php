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
 * @version 	$Id: Pdf.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * PdfViewAdapter
 *
 * Adaptador para generar salidas PDF
 *
 * @category 	Kumbia
 * @package 	View
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @access 		public
 */
class PdfViewResponse {

	/**
	 * Genera la presentacion
	 *
	 * @param ControllerResponse $controllerResponse
	 * @param mixed $valueReturned
	 */
	public function render($controllerResponse, $valueReturned){
		$controllerResponse->setHeader('Content-Type: application/pdf', true);
	}

}
