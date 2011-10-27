<?php

abstract class ProcessContainer {

	private static $_outputHandler = array('ProcessContainer', 'handleNormalOutput');

	private static $_exceptionHandler = array('ProcessContainer', 'handleExceptions');

	public static function run($params){
		$_GET['_url'] = $params;
		Session::disableAutoStart(true);
		Controller::setDefaultOutputHandler(self::$_outputHandler);
		Controller::setDefaultExceptionHandler(self::$_exceptionHandler);
		Router::handleRouterParameters();
		Core::initApplication();
		Core::main();
	}

	public static function handleNormalOutput(){

	}

	public static function handleExceptions($e){
		Script::showConsoleException($e);
	}

	/**
	 * Devuelve un callback que administrará la forma en que se presente
	 * la vista del controlador
	 *
	 * @access public
	 */
	public function getViewHandler(){
		return self::$_outputHandler;
	}

	/**
	 * Devuelve un callback que administrará la forma en que se presente
	 * la vista del controlador
	 *
	 * @access 	public
	 * @return 	callback
	 */
	public function getViewExceptionHandler(){
		return self::$_exceptionHandler;
	}

}