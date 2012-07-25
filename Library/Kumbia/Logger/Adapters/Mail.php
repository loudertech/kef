<?php

/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.

 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category 	Kumbia
 * @package 	Logger
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 * @version 	$Id: Mail.php 65 2009-06-05 19:42:57Z gutierrezandresfelipe $
 */

/**
 * MailLogger
 *
 * Permite generar logs a archivos planos de Texto
 *
 * @category 	Kumbia
 * @package 	Logger
 * @subpackage 	Adapters
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 */
class MailLogger extends LoggerAdapter implements LoggerInterface {

	/**
	 * Email(s) al que se enviara el log
	 *
	 * @var array|string
	 */
	private $_email;

	/**
	 * Conexi&oacute;n SMTP
	 *
	 * @var Swift_Connection_SMTP
	 */
	private $_smtp;

	/**
	 * Opciones pasadas a MailLogger
	 *
	 * @var array
	 */
	private $_options = array();

	/**
	 * Constructor de la clase MailLogger
	 *
	 * @param string $name
	 */
	public function __construct($email, $options=array()){
		if(class_exists('Swift_Connection_SMTP', false)==false){
			Core::importFromLibrary('Swift', 'Swift/Connection/SMTP.php');
		}
		$this->_email = $email;
		$this->_options = $options;
		if(PHP_VERSION<5.1){
			$this->_dateFormat = 'r';
		}
		if(isset($options['server'])==false){
			throw new LoggerException('Debe indicar el nombre del servidor SMTP a utilizar por MailLogger');
		}
		if(isset($options['username'])==false){
			throw new LoggerException('Debe indicar el nombre del usuario en el servidor SMTP a utilizar por MailLogger');
		}
		if(!isset($options['secureConnection'])){
			$options['secureConnection'] = false;
		}
		if($options['secureConnection']==true){
			$port = Swift_Connection_SMTP::PORT_SECURE;
			$encryption = Swift_Connection_SMTP::ENC_TLS;
		} else {
			$port = Swift_Connection_SMTP::PORT_DEFAULT;
			$encryption = Swift_Connection_SMTP::ENC_OFF;
		}
		$this->_smtp = new Swift_Connection_SMTP($options['server'], $port, $encryption);
		$this->_smtp->setUsername($options['username']);
		$this->_smtp->setPassword($options['password']);
		$this->_transaction = true;
	}

	/**
	 * Realiza el proceso del log
	 *
	 * @access public
	 * @param string $msg
	 * @param int $type
	 */
	public function log($msg, $type){
		if(is_array($msg)||is_object($msg)){
			$msg = print_r($msg, true);
		}
		if($this->_transaction==true){
			$this->_quenue[] = new LoggerItem($msg, $type, time());
		} else {
			throw new LoggerException("Solo se pueden agregar items al log cuando esta en una transacci&oacute;n");
		}
	}

	/**
 	 * Commit a una transaccion
 	 *
 	 * @access public
 	 */
	public function commit(){
		if($this->_transaction==false){
			throw new LoggerException("No hay una transacci&oacute;n activa");
		}
		$this->_transaction = false;
		$message = "";
		foreach($this->_quenue as $msg){
			$message.=$this->_applyFormat($msg->getMessage(), $msg->getType(), $msg->getTime()).PHP_EOL;
		}
		if(!isset($this->_options['subject'])){
			$this->_options['subject'] = 'MailLog: '.Router::getApplication().' - '.date('r');
		}
		$swiftMessage = new Swift_Message($this->_options['subject'], $message, 'text/plain');
		$swift = new Swift($this->_smtp);
		if(!isset($this->_options['fromName'])){
			$this->_options['fromName'] = substr($this->_options['username'], 0, strpos($this->_options['username'], '@'));
		}
		$recipients = new Swift_RecipientList();
		if(is_array($this->_email)){
			foreach($this->_email as $name => $email){
				if(is_numeric($name)==false){
					$recipients->add($email, $name);
				} else {
					$recipients->add($email);
				}
			}
		} else {
			$recipients->add($this->_email);
		}
		$swift->send($swiftMessage, $recipients, new Swift_Address($this->_options['username'], $this->_options['fromName']));
	}

	/**
 	 * Cierra el Logger
 	 *
 	 * @access public
 	 * @return boolean
 	 */
	public function close(){
		if($this->_transaction==true){
			$this->commit();
		}
		if($this->_smtp->isAlive()==true){
			return $this->_smtp->stop();
		} else {
			return true;
		}
	}

	/**
	 * Destructor del Logger
	 *
	 */
	public function __destruct(){
		$this->close();
	}

}
