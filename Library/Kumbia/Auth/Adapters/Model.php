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
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Model.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * ModelAuth
 *
 * Esta clase permite autenticar usuarios usando una entidad de la base de datos
 *
 * @category	Kumbia
 * @package		Auth
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class ModelAuth
#if[compile-time]
	implements AuthInterface
#endif
	{

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
	 * Atributos del modelo a comparar para autenticacion valida
	 */
	private $_compareAttributes = array();

	/**
	 * Identidad encontrara
	 */
	private $identity = array();

	/**
	 * Constructor del adaptador
	 *
	 * @param $auth
	 * @param $extra_args
	 */
	public function __construct($auth, $extraArgs){
		foreach(array('class') as $param){
			if(isset($extraArgs[$param])){
				$this->$param = $extraArgs[$param];
			} else {
				throw new AuthException("Debe especificar el parámetro '$param' en los parámetros");
			}
		}
		if(EntityManager::isEntity($extraArgs['class'])==false){
			throw new AuthException("No existe el modelo '".$extraArgs['class']."' para realizar la autenticación");
		}
		unset($extraArgs[0]);
		unset($extraArgs['class']);
		$this->_compareAttributes = $extraArgs;
	}

	/**
	 * Obtiene los datos de identidad obtenidos al autenticar
	 *
	 * @return array
	 */
	public function getIdentity(){
		return $this->identity;
	}

	/**
	 * Autentica un usuario usando el adaptador
	 *
	 * @return boolean
	 */
	public function authenticate(){
		$whereCondition = array();
		foreach($this->_compareAttributes as $field => $value){
			$value = addslashes($value);
			$whereCondition[] = "$field = '$value'";
		}
		if(count($whereCondition)==0){
			throw new AuthException("No se ha especificado los campos del modelo a comparar");
		}
		$model = EntityManager::getEntityInstance($this->class);
		$result = $model->count(join(" AND ", $whereCondition));
		if($result){
			$model = $model->findFirst(join(" AND ", $whereCondition));
			$identity = array();
			foreach($model->getAttributes() as $field){
				/**
				 * Trata de no incluir en la identidad el password del usuario
				 */
				if(!in_array($field, array('password', 'clave', 'contrasena', 'passwd', 'pass'))){
					$identity[$field] = trim($model->readAttribute($field));
				}
			}
			$this->identity = $identity;
		}
		return $result;

	}

	/**
	 * Asigna los valores de los parametros al objeto autenticador
	 *
	 * @param array $extraArgs
	 */
	public function setParams($extraArgs){
		foreach(array('server', 'secret', 'principal', 'password', 'port', 'max_retries') as $param){
			if(isset($extraArgs[$param])){
				$this->$param = $extraArgs[$param];
			}
		}
	}

}

