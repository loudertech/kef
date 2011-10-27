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
 * @package		AuditLogger
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: AuditLogger.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * AuditLogger
 *
 * Componente que permite realizar auditoría de sistemas
 *
 * @category	Kumbia
 * @package		AuditLogger
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class AuditLogger extends Object {

	/**
	 * Modelo utilizado para grabar los log de auditoria
	 *
	 * @var string
	 */
	private $_model = "";

	/**
	 * Transaccion con la que almacenara el log
	 *
	 * @var ActiveRecordTransaction|boolean
	 */
	private $_transaction = false;

	/**
	 * Indica si ya se almaceno el log
	 *
	 * @var boolean
	 */
	private $_commited = false;

	/**
	 * Campos que se guardaran en el log
	 *
	 * @var array
	 */
	private $_fieldsToLog = array(
		'IP_ADDRESS' => 'Y',
		'MAC_ADDRESS' => 'N',
		'USERNAME' => 'N',
		'USER_ID' => 'Y',
		'NOTE' => 'Y'
	);

	/**
	 * Nombres de las columnas
	 *
	 * @var array
	 */
	private $_bindFields = array(
		'IP_ADDRESS' => '',
		'MAC_ADDRESS' => '',
		'USERNAME' => '',
		'USER_ID' => '',
		'NOTE' => ''
	);

	/**
	 * Valores de las Columnas
	 *
	 * @var array
	 */
	private $_fieldData = array(
		'IP_ADDRESS' => '',
		'MAC_ADDRESS' => '',
		'USERNAME' => '',
		'USER_ID' => '',
		'NOTE' => ''
	);

	/**
	 * Activa o desactiva un campo de la auditoria
	 *
	 * @abstract public
	 * @param string $key
	 */
	public function toggleField($key){
		if(!isset($this->_itemsToLog[$key])){
			if($this->_itemsToLog[$key]=='Y'){
				$this->_itemsToLog[$key] = 'N';
			} else {
				$this->_itemsToLog[$key] = 'Y';
			}
		} else {
			throw new AuditLoggerException("No esta soportado el campo de auditoria '$key'");
		}
	}

	/**
	 * Asigna el nombre de una columna de la entidad a un campo del log
	 *
	 * @param string $key
	 * @param string $field
	 */
	public function bindToField($key, $field){
		$this->_bindFields[$key] = $field;
	}

	/**
	 * Obtiene la direccion MAC de quien visita el sitio
	 *
	 * @param string $ip
	 * @return string
	 */
	private function _getMacAddress($ip){
		if(PHP_OS=='Linux'){
			$datos = `arp -a $ip`;
			if(strpos($datos,$ip)){
				foreach(explode("\n", $datos) as $line){
					if(strpos($line, $ip)){
						$items = explode(" ", $line);
						return $items[3];
					}
				}
			} else {
				return "00:00:00:00";
			}
		} else {
			return "00:00:00:00";
		}
	}

	/**
	 * Constructor de la clase
	 *
	 * @param string $model
	 */
	public function __construct($model){
		$model = (string) $model;
		if(EntityManager::isModel($model)==false){
			throw new AuditLoggerException("El modelo '$model' no es valido");
		}
		$this->_model = $model;
		if($this->_fieldsToLog['IP_ADDRESS']=='Y'){
			$this->setFieldData('IP_ADDRESS', $_SERVER['REMOTE_ADDR']);
		}
		if($this->_fieldsToLog['MAC_ADDRESS']=='Y'){
			$mac = $this->_getMacAddress($_SERVER['REMOTE_ADDR']);
			$this->setFieldData('MAC_ADDRESS', $mac);
		}
	}

	/**
	 * Establece el objeto de transaccion
	 *
	 * @param ActiveRecordTransaction $transaction
	 */
	public function setTransaction(ActiveRecordTransaction $transaction){
		$this->_transaction = $transaction;
	}

	/**
	 * Agrega un dato solicitado en un registro de la auditoria
	 *
	 * @param string $keyname
	 * @param mixed $data
	 */
	public function setFieldData($keyname, $data){
		$this->_fieldsToLog[$keyname] = 'Y';
		$this->_fieldData[$keyname] = $data;
	}

	/**
	 * Al destruir el objeto se graba la auditoria
	 *
	 */
	public function __destruct(){
		$this->commit();
	}

	/**
	 * Graba el log de auditoria
	 *
	 * @access public
	 */
	public function commit(){
		if($this->_commited==false){
			if(!$this->_model){
				throw new AuditLoggerException('No ha definido el modelo para almacenar el log de auditoria');
			} else {
				$audit = new $this->_model();
				if($this->_transaction!==false){
					if(is_object($this->_transaction)){
						if($this->_transaction instanceof ActiveRecordTransaction){
							$audit->setTransaction($this->_transaction);
						} else {
							throw new AuditLoggerException("Objeto invalido de transacción");
						}
					} else {
						throw new AuditLoggerException("Objeto invalido de transacción");
					}
				}
				foreach($this->_fieldsToLog as $key => $isEnabled){
					if($isEnabled=='Y'){
						if(isset($this->_bindFields[$key])){
							$bindField = $this->_bindFields[$key];
							if($bindField==""){
								$bindField = $key;
							}
						} else {
							$bindField = $key;
						}
						$audit->writeAttribute($bindField, $this->_fieldData[$key]);
					}
				}
				if($audit->save()==false){
					foreach($audit->getMessages() as $message){
						throw new AuditLoggerException($message->getMessage());
					}
					return false;
				} else {
					$this->_commited = true;
					return true;
				}
			}
		}
	}

}
