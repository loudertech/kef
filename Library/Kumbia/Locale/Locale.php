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
 * @package 	Locale
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright 	Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license 	New BSD License
 * @version 	$Id: Date.php 117 2009-12-11 21:09:16Z game013 $
 */

/**
 * Locale
 *
 * Proporciona capacidades de localización para aplicaciones Web
 *
 * @category 	Kumbia
 * @package 	Locale
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright 	Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license 	New BSD License
 */
class Locale extends Object {

	/**
	 * Datos de localizacion
	 *
	 * @var array
	 */
	private static $_localeData = array(
        'root'  => true, 'aa_DJ' => true, 'aa_ER' => true, 'aa_ET' => true, 'aa'    => true,
        'af_NA' => true, 'af_ZA' => true, 'af'    => true, 'ak_GH' => true, 'ak'    => true,
        'am_ET' => true, 'am'    => true, 'ar_AE' => true, 'ar_BH' => true, 'ar_DZ' => true,
        'ar_EG' => true, 'ar_IQ' => true, 'ar_JO' => true, 'ar_KW' => true, 'ar_LB' => true,
        'ar_LY' => true, 'ar_MA' => true, 'ar_OM' => true, 'ar_QA' => true, 'ar_SA' => true,
        'ar_SD' => true, 'ar_SY' => true, 'ar_TN' => true, 'ar_YE' => true, 'ar'    => true,
        'as_IN' => true, 'as'    => true, 'az_AZ' => true, 'az'    => true, 'be_BY' => true,
        'be'    => true, 'bg_BG' => true, 'bg'    => true, 'bn_BD' => true, 'bn_IN' => true,
        'bn'    => true, 'bo_CN' => true, 'bo_IN' => true, 'bo'    => true, 'bs_BA' => true,
        'bs'    => true, 'byn_ER'=> true, 'byn'   => true, 'ca_ES' => true, 'ca'    => true,
        'cch_NG'=> true, 'cch'   => true, 'cop_EG'=> true, 'cop_US'=> true, 'cop'   => true,
        'cs_CZ' => true, 'cs'    => true, 'cy_GB' => true, 'cy'    => true, 'da_DK' => true,
        'da'    => true, 'de_AT' => true, 'de_BE' => true, 'de_CH' => true, 'de_DE' => true,
        'de_LI' => true, 'de_LU' => true, 'de'    => true, 'dv_MV' => true, 'dv'    => true,
        'dz_BT' => true, 'dz'    => true, 'ee_GH' => true, 'ee_TG' => true, 'ee'    => true,
        'el_CY' => true, 'el_GR' => true, 'el'    => true, 'en_AS' => true, 'en_AU' => true,
        'en_BE' => true, 'en_BW' => true, 'en_BZ' => true, 'en_CA' => true, 'en_GB' => true,
        'en_GU' => true, 'en_HK' => true, 'en_IE' => true, 'en_IN' => true, 'en_JM' => true,
        'en_MH' => true, 'en_MP' => true, 'en_MT' => true, 'en_NZ' => true, 'en_PH' => true,
        'en_PK' => true, 'en_SG' => true, 'en_TT' => true, 'en_UM' => true, 'en_US' => true,
        'en_VI' => true, 'en_ZA' => true, 'en_ZW' => true, 'en'    => true, 'eo'    => true,
        'es_AR' => true, 'es_BO' => true, 'es_CL' => true, 'es_CO' => true, 'es_CR' => true,
        'es_DO' => true, 'es_EC' => true, 'es_ES' => true, 'es_GT' => true, 'es_HN' => true,
        'es_MX' => true, 'es_NI' => true, 'es_PA' => true, 'es_PE' => true, 'es_PR' => true,
        'es_PY' => true, 'es_SV' => true, 'es_US' => true, 'es_UY' => true, 'es_VE' => true,
        'es'    => true, 'et_EE' => true, 'et'    => true, 'eu_ES' => true, 'eu'    => true,
        'fa_AF' => true, 'fa_IR' => true, 'fa'    => true, 'fi_FI' => true, 'fi'    => true,
        'fil'   => true, 'fo_FO' => true, 'fo'    => true, 'fr_BE' => true, 'fr_CA' => true,
        'fr_CH' => true, 'fr_FR' => true, 'fr_LU' => true, 'fr_MC' => true, 'fr'    => true,
        'fur_IT'=> true, 'fur'   => true, 'ga_IE' => true, 'ga'    => true, 'gaa_GH'=> true,
        'gaa'   => true, 'gez_ER'=> true, 'gez_ET'=> true, 'gez'   => true, 'gl_ES' => true,
        'gl'    => true, 'gu_IN' => true, 'gu'    => true, 'gv_GB' => true, 'gv'    => true,
        'ha_GH' => true, 'ha_NE' => true, 'ha_NG' => true, 'ha'    => true, 'haw_US'=> true,
        'haw'   => true, 'he_IL' => true, 'he'    => true, 'hi_IN' => true, 'hi'    => true,
        'hr_HR' => true, 'hr'    => true, 'hu_HU' => true, 'hu'    => true, 'hy_AM' => true,
        'hy'    => true, 'ia'    => true, 'id_ID' => true, 'id'    => true, 'ig_NG' => true,
        'ig'    => true, 'ii_CN' => true, 'ii'    => true, 'is_IS' => true, 'is'    => true,
        'it_CH' => true, 'it_IT' => true, 'it'    => true, 'iu'    => true, 'ja_JP' => true,
        'ja'    => true, 'ka_GE' => true, 'ka'    => true, 'kaj_NG'=> true, 'kaj'   => true,
        'kam_KE'=> true, 'kam'   => true, 'kcg_NG'=> true, 'kcg'   => true, 'kfo_NG'=> true,
        'kfo'   => true, 'kk_KZ' => true, 'kk'    => true, 'kl_GL' => true, 'kl'    => true,
        'km_KH' => true, 'km'    => true, 'kn_IN' => true, 'kn'    => true, 'ko_KR' => true,
        'ko'    => true, 'kok_IN'=> true, 'kok'   => true, 'kpe_GN'=> true, 'kpe_LR'=> true,
        'kpe'   => true, 'ku_IQ' => true, 'ku_IR' => true, 'ku_SY' => true, 'ku_TR' => true,
        'ku'    => true, 'kw_GB' => true, 'kw'    => true, 'ky_KG' => true, 'ky'    => true,
        'ln_CD' => true, 'ln_CG' => true, 'ln'    => true, 'lo_LA' => true, 'lo'    => true,
        'lt_LT' => true, 'lt'    => true, 'lv_LV' => true, 'lv'    => true, 'mk_MK' => true,
        'mk'    => true, 'ml_IN' => true, 'ml'    => true, 'mn_MN' => true, 'mn'    => true,
        'mr_IN' => true, 'mr'    => true, 'ms_BN' => true, 'ms_MY' => true, 'ms'    => true,
        'mt_MT' => true, 'mt'    => true, 'my_MM' => true, 'my'    => true, 'nb_NO' => true,
        'nb'    => true, 'ne_NP' => true, 'ne'    => true, 'nl_BE' => true, 'nl_NL' => true,
        'nl'    => true, 'nn_NO' => true, 'nn'    => true, 'nr_ZA' => true, 'nr'    => true,
        'nso_ZA'=> true, 'nso'   => true, 'ny_MW' => true, 'ny'    => true, 'om_ET' => true,
        'om_KE' => true, 'om'    => true, 'or_IN' => true, 'or'    => true, 'pa_IN' => true,
        'pa_PK' => true, 'pa'    => true, 'pl_PL' => true, 'pl'    => true, 'ps_AF' => true,
        'ps'    => true, 'pt_BR' => true, 'pt_PT' => true, 'pt'    => true, 'ro_RO' => true,
        'ro'    => true, 'ru_RU' => true, 'ru_UA' => true, 'ru'    => true, 'rw_RW' => true,
        'rw'    => true, 'sa_IN' => true, 'sa'    => true, 'se_FI' => true, 'se_NO' => true,
        'se'    => true, 'sh_BA' => true, 'sh_CS' => true, 'sh_YU' => true, 'sh'    => true,
        'sid_ET'=> true, 'sid'   => true, 'sk_SK' => true, 'sk'    => true, 'sl_SI' => true,
        'sl'    => true, 'so_DJ' => true, 'so_ET' => true, 'so_KE' => true, 'so_SO' => true,
        'so'    => true, 'sq_AL' => true, 'sq'    => true, 'sr_BA' => true, 'sr_CS' => true,
        'sr_ME' => true, 'sr_RS' => true, 'sr_YU' => true, 'sr'    => true, 'ss_ZA' => true,
        'ss'    => true, 'ssy'   => true, 'st_ZA' => true, 'st'    => true, 'sv_FI' => true,
        'sv_SE' => true, 'sv'    => true, 'sw_KE' => true, 'sw_TZ' => true, 'sw'    => true,
        'syr_SY'=> true, 'syr'   => true, 'ta_IN' => true, 'ta'    => true, 'te_IN' => true,
        'te'    => true, 'tg_TJ' => true, 'tg'    => true, 'th_TH' => true, 'th'    => true,
        'ti_ER' => true, 'ti_ET' => true, 'ti'    => true, 'tig_ER'=> true, 'tig'   => true,
        'tn_ZA' => true, 'tn'    => true, 'to_TO' => true, 'to'    => true, 'tr_TR' => true,
        'tr'    => true, 'ts_ZA' => true, 'ts'    => true, 'tt_RU' => true, 'tt'    => true,
        'ug'    => true, 'uk_UA' => true, 'uk'    => true, 'und_ZZ'=> true, 'und'   => true,
        'ur_IN' => true, 'ur_PK' => true, 'ur'    => true, 'uz_AF' => true, 'uz_UZ' => true,
        'uz'    => true, 've_ZA' => true, 've'    => true, 'vi_VN' => true, 'vi'    => true,
        'wal_ET'=> true, 'wal'   => true, 'wo_SN' => true, 'wo'    => true, 'xh_ZA' => true,
        'xh'    => true, 'yo_NG' => true, 'yo'    => true, 'zh_CN' => true, 'zh_HK' => true,
        'zh_MO' => true, 'zh_SG' => true, 'zh_TW' => true, 'zh'    => true, 'zu_ZA' => true,
        'zu'    => true
    );

