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
 * @package		Auth
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Interface.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * AuthInterface
 *
 * Interface que deben implementar todos los adaptadores de Auth
 *
 * @category	Kumbia
 * @package		Auth
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
interface AuthInterface {

	/**
	 * Constructor del adaptador
	 *
	 */
	public function __construct($auth, $extraArgs);

	/**
	 * Obtiene los datos de identidad obtenidos al autenticar
	 *
	 */
	public function getIdentity();

	/**
	 * Autentica un usuario usando el adaptador
	 *
	 * @return boolean
	 */
	public function authenticate();

}
