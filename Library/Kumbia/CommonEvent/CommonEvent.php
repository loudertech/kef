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
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: CommonEvent.php 5 2009-04-24 01:48:48Z gutierrezandresfelipe $
 */

/**
 * CommonEvent
 *
 * Allows to dynamically add events to be run in the framework
 *
 * @category	Kumbia
 * @package		CommonEvent
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
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
	 * Observable events
	 *
	 * @var array
	 */
	private static $_callbacks = array();

	/**
	 * Shutdown events/callbacks
	 *
	 * @var array
	 */
	private static $_shutdownEvents = array();

	/**
	 * Adds an event to event manager
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

	/**
	 * Add closures to observe and fire different events
	 *
	 * @param string $eventName
	 * @param callback $callback
	 */
	public static function observe($eventName, $callback){
		if(!is_callable($callback)){
			throw new CommonEventException('The callback is not callable');
		}
		self::$_callbacks[$eventName][] = $callback;
	}

	/**
	 * Adds a event/callback to be executed at the end of the request
	 *
	 * @param callback $callback
	 */
	public static function onShutdown($callback){
		if(count(self::$_shutdownEvents)==0){
			register_shutdown_function(array('CommonEvent', 'shutdownProcedure'));
		}
		self::$_shutdownEvents[] = $callback;
	}

	/**
	 * Method to be executed at the end of request if there are any shutdownEvents
	 *
	 */
	public static function shutdownProcedure(){
		try {
			foreach(self::$_shutdownEvents as $callback){
				call_user_func_array($callback, array());
			}
		}
		catch(Exception $e){
			Core::handleException($e);
		}
	}

}
