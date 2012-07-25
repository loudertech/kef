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
 * @package		Auth
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Ldap.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * LdapAuth
 *
 * Esta clase permite autenticar usuarios mediante servidores LDAP
 *
 * @category	Kumbia
 * @package		Auth
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @link 		http://en.wikipedia.org/wiki/LDAP
 */
class LdapAuth implements AuthInterface {

	/**
	 * Recurso LDAP
	 *
	 * @var resource
	 */
	private $_ldapResource;

	/**
	 * Constructor del adaptador
	 *
	 * @param string $auth
	 * @param array $extraArgs
	 */
	public function __construct($auth, $extraArgs){
		if(extension_loaded("ldap")==false){
			throw new AuthException("Debe cargar la extensión de php llamada 'ldap'");
		}
		$this->setParams($extraArgs);
		if(!isset($this->server)){
			throw new AuthException("No ha establecido el servidor LDAP");
		}
		if(!isset($this->port)){
			$this->port = 389;
		}
		$this->_ldapResource = ldap_connect($this->server, $this->port);
		if($this->_ldapResource==false){
			throw new AuthException("No se pudo conectar al servidor LDAP");
		}
	}

	/**
	 * Obtiene los datos de identidad obtenidos al autenticar
	 *
	 * @access public
	 * @return boolean
	 */
	public function getIdentity(){
		if($this->_ldapResource==false){
			throw new AuthException("Recurso LDAP no disponible");
		}
		if(!isset($this->baseDN)||!$this->baseDN){
			throw new AuthException("No ha establecido el par&aacute;metro baseDN para obtener los datos de identidad");
		}

		$dnParts = $this->_getDNAttributes($this->username);
		if(isset($dnParts['uid'])){
			if(isset($this->identityAttributes)){
				$identityAttributes = explode(",", $this->identityAttributes);
			} else {
				$identityAttributes = array('cn', 'uid');
			}
			$ldapResult = ldap_search($this->_ldapResource, $this->baseDN, "uid=".$dnParts['uid'], $identityAttributes);
		}
		$numberEntries = ldap_count_entries($this->_ldapResource, $ldapResult);
		$identity = array();
		if($numberEntries>0){
			$entries = ldap_get_entries($this->_ldapResource, $ldapResult);
			if($numberEntries>1){
				throw new AuthException("Múltiples identidades para '".$this->userName."'");
			} else {
				foreach($identityAttributes as $attribute){
					$identity[$attribute] = $entries[0][$attribute][0];
					if($attribute=='uid'){
						$attribute = 'username';
					} else {
						if($attribute=='cn'){
							$attribute = 'canonicalName';
						}
					}
				}
			}
		} else {
			throw new AuthException("No se pudo obtener los datos de identidad de '".$this->userName."'");
		}
		return $identity;
	}

	/**
	 * Devuelve un array con cada parte del DN
	 *
	 * @param string $distinguishedName
	 * @return array
	 */
	private function _getDNAttributes($distinguishedName){
		$dnParts = explode(',', $distinguishedName);
		$returnedDNParts = array();
		foreach($dnParts as $dnPart){
			$entry = explode('=', $dnPart);
			$returnedDNParts[$entry[0]] = $entry[1];
		}
		return $returnedDNParts;
	}

	/**
	 * Devuelve el nombre canonizado
	 *
	 * @access private
	 * @param string $canonicalName
	 */
	private function _getCanonicalName($canonicalName){
		if(!isset($this->accountCanonicalForm)||$this->accountCanonicalForm==2){
			return $canonicalName;
		}
		if($this->accountCanonicalForm==3){
			$canonicalName = str_replace("\\\\", "", $canonicalName);
			$cnParts = explode("\\", $canonicalName);
			$dc = array();
			foreach(explode('.', $cn[0]) as $domainPart){
				$dc[] = 'DC='.$domainPart;
			}
			return 'uid='.$cnParts[1].','.join(',', $dc);
		}
		if($this->accountCanonicalForm==4){
			$cnParts = explode('@', $canonicalName);
			$dc = array();
			foreach(explode('.', $cn[0]) as $domainPart){
				$dc[] = 'DC='.$domainPart;
			}
			return 'uid='.$cnParts[1].','.join(',', $dc);
		}
		throw new AuthException("Uso de accountCanonicalForm indefinido '".$this->accountCanonicalForm."'");
	}

	/**
	 * Autentica un usuario usando el adaptador
	 *
	 * @access public
	 * @return boolean
	 */
	public function authenticate(){
		if($this->_ldapResource==false){
			throw new AuthException("Recurso LDAP no disponible");
		}
		$canonicalName = $this->_getCanonicalName($this->username);
		$canonicalPassword = $this->password;
		$result = ldap_bind($this->_ldapResource, $canonicalName, $canonicalPassword);
		return $result;
	}


	/**
	 * Asigna los valores de los parametros al objeto autenticador
	 *
	 * @param array $extraArgs
	 */
	public function setParams($extraArgs){
		$arguments = array(
			'server',
			'accountDomainName',
			'accountCanonicalForm',
			'baseDN',
			'username',
			'password',
			'port'
		);
		foreach($arguments as $param){
			if(isset($extraArgs[$param])){
				$this->$param = $extraArgs[$param];
			}
		}
	}

	/**
	 * Limpia el objeto cerrando la conexión si esta existe
	 *
	 * @access public
	 */
	public function __destruct(){
		if($this->_ldapResource!=false){
			ldap_unbind($this->_ldapResource);
		}
	}

}
