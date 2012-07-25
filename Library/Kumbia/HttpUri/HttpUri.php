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
 * @package		HttpUri
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license  	New BSD License
 * @version 	$Id: HttpUri.php 88 2009-09-19 19:10:13Z gutierrezandresfelipe $
 */

/**
 * Helpers
 *
 * Componente que implementa ayudas utiles al desarrollador
 *
 * @category 	Kumbia
 * @package		HttpUri
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @license  	New BSD License
 * @license  	New BSD License
 * @access 		public
 */
class HttpUri {

	/**
	 * Schema de la URL
	 *
	 * @var string
	 */
	private $_schema;

	/**
	 * Nombre del host
	 *
	 * @var string
	 */
	private $_host;

	/**
	 * Numero del puerto
	 *
	 * @var int
	 */
	private $_port;

	/**
	 * Uri de la Url
	 *
	 * @param string
	 */
	private $_uri;

	/**
	 * Constructor de HttpUri
	 *
	 * @param string $url
	 */
	public function __construct($url){
		if(preg_match('#([a-z]+)://(.+)#', $url, $matches)){
			$this->_schema = $matches[1];
			$uriParts = $matches[2];
		} else {
			$this->_schema = 'http';
			$uriParts = $url;
		}
		$urlParts = explode('/', $uriParts);
		if(isset($urlParts)){
			if(preg_match('/([0-9a-zA-Z\.\-\_]+):([0-9]+)/', $urlParts[0], $matches)){
				$this->_host = $matches[1];
				$this->_port = (int) $matches[2];
			} else {
				$this->_host = $urlParts[0];
				$this->_port = 80;
			}
		}
		$countParts = count($urlParts);
		if($countParts>1){
			$this->_uri = join('/', array_slice($urlParts, 1, $countParts-2));
			$position = strpos($urlParts[$countParts-1], '?');
			if($position===false){
				$lastUriPart = $urlParts[$countParts-1];
			} else {
				$lastUriPart = substr($urlParts[$countParts-1], 0, $position);
			}
			if($this->_uri!=''){
				$this->_uri.='/'.$lastUriPart;
			} else {
				$this->_uri.=$lastUriPart;
			}
		} else {
			$this->_uri = '';
		}
	}

	/**
	 * Obtiene el nombre de la maquina ó dirección IP
	 *
	 */
	public function getHostName(){
		return $this->_host;
	}

	/**
	 * Devuelve el schema de la Uri
	 *
	 * @return string
	 */
	public function getSchema(){
		return $this->_schema;
	}

	/**
	 * Devuelve el puerto utilizado para realizar el acceso
	 *
	 * @return int
	 */
	public function getPort(){
		return $this->_port;
	}

	/**
	 * Obtiene la uri de la la URL
	 *
	 * @return string
	 */
	public function getUri(){
		return $this->_uri;
	}

}
