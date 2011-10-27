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
 * @version 	$Id: CommonEvent.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * CommonEvent
 *
 * Permite agregar dinámicamente eventos que se ejecuten en la framework
 *
 * @category	Kumbia
 * @package		CommonEvent
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @abstract
 */
abstract class CommonEvent {

	/**
	 * Eventos del administrador
	 *
	 * @var array
	 */
	private static $_events = array();

	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	private static $_callbacks = array();

	/**
	 * Agrega un evento al administrador de eventos
	 *
	 * @param Event $event
	 */
	public static function attachEvent(Event $event){
		if(!isset(self::$_events[$event->getEventName()])){
			self::$_events[$event->getEventName()] = array();
		}
		self::$_events[$event->getEventName()][] = $event;
	}

	/**
	 * Notifica un evento por su nombre
	 *
	 * @param string $eventName
	 */
	public static function notifyEvent($eventName){
		if(isset(self::$_events[$eventName])){
			foreach(self::$_events[$eventName] as $event){
				$event->execute();
			}
		}
		if(isset(self::$_callbacks[$eventName])){
			foreach(self::$_callbacks[$eventName] as $callback){
				call_user_func($callback);
			}
		}
	}

	public static function observe($eventName, $callback){
		if(!is_callable($callback)){
			throw new CommonEventException('El callback no es invocable');
		}
		self::$_callbacks[$eventName][] = $callback;
	}

}
