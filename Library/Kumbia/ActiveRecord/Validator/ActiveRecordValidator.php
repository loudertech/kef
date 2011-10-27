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
 * @version 	$Id: ActiveRecordValidator.php 87 2009-09-19 19:02:50Z gutierrezandresfelipe $
 */

/**
 * ActiveRecordValidator
 *
 * ActiveRecord permite que los modelos ejecuten tareas de validación
 * definidas por el desarrollador que garanticen que los datos que se
 * almacenen en la persistencia sean íntegros y se evite todo lo
 * que esto conlleva.
 *
 * @category	Kumbia
 * @package		ActiveRecord
 * @subpackage	ActiveRecordValidator
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2008-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @access		public
 */
class ActiveRecordValidator extends Object {

	/**
	 * Objeto ActiveRecord del Validador
	 *
	 * @var ActiveRecord
	 */
	private $_record;

	/**
	 * Campo a validar
	 *
	 * @var string
	 */
	private $_fieldName;

	/**
	 * Valor del Campo a validar
	 *
	 * @var mixed
	 */
	private $_value;

	/**
	 * Opciones del Validador
	 *
	 * @var array
	 */
	private $_options = array();

	/**
	 * Mensajes del Validador
	 *
	 * @var array
	 */
	private $_messages = array();

	/**
	 * Constructor del validador
	 *
	 * @param ActiveRecord $record
	 * @param string $field
	 * @param string $value
	 * @param array $options
	 */
	public final function __construct($record, $fieldName, $value, $options = array()){
		$this->_record = $record;
		$this->_fieldName = $fieldName;
		$this->_value = $value;
		$this->_options = $options;
	}

	/**
	 * Agrega un mensaje en el validador
	 *
	 * @access protected
	 * @param string $message
	 * @param string $field
	 * @param string $type
	 */
	protected function appendMessage($message, $field='', $type=''){
		if($field==''){
			$field = $this->_fieldName;
		}
		if($type==''){
			$type = str_replace('Validator', '', get_class($this));
		}
		$this->_messages[] = new ActiveRecordMessage($message, $field, $type);
	}

	/**
	 * Devuelve los mensajes generados en el Validador
	 *
	 * @access public
	 * @return array
	 */
	public function getMessages(){
		return $this->_messages;
	}

	/**
	 * Permite saber si la opcion 'required' fue pasada como parametro
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function isRequired(){
		$required = true;
		if($this->_value===''||$this->_value===null){
			if(isset($this->_options['required'])){
				$required = $this->_options['required'];
			}
			return $required;
		} else {
			return true;
		}
	}

	/**
	 * Devuelve las opciones del Validador
	 *
	 * @return array
	 */
	protected function getOptions(){
		return $this->_options;
	}

	/**
	 * Devuelve la opcion solicitada
	 *
	 * @access	protected
	 * @param	string $option
	 * @return	mixed
	 */
	protected function getOption($option){
		return isset($this->_options[$option]) ? $this->_options[$option] : "";
	}

	/**
	 * Indica si la opcion solicitada ha sido definida por parte del usuario
	 *
	 * @access	protected
	 * @param	string $option
	 * @return	boolean
	 */
	protected function isSetOption($option){
		return isset($this->_options[$option]) ? true : false;
	}

	/**
	 * Devuelve el valor del campo validado
	 *
	 * @access	protected
	 * @return	mixed
	 */
	protected function getValue(){
		return $this->_value;
	}

	/**
	 * Devuelve el nombre del campo validado
	 *
	 * @access protected
	 * @return string
	 */
	protected function getFieldName(){
		return $this->_fieldName;
	}

	/**
	 * Devuelve el objeto ActiveRecord donde se efectua la validacion
	 *
	 * @access protected
	 * @return ActiveRecord
	 */
	protected function getRecord(){
		return $this->_record;
	}

	/**
	 * Puede ser reescrita por el usuario para validar que las opciones
	 * tengas las opciones requeridas
	 *
	 * @access public
	 */
	public function checkOptions(){

	}

}

