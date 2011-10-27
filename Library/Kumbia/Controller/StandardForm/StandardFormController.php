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
 * @package		Controller
 * @subpackage	StandardForm
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: StandardFormController.php 122 2010-02-11 19:09:18Z gutierrezandresfelipe $
 */

/**
 * StandardFormController
 *
 * La Clase StandardForm es la base principal para la generacin de formularios
 * de tipo Standard
 *
 * Notas de Version:
 * Desde Kumbia-0.4.7, StandardForm mantiene los valores de la entrada
 * cuando los metodos before_ o validation devuelven false;
 *
 * @category	Kumbia
 * @package		Controller
 * @subpackage	StandardForm
 * @copyright	Copyright (c) 2008-2010 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 */
abstract class StandardForm extends Controller {

	/**
	 * Tabla con la que trabaja el formulario
	 *
	 * @var string
	 */
	protected $source;

	/**
	 * Lista de campos a ignorar en la generacion
	 *
	 * @var array
	 */
	protected $ignoreList = array();

	/**
	 * Array que contiene los meta-datos internos
	 * para generar el formulario
	 *
	 * @var string
	 */
	protected $form = array();

	/**
	 * Hace que el formulario no sea persistente
	 *
	 * @var boolean
	 * @staticvar
	 */
	static public $force = false;

	/**
	 * Numero de Minutos que el layout ser� cacheado
	 *
	 * @var integer
	 */
	protected $cacheLayout = 0;

	/**
	 * Numero de Minutos que la vista será cacheada
	 *
	 * @var integer
	 */
	public $cache_view = 0;

	/**
	 * Indica si se deben leer los datos de la base
	 * de datos para generar el formulario
	 *
	 * @var boolean
	 */
	public $scaffold = false;

	/**
	 * Indica el tipo de respuesta que será generado
	 *
	 * @var string
	 */
	public $view;

	/**
	 * Indica si se debe mantener los valores del
	 * formulario o no
	 *
	 * @var boolean
	 */
	public $keep_action = true;

	/**
	 * Último fetch realizado en la consulta
	 *
	 * @var int
	 */
	private $_lastFetch = 0;

	/**
	 * Mensaje de exito al insertar
	 *
	 * @var string
	 */
	public $successInsertMessage = '';

	/**
	 * Mensaje de Fallo al insertar
	 *
	 * @var string
	 */
	public $failureInsertMessage = '';

	/**
	 * Mensaje de Suceso al Actualizar
	 *
	 * @var string
	 */
	public $successUpdateMessage = "";

	/**
	 * Mensaje de Fallo al Actualizar
	 *
	 * @var string
	 */
	public $failureUpdateMessage = "";

	/**
	 * Mensaje de Exito al Borrar
	 *
	 * @var string
	 */
	public $successDeleteMessage = "";

	/**
	 * Mensaje de fallo al borrar
	 *
	 * @var string
	 */
	public $failureDeleteMessage = "";

	/**
	 * SQL que contiene la ultima consulta efectuada
	 *
	 * @var string
	 */
	protected $query;

	/**
	 * Constructor de la clase
	 *
	 */
	public function __construct(){
		$this->setPersistance(true);
		if(method_exists($this, 'initialize')){
			$this->initialize();
		}
	}

	/**
	 * Emula la acción Report llamando a show
	 *
	 * @access public
	 */
	public function reportAction(){
		$this->view = 'index';
		$modelName = EntityManager::getEntityName($this->getSource());
		if(!EntityManager::isEntity($modelName)){
			throw new StandardFormException('No hay un modelo "'.$modelName.'" para hacer la operación de reporte');
			return $this->routeTo(array('action' => 'index'));
		}
		if(!$this->{$modelName}->isDumped()){
			$this->{$modelName}->dumpModel();
		}
		foreach($this->{$modelName}->getAttributesNames() as $field_name){
			if(isset($_REQUEST["fl_$field_name"])){
				$this->{$modelName}->$field_name = $_REQUEST["fl_$field_name"];
			}
		}

		/**
		 * Busca si existe un método o un llamado variable al método
		 * beforeReport, si este método devuelve false termina la ejecución
		 * de la acción
		 */
		if(method_exists($this, 'beforeReport')){
			if($this->beforeReport()===false){
				return null;
			}
			if(Router::getRouted()){
				return null;
			}
		} else {
			if(isset($this->beforeReport)){
				if($this->{$this->beforeReport}()===false){
					return null;
				}
				if(Router::getRouted()){
					return null;
				}
			}
		}

		Generator::scaffold($this->form, $this->scaffold);
		GeneratorReport::generate($this->form);

		/**
		 * Busca si existe un método o un llamado variable al método
		 * afterInsert, si este método devuelve false termina la ejecución
		 * de la acción
		 */
		if(method_exists($this, 'afterReport')){
			if($this->afterReport()===false){
				return null;
			}
			if(Router::getRouted()){
				return null;
			}
		} else {
			if(isset($this->afterReport)){
				if($this->{$this->afterReport}()===false){
					return null;
				}
				if(Router::getRouted()){
					return null;
				}
			}
		}
		return $this->routeTo(array('action' => 'index'));
	}

