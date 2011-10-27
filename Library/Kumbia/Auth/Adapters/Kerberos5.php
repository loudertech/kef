<?php

/**
 * Kumbia PHP Framework
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
 * @version 	$Id: Kerberos5.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * Kerberos5Auth
 *
 * Esta clase permite autenticar usuarios usando servidores Kerberos V.
 *
 * @category	Kumbia
 * @package		Auth
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @link		http://web.mit.edu/kerberos/www/krb5-1.2/krb5-1.2.8/doc/admin_toc.html.
 */
class Kerberos5Auth implements AuthInterface {

	/**
	 * Nombre del archivo (si es utilizado)
	 *
	 * @var string
	 */
	private $_filename;

	/**
	 * Servidor de autenticación (si es utilizado)
	 *
	 * @var string
	 */
	private $_server;

	/**
	 * Nombre de usuario para conectar al servidor de autenticacion (si es utilizado)
	 *
	 * @var string
	 */
	private $_username;

	/**
	 * Password de usuario para conectar al servidor de autenticacion (si es utilizado)
	 *
	 * @var string
	 */
	private $_password;

	/**
	 * Resource Kerberos5
	 */
	private $_resource;

	/**
	 * Constructor del adaptador
	 *
	 * @param string $auth
	 * @param array $extraArgs
	 */
	public function __construct($auth, $extraArgs){

		if(!extension_loaded("kadm5")){
			throw new AuthException("Debe cargar la extensión de php llamada kadm5");
		}

		foreach(array('server', 'realm', 'principal', 'password') as $param){
			if(isset($extraArgs[$param])){
				$this->$param = $extraArgs[$param];
			} else {
				throw new AuthException("Debe especificar el parámetro '$param' en los par&aacute;metros");
			}
		}
	}

	/**
	 * Obtiene los datos de identidad obtenidos al autenticar
	 *
	 */
	public function getIdentity(){
		if(!$this->_resource){
			new AuthException("La conexion al servidor kerberos5 es invalida");
		}
		$identity = array("username" => $this->_principal, "realm" => $this->_realm);
		return $identity;
	}

	/**
	 * Autentica un usuario usando el adaptador
	 *
	 * @return boolean
	 */
	public function authenticate(){
		$this->_resource = kadm5_init_with_password($this->_server, $this->_realm, $this->_principal, $this->_password);
		if($this->_resource===false){
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Obtiene los prinicipals del usuario autenticado
	 *
	 */
	public function getPrincipals(){
		if(!$this->_resource){
			new AuthException("La conexion al servidor kerberos5 es invalida");
		}
		return kadm5_get_principals($this->_resource);
	}

	/**
	 * Obtiene los policies del usuario autenticado
	 *
	 */
	public function getPolicies(){
		if(!$this->_resource){
			new AuthException("La conexion al servidor kerberos5 es invalida");
		}
		return kadm5_get_policies($this->_resource);
	}

	/**
	 * Limpia el objeto cerrando la conexion si esta existe
	 *
	 */
	public function __destruct(){
		if($this->_resource){
			kadm5_destroy($this->_resource);
		}
	}

	/**
	 * Asigna los valores de los parametros al objeto autenticador
	 *
	 * @param array $extraArgs
	 */
	public function setParams($extraArgs){
		foreach(array('server', 'principal', 'realm', 'password') as $param){
			if(isset($extra_args[$param])){
				$this->${" ".$param} = $extraArgs[$param];
			}
		}
	}

}
