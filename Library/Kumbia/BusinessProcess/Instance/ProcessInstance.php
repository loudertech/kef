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
 * @package		BusinessProcess
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ProcessInstance.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

require KEF_ABS_PATH.'Library/Kumbia/BusinessProcess/Instance/ProcessInstanceException.php';

/**
 * ProcessInstance
 *
 * Carga una definicion de un Proceso de Negocio
 *
 * @category	Kumbia
 * @package		BusinessProcess
 * @subpackage	Definition
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 * @abstract
 */
class ProcessInstance {

	/**
	 * Definicion del Proceso
	 *
	 * @var ProcessDefinition
	 */
	private $_definition;

	/**
	 * Nodos Visitados
	 *
	 * @var array
	 */
	private $_nodes;

	/**
	 * Proceso de negocio
	 *
	 * @var BusinessProcess
	 */
	private $_businessProcess;

	/**
	 * State Activo
	 *
	 * @var string
	 */
	private $_activeState;

	/**
	 * Contructor de ProcessInstance
	 *
	 * @param ProcessDefinition $processDefinition
	 * @param BusinessProcess $businessProcess
	 */
	public function __construct(ProcessDefinition $processDefinition, BusinessProcess $businessProcess){
		$this->_definition = $processDefinition;
		$this->_businessProcess = $businessProcess;
	}

	/**
	 * Ejecuta el proceso inicial
	 *
	 */
	public function signal(){
		$startState = $this->_definition->getStartState();
		if($startState->getName()!=""){
			$this->_nodes[$startState->getName()] = $startState;
		}
		$this->_executeNodes($startState->getChildNodes());
	}

	/**
	 * Localiza un state
	 *
	 * @param string $stateName
	 */
	private function _lookupState($stateName){
		if(isset($this->_nodes[$stateName])){
			return $this->_nodes[$stateName];
		}
		$state = $this->_definition->getStateByName($stateName);
		if($state==false){
			throw new ProcessInstanceException("No se encontró el state '$stateName'");
		}
		if($state->getName()!=""){
			$this->_activeState = $state->getName();
			$this->_nodes[$state->getName()] = $state;
		}
		return $state;
	}

	/**
	 * Realiza el proceso de transicion
	 *
	 * @param ProcessNode $node
	 */
	private function _doTransition(ProcessNode $node){
		$destination = $node->getAttribute('to');
		if($destination->value==''){
			throw new ProcessInstanceException("Debe indicar el destino de la transición (state=".$this->_activeState.")");
		} else {
			$state = $this->_lookupState($destination->value);
			echo "Internal: Haciendo Transición a : ".$destination->value."\n";
			$this->_executeNodes($state->getChildNodes());
		}
	}

	/**
	 * Evalua una expresion
	 *
	 * @param string $expression
	 */
	private function _evaluateExpression($expression){
		while(preg_match('/\{\#([a-zA-Z0-9\_]+)\}/', $expression, $matches)){
			if($this->_businessProcess->isSetVariable($matches[1])==true){
				$var = $this->_businessProcess->getVariable($matches[1]);
				if(is_string($var)){
					$var = "'".addslashes($var)."'";
				}
				if($var===true){
					$var = "true";
				}
				if($var===false){
					$var = "false";
				}
				$expression = str_replace($matches[0], $var, $expression);
			} else {
				throw new ProcessInstanceException("No existe la variable '".$matches[1]."' (state=".$this->_activeState.")");
			}
		}
		echo "Internal: Expression ".$expression."\n";
		return eval("return $expression;");
	}

	/**
	 * Evalua las condiciones en una transicion
	 *
	 * @param array $nodeList
	 */
	private function _evaluateDecision(array $nodeList){
		foreach($nodeList as $node){
			if($node->getType()=='transition'){
				$condition = $node->getAttribute('condition');
				if($condition->value!=''){
					if($this->_evaluateExpression($condition->value)==false){
						echo "Internal: Evaluation ".$node->getAttribute('to')->value." FAILED\n";
						continue;
					} else {
						echo "Internal: Evaluation ".$node->getAttribute('to')->value." OK\n";
					}
				}
				$destination = $node->getAttribute('to');
				if($destination->value==''){
					throw new ProcessInstanceException("Debe indicar el destino de la transición (state=".$this->_activeState.")");
				} else {
					$this->_doTransition($node);
					break;
				}
			} else {
				throw new ProcessInstanceException("No se permite usar '".$node->getType()."' en este lugar (state=".$this->_activeState.")");
			}
		}
	}

	private function _doTask(ProcessNode $node){
		echo "Internal: Ejecutando Task : ".$node->getName()." (state=".$this->_activeState.")\n";
		$handler = $node->getAttribute('handler');
			if($handler->value==''){
				throw new ProcessInstanceException('Debe indicar el gestor de la tarea');
			} else {
			if(method_exists($this->_businessProcess, $handler->value."Handler")){
				$this->_businessProcess->{$handler->value."Handler"}();
				$this->_evaluateDecision($node->getChildNodes());
			} else {
				throw new ProcessInstanceException("No existe el handler de tarea '".$handler->value."Handler' en el proceso de negocio (state={$this->_activeState})");
			}
		}
	}

	/**
	 * Ejecuta un conjunto de nodos
	 *
	 * @param array $nodeList
	 */
	private function _executeNodes(array $nodeList){
		foreach($nodeList as $node){
			if($node->getType()=='task'){
				$this->_doTask($node);
				continue;
			}
			if($node->getType()=='transition'){
				$this->_doTransition($node);
				continue;
			}
			if($node->getType()=='decision'){
				echo "Internal: Evaluando decisión : ".$node->getName()."\n";
				$handler = $node->getAttribute('handler');
				if($handler->value==''){
					throw new ProcessInstanceException('Debe indicar el gestor de la decision');
				} else {
					if(method_exists($this->_businessProcess, $handler->value."Handler")){
						$this->_businessProcess->{$handler->value."Handler"}();
						$this->_evaluateDecision($node->getChildNodes());
					} else {
						throw new ProcessInstanceException("No existe el handler de decisión '".$handler->value."Handler' en el proceso de negocio");
					}
				}
				continue;
			}
			if($node->getType()=='task-node'){
				echo "Internal: Ejecutando bloque : ".$node->getName()."\n";
				$this->_executeNodes($node->getChildNodes());
				continue;
			}
		}
	}

}
