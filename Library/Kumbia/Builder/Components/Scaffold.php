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
 * @version 	$Id: Scaffold.php,v 5f278793c1ae 2011/10/27 02:50:13 andres $
 */

/**
 * ScaffoldBuilderComponent
 *
 * Builder para construir formularios
 *
 * @copyright	Copyright (c) 2008-2011 Louder Technology COL. (http://www.loudertechnology.com)
 * @license		New BSD License
 * @version 	$Id: Scaffold.php,v 5f278793c1ae 2011/10/27 02:50:13 andres $
 */
class ScaffoldBuilderComponent {

	private $_options;

	public function __construct($options){
		$this->_options = $options;
	}

	private function _findDetailField($entity){
		$posible = array('detalle', 'nombre', 'nombres', 'name', 'descripcion', 'description');
		$attributes = EntityManager::get($entity)->getAttributes();
		foreach($attributes as $attribute){
			if(in_array($attribute, $posible)){
				return $attribute;
			}
		}
		return $attributes[0];
	}

	private function _getPosibleLabel($fieldName){
		$fieldName = preg_replace('/_id$/', '', $fieldName);
		$fieldName = preg_replace('/_at$/', '', $fieldName);
		$fieldName = preg_replace('/_in$/', '', $fieldName);
		$fieldName = str_replace('_', ' de ', $fieldName);
		return ucwords($fieldName);
	}

	public function build(){

		$options = $this->_options;
		//If not existe the model, make it
		if(!EntityManager::isModel($options['className'])){
			$modelBuilder = Builder::factory('Model', array(
				'name' => $options['name'],
				'application' => $options['application'],
				'schema' => $options['schema'],
				'className' => $options['className'],
				'fileName' => $options['fileName'],
				'force' => $options['force']
			));
			$modelBuilder->build();
		}

		$entity = EntityManager::get($options['className']);
		if($entity==false){
			throw new BuilderException('El modelo '.$options['className'].' no existe');
		}

		$primaryKeys = EntityManager::get($options['className'])->getPrimaryKeyAttributes();
		if(count($primaryKeys)==0){
			throw new BuilderException('Se requiere una llave primaria en el modelo '.$options['className'].'');
		} else {
			$options['primaryKeys'] = $primaryKeys;
		}

		$linguistic = new Linguistics();

		$name = str_replace('_', ' de ', $options['name']);
		$plural = i18n::ucfirst($linguistic->pluralize($name));
		$options['name'] = $name;
		$options['plural'] = $plural;

		$single = $linguistic->singlify($name);
		if($linguistic->isFemale($single)){
			$options['theSingle'] = 'la '.$single;
			$options['aSingle'] = 'una '.$single;
		} else {
			$options['theSingle'] = 'el '.$single;
			$options['aSingle'] = 'un '.$single;
		}
		$options['single'] = $single;

		$singleVar = $linguistic->singlify($options['name']);
		$options['singleVar'] = $singleVar;

		$relationField = array();
		$belongsToDefinitions = EntityManager::getAllBelongsToDefinition($options['className']);
		$options['belongsToDefinitions'] = $belongsToDefinitions;

		foreach($belongsToDefinitions as $relationEntity => $definition){
			$relationField[$definition['fields']] = true;
		}
		$options['relationField'] = $relationField;

		$autocompleteFields = array();
		$attributes = $entity->getDataTypes();
		$autocomplete = $this->_options['autocomplete'];
		if($autocomplete){
			foreach(explode(',', $autocomplete) as $autocompleteField){
				if(!isset($attributes[$autocompleteField])){
					throw new BuilderException('El campo "'.$autocompleteField.'" no hace parte de la tabla "'.$options['name'].'" en la lista de autocomplete');
				} else {
					$autocompleteFields[$autocompleteField] = true;
				}
			}
		}

		$setParams = array();
		$selectDefinition = array();
		foreach($options['belongsToDefinitions'] as $relationEntity => $definition){
			$detailField = $this->_findDetailField($definition['referencedEntity']);
			$varName = Utils::lcfirst($definition['referencedEntity']);
			if(!isset($autocompleteFields[$definition['fields']])){
				$setParams[] = array(
					'varName' => $varName,
					'entity' => $definition['referencedEntity'],
					'order' => $detailField
				);
				$selectDefinition[$definition['fields']] = array(
					'primaryKey'	=> $definition['referencedFields'],
					'varName'		=> $varName,
					'detail'		=> $detailField,
					'tableName'		=> $definition['referencedEntity']
				);
			} else {
				$autocompleteFields[$definition['fields']] = array(
					'primaryKey'	=> $definition['referencedFields'],
					'varName'		=> $varName,
					'detail'		=> $detailField,
					'tableName'		=> $definition['referencedEntity']
				);
			}
		}
		$options['setParams'] = $setParams;
		$options['selectDefinition'] = $selectDefinition;
		$options['autocompleteFields'] = $autocompleteFields;
		$options['attributes'] = $attributes;

		//Make Controller
		$this->_makeController($options);

		//View layouts
		$this->_makeLayouts($options);

		//View index.phtml
		$this->_makeViewIndex($options);

		//View report.phtml
		$this->_makeViewReport($options);

		//View search.phtml
		$this->_makeViewSearch($options);

		//View new.phtml
		$this->_makeViewNew($options);

		//View edit.phtml
		$this->_makeViewEdit($options);
	}

