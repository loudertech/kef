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
 * @version 	$Id: InclusionInValidator.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * InclusionInValidator
 *
 * Permite validar si el valor de un campo se encuentra en una lista de dominio
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	Validators
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class InclusionInValidator extends ActiveRecordValidator implements ActiveRecordValidatorInterface {

	/**
	 * Valida que las opciones sean validas
	 *
	 */
	public function checkOptions(){
		if(!$this->isSetOption('domain')){
			throw new ActiveRecordValidatorException("El Validador InclusionIn requiere que indique la lista de opciones de inclusión");
		}
		if(!is_array($this->getOption('domain'))){
			throw new ActiveRecordValidatorException("La lista de comparación debe ser un vector en InclusionInValidator");
		}
	}

	/**
	 * Ejecuta el validador
	 *
	 * @param ActiveRecord $record
	 * @return boolean
	 */
	public function validate(){
		if($this->isRequired()){
			$validateFails = false;
			if($this->isSetOption('domain')){
				if(!in_array($this->getValue(), $this->getOption('domain'))){
					if($this->isSetOption('message')){
						$this->appendMessage($this->getOption('message'));
					} else {
						$this->appendMessage("El valor del campo '".$this->getFieldName()."' debe ser parte del rango ".join(", ", $this->getOption('domain')));
					}
					$validateFails = true;
				}
			}
			if($validateFails){
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}

}
