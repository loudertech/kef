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
 * @package		Generator
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 */

/**
 * Generator
 *
 * Obtiene los metadatos y construye el esquema para crear un formulario StandarForm
 *
 * @category	Kumbia
 * @package 	Generator
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright 	Copyright (c) 2005-2009 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license 	New BSD License
 * @abstract
 */
abstract class Generator {

	/**
	 * Salida string al formulario
	 *
	 * @var string
	 */
	static public $outForm = "";

	/**
	 * Obtiene el indice de un campo en la lista de campos
	 *
	 * @param string $field
	 * @param array $form
	 * @return integer
	 */
	public static function getIndex($field, $form){
		$n = 0;
		foreach($form['components'] as $name => $comp){
			if($name==$field) {
				return $n;
			}
			++$n;
		}
		return 0;
	}

	/**
	 * Obtiene el tipo de explorador usado por el cliente
	 * de la aplicación
	 *
	 * @return string
	 */
	public static function getBrowser(){
		if(strpos($_SERVER['HTTP_USER_AGENT'], "Firefox")){
			return "firefox";
		} else {
			return "msie";
		}
	}

	/**
	 * Genera una salida que es cacheada para luego hacer que
	 * salga toda junta
	 *
	 * @param mixed $val
	 */
	static function formsPrint($val){
		self::$outForm.=$val;
	}

	/**
	 * Imprime la salida que estaba cacheada utilizando self::formsPrint
	 *
	 */
	static function buildFormOut(){
		echo self::$outForm;
		self::$outForm = "";
	}

	/**
	 * Vuelca la información de la tabla para construir el array
	 * interno que luego sirve para construir el formulario
	 *
	 * @access 	public
	 * @param	array $form
	 * @return	boolean
	 * @static
	 */
	static public function dumpFieldInformation(&$form){

		$controllerName = Router::getController();
		$form['force'] = true;
		$instanceName = Core::getInstanceName();
		$activeApp = Router::getApplication();
		$config = CoreConfig::readAppConfig();
		$db = DbPool::getConnection();
		if($form['source']==''){
			throw new StandardFormException("No se pudo determinar la entidad para generar el formulario");
		}
		$fields = $db->describeTable($form['source']);
		if(!$fields) {
			throw new StandardFormException("No existe la tabla {$form['source']} en la base de datos {$config->database->name}");
			return false;
		}
		$cp = $form;
		$form = array();
		$n = 0;
		if(!isset($form['components'])) {
			$form['components'] = array();
		}
		foreach($fields as $field){
			Utils::arrayInsert($form['components'], $n, array(), $field['Field']);
			if($field['Type']=='date'){
				if(!isset($form['components'][$field['Field']]['valueType'])){
					$form['components'][$field['Field']]['valueType'] = "date";
				}
			}
			if($field['Field']=='id'){
				$form['components'][$field['Field']]['auto_numeric'] = true;
				if($cp['type']=='grid'){
					$form['components'][$field['Field']]['type'] = "auto";
				}
			}
			if($field['Field']=='email'){
				if(!isset($form['components'][$field['Field']]['valueType'])){
					$form['components'][$field['Field']]['valueType'] = "email";
				}
			}
			if($field['Key']=='PRI'){
				if(!isset($form['components'][$field['Field']]['primary'])){
					$form['components'][$field['Field']]['primary'] = true;
				}
			}
			if($field['Null']=='NO'){
				if(!isset($form['components'][$field['Field']]['notNull'])){
					$form['components'][$field['Field']]['notNull'] = true;
				}
			}
			if(strpos(" ".$field['Type'], "int")||strpos(" ".$field['Type'], "decimal")){
				if(!isset($form['components'][$field['Field']]['valueType'])){
					$form['components'][$field['Field']]['valueType'] = "numeric";
				}
			}
			if($field['Type']=='text'){
				$form['components'][$field['Field']]['type'] = 'textarea';
			}
			if($field['Field']=='email'){
				if(!isset($form['components'][$field['Field']]['valueType'])){
					$form['components'][$field['Field']]['valueType'] = "email";
				}
			}
			$detail = '';
			if(preg_match('/[a-z_0-9A-Z]+_id$/', $field['Field'])){
				$table = substr($field['Field'], 0, strpos($field['Field'], '_id'));
				$dq = $db->describeTable($table);
				if($dq){
					$y = 0;
					$p = 0;
					foreach($dq as $rowq){
						if($rowq['Field']=='id'){
							$p = 1;
						}
						if(
						($rowq['Field']=='detalle')||
						($rowq['Field']=='nombre')||
						($rowq['Field']=='descripcion')||
						($rowq['Field']=='name')
						){
							$detail = $rowq['Field'];
						}
					}
					if($p&&$detail&&!isset($form['components'][$field['Field']]['type'])){
						$form['components'][$field['Field']]['type'] = 'combo';
						$form['components'][$field['Field']]['class'] = 'dynamic';
						$form['components'][$field['Field']]['foreignTable'] = $table;
						if(!isset($form['components'][$field['Field']]['detailField'])){
							$form['components'][$field['Field']]['detailField'] = $detail;
						}
						$form['components'][$field['Field']]['orderBy'] = "2";
						$form['components'][$field['Field']]['column_relation'] = "id";
						$form['components'][$field['Field']]['caption'] =
						ucwords(str_replace("_", " ", str_replace("_id", "", $field['Field'])));
					}
				}
			} else {
				if($x = strpos(" ".$field['Type'], "(")){
					$l = substr($field['Type'], $x);
					$l = substr($l, 0, strpos($l, ")"));
					if(!isset($form['components'][$field['Field']]['attributes']['size'])){
						$form['components'][$field['Field']]['attributes']['size'] = (int) $l;
					}
					if(!isset($form['components'][$field['Field']]['attributes']['maxlength'])){
						$form['components'][$field['Field']]['attributes']['maxlength'] = (int) $l;
					}
				}
			}
			if(!isset($form['components'][$field['Field']]['type'])){
				$form['components'][$field['Field']]['type'] = "text";
			}
			++$n;
		}

		if(!count($cp['components'])) {
			unset($cp['components']);
		}

		$form = Utils::arrayMergeOverwrite($form, $cp);
		foreach($form['components'] as $key => $value){
			if(isset($value['ignore'])) {
				if($value['ignore']){
					unset($form['components'][$key]);
				}
			}
		}
		/*$_SESSION['KSF'][$instanceName][$activeApp][$form['source'].$form['type']] = array(
			'time' => Core::getProximityTime(),
			'data' => serialize($form),
			'status' => 'N'
		);*/
		return true;
	}

