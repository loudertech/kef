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
 * @package		Controller
 * @subpackage	WebService
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: WebServiceController.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * JsonController
 *
 * Implementa un controlador estilo JSON-RPC
 *
 * @category	Kumbia
 * @package		Controller
 * @subpackage	Mutable
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 */
class JsonController extends ApplicationController {

	/**
	 * El inicializador del controlador, lo hace persistente si se accede por JSONP
	 *
	 */
	public function initialize(){
		$this->setPersistance(true);
		$this->setResponse('json');
	}

}