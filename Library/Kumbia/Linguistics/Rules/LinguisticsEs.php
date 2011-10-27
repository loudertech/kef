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
 * @package 	Linguistics
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 */

/**
 * LinguisticsEs
 *
 * Reglas para el lenguage español LinguisticsEs
 *
 * @category 	Kumbia
 * @package 	Linguistics
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 */
class LinguisticsEs extends Object {

	/**
	 * Localización de donde se obtienen la información
	 *
	 * @var Locale
	 */
	private $_locale;

	/**
	 * Articulos determinados y no-determinados
	 *
	 * @var array
	 */
	private $_articles = null;

	/**
	 * Una localización del idioma español
	 *
	 * @param Locale $locale
	 */
	public function __construct(Locale $locale){
		$this->_locale = $locale;
	}

	/**
	 * Indica si una palabra tiene genero femenino
	 *
	 * @param	string $word
	 * @return	boolean
	 */
	public function isFemale($word){
		if(strpos($word, ' ')!==false){
			$words = preg_split('/[ ]+/', $word);
			$word = $words[0];
		}
		$length = i18n::strlen($word);
		foreach($this->_locale->getWordRules('female') as $wordEnd){
			if(i18n::substr($word, $length-i18n::strlen($wordEnd))===$wordEnd){
				return true;
			}
		}
		return false;
	}

	/**
	 * Pluraliza una palabra en singular
	 *
	 * @param	string $word
	 * @return	string
	 */
	public function pluralize($word){
		$length = i18n::strlen($word);
		$last = i18n::substr($word, $length-1);
		if(!in_array($last, $this->_locale->getAllVowels())){
			if($last!='s'&$last!='x'){
				switch($last){
					case 'z':
						return $this->_replaceAccented(i18n::substr($word, 0, $length-1)).'ces';
					case 'k':
					case 'c':
						return $this->_replaceAccented(i18n::substr($word, 0, $length-1)).'ques';
					case 'g':
						return $this->_replaceAccented($word).'ues';
					default:
						return $this->_replaceAccented($word).'es';
				}
			} else {
				return $word;
			}
		} else {
			return $word.'s';
		}
	}

	/**
	 * Convierte a singular una palabra en plural
	 *
	 * @param	string $word
	 * @return	string
	 */
	public function singlify($word){
		$length = i18n::strlen($word);
		$last = i18n::substr($word, $length-1);
		if($last!='x'){
			if(preg_match('/([a-z]+)ces$/', $word, $matches)){
				$plast = i18n::substr($matches[1], $length-1);
				if($plast=='z'){
					return $matches[1];
				}
			}
			if(preg_match('/([a-z]+)ques$/', $word, $matches)){
				$plast = i18n::substr($matches[1], $length-1);
				if($plast=='k'||$last=='c'){
					return $matches[1];
				}
			}
			if(preg_match('/([a-z]+)ues$/', $word, $matches)){
				$plast = i18n::substr($matches[1], $length-1);
				if($plast=='g'){
					return $matches[1];
				}
			}
			if(preg_match('/([a-z]+)es$/', $word, $matches)){
				$plast = i18n::substr($matches[1], $length-1);
				if($plast=='g'){
					return $matches[1];
				}
			}
			if($last=='s'){
				return i18n::substr($word, 0, $length-1);
			}
		}
		return $word;
	}

	/**
	 * Recibe una palabra en singular y aplica el artículo indeterminado ó determinado según su genero
	 *
	 * @param 	string $type
	 * @param	string $word
	 * @package boolean $isPlural
	 */
	public function applyArticle($type, $word, $isPlural){
		if($this->_articles===null){
			$this->_articles = $this->_locale->getLinguisticArticles();
		}
		if(strpos($word, ' ')===false){
			$completeWord = $word;
		} else {
			$completeWord = $word;
			$words = preg_split('/[ ]+/', $word);
			$word = $words[0];
		}
		$length = i18n::strlen($word);
		$last = i18n::substr($word, $length-1);
		if($isPlural==false){
			$female = $this->_articles[$type]['female']['one'];
			$male = $this->_articles[$type]['male']['one'];
		} else {
			$female = $this->_articles[$type]['female']['other'];
			$male = $this->_articles[$type]['male']['other'];
		}
		foreach($this->_locale->getWordRules('female') as $wordEnd){
			if(i18n::substr($word, $length-i18n::strlen($wordEnd))===$wordEnd){
				return $female.' '.$completeWord;
			}
		}
		return $male.' '.$completeWord;
	}

