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
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license		New BSD License
 * @version		$Id: Acl.php 97 2009-09-30 19:28:13Z gutierrezandresfelipe $
 */

/**
 * Acl
 *
 * La Lista de Control de Acceso o ACLs (del inglés, Access Control List)
 * es un concepto de seguridad informatica usado para fomentar la separacion
 * de privilegios. Es una forma de determinar los permisos de acceso apropiados
 * a un determinado objeto, dependiendo de ciertos aspectos del proceso
 * que hace el pedido.
 *
 * @category	Kumbia
 * @package		Acl
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license		New BSD License
 * @access		public
 */
class Acl extends Object {

	/**
	 * Objeto Adaptador Interno
	 *
	 * @var mixed
	 */
	private $_adapter;

	/**
	 * Constructor de la Clase Acl
	 *
	 * @param string $adapterClassName
	 * @param string $path
	 */
	public function __construct(){
		$numberArguments = func_num_args();
		$params = Utils::getParams(func_get_args(), $numberArguments);
		if(isset($params[0])){
			$adapterClassName = $params[0];
		} else {
		 	$adapterClassName = 'Memory';
		}
		$adapterClass = 'Acl'.$adapterClassName;
		if(class_exists($adapterClass, false)==false){
			if(!isset($params['path'])){
				if(interface_exists('AclAdapter', false)==false){
					require KEF_ABS_PATH.'Library/Kumbia/Acl/Interface.php';
				}
				$adapterFile = 'Library/Kumbia/Acl/Adapters/'.$adapterClassName.'.php';
				if(Core::fileExists($adapterFile)==true){
					require KEF_ABS_PATH.$adapterFile;
				} else {
					$message = CoreLocale::getErrorMessage(-14, $adapterClassName);
					throw new AclException($message, -14);
				}
			} else {
				require $path.'/'.$adapterClassName.'.php';
			}
		}
		$this->_adapter = new $adapterClass($params);
	}

	/**
	 * Este metodo pasa cualquier llamado al objeto Acl a la instancia interna del Adaptador
	 *
	 * @param string $method
	 */
	public function __call($method, $arguments=array()){
		return call_user_func_array(array($this->_adapter, $method), $arguments);
	}

	/**
	 * Crea un objeto Acl apartir de un descriptor ACL
	 *
	 * @param	string $descriptor
	 * @return	Acl
	 * @static
	 */
	static public function getAclFromDescriptor($descriptor){
		$descriptorParts = explode(":", $descriptor);
		$adapterName = $descriptorParts[0];
		$settings = explode(";", $descriptorParts[1]);
		$arguments = array("\"$adapterName\"");
		foreach($settings as $setting){
			$arguments[] = "\"".str_replace("=", ": ", $setting)."\"";
		}
		eval("\$acl = new Acl(".join(",", $arguments).");");
		return $acl;
	}

}