	/**
	 * Genera información importante para la construcción del formulario
	 *
	 * @param mixed $form
	 * @param boolean $scaffold
	 * @return boolean
	 */
	static function scaffold(&$form, $scaffold = false){

		if(!is_array($form)){
			$form = array();
		}

		$controller = Dispatcher::getController();
		$controllerName = Router::getController();

		if(isset($form['source'])) {
			if(!$form['source']) {
				$controller->source = $controllerName;
				$form['source'] = $controllerName;
			}
		} else {
			if(isset($controller->source)){
				if($controller->source){
					$form['source'] = $controller->source;
				} else {
					$controller->source = $controllerName;
					$form['source'] = $controllerName;
				}
			} else {
				$controller->source = $controllerName;
				$form['source'] = $controllerName;
			}
		}
		if(isset($form['caption'])) {
			if(!$form['caption']) {
				$form['caption'] = ucwords(str_replace('_', ' ', $controllerName));
			}
		} else {
			$form['caption'] = ucwords(str_replace('_', ' ', $controllerName));
		}

		if(isset($form['type'])) {
			if(!$form['type']) {
				$form['type'] = 'standard';
			}
		} else {
			$form['type'] = 'standard';
		}

		//Dump Data Field Information if no components are loaded
		if(!isset($form['components']))	{
			$form['components'] = null;
		}
		if(!isset($form['scaffold'])) {
			$form['scaffold'] = false;
		}
		if((!$form['components'])||$form['scaffold']||$scaffold){
			if(!self::dumpFieldInformation($form)){
				return false;
			}
			if($form['type']=='master-detail'){
				self::dumpFieldInformation($form['detail']);
				$form['detail']['dataFilter'] = "{$form['detail']['source']}.{$form['source']}_id = '@id'";
				foreach($form["detail"]['components'] as $k => $f){
					if($k=='id'){
						$form["detail"]['components'][$k]['type'] = "auto";
						$form["detail"]['components'][$k]['caption'] = "";
						$f['caption'] = "";
						$f['type'] = "auto";
					}
					if($k==$form['source']."_id"){
						$form["detail"]['components'][$k]['type'] = "hidden";
						$form["detail"]['components'][$k]['caption'] = "";
						$form["detail"]['components'][$k]['attributes']['value'] = $_POST["fl_id"];
						$f['caption'] = "";
						$f['type'] = "hidden";
					}
					if(!isset($f["caption"])) {
						if($f['type']!='auto'&&$f['type']!='hidden'){
							$form["detail"]['components'][$k]['caption'] = ucwords(str_replace("_", " ", $k));
						}
					}
				}
			}
		}

		if(!$form['components']){
			throw new StandardFormException("No se pudo cargar la información de la relación '{$form['source']}'</span><br>Verifique que la entidad exista en la base de datos actual ó que los par&aacute;metros se&aacute;n correctos");
			return;
		}

		//Creating Captions
		foreach($form['components'] as $k => $f){
			if(!isset($f["caption"])) {
				if($f['type']!='auto'&&$f['type']!='hidden'){
					$form['components'][$k]['caption'] = ucwords(str_replace("_", " ", $k));
				}
			}
		}

	}

