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
 * @version 	$Id: Model.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * AclModel
 *
 * Permite administrar listas ACL usando entidades de un gestor relacional
 *
 * @category	Kumbia
 * @package		Acl
 * @subpackage	Adapters
 * @copyright 	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license		New BSD License
 */
class AclModel implements AclAdapter {

	/**
	 * Nombre de la clase que administra los accesos
	 *
	 * @var string
	 */
	private $_modelName;

	/**
	 * Nombre de la clase que administra los roles
	 *
	 * @var string
	 */
	private $_modelRolesName;

	/**
	 * Nombre de la clase que administra los recursos
	 *
	 * @var string
	 */
	private $_modelResourcesName;

	/**
	 * Nombre de la clase que relaciona los recursos con sus operaciones
	 *
	 * @var string
	 */
	private $_modelAccessResourcesName;

	/**
	 * Nombre de la clase que administra la herencia entre roles
	 *
	 * @var string
	 */
	private $_modelInheritsName;

	/**
	 * Objeto ActiveRecordTransaction
	 *
	 * @var ActiveRecordTransaction
	 */
	private $_transaction;

	/**
	 * Constructor de la Clase AclModel
	 *
	 * @param array $params
	 */
	public function __construct($params){
		if(!isset($params['className'])){
			/**
			 * Debe indicar el nombre del modelo que administra la lista de Acceso
			 */
			$message = CoreLocale::getErrorMessage(-20);
			throw new AclException($message, -20);
		}
		if(!EntityManager::isModel($params['className'])){
			/**
			 * La clase no es un modelo valido
			 */
			$message = CoreLocale::getErrorMessage(-21, $params['className']);
			throw new AclException($message, -21);
		}
		$this->_modelName = $params['className'];
		if(isset($params['rolesClassName'])){
			if(!EntityManager::isModel($params['rolesClassName'])){
				$message = CoreLocale::getErrorMessage(-21, $params['rolesClassName']);
				throw new AclException($message, -21);
			}
			$this->_modelRolesName = $params['rolesClassName'];
		}
		if(isset($params['resourcesClassName'])){
			if(!EntityManager::isModel($params['resourcesClassName'])){
				$message = CoreLocale::getErrorMessage(-21, $params['resourcesClassName']);
				throw new AclException($message, -21);
			}
			$this->_modelResourcesName = $params['resourcesClassName'];
		}
		if(isset($params['accessResourcesClassName'])){
			if(!EntityManager::isModel($params['accessResourcesClassName'])){
				$message = CoreLocale::getErrorMessage(-21, $params['accessResourcesClassName']);
				throw new AclException($message, -21);
			}
			$this->_modelAccessResourcesName = $params['accessResourcesClassName'];
		}
	}

	/**
	 * Agrega un Rol a la Lista ACL
	 *
	 * $roleObject = Objeto de la clase AclRole para agregar a la lista
	 * $accessInherits = Nombre del Role del cual hereda permisos o array del grupo
	 * de perfiles del cual hereda permisos
	 *
	 * Ej:
	 * <code>$acl->addRole(new Acl_Role('administrador'), 'consultor');</code>
	 *
	 * @param AclRole $roleObject
	 * @param array $accessInherits
	 * @return boolean
	 */
	public function addRole(AclRole $roleObject, $accessInherits=''){
		if(!$this->_modelRolesName){
			//No se ha definido el modelo que administra los roles
			$message = CoreLocale::getErrorMessage(-22);
			throw new AclException($message, -22);
		}
		$role = EntityManager::getEntityInstance($this->_modelRolesName);
		if(method_exists($role, "setName")){
			$role->setName($roleObject->getName());
		} else {
			$role->name = $roleObject->getName();
		}
		if($role->hasField('description')){
			$role->description = $roleObject->getDescription();
		}
		if(!$this->isRole($roleObject->getName())){
			if($role->save()==false){
				//No se pudo crear el rol
				foreach($role->getMessages() as $message){
					$message = CoreLocale::getErrorMessage(-23, $message->getMessage());
					throw new AclException($message, -23);
				}
			}
		} else {
			//El role ya se ha creado en la lista de acceso
			$message = CoreLocale::getErrorMessage(-24);
			throw new AclException($message, -24);
		}
		if($accessInherits!=''){

		}
		return true;
	}

