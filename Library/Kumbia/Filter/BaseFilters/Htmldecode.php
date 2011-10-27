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
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2007-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Htmldecode.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * HtmlDecodeFilter
 *
 * Convierte entidades HTML a su carácter UTF8 correspondiente
 *
 * @category	Kumbia
 * @package		Filter
 * @subpackage	BaseFilters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2007-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class HtmlDecodeFilter implements FilterInterface {

	/**
	 * Ejecuta el filtro
	 *
	 * @param string $value
	 * @return string
	 */
	public function execute($value){
		return html_entity_decode((string)$value, ENT_COMPAT, 'UTF-8');
	}

}
