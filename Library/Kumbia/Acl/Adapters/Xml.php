<?php

/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category	Kumbia
 * @package		Acl
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license		New BSD License
 * @version 	$Id: Xml.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * AclXML
 *
 * Permite administrar listas ACL usando archivos XML
 *
 * @category	Kumbia
 * @package		Acl
 * @subpackage	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license		New BSD License
 */
class AclXml implements AclAdapter {

	/**
	 * Nombres de Roles en la lista ACL
	 *
	 * @var array
	 */
	private $_rolesNames = array();

	/**
	 * Objetos Roles en lista ACL
	 *
	 * @var array
	 */
	private $_roles = array();

	/**
	 * Objetos Resources en la lista ACL
	 *
	 * @var array
	 */
	private $_resources = array();

	/**
	 * Permisos de la Lista de Acceso
	 *
	 * @var array
	 */
	public $_access = array();

	/**
	 * Herencia entre Roles
	 *
	 * @var array
	 */
	private $_roleInherits = array();

	/**
	 * Array de Nombres de Recursos
	 *
	 * @var array
	 */
	private $_resourcesNames = array('*');

	/**
	 * Lista ACL de permisos
	 *
	 * @var array
	 */
	private $_accessList = array('*' => array('*'));

	/**
	 * Constructor de la clase AclXML
	 *
	 * @param array $params
	 */
	public function __construct($params){
		if(isset($params['descriptor'])){
			$params['filePath'] = $params['descriptor'];
		}
		if(isset($params['filePath'])==false){
			//Debe definir el archivo con la lista de políticas seguridad XML
			$message = CoreLocale::getErrorMessage(-31);
			throw new AclException($message, -31);
		}
		$filePath = Core::getKumbiaNDI($params['filePath']);
		if(Core::fileExists($filePath)==false){
			//El archivo con la lista AclXML no existe ($filePath)
			$message = CoreLocale::getErrorMessage(-32, $filePath);
			throw new AclException($message, -32);
		}
		$xml = simplexml_load_file($filePath);
		$roles = $xml->xpath('/security/roles-collection/role');
		$i = 1;
		foreach($roles as $role){
			$this->addRole(new AclRole((string)$role->name, (string)$role->description));
		}
		$resources = $xml->xpath('/security/resources-collection/resource');
		foreach($resources as $resource){
			$this->addResource(new AclResource((string)$resource->name, (string)$resource->description));
		}
		$accessConstraints = $xml->xpath('/security/access-constraint');
		$n = 1;
		foreach($accessConstraints as $accessConstraint){
			if(isset($accessConstraint->{"role-name"})){
				$roleName = (string)$accessConstraint->{"role-name"};
			} else {
				//El constraint de la lista de acceso No. $n no ha definido el rol a aplicar
				$message = CoreLocale::getErrorMessage(-33, $n);
				throw new AclException($message, -33);
			}
			if(isset($accessConstraint->{"resource-name"})){
				$resourceName = (string) $accessConstraint->{"resource-name"};
			} else {
				//El constraint de la lista de acceso No. $n no ha definido el recurso a aplicar
				$message = CoreLocale::getErrorMessage(-34, $n);
				throw new AclException($message, -34);
			}
			if(isset($accessConstraint->{"action-name"})){
				$actionName = (string) $accessConstraint->{"action-name"};
			} else {
				//El constraint de la lista de acceso No. $n no ha definido la acción a aplicar
				$message = CoreLocale::getErrorMessage(-35, $n);
				throw new AclException($message, -35);
			}
			if(isset($accessConstraint->{"rule-type"})){
				$ruleType = (string) $accessConstraint->{"rule-type"};
			} else {
				//El constraint de la lista de acceso No. $n no ha definido el tipo de regla a aplicar
				$message = CoreLocale::getErrorMessage(-36, $n);
				throw new AclException($message, -36);
			}
			if(!in_array($ruleType, array('allow', 'deny'))){
				//El tipo de regla del constraint de la lista de acceso No. $n es invalido
				$message = CoreLocale::getErrorMessage(-37, $n);
				throw new AclException($message, -37);
			}
			$this->addResourceAccess($resourceName, $actionName);
			if($ruleType=='allow'){
				$this->allow($roleName, $resourceName, $actionName);
			} else {
				$this->deny($roleName, $resourceName, $actionName);
			}
			++$n;
		}
	}

