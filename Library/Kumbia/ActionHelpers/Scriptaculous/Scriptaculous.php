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
 * @package		ActionHelpers
 * @subpackage	Scriptaculous
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Scriptaculous.php 29 2009-05-01 02:19:38Z gutierrezandresfelipe $
 */

/**
 * Scriptaculous
 *
 * Permite interacturar con los frameworks Prototype y Scriptaculous
 *
 * @category	Kumbia
 * @package		ActionHelpers
 * @subpackage	Scriptaculous
 * @abstract
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 * @abstract
 */
abstract class Scriptaculous {

	/**
	 * Filtra un array con los valores que contengan cierto valor
	 *
	 * @param unknown_type $needle
	 * @param array $haystack
	 */
	public static function filter($needle, array $haystack){
		$results = array();
		foreach($haystack as $key => $value){
			if(stripos($value, $needle)!==false){
				$results[$key] = $value;
			}
		}
		return $results;
	}

	/**
	 * Crea los parametros de busqueda apropidados para la consulta en un modelo
	 *
	 * @param string $table
	 * @param array $fields
	 * @param mixed $value
	 */
	public static function queryModel($table, $fields, $value){
		if(count($fields)<2){
			throw new ScriptaculousException('Debe indicar el "id" y "texto" de la opción de autocompletar');
		}
		$magicQuotes = magic_quotes_runtime(1);
		if($magicQuotes==false){
			$value = addslashes($value);
		}
		$value = preg_replace('/[ ]+/', '%', trim($value));
		$sql = 'SELECT '.$fields[0].','.$fields['1'].', 1 AS _op FROM '.$table.' WHERE '.$fields[1].' LIKE \'%'.$value.'%\'';
		$sql.= 'UNION SELECT '.$fields[0].','.$fields['1'].', 2 AS _op FROM '.$table.' WHERE '.$fields[1].' LIKE \''.$value.'%\' ORDER BY 3,2';
		return $sql;
	}

	/**
	 * Genera el HTML requerido para la funcion de autocompletar de Scriptaculous
	 *
	 * @param mixed $data
	 * @param mixed $fields
	 * @param string $encoding
	 * @return string
	 */
	public static function autocomplete($data, $fields=array(), $encoding='UTF-8', $options=array()){
		$code = '<ul>';
		if(is_array($data)){
			foreach($data as $key => $value){
				if(!isset($options['showAll'])||$options['showAll']== false){
					if(isset($options['match'])){
						$value = preg_replace("/({$options['match']})/i",'<b>\1</b>',$value);
					}
					$code.= '<li id="'.$key.'">'.$value.'</option>';
				} else {
					$code.= '<li id="'.$key.'">'.$key.' - '.$value.'</option>';
				}
			}
		} else {
			if(count($fields)<2){
				throw new ScriptaculousException('Debe indicar el "id" y "texto" de la opción de autocompletar');
			}
			if(is_object($data)&&($data instanceof ActiveRecordResultset)){
				foreach($data as $row){
					if($encoding=='UTF-8'){
						if(!isset($options['showAll'])||$options['showAll']== false){
							$text = utf8_encode($row->readAttribute($fields[1]));
						} else {
							$text = $row->readAttribute($fields[0]).' - '.utf8_encode($row->readAttribute($fields[1]));
						}
					} else {
						if($encoding=='ISO-8859-1'){
							$text = utf8_decode($row->readAttribute($fields[1]));
						} else {
							$text = $row->readAttribute($fields[0]).' - '.$row->readAttribute($fields[1]);
						}
					}
					if(isset($options['match'])){
						$text = preg_replace("/({$options['match']})/i",'<b>\1</b>',$text);
					}
					$code.= '<li id="'.$row->readAttribute($fields[0]).'">'.$text.'</option>';
				}
			} else {
				throw new ScriptaculousException('Tipo de dato no soportado por el autocomplete');
			}
		}
		$code.= '</ul>';
		return $code;
	}
}
