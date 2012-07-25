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
 * @package		Filter
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2007-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @copyright	Copyright (c) 2007-2007 Emilio Rafael Silveira Tovar(emilio.rst at gmail.com)
 * @copyright	Copyright (c) 2007-2007 Deivinson Tejeda Brito (deivinsontejeda at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Filter.php,v f5add30bf4ba 2011/10/26 21:05:13 andres $
 */

/**
 * Filter
 *
 * Implementación de Filtros para Kumbia
 *
 * @category	Kumbia
 * @package		Filter
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 */
class Filter extends Object {

	/**
	 * Filtros a que se aplicaran a través del metodo "apply"
	 *
	 * @var array
	 */
	private $_filters = array();

	/**
	 * Valor al cual se deben aplicar los filtros
	 *
	 * @var string
	 */
	private $_value;

	/**
	 * Constructor de la clase Filter
	 *
	 */
	public function __construct($filters=array()){
		$this->addFilters($filters);
	}

	/**
	 * Agrega un filtro a la cola de filtros
	 *
	 * @return boolean
	 */
	public function addFilters($filters){
		foreach($filters as $filter){
			if(is_object($filter)&&method_exists($filter, 'execute')){
				$this->_bufferFilters[] = $filter;
			} else {
				if(is_array($filter)){
					foreach($filter as $subFilter){
						$this->_filters[] = self::getFilter($subFilter);
					}
				} else {
					$this->_filters[] = self::getFilter($filter);
				}
			}
		}
		return true;
	}

	/**
	 * Aplica un filtro
	 *
	 * @param	mixed $value
	 * @param	array $filters
	 * @return	mixed
	 */
	public function apply($value, $filters=array()){
		$this->_value = $value;
		$this->addFilters($filters);
		foreach($this->_filters as $filter){
			if(is_array($this->_value)){
				$values = $this->_value;
				foreach($values as $key => $value){
					$values[$key] = $filter->execute($value);
				}
				$this->_value = $values;
			} else {
				$this->_value = $filter->execute($this->_value);
			}
		}
		return $this->_value;
	}

	/**
	 * Devuelve el valor obtenido después de aplicar el filtro
	 *
	 * @return mixed
	 */
	public function getValue(){
		return $this->_value;
	}

	/**
	 * Obtiene una instancia de un filtro
	 *
	 * @param	string $filterName
	 * @return	Filter
	 */
	public static function getFilter($filterName){
		$className = $filterName.'Filter';
		if(class_exists($className, false)==false){
			self::load($filterName);
		}
		return new $className();
	}

	/**
	 * Carga la clase de un filtro para su posterior aplicacion
	 *
	 * @access	public
	 * @param	string $filterName
	 * @static
	 */
	public static function load($filterName){
		$filters = func_get_args();
		#if[compile-time]
		if(interface_exists('FilterInterface', false)==false){
			/**
			 * @see FilterInterface
			 */
			require KEF_ABS_PATH.'Library/Kumbia/Filter/Interface.php';
		}
		#endif
		foreach($filters as $filterName){
			if(class_exists($filterName.'Filter', false)==false){
				$fileName = ucfirst($filterName);
				if(Core::fileExists('Library/Kumbia/Filter/BaseFilters/'.$fileName.'.php')==true){
					require KEF_ABS_PATH.'Library/Kumbia/Filter/BaseFilters/'.$fileName.'.php';
				} else {
					$activeApp = Router::getApplication();
					if($activeApp!=""){
						$config = CoreConfig::readAppConfig();
						if(isset($config->application->filtersDir)){
							$filtersDir = 'apps/'.$config->application->filtersDir;
						} else {
							$filtersDir = 'apps/'.$activeApp.'/filters';
						}
						$path = $filtersDir.'/'.$fileName.'.php';
						if(Core::fileExists($path)){
							require KEF_ABS_PATH.$path;
						} else {
							throw new FilterException("No existe el filtro '$fileName'");
						}
					}
				}
			}
		}
	}

	/**
	 * Aplica un conjunto de filtros a un valor de manera estática
	 *
	 * @param	string $value
	 * @return	string
	 */
	public static function bring($value){
		if(func_num_args()>1){
			$params = func_get_args();
			unset($params[0]);
			$filters = array();
			foreach($params as $param){
				if(is_array($param)){
					$filters = $filters + $param;
				} else {
					$filters[] = $param;
				}
			}
			$filter = new self($filters);
			return $filter->apply($value);
		} else {
			return $value;
		}
	}

}