	/**
	 * Cambia una palabra a genero masculino
	 *
	 * @param	string $word
	 * @return	string
	 */
	public function toMale($word){
		$words = explode(' ', $word);
		if(isset($words[0])){
			if(count($words)>1){
				$otherWords = ' '.join(' ', array_splice($words, 1));
			} else {
				$otherWords = '';
			}
			$vowels = $this->_locale->getVowels();
			$length = i18n::strlen($words[0]);
			$penultimate = i18n::substr($words[0], $length-2, 1);
			$penultimateSyllable = i18n::substr($words[0], $length-4, 2);
			$lastConsonant = i18n::substr($words[0], $length-2, 1);
			foreach($this->_locale->getWordRules('female') as $wordEnd){
				if(i18n::substr($words[0], $length-i18n::strlen($wordEnd))===$wordEnd){
					if(!in_array($penultimateSyllable, array('ie', 'a'))){
						if(!in_array($lastConsonant, $vowels)){
							return i18n::substr($words[0], 0, $length-1).$otherWords;
						}
					} else {
						if(!in_array($lastConsonant, $vowels)){
							return i18n::substr($words[0], 0, $length-1).'o'.$otherWords;
						}
					}
				}
			}
		}
		return $word;
	}

	/**
	 * Cambiar a genero femenino una palabra en singular
	 *
	 * @param string $word
	 */
	public function toFemale($word){
		$words = explode(' ', $word);
		if(isset($words[0])){
			if(count($words)>1){
				$otherWords = ' '.join(' ', array_splice($words, 1));
			} else {
				$otherWords = '';
			}
			$vowels = $this->_locale->getAllVowels();
			$length = i18n::strlen($words[0]);
			$last = i18n::substr($words[0], $length-1);
			$penultimate = i18n::substr($words[0], $length-2, 1);
			foreach($this->_locale->getWordRules('male') as $wordEnd){
				if(i18n::substr($words[0], $length-i18n::strlen($wordEnd))===$wordEnd){
					if(in_array($last, $vowels)){
						if($last=='e'){
							return $words[0].$otherWords;
						} else {
							return i18n::substr($words[0], 0, $length-1).'a'.$otherWords;
						}
					} else {
						$penultimate = i18n::substr($words[0], $length-2, 1);
						if(!in_array($penultimate, $vowels)){
							return $words[0].'a'.$otherWords;
						} else {
							return $this->_replaceAccented($words[0]).'a'.$otherWords;
						}
					}
				}
			}
		}
		return $word;
	}

	/**
	 * Obtiene la traducción para "muchos"
	 *
	 * @param string $word
	 */
	public function getSeveral($word){
		$quantities = $this->_locale->getQuantities();
		$length = i18n::strlen($word);
		foreach($this->_locale->getWordRules('male') as $wordEnd){
			if(i18n::substr($word, $length-i18n::strlen($wordEnd))===$wordEnd){
				return $quantities['several'].'s '.$this->pluralize($word);
			}
		}
		return $this->toFemale($quantities['several']).'s '.$this->pluralize($word);
	}

	/**
	 * Obtiene la traducción para "ninguno" ó "nada"
	 *
	 * @param string $word
	 */
	public function getNoQuantity($word){
		$quantities = $this->_locale->getQuantities();
		$length = i18n::strlen($word);
		foreach($this->_locale->getWordRules('male') as $wordEnd){
			if(i18n::substr($word, $length-i18n::strlen($wordEnd))===$wordEnd){
				return $quantities['nothing'].' '.$word;
			}
		}
		return $this->toFemale($quantities['nothing']).' '.$word;
	}

	/**
	 * Reemplaza vocales con acentos por vocales sin acentos
	 *
	 * @param array $languageRules
	 * @param string $word
	 */
	public function _replaceAccented($word){
		$accentedVowels = $this->_locale->getAccentedVowels();
		foreach($accentedVowels as $vowel => $accentedVowel){
			$word = str_replace($accentedVowel, $vowel, $word);
		}
		return $word;
	}

}