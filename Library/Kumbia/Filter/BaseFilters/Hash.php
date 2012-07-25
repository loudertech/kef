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
 * @package		Filter
 * @subpackage	BaseFilters
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: Md5.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * Md5Filter
 *
 * Convierte una cadena a un string MD5
 *
 * @category	Kumbia
 * @package		Filter
 * @subpackage	BaseFilters
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 */
class HashFilter implements FilterInterface {

	/**
 	 * Ejecuta el filtro
 	 *
 	 * @param string $s
 	 * @return int
 	 */
	public function execute($s){
		return preg_replace('/[^a-fA-F0-9]/', '', $s);
	}

}
