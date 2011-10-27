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
 * @package		Date
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2008-2009 Oscar Garavito (game013@gmail.com)
 * @copyright	Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license		New BSD License
 * @version 	$Id: Date.php 117 2009-12-11 21:09:16Z game013 $
 */

/**
 * Date
 *
 * El componente Date esta diseñado para extender el lenguaje PHP agregando
 * un tipo de dato para el manejo de fechas de forma orientada a
 * objetos que permita las operaciones entre estas, obtener fragmentos e
 * información de las propiedades del tiempo teniendo en cuenta la
 * configuración de localización requerida por la aplicación.
 *
 * Este componente esta basado en la clase DateObject de Zend Framework.
 *
 * @category	Kumbia
 * @package		Date
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2008-2009 Oscar Garavito (game013@gmail.com)
 * @copyright	Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license		New BSD License
 * @access		public
 */
class Date extends Object {

	/**
	 * Valor interno de fecha
	 *
	 * @var string
	 */
	private $_date;

	/**
	 * Valor interno del Dia
	 *
	 * @var string
	 */
	private $_day;

	/**
	 * Valor interno del Año
	 *
	 * @var string
	 */
	private $_year;

	/**
	 * Valor interno del Mes
	 *
	 * @var string
	 */
	private $_month;

	/**
	 * Valor interno del Mes
	 *
	 * @var string
	 */
	private $_timestamp;

	/**
	 * Localización actual del objeto
	 *
	 * @var Locale
	 */
	private $_locale;

	/**
	 * Intervalo para Segundos
	 *
	 */
	const INTERVAL_SECOND = -3;

	/**
	 * Intervalo para Minutos
	 *
	 */
	const INTERVAL_MINUTE = -2;

	/**
	 * Intervalo para Horas
	 *
	 */
	const INTERVAL_HOUR = -1;

	/**
	 * Intervalo para Dias
	 *
	 */
	const INTERVAL_DAY = 0;

	/**
	 * Intervalo para Meses
	 *
	 */
	const INTERVAL_MONTH = 1;

	/**
	 * Intervalo para Años
	 *
	 */
	const INTERVAL_YEAR = 2;

	/**
	 * Intervalo para Semanas
	 *
	 */
	const INTERVAL_WEEK = 3;

	/**
     * Tabla del número de días de cada mes
     *
     * @var array
     */
    private static $_monthTable = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    /**
     * Tabla de Años
     *
     * @var array
     */
    private static $_yearTable = array(
		1970 => 0,            1960 => -315619200,   1950 => -631152000,
		1940 => -946771200,   1930 => -1262304000,  1920 => -1577923200,
		1910 => -1893456000,  1900 => -2208988800,  1890 => -2524521600,
		1880 => -2840140800,  1870 => -3155673600,  1860 => -3471292800,
		1850 => -3786825600,  1840 => -4102444800,  1830 => -4417977600,
		1820 => -4733596800,  1810 => -5049129600,  1800 => -5364662400,
		1790 => -5680195200,  1780 => -5995814400,  1770 => -6311347200,
		1760 => -6626966400,  1750 => -6942499200,  1740 => -7258118400,
		1730 => -7573651200,  1720 => -7889270400,  1710 => -8204803200,
		1700 => -8520336000,  1690 => -8835868800,  1680 => -9151488000,
		1670 => -9467020800,  1660 => -9782640000,  1650 => -10098172800,
		1640 => -10413792000, 1630 => -10729324800, 1620 => -11044944000,
		1610 => -11360476800, 1600 => -11676096000
	);

	/**
	 * Crea un objeto de fecha Date
	 *
	 * @param	string $date
	 * @param	boolean $fixDate
	 * @param	Locale $locale
	 */
	public function __construct($date="", $fixDate=false, Locale $locale=null){
		if(is_object($date)){
			$date = (string) $date;
		} else {
			if(!$date){
				$date = date('Y-m-d');
			}
		}
		$dateParts = preg_split('#[/-]#', $date);
		if(!isset($dateParts[1])){
			throw new DateException("La fecha '$date' es inválida");
		}
		$this->_year = (int) $dateParts[0];
		$this->_month = (int) $dateParts[1];
		$this->_day = (int) $dateParts[2];
		if($this->_day>31||$this->_month>12){
			throw new DateException("La fecha '$date' es inválida");
		}
		if(isset(self::$_monthTable[$this->_month-1])){
			$monthDays = self::$_monthTable[$this->_month-1];
		} else {
			throw new DateException("La fecha '$date' es inválida");
		}
		if($this->isLeapYear()==true&&$this->_month==2){
			++$monthDays;
		}
		if($this->_day>$monthDays){
			if($fixDate==false){
				throw new DateException("La fecha '$date' es invalida");
			} else {
				$this->_day = self::$_monthTable[$this->_month-1];
			}
		}
		$this->_mktime();
		$this->_setDate();
		if($locale!=null){
			$this->_locale = $locale;
		}
	}

	/**
	 * Establece la localizacion del objeto Fecha
	 *
	 * @param Locale $locale
	 */
	public function setLocale(Locale $locale){
		$this->_locale = $locale;
	}

	/**
	 * Obtiene la localización de la fecha, si no se ha definido una usa la del sistema
	 *
	 * @return Locale
	 */
	public function getLocale(){
		if($this->_locale==null){
			$locale = Locale::getApplication();
			$this->_locale = $locale;
			return $locale;
		} else {
			return $this->_locale;
		}
	}

	/**
	 * Establece la fecha en el formato interno YYYY-MM-DD
	 *
	 */
	private function _setDate(){
		if($this->_year>=1970&&$this->_year<=2038){
			$this->_day = date('d', $this->_timestamp);
			$this->_month = date('m', $this->_timestamp);
			$this->_year = date('Y', $this->_timestamp);
		} else {
			$dateParts = self::_getDateParts($this->_timestamp, true);
			$this->_day = sprintf('%02s', $dateParts['mday']);
			$this->_month = sprintf('%02s', $dateParts['mon']);
			$this->_year = $dateParts['year'];
		}
		$this->_date = $this->_year.'-'.$this->_month.'-'.$this->_day;
	}