	/**
	 * Agrega un Rol a la Lista ACL
	 *
	 * $roleObject = Objeto de la clase AclRole para agregar a la lista
	 * $accessInherits = Nombre del Role del cual hereda permisos � array del grupo
	 * de perfiles del cual hereda permisos
	 *
	 * Ej:
	 * <code>$acl->addRole(new Acl_Role('administrador'), 'consultor');</code>
	 *
	 * @param string $roleObject
	 * @param array $accessInherits
	 * @return boolean
	 */
	public function addRole(AclRole $roleObject, $accessInherits=''){
		if(in_array($roleObject->getName(), $this->_rolesNames)){
			return false;
		}
		$this->_roles[] = $roleObject;
		$this->_rolesNames[] = $roleObject->getName();
		$this->_access[$roleObject->getName()]['*']['*'] = 'A';
		if($accessInherits){
			$this->addInherit($roleObject->getName(), $accessInherits);
		}
	}

	/**
	 * Hace que un rol herede los accesos de otro rol
	 *
	 * @param string $role
	 * @param string $roleToInherit
	 */
	public function addInherit($role, $roleToInherit){
		if(!in_array($role, $this->_rolesNames)){
			return false;
		}
		if($roleToInherit!=''){
			if(is_array($roleToInherit)){
				foreach($roleToInherit as $rol_in){
					if($rol_in==$role){
						return false;
					}
					if(!in_array($rol_in, $this->_rolesNames)){
						//"El Rol '{$rol_in}' no existe en la lista"
						$message = CoreLocale::getErrorMessage(-16, $rol_in);
						throw new AclException($message, -16);
					}
					$this->_roleInherits[$role][] = $role_in;
				}
				$this->_rebuildAccessList();
			} else {
				if($roleToInherit==$role){
					return false;
				}
				if(!in_array($roleToInherit, $this->_rolesNames)){
					//El Rol '{$roleToInherit}' no existe en la lista
					$message = CoreLocale::getErrorMessage(-16, $rol_in);
					throw new AclException($message, -16);
				}
				$this->_roleInherits[$role][] = $roleToInherit;
				$this->_rebuildAccessList();
			}
		} else {
			//Debe especificar un rol a heredar en Acl::addInherit
			$message = CoreLocale::getErrorMessage(-17, $rol_in);
			throw new AclException($message, -17);
			return false;
		}
	}

	/**
	 *
	 * Verifica si un rol existe en la lista o no
	 *
	 * @param string $roleName
	 * @return boolean
	 */
	public function isRole($roleName){
		return in_array($roleName, $this->_rolesNames);
	}

	/**
	 *
	 * Verifica si un resource existe en la lista o no
	 *
	 * @param string $resourceName
	 * @return boolean
	 */
	public function isResource($resourceName){
		return in_array($resourceName, $this->_resourcesNames);
	}

	/**
	 * Agrega un  a la Lista ACL
	 *
	 * Resource_name puede ser el nombre de un objeto concreo, por ejemplo
	 * consulta, buscar, insertar, valida etc o una lista de ellos
	 *
	 * Ej:
	 * <code>
	 * //Agregar un resource a la lista:
	 * $acl->addResource(new AclResource('clientes'), 'consulta');
	 *
	 * //Agregar Varios resources a la lista:
	 * $acl->addResource(new AclResource('clientes'), 'consulta', 'buscar', 'insertar');
	 * </code>
	 *
	 * @param AclResource $resource
	 * @return boolean
	 */
	public function addResource(AclResource $resource){
		if(!in_array($resource->getName(), $this->_resources)){
			$this->_resources[] = $resource;
			$this->_accessList[(string)$resource->getName()] = array();
			$this->_resourcesNames[] = $resource->getName();
		}
		if(func_num_args()>1){
			$accessList = func_get_args();
			unset($accessList[0]);
			$this->addResourceAccess($resource->getName(), $accessList);
		}
	}

