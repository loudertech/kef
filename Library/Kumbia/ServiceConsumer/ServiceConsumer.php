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
 * @category 	Kumbia
 * @package 	ServiceConsumer
 * @copyright	Copyright (c) 2008-2012 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @version 	$Id: Security.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * ServiceConsumer
 *
 * Permite consumir servicios web usando diferentes protocolos y capas de transporte de
 * manera unificada
 *
 * @category 	Kumbia
 * @package 	Security
 * @copyright	Copyright (c) 2008-2012 Louder Technology COL. (http://www.loudertechnology.com)
 * @license 	New BSD License
 * @access 		public
 */
class ServiceConsumer extends Object {

	private $_transaction;

	private $_transport;

	private $_protocol;

	public function __construct($protocol, $transport, $options){
		$this->_transport = $transport;
		$className = $protocol.'Protocol';
		if(class_exists($className, false)==false){
			require KEF_ABS_PATH.'Library/Kumbia/ServiceConsumer/Protocols/'.$protocol.'.php';
		}
		$this->_protocol = new $className($this, $options);
	}

	public function setTransaction(Transaction $transaction){
		$transaction->attachServiceDependency(null, $this);
	}

	public function getTransport($url){
		$className = $this->_transport.'Transport';
		if(class_exists($className, false)==false){
			require KEF_ABS_PATH.'Library/Kumbia/ServiceConsumer/Transport/'.$this->_transport.'.php';
		}
		$uri = new HttpUri($url);
		$transport = new $className($uri->getSchema(), $uri->getHostname(), $uri->getUri(), 'POST', $uri->getPort());
		$transport->enableCookies(true);
		return $transport;
	}

	public function __call($method, $arguments=array()){
		return call_user_func_array(array($this->_protocol, $method), $arguments);
	}

}