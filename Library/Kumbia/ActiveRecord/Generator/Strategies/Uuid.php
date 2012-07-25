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
 * @subpackage	Generator
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Uuid.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * UuidGenerator
 *
 * Genera identificadores UUID (Universal Unique Identifier)
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	Generator
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) Marius Karthaus
 * @license		New BSD License
 * @link		http://tools.ietf.org/html/rfc4122#section-4.1.3
 * @link		http://en.wikipedia.org/wiki/UUID
 */
class UuidGenerator implements ActiveRecordGeneratorInterface {

	/**
	 * Generador de numeros aleatorios
	 *
	 * @var string
	 */
	protected $urand;

	/**
	 * Constructor de UuidGenerator
	 *
	 */
	public function __construct(){
		$this->urand = @fopen('/dev/urandom', 'rb');
	}

	/**
	 * Establece las opciones del generador
	 *
	 * @param array $options
	 */
	public function setOptions($options){

	}

	/**
	 * Genera un UUID
	 *
	 * @return unknown
	 */
	public function generateUUID(){
		$pr_bits = false;
		if(is_resource($this->urand)){
			$pr_bits .= @fread($this->urand, 16);
		}
		if(!$pr_bits){
			$fp = @fopen('/dev/urandom', 'rb');
			if($fp!==false){
				$pr_bits.=@fread($fp,16);
				@fclose($fp);
			} else {
				// If /dev/urandom isn't available (eg: in non-unix systems), use mt_rand().
				$pr_bits="";
				for($cnt=0;$cnt<16;++$cnt){
					$pr_bits.=chr(mt_rand(0, 255));
				}
			}
		}
		$time_low = bin2hex(substr($pr_bits, 0, 4));
		$time_mid = bin2hex(substr($pr_bits, 4, 2));
		$timeHiAndVersion = bin2hex(substr($pr_bits, 6, 2));
		$clockSeqHiAndReserved = bin2hex(substr($pr_bits, 8, 2));
		$node = bin2hex(substr($pr_bits, 10, 6));

		/**
         * Set the four most significant bits (bits 12 through 15) of the
         * timeHiAndVersion field to the 4-bit version number from
         * Section 4.1.3.
         * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
         */
		$timeHiAndVersion = hexdec($timeHiAndVersion);
		$timeHiAndVersion = $timeHiAndVersion >> 4;
		$timeHiAndVersion = $timeHiAndVersion | 0x4000;

		/**
         * Set the two most significant bits (bits 6 and 7) of the
         * clockSeqHiAndReserved to zero and one, respectively.
         */
		$clockSeqHiAndReserved = hexdec($clockSeqHiAndReserved);
		$clockSeqHiAndReserved = $clockSeqHiAndReserved >> 2;
		$clockSeqHiAndReserved = $clockSeqHiAndReserved | 0x8000;

		return sprintf('%08s-%04s-%04x-%04x-%012s', $time_low, $time_mid, $timeHiAndVersion, $clockSeqHiAndReserved, $node);
	}

	/**
	 * Establece el nombre de la columna identidad
	 *
	 * @param string $identityColumn
	 */
	public function setIdentityColumn($identityColumn){
		$this->_identityColumn = $identityColumn;
	}

	/**
	 * Objeto que solicita el identificador
	 *
	 * @param ActiveRecord $record
	 */
	public function setIdentifier($record){
		$record->writeAttribute($this->_identityColumn, $this->generateUUID());
	}

	/**
	 * Actualiza el consecutivo
	 *
	 * @return boolean
	 */
	public function updateConsecutive($record){

	}

	/**
	 * Finaliza el generador
	 *
	 */
	public function finalizeConsecutive(){

	}

}
