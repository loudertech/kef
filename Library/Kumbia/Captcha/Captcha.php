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
 * @package		Captcha
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * Captcha
 *
 * Se trata de una prueba desafío-respuesta utilizada en computación para
 * determinar cuándo el usuario es o no humano.
 *
 * This component lets to create captchas from diferents adapters.
 *
 * @category	Kumbia
 * @package		Captcha
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */
class Captcha extends Object {

	/**
	 * Objeto Adaptador de Captcha
	 *
	 * @var ImageCaptcha
	 */
	private $_adapter;

	/**
	 * Identificador único del captcha
	 *
	 * @var string
	 */
	private $_name;

	/**
	 * Constructor del Captcha
	 *
	 * @param string $adapter
	 * @param string $name
	 * @param array $options
	 */
	public function __construct($adapter='Image', $name='captcha1', $options=array()){
		$this->_name = $name;
		$this->_options = $options;
		$className = $adapter.'Captcha';
		if(class_exists($className, false)==false){
			$path = 'Library/Kumbia/Captcha/Adapters/'.ucfirst($adapter).'.php';
			if(file_exists($path)){
				require KEF_ABS_PATH.$path;
			} else {
				throw new CaptchaException('No existe el adaptador "'.$adapter.'"');
			}
		}
		$this->_name = $name;
		$this->_adapter = new $className($name, $options);
	}

	/**
	 * Genera la salida del captcha y almacena la llave de texto
	 *
	 */
	public function output(){
		$value = $this->_adapter->output();
		Session::set('CAP1_'.$this->_name, $value);
	}

	/**
	 * Comprueba si el valor es igual al del captcha
	 *
	 * @param 	string $value
	 * @param 	string $name
	 * @return 	boolean
	 */
	public static function isValid($value, $name='captcha1'){
		$captcha = Session::get('CAP1_'.$name);
		if($captcha==$value){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Helper que muestra la imagen del captcha
	 *
	 * @param 	string $action
	 * @return 	string
	 */
	public static function image($action){
		return Tag::image(array('src' => Utils::getKumbiaUrl($action)));
	}

}