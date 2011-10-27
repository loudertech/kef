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
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: Ini.php 88 2009-09-19 19:10:13Z gutierrezandresfelipe $
 */

/**
 * IniConfig
 *
 * Clase para la carga de archivos .ini
 *
 * Config soporta archivos INI estos son ampliamente usados por todo
 * tipo de software que además son el formato predeterminado del
 * framework. El adaptador procesa las secciones del archivo y
 * variables compuestas. Gracias a que se usan funciones nativas
 * del lenguaje su procesado es más rápido.
 *
 * @category	Kumbia
 * @package		Config
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */
class IniConfig {

	/**
	 * Permite leer un archivo .ini y lo devuelve como un objeto
	 *
	 * @access 	public
	 * @param 	Config $config
	 * @param 	string $file
	 * @return 	Config
	 * @static
	 */
	public function read(Config $config, $file){
		$iniSettings = @parse_ini_file(Core::getFilePath($file), true);
		if($iniSettings==false){
			throw new ConfigException("El archivo de configuración '$file' tiene errores '$php_errormsg'");
		} else {
			foreach($iniSettings as $conf => $value){
				$config->$conf = new stdClass();
				foreach($value as $cf => $val){
					$config->$conf->$cf = $val;
				}
			}
		}
		return $config;
	}

	/**
	 * Escribe un archivo .ini desde un objeto Config
	 *
	 * @param Config $config
	 * @param string $file
	 */
	public function write(Config $config, $file){
		$configContent = '';
		foreach($config as $sectionName => $section){
			$configContent.= '['.$sectionName.']'.PHP_EOL;
			foreach($section as $key => $value){
				if(is_bool($value)){
					if($value){
						$configContent.= $key.' = On'.PHP_EOL;
					} else {
						$configContent.= $key.' = Off'.PHP_EOL;
					}
				} else {
					if($value===''){
						$configContent.= $key.' = Off'.PHP_EOL;
					} else {
						if(strpos($value, ' ')===false){
							$configContent.= $key.' = '.$value.PHP_EOL;
						} else {
							$configContent.= $key.' = "'.$value.'"'.PHP_EOL;
						}
					}
				}
			}
			$configContent.= PHP_EOL;
		}
		return file_put_contents($file, $configContent);
	}

}