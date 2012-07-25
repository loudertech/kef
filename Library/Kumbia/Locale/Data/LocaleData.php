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
 * @package		Locale
 * @subpackage 	Data
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright 	Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license 	New BSD License
 * @version 	$Id: LocaleData.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * LocaleData
 *
 * Obtiene informacion de los archivos LDML Unicode
 *
 * @category 	Kumbia
 * @package 	Locale
 * @subpackage 	Data
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright 	Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license 	New BSD License
 */
class LocaleData {

	/**
	 * Objeto LDML de Idioma
	 *
	 * @var DOMDocument
	 */
	private static $_ldmlLang;

	/**
	 * Objeto LDML del territorio
	 *
	 * @var DOMDocument
	 */
	private static $_ldmlCountry;

	/**
	 * Objeto LDML de los datos complementarios
	 *
	 * @var DOMDocument
	 */
	private static $_ldmlSupplemental;

	/**
	 * Objeto de XPATH de Idioma
	 *
	 * @var DOMXPath
	 */
	private static $_ldmlLangXpath;

	/**
	 * Objeto de XPATH de territorio
	 *
	 * @var DOMXPath
	 */
	private static $_ldmlCountryXpath;

	/**
	 * Objeto de XPATH de los datos complementarios
	 *
	 * @var DOMXPath
	 */
	private static $_ldmlSupplementalXpath;

	/**
	 * Constructor de LocaleData
	 *
	 * @param	string $language
	 * @param	string $country
	 */
	public function __construct($language, $country=''){
		if(!isset(self::$_ldmlLang[$language])){
			$ldmlPath = 'Library/Kumbia/Locale/Data/'.$language.'.xml';
			if(file_exists($ldmlPath)){
				self::$_ldmlLang[$language] = new DOMDocument();
				self::$_ldmlLang[$language]->load($ldmlPath);
				self::$_ldmlLangXpath[$language] = new DOMXPath(self::$_ldmlLang[$language]);
			} else {
				throw new LocaleException('La definición LDML del idioma '.$language.' no existe');
			}
		}
		if($country!=''){
			if(!isset(self::$_ldmlCountry[$country])){
				$ldmlPath = 'Library/Kumbia/Locale/Data/'.$language.'_'.$country.'.xml';
				if(file_exists($ldmlPath)){
					self::$_ldmlCountry[$country] = new DOMDocument();
					self::$_ldmlCountry[$country]->load($ldmlPath);
					self::$_ldmlCountryXpath[$country] = new DOMXPath(self::$_ldmlCountry[$country]);
				} else {
					throw new LocaleException('La definición LDML del territorio "'.$country.'" no existe');
				}
			}
		}
	}

	/**
	 * Consulta los datos complementarios
	 *
	 * @param	string $path
	 * @return	DOMNodeList
	 */
	public function querySupplementalData($path){
		if(self::$_ldmlSupplemental==null){
			$ldmlPath = 'Library/Kumbia/Locale/Data/supplementalData.xml';
			self::$_ldmlSupplemental = new DOMDocument();
			self::$_ldmlSupplemental->load($ldmlPath);
			self::$_ldmlSupplementalXpath = new DOMXPath(self::$_ldmlSupplemental);
		}
		return self::$_ldmlSupplementalXpath->query($path);
	}

	/**
	 * Hace una consulta en el arbol LDML del Idioma
	 *
	 * @param	string $language
	 * @param	string $path
	 * @return	DOMNodeList
	 */
	public function queryLanguage($language, $path){
		return self::$_ldmlLangXpath[$language]->query($path);
	}

	/**
	 * Hace una consulta en el arbol LDML del Territorio
	 *
	 * @param	string $country
	 * @param	string $path
	 * @return	DOMNodeList
	 */
	public function queryCountry($country, $path){
		return self::$_ldmlCountryXpath[$country]->query($path);
	}

	/**
	 * Hace una consulta en el arbol LDML del Territorio e Idioma
	 *
	 * @param	string $language
	 * @param	string $country
	 * @param	string $path
	 * @return	DOMNodeList
	 */
	public function queryAny($language, $country, $path){
		$result = self::$_ldmlCountryXpath[$country]->query($path);
		if($result->length==0){
			return self::$_ldmlLangXpath[$language]->query($path);
		} else {
			return $result;
		}
	}

}
