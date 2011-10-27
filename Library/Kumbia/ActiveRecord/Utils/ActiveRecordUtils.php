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
 * @package		ActiveRecord
 * @subpackage	ActiveRecordUtils
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ActiveRecordUtils.php,v f5add30bf4ba 2011/10/26 21:05:13 andres $
 */

/**
 * ActiveRecordUtils
 *
 * Implementa métodos de seguridad y validación usados internamente
 * por ActiveRecordBase
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	ActiveRecordUtils
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
abstract class ActiveRecordUtils extends Object {

	/**
	 * Elimina caracteres que podrian ayudar a ejecutar
	 * un ataque de Inyeccion SQL
	 *
	 * @access public
	 * @param string $sqlItem
	 * @static
	 */
	public static function sqlItemSanizite($sqlItem){
		if(preg_match('/^[a-zA-Z0-9_]+$/', $sqlItem, $regs)){
			return $regs[0];
		} else {
			return null;
		}
	}

	/**
	 * Elimina caracteres que podrian ayudar a ejecutar
	 * un ataque de Inyeccion SQL
	 *
	 * @access public
	 * @param string $sqlItem
	 * @static
	 */
	public static function sqlSanizite($sqlItem){
		return $sqlItem;
	}

	/**
	 * Sanea por el tipo de dato en el modelo
	 *
	 * @param	string $modelName
	 * @param	string $fieldName
	 * @param	mixed $value
	 */
	public static function saniziteByDataType($modelName, $fieldName, $value){
		$entity = EntityManager::get($modelName);
		if($entity->hasField($fieldName)){
			if(get_magic_quotes_gpc()==false){
				$value = addslashes($value);
			}
			$dataTypes = $entity->getDataTypes();
			$dataType = $dataTypes[$fieldName];
			if($value===''||$value===null){
				return null;
			} else {
				if(strpos($dataType, 'decimal')){
					return Filter::bring($value, 'double');
				} else {
					if(strpos($dataType, 'int')){
						return Filter::bring($value, 'int');
					} else {
						return Filter::bring($value, 'striptags');
					}
				}
			}
		} else {
			throw new ActiveRecordException('El campo "'.$fieldName.'" no hace parte de la entidad "'.$modelName.'"');
		}
	}

}
