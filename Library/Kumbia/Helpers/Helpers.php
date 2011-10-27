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
 * @package		Helpers
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andrés Felipe Gutiérrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2007-2008 Emilio Rafael Silveira Tovar(emilio.rst at gmail.com)
 * @copyright	Copyright (c) 2007-2008 Deivinson Tejeda Brito (deivinsontejeda at gmail.com)
 * @license  	New BSD License
 * @version 	$Id: Helpers.php 47 2009-05-09 01:21:19Z gutierrezandresfelipe $
 */

/**
 * Helpers
 *
 * Componente que implementa ayudas utiles al desarrollador
 *
 * @category 	Kumbia
 * @package 	Helpers
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andrés Felipe Gutiérrez (gutierrezandresfelipe at gmail.com)
 * @copyright 	Copyright (c) 2007-2008 Emilio Rafael Silveira Tovar(emilio.rst at gmail.com)
 * @copyright 	Copyright (c) 2007-2008 Deivinson Tejeda Brito (deivinsontejeda at gmail.com)
 * @license  	New BSD License
 * @access 		public
 */
abstract class Helpers {

	/**
	 * Escribe un valor en bytes en forma humana
	 *
	 * @param	double $num
	 * @param	integer $decimals
	 * @return	string
	 * @static
	 */
	static public function toHuman($num, $decimals=2){
		$num = (int) $num;
		if($num<1024){
			return $num.' bytes';
		} else {
			if($num<1048576){
				return round($num/1024, $decimals).' kb';
			} else {
				if($num<1073741824){
					return round($num/1024/1024, $decimals).' mb';
				} else {
					return round($num/1024/1024/1024, $decimals).' gb';
				}
			}
		}
	}

	/**
	 * Genera una frase mediante un timestamp indicando con palabras
	 * hace cuanto ocurrio algo
	 *
	 * @access	public
	 * @param	integer $time
	 * @static
	 */
	static public function verboseTimeAgo($time){
		$now = time();
		if($time){
			if($time>=($now-60)){
				return 'Hace unos segundos';
			} else {
				if($time>=($now-3600)){
					return 'Hace unos minutos ('.date('H:i:s', $time).')';
				} else {
					if($time>=($now-86400)){
						return 'Hace unos horas ('.date('H:i:s', $time).')';
					} else {
						return date('Y-m-d H:i:s');
					}
				}
			}
		}
		return '';
	}

	/**
	 * Devuelve un string encerrado en comillas
	 *
	 * @param	string $word
	 * @return	string
	 */
	static public function comillas($word){
		return "'$word'";
	}

	/**
	 * Resalta un Texto en otro Texto
	 *
	 * @param	string $sentence
	 * @param	string $what
	 * @return	string
	 */
	static public function highlight($sentence, $what){
		return str_replace($what, '<strong class="highlight">'.$what.'</strong>', $sentence);
	}

	/**
	 * Una version avanzada de trim
	 *
	 * @param	string $word
	 * @param	integer $number
	 * @return	string
	 */
	public static function truncate($word, $number=0){
		if($number){
			return substr($word, 0, $number);
		} else {
			return rtrim($word);
		}
	}

}

