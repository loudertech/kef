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
 * @category	Kumbia
 * @package		PHPUnit
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 * @version 	$Id: PHPUnit.php 90 2009-09-21 01:29:23Z gutierrezandresfelipe@gmail.com $
 */

/**
 * PHPUnit
 *
 * Clase que permite crear entornos de pruebas
 *
 * @category 	Kumbia
 * @package 	PHPUnit
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 * @abstract
 */
abstract class PHPUnit {

	private static $_tests = array();

	/**
	 * Proporciona un entorno controlado para la ejecución
	 * de pruebas de unidad
	 *
	 * @param string $className
	 */
	static public function testClass($className){
		if(!class_exists($className, false)){
			throw new PHPUnitException("La clase de test de unidad '$className' no existe");
		}
		self::$_tests[$className] = array(
			'assertionFailed' => 0,
			'numberOfTest' => 0,
			'numberOfErrors' => 0,
			'assertionMessages' => array(),
			'testCases' => array(),
			'startTime' => microtime(true),
			'endTime' => 0
		);
		try {
			$test = new $className();
			$reflector = new ReflectionClass($className);
			$assertionFailed = 0;
			$numberOfTest = 0;
			$numberOfErrors = 0;
			$assertionMessages = array();
			try {
				foreach($reflector->getMethods() as $method){
					try {
						if($method->isPublic()&&substr($method->getName(), 0, 4)=='test'){
							$comment = $method->getDocComment();
							$comment = trim(str_replace(array('/*', '*/', '*'), '', $comment));
							$methodName = $method->getName();
							echo sprintf('%03s', $numberOfTest+1).'. '.
							sprintf('%-30s', $methodName).
							sprintf('%-60s', $comment);
							++$numberOfTest;
							self::$_tests[$className]['numberOfTest']++;
							$testCase = array(
								'name' => $methodName,
								'description' => $comment,
								'startTime' => microtime(true)
							);
							$test->$methodName();
						} else {
							continue;
						}
					}
					catch(AssertionFailed $e){

						$assertionMessages[$methodName] = get_class($e).' > '.$e->getMessage();
						self::$_tests[$className]['assertionFailed']++;
						++$assertionFailed;

						$testCase['result'] = 'failure';
						$testCase['endTime'] = microtime(true);
						self::$_tests[$className]['testCases'][] = $testCase;

						echo 'FAIL', "\n";
						continue;
					}
					catch(Exception $e){

						$assertionMessages[$methodName] = get_class($e).' > '.$e->getConsoleMessage();
						file_put_contents('console.log', print_r($e->getTrace(), true), FILE_APPEND);
						self::$_tests[$className]['numberOfErrors']++;
						++$numberOfErrors;

						$testCase['result'] = 'error';
						$testCase['endTime'] = microtime(true);
						self::$_tests[$className]['testCases'][] = $testCase;

						echo 'FAIL', "\n";
						continue;

					}

					$testCase['result'] = 'pass';
					$testCase['endTime'] = microtime(true);
					self::$_tests[$className]['testCases'][] = $testCase;
					echo 'OK', "\n";
				}
			}
			catch(AssertionFailed $e){
				$assertionMessages['_start'] = get_class($e).' > '.$e->getMessage();
				$assertionFailed++;
				self::$_tests[$className]['assertionFailed']++;
			}
			catch(Exception $e){
				$assertionMessages['_start'] = get_class($e).' > '.$e->getConsoleMessage();
				++$numberOfErrors;
				self::$_tests[$className]['assertionError']++;
			}
			self::$_tests[$className]['assertionMessages'] = $assertionMessages;
			self::$_tests[$className]['endTime'] = microtime(true);
			self::$_tests[$className]['assertions'] = $test->getNumberAssertions();
			self::$_tests[$className]['failures'] = $test->getFailedAssertions();

			echo "Total Pruebas: ".$numberOfTest." Fallaron: ".$assertionFailed."\n";
			echo "Total Aserciones: ".$test->getNumberAssertions()." Exitosas: ".$test->getSuccessAssertions()." Fallaron: ".$test->getFailedAssertions()."\n";
			if($assertionFailed>0){
				echo "Los test han fallado con los siguientes mensajes:\n\n";
				foreach($assertionMessages as $test => $messsage){
					echo $test." : ".$messsage."\n";
				}
			}
		}
		catch(Exception $e){
			echo "Exception ".$e->getMessage()."\n";
			echo "Archivo: ".$e->getFile()."\n";
			debug_print_backtrace();
		}
	}

	public static function outputToXml($file){
		try {
			$dom = new DOMDocument('1.0', 'UTF-8');
			$testsuites = new DOMElement('testsuites');
			$dom->appendChild($testsuites);
			foreach(self::$_tests as $test){
				$testsuite = new DOMElement('testsuite');
				$testsuites->appendChild($testsuite);
				$testsuite->setAttribute('tests', $test['numberOfTest']);
				$testsuite->setAttribute('assertions', $test['assertions']);
				$testsuite->setAttribute('failures', $test['assertionFailed']);
				$testsuite->setAttribute('errors', $test['numberOfErrors']);
				$testsuite->setAttribute('time', LocaleMath::round($test['endTime']-$test['startTime'], 2));
				foreach($test['testCases'] as $testCase){
					$case = new DOMElement('testcase');
					$testsuite->appendChild($case);
					$case->setAttribute('name', $testCase['description']);
					$case->setAttribute('classname', $testCase['name']);
					$case->setAttribute('result', $testCase['result']);
					$case->setAttribute('time', LocaleMath::round($testCase['endTime']-$testCase['startTime'], 2));
				}
			}
			file_put_contents($file, $dom->saveXML());
		}
		catch(Exception $e){
			print_r($e->getMessage().' '.$e->getLine());
		}

	}

}