	/**
	 * Devuelve el source
	 *
	 * @return string
	 */
	public function getSource(){
		if($this->source==''){
			$controller = Router::getController();
			ActiveRecordUtils::sqlSanizite($controller);
			$this->source = $controller;
		}
		return $this->source;
	}

	/**
	 * Metodo Insert por defecto del Formulario
	 *
	 */
	public function insertAction(){


		$controllerRequest = ControllerRequest::getInstance();
		if($controllerRequest->isPost()){

			$this->view = 'index';
			$this->keep_action = '';

			Generator::scaffold($this->form, $this->scaffold);

			$modelName = EntityManager::getEntityName($this->getSource());
			if(!EntityManager::isEntity($modelName)){
				throw new StandardFormException('No hay un modelo "'.$modelName.'" para hacer la operación de inserción');
				return $this->routeTo(array('action' => 'index'));
			}

			if(!$this->{$modelName}->isDumped()){
				$this->{$modelName}->dumpModel();
			}

			foreach($this->{$modelName}->getAttributesNames() as $field_name){
				if(isset($_REQUEST["fl_$field_name"])){
					$this->{$modelName}->$field_name = $_REQUEST["fl_$field_name"];
				}
			}

			/**
			 * Busca si existe un método o un llamado variable al método
			 * validation, si este método devuelve false termina la ejecución
			 * de la acción
			 */
			if(method_exists($this, 'validation')){
				if($this->validation()===false){
					$this->keep_action = 'insert';
					if(!Router::getRouted()){
						return $this->routeTo(array('action' => 'index'));
					}
				}
				if(Router::getRouted()){
					return;
				}
			} else {
				if(isset($this->validation)){
					if($this->{$this->validation}()===false){
						$this->keep_action = 'insert';
						if(!Router::getRouted()){
							return $this->routeTo(array('action' => 'index'));
						}
					}
					if(Router::getRouted()){
						return;
					}
				}
			}

			/**
			 * Busca si existe un método o un llamado variable al método
			 * beforeInsert, si este método devuelve false termina la ejecucin
			 * de la acción
			 */
			if(method_exists($this, 'beforeInsert')){
				if($this->beforeInsert()===false){
					$this->keep_action = 'insert';
					if(!Router::getRouted()){
						return $this->routeTo(array('action' => 'index'));
					}
				}
				if(Router::getRouted()){
					return;
				}
			} else {
				if(isset($this->beforeInsert)){
					if($this->{$this->beforeInsert}()===false){
						$this->keep_action = 'insert';
						if(!Router::getRouted()){
							return $this->routeTo(array('action' => 'index'));
						}
					}
					if(Router::getRouted()){
						return;
					}

				}
			}

			/**
			 * Subimos los archivos de Imagenes del Formulario
			 */
			foreach($this->form['components'] as $fkey => $rrow){
				if($this->form['components'][$fkey]['type']=='image'){
					if(isset($_FILES['fl_'.$fkey])){
						move_uploaded_file($_FILES['fl_'.$fkey]['tmp_name'], htmlspecialchars('public/img/upload/'.$_FILES['fl_'.$fkey]['name']));
						$this->{$modelName}->$fkey = urlencode(htmlspecialchars('upload/'.$_FILES['fl_'.$fkey]['name']));
					}
				}
			}

			/**
			 * Utilizamos el modelo ActiveRecord para insertar el registro
			 * por lo tanto los
			 */
			$attributes = $this->$modelName->getPrimaryKeyAttributes();
			foreach($attributes as $attribute){
				if($attribute=='id'){
					$this->{$modelName}->id = null;
				}
			}

			if($this->{$modelName}->create()==true){
				if($this->successInsertMessage){
					Flash::success($this->successInsertMessage);
				} else {
					Flash::success("Se insertó correctamente el registro");
				}
			} else {
				foreach($this->{$modelName}->getMessages() as $message){
					Flash::error($message->getMessage());
				}
				if(isset($this->failuresInsertMessage)&&$this->failuresInsertMessage!=""){
					Flash::error($this->failureInsertMessage);
				} else {
					Flash::error("Hubo un error al insertar el registro");
					$this->keep_action = 'insert';
					if(Router::getRouted()==false){
						return $this->routeTo(array('action' => 'index'));
					}
				}
			}

			foreach($this->{$modelName}->getAttributesNames() as $fieldName){
				if(isset($_REQUEST['fl_'.$fieldName])){
					$_REQUEST['fl_'.$fieldName] = $this->{$modelName}->readAttribute($fieldName);
				}
			}

			/**
			 * Busca si existe un método o un llamado variable al método
			 * after_insert
			 */
			if(method_exists($this, 'afterInsert')){
				$this->afterInsert();
				if(Router::getRouted()){
					return;
				}
			} else {
				if(isset($this->afterInsert)){
					$this->{$this->afterInsert}();
				}
				if(Router::getRouted()){
					return;
				}
			}
		} else {
			Flash::error('Debe volver a digitar los datos del formulario');
		}

		// Muestra el Formulario en la accion show
		return $this->routeTo(array('action' => 'index'));

	}

