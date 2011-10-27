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
 * Linguistics
 *
 * Permite efectuar diversas operaciones lingüisticas basadas en localización
 *
 * @category 	Kumbia
 * @package 	Linguistics
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 */
class Linguistics {

	/**
	 * Valor a convertir
	 *
	 * @var integer
	 */
	private static $_value = 0;

	/**
	 * Estado de la conversion
	 *
	 * @var string
	 */
	private static $_state = '';

	/**
	 * Localización usada para realizar las operaciones
	 *
	 * @var Locale
	 */
	private $_locale;

	/**
	 * Reglas de linguisticas para la localización activa
	 *
	 * @var LinguisticsEs
	 */
	private $_rules;

	/**
	 * Constructor de Linguistics
	 *
	 * @param Locale $locale
	 */
	public function __construct($locale=null){
		if($locale==null){
			$locale = Locale::getApplication();
		}
		$this->setLocale($locale);
	}

	/**
	 * Establece la localización para la lingüistica
	 *
	 * @param string $language
	 */
	public function setLocale($locale){
		if(is_string($locale)){
			$this->_locale = new Locale($locale);
		} else {
			$this->_locale = $locale;
		}
		$language = $this->_locale->getLanguage();
		$className = 'Linguistics'.ucfirst($language);
		if(class_exists($className, false)==false){
			$path = 'Library/Kumbia/Linguistics/Rules/'.$className.'.php';
			if(file_exists($path)){
				require KEF_ABS_PATH.$path;
			} else {
				throw new LinguisticsException('No existen reglas de localización para el idioma '.$language);
			}
		}
		$this->_rules = new $className($this->_locale);
	}

	/**
	 * Pluralizar una palabra
	 *
	 * @param  $word
	 * @return string
	 */
	public function pluralize($word){
		return $this->_rules->pluralize($word);
	}

	/**
	 * Convierte a singular una palabra
	 *
	 * @param  $word
	 * @return string
	 */
	public function singlify($word){
		return $this->_rules->singlify($word);
	}

	/**
	 * Indica si una palabra tiene genero femenino
	 *
	 * @param  $word
	 * @return string
	 */
	public function isFemale($word){
		return $this->_rules->isFemale($word);
	}

	/**
	 * Recibe una palabra en singular y genera el infinitivo "a" según su genero
	 *
	 * @param	string $singleWord
	 * @package boolean $isPlural
	 */
	public function a($word, $isPlural=false){
		return $this->_rules->applyArticle('indefinite', $word, $isPlural);
	}

	/**
	 * Recibe una palabra y genera el artículo "el", "la", "los", "las" según su genero
	 *
	 * @param	string $singleWord
	 * @package boolean $isPlural
	 */
	public function the($word, $isPlural=false){
		return $this->_rules->applyArticle('definite', $word, $isPlural);
	}

	/**
	 * Cambiar a genero masculino una palabra singular
	 *
	 * @param string $word
	 */
	public function toMale($word){
		return $this->_rules->toMale($word);
	}

	/**
	 * Cambiar a genero femenino una palabra en singular
	 *
	 * @param string $word
	 */
	public function toFemale($word){
		return $this->_rules->toFemale($word);
	}

	/**
	 * Realiza una conjunción de valores
	 *
	 * @param mixed $values
	 */
	public function getConjunction($values, $indefinite=false){
		if(!is_array($values)){
			$values = explode(' ', $values);
		}
		$values = array_count_values($values);
		$phrase = array();
		foreach($values as $word => $number){
			if($number==1){
				if($indefinite){
					$phrase[] = $this->a($word);
				} else {
					$phrase[] = $this->the($word);
				}
			} else {
				$phrase[] = i18n::strtolower(self::getNumberToWords($number)).' '.$this->pluralize($word);
			}
		}
		return $this->_locale->getConjunction($phrase);
	}

	/**
	 * Obtiene la cuatificación para "muchos"
	 *
	 * @param	string $word
	 * @return	string
	 */
	public function getSeveral($word){
		return $this->_rules->getSeveral($word);
	}