	/**
	 * Hace que un rol herede los accesos de otro rol
	 *
	 * @param string $role
	 * @param string $roleToInherit
	 */
	public function addInherit($role, $roleToInherit){

	}

	/**
	 *
	 * Verifica si un rol existe en la lista o no
	 *
	 * @param string $roleName
	 * @return boolean
	 */
	public function isRole($roleName){
		if(!$this->_modelRolesName){
			//No se ha definido el modelo que administra los Roles
			$message = CoreLocale::getErrorMessage(-22);
			throw new AclException($message, -22);
		}
		$roles = EntityManager::getEntityInstance($this->_modelRolesName);
		return $roles->count("name = '$roleName'") > 0 ? true : false;
	}

	/**
	 *
	 * Verifica si un resource existe en la lista o no
	 *
	 * @param string $resourceName
	 * @return boolean
	 */
	public function isResource($resourceName){
		if(!$this->_modelResourcesName){
			//No se ha definido el modelo que administra los Resources
			$message = CoreLocale::getErrorMessage(-25);
			throw new AclException($message, -25);
		}
		$resource = EntityManager::getEntityInstance($this->_modelResourcesName());
		return $resource->count("name = '$resourceName'") > 0 ? true : false;
	}

	/**
	 * Agrega un recurso a la Lista ACL
	 *
	 * Resource_name puede ser el nombre de un objeto concreo, por ejemplo
	 * consulta, buscar, insertar, valida etc ó una lista de ellos
	 *
	 * Ej:
	 * <code>
	 * //Agregar un resource a la lista:
	 * $acl->addResource(new AclResource('clientes'), 'consulta');
	 *
	 * //Agregar Varios resources a la lista:
	 * $acl->addResource(new AclResource('clientes'), array('consulta', 'buscar', 'insertar'));
	 * </code>
	 *
	 * @param AclResource $resourceObject
	 * @param array $operationsList
	 * @return boolean
	 */
	public function addResource(AclResource $resourceObject, $operationsList=''){
		if($operationsList==''){
			return $this->_createResource($resourceObject);
		} else {
			return $this->addResourceAccess($resourceObject, $operationsList);
		}
	}

	/**
	 * Crea un recurso en el Modelo definido
	 *
	 * @param AclResource $resourceObject
	 */
	private function _createResource(AclResource $resourceObject){
		if(!$this->_modelResourcesName){
			//No se ha definido el modelo que administra los Resources
			$message = CoreLocale::getErrorMessage(-25);
			throw new AclException($message, -25);
		}
		$resource = EntityManager::getEntityInstance($this->_modelResourcesName);
		if($this->_transaction){
			$resource->setTransaction($this->_transaction);
		}
		if(method_exists($resource, "setName")){
			$resource->setName($resourceObject->getName());
		} else {
			$resource->name = $resourceObject->getName();
		}
		if($resource->hasField('description')){
			if(method_exists($resource, "setDescription")){
				$resource->setDescription($resourceObject->getDescription());
			} else {
				$resource->description = $resourceObject->getDescription();
			}
		}
		if(!$this->isResource($resourceObject->getName())){
			if($resource->save()==false){
				//No se pudo crear el recurso
				foreach($resource->getMessages() as $message){
					$message = CoreLocale::getErrorMessage(-26, $message->getMessage());
					throw new AclException($message, -26);
				}
			}
		} else {
			//El resource ya se ha creado en la lista de acceso
			$message = CoreLocale::getErrorMessage(-27);
			throw new AclException($message, -27);
		}
		return true;
	}

