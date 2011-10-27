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
 * @subpackage	Format
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: DateFormat.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * DateFormat
 *
 * Esta clase es utilizada para aplicar formatos segun identificadores
 * UNICODE a objetos Fecha
 *
 * @category	Kumbia
 * @package		Date
 * @subpackage	Format
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class DateFormat {

	/**
	 * Fecha formateada
	 *
	 * @var string
	 */
	private $_formatedDate;

	/**
	 * Parsea un formato en un array de partes
	 *
	 * @param	string $format
	 * @return	array
	 */
	public static function parseFormat($format){
		$token = "";
		$stoken = "";
		$st = false;
		$formatParts = array();
		$n = strlen($format);
		$tokens = array(
			'Y', 'y', 'm', 'E', 'M', 'd', 'e', 'N', '\'',
			'D', 'L', 'z', 'r', 'c', 't', 'o', 'W',
		);
		$posibleTokens = array(
			'MMMM', 'MMM', 'MM', 'm','mm', 'M', 'EEEE', 'EEE',
			'DD', 'dd', 'z', 'd', 'EE', 'E',
			't', 'e', 'L', 'r', 'o', 'W', 'N',
			'yyy', 'yyyy', 'y', 'YYYY'
		);
		$quote = false;
		for($i=0;$i<$n;++$i){
			$ch = substr($format, $i, 1);
			if(in_array($ch, $tokens)){
				if($ch!='\''){
					if($quote==false){
						if($token!=''){
							$someToken = false;
							foreach($posibleTokens as $stoken){
								#print substr($stoken, 0, strlen($token.$ch)).' '.$token.$ch.'<br>';
								if(substr($stoken, 0, strlen($token.$ch))==$token.$ch){
									$someToken = true;
									break;
								}
							}
							if($someToken==false){
								$formatParts[] = $token;
								$token = $ch;
							} else {
								$token.=$ch;
							}
						} else {
							$token.=$ch;
						}
					} else {
						$token.=$ch;
					}
				} else {
					if($quote==false){
						$quote = true;
					} else {
						$quote = false;
					}
				}
			} else {
				$formatParts[] = $token;
				$formatParts[] = $ch;
				$token = '';
			}
		}
		$formatParts[] = $token;
		return $formatParts;
	}

	/**
	 * Constructor de DateFormat
	 *
	 * @param	string $format
	 */
	public function __construct($format){
		$this->_formatParts = self::parseFormat($format);
	}

	/**
	 * Devuelve la fecha formateada
	 *
	 * @param	Date $date
	 * @return	string
	 */
	public function formatDate(Date $date){
		$defaultPart = false;
		$formatedDate = '';
		foreach($this->_formatParts as $formatPart){
			switch($formatPart){
				case 'MMMM':
					$formatedDate.=$date->getMonthName();
					break;
				case 'MMM':
					$formatedDate.=$date->getAbrevMonthName();
					break;
				case 'MM':
				case 'm':
				case 'mm':
					$formatedDate.=sprintf('%02s', $date->getMonth());
					break;
				case 'M':
					$formatedDate.=$date->getMonth();
					break;
				case 'EEEE':
					$formatedDate.=$date->getDayOfWeek();
					break;
				case 'EEE':
					$formatedDate.=$date->getAbrevDayOfWeek();
					break;
				case 'DD':
					$formatedDate.=$date->getDayOfYear();
					break;
				case 'z':
					$formatedDate.=$date->getDayOfYear();
					break;
				case 'dd':
					$formatedDate.=sprintf('%02s', $date->getDay());
					break;
				case 'd':
					$formatedDate.=$date->getDay();
					break;
				case 'YYYY':
				case 'yyyy':
				case 'y':
					$formatedDate.=$date->getYear();
					break;
				case 'yy':
					$formatedDate.=$date->getShortYear();
					break;
				case 'EE':
				case 'E':
				case 'N':
					$formatedDate.=$date->getDayNumberOfWeek();
					break;
				case 'L':
					$formatedDate.=($date->isLeapYear() ? 1 : 0);
					break;
				case 'r':
					$formatedDate.=$date->getRFC2822Date();
					break;
				case 'c':
					$formatedDate.=$date->getISO8601Date();
					break;
				case 't':
					$formatedDate.=$date->getTimestamp();
					break;
				case 'e':
					$formatedDate.=$date->getTimezone();
					break;
				default:
					$formatedDate.=$formatPart;
					$defaultPart = true;
			}
		}
		return $formatedDate;
	}

	/**
	 * Convierte un string en una fecha según el formato
	 *
	 * @param	string $stringDate
	 * @return	Date
	 */
	public function getDate($stringDate){
		$position = 0;
		$year = null;
		$month = null;
		$day = null;
		foreach($this->_formatParts as $formatPart){
			$length = strlen($formatPart);
			$item = substr($stringDate, $position, $length);
			switch($formatPart){
				case 'MM':
				case 'm':
				case 'mm':
					$month = $item;
					break;
				case 'M':
					$month = $item;
					break;
				case 'DD':
					$day = $item;
					break;
				case 'dd':
					$day = $item;
					break;
				case 'd':
					$day = $item;
					break;
				case 'YYYY':
				case 'yyyy':
				case 'y':
					$year = $item;
					break;
				case 'yy':
					$year = $item;
					break;
			}
			$position+=$length;
		}
		return Date::fromParts($year, $month, $day);
	}

	/**
	 * Obtiene las partes del formato de fecha encontradas
	 *
	 * @return array
	 */
	public function getFormatParts(){
		return $this->_formatParts;
	}

}
