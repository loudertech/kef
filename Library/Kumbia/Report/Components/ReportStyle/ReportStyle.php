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
 * @package 	Report
 * @subpackage 	Components
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: ReportStyle.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * ReportStyle
 *
 * Componente para crear estilos para Componentes
 *
 * @category 	Kumbia
 * @package 	Report
 * @subpackage 	Components
 * @copyright 	Copyright (c) 2005-2010 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 */
class ReportStyle {

	/**
	 * Propiedades del componente
	 *
	 * @var array
	 */
	private $_styles = array();

	/**
	 * Establece los parámetros del ReportStyle
	 *
	 * @param array $styles
	 */
	public function __construct($styles=array()){
		$this->_styles = $styles;
	}

	/**
	 * Establece los parámetros del ReportStyle
	 *
	 * @param array $styles
	 */
	public function setParameters($styles=array()){
		$this->_styles = $styles;
	}

	/**
	 * Devuelve los estilos
	 *
	 * @return array
	 */
	public function getStyles(){
		return $this->_styles;
	}

}
