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
 * @version 	$Id: DateInValidator.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * DateInValidator
 *
 * Permite validar que un campo tenga valores de fecha correctos
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	Validators
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class DateInValidator extends ActiveRecordValidator implements ActiveRecordValidatorInterface {

	/**
	 * Ejecuta el validador
	 *
	 * @return boolean
	 */
	public function validate(){
		if($this->isRequired()){
			if(!preg_match('/^[0-9]{4}[-\/](0[1-9]|1[12])[-\/](0[1-9]|[12][0-9]|3[01])$/', $this->getValue(), $regs)){
				if($regs[0]!=$this->getValue()){
					$this->appendMessage("El valor del campo '".$this->getFieldName()."' debe ser una fecha valida");
					return false;
				}
			}
		}
		return true;
	}
}