	/**
	 * Obtiene la cuatificación para "ninguno" ó "nada"
	 *
	 * @param	string $word
	 * @return	string
	 */
	public function getNoQuantity($word){
		return $this->_rules->getNoQuantity($word);
	}

	/**
	 * Devuelve un valor cuantificado
	 *
	 * @param	int $value
	 * @return	number
	 */
	public function getQuantification($value, $substantive){
		$quantities = $this->_locale->getQuantities();
		if($value>=0){
			if($value==0){
				return $this->getNoQuantity($substantive);
			} else {
				if($value==1){
					return $this->a($substantive);
				} else {
					if($value<29){
						return $this->getSeveral($substantive);
					} else {
						$prepositions = $this->_locale->getLinguisticPrepositions();
						if($value<199){
							return $quantities['dozens'].' '.$prepositions['of'].' '.$this->pluralize($substantive);
						} else {
							if($value<1999){
								return $quantities['hundreds'].' '.$prepositions['of'].' '.$this->pluralize($substantive);
							} else {
								if($value<19999){
									return $quantities['thousands'].' '.$prepositions['of'].' '.$this->pluralize($substantive);
								} else {
									return $quantities['millions'].' '.$prepositions['of'].' '.$this->pluralize($substantive);
								}
							}
						}
					}
				}
			}
			return '';
		} else {
			if($value==-1){
				return $quantities['minus'].' '.$this->a($substantive);
			} else {
				return $quantities['minus'].' '.abs($value).' '.$substantive;
			}
		}
	}

	/**
	 * Las siguientes funciones son utilizadas para la generación
	 * de versiones escritas de numeros
	 *
	 * @param numeric $a
	 * @return string
	 * @static
	 */
	private static function valueNumber($a){
		if($a<=21){
			switch ($a){
				case 1:
					if(self::$_state=='DEC'||self::$_state==''){
						return 'UN';
					} else {
						return 'UNO';
					}
				case 2: return 'DOS';
				case 3: return 'TRES';
				case 4: return 'CUATRO';
				case 5: return 'CINCO';
				case 6: return 'SEIS';
				case 7: return 'SIETE';
				case 8: return 'OCHO';
				case 9: return 'NUEVE';
				case 10: return 'DIEZ';
				case 11: return 'ONCE';
				case 12: return 'DOCE';
				case 13: return 'TRECE';
				case 14: return 'CATORCE';
				case 15: return 'QUINCE';
				case 16: return 'DIECISEIS';
				case 17: return 'DIECISIETE';
				case 18: return 'DIECIOCHO';
				case 19: return 'DIECINUEVE';
				case 20: return 'VEINTE';
				case 21:
					if(self::$_state==''){
						return 'VENTIUNO';
					} else {
						return 'VENTIUN';
					}
			}
		} else {
			if($a<=99){
				self::$_state = 'DEC';
				if($a>=22&&$a<=29){
					return 'VENTI'.self::valueNumber($a % 10);
				}
				if($a==30){
					return  'TREINTA';
				}
				if($a>=31&&$a<=39){
					return 'TREINTA Y '.self::valueNumber($a % 10);
				}
				if($a==40){
					return 'CUARENTA';
				}
				if($a>=41&&$a<=49){
					return 'CUARENTA Y '.self::valueNumber($a % 10);
				}
				if($a==50){
					return 'CINCUENTA';
				}
				if($a>=51&&$a<=59){
					return 'CINCUENTA Y '.self::valueNumber($a % 10);
				}
				if($a==60){
					return 'SESENTA';
				}
				if($a>=61&&$a<=69){
					return 'SESENTA Y '.self::valueNumber($a % 10);
				}
				if($a==70) {
					return 'SETENTA';
				}
				if($a>=71&&$a<=79){
					return 'SETENTA Y '.self::valueNumber($a % 10);
				}
				if($a==80){
					return 'OCHENTA';
				}
				if($a>=81&&$a<=89){
					return 'OCHENTA Y '.self::valueNumber($a % 10);
				}
				if($a==90){
					return 'NOVENTA';
				}
				if($a>=91&&$a<=99){
					return 'NOVENTA Y '.self::valueNumber($a % 10);
				}
			} else {
				self::$_state = 'CEN';
				if($a==100){
					return 'CIEN';
				}
				if($a>=101&&$a<=199){
					return 'CIENTO '.self::valueNumber($a % 100);
				}
				if($a>=200&&$a<=299){
					return 'DOSCIENTOS '.self::valueNumber($a % 100);
				}
				if($a>=300&&$a<=399){
					return 'TRECIENTOS '.self::valueNumber($a % 100);
				}
				if($a>=400&&$a<=499){
					return 'CUATROCIENTOS '.self::valueNumber($a % 100);
				}
				if($a>=500&&$a<=599){
					return 'QUINIENTOS '.self::valueNumber($a % 100);
				}
				if($a>=600&&$a<=699){
					return 'SEICIENTOS '.self::valueNumber($a % 100);
				}
				if($a>=700&&$a<=799){
					return 'SETECIENTOS '.self::valueNumber($a % 100);
				}
				if($a>=800&&$a<=899){
					return 'OCHOCIENTOS '.self::valueNumber($a % 100);
				}
				if($a>=901&&$a<=999){
					return 'NOVECIENTOS '.self::valueNumber($a % 100);
				}
			}
		}
	}

