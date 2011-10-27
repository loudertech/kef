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
 * @package		Builder
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: Builder.php,v 7a54c57f039b 2011/10/19 23:41:19 andres $
 */

/**
 * Builder
 *
 * Genera componentes para construir o generar codigo de las aplicaciones
 */
class Builder extends Object {

	/**
	 * Crea componentes del Builder
	 *
	 * @param	string $component
	 * @param	array $options
	 * @return	Object
	 */
	public static function factory($component, $options=array()){
		$className = $component.'BuilderComponent';
		if(!class_exists($className, false)){
			$path = KEF_ABS_PATH.'Library/Kumbia/Builder/Components/'.$component.'.php';
			if(file_exists($path)){
				require $path;
			} else {
				throw new BuilderException('No existe el componente de builder llamado "'.$component.'"');
			}
		}
		$componentObject = new $className($options);
		return $componentObject;
	}

}