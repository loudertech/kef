<?php

/**
 * Kumbia PHP Framework
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
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: Time.php 88 2009-09-19 19:10:13Z gutierrezandresfelipe $
 */

/**
 * TimezoneFilter
 *
 * Filtra una cadena para que contenga una zona horaria válida
 *
 * @category	Kumbia
 * @package 	Filter
 * @subpackage 	BaseFilters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: Time.php 88 2009-09-19 19:10:13Z gutierrezandresfelipe $
 */
class TimezoneFilter implements FilterInterface {

	/**
 	 * Ejecuta el filtro
 	 *
 	 * @param string $s
 	 * @return string
 	 */
	public function execute($s){
		$patron = '/[a-z_]+\/[a-z_]+/';
		if(preg_match($patron, (string) $s, $regs)){
			return $regs[0];
		} else {
			return "";
		}
	}

}
