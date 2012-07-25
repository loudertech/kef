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
 * @package		Facility
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Facility.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * Facility
 *
 * Administra los facilities de la aplicacion y contextos de ejecucion
 *
 * @category	Kumbia
 * @package		Facility
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierezandresfelipe at gmail.com)
 * @license		New BSD License
 * @abstract
 */
abstract class Facility {

	/**
	 * Contexto del Sistema Operativo
	 *
	 */
	const SO = 0;

	/**
	 * Contexto del Hardware
	 *
	 */
	const HARDWARE = 1;

	/**
	 * Contexto de Red
	 *
	 */
	const NETWORK = 2;

	/**
	 * Framework Core
	 *
	 */
	const FRAMEWORK_CORE = 3;

	/**
	 * Framework Components
	 *
	 */
	const FRAMEWORK_COMPONENTS = 4;

	/**
	 * Security y Authentication
	 *
	 */
	const SECURITY = 5;

	/**
	 * Auditoria
	 *
	 */
	const AUDIT = 6;

	/**
	 * Issue de tipo Notificación
	 *
	 */
	const I_NOTICE = 0;

	/**
	 * Issue de tipo Advertencia
	 *
	 */
	const I_WARNING = 1;

	/**
	 * Issue de tipo error
	 *
	 */
	const I_ERROR = 2;

	/**
	 * Facility actual
	 *
	 * @var int
	 */
	private static $_facility = self::FRAMEWORK_CORE;

	/**
	 * Nivel de Usuario
	 *
	 */
	const USER_LEVEL = 7;

	/**
	 * Establece el Facility Actual
	 *
	 * @access public
	 * @param integer $facility
	 * @static
	 */
	public static function setFacility($facility){
		self::$_facility = $facility;
	}

	/**
	 * Obtener el Facility Actual
	 *
	 * @access public
	 * @return integer
	 * @static
	 */
	public static function getFacility(){
		return self::$_facility;
	}

	/**
	 * Envia un evento al facility actual
	 *
	 * @param string $message
	 * @param int $level
	 */
	public function issueEvent($message, $level=self::I_NOTICE){

	}
}
