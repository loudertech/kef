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
 * @package		Builder
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: Benchmark.php,v 7a54c57f039b 2011/10/19 23:41:19 andres $
 */

/**
 * Benchmark
 *
 * Permite realizar profiling de la ejecución de un proceso para determinar cuellos de botella
 *
 * @category	Kumbia
 * @package		Builder
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: Benchmark.php,v 7a54c57f039b 2011/10/19 23:41:19 andres $
 */
class Benchmark extends Object {

	private static $_startTime = 0;

	private static $_started = false;

	private static $_stats = array();

	public static function profile(){
		if(self::$_started==false){
			register_shutdown_function(array('Benchmark', 'dumpStats'));
			self::$_startTime = microtime(true);
			self::$_started = true;
		}
		self::$_stats[] = array(
			'memory' => memory_get_peak_usage(true),
			'time' => microtime(true),
			'trace' => debug_backtrace()
		);
	}

	public static function dumpStats(){
		if(count(self::$_stats)){
			$fp = fopen('benchmark.txt', 'w');
			$lastTime = self::$_startTime;
			foreach(self::$_stats as $stat){
				$function = '';
				if(isset($stat['trace'][1]['class'])){
					$function = $stat['trace'][1]['class'].'::'.$stat['trace'][1]['function'];
				} else {
					$function = $stat['trace'][1]['function'];
				}
				fwrite($fp, $function.' | '.LocaleMath::round($stat['time']-$lastTime, 4).PHP_EOL);
				$lastTime = $stat['time'];
			}
			fclose($fp);
		}
	}

}