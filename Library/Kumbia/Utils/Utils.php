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
 * @category 	Kumbia
 * @package 	Utils
 * @copyright	Copyright (c) 2008-2012 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright 	Copyright (c) 2007-2008 Emilio Rafael Silveira Tovar (emilio.rst@gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Utils.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * Utils
 *
 * Utils es un componente que principalmente es utilizado por el framework para centralizar
 * funciones auxiliares de propósito general, su funcionalidad también
 * está disponible al desarrollador.
 *
 * @category 	Kumbia
 * @package 	Utils
 * @copyright	Copyright (c) 2008-2012 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright  	Copyright (c) 2007-2008 Emilio Rafael Silveira Tovar (emilio.rst@gmail.com)
 * @license 	New BSD License
 * @abstract
 */
abstract class Utils {

	/**
	 * Merge Two Arrays Overwriting Values $a1
	 * from $a2
	 *
	 * @param array $a1
	 * @param array $a2
	 * @return array
	 */
	public static function arrayMergeOverwrite($a1, $a2){
		foreach($a2 as $key2 => $value2){
			if(!is_array($value2)){
				$a1[$key2] = $value2;
			} else {
				if(!isset($a1[$key2])){
					$a1[$key2] = null;
				}
				if(!is_array($a1[$key2])){
					$a1[$key2] = $value2;
				} else {
					$a1[$key2] = self::arrayMergeOverwrite($a1[$key2], $a2[$key2]);
				}
			}
		}
		return $a1;
	}

	/**
	 * Inserts a element into a defined position
	 * in a array
	 *
	 * @param array $form
	 * @param mixed $index
	 * @param mixed $value
	 * @param mixed $key
	 */
	public static function arrayInsert(&$form, $index, $value, $key=null){
		$ret = array();
		$n = 0;
		$i = false;
		foreach($form as $keys => $val){
			if($n!=$index){
				$ret[$keys] = $val;
			} else {
				if(!$key){
					$ret[$index] = $value;
					$i = true;
				} else {
					$ret[$key] = $value;
					$i = true;
				}
				$ret[$keys] = $val;
			}
			++$n;
		}
		if(!$i){
			if(!$key){
				$ret[$index] = $value;
				$i = true;
			} else {
				$ret[$key] = $value;
				$i = true;
			}
		}
		$form = $ret;
	}

	/**
	 * Realiza un escaneo recursivo en un directorio
	 *
	 * @param string $package_dir
	 * @param array $files
	 * @return array
	 */
	public static function scandirRecursive($package_dir, $files=array()){
		foreach(scandir($package_dir) as $file){
			if($file!='.'&&$file!='..'){
				if(is_dir($package_dir.'/'.$file)){
					$files = self::scandirRecursive($package_dir.'/'.$file, $files);
				} else {
					if(preg_match('/(.)+\.php$/', $file)){
						$files[] = $package_dir.'/'.$file;
					}
				}
			}
		}
		return $files;
	}

	/**
 	 * Convierte los parámetros de una funcion o metodo a parámetros por nombre
	 *
	 * @access 	public
	 * @param 	array $params
	 * @param 	int $numberArgs
	 * @return 	array
	 */
	public static function getParams($params, $numberArgs){
		if(isset($params[0])&&is_array($params[0])&&$numberArgs==1){
			return $params[0];
		} else {
			$data = array();
			$i = 0;
			foreach($params as $p){
				if(is_string($p)&&preg_match('/([a-zA-Z_0-9]+): (.*)/', $p, $regs)){
					$data[$regs[1]] = $regs[2];
				} else {
					$data[$i] = $p;
				}
				++$i;
			}
			return $data;
		}
	}

	/**
	 * Convierte un parámetro por nombre en un array
	 *
	 * @access 	public
	 * @param 	string $param
	 * @return 	array
	 * @static
	 */
	public static function getParam($param){
		if(is_string($param)&&preg_match('/([a-zA-Z_0-9]+): (.)+/', $param, $regs)){
			$data = array('key' => $regs[1], 'value' => $regs[2]);
		} else {
			$data = array('key' => 0, 'value' => $param);
		}
		return $data;
	}

	/**
	 * Cameliza una cadena
	 *
	 * @access	public
	 * @param	string $str
	 * @return	string
	 * @static
	 */
	static public function camelize($str) {
		return str_replace(' ','',ucwords(str_replace('_',' ',$str)));
	}

	/**
	 * Descameliza una cadena camelizada
	 *
	 * @access 	public
	 * @param 	string $str
	 * @return 	string
	 * @static
	 */
	static public function uncamelize($str){
		if(i18n::isUnicodeEnabled()==true){
			$patterns = array(
			 	'/(?<=(?:\p{Lu}))(\p{Lu}\p{Ll})/' => '_\1',
				'/(?<=(?:\p{Ll}))(\p{Lu})/' => '_\1'
			);
		} else {
			$patterns = array(
				'/(?<=(?:[A-Z]))([A-Z]+)([A-Z][A-z])/' => '\1_\2',
				'/(?<=(?:[a-z]))([A-Z])/' => '_\1'
			);
		}
		foreach ($patterns as $pattern => $replacement){
			if(preg_match($pattern, $str)){
				return i18n::strtolower(preg_replace($pattern, $replacement, $str));
			}
		}
		return i18n::strtolower($str);
	}

	/**
     * Coloca la primera letra en minúscula
     *
     * @access 	public
     * @param 	string $s
     * @return 	string
     * @static
     **/
    public static function lcfirst($s){
        return strtolower(substr($s, 0, 1)) . substr($s, 1);
    }

	/**
	 * Devuelve una URL adecuada de Kumbia
	 *
	 * @access 	public
	 * @param	string $url
	 * @return	string
	 * @static
	 */
	static public function getKumbiaUrl($url){
		/*if(Core::isWebsphere()){
			$returnURL = '?_url='.Core::getInstancePath();
		} else {
			$returnURL = Core::getInstancePath();
		}*/
		$returnURL = Core::getInstancePath();
		if(!is_array($url)){
			$action = $url;
			$module = Router::getModule();
			$application = Router::getActiveApplication();
		} else {
			$action = $url[0];
			if(!isset($url['module'])||!$url['module']){
				$module = Router::getModule();
			} else {
				$module = $url['module'];
			}
			if(!isset($url['application'])||!$url['application']){
				$application = Router::getActiveApplication();
			} else {
				$application = $url['application'];
			}
		}
		if($application){
			$returnURL.=$application.'/';
		}
		if($module){
			$returnURL.=$module.'/';
		}
		$returnURL.=$action;
		return $returnURL;
	}

	/**
	 * Genera una URL externa a un recurso de la aplicación
	 *
	 * @param 	string $path
	 * @return	string
	 */
	static public function getExternalUrl($path){
		return 'http://'.$_SERVER['HTTP_HOST'].Core::getInstancePath().$path;
	}

	/**
	 * Devuelve el array enviado como parametro con cada elemento con comillas
	 *
	 * @access 	public
	 * @param 	array $toQuoteArray
	 * @param 	string $quoteChar
	 * @return 	array
	 */
	public static function getQuotedArray($toQuoteArray, $quoteChar="'"){
		$returnedArray = array();
		foreach($toQuoteArray as $index => $value){
			$returnedArray[$index] = $quoteChar.$value.$quoteChar;
		}
		return $returnedArray;
	}

	/**
	 * Cambia la primera letra de las palabras a mayúsculas
	 *
	 * @access 	public
	 * @param 	string $words
	 * @return 	string
	 */
	public static function ucwords($words){
		return ucwords(i18n::strtolower($words));
	}

	/**
	 * Reemplaza una parte de un texto en una determinada posición
	 *
	 * @param string $subject
	 * @param int $position
	 * @param int $length
	 * @param string $replace
	 * @return string
	 */
	public static function replaceText($subject, $position, $length, $replace){
		if(strlen($replace)>$length){
			$replace = substr($replace, 0, $length);
		} else {
			$replace = str_pad($replace, $length, ' ');
		}
		return substr($subject, 0, $position).$replace.substr($subject, $position+$length);
	}

	/**
	 * Quita los decimales no representativos de una cantidad
	 *
	 * @param	double $number
	 * @return 	string
	 */
	public static function dropDecimals($number){
		return preg_replace('/\.[0]+$/', '', $number);
	}

	/**
	 * Ordena una matriz por sus claves de modo natural
	 *
	 * uksort($variable, array('Utils', 'natksort'));
	 *
	 * @access 	public
	 * @param 	string $a
	 * @param 	string $b
	 * @return 	integer
	 */
	public static function natksort($a, $b){
		$la = strlen($a);
		$lb = strlen($b);
		if($la<12){
			$a = $a.str_repeat('0', 12-$la);
		}
		if($lb<12){
			$b = $b.str_repeat('0', 12-$lb);
		}
		if($a<$b){
			return -1;
		} else {
			if($a==$b){
				return 0;
			} else {
				return 1;
			}
		}
	}

	/**
	 * Devuelve un array con los parámetros ordenados ascendentemente
	 *
	 * @return array
	 */
	public static function sortRange(){
		$params = func_get_args();
		sort($params);
		return $params;
	}

	/**
	 * Devuelve un array con los parámetros ordenados ascendentemente
	 *
	 * @return array
	 */
	public static function sortStringRange(){
		$params = func_get_args();
		sort($params, SORT_STRING);
		return $params;
	}

}