	/**
	 * Agrega accesos a un Resource
	 *
	 * @param string $resourceName
	 * @param mixed $accessList
	 */
	public function addResourceAccess($resourceName, $accessList){
		if(!in_array($resourceName, $this->_resourcesNames)){
			//El recurso '$resourceName' no ha sido definido previamente
			$message = CoreLocale::getErrorMessage(-18, $resourceName);
			throw new AclException($message, -18);
		}
		if(is_array($accessList)){
			foreach($accessList as $accessName) {
				if(!in_array($accessName, $this->_accessList[$resourceName])){
					$this->_accessList[$resourceName][] = $accessName;
				}
			}
		} else {
			if(!in_array($accessList, $this->_accessList[$resourceName])){
				$this->_accessList[$resourceName][] = $accessList;
			}
		}
	}

	/**
	 * Elimina un acceso del resorce
	 *
	 * @param string $resource
	 * @param mixed $accessList
	 */
	public function dropResourceAccess($resource, $accessList){
		if(is_array($accessList)){
			foreach($accessList as $accessName) {
				if(in_array($accessName, $this->_accessList[$resource])){
					foreach($this->_accessList[$resource] as $i => $access){
						if($access==$accessName){
							unset($this->_accessList[$resource][$i]);
						}
					}
				}
			}
		} else {
			if(in_array($accessList, $this->_accessList[$resource])){
				foreach($this->_accessList[$resource] as $i => $access){
					if($access==$accessList){
						unset($this->_accessList[$resource][$i]);
					}
				}
			}
		}
		$this->_rebuildAccessList();
	}

	/**
	 * Agrega un acceso de la lista de resources a un rol
	 *
	 * Utilizar '*' como comod�n
	 *
	 * Ej:
	 * <code>
	 * //Acceso para invitados a consultar en clientes
	 * $acl->allow('invitados', 'clientes', 'consulta');
	 *
	 * //Acceso para invitados a consultar e insertar en clientes
	 * $acl->allow('invitados', 'clientes', array('consulta', 'insertar'));
	 *
	 * //Acceso para cualquiera a visualizar en productos
	 * $acl->allow('*', 'productos', 'visualiza');
	 *
	 * //Acceso para cualquiera a visualizar en cualquier resource
	 * $acl->allow('*', '*', 'visualiza');
	 * </code>
	 *
	 * @param string $role
	 * @param string $resource
	 * @param mixed $access
	 */
	public function allow($role, $resource, $access){
		if(!in_array($role, $this->_rolesNames)){
			//No existe el role en la lista
			$message = CoreLocale::getErrorMessage(-16, $role);
			throw new AclException($message, -16);
			return;
		}
		if(!in_array($resource, $this->_resourcesNames)){
			//No existe el recurso en la lista
			$message = CoreLocale::getErrorMessage(-17, $resource);
			throw new AclException($message, -17);
			return;
		}
		if(is_array($access)){
			foreach($access as $acc){
				if(!in_array($acc, $this->_accessList[$resource])){
					//No existe el acceso en el recurso
					$message = CoreLocale::getErrorMessage(-19, $acc, $resource);
					throw new AclException($message, -19);
				}
			}
			foreach($access as $acc){
				$this->_access[$role][$resource][$acc] = 'A';
				if(!isset($this->_access[$role][$resource]['*'])){
					$this->_access[$role][$resource]['*'] = 'A';
				}
			}
		} else {
			if(!in_array($access, $this->_accessList[$resource])){
				//No existe el acceso en el recurso
				$message = CoreLocale::getErrorMessage(-19, $access, $resource);
				throw new AclException($message, -19);
				return false;
			}
			$this->_access[$role][$resource][$access] = 'A';
			if(!isset($this->_access[$role][$resource]['*'])){
				$this->_access[$role][$resource]['*'] = 'A';
			}
			$this->_rebuildAccessList();
		}
	}

