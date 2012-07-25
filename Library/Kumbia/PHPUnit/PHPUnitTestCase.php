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
 * @package 	PHPUnit
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 * @version 	$Id: PHPUnitTestCase.php 82 2009-09-13 21:06:31Z gutierrezandresfelipe $
 */

/**
 * PHPUnitTestCase
 *
 * Clase que permite definir los metodos de aserción
 * para realizar los test de unidad
 *
 * @category 	Kumbia
 * @package 	PHPUnit
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe@gmail.com)
 * @license 	New BSD License
 * @abstract
 */
abstract class PHPUnitTestCase extends Object {

	/**
	 * Número de aserciones realizadas en el test
	 *
	 * @var int
	 */
	private $_numberAsserts = 0;

	/**
	 * Numero de aserciones exitosas
	 *
	 * @var int
	 */
	private $_numberSuccessAsserts = 0;

	/**
	 * Numero de aserciones que fallaron
	 *
	 * @var int
	 */
	private $_numberFailedAsserts = 0;

	/**
	 * Hace una assercion si el archivo existe
	 *
	 * @access protected
	 * @param string $path
	 * @return boolean
	 */
	protected function assertFileExists($path){
		++$this->_numberAsserts;
		if(!Core::fileExists($path)){
			++$this->_numberFailedAsserts;
			throw new AssertionFailed("No existe el archivo '$path'");
		} else {
			++$this->_numberSuccessAsserts;
			return true;
		}
	}

	/**
	 * Hace una asercion sobre valores iguales
	 *
	 * @param 	string $value1
	 * @param 	string $value2
	 * @return 	boolean
	 */
	protected function assertEquals($value1, $value2){
		$this->_numberAsserts++;
		if($value1!==$value2){
			if($value1===false){
				$v1 = 'false';
			} else {
				if($value1===true){
					$v1 = 'true';
				} else {
					$v1 = $value1;
				}
			}
			if($value2===false){
				$v2 = 'false';
			} else {
				if($value2===true){
					$v2 = 'true';
				} else {
					$v2 = $value2;
				}
			}
			++$this->_numberFailedAsserts;
			throw new AssertionFailed("El valor (".gettype($value1).") $v1 no es igual a (".gettype($value2).") '$value2'");
		} else {
			++$this->_numberSuccessAsserts;
			return true;
		}
	}

	/**
	 * Hace una asercion sobre el tamaño de un string
	 *
	 * @param 	string $value1
	 * @param 	string $value2
	 * @return 	boolean
	 */
	protected function assertLength($value, $length){
		$this->_numberAsserts++;

		if(is_array($value)){
			if(count($value)==$length){
				++$this->_numberSuccessAsserts;
				return true;
			}
		} else {
			if(i18n::strlen($value)==$length){
				++$this->_numberSuccessAsserts;
				return true;
			}
		}

		++$this->_numberFailedAsserts;
		throw new AssertionFailed("El tamaño de (".gettype($value).") $value no es igual a (".$length.")");
	}

	/**
	 * Hace una aserción sobre si un objeto pertenece a una clase
	 *
	 * @param 	object $object
	 * @param 	string $className
	 * @return 	boolean
	 */
	protected function assertInstanceOf($object, $className){
		++$this->_numberAsserts;
		if(!is_object($object)){
			++$this->_numberFailedAsserts;
			throw new AssertionFailed("El valor no es un objeto (".gettype($object).")");
		}
		if(get_class($object)!=$className){
			++$this->_numberFailedAsserts;
			throw new AssertionFailed("El objeto no pertenece a la clase '$className'");
		}
		++$this->_numberSuccessAsserts;
		return true;
	}

	/**
	 * Hace una aserción sobre si una variable es un recurso
	 *
	 * @param 	resource $resource
	 * @return 	boolean
	 */
	protected function assertResource($resource){
		++$this->_numberAsserts;
		if(!is_object($resource)){
			$this->_numberFailedAsserts++;
			throw new AssertionFailed("El valor no es un recurso");
		}
		++$this->_numberSuccessAsserts;
		return true;
	}

	/**
	 * Hace una aserción sobre si un valor es verdadero
	 *
	 * @param 	bool $value
	 * @return 	boolean
	 */
	protected function assertTrue($value){
		++$this->_numberAsserts;
		if($value!==true){
			++$this->_numberFailedAsserts;
			throw new AssertionFailed("El valor '$value' no es un verdadero");
		}
		++$this->_numberSuccessAsserts;
		return true;
	}

	/**
	 * Hace una aserción sobre si un valor es nulo
	 *
	 * @param 	null $value
	 * @return 	boolean
	 */
	protected function assertNull($value){
		++$this->_numberAsserts;
		if($value!==null){
			++$this->_numberFailedAsserts;
			throw new AssertionFailed("El valor '$value' no es un nulo");
		}
		++$this->_numberSuccessAsserts;
		return true;
	}

	/**
	 * Devuelve el numero de aserciones ejecutadas en el test
	 *
	 * @return int
	 */
	public function getNumberAssertions(){
		return $this->_numberAsserts;
	}

	/**
	 * Devuelve el numero de aserciones ejecutadas en el test que fueron exitosas
	 *
	 * @return int
	 */
	public function getSuccessAssertions(){
		return $this->_numberSuccessAsserts;
	}

	/**
	 * Devuelve el numero de aserciones ejecutadas en el test que fallaron
	 *
	 * @return int
	 */
	public function getFailedAssertions(){
		return $this->_numberFailedAsserts;
	}

	/**
	 * Obliga a que todas las propiedades del testcase esten definidas previamente
	 *
	 * @access	public
	 * @param	string $property
	 */
	public function __get($property){
		if(EntityManager::isModel($property)==false){
			throw new UserComponentException("Leyendo propiedad indefinida '$property' del testcase");
		} else {
			$entity = EntityManager::getEntityInstance($property);
			$this->_settingLock = true;
			$this->$property = $entity;
			$this->_settingLock = false;
			return $this->$property;
		}
	}


}