	/**
	 * Genera una marca de tiempo
	 *
	 * @param	int $month
	 * @param	int $day
	 * @param	int $year
	 * @return	int
	 */
	static public function mktime($month, $day, $year){
		if(!$year){
			throw new DateException('Las partes de la fecha son invalidas');
		}
		if($year>=1970&&$year<=2038){
			return mktime(0, 0, 0, $month, $day, $year);
		} else {
			// From Zend Framework DateObject _mktime
			$hour = 0;
			$minute = 0;
			$second = 0;
			$date = 0;
			if($year<1970){
				for($count=1969;$count>=$year;$count--){
	                $leapyear = self::isYearLeapYear($count);
	                if($count>$year){
	                    $date += 365;
	                    if($leapyear===true){
	                        ++$date;
	                    }
	                } else {
	                    for($mcount=11;$mcount>($month-1);$mcount--){
	                        $date += self::$_monthTable[$mcount];
	                        if(($leapyear===true)&&($mcount==1)){
	                            ++$date;
	                        }
	                    }
	                }
	            }
	            $date += (self::$_monthTable[$month-1]-$day);
                $date = self::isYearLeapYear($year) && $month == 2 ? $date + 1 : $date;
	            $date = -(($date*86400)+(86400-(($hour*3600)+($minute*60)+$second)));

	            // gregorian correction for 5.Oct.1582
	            if($date<-12220185600){
	                $date+=864000;
	            } else {
	            	if($date<-12219321600){
	                	$date=-12219321600;
	            	}
	            }
			} else {
				for($count=1970;$count<=$year;++$count){
					$leapyear = self::isYearLeapYear($count);
					if($count<$year){
						$date += 365;
						if($leapyear===true){
							++$date;
						}
					} else {
						for($mcount=0;$mcount<($month-1);++$mcount){
							$date += self::$_monthTable[$mcount];
							if(($leapyear===true)&&($mcount==1)){
								++$date;
							}
						}
					}
				}
				$date+=$day-1;
				$date=(($date*86400)+($hour*3600)+($minute*60)+$second);
			}
			return $date;
		}
	}

	/**
	 * Calcula el timestamp de una fecha
	 *
	 * @param	int $month
	 * @param	int $day
	 * @param	int $year
	 * @return	string
	 */
	private function _mktime(){
		$this->_timestamp = self::mktime($this->_month, $this->_day, $this->_year);
	}

	/**
	 * Si un mes es biciesto
	 *
	 * @param integer $year
	 * @return boolean
	 */
	protected static function isYearLeapYear($year){
		//basado en Zend Framework Date
        // all leapyears can be divided through 4
        if(($year%4)!=0){
            return false;
        }
        // all leapyears can be divided through 400
        if($year%400==0){
            return true;
        } else {
        	if(($year>1582)&&($year%100==0)){
            	return false;
        	}
        }
        return true;
    }

	/**
	 * Establece el dia de la fecha
	 *
	 * @param int $day
	 */
	public function setDay($day){
		$this->_day = (int) $day;
		$monthDays = self::$_monthTable[$this->_month-1];
		if($this->isLeapYear()==true&&$this->_month==2){
			++$monthDays;
		}
		if($this->_day>$monthDays){
			$this->_day = self::$_monthTable[$this->_month-1];
		}
		$this->_mktime();
		$this->_setDate();
	}

	/**
	 * Establece el mes de la fecha
	 *
	 * @param int $month
	 */
	public function setMonth($month){
		if($month>12){
			$this->_month = $month%12;
			$this->_year += ((int)($month/12));
		} else {
			if($month<1){
				$this->_month = 13-(abs($month)%12);
				$this->_year -= (((int)(abs($month)/12))+1);
			} else {
				$this->_month = (int) $month;
			}
		}
		$monthDays = self::$_monthTable[$this->_month-1];
		if($this->isLeapYear()==true&&$this->_month==2){
			++$monthDays;
		}
		if($this->_day>$monthDays){
			$this->_day = self::$_monthTable[$this->_month-1];
		}
		$this->_mktime();
		$this->_setDate();
	}

	/**
	 * Establece el año de la fecha
	 *
	 * @param int $year
	 */
	public function setYear($year){
		$this->_year = (int) $year;
		$monthDays = self::$_monthTable[$this->_month-1];
		if($this->isLeapYear()==true&&$this->_month==2){
			++$monthDays;
		}
		if($this->_day>$monthDays){
			$this->_day = self::$_monthTable[$this->_month-1];
		}
		$this->_mktime();
		$this->_setDate();
	}

	/**
	 * Devuelve el dia interno de la fecha
	 *
	 * @return string
	 */
	public function getDay(){
		return $this->_day;
	}

	/**
	 * Devuelve la fecha interna
	 *
	 * @return string
	 */
	public function getDate(){
		return $this->_date;
	}

	/**
	 * Devuelve el mes interno de la fecha
	 *
	 * @return string
	 */
	public function getMonth(){
		return $this->_month;
	}

	/**
	 * Devuelve el a#o interno de la fecha
	 *
	 * @return string
	 */
	public function getYear(){
		return $this->_year;
	}

	/**
	 * Devuelve el timestamp de la fecha interna
	 *
	 */
	public function getTimestamp(){
		return $this->_timestamp;
	}

	/**
	 * Devuelve el nombre del mes de la fecha interna
	 *
	 * @return string
	 */
	public function getMonthName(){
		if($this->_year>1970&&$this->_year<2038){
			$timestamp = $this->_timestamp;
		} else {
			$timestamp = mktime(0, 0, 0, $this->_month, 1, date('Y'));
		}
		$locale = $this->getLocale();
		$months = $locale->getMonthList();
		return ucfirst($months[$this->_month-1]);
	}

