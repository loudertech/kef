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
 * @package 	Security
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version 	$Id: SecurityFirewall.php 88 2009-09-19 19:10:13Z gutierrezandresfelipe $
 */

/**
 * SecurityFirewall
 *
 * Una característica importante de este componente es un Firewall
 * que permite definir reglas de acceso estilo firewall a una aplicación.
 *
 * @category 	Kumbia
 * @package 	Security
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */
class SecurityFirewall extends Object {

	/**
	 * Objeto DOMDocument
	 *
	 * @var DOMDocument
	 */
	private $_dom;

	/**
	 * Reglas del firewall
	 *
	 * @var array
	 */
	private $_rules = array();

	/**
	 * buffer de direcciones IPs para hosts
	 *
	 * @var array
	 */
	private $_bufferHosts = array();

	/**
	 * Nombres de hosts consultados
	 *
	 * @var array
	 */
	private $_queryHosts = array();

	/**
	 * Constructor de SecurityFirewall
	 *
	 */
	public function __construct(){

	}

	/**
	 * Agrega una direccion IP a un nombre de maquina
	 *
	 * @param string $hostname
	 * @param string $ipAddress
	 */
	public function addHostTraslatation($hostname, $ipAddress){
		if(!isset($this->_bufferHosts[$hostname])){
			$this->_bufferHosts[$hostname] = array();
		}
		$this->_bufferHosts[$hostname][] = $ipAddress;
	}

	/**
	 * Carga las reglas desde un archivo de configuración
	 *
	 * @param 	string $rulesFile
	 * @return 	boolean
	 */
	public function loadRules($rulesFile){
		$this->_dom = new DOMDocument();
		if($this->_dom->load($rulesFile)==false){
			throw new SecurityFirewallException('Las reglas del firewall son inválidas');
		} else {
			$rules = array();
			foreach($this->_dom->getElementsByTagName('rule') as $element){
				$rule = array();
				foreach($element->childNodes as $property){
					if($property->localName!=''){
						$rule[$property->localName] = $property->nodeValue;
					}
				}
				$rules[] = $rule;
			}
			$this->_rules = $rules;
		}
		#print_r($this->_rules);
		return true;
	}

	/**
	 * Verifica si dadas las condiciones del entorno web se le permite
	 * el acceso a una petición
	 *
	 * @return 	boolean
	 */
	public function checkRules(){
		$eval = true;
		foreach($this->_rules as $rule){
			if(isset($rule['source'])){
				$eval = ($eval && $this->_checkSource($rule['source']));
			}
			if($eval&&isset($rule['controller'])){
				$eval = ($eval && $this->_checkController($rule['controller']));
			}
			if($eval&&isset($rule['action'])){
				$eval = ($eval && $this->_checkAction($rule['action']));
			}
			if($eval==true){
				if(isset($rule['target'])){
					switch($rule['target']){
						case 'accept':
							return true;
						break;
						case 'reject':
							return false;
						break;
						default:
							throw new SecurityFirewallException('La regla no tiene un "target" definido');
					}
				} else {
					throw new SecurityFirewallException('La regla no tiene un "target" definido');
				}
			}
		}
		return true;
	}

	/**
	 * Valida si un origen es una IPv4
	 *
	 * @param string $source
	 * @return boolean
	 */
	private function _isIPV4($source){
		$pattern = '/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/';
		if(preg_match($pattern, (string) $source, $regs)){
			if($regs[0]!=$source){
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Obtiene la direccion IP del cliente
	 *
	 * @return string
	 */
	private function _getClientAddress(){
		if(isset($_SERVER)){
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
				$clientAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				if(isset($_SERVER['HTTP_CLIENT_IP'])){
					$clientAddress = $_SERVER['HTTP_CLIENT_IP'];
				} else {
					$clientAddress = $_SERVER['REMOTE_ADDR'];
				}
			}
		} else {
			if(getenv('HTTP_X_FORWARDED_FOR')){
				$clientAddress = getenv('HTTP_X_FORWARDED_FOR');
			} else {
				if(getenv('HTTP_CLIENT_IP')){
					$clientAddress = getenv('HTTP_CLIENT_IP');
				} else {
					$clientAddress = getenv('REMOTE_ADDR');
				}
			}
		}
		return $clientAddress;
	}

	/**
	 * Consulta si el host coincide con la regla activa
	 *
	 * @param string $source
	 * @return boolean
	 */
	private function _checkSource($source){
		$clientAddress = $this->_getClientAddress();
		if($this->_isIPv4($source)==false){
			if(!isset($this->_queryHosts[$source])){
				foreach(gethostbynamel($source) as $address){
					$this->_bufferHosts[$source][] = $address;
				}
				$this->_queryHosts[$source] = true;
				return in_array($clientAddress, $this->_bufferHosts[$source]);
			} else {
				return in_array($clientAddress, $this->_bufferHosts[$source]);
			}
		} else {
			return $clientAddress==$source;
		}
	}

	/**
	 * Consulta si el controlador de la regla es el actual
	 *
	 * @param string $controller
	 */
	private function _checkController($controller){
		if($controller!='*'){
			return $controller==Router::getController();
		} else {
			return true;
		}
	}

	/**
	 * Consulta si el controlador de la regla es el actual
	 *
	 * @param string $controller
	 */
	private function _checkAction($action){
		if($action!='*'){
			return $action==Router::getAction();
		} else {
			return true;
		}
	}

}