    /**
	 * Lista del idioma oficial más hablado y la localización de cada país
	 *
	 * @var array
	 */
	private static $_countries = array(
		'Afghanistan' => array('Dar', ''),
		'Albania' => array('Alb', ''),
		'Algeria' => array('Ara', 'ar_DZ'),
		'Andorra' => array('Cat', 'ca'),
		'Angola' => array('Por', 'pt'),
		'Antigua and Barbuda' => array('Eng', 'en'),
		'Argentina' => array('Spa', 'es_AR'),
		'Armenia' => array('Arm', ''),
		'Australia' => array('Eng', 'en_AU'),
		'Austria' => array('Ger', 'de'),
		'Azerbaijan' => array('Aze', ''),
		'Bahamas' => array('Eng', 'en'),
		'Bahrain' => array('Ara', 'ar'),
		'Bangladesh' => array('Ban', ''),
		'Barbados' => array('Eng', 'en'),
		'Belarus' => array('Bel', 'be'),
		'Belgium' => array('Dut', 'fr_BE'),
		'Belize' => array('Eng', 'en_BE'),
		'Benin' => array('Fre', 'fr_BE'),
		'Bhutan' => array('Dzo', ''),
		'Bolivia' => array('Spa', 'es_BO'),
		'Bosnia and Herzegovina' => array('Bos', ''),
		'Botswana' => array('Eng', 'en_BW'),
		'Brazil' => array('Por', 'pt_BR'),
		'Brunei' => array('Mal', 'mt'),
		'Bulgaria' => array('Bul', 'bg'),
		'Burkina Faso' => array('Fre', 'fr'),
		'Burundi' => array('Kir', ''),
		'Cambodia' => array('Khm', ''),
		'Cameroon' => array('Fre', 'fr_CA'),
		'Canada' => array('Fre', 'fr_CA'),
		'Cape Verde' => array('Por', 'pt'),
		'Central African Republic' => array('Fre', 'fr'),
		'Chad' => array('Fre', 'fr_CH'),
		'Chile' => array('Spa', 'es_CL'),
		'China' => array('Sta', 'zh_CN'),
		'Colombia' => array('Spa', 'es_CO'),
		'Comoros' => array('Ara', 'ar'),
		'Congo, Democratic Republic of the' => array('Fre', 'fr'),
		'Congo, Republic of' => array('Fre', 'fr'),
		'Costa Rica' => array('Spa', 'es_CR'),
		'Cote d\'Ivoire' => array('Fre', 'fr'),
		'Croatia' => array('Cro', 'hr_HR'),
		'Cuba' => array('Spa', 'es_ES'),
		'Cyprus' => array('Gre', 'el_CY'),
		'Czech Republic' => array('Cze', 'cs_CZ'),
		'Denmark' => array('Dan', 'da_DK'),
		'Djibouti' => array('Fre', 'fr_FR'),
		'Dominica' => array('Eng', 'en_US'),
		'Dominican Republic' => array('Spa', 'es_DO'),
		'East Timor' => array('Tet', 'en_US'),
		'Ecuador' => array('Spa', 'es_EC'),
		'Egypt' => array('Ara', 'ar_EG'),
		'El Salvador' => array('Spa', 'es_SV'),
		'Equatorial Guinea' => array('Spa', 'es_ES'),
		'Eritrea' => array('Afa', 'aa_ER'),
		'Estonia' => array('Est', 'et_EE'),
		'Ethiopia' => array('Amh', ''),
		'Fiji' => array('Eng', 'en_US'),
		'Finland' => array('Fin', 'fi_FI'),
		'France' => array('Fre', 'fr_FR'),
		'Gabon' => array('Fre', 'fr_FR'),
		'Gambia' => array('Eng', 'en'),
		'Georgia' => array('Geo', 'en_GB'),
		'Germany' => array('Ger', 'de_DE'),
		'Ghana' => array('Eng', 'en_GB'),
		'Greece' => array('Gre', 'el_GR'),
		'Grenada' => array('Eng', 'en_GB'),
		'Guatemala' => array('Spa', 'es_GT'),
		'Guinea' => array('Fre', 'fr_FR'),
		'Guinea-Bissau' => array('Por', 'pt_PT'),
		'Guyana' => array('Eng', 'en_GU'),
		'Haiti' => array('Cre', 'fr_FR'),
		'Honduras' => array('Spa', 'es_HN'),
		'Hungary' => array('Mag', 'hu_HU'),
		'Iceland' => array('Ice', 'is_IS'),
		'India' => array('Hin', 'hi_IN'),
		'Indonesia' => array('Bah', 'en_US'),
		'Iran' => array('Per', 'fa_IR'),
		'Iraq' => array('Ara', 'ar_IQ'),
		'Ireland' => array('Eng', 'en_IE'),
		'Israel' => array('Heb', 'he_IL'),
		'Italy' => array('Ita', 'it_IT'),
		'Jamaica' => array('Eng', 'en_JM'),
		'Japan' => array('Jap', 'jp_JP'),
		'Jordan' => array('Ara', 'ar_JO'),
		'Kazakhstan' => array('Kaz', 'ru_RU'),
		'Kenya' => array('Eng', 'en_US'),
		'Kiribati' => array('Eng', 'en_US'),
		'Korea, North' => array('Kor', 'ko_KR'),
		'Korea, South' => array('Kor', 'ko_KR'),
		'Kuwait' => array('Ara', 'ar'),
		'Kyrgyzstan' => array('Kyr', ''),
		'Laos' => array('Lao', ''),
		'Latvia' => array('Lat', ''),
		'Lebanon' => array('Ara', 'ar'),
		'Lesotho' => array('Eng', 'en_US'),
		'Liberia' => array('Eng', 'en_US'),
		'Libya' => array('Ara', 'ar'),
		'Liechtenstein' => array('Ger', 'de_LI'),
		'Lithuania' => array('Lit', 'lt_LT'),
		'Luxembourg' => array('Lux', 'de_LU'),
		'Macedonia' => array('Mac', ''),
		'Madagascar' => array('Mal', 'mt'),
		'Malawi' => array('Chi', 'zh_ZH'),
		'Malaysia' => array('Bah', ''),
		'Maldives' => array('Mal', 'mt'),
		'Mali' => array('Fre', 'fr_FR'),
		'Malta' => array('Mal', 'mt'),
		'Marshall Islands' => array('Mar', ''),
		'Mauritania' => array('Has', ''),
		'Mauritius' => array('Eng', 'en_US'),
		'Mexico' => array('Spa', 'es_MX'),
		'Micronesia' => array('Eng', 'en_US'),
		'Moldova' => array('Mol', ''),
		'Monaco' => array('Fre', 'fr_FR'),
		'Mongolia' => array('Mon', 'zh_ZH'),
		'Montenegro' => array('Ser', ''),
		'Morocco' => array('Ara', 'ar'),
		'Mozambique' => array('Por', 'pt'),
		'Myanmar' => array('Bur', ''),
		'Namibia' => array('Eng', 'en_NA'),
		'Nauru' => array('Nau', ''),
		'Nepal' => array('Nep', ''),
		'Netherlands' => array('Dut', 'nl_NL'),
		'New Zealand' => array('Eng', 'en_NZ'),
		'Nicaragua' => array('Spa', 'es_NI'),
		'Niger' => array('Fre', 'fr'),
		'Nigeria' => array('Eng', 'en'),
		'Norway' => array('Bok', 'nn_NO'),
		'Oman' => array('Ara', 'ar_OM'),
		'Pakistan' => array('Urd', 'ur_PK'),
		'Palau' => array('Pal', ''),
		'Palestinian State (proposed)' => array('Ara', 'ar'),
		'Panama' => array('Spa', 'es_PA'),
		'Papua New Guinea' => array('Tok', ''),
		'Paraguay' => array('Spa', 'es_PY'),
		'Peru' => array('Spa', 'es_PE'),
		'Philippines' => array('Fil', 'tl_PH'),
		'Poland' => array('Pol', 'pl_PL'),
		'Portugal' => array('Por', 'pt_PT'),
		'Puerto Rico' => array('Spa', 'es_PR'),
		'Qatar' => array('Ara', 'ar_QA'),
		'Romania' => array('Rom', 'ro_RO'),
		'Russia' => array('Rus', 'ru_RU'),
		'Rwanda' => array('Kin', ''),
		'St. Kitts and Nevis' => array('Eng', 'en'),
		'St. Lucia' => array('Eng', 'en'),
		'St. Vincent and the Grenadines' => array('Eng', 'en'),
		'Samoa' => array('Sam', ''),
		'San Marino' => array('Ita', 'it'),
		'Sao Tome and Principe' => array('Por', 'pt'),
		'Saudi Arabia' => array('Ara', 'ar_SA'),
		'Senegal' => array('Fre', 'fr'),
		'Serbia' => array('Ser', 'sr_YU'),
		'Seychelles' => array('Ses', ''),
		'Sierra Leone' => array('Eng', 'en'),
		'Singapore' => array('Man', 'en_SG'),
		'Slovakia' => array('Slo', 'sk_SK'),
		'Slovenia' => array('Slo', 'sl_SI'),
		'Solomon Islands' => array('Eng', 'en'),
		'Somalia' => array('Som', ''),
		'South Africa' => array('Isi', 'af_ZA'),
		'Spain' => array('Spa', 'es_ES'),
		'Sri Lanka' => array('Sin', ''),
		'Sudan' => array('Ara', 'ar_SD'),
		'Suriname' => array('Dut', 'nl'),
		'Swaziland' => array('Eng', 'en'),
		'Sweden' => array('Swe', 'sv'),
		'Switzerland' => array('Ger', 'de_CH'),
		'Syria' => array('Ara', 'ar_SY'),
		'Taiwan' => array('Chi', 'zh_TW'),
		'Tajikistan' => array('Taj', ''),
		'Tanzania' => array('Swa', ''),
		'Thailand' => array('Tha', 'th_TH'),
		'Togo' => array('Fre', 'fr'),
		'Tonga' => array('Ton', 'to_TO'),
		'Trinidad and Tobago' => array('Eng', 'en'),
		'Tunisia' => array('Ara', 'ar'),
		'Turkey' => array('Tur', 'tr'),
		'Turkmenistan' => array('Tur', ''),
		'Tuvalu' => array('Tuv', 'tv'),
		'Uganda' => array('Eng', 'en'),
		'Ukraine' => array('Ukr', 'uk'),
		'United Arab Emirates' => array('Ara', 'ar'),
		'United Kingdom' => array('Eng', 'en_GB'),
		'United States' => array('Eng', 'en_US'),
		'Uruguay' => array('Spa', 'es_UY'),
		'Uzbekistan' => array('Uzb', 'uz_UZ'),
		'Vanuatu' => array('Bis', ''),
		'Vatican City (Holy See)' => array('Ita', 'it'),
		'Venezuela' => array('Spa', 'es_VE'),
		'Vietnam' => array('Vie', 'vi_VN'),
		'Western Sahara (proposed state)' => array('Has', ''),
		'Yemen' => array('Ara', 'ar_YE'),
		'Zambia' => array('Eng', 'en_ZA'),
		'Zimbabwe' => array('Eng', 'en_ZW'),
	);