	/**
	 * Devuelve el nombre abreviado del mes de la fecha interna
	 *
	 * @return string
	 */
	public function getAbrevMonthName(){
		if($this->_year>1970&&$this->_year<2038){
			$timestamp = $this->_timestamp;
		} else {
			$timestamp = mktime(0, 0, 0, $this->_month, 1, date('Y'));
		}
		$locale = $this->getLocale();
		$months = $locale->getAbrevMonthList();
		return ucfirst($months[$this->_month-1]);
	}

	/**
	 * Devuelve el nombre del dia de la semana
	 *
	 * @access public
	 * @return string
	 */
	public function getDayOfWeek(){
		$locale = $this->getLocale();
		$days = $locale->getDaysNamesList();
		return ucfirst($days[$this->getDayNumberOfWeek()]);
	}

	/**
	 * Devuelve el nombre del dia de la semana en forma abreviada
	 *
	 * @access public
	 * @return string
	 */
	public function getAbrevDayOfWeek(){
		$locale = $this->getLocale();
		$days = $locale->getAbrevDaysNamesList();
		return ucfirst($days[$this->getDayNumberOfWeek()]);
	}

	/**
	 * Devuelve el numero del dia de la semana
	 *
	 * @access public
	 * @return string
	 */
	public function getDayNumberOfWeek(){
		if($this->_year>1970&&$this->_year<2038){
			return date('w', $this->_timestamp);
		} else {
			$dateParts = self::_getDateParts($this->_timestamp, false);
			return $dateParts['wday'];
		}
	}

	/**
	 * Devuelve el numero del dia en el año
	 *
	 * @access public
	 * @return string
	 */
	public function getDayOfYear(){
		if($this->_year>1970&&$this->_year<2038){
			return date('z', $this->_timestamp);
		} else {
			$dateParts = self::_getDateParts($this->_timestamp, false);
			return $dateParts['yday'];
		}
	}

	/**
	 * Devuelve el año interno de la fecha en formato corto
	 *
	 * @return string
	 */
	public function getShortYear(){
		return substr($this->_year, 2, 2);
	}

	/**
	 * Obtiene la zona horaria de la fecha
	 *
	 * @return
	 */
	public function getTimezone(){
		return Core::getTimezone();
	}

	/**
	 * Obtener usando un formato
	 *
	 * @param	string $format
	 * @return	string
	 */
	public function getUsingFormat($format){
		$formatDate = new DateFormat($format);
		return $formatDate->formatDate($this);
	}

	/**
	 * Obtener usando el formato predeterminado
	 *
	 * @param	string $format
	 * @return	string
	 */
	public function getUsingFormatDefault(){
		$config = CoreConfig::readAppConfig();
		$format = strtolower($config->application->dbdate);
		$dateFormat = new DateFormat($format);
		return $dateFormat->formatDate($this);
	}

	/**
	 * Devuelve la periodo de la fecha YYYYMM
	 *
	 * @return string
	 */
	public function getPeriod(){
		if($this->_year>1970&&$this->_year<2038){
			return date('Ym', $this->_timestamp);
		} else {
			$dateParts = self::_getDateParts($this->_timestamp, false);
			return $dateParts['year'].$dateParts['mon'];
		}
	}

	/**
	 * Obtiene la fecha en el formato adecuado segun la localización
	 *
	 * @param string $type
	 * @return string
	 */
	public function getLocaleDate($type='full'){
		$locale = $this->getLocale();
		$format = $locale->getDateFormat($type);
		$dateFormat = new DateFormat($format);
		return $dateFormat->formatDate($this);
	}

	/**
	 * Devuelve la fecha en formato ISO-8601
	 *
	 * @return string
	 */
	public function getISO8601Date($withUTC=true){
		if($withUTC==true){
			return date('c', $this->_timestamp);
		} else {
			return date("Y-m-d\TH:i:s", $this->_timestamp);
		}
	}

	/**
	 * Devuelve la fecha en formato RFC-2822
	 *
	 * @return string
	 */
	public function getRFC2822Date(){
		//determinar el timezone?
		return $this->getAbrevDayOfWeek().', '.sprintf('%02s', $this->getDay()).' '.
			$this->getAbrevMonthName().' '.$this->getYear().' 00:00:00 +0000';
	}

	/**
	 * Suma meses a la fecha interna
	 *
	 * @param integer $month
	 */
	public function addMonths($month){
		$month = abs($month);
		if($this->_month+$month>12){
			$year = ceil(($month+$this->_month)/12) - 1;
			$this->_month = ($year * 12) - $this->_month + $month;
			$this->_year+= $year;
		} else {
			$this->_month+=$month;
		}
		if($this->_day>30||($this->_day>28&&$this->_month==2)){
			$this->_day = substr(self::getLastDayOfMonth($this->_month, $this->_year),-2);
		}
		$this->_consolideDate();
		return $this->_date;
	}

	/**
	 * Resta meses a la fecha interna
	 *
	 * @access public
	 * @param integer $month
	 */
	public function diffMonths($month){
		$month = abs($month);
		if($this->_month-$month<1){
			$year = floor(($month-$this->_month)/12) + 1;
			$this->_month = $this->_month + ($year * 12) - $month;
			$this->_year-= $year;
		} else {
			$this->_month-=$month;
		}
		if($this->_day>30||($this->_day>28&&$this->_month==2)){
			$this->_day = substr(self::getLastDayOfMonth($this->_month, $this->_year),-2);
		}
		$this->_consolideDate();
		return $this->_date;
	}

	/**
	 * Suma numero dias a la fecha actual
	 *
	 * @param integer $days
	 * @return string
	 */
	public function addDays($days){
		$this->_timestamp = self::_add($this->_timestamp, self::_mul($days, 86400));
		$this->_setDate();
		return $this->_date;
	}

	/**
	 * Resta numero dias a la fecha actual
	 *
	 * @param integer $days
	 * @return string
	 */
	public function diffDays($days){
		$this->_timestamp = self::_sub($this->_timestamp, self::_mul($days, 86400));
		$this->_setDate();
		return $this->_date;
	}

