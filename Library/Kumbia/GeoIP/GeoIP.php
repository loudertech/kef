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
 * @package		GeoIP
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * GeoIP
 *
 * Geolocation es la identificación de ubicaciones del mundo real apartir
 * de los segmentos de IPs previamente asignados a cada país.
 *
 * @category	Kumbia
 * @package		GeoIP
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @abstract
 */
class GeoIP extends Object {

	/**
	 * Instancia de GeoIP
	 *
	 * @var GeoIP
	 */
	private static $_instance = null;

	/**
	 * Nombres de Paises
	 *
	 * @var array
	 */
	private static $_countries = array(
		'',
		'Afghanistan',
		'Aland Islands',
		'Albania',
		'Algeria',
		'American Samoa',
		'Andorra',
		'Angola',
		'Anguilla',
		'Anonymous Proxy',
		'Antarctica',
		'Antigua and Barbuda',
		'Argentina',
		'Armenia',
		'Aruba',
		'Asia/Pacific Region',
		'Australia',
		'Austria',
		'Azerbaijan',
		'Bahamas',
		'Bahrain',
		'Bangladesh',
		'Barbados',
		'Belarus',
		'Belgium',
		'Belize',
		'Benin',
		'Bermuda',
		'Bhutan',
		'Bolivia',
		'Bosnia and Herzegovina',
		'Botswana',
		'Bouvet Island',
		'Brazil',
		'British Indian Ocean Territory',
		'Brunei Darussalam',
		'Bulgaria',
		'Burkina Faso',
		'Burundi',
		'Cambodia',
		'Cameroon',
		'Canada',
		'Cape Verde',
		'Cayman Islands',
		'Central African Republic',
		'Chad',
		'Chile',
		'China',
		'Christmas Island',
		'Cocos (Keeling) Islands',
		'Colombia',
		'Comoros',
		'Congo',
		'Congo, The Democratic Republic of the',
		'Cook Islands',
		'Costa Rica',
		'Cote D\'Ivoire',
		'Croatia',
		'Cuba',
		'Cyprus',
		'Czech Republic',
		'Denmark',
		'Djibouti',
		'Dominica',
		'Dominican Republic',
		'Ecuador',
		'Egypt',
		'El Salvador',
		'Equatorial Guinea',
		'Eritrea',
		'Estonia',
		'Ethiopia',
		'Europe',
		'Falkland Islands (Malvinas)',
		'Faroe Islands',
		'Fiji',
		'Finland',
		'France',
		'France, Metropolitan',
		'French Guiana',
		'French Polynesia',
		'French Southern Territories',
		'Gabon',
		'Gambia',
		'Georgia',
		'Germany',
		'Ghana',
		'Gibraltar',
		'Greece',
		'Greenland',
		'Grenada',
		'Guadeloupe',
		'Guam',
		'Guatemala',
		'Guernsey',
		'Guinea',
		'Guinea Bissau',
		'Guyana',
		'Haiti',
		'Heard Island and McDonald Islands',
		'Holy See (Vatican City State)',
		'Honduras',
		'Hong Kong',
		'Hungary',
		'Iceland',
		'India',
		'Indonesia',
		'Iran, Islamic Republic of',
		'Iraq',
		'Ireland',
		'Isle of Man',
		'Israel',
		'Italy',
		'Jamaica',
		'Japan',
		'Jersey',
		'Jordan',
		'Kazakhstan',
		'Kenya',
		'Kiribati',
		'Korea, Democratic People\'s Republic of',
		'Korea, Republic of',
		'Kuwait',
		'Kyrgyzstan',
		'Lao People\'s Democratic Republic',
		'Latvia',
		'Lebanon',
		'Lesotho',
		'Liberia',
		'Libyan Arab Jamahiriya',
		'Liechtenstein',
		'Lithuania',
		'Luxembourg',
		'Macau',
		'Macedonia',
		'Madagascar',
		'Malawi',
		'Malaysia',
		'Maldives',
		'Mali',
		'Malta',
		'Marshall Islands',
		'Martinique',
		'Mauritania',
		'Mauritius',
		'Mayotte',
		'Mexico',
		'Micronesia, Federated States of',
		'Moldova, Republic of',
		'Monaco',
		'Mongolia',
		'Montenegro',
		'Montserrat',
		'Morocco',
		'Mozambique',
		'Myanmar',
		'Namibia',
		'Nauru',
		'Nepal',
		'Netherlands',
		'Netherlands Antilles',
		'New Caledonia',
		'New Zealand',
		'Nicaragua',
		'Niger',
		'Nigeria',
		'Niue',
		'Norfolk Island',
		'Northern Mariana Islands',
		'Norway',
		'Oman',
		'Other',
		'Pakistan',
		'Palau',
		'Palestinian Territory',
		'Panama',
		'Papua New Guinea',
		'Paraguay',
		'Peru',
		'Philippines',
		'Pitcairn Islands',
		'Poland',
		'Portugal',
		'Puerto Rico',
		'Qatar',
		'Reunion',
		'Romania',
		'Russian Federation',
		'Rwanda',
		'Saint Barthelemy',
		'Saint Helena',
		'Saint Kitts and Nevis',
		'Saint Lucia',
		'Saint Martin',
		'Saint Pierre and Miquelon',
		'Saint Vincent and the Grenadines',
		'Samoa',
		'San Marino',
		'Sao Tome and Principe',
		'Satellite Provider',
		'Saudi Arabia',
		'Senegal',
		'Serbia',
		'Seychelles',
		'Sierra Leone',
		'Singapore',
		'Slovakia',
		'Slovenia',
		'Solomon Islands',
		'Somalia',
		'South Africa',
		'South Georgia and the South Sandwich Islands',
		'Spain',
		'Sri Lanka',
		'Sudan',
		'Suriname',
		'Svalbard and Jan Mayen',
		'Swaziland',
		'Sweden',
		'Switzerland',
		'Syrian Arab Republic',
		'Taiwan',
		'Tajikistan',
		'Tanzania, United Republic of',
		'Thailand',
		'Timor-Leste',
		'Togo',
		'Tokelau',
		'Tonga',
		'Trinidad and Tobago',
		'Tunisia',
		'Turkey',
		'Turkmenistan',
		'Turks and Caicos Islands',
		'Tuvalu',
		'Uganda',
		'Ukraine',
		'United Arab Emirates',
		'United Kingdom',
		'United States',
		'United States Minor Outlying Islands',
		'Uruguay',
		'Uzbekistan',
		'Vanuatu',
		'Venezuela',
		'Vietnam',
		'Virgin Islands, British',
		'Virgin Islands, U.S.',
		'Wallis and Futuna',
		'Western Sahara',
		'Yemen',
		'Zambia'
	);