	/**
	 * Agrega accesos a un Resource
	 *
	 * @param AclResource $resourceObject
	 * @param array $operationsList
	 */
	public function addResourceAccess($resourceObject, $operationsList){
		if(!$this->_modelAccessResourcesName){
			//No se ha definido el modelo que relaciona los Resources con sus operaciones
			$message = CoreLocale::getErrorMessage(-28);
			throw new AclException($message, -28);
		}
		$createdResource = false;
		$this->_transaction = TransactionManager::getUserTransaction();
		if(!is_array($operationsList)){
			$operationsList = array($operationsList);
		}
		$numberList = count($operationsList);
		for($i=0;$i<$numberList;++$i){
			if(is_string($operationsList[$i])==false){
				$this->_transaction->rollback();
				$this->_transaction = null;
				//Nombre de operación invalido
				$message = CoreLocale::getErrorMessage(-29, $operationsList[$i]);
				throw new AclException($message, -29);
			}
			if($createdResource==false){
				if(!$this->isResource($resourceObject->getName())){
					$this->_createResource($resourceObject);
				} else {
				}
				$createdResource = true;
			}
			$accessResources = EntityManager::getEntityInstance($this->_modelAccessResourcesName);
			if(method_exists($accessResources, "setResource")){
				$accessResources->setResource($resourceObject->getName());
			} else {
				$accessResources->resource = $resourceObject->getName();
			}
			if(method_exists($accessResources, "setAction")){
				$accessResources->setAction($operationsList[$i]);
			} else {
				$accessResources->action = $operationsList[$i];
			}
			if($accessResources->save()==false){
				foreach($accessResources->getMessages() as $message){
					$this->_transaction->rollback();
					$this->_transaction = null;
					throw new AclException($accessResources->getMessage());
				}
			}
		}
		$this->_transaction->commit();
		$this->_transaction = null;
		return true;
	}

	/**
	 * Elimina un acceso del resorce
	 *
	 * @param string $resource
	 * @param mixed $accessList
	 */
	public function dropResourceAccess($resource, $accessList){

	}

	/**
	 * Agrega un acceso de la lista de resources a un rol
	 *
	 * Utilizar '*' como comodín
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
	 * @return boolean
	 */
	public function allow($role, $resource, $access){
		$model = EntityManager::getEntityInstance($this->modelName());
		$conditions = "role='$role' AND resource='$resource' AND action = '$access'";
		$access = $model->findFirst($conditions);
		if(!$access){
			if(method_exists($access, "setRole")){
				$access->setRole($role);
			} else {
				$access->role = $role;
			}
			if(method_exists($access, "setResource")){
				$access->setResource($resource);
			} else {
				$access->resource = $resource;
			}
			if(method_exists($access, "setAction")){
				$access->setAction($action);
			} else {
				$access->action = $action;
			}
		}
		$access->allow = 'Y';
		if($access->save()==false){
			//No se pudo guardar el acceso
			foreach($access->getMessages() as $message){
				$message = CoreLocale::getErrorMessage(-30, $message->getMessage());
				throw new AclException($message, -30);
			}
		}
		return true;
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
	 * @return boolean
	 */
	public function deny($role, $resource, $access){
		$model = EntityManager::getEntityInstance($this->modelName);
		$conditions = "role='$role' AND resource='$resource' AND action = '$access'";
		$access = $model->findFirst($conditions);
		if(!$access){
			if(method_exists($access, "setRole")){
				$access->setRole($role);
			} else {
				$access->role = $role;
			}
			if(method_exists($access, "setResource")){
				$access->setResource($resource);
			} else {
				$access->resource = $resource;
			}
			if(method_exists($access, "setAction")){
				$access->setAction($action);
			} else {
				$access->action = $action;
			}
		}
		$access->allow = 'N';
		if($access->save()==false){
			//No se pudo guardar el acceso
			foreach($access->getMessages() as $message){
				$message = CoreLocale::getErrorMessage(-30, $message->getMessage());
				throw new AclException($message, -30);
			}
		}
		return true;
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
		$model = EntityManager::getEntityInstance($this->_modelName);
		$conditions = "role='$role' AND resource='*' AND action = '*'";
		$access = $model->findFirst($conditions);
		if($access){
			if($access->getAllow()=='N'){
				return false;
			}
		}
		$conditions = "role='$role' AND resource='$resource' AND action = '*'";
		$access = $model->findFirst($conditions);
		if($access){
			if($access->getAllow()=='N'){
				return false;
			}
		}
		$conditions = "resource='$resource' AND role='$role' AND action = '$accessList' AND allow='N'";
		$deny = $model->count($conditions);
		if($deny){
			return false;
		}
		return true;
	}

}
