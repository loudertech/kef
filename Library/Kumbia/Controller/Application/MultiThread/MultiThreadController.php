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
 * @package		Controller
 * @subpackage	ApplicationController
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */

/**
 * MultiThreadController
 *
 * Este controlador
 *
 * @category	Kumbia
 * @package		Controller
 * @subpackage	ApplicationController
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class MultiThreadController extends ApplicationController {

	/**
	 * Vector de metodos a sincronizar
	 *
	 * @var array
	 */
	private $_synchronizedActions = array();

	/**
	 * Indica si un proceso debe ser sincronizado
	 *
	 * @param string $docComment
	 */
	private function _isSynchronized($docComment){
		$docComment = str_replace(array('/*', '*/', '*'), '', $docComment);
		$params = array();
		foreach(explode('\n', $docComment) as $line){
			if(preg_match('/\@([a-z]+)/', $line, $regs)){
				if($regs[1]=='synchronized'){
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Desregistra las acciones sincronizadas
	 */
	public final function unregisterMethods(){
		unregister_tick_function(array($this, 'executeProcesses'));
	}

	/**
	 * Executa las aciones sincronizadas
	 */
	public final function executeProcesses(){
		if(Router::getController()==$this->getControllerName()){
			foreach($this->_synchronizedActions as $registeredAction){
				call_user_func($registeredAction);
			}
		}
	}

	/**
	 * Constructor del MultiThreadController
	 */
	public function __construct(){
		parent::__construct();
		$reflectionClass = new ReflectionClass(get_class($this));
		$numberMethods = 0;
		$this->_synchronizedActions = array();
		foreach($reflectionClass->getMethods() as $method){
			if($this->_isSynchronized($method->getDocComment())){
				$registerAction = array($this, $method->getName());
				$this->_synchronizedActions[] = $registerAction;
				++$numberMethods;
			}
		}
		if($numberMethods>0){
			$event = new CommonEvent('afterDispatchLoop', array($this, 'unregisterMethods'));
			CommonEventManager::attachEvent($event);
			register_tick_function(array($this, 'executeProcesses'));
		}
		declare(ticks=15);
	}

}
