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
 * @package 	View
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Zend.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * ZendProxyView
 *
 * Esta clase funciona como un proxy al componente de presentación Zend_View
 *
 * @category 	Kumbia
 * @package 	View
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class ZendProxyView {

	/**
	 * Datos a pasar a las vistas
	 *
	 * @var array
	 */
	private $_data = array();

	/**
	 * Opciones de Zend_View
	 *
	 * @var array
	 */
	private $_options = array();

	/**
	 * Constructor
	 *
	 * @param array $options
	 */
	public function __construct($options){
		if(!class_exists('Zend_View')){
			if(isset($options['zendPath'])){
				$pwd = getcwd();
				chdir($options['zendPath']);
				require 'Zend/View.php';
				chdir($pwd);
			}
		}
	}

	/**
	 * Asigna datos a la vista
	 *
	 * @param string $index
	 * @param mixed $value
	 */
	public function setData($index, $value){
		$this->_data[$index] = $value;
	}

	/**
	 * Genera una vista
	 *
	 * @param string $path
	 * @param string $viewFile
	 */
	public function renderView($path, $viewFile){
		$view = new Zend_View();
		$view->setScriptPath($path);
		$view->assign($this->_data);
		return $view->render($viewFile.'.phtml');
	}

}
