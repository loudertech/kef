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
 * @subpackage	Validators
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: LengthValidator.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * LengthValidator
 *
 * Permite validar si el tamaño de un campo es correcto
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	Validators
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class LengthValidator extends ActiveRecordValidator implements ActiveRecordValidatorInterface {

	/**
	 * Chequea que las opciones del validador sean correctas
	 *
	 */
	public function checkOptions(){
		if(!$this->isSetOption('minimum')&&!$this->isSetOption('maximum')){
			throw new ActiveRecordValidatorException("El Validador Length requiere que indique la opción maximum ó minimum");
		}
		if($this->isSetOption('minimum')){
			if($this->getOption('minimum')<0){
				throw new ActiveRecordValidatorException("La opción 'mimimum' debe ser un entero positivo");
			}
		}
	}

	/**
	 * Ejecuta el validador
	 *
	 * @param ActiveRecord $record
	 * @return boolean
	 */
	public function validate(){
		$validateFails = false;
		if($this->isSetOption('minimum')){
			if(strlen($this->getValue())<$this->getOption('minimum')){
				if($this->getOption('minimum')==1){
					$text = "un caracter";
				} else {
					$text = $this->getOption('minimum')." caracteres";
				}
				$this->appendMessage("El campo '".$this->getFieldName()."' debe tener al menos ".$text);
				$validateFails = true;
			}
		}
		if($this->isSetOption('maximum')){
			if(strlen($this->getValue())<$this->getOption('maximum')){
				if($this->getOption('maximum')==1){
					$text = "un caracter";
				} else {
					$text = $this->getOption('maximum')." caracteres";
				}
				$this->appendMessage("El campo '".$this->getFieldName()."' debe tener máximo ".$text);
				$validateFails = true;
			}
		}
		if($validateFails){
			return false;
		} else {
			return true;
		}
	}
}
