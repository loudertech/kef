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
 * @subpackage	ActiveRecordCriteria
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: ActiveRecordCriteria.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * ActiveRecordCriteria
 *
 * Permite crear condiciones para usar en consultas de modelos
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	ActiveRecordCriteria
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class ActiveRecordCriteria {

	/**
	 * Reemplaza los parametros en el criterio
	 *
	 * @param string $criteria
	 * @param array $params
	 */
	public static function bindParams($criteria, $params){
		$i = 1;
		foreach($params as $key => $value){
			if(is_integer($key)){
				$key = $i;
			}
			if(is_object($value)){
				if($value instanceof Date){
					#$value = 'TO_DATE(\''.$value.'\', \'YYYY-MM-DD\')';
					$value = "'$value'";
				}
			} else {
				if(!is_integer($value)){
					$value = "'$value'";
				}
			}
			$criteria = str_replace(":$key", $value, $criteria);
			++$i;
		}
		return $criteria;
	}

}
