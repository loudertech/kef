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
 * @package		Captcha
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id$
 */

/**
 * ImageCaptcha
 *
 * Adaptador de Captcha que genera imagenes con un texto aleatorio
 *
 * @category	Kumbia
 * @package		Captcha
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @access		public
 */
class ImageCaptcha {

	/**
	 * Identificador único del Captcha
	 *
	 * @var string
	 */
	private $_name;

	/**
	 * Opciones del Adaptador
	 *
	 * @var array
	 */
	private $_options;

	/**
	 * Constructor de ImageCaptcha
	 *
	 * @param string $name
	 * @param string $options
	 */
	public function __construct($name, $options){
		#if[compile-time]
		if(extension_loaded('gd')==false){
			throw new CaptchaException('Se require la extensión de php GD');
		}
		#endif
		$this->_name = $name;
		$this->_options = $options;
	}

	public function output(){
		View::setRenderLevel(View::LEVEL_NO_RENDER);
		$controllerResponse = ControllerResponse::getInstance();
		if(!isset($this->_options['imageType'])){
			$this->_options['imageType'] = 'image/png';
		}
		if(!isset($this->_options['width'])){
			$width = 225;
		} else {
			$width = $this->_options['width'];
		}
		if(!isset($this->_options['height'])){
			$height = 75;
		} else {
			$height = $this->_options['height'];
		}
		$controllerResponse->setContentType($this->_options['imageType']);
		$im = imagecreatetruecolor($width, $height);
		$grey = imagecolorallocate($im, 0x17, 0x17, 0x17);
		$white = imagecolorallocate($im, 0xEA, 0xEA, 0xEA);
		imagefilledrectangle($im, 0, 0, 399, 99, $white);
		if(!isset($this->_options['text'])){
			$text = '';
			if(!isset($this->_options['length'])){
				$length = 5;
			} else {
				$length = $this->_options['length'];
			}
			$letters = array(
				'WE', 'AW', 'Se', 'u', 'Ro', 'bu', 'wo', 'mA', 'Ze', 'DA',
				'Ma', 'SE', 'vA', 'Co', 'VA', 'Bu', 'be', 'Re', 'th', 'Bo', 'ZA',
				'Te', 'Pe', 'De', 'Se', 'Co', 'e', 'Mu', 'LA', 'Kem', 'Com', 'FRA',
				'VaT', 'SAB', 'ReP', 'Kef', 'Cuc', 'Pu', 'PA', 'TA', 'PAX',
				'Lam', 'SuC', 'Ceu', 'Zed', 'Fla', 'Pem', 'eMA', 'Los', 'Lum',
				'Am', 'AC', 'AD', 'Ce', 'Ch', 'DeR', 'RoA', 'CAu', 'ShA', 'APA',
				'Ace', 'Moc', 'Cho', 'ChA', 'SAK', 'Chu', 'Shu', 'KoX'
			);
			$setLength = count($letters)-1;
			$text = array();
			$i = 0;
			while($i<$length){
				$letter = $letters[mt_rand(0, $setLength)];
				if(!in_array($letter, $text)){
					$text[] = $letter;
					$i+=strlen($letter);
				}
			}
			$text = substr(join('', $text), 0, $length);
		} else {
			$text = $this->_options['text'];
			$length = i18n::strlen($text);
		}
		if(!isset($this->_options['font'])){
			$font = array(
				'Library/Kumbia/Captcha/Resources/Heroin.ttf'
			);
			$fontLength = 1;
		} else {
			if(is_array($this->_options['font'])){
				$font = $this->_options['font'];
				$fontLength = count($font);
			} else {
				$font = array($this->_options['font']);
				$fontLength = 1;
			}
		}

		$maxSize = $length*40;
		$j = 0;
		$black = imagecolorallocate($im, 0x17, 0x17, 0x17);
		$k = mt_rand($height*0.25, $height*0.75);
		$s = mt_rand(0, $length-1);
		$middle = $height*0.5;
		$angle = 25;
		for($i=5;$i<$maxSize;$i+=40){
			$rgb = mt_rand(0x11, 0x90);
			$color = imagecolorallocate($im, $rgb, $rgb, $rgb);
			$size = mt_rand(65, 75);
			$f = $font[mt_rand(0, $fontLength-1)];
			$ch = substr($text, $j, 1);
			$y = mt_rand(50, 55);
			imagettftext($im, $size, $angle, $i, $y, $grey, $f, $ch);
			imagettftext($im, $size, $angle, $i+1, $y+4, $color, $f, $ch);
			$box = imagettfbbox($size, $angle, $f, $ch);
			if($i==5){
				if($k<$middle){
					$k = min($box[3], $box[1])+10;
				} else {
					$k = max($box[0], $box[2])-10;
				}
			}
			$in = mt_rand(-2, 2);
			if($k<$middle){
				if(($k+$in+10)>$box[3]||($k+$in+10)>$box[1]){
					$k-=2;
				} else {
					$k+=2;
				}
			} else {
				if(($k+$in-10)>$box[2]||($k+$in-10)>$box[0]){
					$k+=2;
				} else {
					$k-=2;
				}
			}
			if($k<10){
				$k = 35;
			}
			for($h=$i;$h<=($i+45);$h++){
				imagefilledellipse($im, $h, $k, 3, 3, $black);
				imagefilledellipse($im, $h+1, $k+1, 3, 3, $black);
			}
			$j++;
			$angle-=10;
		}

		$ixi = ((int)($width*0.25));
		$ixf = ((int)($width*0.75));
		for($a=$ixi;$a<$ixf;$a++){
			for($c=63;$c<$height;$c++){
				$rgb = imagecolorat($im, $a, $c);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				$ng = mt_rand(-10, 10);
				$ncolor = imagecolorallocate($im, 255-$r, 255-$g, 255-$b);
				imagesetpixel($im, $a, $c, $ncolor);
			}
		}


		$ia = 1;
		for($c=30;$c<=45;$c++){
			$px = array();
			for($a=$ia;$a<$width;$a++){
				$px[$a] = imagecolorat($im, $a, $c);
			}
			$ia = mt_rand(1, 2);
			for($a=$ia;$a<$width-$ia;$a++){
				if(isset($px[$a-$ia])){
					imagesetpixel($im, $a, $c, $px[$a-$ia]);
				} else {
					imagesetpixel($im, $a, $c, $white);
				}
			}
		}

		switch($this->_options['imageType']){
			case 'image/png':
				imagepng($im);
				break;
			case 'image/jpeg':
				imagejpeg($im);
				break;
			case 'image/gif':
				imagegif($im);
				break;
			case 'image/gif':
				imagewbmp($im);
				break;
		}
		imagepng($im);
		imagedestroy($im);

		return strtolower($text);

	}

}