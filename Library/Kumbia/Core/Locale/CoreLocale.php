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
 * @package		Core
 * @subpackage	CoreLocale
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: CoreLocale.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * CoreLocale
 *
 * Su objetivo es servir de puente entre los componentes de la aplicación
 * y los componentes de localización e internacionalización. Cuando se
 * genera una excepción este componente obtiene los mensajes localizados
 * apropiados al desarrollador.
 *
 * @category	Kumbia
 * @package		Core
 * @subpackage	CoreLocale
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class CoreLocale {

	/**
	 * Devuelve el mensaje localizado de la excepcion del framework generada
	 *
	 * @param string $errorCode
	 */
	static public function getErrorMessage($errorCode){
		$errorCode = (int) $errorCode;
		$config = CoreConfig::getInstanceConfig();
		if(!isset($config->core->locale)){
			throw new CoreLocaleException("No esta definida la localización en el archivo de configuración de la instancia", -99);
		}
		$language = substr($config->core->locale, 0, 2);
		$messagesFile = "languages/$language/LC_MESSAGES/errorMessages.php";
		if(Core::fileExists($messagesFile)==false){
			throw new CoreLocaleException("No existe el archivo de mensajes para el idioma '$language'", -98);
		}
		require $messagesFile;
		if(!isset($messages[$errorCode])){
			$message = "Error desconocido del framework";
		} else {
			$message = $messages[$errorCode];
		}
		if(func_num_args()>1){
			$extraParams = func_get_args();
			$extraParams[0] = $message;
			$message = call_user_func_array("sprintf", $extraParams);
		}
		return $message;
	}

}