	/**
	 * Emula la acción Update llamando a show
	 *
	 * @access public
	 */
	public function updateAction(){

		$this->view = 'index';
		$this->keep_action = '';

		Generator::scaffold($this->form, $this->scaffold);

		$modelName = EntityManager::getEntityName($this->getSource());
		if(!EntityManager::isEntity($modelName)){
			throw new StandardFormException('No hay un modelo "'.$this->getSource().'" para hacer la operación de actualización');
			return $this->routeTo(array('action' => 'index'));
		}

		if($this->{$modelName}->isDumped()==false){
			$this->{$modelName}->dumpModel();
		}

		/**
		 * Subimos los archivos de Imágenes del Formulario
		 */
		foreach($this->form['components'] as $fkey => $rrow){
			if($this->form['components'][$fkey]['type']=='image'){
				if(isset($_FILES['fl_'.$fkey])){
					move_uploaded_file($_FILES['fl_'.$fkey]['tmp_name'], htmlspecialchars('public/img/upload/'.$_FILES['fl_'.$fkey]['name']));
					$this->{$modelName}->$fkey = urlencode(htmlspecialchars('upload/'.$_FILES['fl_'.$fkey]['name']));
				}
			}
		}

		$primaryKey = array();
		foreach($this->form['components'] as $fkey => $rrow){
			if(isset($rrow['primary'])&&$rrow['primary']==1){
				if(isset($_REQUEST["fl_$fkey"])){
					$primaryKey[] = "$fkey = '".$_REQUEST["fl_$fkey"]."'";
				} else {
					Flash::error('Datos incorrectos de actualización');
					return $this->routeTo('action: index');
				}
			}
		}

		if(count($primaryKey)){
			$this->{$modelName}->findFirst(join(' AND ', $primaryKey));
		}

		foreach($this->{$modelName}->getAttributes() as $fieldName){
			if(isset($_REQUEST["fl_$fieldName"])){
				$this->{$modelName}->writeAttribute($fieldName, $_REQUEST["fl_$fieldName"]);
			}
		}

		/**
		 * Busca si existe un método o un llamado variable al método
		 * validation, si este método devuelve false termina la ejecución
		 * de la acción
		 */
		if(method_exists($this, 'validation')){
			if($this->validation()===false){
				$this->keep_action = 'update';
				if(!Router::getRouted()){
					return $this->routeTo(array('action' => 'index'));
				}
			}
			if(Router::getRouted()){
				return;
			}
		} else {
			if(isset($this->validation)){
				if($this->{$this->validation}()===false){
					$this->keep_action = 'update';
					if(!Router::getRouted()){
						return $this->routeTo(array('action' => 'index'));
					}
				}
				if(Router::getRouted()){
					return;
				}
			}
		}

		/**
		 * Busca si existe un metodo o un llamado variable al metodo
		 * before_update, si este metodo devuelve false termina la ejecucion
		 * de la accion
		 */
		if(method_exists($this, 'beforeUdate')){
			if($this->beforeUpdate()===false){
				$this->keep_action = 'update';
				if(!Router::getRouted()){
					return $this->routeTo(array('action' => 'index'));
				}
			}
			if(Router::getRouted()){
				return null;
			}
		} else {
			if(isset($this->beforeUpdate)){
				if($this->{$this->beforeUpdate}()===false){
					$this->keep_action = 'update';
					if(!Router::getRouted()){
						return $this->routeTo(array('action' => 'index'));
					}
				}
				if(Router::getRouted()){
					return null;
				}
			}
		}

		/**
		 * Utilizamos el modelo ActiveRecord para actualizar el registro
		 */
		if($this->{$modelName}->update()==true){
			if($this->successUpdateMessage){
				Flash::success($this->successUpdateMessage);
			} else {
				Flash::success('Se actualizó correctamente el registro');
			}
		} else {
			$this->keep_action = 'update';
			foreach($this->{$modelName}->getMessages() as $message){
				Flash::error($message->getMessage());
			}
			if($this->failureUpdateMessage){
				Flash::error($this->failureUpdateMessage);
			} else {
				Flash::error('Hubo un error al actualizar el registro');
			}
			$_REQUEST['queryStatus'] = 1;
			$_REQUEST['id'] = $this->{$modelName}->readAttribute('id');
			return $this->routeTo(array('action' => 'index'));
		}

		foreach($this->{$modelName}->getAttributes() as $fieldName){
			$_REQUEST['fl_'.$fieldName] = $this->{$modelName}->readAttribute($fieldName);
		}

		/**
		 * Busca si existe un método o un llamado variable al método
		 * afterUpdate
		 */
		if(method_exists($this, 'afterUpdate')){
			$this->afterUpdate();
			if(Router::getRouted()){
				return;
			}
		} else {
			if(isset($this->afterUpdate)){
				$this->{$this->afterUpdate}();
				if(Router::getRouted()){
					return;
				}
			}
		}

		// Muestra el Formulario en la accion index
		return $this->routeTo(array('action' => 'index'));

	}

	/**
	 * Esta acción se emplea al generarse un error de validación al actualizar
	 *
	 * @access public
	 */
	public function checkAction(){
		$_REQUEST['queryStatus'] = true;
	}

