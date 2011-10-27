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
 * @package 	ProcessContainer
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: process.php,v b9cc10ddf716 2011/10/19 23:38:16 andres $
 */

error_reporting(E_ALL | E_NOTICE | E_STRICT);
ini_alter('track_errors', true);

require 'public/index.config.php';
require KEF_ABS_PATH.'Library/Kumbia/Autoload.php';
require KEF_ABS_PATH.'Library/Kumbia/Object.php';
require KEF_ABS_PATH.'Library/Kumbia/Core/Core.php';
require KEF_ABS_PATH.'Library/Kumbia/Session/Session.php';
require KEF_ABS_PATH.'Library/Kumbia/Config/Config.php';
require KEF_ABS_PATH.'Library/Kumbia/Core/Config/CoreConfig.php';
require KEF_ABS_PATH.'Library/Kumbia/Core/Type/CoreType.php';
require KEF_ABS_PATH.'Library/Kumbia/Core/ClassPath/CoreClassPath.php';
require KEF_ABS_PATH.'Library/Kumbia/Router/Router.php';
require KEF_ABS_PATH.'Library/Kumbia/Plugin/Plugin.php';
require KEF_ABS_PATH.'Library/Kumbia/Registry/Memory/MemoryRegistry.php';
require KEF_ABS_PATH.'Library/Kumbia/ProcessContainer/ProcessContainer.php';
require KEF_ABS_PATH.'Library/Kumbia/Controller/Application/Process/ProcessController.php';

try {
	set_exception_handler(array('Core', 'manageExceptions'));
	set_error_handler(array('Core', 'manageErrors'));
	if(isset($_SERVER['argv'][1])){
		ProcessContainer::run($_SERVER['argv'][1]);
	}
}
catch(CoreException $e){
	try {
		Session::startSession();
		$exceptionHandler = Core::determineExceptionHandler();
		call_user_func_array($exceptionHandler, array($e, null));
	}
	catch(Exception $e){
		ob_start();
		Script::showConsoleException(get_class($e).': '.$e->getMessage()." ".$e->getFile()."(".$e->getLine().")");
		echo 'Backtrace:<br/>'."\n";
		foreach($e->getTrace() as $debug){
			echo $debug['file'].' ('.$debug['line'].") <br/>\n";
		}
		ob_end_clean();
	}
}
catch(Exception $e){
	echo 'Exception: '.$e->getMessage();
	foreach(debug_backtrace() as $debug){
		echo $debug['file'].' ('.$debug['line'].") <br>\n";
	}
}
