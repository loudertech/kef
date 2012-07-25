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
 * @package		Acl
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @version		$Id: Interface.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * AclAdapterInterfase
 *
 * Interfase que deben implementar todos los adaptadores de ACL
 *
 * @category	Kumbia
 * @package		Acl
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
interface AclAdapter {

	public function addRole(AclRole $roleObject, $accessInherits='');
	public function addInherit($role, $roleToInherit);
	public function isRole($role_name);
	public function isResource($resource_name);
	public function addResource(AclResource $resource);
	public function addResourceAccess($resource, $accessList);
	public function dropResourceAccess($resource, $accessList);
	public function allow($role, $resource, $access);
	public function deny($role, $resource, $access);
	public function isAllowed($role, $resource, $accessList);

}