	/**
	 * Denegar un acceso de la lista de resources a un rol
	 *
	 * Utilizar '*' como comod�n
	 *
	 * Ej:
	 * <code>
	 * //Denega acceso para invitados a consultar en clientes
	 * $acl->deny('invitados', 'clientes', 'consulta');
	 *
	 * //Denega acceso para invitados a consultar e insertar en clientes
	 * $acl->deny('invitados', 'clientes', array('consulta', 'insertar'));
	 *
	 * //Denega acceso para cualquiera a visualizar en productos
	 * $acl->deny('*', 'productos', 'visualiza');
	 *
	 * //Denega acceso para cualquiera a visualizar en cualquier resource
	 * $acl->deny('*', '*', 'visualiza');
	 * </code>
	 *
	 * @param string $role
	 * @param string $resource
	 * @param mixed $access
	 */
	public function deny($role, $resource, $access){

		if(!in_array($role, $this->_rolesNames)){
			// No existe el rol en la lista
			$message = CoreLocale::getErrorMessage(-16, $role);
			throw new AclException($message, -16);
			return;
		}

		if(!in_array($resource, $this->_resourcesNames)){
			//No existe el recurso en la lista
			$message = CoreLocale::getErrorMessage(-18, $role);
			throw new AclException($message, -18);
			return;
		}

		if(is_array($access)){
			foreach($access as $acc){
				if(!in_array($acc, $this->_accessList[$resource])){
					//No existe el acceso en el recurso
					$message = CoreLocale::getErrorMessage(-19, $access, $resource);
					throw new AclException($message, -19);
				}
			}
			foreach($access as $acc){
				$this->access[$role][$resource][$acc] = 'D';
			}
			if(!isset($this->_access[$role][$resource]['*'])){
				$this->_access[$role][$resource]['*'] = 'A';
			}
		} else {
			if(!in_array($access, $this->_accessList[$resource])){
				//No existe el acceso en el recurso
				$message = CoreLocale::getErrorMessage(-19, $access, $resource);
				throw new AclException($message, -19);
			}
			$this->_access[$role][$resource][$access] = 'D';
			if(!isset($this->_access[$role][$resource]['*'])){
				$this->_access[$role][$resource]['*'] = 'A';
			}
			$this->_rebuildAccessList();
		}
	}

	/**
	 * Devuelve true si un $role, tiene acceso en un resource
	 *
	 * <code>
	 * //Andres tiene acceso a insertar en el resource productos
	 * $acl->isAllowed('andres', 'productos', 'insertar');
	 *
	 * //Invitado tiene acceso a editar en cualquier resource?
	 * $acl->isAllowed('invitado', '*', 'editar');
	 *
	 * //Invitado tiene acceso a editar en cualquier resource?
	 * $acl->isAllowed('invitado', '*', 'editar');
	 * </code>
	 *
	 * @param string $role
	 * @param string $resource
	 * @param mixed $accessList
	 * @return boolean
	 */
	public function isAllowed($role, $resource, $accessList){

		if(!in_array($resource, $this->_resourcesNames)){
			return true;
		}
		if(!is_array($accessList)){
			$accessList = array($accessList);
		}
		$isAllowed = false;
		foreach($accessList as $access){
			foreach($this->_access[$role] as $resourceName => $resourceAccess){
				if($resourceName==$resource||$resourceName=='*'){
					if($resourceAccess[$access]=='A'){
						$isAllowed = true;
					} else {
						if($resourceAccess[$access]=='D'){
							$isAllowed = false;
						} else {
							if($resourceAccess['*']=='A'){
								$isAllowed = true;
							}
							if($resourceAccess['*']=='D'){
								$isAllowed = false;
							}
						}
					}
				}
			}
		}
		return $isAllowed;
	}

	/**
	 * Reconstruye la lista de accesos a partir de las herencias
	 * y accesos permitidos y denegados
	 *
	 * @access private
	 */
	private function _rebuildAccessList(){
		$middle = ceil(count($this->_roles)*count($this->_roles)/2);
		for($i=0;$i<=$middle;++$i){
			foreach($this->_rolesNames as $role){
				if(isset($this->_roleInherits[$role])){
					foreach($this->_roleInherits[$role] as $role_inherit){
						if(isset($this->_access[$roleInherit])){
							foreach($this->_access[$roleInherit] as $resourceName => $access){
								foreach ($access as $accessName => $value){
									if(!in_array($accessName, $this->_accessList[$resourceName])){
										unset($this->_access[$role_inherit][$resourceName][$accessName]);
									} else {
										if(!isset($this->access[$role][$resourceName][$accessName])){
											$this->_access[$role][$resourceName][$accessName] = $value;
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
