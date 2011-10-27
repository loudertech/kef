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
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @version 	$Id: Components.php 87 2009-09-19 19:02:50Z gutierrezandresfelipe $
 */

/**
 * Component
 *
 * Crea los componentes utilizados en los formularios Standard y Maestro-Detalle
 *
 * @category	Kumbia
 * @package		Generator
 * @copyright	Copyright (c) 2008-2009 Louder Technology COL. (http://www.loudertechnology.com)
 * @copyright	Copyright (c) 2005-2008 Andres Felipe Gutierrez (gutierrezandresfelipe at gmail.com)
 * @license		New BSD License
 * @abstract
 */
abstract class Component {

	/**
	 * Crea un componente tipo TextArea, estos componentes se crean automaticamente
 	 * cuando un campo es de tipo TEXT o mediante el metodo $this->set_type_textarea
 	 *
 	 * @access public
 	 * @param string $com
  	 * @param string $name
  	 * @static
 	 */
	static public function buildTextArea($com, $name){
		Generator::formsPrint("<label for='flid_$name'>".$com['caption']."</label></td><td>");
		Generator::formsPrint("<textarea name='fl_$name' id='flid_$name' disabled='disabled' ");
		if(!isset($com['attributes']['rows'])||!$com['attributes']['rows']) {
			$com['attributes']['rows'] = 4;
		}
		if(!isset($com['atributes']['cols'])||!$com['attributes']['cols']) {
			$com['attributes']['cols'] = 50;
		}
		if($com['attributes']){
			foreach($com["attributes"] as $nitem => $item){
				Generator::formsPrint(" $nitem='$item' ");
			}
		}
		Generator::formsPrint(">");
		if(isset($_REQUEST['fl_'.$name])){
			Generator::formsPrint($_REQUEST['fl_'.$name]);
		}
		if(!isset($com['extraText'])){
			$com['extraText'] = "";
		}
		Generator::formsPrint("</textarea>&nbsp;<span id='det_$name'>".$com['extraText']."</span>\r\n");
	}


