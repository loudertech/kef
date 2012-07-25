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
 * @package		Validation
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version		$Id: Validation.php 52 2009-05-12 21:15:44Z gutierrezandresfelipe $
 */

/**
 * Validation
 *
 * Este componente está integrado a las implementaciones de controladores y
 * permite realizar validaciones sobre la entrada de usuario. Al ser
 * independiente de la capa de lógica de dominio y presentación puede
 * ser usado en los puntos de la aplicación que se requiera sin afectar
 * la arquitectura de la misma.
 *
 * @category	Kumbia
 * @package		Validation
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class Validation {

	/**
	 * Mensajes de Validación
	 *
	 * @var array
	 */
	private static $_validationMessages = array();

	/**
	 * Indica el éxito del proceso de validación
	 *
	 * @var boolean
	 */
	private static $_validationFailed = false;

	/**
	 * Limpia el buffer de mensajes de validación
	 *
	 * @access public
	 * @static
	 */
	public static function cleanValidationMessages(){
		self::$_validationMessages = array();
	}

	/**
	 * Agrega un mensaje de validación al buffer
	 *
	 * @access 	public
	 * @param 	string $message
	 * @param 	string $fieldName
	 * @static
	 */
	public static function addValidationMessage($message, $fieldName){
		if(!isset(self::$_validationMessages[$fieldName])){
			self::$_validationMessages[$fieldName] = array();
		}
		self::$_validationMessages[$fieldName][] = $message;
		if(self::$_validationFailed==false){
			self::$_validationFailed = true;
		}
	}

	/**
	 * Efectua una validación sobre los valores de la entrada
	 *
	 * @param 	array $fields
	 * @param 	string $base
	 * @param 	string $getMode
	 * @return 	boolean
	 */
	public static function validateRequired($fields, $base='', $getMode=''){
		$validationFailed = false;
		$fieldFailed = false;
		self::cleanValidationMessages();
		if(is_array($fields)){
			if(!$base){
				$base = 'Post';
				$getMode = 'getParamPost';
			}
			$controllerRequest = ControllerRequest::getInstance();
			foreach($fields as $fieldName => $config){
				$fieldFailed = false;
				if(!is_numeric($fieldName)){
					if(isset($config['filter'])){
						$params = explode('|', $config['filter']);
						array_unshift($params, $fieldName);
						if(in_array(call_user_func_array(array($controllerRequest, $getMode), $params), array('', null), true)){
							$fieldFailed = true;
						}
					} else {
						$valueRequested = $controllerRequest->$getMode($fieldName);
						if(!isset($config['nullValue'])){
							if(in_array($valueRequested, array('', null), true)){
								$fieldFailed = true;
							}
						} else {
							if($valueRequested==$config['nullValue']){
								$fieldFailed = true;
							}
						}
					}
					if($fieldFailed==true){
						if(isset($config['message'])){
							$message = $config['message'];
						} else {
							$message = 'Un valor para "'.$fieldName.'" es requerido';
						}
						self::addValidationMessage($message, $fieldName);
						$validationFailed = true;
					}
				} 
			}
		} else {
			if(func_num_args()>1){
				$validationFailed = false;
				$fieldFailed = false;
				foreach(func_get_args() as $field){
					$fieldFailed = false;
					$validation = explode(':', $field);
					if(!isset($validation[1])){
						if(in_array($this->getRequest($validation[0], $validation[1]), array("", null), true)){
							$fieldFailed = true;
						}
					} else {
						if(in_array($this->getRequest($validation[0]), array("", null), true)){
							$fieldFailed = true;
						}
					}
					if($fieldFailed==true){
						self::addValidationMessage("Un valor para '{$validation[0]}' es requerido", $validation[0]);
						$validationFailed = true;
					}
				}
			}
		}
		self::$_validationFailed = $validationFailed;
		return !self::$_validationFailed;
	}

	/**
	 * Muestra mensajes de validación para un determinado campo
	 *
	 * @access 	public
	 * @param 	string $field
	 * @param 	array $callback
	 * @throws 	ValidationException
	 * @static
	 */
	public static function showMessagesFor($field, $callback=array('Flash', 'error')){
		if(isset(self::$_validationMessages[$field])){
			if(is_callable($callback)==false){
				throw new ValidationException('El callback para mostrar mensajes no es válido');
			}
			foreach(self::$_validationMessages[$field] as $message){
				call_user_func_array($callback, array($message));
			}
		}
	}

	/**
	 * Indica si existen mensajes de validación
	 *
	 * @access 	public
	 * @param 	string $fieldName
	 * @return 	boolean
	 * @static
	 */
	public static function hasMessages($fieldName=''){
		if($fieldName==''){
			return count(self::$_validationMessages)>0 ? true : false;
		} else {
			if(isset(self::$_validationMessages[$fieldName])){
				return count(self::$_validationMessages[$fieldName])>0 ? true : false;
			} else {
				return false;
			}
		}
	}

	/**
	 * Indica si la última validación falló
	 *
	 * @access public
	 * @return boolean
	 * @static
	 */
	public static function validationWasFailed(){
		return self::$_validationFailed;
	}

	/**
	 * Obtiene los mensajes de validación
	 *
	 * @access public
	 * @return array
	 * @static
	 */
	public static function getMessages(){
		$validationMessages = array();
		foreach(self::$_validationMessages as $fieldName => $messages){
			foreach($messages as $message){
				$validationMessage = new ValidationMessage($message, $fieldName);
				$validationMessages[] = $validationMessage;
			}
		}
		return $validationMessages;
	}

	/**
	 * Obtiene el primer mensaje de validación para un determinado campo
	 *
	 * @access 	public
	 * @param 	string $fieldName
	 * @return 	ValidationMessage
	 * @static
	 */
	public static function getFirstMessageFor($fieldName){
		if(isset(self::$_validationMessages[$fieldName])){
			return new ValidationMessage(self::$_validationMessages[$fieldName][0], $fieldName);
		} else {
			return false;
		}
	}

}
