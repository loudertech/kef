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
 * @package		i18n
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 * @version 	$Id: i18n.php 118 2010-02-06 21:57:47Z gutierrezandresfelipe $
 */

/**
 * i18n
 *
 * Implenta funciones de internacionalización
 *
 * @category 	Kumbia
 * @package 	i18n
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 * @abstract
 */
abstract class i18n {

	/**
	 * Indica si es posible utilizar unicode
	 *
	 * @var boolean
	 */
	private static $_unicodeEnabled = null;

	/**
	 * Indica si la extension Multi-Byteesta disponible
	 *
	 * @var boolean
	 */
	private static $_multiByteEnabled = null;

	/**
	 * Charset por defecto del Componente
	 *
	 * @var string
	 */
	private static $_defaultCharset = 'UTF-8';

	/**
	 * Permite determinar si es posible realizar operaciones de cadenas en Unicode
	 *
	 * @access public
	 * @static
	 */
	static public function isUnicodeEnabled(){
		if(self::$_unicodeEnabled!==null){
			return self::$_unicodeEnabled;
		} else {
			if(extension_loaded('mbstring')){
				mb_internal_encoding(self::$_defaultCharset);
				mb_regex_encoding(self::$_defaultCharset);
				self::$_multiByteEnabled = true;
			} else {
				self::$_multiByteEnabled = false;
			}
			self::$_unicodeEnabled = (@preg_match('/\pL/u', 'a')) ? true : false;
		}
	}

	/**
	 * Cambia una cadena de caracteres a minúsculas
	 *
	 * @access	public
	 * @param 	string $str
	 * @return 	string
	 * @static
	 */
	static public function strtolower($str){
		self::isUnicodeEnabled();
		if(self::$_multiByteEnabled==false){
			return strtolower($str);
		} else {
			return mb_strtolower($str, self::$_defaultCharset);
		}
	}

	/**
	 * Cambia una cadena de caracteres a mayúsculas
	 *
	 * @access 	public
	 * @param 	string $str
	 * @return 	string
	 * @static
	 */
	static public function strtoupper($str){
		self::isUnicodeEnabled();
		if(self::$_multiByteEnabled==false){
			return strtoupper($str);
		} else {
			return mb_strtoupper($str, self::$_defaultCharset);
		}
	}

	/**
	 * Cambia la primera letra de una cadena de caracteres a mayúsculas
	 *
	 * @access 	public
	 * @param 	string $str
	 * @return 	string
	 * @static
	 */
	static public function ucfirst($str){
		self::isUnicodeEnabled();
		if(self::$_multiByteEnabled==false){
			return ucfirst($str);
		} else {
			return mb_strtoupper(self::substr($str, 0, 1), self::$_defaultCharset).self::substr($str, 1);
		}
	}

	/**
	 * Obtiene una parte de un String
	 *
	 * @param	string $str
	 * @param	int $start
	 * @param	int $length
	 * @return	string
	 */
	static public function substr($str, $start, $length=null){
		self::isUnicodeEnabled();
		if(self::$_multiByteEnabled==false){
			if($length===null){
				return substr($str, $start);
			} else {
				return substr($str, $start, $length);
			}
		} else {
			if($length===null){
				$length = mb_strlen($str);
			}
			if(is_array($str)){
				$str = $str[0];
			}
			return mb_substr($str, $start, $length, self::$_defaultCharset);
		}
	}

	/**
	 * Reemplaza las tildes y acentos por una letra ascii equivalente
	 *
	 * @param	string $str
	 * @return	string
	 */
	static public function toAscii($str){
		$acentos = array(
			'á' => 'a',
			'é' => 'e',
			'í' => 'i',
			'ó' => 'o',
			'ú' => 'u',
			'ñ' => 'n',
			'Á' => 'A',
			'É' => 'E',
			'Í' => 'I',
			'Ó' => 'O',
			'Ú' => 'U',
			'Ñ' => 'N',
		);
		foreach($acentos as $letter => $replace){
			$str = str_replace($letter, $replace, $str);
		}
		return $str;
	}

	/**
	 * Obtiene una parte de un String
	 *
	 * @param	string $str
	 * @param	int $start
	 * @param	int $length
	 * @return	string
	 */
	static public function str_replace($search, $replace, $subject){
		self::isUnicodeEnabled();
		if(self::$_multiByteEnabled==false){
			return str_replace($search, $replace, $subject);
		} else {
			$length = mb_strlen($search);
			$pos = mb_strpos($subject, $search);
            while($pos !== false){
                $subject = mb_substr($subject, 0, $pos).$replace.mb_substr($subject, $pos + $length);
                $pos = mb_strpos($subject, $search, $pos + mb_strlen($replace));
            }
			return $subject;
		}
	}

	/**
	 * Obtiene el tamaño de un string
	 *
	 * @param	string $str
	 * @return	int
	 */
	static public function strlen($str){
		self::isUnicodeEnabled();
		if(self::$_multiByteEnabled==false){
			return strlen($str);
		} else {
			return mb_strlen($str, self::$_defaultCharset);
		}
	}

	/**
	 * Aplica un formato printf a una cadena y devuelve el resultado
	 *
	 * @param	string $str
	 * @return	int
	 */
	static public function sprintf($format){
		self::isUnicodeEnabled();
		$arguments = func_get_args();
      	array_shift($arguments);
		if(self::$_multiByteEnabled==false){
			return sprintf($format);
		} else {
      		return mb_vsprintf($format, $argv);
		}
	}

	/**
	 * Reemplaza en una cadena de caracteres mediante una expresion regular
	 *
	 * @access 	public
	 * @param 	string $pattern
	 * @param 	string $replacement
	 * @param 	array $regs
	 * @static
	 */
	static public function eregReplace($pattern, $replacement, &$regs){
		self::isUnicodeEnabled();
		if(self::$_multiByteEnabled==false){
			return preg_replace('/'.$pattern.'/', $replacement, $regs);
		} else {
			return mb_ereg_replace($pattern, $replacement, $regs);
		}
	}

}
