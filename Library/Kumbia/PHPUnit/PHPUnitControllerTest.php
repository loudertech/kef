<?php

/**
 * Kumbia Enterprise Framework
 *
 * LICENSE
 *
 * This source file is subject to the New BSD License that is bundled
 * with this package in the file docs/LICENSE.txt.

 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web	, please send an email
 * to license@loudertechnology.com so we can send you a copy immediately.
 *
 * @category 	Kumbia
 * @package 	PHPUnit
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 * @version 	$Id: PHPUnitControllerTest.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * PHPUnitControllerTest
 *
 * Clase que permite definir los metodos de aserci&oacute;n
 * para realizar los test de unidad
 *
 * @category 	Kumbia
 * @package 	PHPUnit
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 * @abstract
 */
class PHPUnitControllerTest extends Object {

	/**
	 * Indica si el framework ha sido inicializado
	 *
	 * @var boolean
	 */
	private static $_applicationInitialized = false;

	/**
	 * Inicializa el entorno de Pruebas
	 *
	 */
	public static function initEnvironment(){
		/**
		 * Establece tipo de notificacion de errores
		 */
		error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);

		/**
		 * @see Kumbia
		 */
		require KEF_ABS_PATH.'Library/Kumbia/Kumbia.php';

		/**
		 * @see Session
		 */
		require KEF_ABS_PATH.'Library/Kumbia/Session/Session.php';

		/**
		 * @see Config
		 */
		require KEF_ABS_PATH.'Library/Kumbia/Config/Config.php';

		/**
		 * @see Router
		 */
		require KEF_ABS_PATH.'Library/Kumbia/Router/Router.php';

		/**
		 * @see Plugin
		 */
		require KEF_ABS_PATH.'Library/Kumbia/Plugin/Plugin.php';

	}

	/**
	 * Genera un string aleatorio
	 *
	 * @param int $maxLength
	 */
	public function getRandomString($maxLength=-1){
		if($maxLength==-1){
			$maxLength = mt_rand(1, 128);
		}
		$str = "";
		for($i=0;$i<$maxLength;++$i){
			$str.=chr(mt_rand(8, 255));
		}
		return $str;
	}

	/**
	 * Realiza el test del UnitTest
	 *
	 * @param string $applicationName
	 * @param string $className
	 */
	public static function test($applicationName, $className){
		try {
			if(class_exists($className)){
				$testCase = new $className();
				$reflectionClass = new ReflectionClass($className);
				$controllerName = strtolower(preg_replace('/ControllerTest$/', '', $className));
				/**
				 * Establecer el framework en modo Test
				 */
				Core::setTestingMode(true);
				foreach($reflectionClass->getMethods() as $method){
					if(preg_match('/Test$/', $method->getName())==true){
						$actionName = preg_replace('/Test$/', '', $method->getName());
						$url = $applicationName.'/'.$controllerName.'/'.$actionName;
						$dataProvider = $actionName.'DataProvider';
						if(method_exists($testCase, $dataProvider)){
							$data = $testCase->$dataProvider();
							Router::rewrite($url);
							if(self::$_applicationInitialized==false){
								Core::initApplication();
								self::$_applicationInitialized = true;
							}
							if(isset($data['POST'])){

							}
							if(isset($data['POST'])){
								unset($_POST);
								$firstRequest = true;
								foreach($data['POST'] as $postData){
									foreach($postData as $index => $value){
										$_POST[$index] = $value;
										$_REQUEST[$index] = $value;
									}
									if($firstRequest==true){
										Core::main();
										$firstRequest = false;
									} else {
										Core::resetRequest();
										Core::handleRequest();
									}
								}
							}
						} else {
							Router::rewrite($url);
							if(self::$_applicationInitialized==false){
								Core::initApplication();
								self::$_applicationInitialized = true;
							}
							Core::main();
						}
					}
				}
			} else {
				throw new PHPUnitException("No existe la clase de testeo '$className'");
			}
		}
		catch(Exception $e){
			if(PHP_OS=="Darwin"){
				$message = preg_replace('/[ ]+/', ' ', html_entity_decode($e->getMessage(), ENT_COMPAT, "UTF-8"));
			} else {
				$message = preg_replace('/[ ]+/', ' ', html_entity_decode($e->getMessage(), ENT_COMPAT));
			}
			$message = preg_replace('/[\n\t\r]+/', ' ', $message);
			echo get_class($e).": ".$message."\n";
			echo "URI: ".Router::getURL()."\n";
		}
	}

}
