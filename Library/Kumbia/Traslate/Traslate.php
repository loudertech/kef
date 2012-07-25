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
 * @package 	Traslate
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Traslate.php 97 2009-09-30 19:28:13Z gutierrezandresfelipe $
 */

/**
 * Traslate
 *
 * El componente Traslate permite la creación de aplicaciones multi-idioma usando
 * diferentes adaptadores para obtener las listas de traducción.
 *
 * @category	Kumbia
 * @package		Traslate
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class Traslate extends Object implements ArrayAccess {

	/**
	 * Objeto Adaptador
	 *
	 * @var mixed
	 */
	private $_adapter;

	/**
	 * Contructor de la clase Traslate
	 *
	 * @param	string $adapter
	 * @param	mixed $data
	 */
	public function __construct($adapter, $data){
		$adapterClass = $adapter.'Traslate';
		if(interface_exists('TraslateInterface', false)==false){
			require KEF_ABS_PATH.'Library/Kumbia/Traslate/Interface.php';
		}
		if(class_exists($adapterClass, false)==false){
			$file = 'Library/Kumbia/Traslate/Adapters/'.$adapter.'.php';
			if(Core::fileExists($file)==true){
				require KEF_ABS_PATH.$file;
			} else {
				throw new TraslateException('No existe el adaptador "'.$adapter.'"');
			}
		}
		$this->_adapter = new $adapterClass($data);
	}

	/**
	 * Traduce una cadena usando el adaptador interno
	 *
	 * @param	string $traslateKey
	 * @return	string
	 */
	public function _($traslateKey){
		return $this->_adapter->query($traslateKey);
	}

	/**
	 * Establece el valor de una traducción
	 *
	 * @param 	string $offset
	 * @param 	string $value
	 */
	public function offsetSet($offset, $value){
        throw new TraslateException('El objeto de traducción es de solo lectura');
    }

    /**
     * Indica si existe un valor en el diccionario de traducción
     *
     * @param	string $traslateKey
     * @return	boolean
     */
    public function offsetExists($traslateKey){
        return $this->_adapter->exists($traslateKey);
    }

    /**
     * Elimina un indice del diccionario
     *
     * @param	string $offset
     */
    public function offsetUnset($offset){
        throw new TraslateException('El objeto de traducción es de solo lectura');
    }

    /**
	 * Traduce una cadena usando el adaptador interno
	 *
	 * @param	string $traslateKey
	 * @return	string
	 */
    public function offsetGet($traslateKey){
		return $this->_adapter->query($traslateKey);
    }

}