	/**
	 * Suma un numero de a#os a la fecha interna
	 *
	 * @param numeric $years
	 * @return string
	 */
	public function addYears($years){
		$this->_year+=$years;
		$this->_consolideDate();
		return $this->_date;
	}

	/**
	 * Resta un numero de a#os a la fecha interna
	 *
	 * @param numeric $years
	 * @return string
	 */
	public function diffYears($years){
		$this->_year-=$years;
		$this->_consolideDate();
		return $this->_date;
	}

	/**
	 * Resta una fecha de otra y devuelve el número de días de diferencia
	 *
	 * @access	public
	 * @param	string $date
	 */
	public function diffDate($date){
		if(is_object($date) && $date instanceof Date){
			$date = $date->getDate();
		}
		$dateParts = preg_split('#[/-]#', $date);
		if(count($dateParts)!=3){
			throw new DateException('La fecha "'.$date.'" es inválida');
		}
		$year = (int) $dateParts[0];
		$month = (int) $dateParts[1];
		$day = (int) $dateParts[2];
		$timestamp = self::mktime($month, $day, $year);
		return self::_round(self::_div((self::_sub($this->_timestamp, $timestamp)), 86400), 0);
	}

	/**
	 * Asigna el día de la fecha como el primer del mes
	 *
	 * @access	public
	 */
	public function toFirstDayOfMonth(){
		$this->_day = 1;
		$this->_consolideDate();
	}

	/**
	 * Asigna el día de la fecha como el último del mes
	 *
	 * @access	public
	 */
	public function toLastDayOfMonth(){
		$this->_day = self::$_monthTable[$this->_month-1];
		if(self::isYearLeapYear($this->_year)&&$this->_month==2){
			++$this->_day;
		}
		$this->_consolideDate();
	}

