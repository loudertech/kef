<?php

/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundledw
 * with this package in the file docs/LICENSE.txt.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ActiveRecordException.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * ActiveRecordException
 *
 * Clase para manejar errores ocurridos en operaciones de ActiveRecord
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class ActiveRecordException extends CoreException {

	/**
	 * Muestra un warning de ActiveRecord
	 *
	 * @param string $title
	 * @param string $message
	 * @param string $source
	 */
	public static function displayWarning($title, $message, $source){
		$controller_name = Router::getController();
		$action = Router::getAction();
		Flash::warning("
                <span style='font-size:16px;color:black'>KumbiaWarning: $title</span><br/>
                <div>$message<br>
                <span style='font-size:12px;color:black'>En el modelo <i>$source</i> al ejecutar <i>$controller_name/$action</i></span></div>", true);
		echo "<pre style='border:1px solid #969696;background:#FFFFE8;color:black;font-size:11px'>";
		echo debug_print_backtrace()."\n";
		echo "</pre>";
	}
}