	/**
	 * Permite mostrar/ocultar los asteriscos al lado
	 * de los componentes del formulario
	 *
	 * @access public
	 * @param boolean $option
	 */
	public function showNotNulls($option = true){
		$this->form['show_not_nulls'] = $option;
	}

	/**
	 * Emula la accion Delete llamando a show
	 *
	 * @access public
	 */
	public function deleteAction(){

		$this->view = 'index';

		Generator::scaffold($this->form, $this->scaffold);

		$modelName = EntityManager::getEntityName($this->getSource());
		if(!EntityManager::isEntity($modelName)){
			throw new StandardFormException('No hay un modelo "'.$this->getSource().'" para hacer la operación de actualización');
			return $this->routeTo(array('action' => 'index'));
		}

		if(!$this->{$modelName}->isDumped()){
			$this->{$modelName}->dumpModel();
		}

		foreach($this->{$modelName}->getAttributesNames() as $fieldName){
			if(isset($_REQUEST["fl_$fieldName"])){
				$this->{$modelName}->$fieldName = $_REQUEST["fl_$fieldName"];
			} else {
				$this->{$modelName}->$fieldName = "";
			}
		}

		/**
		 * Busca si existe un método o un llamado variable al método
		 * before_delete, si este método devuelve false termina la ejecución
		 * de la acción
		 */
		if(method_exists($this, "beforeDelete")){
			if($this->beforeDelete()===false){
				if(!Router::getRouted()){
					return $this->routeTo(array('action' => 'index'));
				}
			}
			if(Router::getRouted()){
				return null;
			}
		} else {
			if(isset($this->beforeDelete)){
				if($this->{$this->beforeDelete}()===false){
					if(!Router::getRouted()){
						return $this->routeTo(array('action' => 'index'));
					}
				}
				if(Router::getRouted()){
					return null;
				}
			}
		}


		/**
		 * Utilizamos el modelo ActiveRecord para eliminar el registro
		 */
		if($this->{$modelName}->delete()){
			if($this->successDeleteMessage!=''){
				Flash::success($this->successDeleteMessage);
			} else {
				Flash::success("Se eliminó correctamente el registro");
			}
		} else {
			if($this->failureDeleteMessage!=''){
				Flash::error($this->failureDeleteMessage);
			} else {
				Flash::error("Hubo un error al eliminar el registro");
			}
		}
		foreach($this->{$modelName}->getAttributesNames() as $fieldName){
			$_REQUEST["fl_$fieldName"] = $this->{$modelName}->readAttribute($fieldName);
		}

		/**
		 * Busca si existe un método o un llamado variable al método
		 * after_delete
		 */
		if(method_exists($this, "afterDelete")){
			$this->afterDelete();
			if(Router::getRouted()){
				return;
			}
		} else {
			if(isset($this->afterDelete)){
				$this->{$this->afterDelete}();
				if(Router::getRouted()){
					return;
				}
			}
		}

		// Muestra el Formulario en la accion index
		return $this->routeTo(array('action' => 'index'));
	}

	/**
	 * Emula la acción Query llamando a show
	 */
	public function queryAction(){

		$this->view = 'index';

		Generator::scaffold($this->form, $this->scaffold);

		$modelName = EntityManager::getEntityName($this->getSource());
		if(!EntityManager::isEntity($modelName)){
			throw new StandardFormException('No hay un modelo "'.$modelName.'" para hacer la operación de consulta');
			return $this->routeTo(array('action' => 'index'));
		}

		if(isset($this->form['dataFilter'])) {
			if($this->form['dataFilter']){
				$dataFilter = $form['dataFilter'];
			} else {
				$dataFilter = "1=1";
			}
		} else {
			$dataFilter = "1=1";
		}

		if(!isset($this->form['joinTables'])) {
			$this->form['joinTables'] = "";
			$tables = "";
		} else {
			if($this->form['joinTables']) {
				$tables = ",".$this->form['joinTables'];
			} else {
				$tables = "";
			}
		}
		if(isset($this->form['joinConditions'])){
			$joinConditions = " AND ".$this->form['joinConditions'];
		} else {
			$joinConditions = '';
		}

		$modelName = EntityManager::getEntityName($this->getSource());
		$model = $this->{$modelName};

		if($model->isDumped()==false){
			$model->dumpModel();
		}

		$primaryKeys = $model->getPrimaryKeyAttributes();
		$query =  "SELECT ".join(',', $primaryKeys)." FROM ".$this->form['source']."$tables WHERE $dataFilter $joinConditions ";
		$source = $this->form['source'];

		$form = $this->form;
		$config = CoreConfig::readEnviroment();
		foreach($this->{$modelName}->getAttributesNames() as $fkey){
			if(!isset($_REQUEST["fl_".$fkey])){
				$_REQUEST["fl_".$fkey] = "";
			}
			if(trim($_REQUEST["fl_".$fkey])&&$_REQUEST["fl_".$fkey]!='@'){
				if(!isset($form['components'][$fkey]['valueType'])){
					$form['components'][$fkey]['valueType'] = "";
				}
				if($form['components'][$fkey]['valueType']=='numeric'||$form['components'][$fkey]['valueType']=='date'){
					if($config->database->type!='oracle'){
						$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
					} else {
						if($form['components'][$fkey]['valueType']=='date'){
							$query.=" and $source.$fkey = TO_DATE('".$_REQUEST["fl_".$fkey]."', 'YYYY-MM-DD')";
						} else {
							$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
						}
					}
				} else {
					if($form['components'][$fkey]['type']=='hidden'){
						$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
					} else {
						if($form['components'][$fkey]['type']=='check'){
							if($_REQUEST["fl_".$fkey]==$form['components'][$fkey]['checkedValue']){
								$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
							}
						} else {
							if($form['components'][$fkey]['type']=='time'){
								if($_REQUEST["fl_".$fkey]!='00:00'){
									$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
								}
							} else {
								if(isset($form['components'][$fkey]['primary'])&&$form['components'][$fkey]['primary']){
									$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
								} else {
									$query.=" and $source.$fkey LIKE '%".$_REQUEST["fl_".$fkey]."%'";
								}
							}
						}
					}
				}
			}
		}

		$this->query = $query;

		$_REQUEST['queryStatus'] = true;
		$_REQUEST['id'] = 0;

		$this->fetchAction(0);

	}