	/**
	 * Devuelve true si la fecha interna es la de hoy
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function isToday(){
		if($this->_date==date('Y-m-d')){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Devuelve true si la fecha interna esta en el mes actual
	 *
	 * @return boolean
	 */
	public function isThisMonth(){
		if($this->_month==date('m')&&$this->_year==date('Y')){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Devuelve true si la fecha interna esta en el año actual
	 *
	 * @return boolean
	 */
	public function isThisYear(){
		if($this->_year==date('Y')){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Devuelve true si la fecha interna es la de ayer
	 *
	 * @return boolean
	 */
	public function isYesterday(){
		if(!isset($this->yesterday)){
			$time = self::mktime(date('m'), date('d'), date('Y'));
			$this->yesterday = self::_sub($time, 86400);
		}
		if($this->_timestamp==$this->yesterday){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Devuelve true si la fecha interna es la de mañana
	 *
	 * @return boolean
	 */
	public function isTomorrow(){
		if(!isset($this->_tomorrow)){
			$time = self::mktime(date('m'), date('d'), date('Y'));
			$this->_tomorrow = self::_add($time, 86400);
		}
		if($this->_timestamp==$this->_tomorrow){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Indica si el año interno es biciesto
	 *
	 * @return boolean
	 */
	public function isLeapYear(){
		return self::isYearLeapYear($this->_year);
	}

	/**
	 * Indica si la fecha actual esta en un rango
	 *
	 * @param	string $initialDate
	 * @param	string $finalDate
	 * @return	boolean
	 */
	public function isBetween($initialDate, $finalDate){
		$fecha = $this->getDate();
		$c1 = self::compareDates($fecha, $initialDate);
		$c2 = self::compareDates($fecha, $finalDate);
		return ($c1!=-1)&&($c2!=1);
	}

	/**
	 * Indica si la fecha actual NO esta en un rango
	 *
	 * @param	string $initialDate
	 * @param	string $finalDate
	 * @return	boolean
	 */
	public function isNotBetween($initialDate, $finalDate){
		return !$this->isBetween($initialDate, $finalDate);
	}

	/**
	 * Indica si la fecha esta en el pasado
	 *
	 * @param 	string $referenceDate
	 * @return	boolean
	 */
	public function isPast($referenceDate=''){
		if(!$referenceDate){
			$referenceDate = Date::getCurrentDate();
		}
		$fecha = $this->getDate();
		return self::compareDates($fecha, $referenceDate) == -1 ? true : false;
	}

	/**
	 * Indica si la fecha esta en el futuro
	 *
	 * @param 	string $referenceDate
	 * @return	boolean
	 */
	public function isFuture($referenceDate=''){
		if(!$referenceDate){
			$referenceDate = Date::getCurrentDate();
		}
		$fecha = $this->getDate();
		return self::compareDates($fecha, $referenceDate) == 1 ? true : false;
	}

	/**
	 * Obtiene el rango de fechas de la semana de Lunes a Domingo donde esta ubicada la fecha
	 *
	 * @return array
	 */
	public function getWeekRange(){
		$timestamp = $this->_timestamp;
		$t = self::_sub($timestamp, 86400);
		for($i=$t;$i>0;$i=self::_sub($i, 86400)){
			$array = self::_getDateParts($i, false);
			if($array['wday']==1){
				$initialDate = $array['year'].'-'.sprintf('%02s', $array['mon']).'-'.sprintf('%02s', $array['mday']);
				break;
			}
		}
		$t = self::_add($timestamp, 86400);
		for($i=$t;;$i=self::_add($i, 86400)){
			$array = self::_getDateParts($i, false);
			if($array['wday']==0){
				$finalDate = $array['year'].'-'.sprintf('%02s', $array['mon']).'-'.sprintf('%02s', $array['mday']);
				break;
			}
		}
		return array($initialDate, $finalDate);
	}

	/**
	 * Obtiene un vector con un rango de fechas
	 *
	 * @param string $initialDate
	 * @param string $finalDate
	 */
	static public function getRange($initialDate, $finalDate){
		if(!$initialDate){
			throw new DateException("Fecha inicial invalida");
		}
		if(!$finalDate){
			throw new DateException("Fecha final invalida");
		}
		list($initialDate, $finalDate) = self::orderDates($initialDate, $finalDate);
		if(!is_object($initialDate)){
			$initialDate = new Date($initialDate);
		}
		if(is_object($finalDate)){
			$finalDate = (string) $finalDate;
		}
		$initialTime = $initialDate->getTimestamp();
		$date = (string) $initialDate;
		$range = array($date);
		while($date!=$finalDate){
			$initialTime+=86400;
			$date = date('Y-m-d', $initialTime);
			$range[] = $date;
		}
		return $range;
	}

	/**
	 * Obtiene un vector con un rango de fechas
	 *
	 * @param string $initialDate
	 * @param int $number
	 * @param int $type
	 * @param int $limit
	 */
	static public function getRangeInterval($initialDate, $number, $type, $limit){
		if(!is_object($initialDate)){
			$initialDate = new Date($initialDate);
		}
		$initialTime = $initialDate->getTimestamp();
		$date = (string) $initialDate;
		$range = array($date);
		for($i=0;$i<$limit;$i++){
			$range[] = Date::addInterval($initialDate, $i+1, $type);
		}
		return $range;
	}

	/**
	 * Crear una fecha apartir del año, mes y día
	 *
	 * @param	int $year
	 * @param	int $month
	 * @param	int $day
	 * @return	Date
	 */
    static public function fromParts($year, $month, $day){
    	return new Date(sprintf('%4s', $year).'-'.sprintf('%2s', $month).'-'.sprintf('%2s', $day));
    }

    /**
     * Crea una fecha apartir de un determinado formato
     *
     * @param	string $date
     * @param	string $format
     * @return	Date
     */
    static public function fromFormat($date, $format='YYYY-MM-DD'){
		$format = new DateFormat($format);
		return $format->getDate($date);
    }

	/**
	 * Compara dos fechas, si la primera es menor a la segunda devuelve -1, si
	 * son iguales devuelve 0 y si la primera es mayor a la segunda devuelve 1
	 *
	 * @param	string $initialDate
	 * @param	string $finalDate
	 * @return	integer
	 */
	static public function compareDates($initialDate, $finalDate=''){
		if($finalDate===""){
			$finalDate = self::getCurrentDate();
		}
		$initialDate = new Date($initialDate);
		$finalDate = new Date($finalDate);
		return self::_cmp($initialDate->getTimestamp(), $finalDate->getTimestamp());
	}

	/**
	 * Indica si una fecha es menor a la otra
	 *
	 * @param	string $initialDate
	 * @param	string $finalDate
	 * @return	boolean
	 */
	static public function isEarlier($initialDate, $finalDate){
		return self::compareDates($initialDate, $finalDate) == -1 ? true : false;
	}

	/**
	 * Indica si una fecha es mayor a la otra
	 *
	 * @param	string $initialDate
	 * @param	string $finalDate
	 * @return	boolean
	 */
	static public function isLater($initialDate, $finalDate){
		return self::compareDates($initialDate, $finalDate) == 1 ? true : false;
	}

	/**
	 * Indica si una fecha es igual a la otra
	 *
	 * @param	string $initialDate
	 * @param	string $finalDate
	 * @return	boolean
	 */
	static public function isEquals($initialDate, $finalDate){
		return self::compareDates($initialDate, $finalDate) == 0 ? true : false;
	}

	/**
	 * Consolida los valores internos de la fecha
	 *
	 * @access private
	 */
	private function _consolideDate(){
		$this->_mktime();
		$this->_setDate();
	}

	/**
	 * Devuelve la fecha actual
	 *
	 * @access	public
	 * @param	string $format
	 * @return	string
	 * @static
	 */
	static public function getCurrentDate($format='Y-m-d'){
		return date($format);
	}

	/**
	 * Devuelve la hora actual
	 *
	 * @access	public
	 * @param	string $format
	 * @return	string
	 * @static
	 */
	static public function getCurrentTime($format='H:i:s'){
		return date($format);
	}

	/**
	 * Devuelve la hora actual en formato Y-m-d H:i:s
	 *
	 * @access public
	 * @param string $format
	 * @return string
	 * @static
	 */
	static public function now($format='Y-m-d H:i:s'){
		return date($format);
	}

	/**
	 * Devulve el primer dia del año
	 *
	 * @access public
	 * @param string $year
	 * @return string
	 * @static
	 */
	static public function getFirstDayOfYear($year=''){
		if(!$year){
			$year = date('Y');
		} else {
			$year = (int) $year;
		}
		return $year.'-01-01';
	}

	/**
	 * Devulve el ultimo dia del año
	 *
	 * @access	public
	 * @param	string $year
	 * @return	string
	 * @static
	 */
	static public function getLastDayOfYear($year=''){
		if($year==''){
			$year = date('Y');
		} else {
			$year = (int) $year;
		}
		return $year.'-12-31';
	}

	/**
	 * Devuelve el primer dia del mes
	 *
	 * @param integer $month
	 * @param integer $year
	 */
	static public function getFirstDayOfMonth($month='', $year=''){
		if(!$month){
			$month = date('m');
		}
		if(!$year){
			$year = date('Y');
		}
		return $year.'-'.sprintf('%02s', $month).'-01';
	}

	/**
	 * Devuelve el primer día de la semana
	 *
	 * @param integer $day
	 * @param integer $month
	 * @param integer $year
	 */
	static public function getFirstDayOfWeek($month='', $day='', $year=''){
		if(!$month){
			$month = date('m');
		}
		if(!$day){
			$day = date('d');
		}
		if(!$year){
			$year = date('Y');
		}
		$timestamp = self::mktime($month, $day, $year);
		$array = self::_getDateParts($timestamp, false);
		if($array['wday']==1){
			return $array['year'].'-'.sprintf('%02s', $array['mon']).'-'.sprintf('%02s', $array['mday']);
		} else {
			if($array['wday']>0){
				$timestamp-=(86400*($array['wday']-1));
			} else {
				$timestamp-=(86400*6);
			}
			$array = self::_getDateParts($timestamp, false);
			return $array['year'].'-'.sprintf('%02s', $array['mon']).'-'.sprintf('%02s', $array['mday']);
		}
	}


	/**
	 * Devuelve el último dia de la semana
	 *
	 * @param integer $day
	 * @param integer $month
	 * @param integer $year
	 */
	static public function getLastDayOfWeek($month='', $day='', $year=''){
		if(!$month){
			$month = date('m');
		}
		if(!$day){
			$day = date('d');
		}
		if(!$year){
			$year = date('Y');
		}
		$timestamp = self::mktime($month, $day, $year);
		$array = self::_getDateParts($timestamp, false);
		if($array['wday']==0){
			return $array['year'].'-'.sprintf('%02s', $array['mon']).'-'.sprintf('%02s', $array['mday']);
		} else {
			$timestamp+=(86400*(7-$array['wday']));
			$array = self::_getDateParts($timestamp, false);
			return $array['year'].'-'.sprintf('%02s', $array['mon']).'-'.sprintf('%02s', $array['mday']);
		}
	}

	/**
	 * Devuelve el ultimo dia del mes
	 *
	 * @param	integer $month
	 * @param	integer $year
	 * @return	Date
	 */
	static public function getLastDayOfMonth($month='', $year=''){
		if(!$month){
			$month = date('m');
		}
		if(!$year){
			$year = date('Y');
		}
		$day = self::$_monthTable[$month-1];
		if(self::isYearLeapYear($year)&&$month==2){
			++$day;
		}
		return new Date($year.'-'.sprintf('%02s', $month).'-'.sprintf('%02s', $day));
	}

	/**
	 * Devuelve el número de días que tiene un mes
	 *
	 * @param	int $month
	 * @param	int $year
	 * @return	int
	 */
	static public function getNumberDaysOfMonth($month='', $year=''){
		if(!$month){
			$month = date('m');
		}
		if(!$year){
			$year = date('Y');
		}
		$day = self::$_monthTable[$month-1];
		if(self::isYearLeapYear($year)&&$month==2){
			++$day;
		}
		return $day;
	}

	/**
	 * Devuelve el último dia hábil del año
	 *
	 * @param	integer $month
	 * @param	integer $year
	 * @return	string
	 */
	static public function getLastNonWeekendDayOfYear($year=''){
		if(!$year){
			$year = date('Y');
		}
		$timestamp = self::mktime(12, 31, $year);
		for($i=$timestamp;$i>0;$i = self::_sub($i, 86400)){
			$array = self::_getDateParts($i, false);
			if($array['wday']!=0&&$array['wday']!=6){
				return $array['year'].'-'.sprintf('%02s', $array['mon']).'-'.$array['mday'];
			}
		}
		return null;
	}

	/**
	 * Devuelve el ultimo dia habil del mes
	 *
	 * @param integer $month
	 * @param integer $year
	 */
	static public function getLastNonWeekendDayOfMonth($month, $year=''){
		if(!$year){
			$year = date('Y');
		}
		$lastDay = self::$_monthTable[$month-1];
		if($month==2&&self::isYearLeapYear($year)){
			++$lastDay;
		}
		$timestamp = self::mktime($month, $lastDay, $year);
		for($i=$timestamp;$i>0;$i = self::_sub($i, 86400)){
			$array = self::_getDateParts($i, false);
			if($array['wday']!=0&&$array['wday']!=6){
				return $array['year']."-".sprintf("%02s", $array['mon'])."-".$array['mday'];
			}
		}
		return null;
	}

	/**
	 * Devuelve un objeto fecha sumándole un intervalo
	 *
	 * @access	public
	 * @param	string $date
	 * @param	integer $number
	 * @param	string $type
	 * @return	Date
	 * @static
	 */
	static public function addInterval($date, $number, $type){
		$number = (int) $number;
		if(is_object($date)==false){
			$date = new Date($date);
			$resultDate = $date;
		} else {
			$resultDate = clone $date;
		}
		if($number>0){
			if($type==Date::INTERVAL_DAY){
				$resultDate->addDays($number);
				return $resultDate;
			}
			if($type==Date::INTERVAL_MONTH){
				$resultDate->addMonths($number);
				return $resultDate;
			}
			if($type==Date::INTERVAL_WEEK){
				$resultDate->addDays($number*7);
				return $resultDate;
			}
			if($type==Date::INTERVAL_YEAR){
				$resultDate->addYears($number);
				return $resultDate;
			}
			throw new DateException('Tipo de intervalo inválido');
		} else {
			return $resultDate;
		}
	}

	/**
	 * Resta una fecha de otra y devuelve el número de días de diferencia
	 *
	 * @access	public
	 * @param	mixed $fromDate
	 * @param	mixed $diffDate
	 * @return 	int
	 */
	static public function difference($fromDate, $diffDate){
		if(is_object($fromDate)==false){
			$fromDate = new Date($fromDate);
		}
		if(is_object($diffDate)==false){
			$diffDate = new Date($diffDate);
		}
		return $fromDate->diffDate($diffDate);
	}

	/**
	 * Devuelve un objeto fecha restándole un intervalo
	 *
	 * @access	public
	 * @param	string $date
	 * @param	integer $number
	 * @param	string $type
	 * @return	Date
	 * @static
	 */
	static public function diffInterval($date, $number, $type){
		$number = (int) $number;
		if(is_object($date)==false){
			$date = new Date($date);
			$resultDate = $date;
		} else {
			$resultDate = clone $date;
		}
		if($number>0){
			if($type==Date::INTERVAL_DAY){
				$resultDate->diffDays($number);
				return $resultDate;
			}
			if($type==Date::INTERVAL_MONTH){
				$resultDate->diffMonths($number);
				return $resultDate;
			}
			if($type==Date::INTERVAL_WEEK){
				$resultDate->diffDays($number*7);
				return $resultDate;
			}
			if($type==Date::INTERVAL_YEAR){
				$resultDate->diffYears($number);
				return $resultDate;
			}
			throw new DateException("Tipo de Intervalo invalido");
		} else {
			return $resultDate;
		}
	}

	/**
	 * Crea un objeto fecha apartir de su UNIX timestamp
	 *
	 * @access public
	 * @param int $timestamp
	 * @return string
	 * @static
	 */
	static public function getDateFromTimestamp($timestamp){
		// 32bit timestamp
        if(abs($timestamp)<=0x7FFFFFFF){
			return new Date(date('Y-m-d', $timestamp));
		} else {
			$dateParts = self::_getDateParts($timestamp, true);
			$fecha = $dateParts['year'].'-'.sprintf('%02s', $dateParts['mon']).'-'.sprintf('%02s', $dateParts['mday']);
			return new Date($fecha);
		}
	}

	/**
	 * Crea un objeto fecha apartir de su UNIX timestamp (alias de getDateFromTimestamp)
	 *
	 * @access	public
	 * @param	int $timestamp
	 * @return	string
	 * @static
	 */
	static public function fromTimestamp($timestamp){
		return self::getDatefromTimestamp($timestamp);
	}

	/**
	 * Crea una fecha apartir de un formato RFC822
	 *
	 * @param string $rfcDate
	 * @return Date
	 * @static
	 */
	static public function getDateFromRFC822($rfcDate){
		$day = substr($rfcDate, 5, 2);
		$monthName = substr($rfcDate, 8, 3);
		$year = substr($rfcDate, 12, 4);
		switch($monthName){
			case 'Jan':
				$month = '01';
				break;
			case 'Feb':
				$month = '02';
				break;
			case 'Mar':
				$month = '03';
				break;
			case 'Apr':
				$month = '04';
				break;
			case 'May':
				$month = '05';
				break;
			case 'Jun':
				$month = '06';
				break;
			case 'Jul':
				$month = '07';
				break;
			case 'Aug':
				$month = '08';
				break;
			case 'Sep':
				$month = '09';
				break;
			case 'Oct':
				$month = '10';
				break;
			case 'Nov':
				$month = '11';
				break;
			case 'Dic':
				$month = '12';
				break;
			default:
				throw new DateException('La fecha no tiene un formato RFC822 correcto');
		}
		return new Date($year.'-'.$month.'-'.$day);
	}

	/**
	 * Crea una fecha apartir de un string datetime
	 *
	 * @param string $datetime
	 * @return Date
	 * @static
	 */
	static public function fromDateTime($datetime){
		$dateParts = explode(' ', $datetime);
		return new Date($dateParts[0]);
	}

	/**
     * Devuelve el dia de la semana para una fecha del calendario gregoriano Gregorian
     * 0 = domingo, 6 = sabado
     *
     * @param integer $year
     * @param integer $month
     * @param integer $day
     * @return integer dayOfWeek
     */
    protected static function dayOfWeek($year, $month, $day){
        if((1901<$year)&&($year<2038)){
            return (int) date('w', mktime(0, 0, 0, $month, $day, $year));
        }
        // gregorian correction
        $correction = 0;
        if(($year<1582)||(($year==1582)&&(($month<10)||(($month==10)&&($day<15))))){
            $correction = 3;
        }
        if($month>2){
            $month-=2;
        } else {
            $month+=10;
            $year--;
        }
        $day = floor((13*$month-1)/5)+$day+($year%100)+floor(($year%100)/4);
        $day += floor(($year/100)/4)-2*floor($year/100)+77+$correction;
        return (int) ($day-7*floor($day/7));
    }

	/**
	 * Obtiene las partes de una fecha
	 *
	 * @param string $fast
	 * @return array
	 */
	protected static function _getDateParts($timestamp, $fast = null){

		// 32bit timestamp
        if($timestamp >= 0 && abs($timestamp)<=0x7FFFFFFF){
            return @getdate($timestamp);
        }

		$numday = 0;
        $month = 0;
        // gregorian correction
        if($timestamp < -12219321600){
            $timestamp  = self::_sub($timestamp, 864000);
        }

        // timestamp lower 0
        if($timestamp<0){
            $sec = 0;
            $act = 1970;
            // iterate through 10 years table, increasing speed
            foreach(self::$_yearTable as $year => $seconds){
                if($timestamp>=$seconds){
                    $i = $act;
                    break;
                }
                $sec = $seconds;
                $act = $year;
            }
            $timestamp = self::_sub($timestamp, $sec);
            if(!isset($i)){
                $i = $act;
            }

            // iterate the max last 10 years
            do {
                --$i;
                $day = $timestamp;
                $timestamp = self::_add($timestamp, 31536000);
                $leapyear = self::isYearLeapYear($i);
                if($leapyear===true){
                    $timestamp = self::_add($timestamp, 86400);
                }
                if($timestamp>=0){
                    $year = $i;
                    break;
                }
            } while($timestamp<0);
            $secondsPerYear = 86400 * ($leapyear ? 366 : 365) + $day;
            $timestamp = $day;
            // iterate through months
            for($i=12;--$i>=0;){
                $day = $timestamp;
                $timestamp += self::$_monthTable[$i] * 86400;
                if(($leapyear===true)&&($i==1)){
                    $timestamp = self::_add($timestamp, 86400);
                }
                if($timestamp>=0){
                    $month = $i;
                    $numday = self::$_monthTable[$i];
                    if(($leapyear===true)&&($i==1)){
                        ++$numday;
                    }
                    break;
                }
            }
            $timestamp = $day;
            $numberdays = $numday+ceil(($timestamp+1)/86400);
            $timestamp += ($numday - $numberdays + 1) * 86400;
            $hours = floor($timestamp / 3600);
        } else {

            // iterate through years
            for($i = 1970;;++$i){
                $day = $timestamp;
                $timestamp -= 31536000;
                $leapyear = self::isYearLeapYear($i);
                if($leapyear===true){
                    $timestamp-=86400;
                }
                if($timestamp<0){
                    $year = $i;
                    break;
                }
            }
            $secondsPerYear = $day;
            $timestamp = $day;
            // iterate through months
            for($i=0;$i<=11;++$i){
                $day = $timestamp;
                $timestamp -= self::$_monthTable[$i]*86400;
                if(($leapyear===true)&&($i==1)){
                    $timestamp-=86400;
                }
                if($timestamp<0){
                    $month = $i;
                    $numday = self::$_monthTable[$i];
                    if(($leapyear===true)&&($i==1)){
                        ++$numday;
                    }
                    break;
                }
            }
            $timestamp = $day;
            $numberdays = ceil(($timestamp+1)/86400);
            $timestamp = $timestamp-($numberdays-1)*86400;
            $hours = floor($timestamp/3600);
        }
        $timestamp-=$hours*3600;
        $month+=1;
        $minutes = floor($timestamp/60);
        $seconds = $timestamp-$minutes*60;
        if($fast===true){
            $array = array(
                'seconds' => $seconds,
                'minutes' => $minutes,
                'hours' => $hours,
                'mday' => $numberdays,
                'mon' => $month,
                'year' => $year,
                'yday' => floor($secondsPerYear/86400),
            );
        } else {
            $dayofweek = self::dayOfWeek($year, $month, $numberdays);
            $array = array(
                    'seconds' => $seconds,
                    'minutes' => $minutes,
                    'hours'   => $hours,
                    'mday'    => $numberdays,
                    'wday'    => $dayofweek,
                    'mon'     => $month,
                    'year'    => $year,
                    'yday'    => floor($secondsPerYear/86400),
                    'weekday' => gmdate('l', 86400*(3+$dayofweek)),
                    'month'   => gmdate('F', mktime(0, 0, 0, $month, 1, 1971)),
                    0         => $timestamp
            );
        }
        return $array;
	}

	/**
	 * Devuelve la fecha actual de una zona horaria
	 *
	 * @param string $timezone
	 * @return Date
	 */
	static public function getCurrentDateFromTimezone($timezone){
		$oldTimezone = date_default_timezone_get();
		if(date_default_timezone_set($timezone)==false){
			throw new DateException("Zona horaria invalida");
		}
		$fecha = Date::getCurrentDate();
		date_default_timezone_set($oldTimezone);
		return new Date($fecha);
	}

	/**
	 * Devuelve la fecha actual de una zona horaria
	 *
	 * @param string $timezone
	 * @return Date
	 */
	static public function getNowFromTimezone($timezone){
		$oldTimezone = date_default_timezone_get();
		if(date_default_timezone_set($timezone)==false){
			throw new DateException("Zona horaria invalida");
		}
		$fechaHora = Date::now();
		date_default_timezone_set($oldTimezone);
		return $fechaHora;
	}

	/**
	 * Obtiene un timestamp teniendo en cuenta horas, minutos y segundos
	 *
	 * @param	string $date
	 * @return	int
	 */
	public static function getRealTimestamp($date){
		$dateObject = new Date($date);
		$hour = substr($date, 11, 2);
		$minutes = substr($date, 14, 2);
		$seconds = substr($date, 17, 2);
		return mktime($hour, $minutes, $seconds, $dateObject->getMonth(), $dateObject->getDay(), $dateObject->getYear());
	}

	/**
	 * Devuelve las fechas ordenadas ascendentemente ó descendentemente
	 *
	 * @param	string|Date $initialDate
	 * @param	string|Date $finalDate
	 * @param	boolean $orderAsc
	 * @return 	array
	 */
	public static function orderDates($initialDate, $finalDate, $orderAsc=true){
		if(Date::isEarlier($initialDate, $finalDate)){
			return array($initialDate, $finalDate);
		} else {
			return array($finalDate, $initialDate);
		}
	}

	/**
	 * Devuelve el rango de fechas correspondiente a $number bimestre en el año dado
	 *
	 * @param int $year
	 * @param int $number
	 */
	public static function getTwoMonths($year, $number){
		if($number>0&&$number<7){
			$initialMonth = ($number*2)-1;
			$finalMonth = $number*2;
			return array(Date::getFirstDayOfMonth($initialMonth, $year), Date::getLastDayOfMonth($finalMonth, $year));
		} else {
			throw new DateException('El número del bimestre es inválido');
		}
	}

	/**
	 * Realiza una suma en forma localizada con soporte a números grandes
	 *
	 * @param string $value1
	 * @param string $value2
	 * @return string
	 */
	static private function _add($value1, $value2){
		return LocaleMath::add($value1, $value2);
	}

	/**
	 * Realiza una resta en forma localizada con soporte a numeros grandes
	 *
	 * @param string $value1
	 * @param string $value2
	 * @return string
	 */
	static private function _sub($value1, $value2){
		return LocaleMath::sub($value1, $value2);
	}

	/**
	 * Realiza una multiplicacion en forma localizada con soporte a numeros grandes
	 *
	 * @param string $value1
	 * @param string $value2
	 * @return string
	 */
	static private function _mul($value1, $value2){
		return LocaleMath::mul($value1, $value2);
	}

	/**
	 * Realiza una division en forma localizada con soporte a numeros grandes
	 *
	 * @param string $value1
	 * @param string $value2
	 * @return string
	 */
	static private function _div($value1, $value2){
		return LocaleMath::div($value1, $value2);
	}

	/**
	 * Compara 2 valores
	 *
	 * @param string $value1
	 * @param string $value2
	 * @return string
	 */
	static private function _cmp($value1, $value2){
		return LocaleMath::cmp($value1, $value2);
	}

	/**
	 * Redondea un valor en forma localizada con soporte a numeros grandes
	 *
	 * @param double $value
	 * @param int $scale
	 * @return string
	 */
	static private function _round($value, $scale){
		return LocaleMath::round($value, $scale);
	}

	/**
	 * Desctructor de Date
	 *
	 * @access public
	 */
	public function __destruct(){
		$this->_locale = null;
	}

	/**
	 * Método mágico Sleep de Date
	 *
	 * @access 	public
	 * @return 	array
	 */
	public function __sleep(){
		return array('_date', '_year', '_month', '_day', '_timestamp');
	}

	/**
	 * Método mágico toString de Date
	 *
	 * @access	public
	 * @return	string
	 */
	public function __toString(){
		return (string) $this->_date;
	}


}