	/**
	 * Genera una cadena de millones
	 *
	 * @param double $a
	 * @return string
	 * @static
	 */
	private static function millions($number){
		self::$_state = 'MILL';
		$number = LocaleMath::div($number, '1000000');
		if($number==1){
			return 'UN MILLON ';
		} else {
			if(LocaleMath::cmp($number, '1000')>=0){
				$mod = LocaleMath::mod($number, '1000');
				$value = self::miles(LocaleMath::sub($number, $mod));
				if($mod>0){
					$value.= self::valueNumber($mod);
				}
				$value.=' MILLONES ';
			} else {
				$value = self::valueNumber($number).' MILLONES ';
			}
			self::$_state = 'MILL';
			return $value;
		}
	}

	/**
	 * Genera una cadena de miles
	 *
	 * @param	double $a
	 * @return	string
	 * @static
	 */
	private static function miles($number){
		self::$_state = 'MIL';
		$number = LocaleMath::div($number, '1000');
		if($number==1){
			return 'MIL';
		} else {
			return self::valueNumber($number).'MIL ';
		}
	}

	/**
	 * Escribe en letras un monto numérico
	 *
	 * @param	numeric $valor
	 * @param	string $moneda
	 * @param	string $centavos
	 * @return	string
	 * @static
	 */
	static public function getNumberToWords($valor, $moneda='', $centavos=''){
		self::$_value = $valor;
		$a = $valor;
		$p = $moneda;
		$c = $centavos;
		$val = '';
		$v = $a;
		$a = LocaleMath::round($a, 0);
		$d = (float) LocaleMath::round($v-$a, 2);
		if(LocaleMath::cmp($a, '1000000')>=0){
			$mod = LocaleMath::mod($a, '1000000');
			$val.= self::millions(LocaleMath::sub($a, $mod));
			$a = $mod;
		}
		if(LocaleMath::cmp($a, '1000')>=0){
			$mod = LocaleMath::mod($a, '1000');
			$val.= self::miles(LocaleMath::sub($a, $mod));
			$a = $mod;
		}
		$rval = self::valueNumber($a);
		if($rval==''){
			if(in_array(self::$_state, array('MILL', 'MMILL'))){
				$val.= 'DE '.strtoupper($p).' ';
			} else {
				$val.= strtoupper($p).' ';
			}
		} else {
			$val.= $rval.' '.strtoupper($p).' ';
		}
		if($d>0){
			$d*=100;
			$val.= ' CON '.self::valueNumber($d).' '.$c.' ';
		}
		return trim($val);
	}

}
