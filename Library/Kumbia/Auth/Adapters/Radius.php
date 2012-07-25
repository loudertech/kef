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
 * @version 	$Id: Radius.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * RadiusAuth
 *
 * Esta clase permite autenticar usuarios usando Radius Authentication (RFC 2865) y Radius Accounting (RFC 2866).
 *
 * @category	Kumbia
 * @package		Auth
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @link		http://www.faqs.org/rfcs/rfc2865
 */
class RadiusAuth implements AuthInterface {

	/**
	 * Nombre del archivo (si es utilizado)
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * Servidor de autenticación (si es utilizado)
	 *
	 * @var string
	 */
	private $server;

	/**
	 * Nombre de usuario para conectar al servidor de autenticacion (si es utilizado)
	 *
	 * @var string
	 */
	private $username;

	/**
	 * Password de usuario para conectar al servidor de autenticacion (si es utilizado)
	 *
	 * @var string
	 */
	private $password;

	/**
	 * Resource Radius
	 */
	private $resource;

	/**
	 * Puerto de Radius
	 */
	private $port = 1812;

	/**
	 * Secreto Radius
	 *
	 * @var string
	 */
	private $secret;

	/**
	 * Timeout para conectarse al servidor
	 *
	 * @var integer
	 */
	private $timeout = 3;

	/**
	 * Numero maximo de intentos
	 *
	 * @var integer
	 */
	private $max_retries = 3;

	/**
	 * Constructor del adaptador
	 *
	 * @param $auth
	 * @param $extra_args
	 */
	public function __construct($auth, $extraArgs){

		if(!extension_loaded("radius")){
			throw new AuthException("Debe cargar la extensión de php llamada radius");
		}

		foreach(array('server', 'secret') as $param){
			if(isset($extraArgs[$param])){
				$this->$param = $extraArgs[$param];
			} else {
				throw new AuthException("Debe especificar el parámetro '$param' en los parámetros");
			}
		}

		foreach(array('username', 'password') as $param){
			if(isset($extraArgs[$param])){
				$this->$param = $extraArgs[$param];
			}
		}
	}

	/**
	 * Obtiene los datos de identidad obtenidos al autenticar
	 *
	 */
	public function getIdentity(){
		if(!$this->resource){
			new AuthException("La conexion al servidor Radius es invalida");
		}
		$identity = array("username" => $this->username);
		return $identity;
	}

	/**
	 * Autentica un usuario usando el adaptador
	 *
	 * @return boolean
	 */
	public function authenticate(){

		$radius = radius_auth_open();
    	if(!$open_radiuse){
    		throw new AuthException("No se pudo crear el autenticador de Radius");
    	}

    	if(!radius_add_server($radius, $this->server, $this->port, $this->secret,
    			$this->timeout, $this->max_retries)) {
    		throw new AuthException(radius_strerror(0));
    	}

    	if(!radius_create_request($radius, RADIUS_ACCESS_REQUEST)){
    		throw new AuthException(radius_strerror(0));
    	}

    	if(!radius_put_string($radius, RADIUS_USER_NAME, $this->username)) {
    		throw new AuthException(radius_strerror(0));
    	}

    	if(!radius_put_string($radius, RADIUS_USER_PASSWORD, $this->password)) {
    		throw new AuthException(radius_strerror(0));
    	}

    	if(!radius_put_int($radius, RADIUS_AUTHENTICATE_ONLY, 1)) {
    		throw new AuthException(radius_strerror(0));
    	}

    	$this->resource = $radius;

    	if(radius_send_request()==RADIUS_ACCESS_ACCEPT){
    		return true;
    	} else {
    		return false;
    	}

	}

	/**
	 * Limpia el objeto cerrando la conexion si esta existe
	 *
	 */
	public function __destruct(){
		if($this->resource){
			radius_close($this->resource);
		}
	}

	/**
	 * Asigna los valores de los parametros al objeto autenticador
	 *
	 * @param array $extraArgs
	 */
	public function setParams($extraArgs){
		foreach(array('server', 'secret', 'username', 'principal',
			'password', 'port', 'max_retries') as $param){
			if(isset($extraArgs[$param])){
				$this->$param = $extraArgs[$param];
			}
		}
	}

}
