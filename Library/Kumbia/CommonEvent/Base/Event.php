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
 * @package		CommonEvent
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */

/**
 * Event
 *
 * Permite la creación de eventos y acceso a sus datos. Los recursos de eventos
 * utilizan la API de CommonBaseEvent para crear nuevos eventos en base al
 * modelo de aplicación. Los consumidores de eventos utilizan su API
 * para leer la información en ellos. Aplicaciones de terceros pueden
 * convertir los objetos en representaciones en XML para intercambiar los datos.
 *
 * @category	Kumbia
 * @package		CommonEvent
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
class Event extends Object {

	/**
	 * Nombre del evento a invocar
	 *
	 * @var string
	 */
	private $_eventName;

	/**
	 * Callback del evento
	 *
	 * @var string
	 */
	private $_callback;

	/**
	 * Constructor de Event
	 *
	 * @param	string $eventName
	 * @param	mixed $callback
	 */
	public function __construct($eventName, $callback){
		CoreType::assertString($eventName);
		$this->_eventName = $eventName;
		if(is_callable($callback)){
			$this->_callback = $callback;
		}
	}

	/**
	 * Devuelve el nombre interno del evento
	 *
	 * @return string
	 */
	public function getEventName(){
		return $this->_eventName;
	}

	/**
	 * Ejecuta el evento
	 *
	 */
	public function execute(){
		call_user_func($this->_callback);
	}

}
