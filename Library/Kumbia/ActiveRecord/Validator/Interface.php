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
 * @subpackage	ActiveRecordValidator
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Interface.php 52 2009-05-12 21:15:44Z gutierrezandresfelipe $
 */

/**
 * ActiveRecordValidatorInterface
 *
 * Interface que todos los validadores deben implementar
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	ActiveRecordValidator
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
interface ActiveRecordValidatorInterface {

	public function __construct($record, $field, $value, $options=array());
	public function checkOptions();
	public function getMessages();
	public function validate();

}
