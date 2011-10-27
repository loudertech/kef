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
 * @version 	$Id: Digest.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * DigestAuth
 *
 * Esta clase permite autenticar usuarios usando Digest Access Authentication.
 *
 * @category	Kumbia
 * @package		Auth
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @link		http://en.wikipedia.org/wiki/Digest_access_authentication
 */
class DigestAuth implements AuthInterface {

	/**
	 * Nombre del archivo (si es utilizado)
	 *
	 * @var string
	 */
	private $filename;

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
	 * Realm encontrado
	 *
	 * @var string
	 */
	private $realm;

	/**
	 * Callback al algoritmo usado para comprobar el password
	 *
	 * @var string
	 */
	private $algorithm = "md5";

	/**
	 * Recurso de acceso al archivo digest
	 *
	 * @var resource
	 */
	private $_resource;

	/**
	 * Constructor del adaptador
	 *
	 * @param string $auth
	 * @param array $extra_args
	 */
	public function __construct($auth, $extra_args){
		foreach(array('filename', 'username', 'password', 'realm',) as $param){
			if(isset($extra_args[$param])){
				$this->$param = $extra_args[$param];
			} else {
				throw new AuthException("Debe especificar el parámetro '$param' en los parámetros de autenticación");
			}
		}
		foreach(array('algorithm') as $param){
			if(isset($extra_args[$param])){
				$this->$param = $extra_args[$param];
			}
		}
	}

	/**
	 * Obtiene los datos de identidad obtenidos al autenticar
	 *
	 * @return array
	 */
	public function getIdentity(){
		$identity = array(
			'username' => $this->username,
			'realm' => $this->realm
		);
		return $identity;
	}

	/**
	 * Autentica un usuario usando el adaptador
	 *
	 * @return boolean
	 */
	public function authenticate(){
		if($this->_resource==null){
			$this->_resource = @fopen($this->filename, "r");
			if($this->_resource===false){
				throw new AuthException("No existe ó no se puede leer el archivo '".$this->filename."'");
			}
		}
		$existsUser = false;
		if(is_callable($this->algorithm)){
			$callback = $this->algorithm;
		} else {
			throw new AuthException('El algoritmo de comprobación del password es inválido');
		}
		if(isset($this->charset)){
			$charset = $this->charset;
		} else {
			$charset = 'UTF-8';
		}
		if(function_exists('mb_ereg_match')){
			$multibyte = true;
			mb_regex_encoding($charset);
		} else {
			$multibyte = false;
		}
		$i = 1;
		$password = $callback($this->password);
		while(!feof($this->_resource)){
			$line = fgets($this->_resource);
			$data = explode(":", $line);
			if($line){
				if(count($data)==3){
					if($multibyte==true){
						if(mb_ereg_match($data[0], $this->username)&&mb_ereg_match($data[1], $this->realm)){
							if($data[2]==$password){
								$existsUser = true;
								break;
							}
						}
					} else {
						if($data[0]==$this->username&&$data[1]==$this->realm){
							if($data[2]==$password){
								$existsUser = true;
								break;
							}
						}
					}
				} else {
					throw new AuthException("La linea $i del archivo digest no es valida");
				}
			}
			++$i;
		}
		return $existsUser;
	}


	/**
	 * Asigna los valores de los parametros al objeto autenticador
	 *
	 * @param array $extra_args
	 */
	public function setParams($extra_args){
		foreach(array('filename', 'username', 'password') as $param){
			if(isset($extra_args[$param])){
				$this->$param = $extra_args[$param];
			}
		}
	}

	/**
	 * Limpia el objeto cerrando la conexion si esta existe
	 *
	 */
	public function __destruct(){
		@fclose($this->resource);
	}

}

