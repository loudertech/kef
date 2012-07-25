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
 * @package		Config
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: Xml.php 88 2009-09-19 19:10:13Z gutierrezandresfelipe $
 */

/**
 * XmlConfig
 *
 * El adaptador de configuración XML permite procesar archivos que usen
 * el lenguaje de marcas XML. Los archivos XML son ampliamente reusables
 * por otras tecnologías y lenguajes. El procesado de los archivos se
 * hace usando funciones nativas del lenguaje PHP por lo que es muy
 * rápida.
 *
 * @category	Kumbia
 * @package		Config
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */
class XmlConfig {

	/**
	 * Constructor de la Clase XmlConfig
	 *
	 * @access 	public
	 * @param 	Config $config
	 * @param 	string $file
	 * @return 	Config
	 * @static
	 */
	public function read(Config $config, $file){
		$dom = new DOMDocument();

	}

}