	/**
	 * BuildForm is the main function that builds all the forms
	 *
	 * @param array $form
	 * @param boolean $scaffold
	 * @return boolean
	 */
	static function buildForm($form, $scaffold=false){

		if(!class_exists('Component', false)){
			require KEF_ABS_PATH.'Library/Kumbia/Generator/Components.php';
		}

		$controllerName = Router::getController();
		$action_name = Router::getAction();

		//self::$outForm = "";

		Generator::scaffold($form, $scaffold);

		if(!$form['components']){
			return false;
		}

		//Loading The JavaScript Functions
		self::formsPrint("<script type='text/javascript' src='".Core::getInstancePath()."javascript/core/standardform/load.js'></script>\r\n");

		if($form['type']=='standard'){
			self::formsPrint("<script type='text/javascript' src='".Core::getInstancePath()."javascript/core/standardform/load.standard.js'></script>\r\n");
		}
		self::formsPrint("<script  type='text/javascript' src='".Core::getInstancePath()."javascript/core/calendar.js'></script>\r\n");

		if(Core::fileExists("public/javascript/".$controllerName.".js")){
			self::formsPrint("<script type='text/javascript' src='".Core::getInstancePath()."javascript/{$_REQUEST["controller"]}.js'></script>\r\n");
		}

		if(Core::fileExists("public/css/$controllerName.css")){
			self::formsPrint("<link rel='stylesheet' href='".Core::getInstancePath()."css/$controllerName.css' type='text/css'/>\n");
		}

		self::formsPrint("<div class='$controllerName'>
		<form method='post' name='fl' action='' onsubmit='return false'>");
		if(!isset($form["notShowTitle"])){
			if(isset($form['titleImage'])){
				if(isset($form['titleHelp'])){
					self::formsPrint("<table class='titleHeader'><tr><td><img src='".Core::getInstancePath()."img/{$form['titleImage']}' border=0></td>
				<td><h1 class='".$form['titleStyle']."' title='{$form['titleHelp']}'
				style='cursor:help'>&nbsp;<u>".$form["caption"]."</u></h1>
				</td></tr></table>\r\n");
				} else {
					if(!isset($form['titleStyle'])){
						$form['titleStyle'] = "";
					}
					self::formsPrint("<table class='titleHeader'><tr><td><img src='".Core::getInstancePath()."img/{$form['titleImage']}' border=0></td>
					<td><h1 class='".$form['titleStyle']."'>&nbsp;".$form["caption"]."</h1>
					</td></tr></table>\r\n");
				}
			} else {
				if(!isset($form['titleStyle'])) {
					self::formsPrint("<h1>&nbsp;".$form["caption"]."</h1>\r\n");
				} else {
					self::formsPrint("<h1 class='".$form['titleStyle']."'>&nbsp;".$form["caption"]."</h1>\r\n");
				}
			}
		}
		self::formsPrint("<input type='hidden' name='aaction' value='".$controllerName."' />\r\n");
		self::formsPrint("<input type='hidden' id='kb_path' name='kb_path' value='".Core::getInstancePath()."' />\r\n");
		if(isset($_REQUEST['value'])){
			self::formsPrint("<input type='hidden' name='vvalue' name='vvalue' value='".$_REQUEST['value']."' />\r\n");
		}
		self::formsPrint("<input type='hidden' id='errStatus' name='errStatus' value='0' />\r\n");
		self::formsPrint("<input type='hidden' id='winHelper' name='winHelper' value='0' />\r\n");
		if($action_name=='validation'){
			self::formsPrint("<input type='hidden' id='validation' name='validation' value='1' />\r\n");
		} else {
			self::formsPrint("<input type='hidden' id='validation' name='validation' value='0' />\r\n");
		}

		//Standard Forms
		if($form['type']=='standard'){
			if(!class_exists('StandardGenerator', false)){
				require KEF_ABS_PATH.'Library/Kumbia/Generator/StandardBuild.php';
			}
			StandardGenerator::buildFormStandard($form);
		}

		self::formsPrint("</div>");

		self::buildFormOut();
	}

	/**
	 * Obtener el siguiente autonumerico
	 *
	 * @param db $db
	 * @param string $table
	 * @param string $field
	 * @return string
	 */
	static function getMaxAuto($db, $table, $field){
		ActiveRecordUtils::sqlItemSanizite($table);
		ActiveRecordUtils::sqlItemSanizite($field);
		$db->query("select max($field)+1 from $table");
		$row = $db->fetchArray();
		if(!$row[0]){
			$row[0] = 1;
		}
		return $row[0];
	}

}