	/**
	 * Base de datos de segmentos
	 *
	 * @var PDO
	 */
	private $_database;

	/**
	 * Constructor de GeoIP
	 *
	 * @param string $path
	 */
	private function __construct($path=null){
		#if[compile-time]
		if(extension_loaded('pdo_sqlite')==false){
			throw new GeoIPException('Debe cargar la extensión de php pdo_sqlite para usar GeoIP');
		}
		#endif
		if($path==null){
			$path = 'sqlite:Library/Kumbia/GeoIP/geo.db';
		}
		$this->_database = new PDO($path);
		$this->_database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->_database->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
		$this->_database->setAttribute(PDO::ATTR_CURSOR, PDO::CURSOR_FWDONLY);
	}

	/**
	 * Inicializa la instancia de GeoIP en el Singleton
	 *
	 */
	private static function _initialize(){
		if(self::$_instance==null){
			self::$_instance = new self();
		}
	}

	/**
	 * Obtiene el país al que corresponde una determinada IP
	 *
	 * @param	string $ipaddress
	 * @return	string
	 */
	public static function countryByIP($ipaddress){
		self::_initialize();
		$ipnum = ip2long($ipaddress);
		if($ipnum!==false){
			$sql = 'SELECT country_id FROM geodata WHERE segini <= '.$ipnum.' AND segfin >= '.$ipnum;
			$cursor = self::$_instance->_database->query($sql);
			$countryId = $cursor->fetch();
			if(isset(self::$_countries[$countryId['country_id']])){
				return self::$_countries[$countryId['country_id']];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Obtiene el país al que corresponde a un hostname
	 *
	 * @param	string $hostname
	 * @return	string
	 */
	public static function countryByHostname($hostname){
		$ipAddress = gethostbyname($hostname);
		return self::countryByIP($ipAddress);
	}

	/**
	 * Obtiene el país desde donde se accede la aplicación
	 *
	 * @return	string
	 */
	public static function getRequestCountry(){
		$controllerRequest = ControllerRequest::getInstance();
		$ipAddress = $controllerRequest->getClientAddress();
		return self::countryByIP($ipAddress);
	}

}