	/**
	 * Emula la acción Fetch llamando a show
	 *
	 * @access	public
	 * @param	integer $id
	 */
	public function fetchAction($id=0){

		$this->view = 'index';
		$db = DbPool::getConnection();
		if(!$this->query){
			return $this->routeTo(array('action' => 'index'));
		}

		if($id!=='last'){
			$id = $this->filter($id, "int");
			if($id==0){
				$id = 0;
			}
		}

		$this->_lastFetch = $id;

		$db->setFetchMode(DbBase::DB_ASSOC);
		$rows = $db->fetchAll($this->query);
		if(!isset($id)) {
			$id = 0;
		} else {
			$num = $id;
		}

		//Hubo resultados en el select?
		if(!count($rows)){
			Flash::notice("No se encontraron resultados en la búsqueda");
			foreach($this->form['components'] as $fkey => $rrow){
				unset($_REQUEST["fl_".$fkey]);
			}
			unset($_REQUEST['queryStatus']);
			return $this->routeTo(array('action' => 'index'));
		}

		if($id>=count($rows)){
			$num = count($rows)-1;
		}
		if($num<0){
			$num = 0;
		}

		if($id==='last'){
			$num = count($rows)-1;
		}

		$_REQUEST['id'] = $num;

		/**
		 * Busca si existe un método o un llamado variable al método
		 * beforeFetch, si este método devuelve false termina la ejecución
		 * de la acción
		 */
		if(method_exists($this, 'beforeFetch')){
			if($this->beforeFetch()===false){
				return null;
			}
			if(Router::getRouted()){
				return null;
			}
		} else {
			if(isset($this->beforeFetch)){
				if($this->{$this->beforeFetch}()===false){
					return null;
				}
				if(Router::getRouted()){
					return null;
				}
			}
		}

		Flash::notice("Visualizando ".($num+1)." de ".count($rows)." registros");

		$modelName = EntityManager::getEntityName($this->getSource());
		$model = $this->{$modelName};

		//especifica el registro que quiero mostrar
		$row = $rows[$num];
		$conditions = array();
		foreach($row as $key => $value){
			$conditions[] = $key.' = \''.$value.'\'';
		}

		$record = $model->findFirst(join(' AND ', $conditions));
		foreach($record->getAttributes() as $attribute){
			$_REQUEST['fl_'.$attribute] = $record->readAttribute($attribute);
		}
		$_REQUEST['id'] = $num;

		/**
		 * Busca si existe un método o un llamado variable al métodp afterDelete
		 */
		if(method_exists($this, 'afterFetch')){
			$this->afterFetch();
			if(Router::getRouted()){
				return;
			}
		} else {
			if(isset($this->afterFetch)){
				$this->{$this->afterFetch}();
			}
			if(Router::getRouted()){
				return;
			}
		}

		return $this->routeTo(array('action' => 'index'));

	}

	/**
	 * Cambia la vista de browse a la vista index
	 *
	 * @access public
	 * @return boolean
	 */
	public function backAction(){
		$this->view = 'index';
		$this->keep_action = "";
		return $this->routeTo(array('action' => 'index'));
	}

	/**
	 * Emula la acción Browse llamando a show
	 *
	 * @access public
	 */
	public function browseAction(){
		$this->view = 'browse';
		$this->keep_action = "";
		return $this->routeTo(array('action' => 'index'));
	}

	/**
	 * Es el metodo principal de StandarForm y es llamado implicitamente
	 * para mostrar el formulario y su accion asociada.
	 * La propiedad $this->getSource() indica la tabla con la que se va a generar
	 * el formulario
	 *
	 * @access public
	 */
	public function indexAction(){

		if($this->scaffold==true){
			$this->form["source"] = $this->getSource();
			foreach($this->ignoreList as $ignore){
				$this->form['components'][$ignore]['ignore'] = true;
			}
			Generator::buildForm($this->form, true);
		} else {
			if(count($this->form)){
				$this->form["source"] = $this->getSource();
				foreach($this->ignoreList as $ignore){
					$this->form['components'][$ignore]['ignore'] = true;
				}
				Generator::buildForm($this->form);
			} else {
				throw new StandardFormException('Debe especificar las propiedades del formulario a crear en $this->form ó coloque public $scaffold = true para generar dinámicamente el formulario.');
				$this->resetRorm();
			}
		}
	}