	/**
	 * make controller of model by scaffold
	 *
	 * @param array $options
	 */
	private function _makeController($options){

		$controllerPath = 'apps/'.$options['application'].'/controllers/'.$options['name'].'_controller.php';
		//if(file_exists($controllerPath)){

		$code = '<?php'.PHP_EOL.PHP_EOL.
		'class '.$options['className'].'Controller extends ApplicationController {'.PHP_EOL.PHP_EOL.
		"\t".'public $conditions = \'\';'.PHP_EOL.PHP_EOL.
		"\t".'function initialize(){'.PHP_EOL.
		"\t\t".'$this->setPersistance(true);'.PHP_EOL.
		"\t\t".'Tag::setDocumentTitle("'.$options['plural'].'");'.PHP_EOL.
		"\t".'}'.PHP_EOL.PHP_EOL;

		//Index
		$code.="\t".'function indexAction(){'.PHP_EOL.
		"\t\t".'Tag::prependDocumentTitle("Buscar - ");'.PHP_EOL;
		foreach($options['setParams'] as $setParam){
			$code.="\t\t".'$this->setParamToView("'.$setParam['varName'].'", '.'$this->'.$setParam['entity'].'->find(array("order" => "'.$setParam['order'].'")));'.PHP_EOL;
		}
		$code.="\t".'}'.PHP_EOL.PHP_EOL;

		$primaryKeys = $options['primaryKeys'];
		$paramsPks = $conditionsPks = $orderPks = array();
		foreach($primaryKeys as $primaryKey){
			$orderPks[] = $primaryKey;
			$paramsPks[] = '$'.$primaryKey;
			$conditionsPks[] =	'\''.$primaryKey.'="\'.$'.$primaryKey.'.\'"\'';
		}
		if(count($orderPks)==0){
			$orderPks[] = 1;
		}
		$paramsPksString = implode(', ',$paramsPks);
		$conditionsPksString = implode(' AND ',$conditionsPks);
		$orderPksString	= implode(', ',$orderPks);
		$autocompleteFields = $options['autocompleteFields'];

		//Search
		$code.=
		"\t".'function searchAction(){'.PHP_EOL.PHP_EOL.
		"\t\t".'$request = $this->getRequestInstance();'.PHP_EOL.
		"\t\t".'if($request->isPost()==true){'.PHP_EOL.
		"\t\t\t".'$page = 1;'.PHP_EOL.
		"\t\t\t".'$this->conditions = FormCriteria::fromModel("'.$options['className'].'", FormCriteria::POST);'.PHP_EOL.
		"\t\t".'} else {'.PHP_EOL.
		"\t\t\t".'$page = $this->getQuery(\'page\', \'int\');'.PHP_EOL.
		"\t\t\t".'if($page<=0){'.PHP_EOL.
		"\t\t\t\t".'$page = 1;'.PHP_EOL.
		"\t\t\t".'}'.PHP_EOL.
		"\t\t".'}'.PHP_EOL.PHP_EOL.
		"\t\t".'$'.$options['name'].' = $this->'.$options['className'].'->find(array($this->conditions, "order" => "'.$orderPksString.'"));'.PHP_EOL.
		"\t\t".'if(count($'.$options['name'].')==0){'.PHP_EOL.
		"\t\t\t".'Flash::notice("No se encontraron '.$options['plural'].'");'.PHP_EOL.
		"\t\t\t".'Tag::resetInput();'.PHP_EOL.
		"\t\t\t".'return $this->routeToAction("index");'.PHP_EOL.
		"\t\t".'}'.PHP_EOL.PHP_EOL.
		"\t\t".'Tag::prependDocumentTitle("Visualizando - ");'.PHP_EOL.
		"\t\t".'$this->setParamToView(\'page\', $page);'.PHP_EOL.
		"\t\t".'$this->setParamToView("'.$options['name'].'", $'.$options['name'].');'.PHP_EOL.
		"\t".'}'.PHP_EOL.PHP_EOL;

		//New
		$code.="\t".'function newAction(){'.PHP_EOL;
		foreach($options['setParams'] as $setParam){
			$code.="\t\t".'$this->setParamToView("'.$setParam['varName'].'", '.'$this->'.$setParam['entity'].'->find(array("order" => "'.$setParam['order'].'")));'.PHP_EOL;
		}
		$code.="\t".'}'.PHP_EOL.PHP_EOL;

		//Edit
		$code.="\t".'function editAction('.$paramsPksString.'){'.PHP_EOL.PHP_EOL.
		"\t\t".'$request = $this->getRequestInstance();'.PHP_EOL.
		"\t\t".'if(!$request->isPost()){'.PHP_EOL.PHP_EOL;
		foreach($options['primaryKeys'] as $primaryKey){
			$methodName = Utils::camelize($primaryKey);
			$code.="\t\t\t".'$'.$primaryKey.' = '.$options['className'].'::sanizite'.$methodName.'("'.$primaryKey.'", $'.$primaryKey.');'.PHP_EOL;
		}
		$code.=PHP_EOL;
		$code.="\t\t\t".'$'.$options['singleVar'].' = $this->'.$options['className'].'->findFirst('.$conditionsPksString.');'.PHP_EOL.
		"\t\t\t".'if($'.$options['singleVar'].'==false){'.PHP_EOL.
		"\t\t\t\t".'Flash::error("No se encontró '.$options['theSingle'].' en consulta");'.PHP_EOL.
		"\t\t\t\t".'return $this->routeToAction(\'index\');'.PHP_EOL.
		"\t\t\t".'}'.PHP_EOL.PHP_EOL;
		foreach($options['attributes'] as $fieldName => $dataType){
			$camelize = Utils::camelize($fieldName);
			$field = Utils::lcfirst($camelize);
			if(isset($autocompleteFields[$fieldName])){
				$fieldConf = $autocompleteFields[$fieldName];
				$detailCamelize = Utils::camelize($fieldConf['detail']);
				$code.="\t\t\t".'Tag::displayTo("'.$field.'", $'.$options['singleVar'].'->get'.$camelize.'());'.PHP_EOL;
				$code.="\t\t\t".'Tag::displayTo("'.$field.'_det", $'.$options['singleVar'].'->get'.$fieldConf['tableName'].'()->get'.$detailCamelize.'());'.PHP_EOL;
			} else {
				if(strpos($dataType, 'date')!==false){
					$code.="\t\t\t".'Tag::displayTo("'.$field.'", (string) $'.$options['singleVar'].'->get'.$camelize.'());'.PHP_EOL;
				} else {
					if(strpos($dataType, 'decimal')!==false){
						$code.="\t\t\t".'Tag::displayTo("'.$field.'", (string) $'.$options['singleVar'].'->get'.$camelize.'());'.PHP_EOL;
					} else {
						$code.="\t\t\t".'Tag::displayTo("'.$field.'", $'.$options['singleVar'].'->get'.$camelize.'());'.PHP_EOL;
					}
				}
			}
		}
		$code.="\t\t".'}'.PHP_EOL;
		$code.=PHP_EOL;
		foreach($options['setParams'] as $setParam){
			$code.="\t\t".'$this->setParamToView("'.$setParam['varName'].'", '.'$this->'.$setParam['entity'].'->find(array("order" => "'.$setParam['order'].'")));'.PHP_EOL;
		}
		$code.="\t".'}'.PHP_EOL.PHP_EOL;

		$exceptions = array();
		foreach($options['attributes'] as $attribute => $x){
			if(preg_match('/_at$/', $attribute)){
				$exceptions[] = '"'.$attribute.'"';
			} else {
				if(preg_match('/_in$/', $attribute)){
					$exceptions[] = '"'.$attribute.'"';
				}
			}
		}

		//createAction
		$code.="\t".'function createAction(){'.PHP_EOL.PHP_EOL.
		"\t\t".'$request = $this->getRequestInstance();'.PHP_EOL.
		"\t\t".'if($request->isPost()){'.PHP_EOL.PHP_EOL.
		"\t\t\t".'$'.$options['singleVar'].' = '.$options['className'].'::factory($request->getPostParams(), array('.join(', ', $exceptions).'));'.PHP_EOL.
		"\t\t\t".'if($'.$options['singleVar'].'->exists()==true){'.PHP_EOL.
		"\t\t\t\t".'Flash::error("'.ucfirst($options['theSingle']).' no puede ser creada porque ya existe");'.PHP_EOL.
		"\t\t\t\t".'return $this->routeToAction("new");'.PHP_EOL.
		"\t\t\t".'}'.PHP_EOL.PHP_EOL.
		"\t\t\t".'if($'.$options['singleVar'].'->save()==false){'.PHP_EOL.
		"\t\t\t\t".'foreach($'.$options['singleVar'].'->getMessages() as $message){'.PHP_EOL.
		"\t\t\t\t\t".'Flash::error((string) $message);'.PHP_EOL.
		"\t\t\t\t".'}'.PHP_EOL.
		"\t\t\t\t".'return $this->routeToAction("new");'.PHP_EOL.
		"\t\t\t".'} else {'.PHP_EOL.
		"\t\t\t\t".'Flash::success("Se creó correctamente '.$options['theSingle'].'");'.PHP_EOL.
		"\t\t\t\t".'Tag::resetInput();'.PHP_EOL.
		"\t\t\t\t".'return $this->routeToAction("index");'.PHP_EOL.
		"\t\t\t".'}'.PHP_EOL.PHP_EOL.
		"\t\t".'} else {'.PHP_EOL.
		"\t\t\t".'return $this->routeToAction("index");'.PHP_EOL.
		"\t\t".'}'.PHP_EOL.PHP_EOL.
		"\t".'}'.PHP_EOL.PHP_EOL;

		//saveAction
		$code.="\t".'function saveAction(){'.PHP_EOL.PHP_EOL.
		"\t\t".'$request = $this->getRequestInstance();'.PHP_EOL.
		"\t\t".'if($request->isPost()){'.PHP_EOL.PHP_EOL.
		"\t\t\t".'$'.$options['singleVar'].' = '.$options['className'].'::factory($request->getPostParams(), array('.join(', ', $exceptions).'));'.PHP_EOL.
		"\t\t\t".'if($'.$options['singleVar'].'->exists()==false){'.PHP_EOL.
		"\t\t\t\t".'Flash::error("'.ucfirst($options['theSingle']).' no puede ser actualizada porque no existe");'.PHP_EOL.
		"\t\t\t\t".'return $this->routeToAction("edit");'.PHP_EOL.
		"\t\t\t".'}'.PHP_EOL.PHP_EOL.
		"\t\t\t".'if($'.$options['singleVar'].'->save()==false){'.PHP_EOL.
		"\t\t\t\t".'foreach($'.$options['singleVar'].'->getMessages() as $message){'.PHP_EOL.
		"\t\t\t\t\t".'Flash::error((string) $message);'.PHP_EOL.
		"\t\t\t\t".'}'.PHP_EOL.
		"\t\t\t\t".'return $this->routeToAction("edit");'.PHP_EOL.
		"\t\t\t".'} else {'.PHP_EOL.
		"\t\t\t\t".'Flash::success("Se actualizó correctamente '.$options['theSingle'].'");'.PHP_EOL.
		"\t\t\t\t".'Tag::resetInput();'.PHP_EOL.
		"\t\t\t\t".'return $this->routeToAction("index");'.PHP_EOL.
		"\t\t\t".'}'.PHP_EOL.PHP_EOL.
		"\t\t".'} else {'.PHP_EOL.
		"\t\t\t".'return $this->routeToAction("index");'.PHP_EOL.
		"\t\t".'}'.PHP_EOL.
		"\t".'}'.PHP_EOL.PHP_EOL;

		//Delete
		$code.="\t".'function deleteAction('.$paramsPksString.'){'.PHP_EOL.PHP_EOL;
		foreach($options['primaryKeys'] as $primaryKey){
			$methodName = Utils::camelize($primaryKey);
			$code.="\t\t".'$'.$primaryKey.' = '.$options['className'].'::sanizite'.$methodName.'("'.$primaryKey.'", $'.$primaryKey.');'.PHP_EOL;
		}
		$code.=PHP_EOL;
		$code.="\t\t".'$'.$options['singleVar'].' = $this->'.$options['className'].'->findFirst('.$conditionsPksString.');'.PHP_EOL.
		"\t\t".'if($'.$options['singleVar'].'==false){'.PHP_EOL.
		"\t\t\t".'Flash::error("No se encontró '.$options['theSingle'].' a borrar");'.PHP_EOL.
		"\t\t\t".'return $this->routeToAction(\'index\');'.PHP_EOL.
		"\t\t".'}'.PHP_EOL.PHP_EOL.
		"\t\t".'if($'.$options['singleVar'].'->delete()==false){'.PHP_EOL.
		"\t\t\t".'foreach($'.$options['singleVar'].'->getMessages() as $message){'.PHP_EOL.
		"\t\t\t\t".'Flash::error((string) $message);'.PHP_EOL.
		"\t\t\t".'}'.PHP_EOL.
		"\t\t\t".'return $this->routeToAction("search");'.PHP_EOL.
		"\t\t".'} else {'.PHP_EOL.
		"\t\t\t".'Flash::success("Se eliminó correctamente '.$options['theSingle'].'");'.PHP_EOL.
		"\t\t\t".'return $this->routeToAction("index");'.PHP_EOL.
		"\t\t".'}'.PHP_EOL.
		"\t".'}'.PHP_EOL.PHP_EOL;

		//Report
		$code.="\t".'function reportAction(){'.PHP_EOL.
		"\t\t".'Tag::prependDocumentTitle("Reporte - ");'.PHP_EOL;
		foreach($options['setParams'] as $setParam){
			$code.="\t\t".'$this->setParamToView("'.$setParam['varName'].'", '.'$this->'.$setParam['entity'].'->find(array("order" => "'.$setParam['order'].'")));'.PHP_EOL;
		}
		$code.="\t".'}'.PHP_EOL.PHP_EOL;

		//Search
		$code.=
		"\t".'function exportAction(){'.PHP_EOL.PHP_EOL.
		"\t\t".'$request = $this->getRequestInstance();'.PHP_EOL.
		"\t\t".'if($request->isPost()==true){'.PHP_EOL.
		"\t\t\t".'$this->conditions = FormCriteria::fromModel("'.$options['className'].'", FormCriteria::POST);'.PHP_EOL.
		"\t\t".'} else {'.PHP_EOL.
		"\t\t\t".'return $this->routeToAction("report");'.PHP_EOL.
		"\t\t".'}'.PHP_EOL.PHP_EOL.
		"\t\t".'$'.$options['name'].' = $this->'.$options['className'].'->find(array($this->conditions, "order" => "'.$orderPksString.'", "limit" => 200));'.PHP_EOL.
		"\t\t".'if(count($'.$options['name'].')==0){'.PHP_EOL.
		"\t\t\t".'Flash::notice("No se encontraron '.$options['plural'].' con el criterio de búsqueda");'.PHP_EOL.
		"\t\t\t".'return $this->routeToAction("report");'.PHP_EOL.
		"\t\t".'}'.PHP_EOL.PHP_EOL.

		"\t\t".'$formatoReporte = $this->getPost("formatoReporte", "alpha");'.PHP_EOL.PHP_EOL.
		"\t\t".'$report = new Report($formatoReporte);'.PHP_EOL.PHP_EOL.
		"\t\t".'$titulo = new ReportText("Reporte de '.$options['plural'].'");'.PHP_EOL.PHP_EOL.

		"\t\t".'$titulo->setAttributes(array(
			"fontWeight" => "bold",
			"textAlign" => "center",
			"fontSize" => "18"
		));'.PHP_EOL.PHP_EOL.

		"\t\t".'$report->setHeader(array($titulo));'.PHP_EOL.PHP_EOL;

		$headers = array();
		foreach($options['attributes'] as $attribute => $dataType){
			$headers[] = "\t\t\t".'"'.i18n::strtoupper($this->_getPosibleLabel($attribute)).'"';
		}

		$code.="\t\t".'$report->setColumnHeaders(array('.PHP_EOL.join(",\n", $headers).PHP_EOL."\t\t".'));'.PHP_EOL.PHP_EOL;

		$code.="\t\t".'$report->start(true);'.PHP_EOL.PHP_EOL;

		$code.="\t\t".'foreach($'.$options['name'].' as $'.$options['singleVar'].'){'.PHP_EOL;
		$code.="\t\t\t".'$report->addRow(array('.PHP_EOL;

		foreach($options['attributes'] as $fieldName => $dataType){
			$camelize = Utils::camelize($fieldName);
			$field = Utils::lcfirst($camelize);
			if(isset($autocompleteFields[$fieldName])){
				$fieldConf = $autocompleteFields[$fieldName];
				$detailCamelize = Utils::camelize($fieldConf['detail']);
				$code.="\t\t\t\t".'$'.$options['singleVar'].'->get'.$fieldConf['tableName'].'()->get'.$detailCamelize.'(),'.PHP_EOL;
			} else {
				if(strpos($dataType, 'date')!==false){
					$code.="\t\t\t\t".'(string) $'.$options['singleVar'].'->get'.$camelize.'(),'.PHP_EOL;
				} else {
					if(strpos($dataType, 'decimal')!==false){
						$code.="\t\t\t\t".'(string) $'.$options['singleVar'].'->get'.$camelize.'(),'.PHP_EOL;
					} else {
						$code.="\t\t\t\t".'$'.$options['singleVar'].'->get'.$camelize.'(),'.PHP_EOL;
					}
				}
			}
		}
		$code.="\t\t\t".'));'.PHP_EOL;
		$code.="\t\t".'}'.PHP_EOL.PHP_EOL;

		$code.="\t\t".'$report->finish();'.PHP_EOL.PHP_EOL;

		$code.="\t\t".'$archivo = $report->outputToFile("public/'.$options['name'].'");'.PHP_EOL;
		$code.="\t\t".'$this->setParamToView("archivo", $archivo);'.PHP_EOL.PHP_EOL;

		$code.="\t\t".'return $this->routeToAction("report");'.PHP_EOL.PHP_EOL;

		$code.="\t".'}'.PHP_EOL.PHP_EOL;

		//Add query of autocomplete fields
		$autocompleteFields = $options['autocompleteFields'];
		foreach($autocompleteFields as $fieldName => $fieldConfig){
			$camelize = Utils::camelize($fieldName);
			$code .= "\t".'function query'.$camelize.'Action(){'.PHP_EOL.
			"\t\t".'$this->setResponse(\'json\');'.PHP_EOL.
			"\t\t".'$data = array();'.PHP_EOL.
			"\t\t".'$paramValue = $this->getPostparam("data", "striptags");'.PHP_EOL.
			"\t\t".'if($paramValue){'.PHP_EOL.
			"\t\t\t".'$results = $this->'.$fieldConfig['tableName'].'->find(array(\''.$fieldConfig['detail'].' LIKE "\'.$paramValue.\'%"\', "limit" => 10));'.PHP_EOL.
			"\t\t\t".'foreach($results as $row){'.PHP_EOL.
			"\t\t\t\t".'$data[] = array("key" => $row->readAttribute("'.$fieldConfig['primaryKey'].'"), "value" => $row->readAttribute("'.$fieldConfig['detail'].'"));'.PHP_EOL.
			"\t\t\t".'}'.PHP_EOL.
			"\t\t".'}'.PHP_EOL.
			"\t\t".'return $data;'.PHP_EOL.
			"\t".'}'.PHP_EOL.PHP_EOL;
		}
		$code .= "".'}'.PHP_EOL.PHP_EOL;

		file_put_contents($controllerPath, $code);
	}

	/**
	 * make layouts of model by scaffold
	 *
	 * @param array $options
	 */
	private function _makeLayouts($options){

		//Make Layouts dir
		$dirPathLayouts	= 'apps/'.$options['application'].'/views/layouts';
		//If not exists dir; we make it
		if(is_dir($dirPathLayouts)==false){
			mkdir($dirPathLayouts);
		}
		$viewPath = $dirPathLayouts.'/'.$options['name'].'.phtml';

		//View model layout
		$code = '';
		if(isset($options['theme'])){
			$code.='<?php Tag::stylesheetLink("themes/lightness/style") ?>'.PHP_EOL;
			$code.='<?php Tag::stylesheetLink("themes/base") ?>'.PHP_EOL;
		}

		if(isset($options['theme'])){
			$code.='<div class="ui-layout" align="center">'.PHP_EOL;
		} else {
			$code.='<div align="center">'.PHP_EOL;
		}
		$code.="\t".'<?php View::getContent() ?>'.PHP_EOL.
		'</div>';
		file_put_contents($viewPath, $code);
	}

	/**
	 * Make field to diferent actions
	 *
	 * @param array $options
	 * @param string $action
	 *
	 * @return string $code
	 */
	private function _makeFields($options, $action){
		$code = '';
		$entity	= $options['entity'];
		$relationField = $options['relationField'];
		$autocompleteFields	= $options['autocompleteFields'];
		$selectDefinition = $options['selectDefinition'];
		foreach($entity->getDataTypes() as $attribute => $dataType){
			if(!preg_match('/_at$/', $attribute)){
				$camelize = Utils::lcfirst(Utils::camelize($attribute));
				$code.= "\t\t".'<tr>'.PHP_EOL.
				"\t\t\t".'<td align="right">'.PHP_EOL;
				if(($action=='new'||$action=='edit' ) && $attribute=='id'){
				}else{
					$code .= "\t\t\t\t".'<label for="'.$camelize.'">'.$this->_getPosibleLabel($attribute).'</label>'.PHP_EOL;
				}
				$code .= "\t\t\t".'</td>'.PHP_EOL.
				"\t\t\t".'<td align="left">';
				if(isset($relationField[$attribute])){
					//Autocomplete
					if(isset($autocompleteFields[$attribute])){
						$code.=PHP_EOL."\t\t\t\t".'<?php echo Tag::hiddenField(array("'.$camelize.'")), Tag::textFieldWithAutocomplete(array("'.$camelize.'_det", "action" => "'.$options['name'].'/query'.ucfirst($camelize).'")) ?>';
					} else {
						$code.=PHP_EOL."\t\t\t\t".'<?php echo Tag::select(array("'.$camelize.'", $'.$selectDefinition[$attribute]['varName'].
						', "using" => "'.$selectDefinition[$attribute]['primaryKey'].','.$selectDefinition[$attribute]['detail'].'", "useDummy"=>true)) ?>';
					}
				} else {
					//PKs
					if(($action=='new'||$action=='edit' ) && $attribute=='id'){
						$code.=PHP_EOL."\t\t\t\t".'<?php echo Tag::hiddenField(array("'.$camelize.'")) ?>';
					} else {
						//Char Field
						if(strpos($dataType, 'char')!==false){
							if(preg_match('/[a-z]+\(([0-9]+)\)/', $dataType, $matches)){
								if($matches[1]>15){
									$size = floor($matches[1]*0.35);
								} else {
									$size = $matches[1];
								}
								$maxlength = $matches[1];
							}
							$code.=PHP_EOL."\t\t\t\t".'<?php echo Tag::textField(array("'.$camelize.'", "size" => '.$size.', "maxlength" => '.$maxlength.')) ?>';
						} else {
							//Decimal field
							if(strpos($dataType, 'decimal')!==false || strpos($dataType, 'int')!==false){
								if(preg_match('/[a-z]+\(([0-9]+)\)/', $dataType, $matches)){
									if($matches[1]>15){
										$size = floor($matches[1]*0.50);
									} else {
										$size = $matches[1];
									}
									$maxlength = $matches[1];
								}
								$code.=PHP_EOL."\t\t\t".'<?php echo Tag::numericField(array("'.$camelize.'", "size" => '.$size.', "maxlength" => '.$maxlength.')) ?>';
							} else {
								//Enum field
								if(strpos($dataType, 'enum')!==false){
									$domain = array();
									if(preg_match('/\((.*)\)/', $dataType, $matches)){
										foreach(explode(',', $matches[1]) as $item){
											$item = strtoupper(str_replace("'", '', $item));
											$domain[$item] = $item;
										}
									}
									$varItems = str_replace(array("\n", " "), '', var_export($domain, true));
									$code.=PHP_EOL."\t\t\t\t".'<?php echo Tag::selectStatic(array("'.$camelize.'", '.$varItems.', "useDummy" => true)) ?>';
								} else {
									//Date field
									if(strpos($dataType, 'date')!==false){
										$code.=PHP_EOL."\t\t\t\t".'<?php echo Tag::dateField(array("'.$camelize.'", "useDummy" => true, "calendar" => true)) ?>';
									}
								}
							}
						}
					}
				}
				$code.=PHP_EOL."\t\t\t".'</td>';
			}
			$code.=PHP_EOL."\t\t".'</tr>'.PHP_EOL;
		}
		return $code;
	}

	/**
	 * make views index.phtml of model by scaffold
	 *
	 * @param array $options
	 */
	private function _makeViewIndex($options){

		$dirPath = 'apps/'.$options['application'].'/views/'.$options['name'];
		if(is_dir($dirPath)==false){
			mkdir($dirPath);
		}

		$relationField = $options['relationField'];
		$belongsToDefinitions = $options['belongsToDefinitions'];
		$selectDefinition = $options['selectDefinition'];
		$autocompleteFields	= $options['autocompleteFields'];

		$entity = EntityManager::get($options['className']);
		$options['entity'] = $entity;

		$plural = $options['plural'];
		$name = $options['name'];

		$viewPath = $dirPath.'/index.phtml';

		$code = '<?php View::getContent() ?>'.PHP_EOL.
		'<div align="right">'.PHP_EOL.
		"\t".'<?php echo Tag::inputButtonToAction("Reporte", "'.$options['name'].'/report") ?> '.
		"\t".'<?php echo Tag::inputButtonToAction("Crear '.ucfirst($options['single']).'", "'.$options['name'].'/new") ?>'.PHP_EOL.
		'</div>'.PHP_EOL.PHP_EOL.
		'<div align="center">'.PHP_EOL.
		"\t".'<h1>Buscar '.$plural.'</h1>'.PHP_EOL.
		"\t".'<?php echo Tag::form("'.$options['name'].'/search") ?>'.PHP_EOL.
		"\t".'<table align="center">'.PHP_EOL;

		//make fields by action
		$code.= self::_makeFields($options, 'index');

		$code.= PHP_EOL.
		"\t\t".'<tr>'.PHP_EOL.
		"\t\t\t".'<td></td><td><?php echo Tag::submitButton("Buscar") ?></td>'.PHP_EOL.
		"\t\t".'</tr>'.PHP_EOL;

		$code.= "\t".'</table>'.PHP_EOL.
		'<?php echo Tag::endForm() ?>'.PHP_EOL.
		'</div>';

		//index.phtml
		file_put_contents($viewPath, $code);
	}

	/**
	 * make views index.phtml of model by scaffold
	 *
	 * @param array $options
	 */
	private function _makeViewReport($options){

		$dirPath = 'apps/'.$options['application'].'/views/'.$options['name'];
		if(is_dir($dirPath)==false){
			mkdir($dirPath);
		}

		$relationField = $options['relationField'];
		$belongsToDefinitions = $options['belongsToDefinitions'];
		$selectDefinition = $options['selectDefinition'];
		$autocompleteFields	= $options['autocompleteFields'];

		$entity = EntityManager::get($options['className']);
		$options['entity'] = $entity;

		$plural = $options['plural'];
		$name = $options['name'];

		$viewPath = $dirPath.'/report.phtml';

		$code = '<?php View::getContent() ?>'.PHP_EOL.
		'<table width="100%">'.PHP_EOL.
		"\t".'<tr>'.PHP_EOL.
		"\t\t".'<td align="left"><?php echo Tag::inputButtonToAction("Volver", "'.$options['name'].'") ?></td>'.PHP_EOL.
		"\t".'<tr>'.PHP_EOL.
		'</table>'.PHP_EOL.PHP_EOL.
		'<div align="center">'.PHP_EOL.
		"\t".'<h1>Reporte de '.$plural.'</h1>'.PHP_EOL.
		"\t".'<?php echo Tag::form("'.$options['name'].'/export") ?>'.PHP_EOL.
		"\t".'<table align="center">'.PHP_EOL;

		//make fields by action
		$code.= self::_makeFields($options, 'index');

		$code.= PHP_EOL.
		"\t\t".'<tr bgcolor="#eaeaea">'.PHP_EOL.
		"\t\t\t".'<td align="right"><label>Formato</label></td><td><?php echo Tag::selectStatic("formatoReporte", array("Html" => "Web", "Excel" => "Excel", "Pdf" => "PDF", "Text" => "Texto Plano")) ?></td>'.PHP_EOL.
		"\t\t".'</tr>'.PHP_EOL.
		"\t\t".'<tr>'.PHP_EOL.
		"\t\t\t".'<td></td><td><?php echo Tag::submitButton("Reporte") ?></td>'.PHP_EOL.
		"\t\t".'</tr>'.PHP_EOL;

		$code.= "\t".'</table>'.PHP_EOL.
		'<?php echo Tag::endForm() ?>'.PHP_EOL.PHP_EOL.
		'</div>';

		$code.= "\t".'<?php if(isset($archivo)){ echo "<script type=\'text/javascript\'>window.open(\\$Kumbia.path+\'", $archivo, "\')</script>"; } ?>'.PHP_EOL;

		//index.phtml
		file_put_contents($viewPath, $code);
	}

	/**
	 * make views index.phtml of model by scaffold
	 *
	 * @param array $options
	 */
	private function _makeViewNew($options){

		$dirPath = 'apps/'.$options['application'].'/views/'.$options['name'];
		if(is_dir($dirPath)==false){
			mkdir($dirPath);
		}

		$relationField = $options['relationField'];
		$belongsToDefinitions = $options['belongsToDefinitions'];
		$selectDefinition = $options['selectDefinition'];
		$autocompleteFields	= $options['autocompleteFields'];

		$entity = EntityManager::get($options['className']);
		$options['entity'] = $entity;

		$plural = $options['plural'];
		$name = $options['name'];

		$viewPath = $dirPath.'/new.phtml';

		$code = '<?php View::getContent() ?>'.PHP_EOL.PHP_EOL;
		$code.= '<?php echo Tag::form("'.$options['name'].'/create") ?>'.PHP_EOL.PHP_EOL.
		'<table width="100%">'.PHP_EOL.
		"\t".'<tr>'.PHP_EOL.
		"\t\t".'<td align="left"><?php echo Tag::inputButtonToAction("Volver", "'.$options['name'].'") ?></td>'.PHP_EOL.
		"\t\t".'<td align="right"><?php echo Tag::submitButton("Grabar") ?></td>'.PHP_EOL.
		"\t".'<tr>'.PHP_EOL.
		'</table>'.PHP_EOL.PHP_EOL.
		'<div align="center">'.PHP_EOL.
		"\t".'<h1>Creando '.$options['single'].'</h1>'.PHP_EOL.
		'</div>'.PHP_EOL.PHP_EOL.
		"\t".'<table align="center">'.PHP_EOL;

		//make fields by action
		$code.= self::_makeFields($options, 'new');

		$code.= "\t".'</table>'.PHP_EOL.
		"\t".'<?php echo Tag::endForm() ?>'.PHP_EOL;

		//index.phtml
		file_put_contents($viewPath, $code);
	}

	/**
	 * make views index.phtml of model by scaffold
	 *
	 * @param array $options
	 */
	private function _makeViewEdit($options){

		$dirPath = 'apps/'.$options['application'].'/views/'.$options['name'];
		if(is_dir($dirPath)==false){
			mkdir($dirPath);
		}

		$relationField = $options['relationField'];
		$belongsToDefinitions = $options['belongsToDefinitions'];
		$selectDefinition = $options['selectDefinition'];
		$autocompleteFields	= $options['autocompleteFields'];

		$entity = EntityManager::get($options['className']);
		$options['entity'] = $entity;

		$plural = $options['plural'];
		$name = $options['name'];

		$viewPath = $dirPath.'/edit.phtml';

		$code = '<?php View::getContent() ?>'.PHP_EOL.PHP_EOL;
		$code.= '<?php echo Tag::form("'.$options['name'].'/save") ?>'.PHP_EOL.PHP_EOL.
		'<table width="100%">'.PHP_EOL.
		"\t".'<tr>'.PHP_EOL.
		"\t\t".'<td align="left"><?php echo Tag::inputButtonToAction("Volver", "'.$options['name'].'") ?></td>'.PHP_EOL.
		"\t\t".'<td align="right"><?php echo Tag::submitButton("Actualizar") ?></td>'.PHP_EOL.
		"\t".'<tr>'.PHP_EOL.
		'</table>'.PHP_EOL.PHP_EOL.
		'<div align="center">'.PHP_EOL.
		"\t".'<h1>Editando '.$options['aSingle'].'</h1>'.PHP_EOL.
		'</div>'.PHP_EOL.PHP_EOL.
		"\t".'<table align="center">'.PHP_EOL;

		//make fields by action
		$code.= self::_makeFields($options, 'new');

		$code.= "\t".'</table>'.PHP_EOL.
		"\t".'<?php echo Tag::endForm() ?>'.PHP_EOL;

		//index.phtml
		file_put_contents($viewPath, $code);
	}

	/**
	 * make view search.phtml of model by scaffold
	 *
	 * @param array $options
	 */
	private function _makeViewSearch($options){

		//View model layout
		$dirPath = 'apps/'.$options['application'].'/views/'.$options['name'];
		$viewPath = $dirPath.'/search.phtml';

		$code = '<table width="100%">'.PHP_EOL.
		"\t".'<tr>'.PHP_EOL.
		"\t\t".'<td align="left"><?php echo Tag::inputButtonToAction("Volver", "'.$options['name'].'") ?></td>'.PHP_EOL.
		"\t\t".'<td align="right"><?php echo Tag::inputButtonToAction("Reporte", "'.$options['name'].'/report") ?> <?php echo Tag::inputButtonToAction("Crear '.ucfirst($options['single']).'", "'.$options['name'].'/new") ?></td>'.PHP_EOL.
		"\t".'<tr>'.PHP_EOL.
		'</table>'.PHP_EOL.PHP_EOL.
		'<?php $pages = Tag::paginate($'.$options['name'].', $page, 10); ?>'.PHP_EOL.PHP_EOL.
		'<table class="browse" align="center">'.PHP_EOL.
		"\t".'<thead>'.PHP_EOL.
		"\t\t".'<tr>'.PHP_EOL;
		foreach($options['attributes'] as $attribute => $dataType){
			$code.="\t\t\t".'<th>'.$this->_getPosibleLabel($attribute).'</th>'.PHP_EOL;
		}
		$code.="\t\t".'</tr>'.PHP_EOL.
		"\t".'</thead>'.PHP_EOL.
		"\t".'<tbody>'.PHP_EOL.
		"\t".'<?php foreach($pages->items as $'.$options['singleVar'].'){ ?>'.PHP_EOL.
		"\t\t".'<tr>'.PHP_EOL;
		$options['allReferences'] = array_merge($options['autocompleteFields'], $options['selectDefinition']);
		foreach($options['attributes'] as $fieldName => $dataType){
			$code.="\t\t\t".'<td><?php echo ';
			if(!isset($options['allReferences'][$fieldName])){
				$camelize = Utils::camelize($fieldName);
				if(strpos($dataType, 'date')!==false){
					$code.='(string) $'.$options['singleVar'].'->get'.$camelize.'()';
				} else {
					if(strpos($dataType, 'decimal')!==false){
						$code.='(string) $'.$options['singleVar'].'->get'.$camelize.'()';
					} else {
						$code.='$'.$options['singleVar'].'->get'.$camelize.'()';
					}
				}
			} else {
				$detailField = ucfirst($options['allReferences'][$fieldName]['detail']);
				$code.='$'.$options['singleVar'].'->get'.$options['allReferences'][$fieldName]['tableName'].'()->get'.$detailField.'()';
			}
			$code.=' ?></td>'.PHP_EOL;
		}
		$primaryKeyCode = array();
		foreach($options['primaryKeys'] as $primaryKey){
			$camelize = Utils::camelize($primaryKey);
			$primaryKeyCode[] = '$'.$options['singleVar'].'->get'.$camelize.'()';
		}
		$code.="\t\t\t".'<td><?php echo Tag::linkTo("'.$options['name'].'/edit/".'.join('/', $primaryKeyCode).', "Editar"); ?></td>'.PHP_EOL;
		$code.="\t\t\t".'<td><?php echo Tag::linkTo("'.$options['name'].'/delete/".'.join('/', $primaryKeyCode).', "Borrar"); ?></td>'.PHP_EOL;

		$code.="\t\t".'</tr>'.PHP_EOL.
		"\t".'<?php } ?>'.PHP_EOL.
		"\t".'</tbody>'.PHP_EOL.
		"\t".'<tbody>'.PHP_EOL.
		"\t\t".'<tr>'.PHP_EOL.
		"\t\t\t".'<td colspan="'.count($options['attributes']).'" align="right">'.PHP_EOL.
		"\t\t\t\t".'<table align="center">'.PHP_EOL.
		"\t\t\t\t\t".'<tr>'.PHP_EOL.
		"\t\t\t\t\t\t".'<td><?php echo Tag::inputButtonToAction("Primera", "'.$options['name'].'/search") ?></td>'.PHP_EOL.
		"\t\t\t\t\t\t".'<td><?php echo Tag::inputButtonToAction("Anterior", "'.$options['name'].'/search?page=".$pages->before) ?></td>'.PHP_EOL.
		"\t\t\t\t\t\t".'<td><?php echo Tag::inputButtonToAction("Siguiente", "'.$options['name'].'/search?page=".$pages->next) ?></td>'.PHP_EOL.
		"\t\t\t\t\t\t".'<td><?php echo Tag::inputButtonToAction("Última", "'.$options['name'].'/search?page=".$pages->last) ?></td>'.PHP_EOL.
		"\t\t\t\t\t\t".'<td><?php echo $pages->current, "/", $pages->total_pages ?></td>'.PHP_EOL.
		"\t\t\t\t\t".'</tr>'.PHP_EOL.
		"\t\t\t\t".'</table>'.PHP_EOL.
		"\t\t\t".'</td>'.PHP_EOL.
		"\t\t".'</tr>'.PHP_EOL.
		"\t".'<tbody>'.PHP_EOL.
		'</table>';
		file_put_contents($viewPath, $code);
	}
}

