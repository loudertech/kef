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
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: EmailValidator.php,v a434b34d7989 2011/10/26 22:23:04 andres $
 */

/**
 * EmailValidator
 *
 * Permite validar que campo tenga valores de e-mail correctos
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	Validators
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class EmailValidator extends ActiveRecordValidator implements ActiveRecordValidatorInterface {

	/**
	 * Ejecuta el validador
	 *
	 * @return boolean
	 */
	public function validate(){
		if($this->isRequired()){
			if(preg_match('/^[a-zA-Z0-9_\.\+]+@[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*$/', $this->getValue(), $regs)){
				if($regs[0]!=$this->getValue()){
					$this->appendMessage("El valor del campo '".$this->getFieldName()."' debe ser un e-mail válido");
					return false;
				}
			} else {
				$this->appendMessage("El valor del campo '".$this->getFieldName()."' debe ser un e-mail válido");
				return false;
			}
		}
		return true;
	}
}
