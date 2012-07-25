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
 * @subpackage	AclResource
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license		New BSD License
 * @version 	$Id: AclResource.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * AclResource
 *
 * Clase para la creacion de Resources ACL
 *
 * @category	Kumbia
 * @package		Acl
 * @subpackage	AclResource
 * @access		public
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license		New BSD License
 */
class AclResource extends Object {

	/**
	 * Nombre del Recurso
	 *
	 * @var $name
	 */
	private $name;

	/**
	 * Descripci�n del Recurso
	 *
	 * @var $description
	 */
	private $description;

	/**
	 * Constructor de la clase AclResource
	 *
	 * @param string $name
	 * @return AclResource
	 */
	public function __construct($name, $description=''){
		if($name=='*'){
			throw new AclException('Nombre invalido "*" para nombre de Resource en Acl_Resoruce::__constuct');
		}
		$this->name = $name;
		$this->description = $description;
	}

	/**
	 * Devuelve el nombre del Resource
	 *
	 */
	public function getName(){
		return $this->name;
	}

	/**
	 * Devuelve el nombre del Resource
	 *
	 */
	public function getDescription(){
		return $this->description;
	}

}