	/**
 	 * Crea un componente tipo Texto, estos componentes son los componentes
 	 * por defecto de los formularios
 	 *
 	 * @param string $com
 	 * @param string $name
 	 * @param array $form
 	 */
	static public function buildTextComponent($com, $name, $form){
		$config = CoreConfig::readAppConfig();
		$dbdate = $config->application->dbdate;
		if(isset($com['not_label'])){
			if(!$com['not_label']){
				Generator::formsPrint("<label for='flid_$name'>".$com['caption']."</label></td><td>");
			}
		} else {
			Generator::formsPrint("<label for='flid_$name'>".$com['caption']."</label></td><td>");
		}
		if(!isset($com['valueType'])){
			$com['valueType'] = "";
		}
		if($com['valueType']!='date'&&$com['valueType']!='email'){
			Generator::formsPrint("<input type='text' name='fl_$name' id='flid_$name' disabled='disabled' ");
			if(isset($_REQUEST['fl_'.$name])){
				Generator::formsPrint("value = '".$_REQUEST['fl_'.$name]."'");
			}
			if(isset($com['attributes'])&&$com['attributes']){
				foreach($com["attributes"] as $nitem => $item){
					Generator::formsPrint(" $nitem='$item' ");
				}
			}

			//Validaciones JavaScript Dominio de Valores >7 ó <15
			$validation = "";

			//Numerico
			if(isset($com['valueType'])){
				if($com['valueType']=="numeric"){
					Generator::formsPrint("onkeydown='valNumeric(event)'");
				} else {
					Generator::formsPrint("onkeydown='nextField(event, \"$name\")'");
				}

				//Texto en Mayusculas
				if($com['valueType']=="textUpper"){
					Generator::formsPrint("onblur='keyUpper2(this)'");
				}

				if($com['valueType']=="onlyText"){
					Generator::formsPrint(" onkeydown='validaText(event)' ");
				}

				if($com['valueType']=="onlyTextUpper"){
					Generator::formsPrint(" onkeydown='validaText(event)' ");
					Generator::formsPrint(" onblur='keyUpper2(this)'");
				}

				///Validar la Fecha
				if($com['valueType']=="date"){
					Generator::formsPrint(" onkeydown='valDate()'");
					Generator::formsPrint(" onblur='checkDate(this)'");
				}
			} else {
				$com['valueType'] = null;
			}

			//Validacion de Formatos
			if(isset($com['format'])){
				Generator::formsPrint(" onkeypress=\"formatNumber(this, '{$com['format']}')\"");
			}
			if(!isset($com['extraText'])) {
				$com['extraText'] = "";
			}
			Generator::formsPrint(" />&nbsp;<span id='det_$name'>".$com['extraText']."</span>\r\n");
		} else {
			if($com['valueType']=="date"){
				$valueDate = "";
				if(isset($_REQUEST['fl_'.$name])){
					$valueDate = "'".$_REQUEST['fl_'.$name]."'";
				} else {
					if(isset($com['value'])){
						$valueDate = "'".$com['value']."'";
					} else {
						$valueDate = "''";
					}
				}
				if($valueDate=="''"){
					$valueDate = "null";
				}
				if($valueDate=="null"){
					Generator::formsPrint("\n<script type='text/javascript'> var vdateId = 'flid_$name';
					DateInput('fl_$name', false, '".$dbdate."')</script>");
				} else {
					Generator::formsPrint("\n<script type='text/javascript'> var vdateId = 'flid_$name';
					DateInput('fl_$name', false, '".$dbdate."', $valueDate)</script>");
				}
				//Generator::formsPrint("<script>DateInput('fl_$name', false, '".$GLOBALS['dbDate']."', $valueDate, 'flid_$name')</script>");
			} else {
				if(isset($_REQUEST["fl_$name"])&&$_REQUEST["fl_$name"]) {
					$p1 = substr($_REQUEST["fl_$name"], 0, strpos($_REQUEST["fl_$name"], "@"));
					$p2 = substr($_REQUEST["fl_$name"], strpos($_REQUEST["fl_$name"], "@")+1);
				} else {
					$_REQUEST["fl_$name"] = "";
					$p1 = "";
					$p2 = "";
				}
				Generator::formsPrint("<input type='hidden' value='{$_REQUEST["fl_$name"]}' name='fl_$name' id='flid_$name' />");
				Generator::formsPrint("<span><input type='text' size='15' disabled='disabled' id='$name"."_email1'
				onblur='saveEmail(\"$name\")' onkeydown='validaEmail(event)' value='$p1'/>
				@<input type='text' size=15 disabled='disabled' id='$name"."_email2'
				onblur='saveEmail(\"$name\")' onkeydown='validaEmail(event)' value='$p2'/></span>");
			}
		}
	}

	/**
	 * Permite definir componentes de usuario
	 *
	 * @param string $com
	 * @param string $name
	 * @param array $form
	 */
	public function buildUserdefinedComponent($com, $name, $form){

	}

	/**
	 * Permite construir un componente de e-mail
	 *
	 */
	public function buildEmailComponent(){

	}

	/**
	 * Construye un HelpContext Componente
	 *
	 * @param array $com
	 * @param string $name
	 * @param array $form
	 */
	public static function buildHelpContext($com, $name, $form){
		Generator::formsPrint("<label for='flid_$name'>".$com['caption']."</label></td><td valign='top'>");
		Generator::formsPrint("<table cellspacing='0'><tr><td><input type='text' name='fl_$name' id='flid_$name' disabled='disabled' size='10' ");
		if(isset($_REQUEST['fl_'.$name])){
			$value = $_REQUEST['fl_'.$name];
			Generator::formsPrint("value = '".$value."'");
		}
		Generator::formsPrint(" /></td><td><input type='text' id='flid_$name"."_det' class='help_context_det' value='' size='45'>
		<div id='".$name."_choices' class='autocomplete'></div></td></tr></table>\r\n");
	}

	/**
 	 * Crea los componentes tipo Combo cuando son creados dinamicamente
 	 * y Estaticamente, en formularios Standard y Master-Detail
 	 *
 	 * @param array $com
 	 * @param string $name
 	 */
	static function buildStandardCombo($com, $name){
		Generator::formsPrint("<label for='flid_$name'>".$com['caption']."</label></td><td><select name='fl_$name' id='flid_$name' disabled='disabled' ");
		if(isset($com["attributes"])){
			if(is_array($com["attributes"])){
				foreach($com["attributes"] as $nitem => $item) {
					if($nitem!='maxlength'&&$nitem!='size'){
						Generator::formsPrint(" $nitem='$item' ");
					}
				}
			}
		}
		$validation = "";
		if(isset($com['dynamicFilter'])){
			if($com['dynamicFilter']){
				$validation.="; getDetailValues(\"".$com['dynamicFilter']['field']."\", \"".$com['dynamicFilter']['foreignTable']."\", \"".$com['dynamicFilter']['detailField']."\", \"";
				$com['dynamicFilter']['whereCondition'] = urlencode($com['dynamicFilter']['whereCondition']);
				$com['dynamicFilter']['whereCondition'] = str_replace('%40', '@', $com['dynamicFilter']['whereCondition']);
				if(strpos($com['dynamicFilter']['whereCondition'], '@')){
					if(preg_match('/[\@][A-Za-z0-9]+/', $com['dynamicFilter']['whereCondition'], $regs)){
						foreach($regs as $reg){
							$com['dynamicFilter']['whereCondition'] = str_replace($reg, "\"+document.getElementById(\"flid_".str_replace("@", "", $reg)."\").value+\"", $com['dynamicFilter']['whereCondition']);
						}
					}
				}
				$validation.=$com['dynamicFilter']['whereCondition']."\", \"".$com['dynamicFilter']['relfield']."\")";
			}
		}
		Generator::formsPrint(" onkeydown='nextField(event, \"$name\")' ");
		if($validation){
			Generator::formsPrint(" onchange='$validation'>\r\n");
		} else {
			Generator::formsPrint(">\r\n");
		}
		if(!isset($com['noDefault'])||!$com['noDefault']){
			Generator::formsPrint("<option value='@'>Seleccione ...</option>\n");
		}
		if($com['class']=='dynamic'){
			$db = DbBase::rawConnect();
			if(isset($com['extraTables'])){
				if($com['extraTables']){
					ActiveRecord::sqlSanizite($com["extraTables"]);
					$com['extraTables']=",".$com['extraTables'];
				}
			}
			if(isset($com["detail_field"])){
				ActiveRecordUtils::sqlSanizite($com["detail_field"]);
			}
			if(isset($com['orderBy'])){
				ActiveRecordUtils::sqlSanizite($com["orderBy"]);
				if(!$com["orderBy"]){
					$ordb = $name;
				} else {
					$ordb = $com["orderBy"];
				}
			} else {
				$ordb = $name;
			}
			ActiveRecordUtils::sqlItemSanizite($com["foreignTable"]);
			$where = "";
			if(isset($com['whereCondition'])){
				if($com['whereCondition']) {
					$where = "where ".$com['whereCondition'];
				} else {
					$where = "";
				}
			}
			if($com['column_relation']){
				ActiveRecordUtils::sqlSanizite($com["column_relation"]);
				if(isset($com['extraTables'])){
					$query = "SELECT ".$com['foreignTable'].".".$com['column_relation']." as $name,
						".$com['detailField']." from
						".$com['foreignTable'].$com['extraTables']." $where order by $ordb";
				} else {
					$query = "SELECT ".$com['foreignTable'].".".$com['column_relation']." as $name,
						".$com['detailField']." FROM
						".$com['foreignTable']." $where ORDER BY $ordb";
				}
				$db->query($query);
			} else {
				$query = "SELECT ".$com['foreignTable'].".$name,
					  ".$com['detailField']." from ".$com['foreignTable'].$com['extraTables']." $where order by $ordb";
				$db->query($query);
			}
			$db->setFetchMode(DbBase::DB_NUM);
			while($row = $db->fetchArray()){
				if(!isset($_REQUEST['fl_'.$name])){
					$_REQUEST['fl_'.$name] = '';
				}
				if(isset($com['force_charset'])){
					$row[1] = utf8_encode($row[1]);
				}
				if($_REQUEST['fl_'.$name]===$row[0]){
					Generator::formsPrint("<option value='".$row[0]."' selected='selected'>".$row[1]."</option>\r\n");
				} else {
					Generator::formsPrint("<option value='".$row[0]."'>".$row[1]."</option>\r\n");
				}
			}
		}
		if($com['class']=='static'){
			if(!isset($_REQUEST["fl_".$name])){
				$_REQUEST["fl_".$name] = "";
			}
			foreach($com['items'] as $it){
				if($_REQUEST["fl_".$name]==$it[0]){
					Generator::formsPrint("<option value='".$it[0]."' selected='selected'>".$it[1]."</option>\r\n");
				} else {
					Generator::formsPrint("<option value='".$it[0]."'>".$it[1]."</option>\r\n");
				}
			}
		}
		Generator::formsPrint("</select>\r\n");
		if(!isset($com['use_helper'])){
			$com['use_helper'] = false;
		}
		if($com['use_helper']&&$com['class']=='dynamic'){
			if($com['column_relation'])
			$op = $com['column_relation'];
			else $op = $name;
			Generator::formsPrint("<input type='text' style='display:none' id='{$name}_helper' />
			<a href='#helper_$name' name='#helper_$name' onclick='show_helper(\"$name\")' id='helper_new_{$name}'>Nuevo</a>
			<a href='#helper_$name' name='#helper_$name' style='display:none' onclick='save_helper(\"$name\")' id='helper_save_{$name}'>Guardar</a>
			<a href='#helper_$name' name='#helper_$name' style='display:none;font-size:12px;color:red' onclick='cancel_helper(\"$name\")' id='helper_cancel_{$name}'>Cancelar</a>
			<img src='".Core::getInstancePath()."img/spinner.gif' style='display:none' alt='' id='{$name}_spinner' />");
		}

	}

	/**
 	 * Crea los componentes para campos que son Password
 	 *
 	 * @param array $com
 	 * @param string $name
  	 */
	public static function buildStandardPassword($com, $name){
		Generator::formsPrint("".$com['caption']."</td><td id='tp' valign='top'><input type='password' name='fl_$name' id='flid_$name' disabled='disabled' ");
		if($_REQUEST['fl_'.$name]){
			Generator::formsPrint("value = '".$_REQUEST['fl_'.$name]."'");
		}
		if($com['attributes']){
			foreach($com["attributes"] as $nitem => $item){
				Generator::formsPrint(" $nitem='$item' ");
			}
		}
		Generator::formsPrint(" onfocus='showConfirmPassword(this)' onblur='nextValidatePassword(this)'");
		Generator::formsPrint(" />\r\n");
		Generator::formsPrint("<br />
		<div id='div_fl_$name' style='display:none'>
		Reescribir Password:<br />
		<input type='password' name='confirm_fl_$name' id='confirm_flid_$name'");
		if(isset($_REQUEST['fl_'.$name])){
			Generator::formsPrint("value = '".$_REQUEST['fl_'.$name]."'");
		}
		if(isset($com['attributes'])){
			foreach($com["attributes"] as $nitem => $item){
				Generator::formsPrint(" $nitem='$item' ");
			}
		}
		Generator::formsPrint(" onblur='validatePassword(this, \"fl_$name\")' />\r\n</div>");
	}

	/**
 	 * Crea los componentes para campos que son imágenes
 	 *
 	 * @param array $com
 	 * @param string $name
 	 */
	static function buildStandardImage($com, $name){
		Generator::formsPrint("<label for='flid_$name'>".$com['caption']."</label></td><td valign='top'>");
		Generator::formsPrint("<table><tr><td>");
		if(!isset($_REQUEST['fl_'.$name])){
			$_REQUEST['fl_'.$name] = 'spacer.gif';
		} else	{
			if($_REQUEST['fl_'.$name]=='@'){
				$_REQUEST['fl_'.$name] = 'spacer.gif';
			}
		}
		Generator::formsPrint("<img src='".Core::getInstancePath()."img/".urldecode($_REQUEST['fl_'.$name])."'
	    	alt='' id='im_$name' style='border:1px solid black;width:128;height:128px' />");
		Generator::formsPrint("</td><td>
		 <select name='fl_$name' id='flid_$name' disabled='disabled'
		 onchange='
		 if(document.getElementById(\"im_$name\")){
		 	document.getElementById(\"im_$name\").src = \$Kumbia.path + \"img/\"+ this.options[this.selectedIndex].value
		 }'>
		 <option value='@'>Seleccione...</option>\n");
		foreach(scandir('public/img/upload/') as $file){
			if($file!='index.html'&&$file!='.'&&$file!='..'
			&&$file!='Thumbs.db'&&$file!='desktop.ini'&&$file!='CVS'){
				$nfile = str_replace('.gif', '', $file);
				$nfile = str_replace('.jpg', '', $nfile);
				$nfile = str_replace('.png', '', $nfile);
				$nfile = str_replace('.bmp', '', $nfile);
				$nfile = str_replace('_', ' ', $nfile);
				$nfile = ucfirst($nfile);
				$nfile = htmlentities($nfile);
				$file = htmlentities($file);
				if(urldecode("upload/$file")==urldecode($_REQUEST['fl_'.$name])){
					Generator::formsPrint("<option selected='selected' value='upload/$file'
					style='background: #EAEAEA'>$nfile</option>\n");
				} else {
					Generator::formsPrint("<option
					  value='upload/$file'>$nfile</option>\n");
				}
			}
		}
		Generator::formsPrint("</select> ");
		Generator::formsPrint("
		<input type='file' name='fl_{$name}_up' style='display:none' id='flid_{$name}_up' disabled='disabled' ");
		if(isset($com["attributes"])){
			foreach($com["attributes"] as $nitem => $item){
				if($nitem!='size'){
					Generator::formsPrint(" $nitem='$item' ");
				}
			}
		}
		Generator::formsPrint("
		onblur='if(document.getElementById(\"im_$name\")){
		 	document.getElementById(\"im_$name\").src = \"file://\"+$(\"flid_{$name}_up\").value
		}'
		/> <a name='a_$name' href='#a_$name' id='a_$name' onclick='show_upload_image(\"$name\")'>Subir Imagen</a>");
		Generator::formsPrint("</td></tr></table>");
	}

	/**
	 * Crea un componente de Tiempo
	 *
	 * @param array $com
	 * @param string $name
	 * @param array $form
	 */
	public static function buildTimeComponent($com, $name, $form){
		$arr = array();
		if(!$_REQUEST["fl_$name"]&&$com['value']){
			$_REQUEST["fl_$name"] = $com['value'];
		}
		if($_REQUEST["fl_$name"]){
			preg_matcg('/([0-2][0-9]):([0-5][0-8])/', $_REQUEST["fl_$name"], $arr);
		}
		Generator::formsPrint("<label for='flid_$name'>".$com['caption']."</label></td><td>\n");
		Generator::formsPrint("<select name='time{$name}_hour' id='time{$name}_hour'
		onchange='document.getElementById(\"flid_$name\").value = document.getElementById(\"time{$name}_hour\").options[document.getElementById(\"time{$name}_hour\").selectedIndex].value+\":\"+document.getElementById(\"time{$name}_minutes\").options[document.getElementById(\"time{$name}_minutes\").selectedIndex].value' disabled='disabled'>\n");
		for($i=0;$i<=23;++$i){
			if($arr[1]!=sprintf("%02s", $i)){
				Generator::formsPrint("<option value='".sprintf("%02s", $i)."'>".sprintf("%02s", $i)."</option>\n");
			} else {
				Generator::formsPrint("<option value='".sprintf("%02s", $i)."' selected='selected'>".sprintf("%02s", $i)."</option>\n");
			}
		}
		Generator::formsPrint("</select>:");
		Generator::formsPrint("<select name='time{$name}_minutes' id='time{$name}_minutes'
		onchange='document.getElementById(\"flid_$name\").value = document.getElementById(\"time{$name}_hour\").options[document.getElementById(\"time{$name}_hour\").selectedIndex].value+\":\"+document.getElementById(\"time{$name}_minutes\").options[document.getElementById(\"time{$name}_minutes\").selectedIndex].value' disabled='disabled'>\n");
		for($i=0;$i<60;++$i){
			if($arr[2]!=sprintf("%02s", $i)){
				Generator::formsPrint("<option value='".sprintf("%02s", $i)."'>".sprintf("%02s", $i)."</option>\n");
			} else {
				Generator::formsPrint("<option value='".sprintf("%02s", $i)."' selected='selected'>".sprintf("%02s", $i)."</option>\n");
			}
		}
		Generator::formsPrint("</select>");
		Generator::formsPrint("<input type='hidden' name='fl_$name' id='flid_$name' value='00:00' />");
	}

}