	/**
	 * Elimina los meta-datos del formulario
	 *
	 * @access public
	 */
	protected function resetForm(){
		$appController = $_REQUEST['controller']."Controller";
		$instanceName = Core::getInstanceName();
		unset($_SESSION['KUMBIA_CONTROLLERS'][$instanceName][$appController]);
	}

	/**
	 * Guarda un nuevo valor para una relacion detalle del
	 * controlador actual
	 *
	 * @access public
	 */
	public function _save_helperAction(){

		$this->set_response('view');
		$db = DbPool::getConnection();
		Generator::scaffold($this->form, $this->scaffold);

		$field = $this->form['components'][$this->request('name')];
		ActiveRecord::sql_item_sanizite($field['foreignTable']);
		ActiveRecord::sql_item_sanizite($field['detailField']);
		$db->query("insert into {$field['foreignTable']} ({$field['detailField']})
		values ('{$this->request('valor')}')");
	}

	/**
	 * Devuelve los valores actualizados de
	 *
	 * @access public
	 */
	public function _get_detailAction(){

		$this->set_response('xml');
		$db = DbPool::getConnection();
		Generator::scaffold($this->form, $this->scaffold);

		$name = $this->request('name');
		$com = $this->form['components'][$this->request('name')];

		if($com['extraTables']){
			ActiveRecord::sql_item_sanizite($com['extraTables']);
			$com['extraTables']=','.$com['extraTables'];
		}

		ActiveRecordUtils::sqlSanizite($com['orderBy']);

		if(!$com['orderBy']){
			$ordb = $name;
		} else {
			$ordb = $com['orderBy'];
		}
		if($com['whereCondition']){
			$where = 'where '.$com['whereCondition'];
		} else {
			$where = '';
		}

		ActiveRecord::sql_item_sanizite($name);
		ActiveRecord::sql_item_sanizite($com['detailField']);
		ActiveRecord::sql_item_sanizite($com['foreignTable']);

		if($com['column_relation']){
			$com['column_relation'] = str_replace(";", "", $com['column_relation']);
			$query = "select ".$com['foreignTable'].".".$com['column_relation']." as $name,
					".$com['detailField']." from
					".$com['foreignTable'].$com['extraTables']." $where order by $ordb";
			$db->query($query);
		} else {
			$query = "select ".$com['foreignTable'].".$name,
					  ".$com['detailField']." from ".$com['foreignTable'].$com['extraTables']." $where order by $ordb";
			$db->query($query);
		}
		$xml = new simpleXMLResponse();
		while($row = $db->fetchArray()){
			if($this->request('valor')==$row[1]){
				$xml->add_node(array("value" => $row[0], "text" => $row[1], "selected" => "1"));
			} else {
				$xml->add_node(array("value" => $row[0], "text" => $row[1], "selected" => "0"));
			}
		}
		$xml->out_response();
	}

	/**
	 * Metodo de ayuda para el componente helpText
	 *
	 */
	public function __autocompleteAction(){

	}

	/**
	 * Metodo de ayuda para el componente helpText
	 *
	 * @access public
	 */
	public function __check_value_inAction(){
		$this->set_response('xml');
		$db = DbPool::getConnection();
		$_REQUEST['condition'] = str_replace(";", "", urldecode($_REQUEST['condition']));
		ActiveRecord::sql_item_sanizite($_REQUEST['ftable']);
		ActiveRecord::sql_item_sanizite($_REQUEST['dfield']);
		ActiveRecord::sql_item_sanizite($_REQUEST['name']);
		ActiveRecord::sql_item_sanizite($_REQUEST['crelation']);
		$_REQUEST['ftable'] = str_replace(";", "", $_REQUEST['ftable']);
		$_REQUEST['dfield'] = str_replace(";", "", $_REQUEST['dfield']);
		$_REQUEST['name'] = str_replace(";", "", $_REQUEST['name']);
		if($_REQUEST["crelation"]){
			$db->query("select ".$_REQUEST["dfield"]." from ".$_REQUEST['ftable']. " where ".$_REQUEST['crelation']." = '".$_REQUEST['value']."'");
		} else {
			$db->query("select ".$_REQUEST["dfield"]." from ".$_REQUEST['ftable']. " where ".$_REQUEST['name']." = '".$_REQUEST['value']."'");
		}
		echo "<?xml version='1.0' encoding='iso8859-1'?>\r\n<response>\r\n";
		$row = $db->fetchArray();
		echo "\t<row num='", $db->numRows(), "' detail='", htmlspecialchars($row[0]), "'/>\r\n";
		$db->close();
		echo "</response>";
	}

	/**
	 * Indica que un campo tendró un helper de ayuda
	 *
	 * @access	public
	 * @param	string $field
	 * @param	string $helper
	 */
	protected function useHelper($field, $helper=''){
		if(!$helper){
			$helper = $field;
		}
		$this->form['components'][$field."_id"]['use_helper'] = $helper;
	}

	/**
	 * Establece el Titulo del Formulario
	 *
	 * @param string $caption
	 */
	protected function setFormCaption($caption){
		$this->form['caption'] = $caption;
	}

	/**
	 * Indica que un campo seró de tipo imagen
	 *
	 * @access public
	 * @param string $what
	 */
	protected function setTypeImage($what){
		$this->form['components'][$what]['type'] = 'image';
	}

	/**
	 * Indica que un campo seró de tipo numerico
	 *
	 * @access public
	 * @param string $what
	 */
	protected function setTypeNumeric($what){
		$this->form['components'][$what]['type'] = 'text';
		$this->form['components'][$what]['valueType'] = 'numeric';
	}

	/**
	 * Indica que un campo seró de tipo Time
	 *
	 * @access public
	 * @param string $what
	 */
	protected function setTypeTime($what){
		$this->form['components'][$what]['type'] = 'time';
	}

	/**
	 * Indica que un campo seró de tipo fecha
	 *
	 * @access public
	 * @param string $what
	 */
	protected function setTypeDate($what){
		$this->form['components'][$what]['type'] = 'text';
		$this->form['components'][$what]['valueType'] = 'date';
	}

	/**
	 * Indica que un campo seró de tipo password
	 *
	 * @access public
	 * @param string $what
	 */
	protected function setTypePassword($what){
		$this->form['components'][$what]['type'] = 'password';
	}

	/**
	 * Indica que un campo seró de tipo textarea
	 *
	 * @access public
	 * @param string $what
	 */
	protected function setTypeTextarea($what){
		$this->form['components'][$what]['type'] = 'textarea';
	}

	/**
	 * Indica una lista de campos recibirón entrada solo en mayósculas
	 *
	 */
	protected function setTextUpper(){
		if(func_num_args()){
			foreach(func_get_args() as $what){
				$this->form['components'][$what]['type'] = 'text';
				$this->form['components'][$what]['valueType'] = 'textUpper';
			}
		}
	}

	/**
	 * Crea un combo estótico
	 *
	 * @param string $what
	 * @param string $arr
	 */
	protected function setComboStatic($what, $arr){
		$this->form['components'][$what]['type'] = 'combo';
		$this->form['components'][$what]['class'] = 'static';
		$this->form['components'][$what]['items'] = $arr;
	}

	/**
	 * Crea un combo Dinamico
	 *
	 * @access public
	 * @param string $what
	 */
	protected function setComboDynamic($what){
		$numberArguments = func_num_args();
		$opt = Utils::getParams(func_get_args(), $numberArguments);
		$opt['field'] = $opt['field'] ? $opt['field'] : $opt[0];
		$opt['relation'] = $opt['relation'] ? $opt['relation'] : $opt[1];
		$opt['detail_field'] = $opt['detail_field'] ? $opt['detail_field'] : $opt[2];
		$this->form['components'][$opt['field']]['type'] = 'combo';
		$this->form['components'][$opt['field']]['class'] = 'dynamic';
		$this->form['components'][$opt['field']]['foreignTable'] = $opt['relation'];
		$this->form['components'][$opt['field']]['detailField'] = $opt['detail_field'];
		if(isset($opt['conditions'])&&$opt['conditions']){
			$this->form['components'][$opt['field']]['whereCondition'] = $opt['conditions'];
		}
		if($opt['column_relation']){
			$this->form['components'][$opt['field']]['column_relation'] = $opt['column_relation'];
		}
		if($opt['column_relation']){
			$this->form['components'][$opt['field']]['column_relation'] = $opt['column_relation'];
		} else {
			$this->form['components'][$opt['field']]['column_relation'] = $opt['id'];
		}
		if(isset($opt['force_charset'])){
			$this->form['components'][$opt['field']]['force_charset'] = $opt['force_charset'];
		}
	}

	/**
	 * Crea un Texto de Ayuda de Contexto
	 *
	 * @access public
	 * @param string $what
	 */
	protected function setHelpContext($what){
		$numberArguments = func_num_args();
		$opt = Utils::getParams(func_get_args(), $numberArguments);
		$field = $opt['field'];
		$this->form['components'][$field]['type'] = 'helpContext';
	}

	/**
	 * Especifica que un campo es de tipo E-Mail
	 *
	 * @access public
	 * @param array $fields
	 */
	protected function setTypeEmail($fields){
		if(func_num_args()){
			foreach(func_get_args() as $field){
				$this->form['components'][$field]['type'] = 'text';
				$this->form['components'][$field]['valueType'] = "email";
			}
		}
	}

	/**
	 * Recibe una lista de campos que no van a ser incluidos en
	 * la generación del formulario
	 *
	 * @access protected
	 */
	protected function ignore(){
		if(func_num_args()){
			foreach(func_get_args() as $what){
				$this->form['components'][$what]['ignore'] = true;
				if(!in_array($what, $this->ignoreList)){
					$this->ignoreList[] = $what;
				}
			}
		}
	}

	/**
	 * Permite cambiar el tamaóo (size) de un campo $what a $size
	 *
	 * @access protected
	 * @param string $what
	 * @param integer $size
	 */
	protected function setSize($what, $size){
		$this->form['components'][$what]['attributes']['size'] = $size;
	}

	/**
	 * Permite cambiar el tamaóo móximo de caracteres que se puedan
	 * digitar en un campo texto
	 *
	 * @access protected
	 * @param string $what
	 * @param integer $size
	 */
	protected function setMaxlength($what, $size){
		$this->form['components'][$what]['attributes']['maxlength'] = $size;
	}

	/**
	 * Hace que un campo aparezca en la pantalla de visualización
	 *
	 * @access protected
	 */
	protected function notBrowse(){
		if(func_num_args()){
			foreach(func_get_args() as $what){
				$this->form['components'][$what]['notBrowse'] = true;
			}
		}
	}

	/**
	 * Hace que un campo no aparezca en el reporte PDF
	 *
	 * @access protected
	 * @param string $what
	 */
	protected function notReport($what){
		if(func_num_args()){
			foreach(func_get_args() as $what){
				$this->form['components'][$what]['notReport'] = true;
			}
		}
	}

	/**
	 * Cambia la imagen del Formulario. $im es una imagen en img/
	 *
	 * @access protected
	 * @param string $im
	 */
	protected function setTitleImage($im){
		$this->form['titleImage'] = $im;
	}

	/**
	 * Cambia el numero de campos que aparezcan por fila
	 * cuando se genere el formulario
	 *
	 * @access protected
	 * @param integer $number
	 */
	protected function fieldsPerRow($number){
		$this->form['fieldsPerRow'] = $number;
	}

	/**
	 * Inhabilita el formulario para insertar
	 *
	 * @access protected
	 */
	protected function unableInsert(){
		$this->form['unableInsert'] = true;
	}

	/**
	 * Inhabilita el formulario para borrar
	 *
	 * @access protected
	 */
	protected function unableDelete(){
		$this->form['unableDelete'] = true;
	}

	/**
	 * Inhabilita el formulario para actualizar
	 *
	 * @access protected
	 */
	protected function unableUpdate(){
		$this->form['unableUpdate'] = true;
	}

	/**
	 * Inhabilita el formulario para consultar
	 *
	 * @access protected
	 */
	protected function unableQuery(){
		$this->form['unableQuery'] = true;
	}

	/**
	 * Inhabilita el formulario para visualizar
	 *
	 * @access protected
	 */
	protected function unableBrowse(){
		$this->form['unableBrowse'] = true;
	}

	/**
	 * Inhabilita el formulario para generar reporte
	 *
	 * @access protected
	 */
	protected function unableReport(){
		$this->form['unableReport'] = true;
	}

	/**
	 * Indica que un campo seró de tipo Hidden
	 *
	 * @access protected
	 * @param array $fields
	 */
	protected function setHidden($fields){
		if(func_num_args()){
			foreach(func_get_args() as $field){
				$this->form['components'][$field]['type'] = 'hidden';
			}
		}
	}

	/**
	 * Cambia el Texto Caption de un campo en especial
	 *
	 * @access protected
	 * @param string $fieldName
	 * @param string $caption
	 */
	protected function setCaption($fieldName, $caption){
		$this->form['components'][$fieldName]['caption'] = $caption;
	}

	/**
	 * Hace que un campo sea de solo lectura
	 *
	 * @access protected
	 * @param string $fields
	 */
	protected function setQueryOnly($fields){
		if(func_num_args()){
			foreach(func_get_args() as $field){
				$this->form['components'][$field]['queryOnly'] = true;
			}
		}
	}

	/**
	 * Cambia el texto de los botones para los formularios
	 * estandar setActionCaption('insert', 'Agregar')
	 *
	 * @access protected
	 * @param string $action
	 * @param string $caption
	 */
	protected function setActionCaption($action, $caption){
		$this->form['buttons'][$action] = $caption;
	}

	/**
	 * Asigna un atributo a un campo del formulario
	 * setAttribute('campo', 'rows', 'valor')
	 *
	 * @access protected
	 * @param string $field
	 * @param string $name
	 * @param mixed $value
	 */
	protected function setAttribute($field, $name, $value){
		$this->form['components'][$field]['attributes'][$name] = $value;
	}


	/**
	 * Asigna un atributo a un campo del formulario
	 * setAttribute('campo', 'rows', 'valor')
	 *
	 * @access protected
	 * @param string $field
	 * @param string $event
	 * @param string $value
	 */
	protected function setEvent($field, $event, $value){
		$this->form['components'][$field]['attributes']["on".$event] = $value;
	}

	/**
	 * Ejecuta el inicializador para tomar los cambios sin reiniciar el navegador
	 *
	 * @access public
	 */
	public function __wakeup(){
		if(method_exists($this, "initialize")){
			$this->initialize();
		}
		$this->setPersistance(true);
		parent::__wakeup();
	}

	/**
	 * Trata de recuperarse de errores ocurridos en la base de datos
	 *
	 * @access public
	 * @param Exception $e
	 */
	public function onException($e){
		if(is_subclass_of($e, 'DbException')){
			if($e instanceof DbConstraintViolationException){
				Flash::error('No se puede efectuar la operación ya que el registro se esta usando en otras partes del sistema');
				$action = Router::getAction();
				$this->RouteTo('action: index');
			} else {
				throw $e;
			}
		} else {
			throw $e;
		}
	}

}