    /**
     * Localización activa en el objeto
     *
     * @var string
     */
    private $_locale;

    /**
     * Objeto de datos de localización
     *
     * @var LocaleData
     */
    private $_data;

    /**
     * Ultima localizacion activa
     *
     * @var array
     */
    private static $_activeLocale;

	/**
	 * Localización segun el explorador
	 *
	 * @var string
	 */
	private static $_browser;

	/**
	 * Localización segun el entorno de ejecución
	 *
	 * @var string
	 */
	private static $_environ;

	/**
	 * Localización según la aplicación
	 *
	 * @var unknown_type
	 */
	private static $_application;

	/**
	 * Localización por defecto
	 *
	 * @var string
	 */
	private static $_default = 'es_CO';

	/**
	 * Constructor de Locale
	 *
	 * @param string $locale
	 */
	public function __construct($locale=''){
		if(is_string($locale)){
			$this->_locale = self::getLocale($locale);
		} else {
			$this->_locale = $locale;
		}
		self::$_activeLocale = $locale;
	}

	/**
	 * Inicializa la localizacion de la aplicacion
	 *
	 * @static
	 */
	public static function initLocale(){
		Locale::getApplication();
	}

	/**
	 * Indica si la localización cargó un territorio
	 *
	 * @return boolean
	 */
	public function hasCountry(){
		if(isset($this->_locale['country'])){
			if($this->_locale['country']==''){
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * Establece el territorio de la localización
	 *
	 * @param string $country
	 */
	public function setCountry($country){
		if(is_array($this->_locale)){
			$this->_locale = Locale::getLocale($this->_locale['language'].'_'.strtoupper($country));
		} else {
			throw new LocaleException('El objeto no tiene una localización válida');
		}
	}

	/**
	 * Obtiene el lenguaje de la localización
	 *
	 * @return string
	 */
	public function getLanguage(){
		if(isset($this->_locale['language'])){
			return $this->_locale['language'];
		} else {
			return '';
		}
	}

	/**
	 * Obtiene la region de la localizacion
	 *
	 * @return string
	 */
	public function getCountry(){
		if(isset($this->_locale['country'])){
			return $this->_locale['country'];
		} else {
			return '';
		}
	}

	/**
	 * Crea ó devuelve el objeto Locale Data
	 *
	 * @return LocaleData
	 */
	private function _getLocaleData(){
		if(!is_object($this->_data)){
			$this->_data = new LocaleData($this->_locale['language'], $this->_locale['country']);
		}
		return $this->_data;
	}

	/**
	 * Obtiene la traducción de SI en la localización actual
	 *
	 * @param  boolean $all
	 * @return string
	 */
	public function getYesString($all=false){
		$data = $this->_getLocaleData();
		$yesstrList = $data->queryLanguage($this->getLanguage(), '/ldml/posix/messages/yesstr');
		foreach($yesstrList as $yesstr){
			$allYesStr = explode(':', $yesstr->nodeValue);
			if(isset($allYesStr[0])){
				if($all==false){
					return $allYesStr[0];
				} else {
					return $allYesStr;
				}
			} else {
				return "";
			}
		}
		return "";
	}

	/**
	 * Obtiene la traducción de SI en la localización actual
	 *
	 * @param 	boolean $all
	 * @return 	string
	 */
	public function getNoString($all=false){
		$data = $this->_getLocaleData();
		$nostrList = $data->queryLanguage($this->getLanguage(), '/ldml/posix/messages/nostr');
		foreach($nostrList as $nostr){
			$allNoStr = explode(':', $nostr->nodeValue);
			if(isset($allNoStr[0])){
				if($all==false){
					return $allNoStr[0];
				} else {
					return $allNoStr;
				}
			} else {
				return "";
			}
		}
		return "";
	}

	/**
	 * Obtiene la lista de meses de la localización actual
	 *
	 * @return array
	 */
	public function getMonthList(){
		$data = $this->_getLocaleData();
		$path = '/ldml/dates/calendars/calendar[@type="gregorian"]/months/monthContext[@type="format"]/monthWidth[@type="wide"]/month';
		$monthList = $data->queryLanguage($this->getLanguage(), $path);
		$localeMonth = array();
		foreach($monthList as $month){
			$localeMonth[] = $month->nodeValue;
		}
		return $localeMonth;
	}

	/**
	 * Obtiene la lista de meses en forma abreviada
	 *
	 * @return array
	 */
	public function getAbrevMonthList(){
		$data = $this->_getLocaleData();
		$path = '/ldml/dates/calendars/calendar[@type="gregorian"]/months/monthContext[@type="format"]/monthWidth[@type="abbreviated"]/month';
		$monthList = $data->queryLanguage($this->getLanguage(), $path);
		$localeMonth = array();
		foreach($monthList as $month){
			$localeMonth[] = $month->nodeValue;
		}
		return $localeMonth;
	}

	/**
	 * Obtiene la lista de nombres de dias de la localización actual
	 *
	 * @return array
	 */
	public function getDaysNamesList(){
		$data = $this->_getLocaleData();
		$path = '/ldml/dates/calendars/calendar[@type="gregorian"]/days/dayContext[@type="format"]/dayWidth[@type="wide"]/day';
		$daysList = $data->queryLanguage($this->getLanguage(), $path);
		$localeDays = array();
		foreach($daysList as $day){
			$localeDays[] = $day->nodeValue;
		}
		return $localeDays;
	}

	/**
	 * Obtiene la lista de nombres de los idiomas en el idioma de la localización actual
	 *
	 * @return array
	 */
	public function getLanguagesList(){
		$data = $this->_getLocaleData();
		$path = '/ldml/localeDisplayNames/languages/language';
		$languagesList = $data->queryLanguage($this->getLanguage(), $path);
		$localeLanguages = array();
		foreach($languagesList as $language){
			$localeLanguages[$language->getAttribute('type')] = $language->nodeValue;
		}
		return $localeLanguages;
	}

	/**
	 * Obtiene la lista de nombres de los territorios en el idioma de la localización actual
	 *
	 * @return array
	 */
	public function getTerritoriesList(){
		$data = $this->_getLocaleData();
		$path = '/ldml/localeDisplayNames/territories/territory';
		$territoriesList = $data->queryLanguage($this->getLanguage(), $path);
		$localeTerritories = array();
		foreach($territoriesList as $territory){
			$localeTerritories[$territory->getAttribute('type')] = $territory->nodeValue;
		}
		return $localeTerritories;
	}

	/**
	 * Obtiene la lista de zonas horarias en el idioma de la localización actual
	 *
	 * @return array
	 */
	public function getTimezonesList(){
		$data = $this->_getLocaleData();
		$path = '/supplementalData/timezoneData/mapTimezones/mapZone[@territory="001"]';
		$localeTimezones = array();
		$timezonesList = $data->querySupplementalData($path);
		foreach($timezonesList as $timezone){
			$type = $timezone->getAttribute('type');
			$localeTimezones[$type] = $type;
		}
		return $localeTimezones;
	}

	/**
	 * Obtiene la lista de nombres de dias de la localización actual en forma abreviada
	 *
	 * @return array
	 */
	public function getAbrevDaysNamesList(){
		$data = $this->_getLocaleData();
		$path = '/ldml/dates/calendars/calendar[@type="gregorian"]/days/dayContext[@type="format"]/dayWidth[@type="abbreviated"]/day';
		$daysList = $data->queryLanguage($this->getLanguage(), $path);
		$localeDays = array();
		foreach($daysList as $day){
			$localeDays[] = $day->nodeValue;
		}
		return $localeDays;
	}

	/**
	 * Obtiene el formato según el tipo de la localización
	 *
	 * @param 	string $type
	 * @return 	string
	 */
	public function getDateFormat($type='full'){
		$data = $this->_getLocaleData();
		$path = '/ldml/dates/calendars/calendar[@type="gregorian"]/dateFormats/dateFormatLength[@type="'.$type.'"]/dateFormat/pattern';
		$dateFormat = $data->queryLanguage($this->getLanguage(), $path);
		if($dateFormat->length>0){
			return $dateFormat->item(0)->nodeValue;
		} else {
			throw new LocaleException('El formato de fecha "'.$type.'" no existe');
		}
	}

	/**
	 * Obtiene la traducción para 'Idioma' de acuerdo a la localización
	 *
	 * @param 	string $format
	 * @return 	string
	 */
	public function getLanguageString(){
		$data = $this->_getLocaleData();
		$path = '/ldml/localeDisplayNames/codePatterns/codePattern[@type="language"]';
		$languagePattern = $data->queryLanguage($this->getLanguage(), $path);
		if($languagePattern->length>0){
			$language = $languagePattern->item(0)->nodeValue;
			return ucfirst(substr($language, 0, strpos($language, ':')));
		} else {
			return false;
		}
	}

	/**
	 * Devuelve el formato monetario
	 *
	 * @return array
	 */
	public function getCurrencyFormat(){
		$data = $this->_getLocaleData();
		$path = '/ldml/numbers/currencyFormats/currencyFormatLength/currencyFormat/pattern';
		$currencyPattern = $data->queryLanguage($this->getLanguage(), $path);
		$currency = array();
		if($currencyPattern->length>0){
			$currency['pattern'] = $currencyPattern->item(0)->nodeValue;
			$path = '/ldml/numbers/symbols/decimal';
			$decimalSymbol = $data->queryLanguage($this->getLanguage(), $path);
			if($decimalSymbol->length>0){
				$currency['decimal'] = $decimalSymbol->item(0)->nodeValue;
			} else {
				$currency['decimal'] = ',';
			}
			$path = '/ldml/numbers/symbols/group';
			$groupSymbol = $data->queryLanguage($this->getLanguage(), $path);
			if($groupSymbol->length>0){
				$currency['group'] = $groupSymbol->item(0)->nodeValue;
			} else {
				$currency['group'] = '.';
			}
			$path = '/ldml/currencies/currency/symbol';
			$symbolSymbol = $data->queryLanguage($this->getLanguage(), $path);
			if($symbolSymbol->length>0){
				$currency['symbol'] = $symbolSymbol->item(0)->nodeValue;
			} else {
				$currency['symbol'] = '$';
			}
			return $currency;
		} else {
			throw new LocaleException('El formato monetario "'.$type."' no existe");
		}
	}

	/**
	 * Devuelve el formato numérico
	 *
	 * @return string
	 */
	public function getNumericFormat(){
		$data = $this->_getLocaleData();
		$path = '/ldml/numbers/currencyFormats/currencyFormatLength/currencyFormat/pattern';
		$currencyPattern = $data->queryLanguage($this->getLanguage(), $path);
		$currency = array();
		if($currencyPattern->length>0){
			$currency['pattern'] = $currencyPattern->item(0)->nodeValue;
			$path = '/ldml/numbers/symbols/decimal';
			$decimalSymbol = $data->queryLanguage($this->getLanguage(), $path);
			if($decimalSymbol->length>0){
				$currency['decimal'] = $decimalSymbol->item(0)->nodeValue;
			} else {
				$currency['decimal'] = ',';
			}
			$path = '/ldml/numbers/symbols/group';
			$groupSymbol = $data->queryLanguage($this->getLanguage(), $path);
			if($groupSymbol->length>0){
				$currency['group'] = $groupSymbol->item(0)->nodeValue;
			} else {
				$currency['group'] = '.';
			}
			return $currency;
		} else {
			throw new LocaleException('El formato numérico "'.$type."' no existe");
		}
	}

	/**
	 * Obtiene el nombre de la moneda utilizada
	 *
	 * @param 	string $codeISO
	 * @param 	string $displayType
	 * @return 	array
	 */
	public function getCurrency($codeISO=null, $displayType=''){
		$data = $this->_getLocaleData();
		if($codeISO==null){
			$path = '/supplementalData/currencyData/region[@iso3166="'.$this->getCountry().'"]/currency';
			$currencyISO = $data->querySupplementalData($path);
			if($currencyISO->length>0){
				$codeISO = $currencyISO->item(0)->getAttribute('iso4217');
				$currency['name'] = $codeISO;
			} else {
				throw new LocaleException('No se pudo encontrar la moneda para el país "'.$this->getCountry().'"');
			}
		} else {
			$currency['name'] = $codeISO;
		}
		if($displayType==''){
			$path = '/ldml/numbers/currencies/currency[@type="'.$codeISO.'"]/displayName';
		} else {
			$path = '/ldml/numbers/currencies/currency[@type="'.$codeISO.'"]/displayName[@count="'.$displayType.'"]';
		}
		$currencyName = $data->queryAny($this->getLanguage(), $this->getCountry(), $path);
		if($currencyName->length>0){
			$currency['displayName'] = $currencyName->item(0)->nodeValue;
		} else {
			$currency['displayName'] = '';
		}
		$path = '/ldml/numbers/currencies/currency[@type="'.$codeISO.'"]/symbol';
		$currencySymbol = $data->queryAny($this->getLanguage(), $this->getCountry(), $path);
		if($currencySymbol->length>0){
			$currency['symbol'] = $currencySymbol->item(0)->nodeValue;
		} else {
			$currency['symbol'] = '$';
		}
		return $currency;
	}

	/**
	 * Obtiene las letras consideradas como vocales
	 *
	 * @return array
	 */
	public function getAllVowels(){
		$data = $this->_getLocaleData();
		$path = '/ldml/linguistics/vowels/vowel';
		$vowels = $data->queryLanguage($this->getLanguage(), $path);
		$arrayVowels = array();
		foreach($vowels as $vowel){
			$arrayVowels[] = $vowel->nodeValue;
			unset($vowel);
		}
		return $arrayVowels;
	}

	/**
	 * Obtiene las letras no acentuadas consideradas como vocales
	 *
	 * @return array
	 */
	public function getVowels(){
		$data = $this->_getLocaleData();
		$path = '/ldml/linguistics/vowels/vowel[@accented="no"]';
		$vowels = $data->queryLanguage($this->getLanguage(), $path);
		$arrayVowels = array();
		foreach($vowels as $vowel){
			$arrayVowels[] = $vowel->nodeValue;
			unset($vowel);
		}
		return $arrayVowels;
	}

	/**
	 * Obtiene una relación de las vocales no acentuadas y acentuadas
	 *
	 * @return array
	 */
	public function getAccentedVowels(){
		$data = $this->_getLocaleData();
		$path = '/ldml/linguistics/accentedVowels/accentedVowel';
		$accentedVowels = $data->queryLanguage($this->getLanguage(), $path);
		$arrayAcentedVowels = array();
		foreach($accentedVowels as $accentedVowel){
			if(!isset($arrayAcentedVowels[$accentedVowel->nodeValue])){
				$arrayAcentedVowels[$accentedVowel->nodeValue] = array($accentedVowel->getAttribute('accent'));
			} else {
				$arrayAcentedVowels[$accentedVowel->nodeValue][] = $accentedVowel->getAttribute('accent');
			}
		}
		return $arrayAcentedVowels;
	}

	/**
	 * Obtiene preposiciones comunes lingüisticas de la localización
	 *
	 * @return array
	 */
	public function getLinguisticPrepositions(){
		$data = $this->_getLocaleData();
		$path = '/ldml/linguistics/prepositions/preposition';
		$prepositions = $data->queryLanguage($this->getLanguage(), $path);
		$arrayPrepositions = array();
		foreach($prepositions as $preposition){
			$arrayPrepositions[$preposition->getAttribute('type')] = $preposition->nodeValue;
		}
		return $arrayPrepositions;
	}

	/**
	 * Obtiene las articulos indeterminados y determinados de la localización
	 *
	 * @return array
	 */
	public function getLinguisticArticles(){
		$articles = array(
			'indefinite' => array(
				'male' => array(
					'one' => '',
					'other' => ''
				),
				'female' => array(
					'one' => '',
					'other' => ''
				)
			),
			'definite' => array(
				'male' => array(
					'one' => '',
					'other' => ''
				),
				'female' => array(
					'one' => '',
					'other' => ''
				)
			)
		);
		$data = $this->_getLocaleData();
		$path = '/ldml/linguistics/articles/indefinite';
		$indefiniteArticles = $data->queryLanguage($this->getLanguage(), $path);
		foreach($indefiniteArticles as $indefiniteArticle){
			$genre = $indefiniteArticle->getAttribute('genre');
			foreach($indefiniteArticle->childNodes as $node){
				if($node->nodeType==1){
					$articles['indefinite'][$genre][$node->getAttribute('count')] = $node->nodeValue;
				}
			}
		}
		$path = '/ldml/linguistics/articles/definite';
		$definiteArticles = $data->queryLanguage($this->getLanguage(), $path);
		foreach($definiteArticles as $definiteArticle){
			$genre = $definiteArticle->getAttribute('genre');
			foreach($definiteArticle->childNodes as $node){
				if($node->nodeType==1){
					$articles['definite'][$genre][$node->getAttribute('count')] = $node->nodeValue;
				}
			}
		}
		return $articles;
	}

	/**
	 * Obtiene las reglas para el idioma para identificar palabras de genero femenino
	 *
	 * @param 	string $genre
	 * @return	array
	 */
	public function getWordRules($genre){
		$arrayGenreRules = array();
		$data = $this->_getLocaleData();
		$path = '/ldml/linguistics/genres/genre[@type="'.$genre.'"]/wordend';
		$genreRules = $data->queryLanguage($this->getLanguage(), $path);
		foreach($genreRules as $genreRule){
			$arrayGenreRules[] = $genreRule->nodeValue;
		}
		return $arrayGenreRules;
	}

	/**
	 * Obtiene las traducciones de cantidades según la localización
	 *
	 * @return array
	 */
	public function getQuantities(){
		$arrayQuantities = array();
		$data = $this->_getLocaleData();
		$path = '/ldml/linguistics/quantities/quantity';
		$quantities = $data->queryLanguage($this->getLanguage(), $path);
		foreach($quantities as $quantity){
			$arrayQuantities[$quantity->getAttribute('type')] = $quantity->nodeValue;
		}
		return $arrayQuantities;
	}

	/**
	 * Realiza una conjunción de valores
	 *
	 * @param mixed $values
	 */
	public function getConjunction($values){
		$length = count($values);
		if($length>1){
			$data = $this->_getLocaleData();
			$path = '/ldml/linguistics/conjunction';
			$conjunction = $data->queryLanguage($this->getLanguage(), $path);
			if($conjunction->length>0){
				$and = $conjunction->item(0)->nodeValue;
			} else {
				$and = 'y';
			}
			return join(', ', array_slice($values, 0, $length-1)).' '.$and.' '.$values[$length-1];
		} else {
			return join('', $values);
		}
	}

	/**
	 * Realiza una disyunción de valores
	 *
	 * @param mixed $values
	 */
	public function getDisjunction($values){
		$length = count($values);
		if($length>1){
			$data = $this->_getLocaleData();
			$path = '/ldml/linguistics/disjunction';
			$conjunction = $data->queryLanguage($this->getLanguage(), $path);
			if($conjunction->length>0){
				$or = $conjunction->item(0)->nodeValue;
			} else {
				$or = 'o';
			}
			return join(', ', array_slice($values, 0, $length-1)).' '.$or.' '.$values[$length-1];
		} else {
			return join('', $values);
		}
	}

	/**
	 * Si la localización no tiene un país definido le asigna uno
	 * teniendo en cuenta la importancia política y demográfica
	 *
	 */
	public function forceCountry(){
		if($this->hasCountry()==false){
			switch($this->getLanguage()){
				case 'es':
					$this->setCountry('ES');
					break;
				case 'en':
					$this->setCountry('US');
					break;
				case 'fr':
					$this->setCountry('FR');
					break;
				case 'de':
					$this->setCountry('DE');
					break;
				case 'pt':
					$this->setCountry('PT');
					break;
				case 'zh':
					$this->setCountry('CN');
					break;
				case 'jp':
					$this->setCountry('JP');
					break;
				case 'it':
					$this->setCountry('IT');
					break;
			}
		}
	}

	/**
	 * Indica si la localización es la misma que la predeterminada
	 *
	 * @return boolean
	 */
	public function isDefaultLocale(){
		return self::$_default==$this->_locale['locale'];
	}

	/**
	 * Establece la localización por defecto
	 *
	 * @param 	string $locale
	 * @static
	 */
	public static function setDefault($locale){
		self::$_default = $locale;
	}

	/**
	 * Obtiene la localización acual
	 *
	 * @param 	string $localeValue
	 * @return 	array
	 */
	public static function getLocale($localeValue){
		if($localeValue=='C'){
			$localeValue = self::$_default;
		}
		if(isset(self::$_localeData[$localeValue])==false){
			return array(
				'locale' => $localeValue,
				'country' => '',
				'language' => ''
			);
		}
		$localeParts = explode('_', $localeValue);
		if(isset($localeParts[1])){
			$locale = array(
				'locale' => $localeValue,
				'country' => $localeParts[1],
				'language' => $localeParts[0]
			);
		} else {
			$locale = array(
				'locale' => $localeValue,
				'country' => '',
				'language' => $localeParts[0]
			);
		}
		return $locale;
	}

	/**
	 * Obtiene todas las localizaciones disponibles en el entorno de ejecución
	 *
	 * @return	array
	 * @static
	 */
	public static function getEnvironmentAll(){
		$language = setlocale(LC_ALL, 0);
        $languages = explode(';', $language);
        $elocales = array();
        foreach($languages as $lang){
        	$lc = explode('/', $lang);
        	$localeParts = explode('_', $lc[0]);
			$elocales[] = array(
				'locale' => str_replace('-', '_', $lc[0]),
				'country' => isset($localeParts[1]) ? strtoupper($localeParts[1]) : '',
				'language' => $localeParts[0],
				'quality' => 1.0
			);
        }
        return $elocales;
	}

	/**
	 * Obtiene la configuración establecida con la funcion de PHP setlocale
	 *
	 * @return Locale
	 * @static
	 */
	public static function getEnviroment(){
		if(self::$_environ!=''){
			return self::$_environ;
		}
		$locales = self::getEnvironmentAll();
		foreach($locales as $locale){
			if($locale['quality']==1.0){
				if($locale['country']!=''){
					self::$_environ = new self($locale['language'].'_'.$locale['country']);
				} else {
					self::$_environ = new self($locale['language']);
				}
				return self::$_environ;
			}
		}
		return '';
	}

	/**
	 * Devuelve todas las localizaciones soportadas por el explorador
	 *
	 * @return array
	 * @static
	 */
	static public function getBrowserAll(){
		$elocales = array();
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			$locales = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			foreach($locales as $locale){
				$elocale = explode(';', $locale);
				$localeParts = explode('-', $elocale[0]);
				if(isset($localeParts[1])){
					$country = strtoupper($localeParts[1]);
					$localeString = $localeParts[0].'_'.$country;
				} else {
					$country = '';
					$localeString = $localeParts[0];
				}
				$elocales[] = array(
					'locale' => $localeString,
					'country' => $country,
					'language' => $localeParts[0],
					'quality' => isset($elocale[1]) ? substr($elocale[1], 2) : 1.0
				);
			}
		}
		return $elocales;
	}

	/**
	 * Devuelve una localización apartir del nombre del país
	 *
	 * @param	string $country
	 * @return	Locale
	 * @static
	 */
	static public function fromCountry($country){
		if(isset(self::$_countries[$country])){
			return new Locale(self::$_countries[$country][1]);
		} else {
			return new Locale();
		}
	}

	static public function getCountryLocaleData($country){
		if(isset(self::$_countries[$country])){
			return self::$_countries[$country];
		} else {
			return false;
		}
	}

	/**
	 * Devuelve la localización del explorador
	 *
	 * @return Locale
	 * @static
	 */
	static public function getBrowser(){
		if(self::$_browser!=''){
			return self::$_browser;
		}
		$locales = self::getBrowserAll();
		foreach($locales as $locale){
			if($locale['quality']==1.0){
				self::$_browser = new self($locale['locale']);
				return self::$_browser;
			}
		}
		return '';
	}

	/**
	 * Devuelve la localización segun la configuración de la aplicación
	 *
	 * @return Locale
	 */
	public static function getApplication(){
		if(self::$_application!=''){
			return self::$_application;
		}
		$config = CoreConfig::readAppConfig();
		if(isset($config->application->locale)){
			$locale = $config->application->locale;
		} else {
			$config = CoreConfig::getInstanceConfig();
			if(isset($config->core->locale)){
				$locale = $config->core->locale;
			} else {
				$locale = self::$_default;
			}
		}
		self::$_application = new self($locale);
		return self::$_application;
	}

	/**
	 * Establece la localización de la aplicación en runtime
	 *
	 * @param	Locale $locale
	 * @static
	 */
	public static function setApplication(Locale $locale){
		self::$_application = $locale;
	}

	/**
	 * Obtiene el string de la localización
	 *
	 * @return string
	 */
	public function getLocaleString(){
		return $this->_locale['locale'];
	}

	/**
	 * Devuelve la localización en formato RFC4646
	 *
	 * @return string
	 */
	public function getRFC4646String(){
		return str_replace('_', '-', $this->_locale['locale']);
	}

	/**
	 * Genera un número formateado con la localización actual
	 *
	 * @param	double $number
	 * @return	string
	 */
	public static function round($x){
		return LocaleMath::round($x, 0);
	}

	/**
	 * Genera un numero formateado con la localización actual para monedas
	 *
	 * @param	double $number
	 * @return	string
	 */
	public static function money($number){
		return Currency::money($number);
	}

	public static function formatDate($x){
		return $x;
	}

	/**
	 * Genera un número formateado con la localización actual
	 *
	 * @param double $number
	 * @return string
	 */
	public static function number($number){
		return Currency::number($number);
	}

	/**
	 * Resetea el LocaleData al serializar el objeto
	 *
	 */
	public function __sleep(){
		return array('_locale');
	}

	/**
	 * Metodo mágico __toString devuelve el identificador de localizacion del objeto
	 *
	 * @return string
	 */
	public function __toString(){
		return $this->_locale['locale'];
	}

}
